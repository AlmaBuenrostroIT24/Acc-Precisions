/* global $, window, document */

(function () {
    'use strict';

    const DASHBOARD = window.__DASHBOARD || {};
    const STORAGE_KEY = 'ap_dashboard_year';

    function getQueryParam(name) {
        const url = new URL(window.location.href);
        return url.searchParams.get(name);
    }

    function setQueryParam(name, value) {
        const url = new URL(window.location.href);
        url.searchParams.set(name, value);
        window.location.href = url.toString();
    }

    function monthName(month) {
        const names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return names[month - 1] || ('M' + month);
    }

    function setFilterActive(filter) {
        $('.js-otd-filter').removeClass('active');
        $('.js-otd-filter[data-filter="' + filter + '"]').addClass('active');
    }

    function renderLoading() {
        $('#otdDetailTbody').html('<tr><td colspan="7" class="text-center text-muted py-3">Loading…</td></tr>');
    }

    function renderError(message) {
        const msg = message || 'Error loading details.';
        $('#otdDetailTbody').html('<tr><td colspan="7" class="text-center text-danger py-3">' + msg + '</td></tr>');
    }

    function loadOtdDetails(year, month, filter) {
        if (!DASHBOARD.otdDetailsUrl) return;

        renderLoading();
        setFilterActive(filter);
        $('#otdDetailMeta').text(monthName(month) + ' ' + year + ' • ' + filter);

        $.get(DASHBOARD.otdDetailsUrl, { year, month, filter })
            .done(function (res) {
                $('#otdDetailTbody').html(res && res.html ? res.html : '');
                const count = (res && typeof res.count === 'number') ? res.count : 0;
                $('#otdDetailMeta').text(monthName(month) + ' ' + year + ' • ' + filter + ' • ' + count + ' rows');
            })
            .fail(function (xhr) {
                const msg = xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : null;
                renderError(msg);
            });
    }

    function loadFaiRejDetails(year, month) {
        if (!DASHBOARD.faiRejDetailsUrl) return;

        $('#faiRejDetailTbody').html('<tr><td colspan="7" class="text-center text-muted py-3">Loading…</td></tr>');
        $('#faiRejDetailMeta').text(monthName(month) + ' ' + year);

        $.get(DASHBOARD.faiRejDetailsUrl, { year, month })
            .done(function (res) {
                $('#faiRejDetailTbody').html(res && res.html ? res.html : '');
                const rejects = (res && typeof res.rejects === 'number') ? res.rejects : 0;
                const total = (res && typeof res.total === 'number') ? res.total : 0;
                const pct = (res && typeof res.pct === 'number') ? res.pct : null;
                const pctText = pct !== null ? (pct.toFixed(1) + '%') : '-';
                $('#faiRejDetailMeta').text(monthName(month) + ' ' + year + ' • ' + pctText + ' (' + rejects + '/' + total + ')');
            })
            .fail(function (xhr) {
                const msg = xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error loading details.';
                $('#faiRejDetailTbody').html('<tr><td colspan="7" class="text-center text-danger py-3">' + msg + '</td></tr>');
            });
    }

    $(function () {
        // Year select
        $('#dashboardYearSelect').on('change', function () {
            const year = String($(this).val() || '');
            if (!year) return;
            try { window.localStorage.setItem(STORAGE_KEY, year); } catch (e) {}
            setQueryParam('year', year);
        });

        // If URL has no ?year=, try localStorage
        const hasYearParam = !!getQueryParam('year');
        if (!hasYearParam) {
            let stored = null;
            try { stored = window.localStorage.getItem(STORAGE_KEY); } catch (e) {}
            if (stored && /^\d{4}$/.test(stored)) {
                setQueryParam('year', stored);
                return;
            }
        }

        // OTD cell click -> open modal + load details
        let selected = { year: DASHBOARD.year || null, month: null, filter: 'all' };

        $(document).on('click', '.js-otd-cell', function () {
            const year = parseInt($(this).data('year'), 10);
            const month = parseInt($(this).data('month'), 10);
            if (!year || !month) return;

            selected.year = year;
            selected.month = month;
            selected.filter = 'all';

            $('#otdDetailModal').modal('show');
            loadOtdDetails(selected.year, selected.month, selected.filter);
        });

        // Filter buttons
        $(document).on('click', '.js-otd-filter', function () {
            const filter = String($(this).data('filter') || 'all');
            selected.filter = filter;
            if (!selected.year || !selected.month) return;
            loadOtdDetails(selected.year, selected.month, selected.filter);
        });

        // Reset modal on close
        $('#otdDetailModal').on('hidden.bs.modal', function () {
            $('#otdDetailMeta').text('Select a month.');
            $('#otdDetailTbody').html('<tr><td colspan="7" class="text-center text-muted py-3">Select a month.</td></tr>');
            setFilterActive('all');
            selected.month = null;
            selected.filter = 'all';
        });

        // FAI Rejection cell click -> open modal + load details
        let faiSelected = { year: DASHBOARD.year || null, month: null };

        $(document).on('click', '.js-fai-rej-cell', function () {
            const year = parseInt($(this).data('year'), 10);
            const month = parseInt($(this).data('month'), 10);
            if (!year || !month) return;

            faiSelected.year = year;
            faiSelected.month = month;

            $('#faiRejDetailModal').modal('show');
            loadFaiRejDetails(faiSelected.year, faiSelected.month);
        });

        $('#faiRejDetailModal').on('hidden.bs.modal', function () {
            $('#faiRejDetailMeta').text('Select a month.');
            $('#faiRejDetailTbody').html('<tr><td colspan="7" class="text-center text-muted py-3">Select a month.</td></tr>');
            faiSelected.month = null;
        });
    });
})();
