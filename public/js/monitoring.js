$(function () {
    var lastLogId = null;

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    function formData() {
        return {
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            area: $('#area').val(),
            waroeng: $('#waroeng').val(),
            kategori: $('#kategori').val(),
            fokus: $('#fokus').val()
        };
    }

    function showStatus(html, type) {
        var cls = type || 'info';
        $('#statusBox').html('<div class="alert alert-' + cls + '">' + html + '</div>');
    }

    function renderTable(rows) {
        if (!rows || rows.length === 0) {
            $('#resultTable').html('<div class="alert alert-secondary">No results.</div>');
            return;
        }

        var html = '';
        html += '<div class="table-responsive">';
        html += '<table class="table table-sm table-bordered">';
        html += '<thead><tr>';
        html += '<th>ID</th><th>Log ID</th><th>Status</th><th>Note</th><th>Created At</th>';
        html += '</tr></thead><tbody>';

        rows.forEach(function (row) {
            html += '<tr>';
            html += '<td>' + (row.id ?? '') + '</td>';
            html += '<td>' + (row.monitoring_log_id ?? '') + '</td>';
            html += '<td>' + (row.status ?? '') + '</td>';
            html += '<td>' + (row.note ?? '') + '</td>';
            html += '<td>' + (row.created_at ?? '') + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        $('#resultTable').html(html);
    }

    $('#btnProcess').on('click', function () {
        var $btn = $(this);
        $btn.prop('disabled', true);
        showStatus('Processing...', 'info');

        $.ajax({
            url: '/monitoring/process',
            method: 'POST',
            data: formData(),
            headers: {
                'X-CSRF-TOKEN': csrfToken()
            }
        }).done(function (res) {
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
        }).fail(function (xhr) {
            var msg = 'Process failed.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            showStatus(msg, 'danger');
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });

    $('#btnResult').on('click', function () {
        showStatus('Loading results...', 'info');

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
                renderTable(res.data);
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
