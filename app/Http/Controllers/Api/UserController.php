<?php

namespace App\Http\Controllers\Api;

use App\Constants\ResponseCode;
use App\DTOs\Pagination;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|unique:users,email',
            'nik' => 'required|string|unique:users,nik',
            'password' => [
                'required',
                'string',
                'min:8',
                'max:72',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
            'position' => 'nullable|string|max:100',
            'role' => 'required|string|exists:roles,name',
        ]);

        if ($validator->fails()) {
            throw ResponseCode::badRequest($validator->errors());
        }

        // Cek user berdasarkan NIK atau Email yang soft-deleted
        $existingUser = User::withTrashed()
            ->where(function ($query) use ($request) {
                $query->where('nik', $request->input('nik'));
                if ($request->filled('email')) {
                    $query->orWhere('email', $request->input('email'));
                }
            })
            ->first();

        if ($existingUser && $existingUser->trashed()) {
            $existingUser->restore();
            $existingUser->update([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'nik' => $request->input('nik'),
                'password' => Hash::make($request->input('password')),
                'position' => $request->input('position'),
                'is_active' => true,
            ]);
            $existingUser->syncRoles([$request->input('role')]);

            return $this->successResponse([
                'id' => $existingUser->id,
                'name' => $existingUser->name,
                'email' => $existingUser->email,
                'nik' => $existingUser->nik,
                'position' => $existingUser->position,
                'role' => $existingUser->getRoleNames()->first()
            ], 'Soft-deleted user restored and updated successfully.');
        }

        // Create user baru
        $user = User::create([
            'name' => $request->input('name'),
            'nik' => $request->input('nik'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'position' => $request->input('position'),
            'is_active' => true,
        ]);

        $user->assignRole($request->input('role'));

        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'nik' => $user->nik,
            'position' => $user->position,
            'role' => $user->getRoleNames()->first()
        ], 'User registered successfully.');
    }


    public function updateRole(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|string|exists:roles,name',
        ]);

        if ($validator->fails()) {
            throw ResponseCode::badRequest($validator->errors());
        }

        $user->syncRoles([$request->input('role')]);

        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'nik' => $user->nik,
            'position' => $user->position,
            'role' => $user->getRoleNames()->first()
        ], 'User role updated successfully.');
    }

    public function show(User $user): JsonResponse
    {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'position' => $user->position,
            'nik' => $user->nik,
            'is_active' => $user->is_active,
            'roles' => $user->getRoleNames(), // From Spatie
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ];

        return $this->successResponse($data, 'User detail retrieved successfully');
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$user->id}",
            'position' => 'required|string|max:100',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            throw ResponseCode::badRequest($validator->errors());
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'position' => $request->position,
            'is_active' => $request->is_active,
        ]);

        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'position' => $user->position,
            'is_active' => $user->is_active,
        ], 'User updated successfully');
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            throw ResponseCode::badRequest('You cannot delete your own account');
        }

        $user->delete();

        return $this->successResponse(null, 'User deleted successfully');
    }

    public function updatePassword(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => [
                'required',
                'string',
                'min:8',
                'max:72',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[\W]/',
            ],
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            throw ResponseCode::badRequest($validator->errors());
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return $this->successResponse(null, 'Password updated successfully');
    }

    public function changeOwnPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => [
                'required',
                'string',
                'min:8',
                'max:72',
                'regex:/[a-z]/',       // lowercase
                'regex:/[A-Z]/',       // uppercase
                'regex:/[0-9]/',       // number
                'regex:/[\W]/',        // special char
            ],
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            throw ResponseCode::badRequest($validator->errors());
        }

        $user = $request->user();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            throw ResponseCode::passwordOrEmailIsInvalid(); // Atau buat khusus `ResponseCode::currentPasswordInvalid()` jika perlu.
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return $this->successResponse(null, 'Password changed successfully');
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'search'   => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page'     => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            throw ResponseCode::badRequest($validator->errors());
        }

        $pagination = new Pagination($request);

        $query = User::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('search') . '%');
            })
            ->orderBy('name');

        $users = $query
            ->offset($pagination->getOffset())
            ->limit($pagination->getLimit())
            ->get()
            ->map(function ($user) {
                return [
                    'id'        => $user->id,
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'position'  => $user->position,
                    'nik'       => $user->nik,
                    'is_active' => $user->is_active,
                    'permission' => $user->getAllPermissions()->pluck('name'),
                    'role'      => $user->roles->pluck('name')->first(), // if single role
                ];
            });

        $total = $query->toBase()->getCountForPagination();

        return $this->successResponse(
            $pagination->buildPaginationResponse($users, $total, shownTotal: true)
        );
    }
}
