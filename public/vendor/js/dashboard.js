/* global $, window, document */

(function () {
    'use strict';

    const DASHBOARD = window.__DASHBOARD || {};
    const OTD_PAGE_SIZE = 12;
    const otdUiState = { search: '', page: 1 };
    const faiUiState = { search: '', page: 1 };

    function getQueryParam(name) {
        const url = new URL(window.location.href);
        return url.searchParams.get(name);
    }

    function normalizeDashboardUrl() {
        const url = new URL(window.location.href);
        if (!url.searchParams.has('year')) return;
        url.searchParams.delete('year');
        const qs = url.searchParams.toString();
        const next = url.pathname + (qs ? ('?' + qs) : '') + url.hash;
        window.history.replaceState({}, '', next);
    }

    function refreshDashboardKpiContainer(year) {
        const url = new URL(window.location.href);
        url.searchParams.set('year', year);

        const $container = $('#dashboardKpiContainer');
        if (!$container.length) {
            window.location.href = url.toString();
            return;
        }

        $container.addClass('is-loading');

        $.get(url.toString())
            .done(function (html) {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const next = doc.querySelector('#dashboardKpiContainer');
                if (!next) {
                    window.location.href = url.toString();
                    return;
                }

                $container.replaceWith(next);
                DASHBOARD.year = parseInt(year, 10) || DASHBOARD.year;
                normalizeDashboardUrl();
                refreshKpiMetaUi();
            })
            .fail(function () {
                window.location.href = url.toString();
            })
            .always(function () {
                $('#dashboardKpiContainer').removeClass('is-loading');
            });
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
        $('#otdDetailTbody').html('<tr><td colspan="7" class="text-center text-muted py-3">Loading...</td></tr>');
    }

    function renderError(message) {
        const msg = message || 'Error loading details.';
        $('#otdDetailTbody').html('<tr><td colspan="7" class="text-center text-danger py-3">' + msg + '</td></tr>');
    }

    function renderPager($pager, pageClass, totalRows, totalPages, currentPage, ariaLabel) {
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
            pagesHtml += '<button type="button" class="btn btn-sm ' + (p === currentPage ? 'btn-primary' : 'btn-outline-secondary') + ' ' + pageClass + ' mx-1" data-page="' + p + '">' + p + '</button>';
        }

        const prevDisabled = currentPage <= 1 ? 'disabled' : '';
        const nextDisabled = currentPage >= totalPages ? 'disabled' : '';

        $pager.html(
            '<div class="small text-muted my-1">Showing ' + totalRows + ' records</div>' +
            '<div class="btn-group btn-group-sm my-1" role="group" aria-label="' + ariaLabel + '">' +
                '<button type="button" class="btn btn-outline-secondary ' + pageClass + '" data-page="' + (currentPage - 1) + '" ' + prevDisabled + '>Prev</button>' +
                pagesHtml +
                '<button type="button" class="btn btn-outline-secondary ' + pageClass + '" data-page="' + (currentPage + 1) + '" ' + nextDisabled + '>Next</button>' +
            '</div>'
        );
    }

    function applySearchAndPaginate(opts) {
        const needle = String(opts.query || '').trim().toLowerCase();
        const uiState = opts.uiState;
        uiState.search = needle;

        if (typeof opts.page === 'number' && opts.page > 0) {
            uiState.page = opts.page;
        }

        const $tbody = $(opts.tbodySelector);
        const $rows = $tbody.find('tr');
        $('#' + opts.noMatchId).remove();

        const dataRows = [];
        $rows.each(function () {
            const $row = $(this);
            const $cells = $row.children('td');
            if ($cells.length <= 1 && $cells.first().attr('colspan')) {
                return;
            }
            dataRows.push($row);
        });

        if (!dataRows.length) {
            renderPager($(opts.pagerSelector), opts.pageClass, 0, 0, 1, opts.ariaLabel);
            return;
        }

        const matchedRows = dataRows.filter(function ($row) {
            const haystack = $row.text().toLowerCase();
            return !needle || haystack.indexOf(needle) !== -1;
        });

        const totalPages = Math.max(1, Math.ceil(matchedRows.length / OTD_PAGE_SIZE));
        if (uiState.page > totalPages) {
            uiState.page = totalPages;
        }

        const from = (uiState.page - 1) * OTD_PAGE_SIZE;
        const to = from + OTD_PAGE_SIZE;
        const pagedRows = matchedRows.slice(from, to);

        dataRows.forEach(function ($row) { $row.hide(); });
        pagedRows.forEach(function ($row) { $row.show(); });

        if (matchedRows.length === 0) {
            $tbody.append('<tr id="' + opts.noMatchId + '"><td colspan="7" class="text-center text-muted py-3">No matching records.</td></tr>');
            renderPager($(opts.pagerSelector), opts.pageClass, 0, 0, 1, opts.ariaLabel);
            return;
        }

        renderPager($(opts.pagerSelector), opts.pageClass, matchedRows.length, totalPages, uiState.page, opts.ariaLabel);
    }

    function applyOtdSearch(query, page) {
        applySearchAndPaginate({
            query,
            page,
            uiState: otdUiState,
            tbodySelector: '#otdDetailTbody',
            pagerSelector: '#otdDetailPagination',
            noMatchId: 'otdDetailNoMatchRow',
            pageClass: 'js-otd-page',
            ariaLabel: 'OTD pagination',
        });
    }

    function applyFaiSearch(query, page) {
        applySearchAndPaginate({
            query,
            page,
            uiState: faiUiState,
            tbodySelector: '#faiRejDetailTbody',
            pagerSelector: '#faiRejDetailPagination',
            noMatchId: 'faiRejDetailNoMatchRow',
            pageClass: 'js-fai-page',
            ariaLabel: 'FAI pagination',
        });
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

    function loadFaiRejDetails(year, month, searchQuery) {
        if (!DASHBOARD.faiRejDetailsUrl) return;

        $('#faiRejDetailTbody').html('<tr><td colspan="7" class="text-center text-muted py-3">Loading...</td></tr>');
        $('#faiRejDetailMeta').text(monthName(month) + ' ' + year);

        $.get(DASHBOARD.faiRejDetailsUrl, { year, month })
            .done(function (res) {
                $('#faiRejDetailTbody').html(res && res.html ? res.html : '');
                faiUiState.page = 1;
                applyFaiSearch(searchQuery, 1);
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

    function toggleFaiSearchClear() {
        const hasValue = String($('#faiRejDetailSearch').val() || '').length > 0;
        $('#faiRejDetailSearchClear').toggleClass('is-visible', hasValue);
    }

    function formatNowEs() {
        const now = new Date();
        const parts = new Intl.DateTimeFormat('es-ES', {
            month: 'short',
            day: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        }).formatToParts(now);
        const pick = function (t) { return (parts.find(function (p) { return p.type === t; }) || {}).value || ''; };
        const month = String(pick('month') || '').replace('.', '');
        const monthCap = month ? (month.charAt(0).toUpperCase() + month.slice(1)) : '';
        return monthCap + '/' + pick('day') + '/' + pick('year') + ' ' + pick('hour') + ':' + pick('minute');
    }

    function refreshKpiMetaUi() {
        // Default tooltip for badges if not explicitly set.
        $('.kpi-sidecards .kpi-badge').each(function () {
            const $b = $(this);
            if (!$b.attr('title')) {
                $b.attr('title', String($b.text() || '').trim());
            }
        });
    }

    function focusKpiRow(rowKey) {
        if (!rowKey) return;
        const $row = $('.kpi-report tbody tr[data-kpi-row="' + rowKey + '"]').first();
        if (!$row.length) return;

        $row.addClass('kpi-row-focus');
        const target = $row[0];
        if (target && typeof target.scrollIntoView === 'function') {
            target.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
        }
        setTimeout(function () { $row.removeClass('kpi-row-focus'); }, 1300);
    }

    $(function () {
        normalizeDashboardUrl();
        refreshKpiMetaUi();

        // Year select
        $(document).on('change', '#dashboardYearSelect', function () {
            const year = String($(this).val() || '');
            if (!year) return;
            refreshDashboardKpiContainer(year);
        });

        $(document).on('click', '#dashboardYearReset', function () {
            const currentYear = String(new Date().getFullYear());
            $('#dashboardYearSelect').val(currentYear);
            refreshDashboardKpiContainer(currentYear);
        });

        $(document).on('click', '.kpi-sidecards .dashboard-kpi-box', function () {
            const label = String($(this).find('.dashboard-kpi-label').first().text() || '').toLowerCase();
            if (label.indexOf('fai rej') !== -1) {
                focusKpiRow('fai_rej');
                return;
            }
            if (label.indexOf('otd') !== -1) {
                focusKpiRow('customer_otd');
            }
        });

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
        let faiSelected = { year: DASHBOARD.year || null, month: null, search: '' };

        $(document).on('click', '.js-fai-rej-cell', function () {
            const year = parseInt($(this).data('year'), 10);
            const month = parseInt($(this).data('month'), 10);
            if (!year || !month) return;

            faiSelected.year = year;
            faiSelected.month = month;

            $('#faiRejDetailModal').modal('show');
            loadFaiRejDetails(faiSelected.year, faiSelected.month, faiSelected.search);
        });

        $('#faiRejDetailSearch').on('input', function () {
            faiSelected.search = String($(this).val() || '');
            faiUiState.page = 1;
            applyFaiSearch(faiSelected.search, 1);
            toggleFaiSearchClear();
        });

        $('#faiRejDetailSearchClear').on('click', function () {
            faiSelected.search = '';
            $('#faiRejDetailSearch').val('').trigger('focus');
            faiUiState.page = 1;
            applyFaiSearch('', 1);
            toggleFaiSearchClear();
        });

        $(document).on('click', '.js-fai-page', function () {
            const page = parseInt($(this).data('page'), 10);
            if (!page || page < 1) return;
            faiUiState.page = page;
            applyFaiSearch(faiSelected.search, page);
        });

        $('#faiRejDetailModal').on('hidden.bs.modal', function () {
            $('#faiRejDetailMeta').text('Select a month.');
            $('#faiRejDetailTbody').html('<tr><td colspan="7" class="text-center text-muted py-3">Select a month.</td></tr>');
            $('#faiRejDetailPagination').html('');
            $('#faiRejDetailSearch').val('');
            faiSelected.month = null;
            faiSelected.search = '';
            faiUiState.page = 1;
            toggleFaiSearchClear();
        });
    });
})();
