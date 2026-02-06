<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MonitoringController extends Controller
{
    public function index()
    {
        return view('monitoring.index');
    }

    public function process(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'area' => ['nullable', 'string'],
            'waroeng' => ['nullable', 'string'],
            'kategori' => ['nullable', 'string'],
            'fokus' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $start = Carbon::parse($request->input('start_date'));
        $end = Carbon::parse($request->input('end_date'));

        if ($end->lt($start)) {
            return response()->json([
                'ok' => false,
                'message' => 'end_date must be after start_date',
            ], 422);
        }

        if ($start->diffInDays($end) > 31) {
            return response()->json([
                'ok' => false,
                'message' => 'Date range must be 31 days or less',
            ], 422);
        }

        $filters = [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'area' => $request->input('area'),
            'waroeng' => $request->input('waroeng'),
            'kategori' => $request->input('kategori'),
            'fokus' => $request->input('fokus'),
        ];

        $connection = DB::connection('central');
        $now = now();

        try {
            $logId = $connection->table('monitoring_logs')->insertGetId([
                'filter_json' => json_encode($filters),
                'status' => 'processing',
                'total_data' => 0,
                'total_ok' => 0,
                'total_warning' => 0,
                'total_error' => 0,
                'created_by' => data_get(session('auth_user'), 'id'),
                'created_at' => $now,
            ]);

            // Placeholder kroscek logic (sync)
            $placeholderResults = [
                [
                    'monitoring_log_id' => $logId,
                    'tanggal' => $filters['start_date'],
                    'area' => $filters['area'],
                    'waroeng' => $filters['waroeng'],
                    'kategori' => $filters['kategori'],
                    'fokus' => $filters['fokus'],
                    'status' => 'OK',
                    'note' => 'Placeholder kroscek result',
                    'created_at' => $now,
                ],
            ];

            $connection->table('monitoring_results')->insert($placeholderResults);

            $totals = [
                'total_data' => 1,
                'total_ok' => 1,
                'total_warning' => 0,
                'total_error' => 0,
            ];

            $connection->table('monitoring_logs')
                ->where('id', $logId)
                ->update(array_merge($totals, [
                    'status' => 'done',
                ]));

            $result = [
                'log_id' => $logId,
                'status' => 'done',
                'totals' => $totals,
            ];
        } catch (\Throwable $e) {
            if (!empty($logId ?? null)) {
                $connection->table('monitoring_logs')
                    ->where('id', $logId)
                    ->update(['status' => 'failed']);
            }
            return response()->json([
                'ok' => false,
                'message' => 'Process failed',
            ], 500);
        }

        return response()->json([
            'ok' => true,
            'log_id' => $result['log_id'],
            'status' => $result['status'],
            'totals' => $result['totals'],
        ]);
    }

    public function result(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'log_id' => ['nullable', 'integer'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'area' => ['nullable', 'string'],
            'waroeng' => ['nullable', 'string'],
            'kategori' => ['nullable', 'string'],
            'fokus' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $page = (int) ($request->input('page', 1));
        $perPage = (int) ($request->input('per_page', 20));

        $connection = DB::connection('central');

        $query = $connection->table('monitoring_results as r');

        if ($request->filled('log_id')) {
            $query->where('r.monitoring_log_id', $request->input('log_id'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('r.tanggal', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('r.tanggal', '<=', $request->input('end_date'));
        }

        foreach (['area', 'waroeng', 'kategori', 'fokus'] as $field) {
            if ($request->filled($field)) {
                $query->where("r.$field", $request->input($field));
            }
        }

        $total = (clone $query)->count();

        $data = $query
            ->select('r.*')
            ->orderByDesc('r.created_at')
            ->forPage($page, $perPage)
            ->get();

        return response()->json([
            'ok' => true,
            'data' => $data,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
            ],
        ]);
    }
}
