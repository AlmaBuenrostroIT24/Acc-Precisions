@extends('adminlte::page')

@section('title', 'Order Inspection Events')

@section('content')
@php
    $partRev = $order->PN . ' - ' . \Illuminate\Support\Str::before((string) $order->Part_description, ',');
    $ops = max(0, (int) ($order->operation ?? 0));
    $sampling = max(0, (int) ($order->sampling ?? 0));
    $ipiReqPerOp = max(0, $sampling - 1);
    $inspectorName = trim((string) ($events->first()->inspector ?? auth()->user()->name ?? ''));

    $normResult = function ($v) {
        $r = strtolower(trim((string) $v));
        if (in_array($r, ['nopass', 'no_pass', 'fail'], true)) return 'no pass';
        return $r;
    };

    $opLabel = function (int $i) {
        return match ($i) {
            1 => '1st Op',
            2 => '2nd Op',
            3 => '3rd Op',
            default => $i . 'th Op'
        };
    };

    $statusRows = [];
    for ($i = 1; $i <= $ops; $i++) {
        $label = $opLabel($i);
        $opEvents = $events->filter(function ($e) use ($label) {
            return trim((string) $e->operation) === $label;
        });

        $faiPassQty = (int) $opEvents->filter(function ($e) use ($normResult) {
            return strtoupper(trim((string) $e->insp_type)) === 'FAI' && $normResult($e->results) === 'pass';
        })->sum(function ($e) {
            return (int) ($e->qty_pcs ?? 1);
        });

        $faiNoPass = (int) $opEvents->filter(function ($e) use ($normResult) {
            return strtoupper(trim((string) $e->insp_type)) === 'FAI' && $normResult($e->results) === 'no pass';
        })->count();

        $ipiPassQty = (int) $opEvents->filter(function ($e) use ($normResult) {
            return strtoupper(trim((string) $e->insp_type)) === 'IPI' && $normResult($e->results) === 'pass';
        })->sum(function ($e) {
            return (int) ($e->qty_pcs ?? 1);
        });

        $ipiNoPass = (int) $opEvents->filter(function ($e) use ($normResult) {
            return strtoupper(trim((string) $e->insp_type)) === 'IPI' && $normResult($e->results) === 'no pass';
        })->count();

        $doneCount = (int) $opEvents->count();

        $faiOk = $faiPassQty >= 1;
        $ipiOk = $ipiReqPerOp === 0 ? true : ($ipiPassQty >= $ipiReqPerOp);
        $state = ($faiOk && $ipiOk) ? 'ok' : (($faiNoPass > 0 || $ipiNoPass > 0) ? 'warn' : 'pending');

        $statusRows[] = [
            'label' => $label,
            'state' => $state,
            'fai_pass' => $faiPassQty,
            'fai_req' => 1,
            'fai_np' => $faiNoPass,
            'ipi_pass' => $ipiPassQty,
            'ipi_req' => $ipiReqPerOp,
            'ipi_np' => $ipiNoPass,
            'done' => $doneCount,
        ];
    }

    $totals = $summary['totals'] ?? [];
    $faiReqTotal = (int) ($totals['faiReq'] ?? 0);
    $faiPassTotal = (int) ($totals['faiPass'] ?? 0);
    $faiFailTotal = (int) ($totals['faiFail'] ?? 0);
    $ipiReqTotal = (int) ($totals['ipiReq'] ?? 0);
    $ipiPassTotal = (int) ($totals['ipiPass'] ?? 0);
    $ipiFailTotal = (int) ($totals['ipiFail'] ?? 0);

    $faiPct = $faiReqTotal > 0 ? max(0, min(100, (int) round(($faiPassTotal / $faiReqTotal) * 100))) : 0;
    $ipiPct = $ipiReqTotal > 0 ? max(0, min(100, (int) round(($ipiPassTotal / $ipiReqTotal) * 100))) : 0;
@endphp

