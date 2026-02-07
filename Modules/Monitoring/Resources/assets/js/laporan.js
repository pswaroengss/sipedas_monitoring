$(function () {
    function initSelect2() {
        if (!$.fn.select2) return;
        $('.js-select2').each(function () {
            var $el = $(this);
            if ($el.data('select2')) return;
            $el.select2({
                width: 'resolve',
                placeholder: $el.data('placeholder') || 'Pilih',
                allowClear: true,
                minimumResultsForSearch: 0
            });
        });
    }

    function getWaroengSales(areaId, tanggal, allWaroeng) {
        if (!areaId || areaId === 'all') {
            $('.filter_waroeng').empty().append('<option value="all" selected>All Waroeng</option>');
            if ($.fn.select2) $('.filter_waroeng').trigger('change.select2');
            return;
        }

        $.ajax({
            url: '/get_waroeng_penjualan',
            type: 'GET',
            dataType: 'json',
            data: {
                area_id: areaId,
                tanggal: tanggal,
                all_waroeng: allWaroeng
            }
        }).done(function (data) {
            $('.filter_waroeng').empty();
            if (data && data.waroeng) {
                $.each(data.waroeng, function (_, waroeng) {
                    $('.filter_waroeng').append('<option value="' + waroeng.m_w_id + '">' + waroeng.m_w_nama + '</option>');
                });
            }
            $('.filter_waroeng').append('<option value="all" selected>All Waroeng</option>');
            if ($.fn.select2) $('.filter_waroeng').trigger('change.select2');
        });
    }

    function initTanggal() {
        if (!window.flatpickr) return;
        flatpickr('.tanggal', {
            dateFormat: 'Y-m-d',
            defaultDate: 'today',
            onChange: function (selectedDates, dateStr) {
                var areaId = $('.filter_area').val();
                getWaroengSales(areaId, dateStr, 'aktif');
            }
        });
    }

    function renderTable(rows) {
        if (!$.fn.DataTable) return;
        if ($.fn.DataTable.isDataTable('#reportTable')) {
            $('#reportTable').DataTable().clear().rows.add(rows || []).draw();
            return;
        }
        $('#reportTable').DataTable({
            data: rows || [],
            columns: [
                { data: 'r_m_s_kategori', defaultContent: '' },
                { data: 'r_m_s_m_area_nama', defaultContent: '' },
                { data: 'r_m_s_m_w_nama', defaultContent: '' },
                { data: 'r_m_s_tanggal', defaultContent: '' },
                { data: 'r_m_s_table_sumber', defaultContent: '' },
                { data: 'r_m_s_table_tujuan', defaultContent: '' },
                { data: 'r_m_s_status', defaultContent: '' },
                { data: 'r_m_s_kode_temuan', defaultContent: '' },
                { data: 'created_by_name', defaultContent: '' }
            ],
            order: [],
            pageLength: 20
        });
    }

    $('.filter_area').on('change', function () {
        var areaId = $(this).val();
        var tanggal = $('#tanggal').val();
        getWaroengSales(areaId, tanggal, 'aktif');
    });

    $('#btnResult').on('click', function (e) {
        e.preventDefault();
        $.ajax({
            url: '/monitoring/laporan/data',
            type: 'GET',
            dataType: 'json',
            data: {
                tanggal: $('#tanggal').val(),
                area: $('#filter_area').val(),
                waroeng: $('#select_waroeng').val()
            },
            success: function (res) {
                if (res && res.ok) {
                    renderTable(res.data || []);
                }
            }
        });
    });

    initSelect2();
    initTanggal();

    var defaultArea = $('.filter_area').find('option').first().val();
    if (defaultArea) {
        $('.filter_area').val(defaultArea).trigger('change');
    }
});
