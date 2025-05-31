<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Pagination;
use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\StockHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockController extends Controller
{

    public function stockIn(Request $request, Medicine $medicine): JsonResponse
    {
        $data = $request->except('request_header');

        $validator = Validator::make($data, [
            'amount' => 'required|integer|min:1',
            'note' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            throw ResponseCode::badRequest($validator->errors());
        }

        $medicine->increment('stock', $data['amount']);

        $history = StockHistory::create([
            'medicine_id' => $medicine->id,
            'user_id' => $request->user()->id,
            'type' => 'in',
            'amount' => $data['amount'],
            'note' => $data['note'] ?? null,
        ]);

        return $this->successResponse([
            'medicine_id' => $medicine->id,
            'new_stock' => $medicine->stock,
            'history_id' => $history->id,
        ], 'Stock successfully added.');
    }

    public function stockOut(Request $request, Medicine $medicine): JsonResponse
    {
        $data = $request->except('request_header');

        $validator = Validator::make($data, [
            'amount' => 'required|integer|min:1',
            'note' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            throw ResponseCode::badRequest($validator->errors());
        }

        if ($medicine->stock < $data['amount']) {
            throw ResponseCode::stockNotEnough($medicine->stock);
        }

        $medicine->decrement('stock', $data['amount']);

        $history = StockHistory::create([
            'medicine_id' => $medicine->id,
            'user_id' => $request->user()->id,
            'type' => 'out',
            'amount' => $data['amount'],
            'note' => $data['note'] ?? null,
        ]);

        return $this->successResponse([
            'medicine_id' => $medicine->id,
            'new_stock' => $medicine->stock,
            'history_id' => $history->id,
        ], 'Stock successfully reduced.');
    }

    public function history(Request $request, Medicine $medicine): JsonResponse
    {
        $pagination = new Pagination($request);

        $query = $medicine->stockHistories()
            ->with('user')
            ->when($request->filled('start_date'), function ($q) use ($request) {
                $startUtc = Carbon::createFromFormat('Y-m-d', $request->start_date, 'Asia/Jakarta')
                    ->startOfDay()
                    ->timezone('UTC');
                $q->where('created_at', '>=', $startUtc);
            })
            ->when($request->filled('end_date'), function ($q) use ($request) {
                $endUtc = Carbon::createFromFormat('Y-m-d', $request->end_date, 'Asia/Jakarta')
                    ->endOfDay()
                    ->timezone('UTC');
                $q->where('created_at', '<=', $endUtc);
            })
            ->orderBy('created_at', 'desc');

        $totalData = $query->count();

        $items = $query->offset($pagination->getOffset())
            ->limit($pagination->getLimit())
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => $item->type,
                    'amount' => $item->amount,
                    'note' => $item->note,
                    'user' => $item->user?->name,
                    'created_at' => $item->created_at->toDateTimeString(),
                ];
            });

        return $this->successResponse(
            $pagination->buildPaginationResponse($items, $totalData, shownTotal: true),
            'Stock history fetched successfully.'
        );
    }

    public function exportHistory(Request $request, Medicine $medicine): StreamedResponse
    {
        $query = $medicine->stockHistories()
            ->with('user')
            ->when($request->filled('start_date'), function ($q) use ($request) {
                $startUtc = Carbon::createFromFormat('Y-m-d', $request->start_date, 'Asia/Jakarta')
                    ->startOfDay()
                    ->timezone('UTC');
                $q->where('created_at', '>=', $startUtc);
            })
            ->when($request->filled('end_date'), function ($q) use ($request) {
                $endUtc = Carbon::createFromFormat('Y-m-d', $request->end_date, 'Asia/Jakarta')
                    ->endOfDay()
                    ->timezone('UTC');
                $q->where('created_at', '<=', $endUtc);
            })
            ->orderBy('created_at', 'desc');

        $filename = 'stock_history_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Type', 'Amount', 'Note', 'User']);

            $query->chunk(100, function ($rows) use ($file) {
                foreach ($rows as $item) {
                    fputcsv($file, [
                        $item->created_at->timezone('Asia/Jakarta')->toDateTimeString(),
                        strtoupper($item->type),
                        $item->amount,
                        $item->note,
                        $item->user?->name ?? '-',
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function indexAll(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'medicine_name' => 'nullable|string',
            'type' => 'nullable|in:in,out',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'page' => 'nullable|integer',
            'per_page' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            throw ResponseCode::badRequest($validator->errors());
        }

        $pagination = new Pagination($request);

        $query = StockHistory::with(['medicine:id,name', 'user:id,name'])
            ->when($request->filled('medicine_name'), function ($q) use ($request) {
                $q->whereHas('medicine', function ($sub) use ($request) {
                    $sub->where('name', 'like', '%' . $request->medicine_name . '%');
                });
            })
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
            ->when($request->filled('start_date'), fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->orderByDesc('created_at');

        $totalData = $query->count();

        $results = $query->offset($pagination->getOffset())
            ->limit($pagination->getLimit())
            ->get();

        $mapped = $results->map(fn($item) => [
            'medicine_name' => $item->medicine->name ?? '-',
            'user_name' => $item->user->name ?? '-',
            'type' => $item->type,
            'amount' => $item->amount,
            'note' => $item->note,
            'created_at' => $item->created_at
                    ->timezone('Asia/Jakarta')  // ⬅️ kunci timezone lokal
                    ->format('Y-m-d H:i:s'),
        ]);

        $response = $pagination->buildPaginationResponse($mapped, $totalData, shownTotal: true);

        return $this->successResponse($response);
    }

    public function exportAllHistory(Request $request): StreamedResponse
    {
        $validator = Validator::make($request->all(), [
            'medicine_name' => 'nullable|string',
            'type' => 'nullable|in:in,out',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            throw ResponseCode::badRequest($validator->errors());
        }

        $query = StockHistory::with(['medicine:id,name', 'user:id,name'])
            ->when($request->filled('medicine_name'), function ($q) use ($request) {
                $q->whereHas('medicine', function ($sub) use ($request) {
                    $sub->where('name', 'like', '%' . $request->medicine_name . '%');
                });
            })
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
            ->when($request->filled('start_date'), fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->orderByDesc('created_at');

        $filename = 'stock_history_all_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Type', 'Amount', 'Note', 'User', 'Medicine']);

            $query->chunk(100, function ($rows) use ($file) {
                foreach ($rows as $item) {
                    fputcsv($file, [
                        $item->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                        strtoupper($item->type),
                        $item->amount,
                        $item->note,
                        $item->user->name ?? '-',
                        $item->medicine->name ?? '-',
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