<div class="print-report-head d-none">
    <div class="prh-top">
        <div>
            <div class="prh-title">FAI/IPI INSPECTION REPORT</div>
            <div class="prh-sub">Order inspection event detail</div>
        </div>
        <div class="prh-meta">
            <div><strong>Print date:</strong> {{ now()->format('m/d/Y H:i') }}</div>
            <div><strong>Job:</strong> {{ $order->work_id }}</div>
            <div><strong>Part:</strong> {{ $order->PN }}</div>
        </div>
    </div>
    <div class="prh-line"></div>
</div>

<div class="card shadow-sm mb-3">
    <div class="modal-header-like d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <span class="evt-title-icon mr-2"><i class="fas fa-clipboard-check"></i></span>
            <div>
                <h5 class="mb-0">Inspection Process</h5>
                <small class="text-muted">Capture and track FAI / IPI inspections</small>
            </div>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-erp btn-erp-danger mr-2" id="btnPrintOrderEvents">
                <i class="fas fa-print mr-1"></i> Print
            </button>
            <a href="{{ route('faisummary.completed') }}" class="btn btn-sm btn-erp btn-erp-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card-body p-2 p-md-3">
        <div class="row">
            <div class="col-lg-7">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="evt-label">Inspector</label>
                        <input type="text" class="form-control evt-readonly" value="{{ $inspectorName }}" readonly>
                    </div>
                    <div class="col-md-5 mb-2">
                        <label class="evt-label">Part# Rev.</label>
                        <input type="text" class="form-control evt-readonly" value="{{ $partRev }}" readonly>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="evt-label">Job</label>
                        <input type="text" class="form-control evt-readonly" value="{{ $order->work_id }}" readonly>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="evt-label">WO Qty</label>
                        <input type="text" class="form-control evt-readonly" value="{{ (int) ($order->group_wo_qty ?? 0) }}" readonly>
                    </div>
                </div>

                <div class="row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label class="evt-label">Qty. to check</label>
                        <input type="text" class="form-control evt-readonly" value="{{ ucfirst((string) ($order->sampling_check ?? 'Normal')) }}" readonly>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="evt-label">Sampling</label>
                        <input type="text" class="form-control evt-readonly" value="{{ $sampling }}" readonly>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="evt-label">No.Ops</label>
                        <input type="text" class="form-control evt-readonly" value="{{ $ops }}" readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-2">
                        <label class="evt-label">Inspection Note</label>
                        <textarea class="form-control evt-readonly evt-note-view" readonly>{{ (string) ($order->inspection_note ?? $order->notes ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="packet-head mb-2">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('qa.faisummary.pdf', $order->id) }}" target="_blank" class="evt-doc-icon evt-doc-btn mr-2" id="btnPrintPacket" title="Open/Print PDF">
                            <i class="fas fa-file-alt"></i>
                        </a>
                        <div>
                            <div class="font-weight-bold">FAI/IPI Inspection Packet Report</div>
                            <small class="text-muted">Resumen</small>
                        </div>
                    </div>
                </div>

                <div class="table-responsive packet-table-wrap">
                    <table class="table table-sm mb-0 packet-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Op</th>
                                <th>FAI</th>
                                <th>NP FAI</th>
                                <th>IPI</th>
                                <th>NP IPI</th>
                                <th>Done</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($statusRows as $r)
                                <tr>
                                    <td>
                                        @if($r['state'] === 'ok')
                                            <span class="pkt-state pkt-ok"><i class="fas fa-check"></i></span>
                                        @elseif($r['state'] === 'warn')
                                            <span class="pkt-state pkt-warn"><i class="fas fa-exclamation-triangle"></i></span>
                                        @else
                                            <span class="pkt-state pkt-pending"><i class="fas fa-minus"></i></span>
                                        @endif
                                    </td>
                                    <td class="font-weight-bold">{{ $r['label'] }}</td>
                                    <td><span class="pkt-pill">FAI {{ $r['fai_pass'] }}/{{ $r['fai_req'] }}</span></td>
                                    <td>{{ $r['fai_np'] }}</td>
                                    <td><span class="pkt-pill pkt-pill-ipi">IPI {{ $r['ipi_pass'] }}/{{ $r['ipi_req'] }}</span></td>
                                    <td>{{ $r['ipi_np'] }}</td>
                                    <td>{{ $r['done'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No operations</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="row mt-2">
                    <div class="col-6">
                        <div class="donut-card">
                            <div class="donut-title">FAI</div>
                            <div class="erp-donut" style="--pct: {{ $faiPct }}; --tone: #22c55e;">
                                <div class="erp-donut-center">{{ $faiPct }}%</div>
                            </div>
                            <div class="donut-sub">Pass {{ $faiPassTotal }} / Req {{ $faiReqTotal }}</div>
                            <div class="donut-sub text-danger">No Pass: {{ $faiFailTotal }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="donut-card">
                            <div class="donut-title">IPI</div>
                            <div class="erp-donut" style="--pct: {{ $ipiPct }}; --tone: #0ea5e9;">
                                <div class="erp-donut-center">{{ $ipiPct }}%</div>
                            </div>
                            <div class="donut-sub">Pass {{ $ipiPassTotal }} / Req {{ $ipiReqTotal }}</div>
                            <div class="donut-sub text-danger">No Pass: {{ $ipiFailTotal }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive evt-table-wrap mt-3">
            <table class="table table-sm table-hover mb-0 evt-table" id="orderEventsTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Operation</th>
                        <th>Operator</th>
                        <th>Results</th>
                        <th>SB/IS</th>
                        <th>Observation</th>
                        <th>Station</th>
                        <th>Method</th>
                        <th>Inspector</th>
                        <th>Qty Insp</th>
                        <th>Qty Process</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $e)
                        @php
                            $res = strtolower(trim((string) $e->results));
                            if (in_array($res, ['nopass', 'no_pass', 'fail'], true)) $res = 'no pass';
                            $isPass = $res === 'pass';
                        @endphp
                        <tr>
                            <td>{{ $e->date ? \Illuminate\Support\Carbon::parse($e->date)->format('m/d/Y H:i') : '' }}</td>
                            <td>{{ strtoupper((string) $e->insp_type) }}</td>
                            <td>{{ $e->operation }}</td>
                            <td>{{ $e->operator }}</td>
                            <td>
                                <span class="badge {{ $isPass ? 'badge-success' : 'badge-danger' }}">
                                    {{ $isPass ? 'Pass' : 'No Pass' }}
                                </span>
                            </td>
                            <td>{{ $e->sb_is }}</td>
                            <td class="evt-obs">{{ $e->observation }}</td>
                            <td>{{ $e->station }}</td>
                            <td>{{ $e->method }}</td>
                            <td>{{ $e->inspector }}</td>
                            <td>{{ (int) ($e->qty_pcs ?? 0) }}</td>
                            <td>{{ (int) ($e->qty_process ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted py-3">No events for this order.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection

@section('css')
<style>
    .modal-header-like {
        background: #f8fafc;
        border-bottom: 1px solid rgba(15,23,42,.08);
        border-radius: 12px 12px 0 0;
        padding: .65rem .9rem;
    }
    .evt-title-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        border: 1px solid rgba(59,130,246,.28);
        background: rgba(59,130,246,.12);
        color: #0d6efd;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .evt-doc-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: #eef2f7;
        border: 1px solid #d5dbe3;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .evt-doc-btn {
        border: 1px solid #d5dbe3;
        cursor: pointer;
    }
    .evt-doc-btn:hover {
        background: #e2e8f0;
    }
    .evt-label {
        font-size: .78rem;
        text-transform: uppercase;
        font-weight: 700;
        color: #334155;
        margin-bottom: 0.18rem;
    }
    .evt-readonly {
        background: #f1f5f9;
        border: 1px solid #d5dbe3;
        font-weight: 600;
        height: 38px;
    }
    .evt-note-view {
        min-height: 72px;
        height: auto;
        resize: vertical;
        white-space: pre-wrap;
    }
    .packet-head {
        border-bottom: 1px solid rgba(15,23,42,.08);
        padding-bottom: .35rem;
    }
    .packet-table-wrap {
        border: 1px solid #d5dbe3;
        border-radius: 10px;
        overflow: auto;
    }
    .packet-table thead th {
        background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
        color: #0f172a;
        font-size: .78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .02em;
        white-space: nowrap;
    }
    .packet-table tbody td {
        font-size: .82rem;
        vertical-align: middle;
        white-space: nowrap;
    }
    .pkt-state {
        width: 28px;
        height: 24px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #d5dbe3;
        background: #f8fafc;
    }
    .pkt-ok { color: #16a34a; border-color: rgba(22,163,74,.45); background: rgba(22,163,74,.12); }
    .pkt-warn { color: #b45309; border-color: rgba(245,158,11,.5); background: rgba(245,158,11,.14); }
    .pkt-pending { color: #64748b; }
    .pkt-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 2px 10px;
        border-radius: 999px;
        border: 1px solid rgba(34,197,94,.45);
        background: rgba(34,197,94,.12);
        color: #14532d;
        font-weight: 700;
        font-size: .78rem;
    }
    .pkt-pill-ipi {
        border-color: rgba(14,165,233,.45);
        background: rgba(14,165,233,.12);
        color: #0c4a6e;
    }
    .donut-card {
        border: 1px solid #dbe2ea;
        border-radius: 10px;
        background: #f8fafc;
        padding: .4rem;
        text-align: center;
    }
    .donut-title {
        font-weight: 800;
        font-size: .8rem;
        color: #0f172a;
        margin-bottom: .25rem;
        letter-spacing: .03em;
    }
    .erp-donut {
        --pct: 0;
        --tone: #22c55e;
        width: 86px;
        height: 86px;
        margin: 0 auto .3rem;
        border-radius: 50%;
        background: conic-gradient(var(--tone) calc(var(--pct) * 1%), #e2e8f0 0);
        display: grid;
        place-items: center;
    }
    .erp-donut-center {
        width: 62px;
        height: 62px;
        border-radius: 50%;
        background: #fff;
        border: 1px solid #dbe2ea;
        display: grid;
        place-items: center;
        font-weight: 800;
        color: #0f172a;
        font-size: .82rem;
    }
    .donut-sub {
        font-size: .72rem;
        color: #334155;
        line-height: 1.15;
    }

    .evt-table-wrap {
        border: 1px solid #d5dbe3;
        border-radius: 10px;
        overflow: auto;
    }
    .evt-table thead th {
        background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
        color: #0f172a;
        font-weight: 800;
        font-size: .83rem;
        text-transform: uppercase;
        letter-spacing: .02em;
        border-bottom: 1px solid rgba(15, 23, 42, 0.12);
        white-space: nowrap;
    }
    .evt-table tbody td {
        font-size: .84rem;
        vertical-align: middle;
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
    }
    .evt-table tbody tr:nth-child(even) {
        background: rgba(248, 250, 252, 0.9);
    }
    .evt-obs {
        min-width: 220px;
        white-space: normal;
    }
    .evt-notes-wrap {
        border: 1px solid #dbe2ea;
        border-radius: 8px;
        overflow: auto;
    }
    .evt-notes-table thead th {
        background: #f1f5f9;
        color: #0f172a;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .02em;
        white-space: nowrap;
    }
    .evt-notes-table tbody td {
        font-size: .82rem;
        vertical-align: top;
        border-top: 1px solid rgba(15, 23, 42, 0.06);
    }
    .evt-note-cell {
        min-width: 260px;
        white-space: normal;
        word-break: break-word;
    }

    .btn-erp {
        border-radius: 8px;
        font-weight: 700;
        border: 1px solid #d5dbe3;
        background: #f8fafc;
        color: #1f2937;
    }
    .btn-erp-danger i { color: #dc2626; }
    .btn-erp-secondary i { color: #334155; }

    @media print {
        @page {
            size: landscape;
            margin: 5mm;
        }

        html, body {
            background: #fff !important;
            color: #000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            zoom: 78%;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        /* Ocultar navegación y acciones */
        .main-sidebar,
        .main-header,
        .main-footer,
        .content-header,
        .nav-tabs,
        .btn,
        a.btn {
            display: none !important;
        }

        .content-wrapper,
        .content {
            margin: 0 !important;
            padding: 0 !important;
            min-height: auto !important;
            overflow: visible !important;
            background: #fff !important;
        }

        .print-report-head {
            display: block !important;
            margin-bottom: 6px;
        }
        .prh-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }
        .prh-title {
            font-size: 14pt;
            font-weight: 800;
            letter-spacing: .04em;
            color: #0f172a;
        }
        .prh-sub {
            font-size: 8.5pt;
            color: #475569;
            margin-top: 1px;
        }
        .prh-meta {
            text-align: right;
            font-size: 8pt;
            line-height: 1.2;
            color: #111827;
            white-space: nowrap;
        }
        .prh-line {
            margin-top: 4px;
            border-bottom: 1px solid #94a3b8;
        }

        .container-fluid,
        .container,
        .row,
        .col,
        [class*="col-"] {
            width: 100% !important;
            max-width: 100% !important;
        }

        /* Top area layout fijo 60/40 para que se vea acomodado */
        .card-body > .row:first-child {
            display: flex !important;
            flex-wrap: nowrap !important;
            gap: 8px !important;
            align-items: flex-start !important;
        }
        .card-body > .row:first-child > .col-lg-7 {
            flex: 0 0 60% !important;
            max-width: 60% !important;
            width: 60% !important;
            padding-right: 4px !important;
        }
        .card-body > .row:first-child > .col-lg-5 {
            flex: 0 0 40% !important;
            max-width: 40% !important;
            width: 40% !important;
            padding-left: 4px !important;
        }

        .card,
        .packet-table-wrap,
        .evt-table-wrap {
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
            break-inside: avoid;
            page-break-inside: avoid;
            border-radius: 0 !important;
        }

        .modal-header-like {
            border-radius: 0 !important;
            padding: .4rem .55rem !important;
            border-bottom: 1px solid #cbd5e1 !important;
            background: #fff !important;
        }

        .modal-header-like h5 {
            font-size: 13pt !important;
            margin: 0 !important;
        }
        .modal-header-like small {
            font-size: 8.5pt !important;
        }

        .evt-table-wrap,
        .packet-table-wrap {
            overflow: visible !important;
            max-height: none !important;
        }

        .evt-table thead th,
        .packet-table thead th {
            position: static !important;
            background: #f1f5f9 !important;
            color: #0f172a !important;
            font-size: 8.6pt !important;
            padding: 3px 5px !important;
            border-top: 1px solid #cbd5e1 !important;
            border-bottom: 1px solid #cbd5e1 !important;
        }
        .evt-table tbody td,
        .packet-table tbody td {
            font-size: 7.9pt !important;
            padding: 2px 5px !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }

        .evt-readonly,
        .evt-note-view {
            background: #f8fafc !important;
            border-color: #cbd5e1 !important;
            height: 26px !important;
            min-height: 26px !important;
            font-size: 8pt !important;
            padding: 2px 5px !important;
        }
        .evt-note-view {
            min-height: 36px !important;
        }

        .donut-card {
            border-color: #cbd5e1 !important;
            background: #fff !important;
            padding: 4px !important;
        }
        .erp-donut {
            width: 62px !important;
            height: 62px !important;
            margin-bottom: 2px !important;
        }
        .erp-donut-center {
            width: 44px !important;
            height: 44px !important;
            font-size: 7.8pt !important;
        }
        .donut-title {
            font-size: 8.4pt !important;
            margin-bottom: 2px !important;
        }
        .donut-sub {
            font-size: 7.6pt !important;
            line-height: 1.05 !important;
        }

        .evt-label {
            font-size: 7.8pt !important;
            margin-bottom: 1px !important;
            color: #334155 !important;
        }

        /* Tabla principal: evitar cortes raros */
        .evt-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .evt-table th,
        .evt-table td {
            word-break: break-word !important;
        }
        .evt-obs {
            min-width: 0 !important;
            max-width: none !important;
        }

        .card-body {
            padding: 4px !important;
        }
    }
</style>
@endsection

@section('js')
<script>
    (function () {
        const doPrint = () => window.print();
        const btnPrint = document.getElementById('btnPrintOrderEvents');
        if (btnPrint) btnPrint.addEventListener('click', doPrint);
    })();
</script>
@endsection
