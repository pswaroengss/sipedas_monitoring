<?php

namespace Modules\Monitoring\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MonitoringController extends Controller
{
    public function index()
    {
        $areas = DB::table('m_area')
            ->select('m_area_id', 'm_area_nama')
            ->orderBy('m_area_id')
            ->get();

        return view('monitoring::index', [
            'areas' => $areas,
        ]);
    }

    public function report()
    {
        $areas = DB::table('m_area')
            ->select('m_area_id', 'm_area_nama')
            ->orderBy('m_area_id')
            ->get();

        return view('monitoring::report', [
            'areas' => $areas,
        ]);
    }

    public function process(Request $request)
    {
        // return $request->all();
        $validator = Validator::make($request->all(), [
            'tanggal' => ['nullable', 'date'],
            'area' => ['nullable', 'string'],
            'waroeng' => ['nullable', 'string'],
            'kategori' => ['nullable', 'string'],
            'fokus' => ['nullable', 'string'],
            'monitoring_task_id' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // $filters = [
        //     'tanggal' => $request->input('tanggal'),
        //     'area' => $request->input('area'),
        //     'waroeng' => $request->input('waroeng'),
        //     'kategori' => $request->input('kategori'),
        //     'fokus' => $request->input('fokus'),
        // ];

        $now = now();

        try {
            $areaId = $request->input('area');
            $waroengId = $request->input('waroeng');

            if (strpos($request->input('tanggal'), 'to') !== false) {
                [$start, $end] = explode('to', $request->input('tanggal'));
                $start = trim($start);
                $end = trim($end);
            } else {
                $start = $request->input('tanggal');
                $end = $request->input('tanggal');
            }

            $area = null;
            if (!empty($areaId) && $areaId !== 'all') {
                $area = DB::table('m_area')
                    ->select('m_area_id', 'm_area_code', 'm_area_nama')
                    ->where('m_area_id', $areaId)
                    ->first();
            }

            $waroeng = null;
            if (!empty($waroengId) && $waroengId !== 'all') {
                $waroeng = DB::table('m_w')
                    ->select('m_w_id', 'm_w_code', 'm_w_nama')
                    ->where('m_w_id', $waroengId)
                    ->first();
            }

            $taskId = $request->input('monitoring_task_id')
                ?: $this->getNextId('monitoring_task', $waroengId);

            $taskPayload = [
                'monitoring_task_id' => $taskId,
                'monitoring_task_type' => $request->input('fokus') ?? '',
                'monitoring_task_m_w_id' => $waroeng->m_w_id ?? 0,
                'monitoring_task_m_w_code' => $waroeng->m_w_code ?? '',
                'monitoring_task_m_w_nama' => $waroeng->m_w_nama ?? '',
                'monitoring_task_m_area_id' => $area->m_area_id ?? 0,
                'monitoring_task_m_area_nama' => $area->m_area_nama ?? '',
                'monitoring_task_m_area_code' => $area->m_area_code ?? '',
                'monitoring_task_start_date' => $start,
                'monitoring_task_end_date' => $end,
                'monitoring_task_status' => 'run',
                'monitoring_task_deskripsi' => 'Monitoring SIPEDAS',
                'monitoring_task_failure_report' => '',
                'monitoring_task_client_target' => ':'. $waroeng->m_w_id .':',
            ];

            $taskExists = DB::table('monitoring_task')
                ->where('monitoring_task_id', $taskId)
                ->exists();

            if ($taskExists) {
                DB::table('monitoring_task')
                    ->where('monitoring_task_id', $taskId)
                    ->update(array_merge($taskPayload, [
                        'monitoring_task_updated_by' => data_get(session('auth_user'), 'id'),
                        'monitoring_task_updated_at' => $now,
                    ]));
            } else {
                DB::table('monitoring_task')
                    ->insert(array_merge($taskPayload, [
                        'monitoring_task_created_by' => data_get(session('auth_user'), 'id'),
                        'monitoring_task_created_at' => $now,
                    ]));
            }
        } catch (\Throwable $e) {
            \Log::error('Monitoring process failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'ok' => false,
                'message' => 'Process failed',
                'error' => $e->getMessage(),
            ], 500);
        }

           return response()->json([
                'ok' => true,
                'monitoring_task_id' => $taskId,
                'status' => 'run',
            ]);
    }

    public function result(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'tanggal' => ['nullable', 'date'],
        ]);

        // if ($validator->fails()) {
        //     return response()->json([
        //         'ok' => false,
        //         'message' => 'Validation failed',
        //         'errors' => $validator->errors(),
        //     ], 422);
        // }

        $page = (int) ($request->input('page', 1));
        $perPage = (int) ($request->input('per_page', 20));

          if (strpos($request->input('tanggal'), 'to') !== false) {
                [$start, $end] = explode('to', $request->input('tanggal'));
                $start = trim($start);
                $end = trim($end);
            } else {
                $start = $request->input('tanggal');
                $end = $request->input('tanggal');
            }

        $query = DB::table('monitoring_task')
            ->leftJoin('users', 'users_id', '=', 'monitoring_task_created_by');

        if (!empty($start) && !empty($end)) {
            $query->whereDate('monitoring_task_start_date', '>=', $start)
                ->whereDate('monitoring_task_end_date', '<=', $end);
        }

        $total = (clone $query)->count();

