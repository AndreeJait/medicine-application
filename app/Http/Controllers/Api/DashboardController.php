<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\StockHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class DashboardController extends Controller
{
    public function summary(): JsonResponse
    {
        $totalMedicine = Medicine::count();
        $totalStock = Medicine::sum('stock');

        $totalStockIn = StockHistory::where('type', 'in')->sum('amount');
        $totalStockOut = StockHistory::where('type', 'out')->sum('amount');

        $recentActivities = StockHistory::with(['medicine:id,name', 'user:id,name'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get([
                'id', 'medicine_id', 'user_id', 'type', 'amount', 'note', 'created_at'
            ])
            ->map(function ($item) {
                return [
                    'medicine_name' => $item->medicine->name ?? '-',
                    'user_name'     => $item->user->name ?? '-',
                    'type'          => $item->type,
                    'amount'        => $item->amount,
                    'note'          => $item->note,
                    'created_at'    => $item->created_at
                        ->timezone('Asia/Jakarta')  // ⬅️ kunci timezone lokal
                        ->format('Y-m-d H:i:s'),
                ];
            });

        $topMedicines = Medicine::orderByDesc('stock')
            ->limit(5)
            ->get(['id', 'name', 'stock']);

        $data = [
            'total_medicine'    => $totalMedicine,
            'total_stock'       => $totalStock,
            'total_stock_in'    => $totalStockIn,
            'total_stock_out'   => $totalStockOut,
            'recent_activities' => $recentActivities,
            'top_medicines'     => $topMedicines,
        ];

        return $this->successResponse($data);
    }


    public function stockChart(): JsonResponse
    {
        $history = StockHistory::orderBy('created_at')->get();

        $grouped = $history->groupBy(function ($item) {
            return $item->created_at
                ->setTimezone('Asia/Jakarta') // <- penting!
                ->format('Y-m-d');
        });

        $data = $grouped->map(function ($rows, $date) {
            $sum = $rows->sum(function ($item) {
                return $item->type === 'in' ? $item->amount : -$item->amount;
            });

            return [
                'date' => $date,
                'total_change' => (int) $sum,
            ];
        })->values();

        return $this->successResponse($data);
    }


    public function exportStockChart(): \Illuminate\Http\Response
    {
        $history = StockHistory::selectRaw('DATE(created_at) as date, SUM(CASE WHEN type = "in" THEN amount ELSE -amount END) as total_change')
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get();

        $csvData = "Date,Total Change\n";
        foreach ($history as $row) {
            $csvData .= "{$row->date},{$row->total_change}\n";
        }

        $fileName = 'stock_chart_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        return Response::make(rtrim($csvData, "\n"), 200, $headers);
    }

}
