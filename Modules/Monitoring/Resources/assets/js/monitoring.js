$(function () {
    var lastLogId = null;

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    function formData() {
        return {
            tanggal: $('#tanggal').val(),
            area: $('#filter_area').val(),
            waroeng: $('#filter_waroeng').val(),
            kategori: $('#kategori').val(),
            fokus: $('#fokus').val()
        };
    }

    function showStatus(html, type) {
        var cls = type || 'info';
        $('#statusBox').html('<div class="alert alert-' + cls + '">' + html + '</div>');
    }

    function renderDataTable(rows) {
        if (!$.fn.DataTable) {
            return;
        }

        if ($.fn.DataTable.isDataTable('#monitoringTable')) {
            $('#monitoringTable').DataTable().clear().rows.add(rows || []).draw();
            return;
        }

        $('#monitoringTable').DataTable({
            data: rows || [],
            columns: [
                { data: 'monitoring_task_type', defaultContent: '' },
                { data: 'monitoring_task_m_area_nama', defaultContent: '' },
                { data: 'monitoring_task_m_w_nama', defaultContent: '' },
                { data: 'monitoring_task_start_date', defaultContent: '' },
                { data: 'monitoring_task_end_date', defaultContent: '' },
                { data: 'monitoring_task_status', defaultContent: '' },
                { data: 'monitoring_task_failure_report', defaultContent: '' },
                { data: 'monitoring_task_created_by_name', defaultContent: '' }
            ],
            order: [],
            pageLength: 20
        });
    }

    function updateHiddenDates(rangeValue) {
        var value = rangeValue || $('#tanggal').val();
        var start = '';
        var end = '';

        if (value && value.indexOf('to') !== -1) {
            var parts = value.split('to');
            start = $.trim(parts[0] || '');
            end = $.trim(parts[1] || '');
        } else {
            start = $.trim(value || '');
            end = $.trim(value || '');
        }

        $('#start_date').val(start);
        $('#end_date').val(end);
    }

    function getWaroengSales(areaId, tanggal, allWaroeng) {
        if (!areaId || areaId === 'all') {
            $('.filter_waroeng').empty();
            if ($.fn.select2) {
                $('.filter_waroeng').trigger('change.select2');
            }
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
                $.each(data.waroeng, function (index, waroeng) {
                    $('.filter_waroeng').append(
                        '<option value="' + waroeng.m_w_id + '">' + waroeng.m_w_nama + '</option>'
                    );
                });
            }

            if (allWaroeng !== 'non aktif') {
                $('.filter_waroeng').append('<option value="all" selected>All Waroeng</option>');
            }

            if (data && data.waroeng_default) {
                $('.filter_waroeng').val(data.waroeng_default).change();
                if ($.fn.select2) {
                    $('.filter_waroeng').trigger('change.select2');
                }
            }
        }).fail(function (xhr, status, error) {
            console.log('Error:', error);
        });
    }

    $('.filter_area').on('change', function () {
        var areaId = $(this).val();
        var tanggal = $('#tanggal').val();
        var allWaroeng = 'aktif';

        if (areaId === 'all') {
            $('#select_waroeng').hide();
            $('.filter_waroeng').empty();
        } else {
            $('#select_waroeng').show();
            getWaroengSales(areaId, tanggal, allWaroeng);
        }
    });

    function initSelect2() {
        if (!$.fn.select2) {
            return;
        }

        $('.js-select2').each(function () {
            var $el = $(this);
            if ($el.data('select2')) {
                return;
            }
            $el.select2({
                width: 'resolve',
                placeholder: $el.data('placeholder') || 'Pilih',
                allowClear: true,
                minimumResultsForSearch: 0
            });
        });
    }

    initSelect2();

    function loadKategori() {
        $.ajax({
            url: '/monitoring/kategori',
            type: 'GET',
            dataType: 'json'
        }).done(function (data) {
            var $kategori = $('#kategori');
            $kategori.empty().append('<option value=""></option>');

            if (data && data.kategori) {
                $.each(data.kategori, function (_, row) {
                    $kategori.append('<option value="' + row.monitoring_type_type + '">' + row.monitoring_type_type + '</option>');
                });
            }

            if ($.fn.select2) {
                $kategori.trigger('change.select2');
            }
        });
    }

    function loadFokusByKategori(kategori) {
        var $fokus = $('#fokus');
        $fokus.empty().append('<option value=""></option>');

        if (!kategori) {
            if ($.fn.select2) {
                $fokus.trigger('change.select2');
            }
            return;
        }

        $.ajax({
            url: '/monitoring/fokus',
            type: 'GET',
            dataType: 'json',
            data: { kategori: kategori }
        }).done(function (data) {
            if (data && data.fokus) {
                $.each(data.fokus, function (_, row) {
                    $fokus.append('<option value="' + row.monitoring_type_nama + '">' + row.monitoring_type_nama + '</option>');
                });
            }

            if ($.fn.select2) {
                $fokus.trigger('change.select2');
            }
        });
    }

    $('#kategori').on('change', function () {
        loadFokusByKategori($(this).val());
    });

    loadKategori();

    var tanggalPicker = null;
    function initTanggalPicker(mode) {
        if (!window.flatpickr) {
            return;
        }
        if (tanggalPicker) {
            tanggalPicker.destroy();
        }
        tanggalPicker = flatpickr('#tanggal', {
            dateFormat: 'Y-m-d',
            defaultDate: "today",
             onChange: function(selectedDates, dateStr, instance) {
                instance.set("mode", "range");
                updateHiddenDates(dateStr);
                var areaId = $('.filter_area').val();
                var tanggal = dateStr;
                var allWaroeng = 'aktif';
                getWaroengSales(areaId, tanggal, allWaroeng);
            }
        });
    }

    initTanggalPicker();

    $('input[name="tanggal_mode"]').on('change', function () {
        var mode = $(this).val();
        initTanggalPicker(mode);
        updateHiddenDates('');
        $('#tanggal').val('');
    });

    var defaultArea = $('.filter_area').find('option').first().val();
    if (defaultArea) {
        $('.filter_area').val(defaultArea).trigger('change');
    }

    $('#btnProcess').on('click', function (e) {
        e.preventDefault();

        var $btn = $(this);
        $btn.prop('disabled', true);
        showStatus('Processing...', 'info');
        updateHiddenDates();

        $.ajax({
            url: '/monitoring/process',
            type: 'POST',
            data: formData(),
            headers: {
                'X-CSRF-TOKEN': csrfToken()
            },
            success: function (res) {
                if (res && res.ok) {
                    lastLogId = res.log_id;
                    var t = res.totals || {};
                    showStatus(
                        'Done. Log ID: ' + res.log_id + '. Totals: data=' + (t.total_data ?? 0) +
                            ', ok=' + (t.total_ok ?? 0) +
                            ', warning=' + (t.total_warning ?? 0) +
                            ', error=' + (t.total_error ?? 0),
                        'success'
                    );
                } else {
                    showStatus('Process failed.', 'danger');
                }
                $btn.prop('disabled', false);
            },
            error: function (xhr) {
                var msg = 'Process failed.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showStatus(msg, 'danger');
                $btn.prop('disabled', false);
            }
        });

        return false;
    });

    $('#btnResult').on('click', function () {
        showStatus('Loading results...', 'info');
        updateHiddenDates();

        var params = formData();
        params.page = 1;
        params.per_page = 20;
        if (lastLogId) {
            params.log_id = lastLogId;
        }

        $.ajax({
            url: '/monitoring/result',
            method: 'GET',
            data: params,
            headers: {
                'X-CSRF-TOKEN': csrfToken()
            }
        }).done(function (res) {
            if (res && res.ok) {
                renderDataTable(res.data || []);
                showStatus('Results loaded.', 'success');
            } else {
                showStatus('Failed to load results.', 'danger');
            }
        }).fail(function (xhr) {
            var msg = 'Failed to load results.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            showStatus(msg, 'danger');
        });
    });
});
