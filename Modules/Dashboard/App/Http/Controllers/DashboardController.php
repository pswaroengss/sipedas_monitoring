<?php

namespace Modules\Dashboard\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard::index');
    }

    public function data(Request $request)
    {
        $today = Carbon::now()->toDateString();
        $monthStart = Carbon::now()->startOfMonth()->toDateString();
        $monthEnd = Carbon::now()->endOfMonth()->toDateString();
        $last7 = Carbon::now()->subDays(6)->toDateString();

        $runningCount = DB::table('monitoring_task')
            ->where('monitoring_task_status', 'run')
            ->count();

        $runningTasks = DB::table('monitoring_task')
            ->leftJoin('users', 'users_id', '=', 'monitoring_task_created_by')
            ->select([
                'monitoring_task_type',
                'monitoring_task_m_area_nama',
                'monitoring_task_m_w_nama',
                'monitoring_task_start_date',
                'monitoring_task_end_date',
                'monitoring_task_status',
                DB::raw('COALESCE(name, email, CAST(monitoring_task_created_by AS TEXT)) AS created_by_name'),
            ])
            ->where('monitoring_task_status', 'run')
            ->orderByDesc('monitoring_task_created_at')
            ->limit(10)
            ->get();

        $temuanToday = DB::table('rekap_monitoring_sistem')
            ->whereBetween('r_m_s_tanggal', [$monthStart, $monthEnd])
            ->count();

        $statusSummary = DB::table('rekap_monitoring_sistem')
            ->select('r_m_s_status', DB::raw('COUNT(*) as total'))
            ->whereBetween('r_m_s_tanggal', [$monthStart, $monthEnd])
            ->groupBy('r_m_s_status')
            ->orderByDesc('total')
            ->get();

        $topArea = DB::table('rekap_monitoring_sistem')
            ->select('r_m_s_m_area_nama', DB::raw('COUNT(*) as total'))
            ->whereBetween('r_m_s_tanggal', [$monthStart, $monthEnd])
            ->groupBy('r_m_s_m_area_nama')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topKategori = DB::table('rekap_monitoring_sistem')
            ->select('r_m_s_kategori', DB::raw('COUNT(*) as total'))
            ->whereBetween('r_m_s_tanggal', [$monthStart, $monthEnd])
            ->groupBy('r_m_s_kategori')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $recentTemuan = DB::table('rekap_monitoring_sistem')
            ->leftJoin('users', 'users_id', '=', 'r_m_s_created_by')
            ->select([
                'r_m_s_tanggal',
                'r_m_s_kategori',
                'r_m_s_m_area_nama',
                'r_m_s_m_w_nama',
                'r_m_s_status',
                'r_m_s_kode_temuan',
                DB::raw('COALESCE(name, email, CAST(r_m_s_created_by AS TEXT)) AS created_by_name'),
            ])
            ->whereBetween('r_m_s_tanggal', [$last7, $today])
            ->orderByDesc('r_m_s_created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'ok' => true,
            'today' => $today,
            'running_count' => $runningCount,
            'running_tasks' => $runningTasks,
            'temuan_today' => $temuanToday,
            'status_summary' => $statusSummary,
            'top_area' => $topArea,
            'top_kategori' => $topKategori,
            'recent_temuan' => $recentTemuan,
            'month_range' => [$monthStart, $monthEnd],
            'last7' => $last7,
            'month_label' => Carbon::now()->translatedFormat('F Y'),
        ]);
    }

    public function dashboardJs()
    {
        $path = base_path('Modules/Dashboard/Resources/assets/js/dashboard.js');
        return response()->file($path, [
            'Content-Type' => 'application/javascript',
        ]);
    }
}
