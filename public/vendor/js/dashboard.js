/* global $, window, document */

(function () {
    'use strict';

    const DASHBOARD = window.__DASHBOARD || {};
    const STORAGE_KEY = 'ap_dashboard_year';
    const OTD_PAGE_SIZE = 12;
    const otdUiState = { search: '', page: 1 };

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

    function renderOtdPagination(totalRows, totalPages, currentPage) {
        const $pager = $('#otdDetailPagination');
        if (!$pager.length) return;

        if (!totalRows) {
            $pager.html('');
            return;
        }

        let pagesHtml = '';
        const maxButtons = 5;
        let start = Math.max(1, currentPage - Math.floor(maxButtons / 2));
        const end = Math.min(totalPages, start + maxButtons - 1);
        start = Math.max(1, end - maxButtons + 1);

        for (let p = start; p <= end; p += 1) {
            pagesHtml += '<button type="button" class="btn btn-sm ' + (p === currentPage ? 'btn-primary' : 'btn-outline-secondary') + ' js-otd-page mx-1" data-page="' + p + '">' + p + '</button>';
        }

        const prevDisabled = currentPage <= 1 ? 'disabled' : '';
        const nextDisabled = currentPage >= totalPages ? 'disabled' : '';

        $pager.html(
            '<div class="small text-muted my-1">Showing ' + totalRows + ' records</div>' +
            '<div class="btn-group btn-group-sm my-1" role="group" aria-label="OTD pagination">' +
                '<button type="button" class="btn btn-outline-secondary js-otd-page" data-page="' + (currentPage - 1) + '" ' + prevDisabled + '>Prev</button>' +
                pagesHtml +
                '<button type="button" class="btn btn-outline-secondary js-otd-page" data-page="' + (currentPage + 1) + '" ' + nextDisabled + '>Next</button>' +
            '</div>'
        );
    }

    function applyOtdSearch(query, page) {
        const needle = String(query || '').trim().toLowerCase();
        otdUiState.search = needle;
        if (typeof page === 'number' && page > 0) {
            otdUiState.page = page;
        }

        const $tbody = $('#otdDetailTbody');
        const $rows = $tbody.find('tr');
        const noMatchId = 'otdDetailNoMatchRow';
        $('#' + noMatchId).remove();

        const dataRows = [];

        $rows.each(function () {
            const $row = $(this);
            const $cells = $row.children('td');

            // Placeholder rows ("Loading...", "No results.", etc.) are single colspan rows.
            if ($cells.length <= 1 && $cells.first().attr('colspan')) {
                return;
            }

            dataRows.push($row);
        });

        if (!dataRows.length) {
            renderOtdPagination(0, 0, 1);
            return;
        }

        const matchedRows = dataRows.filter(function ($row) {
            const haystack = $row.text().toLowerCase();
            return !needle || haystack.indexOf(needle) !== -1;
        });

        const totalPages = Math.max(1, Math.ceil(matchedRows.length / OTD_PAGE_SIZE));
        if (otdUiState.page > totalPages) {
            otdUiState.page = totalPages;
        }

        const from = (otdUiState.page - 1) * OTD_PAGE_SIZE;
        const to = from + OTD_PAGE_SIZE;
        const pagedRows = matchedRows.slice(from, to);

        dataRows.forEach(function ($row) { $row.hide(); });
        pagedRows.forEach(function ($row) { $row.show(); });

        if (matchedRows.length === 0) {
            $tbody.append('<tr id="' + noMatchId + '"><td colspan="7" class="text-center text-muted py-3">No matching records.</td></tr>');
            renderOtdPagination(0, 0, 1);
            return;
        }

        renderOtdPagination(matchedRows.length, totalPages, otdUiState.page);
    }

    function loadOtdDetails(year, month, filter, searchQuery) {
        if (!DASHBOARD.otdDetailsUrl) return;

        renderLoading();
        setFilterActive(filter);
        $('#otdDetailMeta').text(monthName(month) + ' ' + year + ' • ' + filter);

        $.get(DASHBOARD.otdDetailsUrl, { year, month, filter })
            .done(function (res) {
                $('#otdDetailTbody').html(res && res.html ? res.html : '');
                otdUiState.page = 1;
                applyOtdSearch(searchQuery, 1);
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

    function toggleOtdSearchClear() {
        const hasValue = String($('#otdDetailSearch').val() || '').length > 0;
        $('#otdDetailSearchClear').toggleClass('is-visible', hasValue);
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
        let selected = { year: DASHBOARD.year || null, month: null, filter: 'all', search: '' };

        $(document).on('click', '.js-otd-cell', function () {
            const year = parseInt($(this).data('year'), 10);
            const month = parseInt($(this).data('month'), 10);
            if (!year || !month) return;

            selected.year = year;
            selected.month = month;
            selected.filter = 'all';

            $('#otdDetailModal').modal('show');
            loadOtdDetails(selected.year, selected.month, selected.filter, selected.search);
        });

        // Filter buttons
        $(document).on('click', '.js-otd-filter', function () {
            const filter = String($(this).data('filter') || 'all');
            selected.filter = filter;
            if (!selected.year || !selected.month) return;
            loadOtdDetails(selected.year, selected.month, selected.filter, selected.search);
        });

        $('#otdDetailSearch').on('input', function () {
            selected.search = String($(this).val() || '');
            otdUiState.page = 1;
            applyOtdSearch(selected.search, 1);
            toggleOtdSearchClear();
        });

        $('#otdDetailSearchClear').on('click', function () {
            selected.search = '';
            $('#otdDetailSearch').val('').trigger('focus');
            otdUiState.page = 1;
            applyOtdSearch('', 1);
            toggleOtdSearchClear();
        });

        $(document).on('click', '.js-otd-page', function () {
            const page = parseInt($(this).data('page'), 10);
            if (!page || page < 1) return;
            otdUiState.page = page;
            applyOtdSearch(selected.search, page);
        });

        // Reset modal on close
        $('#otdDetailModal').on('hidden.bs.modal', function () {
            $('#otdDetailMeta').text('Select a month.');
            $('#otdDetailTbody').html('<tr><td colspan="7" class="text-center text-muted py-3">Select a month.</td></tr>');
            $('#otdDetailPagination').html('');
            $('#otdDetailSearch').val('');
            setFilterActive('all');
            selected.month = null;
            selected.filter = 'all';
            selected.search = '';
            otdUiState.page = 1;
            toggleOtdSearchClear();
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
