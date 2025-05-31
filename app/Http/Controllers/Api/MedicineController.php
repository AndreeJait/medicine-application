<?php

namespace App\Http\Controllers\Api;

use App\Constants\ResponseCode;
use App\DTOs\Pagination;
use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineImage;
use App\Models\StockHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MedicineController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $pagination = new Pagination($request);

        $query = Medicine::query()
            ->with('images')
            ->when($request->filled('name'), fn($q) => $q->where('name', 'like', '%' . $request->name . '%'))
            ->when($request->filled('unit'), fn($q) => $q->where('unit', $request->unit));

        $totalData = $query->count();

        $items = $query->offset($pagination->getOffset())
            ->limit($pagination->getLimit())
            ->get();

        return $this->successResponse(
            $pagination->buildPaginationResponse($items, $totalData, shownTotal: true),
            'Medicine list fetched successfully'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->except('request_header');

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:100',
            'stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            throw ResponseCode::badRequest($validator->errors());
        }

        $medicine = new Medicine();
        DB::transaction(function () use ($data, &$medicine) {
            $medicine = Medicine::create($data);
            $medicineHistory = StockHistory::create([
                'medicine_id' => $medicine->id,
                'type' => 'in',
                'amount' => $data['stock'],
                'note' => 'init stock'
            ]);
        });

        return $this->successResponse([
            'id' => $medicine->id,
            'name' => $medicine->name,
            'price' => $medicine->price,
            'unit' => $medicine->unit,
            'stock' => $medicine->stock,
            'description' => $medicine->description,
        ], 'Medicine created successfully.');
    }

    public function update(Request $request, Medicine $medicine): JsonResponse
    {
        $data = $request->except([
            'request_header',
            'stock',
        ]);


        $validator = Validator::make($data, [
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'description' => 'nullable|string',
            'unit' => 'sometimes|required|string|max:100',
        ]);

        if ($validator->fails()) {
            throw ResponseCode::badRequest($validator->errors());
        }


        $medicine->update($data);

        return $this->successResponse([
            'id' => $medicine->id,
            'name' => $medicine->name,
            'price' => $medicine->price,
            'unit' => $medicine->unit,
            'stock' => $medicine->stock,
            'description' => $medicine->description,
        ], 'Medicine updated successfully.');
    }

    public function destroy(Request $request, Medicine $medicine): JsonResponse
    {
        DB::transaction(function () use ($medicine) {
            $medicine->delete();
            StockHistory::create([
                'medicine_id' => $medicine->id,
                'type' => 'out',
                'amount' => $medicine->stock,
                'note' => 'medicine deleted',
            ]);
        });
        return $this->successResponse(null, 'Medicine deleted successfully.');
    }

    public function uploadImages(Request $request, Medicine $medicine): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            throw ResponseCode::badRequest($validator->errors());
        }

        $savedImages = [];

        foreach ($request->file('images') as $file) {
            $path = $file->store("private/medicines/{$medicine->id}");

            $image = MedicineImage::create([
                'medicine_id' => $medicine->id,
                'file_path' => $path, // Simpan path internal, bukan URL
            ]);

            $savedImages[] = [
                'id' => $image->id,
                'file_path' => $image->file_path,
            ];
        }

        return $this->successResponse([
            'images' => $savedImages
        ], 'Images uploaded successfully.');
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'medicines_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // CSV Header
            fputcsv($file, ['ID', 'Name', 'Price', 'Unit', 'Stock', 'Description']);

            // CSV Data
            Medicine::chunk(100, function ($medicines) use ($file) {
                foreach ($medicines as $medicine) {
                    fputcsv($file, [
                        $medicine->id,
                        $medicine->name,
                        $medicine->price,
                        $medicine->unit,
                        $medicine->stock,
                        $medicine->description,
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function viewImage(MedicineImage $image): StreamedResponse
    {
        if (!Storage::exists($image->file_path)) {
            throw ResponseCode::fileIsNotExits();
        }

        return Storage::response($image->file_path);
    }

    public function deleteImage(MedicineImage $image): JsonResponse
    {
        // Hapus file dari storage jika ada
        if (Storage::exists($image->file_path)) {
            Storage::delete($image->file_path);
        }

        // Hapus record dari database
        $image->delete();

        return $this->successResponse(null, 'Image deleted successfully.');
    }


    public function show(Medicine $medicine): JsonResponse
    {
        return $this->successResponse([
            'id' => $medicine->id,
            'name' => $medicine->name,
            'price' => $medicine->price,
            'description' => $medicine->description,
            'unit' => $medicine->unit,
            'stock' => $medicine->stock,
            'images' => $medicine->images->map(fn($img) => [
                'id' => $img->id,
                'file_path' => $img->file_path,
            ]),
            'created_at' => $medicine->created_at->timezone('Asia/Jakarta')->toDateTimeString(),
            'updated_at' => $medicine->updated_at->timezone('Asia/Jakarta')->toDateTimeString(),
        ]);
    }
}
