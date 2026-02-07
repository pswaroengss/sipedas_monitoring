$(function () {
    function listToHtml(items, labelKey, valueKey) {
        if (!items || items.length === 0) {
            return '-';
        }
        return items.map(function (row) {
            return '<span class=\"chip\">' + (row[labelKey] || '-') + ' ' + row[valueKey] + '</span>';
        }).join('<br>');
    }

    function renderTable(id, rows, columns) {
        if (!$.fn.DataTable) return;
        if ($.fn.DataTable.isDataTable(id)) {
            $(id).DataTable().clear().rows.add(rows || []).draw();
            return;
        }
        $(id).DataTable({
            data: rows || [],
            columns: columns,
            order: [],
            pageLength: 10
        });
    }

    $.ajax({
        url: '/dashboard/data',
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            if (!res || !res.ok) return;

            $('#runningCount').text(res.running_count || 0);
            $('#temuanToday').text(res.temuan_today || 0);
            if (res.month_label) {
                $('#todayLabel').text('Periode ' + res.month_label);
            } else if (res.month_range) {
                $('#todayLabel').text('Periode ' + res.month_range.join(' - '));
            } else {
                $('#todayLabel').text('Periode bulan ini');
            }

            $('#topArea').html(listToHtml(res.top_area, 'r_m_s_m_area_nama', 'total'));
            $('#topKategori').html(listToHtml(res.top_kategori, 'r_m_s_kategori', 'total'));

            if (res.last7 && res.today) {
                $('#temuanRange').text(res.last7 + ' - ' + res.today);
            }

            renderTable('#temuanTable', res.recent_temuan, [
                { data: 'r_m_s_tanggal', defaultContent: '' },
                { data: 'r_m_s_kategori', defaultContent: '' },
                { data: 'r_m_s_m_area_nama', defaultContent: '' },
                { data: 'r_m_s_m_w_nama', defaultContent: '' },
                { data: 'r_m_s_status', defaultContent: '' },
                { data: 'r_m_s_kode_temuan', defaultContent: '' }
            ]);
        }
    });
});