        $data = $query
            ->select([
                'monitoring_task_type',
                'monitoring_task_m_area_nama',
                'monitoring_task_m_w_nama',
                'monitoring_task_start_date',
                'monitoring_task_end_date',
                'monitoring_task_status',
                'monitoring_task_failure_report',
                DB::raw('COALESCE(name ,email, CAST(monitoring_task_created_by AS TEXT)) AS monitoring_task_created_by_name'),
            ])
            ->orderByDesc('monitoring_task_created_at')
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

    public function reportData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => ['nullable', 'date'],
            'area' => ['nullable', 'string'],
            'waroeng' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = DB::table('rekap_monitoring_sistem')
            ->leftJoin('users', 'users_id', '=', 'r_m_s_created_by');

        if ($request->filled('tanggal')) {
            $query->whereDate('r_m_s_tanggal', $request->input('tanggal'));
        }

        if ($request->filled('area') && $request->input('area') !== 'all') {
            $query->where('r_m_s_m_area_id', $request->input('area'));
        }

        if ($request->filled('waroeng') && $request->input('waroeng') !== 'all') {
            $query->where('r_m_s_m_w_id', $request->input('waroeng'));
        }

        $data = $query->select([
            'r_m_s_kategori',
            'r_m_s_m_area_nama',
            'r_m_s_m_w_nama',
            'r_m_s_tanggal',
            'r_m_s_table_sumber',
            'r_m_s_table_tujuan',
            'r_m_s_status',
            'r_m_s_kode_temuan',
            DB::raw('COALESCE(name, email, CAST(r_m_s_created_by AS TEXT)) AS created_by_name'),
        ])->orderByDesc('r_m_s_tanggal')->get();

        return response()->json([
            'ok' => true,
            'data' => $data,
        ]);
    }

    public function getWaroengPenjualan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'area_id' => ['required'],
            'tanggal' => ['nullable', 'string'],
            'all_waroeng' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'waroeng' => [],
                'waroeng_default' => null,
            ]);
        }

        $waroeng = DB::table('m_w')
            ->selectRaw('m_w_id, m_w_nama')
            ->where('m_w_m_area_id', $request->area_id)
            ->orderBy('m_w_nama')
            ->get();

        return response()->json([
            'waroeng' => $waroeng,
            'waroeng_default' => data_get(session('auth_user'), 'waroeng_id'),
        ]);
    }

    private function getNextId($table, $waroengId)
    {
        $maxId = DB::select("SELECT MAX(id) FROM {$table};")[0]->max ?? null;
        $currentId = DB::select("SELECT last_value FROM {$table}_id_seq;")[0]->last_value ?? null;

        if (!empty($maxId) && $currentId >= 1) {
            if ($maxId != $currentId) {
                DB::select("SELECT setval('{$table}_id_seq', {$maxId});");
            }
        }

        $words = explode("_", $table);
        $prefix = "";

        foreach ($words as $w) {
            $prefix .= mb_substr($w, 0, 1);
        }

        $date = Carbon::now()->format('ymdHis');
        $waroengInfo = DB::table('m_w')->where('m_w_id', $waroengId)->first();

        $counter = DB::table('app_id_counter')
            ->where([
                'app_id_counter_m_w_id' => $waroengId,
                'app_id_counter_table'  => $table,
            ]);

        if (!empty($counter->first())) {
            if ($counter->first()->app_id_counter_date == Carbon::now()->format('Y-m-d')) {
                $nextCounter = $counter->first()->app_id_counter_value + 1;
                $counter->update([
                    'app_id_counter_value' => $nextCounter,
                ]);
            } else {
                $nextCounter = 1;
                $counter->update([
                    'app_id_counter_value' => $nextCounter,
                    'app_id_counter_date'  => Carbon::now()->format('Y-m-d'),
                ]);
            }
        } else {
            $nextCounter = 1;
            DB::table('app_id_counter')->upsert(
                [
                    [
                        'app_id_counter_m_w_id' => $waroengId,
                        'app_id_counter_table'  => $table,
                        'app_id_counter_value'  => $nextCounter,
                        'app_id_counter_date'   => Carbon::now()->format('Y-m-d'),
                    ],
                ],
                ['app_id_counter_m_w_id', 'app_id_counter_table'],
                ['app_id_counter_value', 'app_id_counter_date']
            );
        }

        $userId = Auth::user()->users_id;
        $id = $waroengId . "." . ($waroengInfo->m_w_m_area_id ?? '') . "." . $userId . "." . $date . "." . $nextCounter;
        return strtoupper($prefix) . "." . $id;
    }

    public function getKategoriList()
    {
        $rows = DB::table('monitoring_type')
            ->select('monitoring_type_type')
            ->whereNull('monitoring_type_deleted_at')
            ->distinct()
            ->orderBy('monitoring_type_type')
            ->get();

        return response()->json([
            'kategori' => $rows,
        ]);
    }

    public function getFokusByKategori(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kategori' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'fokus' => [],
            ], 422);
        }

        $rows = DB::table('monitoring_type')
            ->select('monitoring_type_id', 'monitoring_type_nama')
            ->where('monitoring_type_type', $request->input('kategori'))
            ->whereNull('monitoring_type_deleted_at')
            ->orderBy('monitoring_type_nama')
            ->get();

        return response()->json([
            'fokus' => $rows,
        ]);
    }

    public function monitoringJs()
    {
        $path = base_path('Modules/Monitoring/Resources/assets/js/monitoring.js');
        return response()->file($path, [
            'Content-Type' => 'application/javascript',
        ]);
    }

    public function laporanJs()
    {
        $path = base_path('Modules/Monitoring/Resources/assets/js/laporan.js');
        return response()->file($path, [
            'Content-Type' => 'application/javascript',
        ]);
    }
}
