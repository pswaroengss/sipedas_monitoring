@extends('layouts.app')

@section('title', 'Dashboard')

@push('head')
<link rel="stylesheet" href="{{ asset('css/monitoring.css') }}">
<style>
    .dash-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }
    .dash-card {
        background: #fffaf6;
        border-radius: 16px;
        padding: 16px;
        border: 1px solid rgba(161, 98, 7, 0.2);
        box-shadow: 0 18px 40px rgba(59, 35, 26, 0.08);
    }
    .dash-title {
        font-weight: 600;
        color: #7c3e12;
        margin-bottom: 8px;
    }
    .dash-value {
        font-size: 28px;
        font-weight: 800;
        color: #2b1a14;
    }
    .dash-sub {
        color: #7b5f52;
        font-size: 12px;
    }
    .chip {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 999px;
        background: rgba(242, 201, 76, 0.2);
        color: #7c3e12;
        font-size: 12px;
        margin-right: 6px;
        margin-bottom: 6px;
    }
    .temuan-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }
    .temuan-title {
        font-weight: 700;
        color: #2b1a14;
        letter-spacing: 0.3px;
    }
    .temuan-title span {
        color: #7c3e12;
    }
    .table-frame {
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid rgba(161, 98, 7, 0.18);
        background: #fffdf8;
    }
    .temuan-range {
        font-size: 12px;
        color: #7b5f52;
        background: rgba(123, 95, 82, 0.12);
        border: 1px solid rgba(123, 95, 82, 0.2);
        padding: 4px 10px;
        border-radius: 999px;
    }
    @media (max-width: 1200px) {
        .dash-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 768px) {
        .dash-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="monitor-hero mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h3 class="mb-1 monitor-title">
                <svg class="flame" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3c1.7 2.4 3 4.7 3 6.6 0 2.8-2.2 4.4-4.2 6.2-1.5 1.3-2.8 2.5-2.8 4.7 0 2.3 1.8 3.5 4 3.5s4-1.2 4-3.5c0-1.6-0.8-2.7-1.7-3.8 2.3 0.5 4.7-1.4 4.7-4.2 0-2.5-1.9-4.7-3.5-6.6-0.5 1.5-1.6 2.8-3.5 3.1C11.1 7.5 11.3 5.2 12 3z"></path>
                </svg>
                Dashboard Monitoring
            </h3>
            <div class="text-white-50">Ringkasan aktivitas monitoring hari ini.</div>
        </div>
        <div class="badge text-dark px-3 py-2" style="background: rgba(242, 201, 76, 0.9);">Dashboard</div>
    </div>
</div>

<div class="dash-grid mb-4">
    <div class="dash-card">
        <div class="dash-title">Task Running</div>
        <div class="dash-value" id="runningCount">0</div>
        <div class="dash-sub" id="todayLabel">Periode</div>
    </div>
    <div class="dash-card">
        <div class="dash-title">Temuan Bulan Ini</div>
        <div class="dash-value" id="temuanToday">0</div>
        <div class="dash-sub">Total temuan bulan ini</div>
    </div>
    <div class="dash-card">
        <div class="dash-title">Top Area</div>
        <div class="dash-sub" id="topArea"></div>
    </div>
    <div class="dash-card">
        <div class="dash-title">Top Kategori</div>
        <div class="dash-sub" id="topKategori"></div>
    </div>
</div>

<div class="result-shell">
    <div class="temuan-header">
        <h5 class="mb-0 temuan-title">Ringkasan <span>Temuan</span></h5>
        <span class="temuan-range" id="temuanRange">7 Hari Terakhir</span>
    </div>
    <div class="table-responsive">
        <table id="temuanTable" class="table table-sm table-striped nowrap table-bordered align-middle">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kategori</th>
                    <th>Area</th>
                    <th>Waroeng</th>
                    <th>Status</th>
                    <th>Kode Temuan</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ url('/modules/dashboard/dashboard.js') }}?v={{ filemtime(base_path('Modules/Dashboard/Resources/assets/js/dashboard.js')) }}"></script>
@endpush
