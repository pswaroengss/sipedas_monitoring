@extends('layouts.app')

@section('title', 'Monitoring SIPEDAS')

@push('head')
<link rel="stylesheet" href="{{ asset('css/monitoring.css') }}">
@endpush

@section('content')
<div class="monitor-hero mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h3 class="mb-1 monitor-title">
                <svg class="flame" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3c1.7 2.4 3 4.7 3 6.6 0 2.8-2.2 4.4-4.2 6.2-1.5 1.3-2.8 2.5-2.8 4.7 0 2.3 1.8 3.5 4 3.5s4-1.2 4-3.5c0-1.6-0.8-2.7-1.7-3.8 2.3 0.5 4.7-1.4 4.7-4.2 0-2.5-1.9-4.7-3.5-6.6-0.5 1.5-1.6 2.8-3.5 3.1C11.1 7.5 11.3 5.2 12 3z"></path>
                </svg>
                Monitoring SIPEDAS
            </h3>
            <div class="text-white-50">Jalankan kroscek data dan pantau hasil secara real-time.</div>
        </div>
        <div class="badge text-dark px-3 py-2" style="background: rgba(242, 201, 76, 0.9);">Local Server Check</div>
    </div>
</div>

<div class="card monitor-card mb-4">
    <div class="card-body">
        <form id="filterForm" class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="filter_tanggal">Tanggal</label>
                <input type="text" class="form-control monitor-field tanggal" id="tanggal" placeholder="Pilih Tanggal.." readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="filter_area">Area</label>
                <select id="filter_area" class="form-control monitor-field filter_area js-select2" name="area">
                    @foreach ($areas as $area)
                        <option value="{{ $area->m_area_id }}">{{ ucwords($area->m_area_nama) }}</option>
                    @endforeach
                    <option value="all">All Area</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="select_waroeng">Waroeng</label>
                <select id="filter_waroeng" class="form-control monitor-field filter_waroeng js-select2" name="waroeng" data-placeholder="Pilih Waroeng"></select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="kategori">Kategori</label>
                <select id="kategori" class="form-control monitor-field kategori js-select2" name="kategori" data-placeholder="Pilih Kategori">
                    <option value=""></option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="fokus">Fokus</label>
                <select id="fokus" class="form-control monitor-field fokus js-select2" name="fokus" data-placeholder="Pilih Fokus">
                    <option value=""></option>
                </select>
            </div>
            <div class="col-12 d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-primary btn-glow" id="btnProcess">Proses</button>
                <button type="button" class="btn ghost-btn" id="btnResult">Cek Hasil</button>
            </div>
        </form>
    </div>
</div>

<div id="statusBox" class="status-box mb-3">Status: idle</div>
<div class="result-shell">
    <div class="table-responsive">
        <table id="monitoringTable" class="table table-sm table-striped nowrap table-bordered align-middle">
            <thead>
                <tr>
                    <th class="text-center">Type</th>
                    <th class="text-center">Area</th>
                    <th class="text-center">Waroeng</th>
                    <th class="text-center">Start Date</th>
                    <th class="text-center">End Date</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Failure Report</th>
                    <th class="text-center">Created By</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ url('/modules/monitoring/monitoring.js') }}?v={{ filemtime(base_path('Modules/Monitoring/Resources/assets/js/monitoring.js')) }}"></script>
@endpush
