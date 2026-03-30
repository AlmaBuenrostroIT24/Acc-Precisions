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
    $inspectionCompleted = collect($statusRows)->every(function ($r) {
        return (int) ($r['fai_pass'] ?? 0) >= (int) ($r['fai_req'] ?? 0)
            && (int) ($r['ipi_pass'] ?? 0) >= (int) ($r['ipi_req'] ?? 0);
    });
    $completedOnLabel = $order->inspection_endate
        ? \Illuminate\Support\Carbon::parse($order->inspection_endate)->format('m/d/Y H:i')
        : 'Pending';
    $lastInspectionLabel = $events->first()?->date
        ? \Illuminate\Support\Carbon::parse($events->first()->date)->format('m/d/Y H:i')
        : 'No events';
    $inspectionStatusLabel = \Illuminate\Support\Str::of((string) ($order->status_inspection ?? 'pending'))
        ->replace('_', ' ')
        ->title();
    $inspectionUpdatedLabel = $order->updated_at
        ? \Illuminate\Support\Carbon::parse($order->updated_at)->format('m/d/Y H:i')
        : 'N/A';
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

<div class="card shadow-sm mb-3 evt-page-card">
    <div class="modal-header-like d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <span class="evt-title-icon mr-2"><i class="fas fa-clipboard-check"></i></span>
            <div>
                <h5 class="mb-0">Inspection Process</h5>
                <small class="text-muted">Capture and track FAI / IPI inspections</small>
            </div>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-erp btn-erp-primary mr-2" id="btnAddInspectionTop">
                <i class="fas fa-plus mr-1"></i> Inspection
            </button>
            <button type="button" class="btn btn-sm btn-erp btn-erp-danger mr-2" id="btnPrintOrderEvents">
                <i class="fas fa-print mr-1"></i> Print
            </button>
            <a href="{{ route('faisummary.completed') }}" class="btn btn-sm btn-erp btn-erp-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card-body p-2 p-md-3 evt-main-body">
        <div class="row evt-order-band">
            <div class="col-lg-8">
                <div class="evt-left-panel">
                    <div class="row">
                        <div class="col-xl-3 col-md-4 col-sm-6 mb-2">
                        <label class="evt-label">Inspector</label>
                        <input type="text" class="form-control evt-readonly" value="{{ $inspectorName }}" readonly>
                        </div>
                        <div class="col-xl-5 col-md-8 col-sm-6 mb-2">
                        <label class="evt-label">Part# Rev.</label>
                        <input type="text" class="form-control evt-readonly" value="{{ $partRev }}" readonly>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-2">
                        <label class="evt-label">Job</label>
                        <input type="text" class="form-control evt-readonly" value="{{ $order->work_id }}" readonly>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-2">
                        <label class="evt-label">WO Qty</label>
                        <input type="text" class="form-control evt-readonly" value="{{ (int) ($order->group_wo_qty ?? 0) }}" readonly>
                        </div>
                        <div class="col-xl-4 col-md-4 col-sm-6 mb-2">
                        <label class="evt-label">Qty. to check</label>
                        <input type="text" class="form-control evt-readonly" value="{{ ucfirst((string) ($order->sampling_check ?? 'Normal')) }}" readonly>
                        </div>
                        <div class="col-xl-2 col-md-3 col-sm-6 mb-2">
                        <label class="evt-label">Sampling</label>
                        <input type="text" class="form-control evt-readonly" value="{{ $sampling }}" readonly>
                        </div>
                        <div class="col-xl-2 col-md-3 col-sm-6 mb-2">
                        <label class="evt-label">No.Ops</label>
                        <input type="text" class="form-control evt-readonly" value="{{ $ops }}" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-0">
                            <label class="evt-label">Inspection Note</label>
                            <textarea class="form-control evt-readonly evt-note-view" readonly>{{ (string) ($order->inspection_note ?? $order->notes ?? '') }}</textarea>
                        </div>
                    </div>

                    @if(count($statusRows))
                        <div class="ops-journey ops-journey-embedded mt-2 {{ count($statusRows) <= 1 ? 'ops-journey-single' : '' }}">
                            <div class="ops-journey-head">
                                <span class="ops-journey-title">Inspection Journey</span>
                            </div>
                            <div class="ops-journey-track">
                                <div class="ops-simple-track">
                                    <div class="ops-simple-steps {{ count($statusRows) >= 5 ? 'ops-simple-steps-compact' : '' }} {{ count($statusRows) >= 7 ? 'ops-simple-steps-dense' : '' }}" style="grid-template-columns: repeat({{ max(1, count($statusRows) + 1) }}, minmax(0, 1fr));">
                                        @foreach($statusRows as $r)
                                            @php
                                                $faiState = $r['fai_pass'] >= $r['fai_req'] ? 'ok' : ($r['fai_np'] > 0 ? 'warn' : 'pending');
                                                $ipiState = $r['ipi_pass'] >= $r['ipi_req'] ? 'ok' : ($r['ipi_np'] > 0 ? 'warn' : 'pending');
                                                $opState = ($faiState === 'ok' && $ipiState === 'ok') ? 'ok' : (($faiState === 'warn' || $ipiState === 'warn') ? 'warn' : 'pending');
                                            @endphp
                                            <div class="ops-simple-step">
                                                <div class="ops-simple-head">
                                                    <div class="ops-simple-label">{{ $r['label'] }}</div>
                                                    <div class="ops-grid-node ops-grid-node-{{ $opState }}">
                                                        <i class="fas {{ $opState === 'ok' ? 'fa-check' : ($opState === 'warn' ? 'fa-times' : 'fa-minus') }}"></i>
                                                    </div>
                                                </div>
                                                <div class="ops-simple-body">
                                                    <div class="ops-simple-row">
                                                        <span class="ops-mini-pill ops-mini-pill-fai">FAI</span>
                                                        <span class="ops-simple-metric">{{ $r['fai_pass'] }}/{{ $r['fai_req'] }}</span>
                                                        <div class="ops-grid-mini">
                                                            @for($i = 1; $i <= max(1, (int) $r['fai_req']); $i++)
                                                                @php
                                                                    $faiPass = (int) $r['fai_pass'];
                                                                    $faiFail = (int) $r['fai_np'];
                                                                    $faiClass = $i <= $faiPass ? 'is-done' : (($i > $faiPass && $i <= ($faiPass + $faiFail)) ? 'is-fail' : '');
                                                                @endphp
                                                                <span class="ops-chain-done {{ $faiClass }}"><i class="fas fa-check"></i></span>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                    <div class="ops-simple-row">
                                                        <span class="ops-mini-pill ops-mini-pill-ipi">IPI</span>
                                                        <span class="ops-simple-metric">{{ $r['ipi_pass'] }}/{{ $r['ipi_req'] }}</span>
                                                        <div class="ops-grid-mini ops-grid-mini-ipi">
                                                            @for($i = 1; $i <= max(1, (int) $r['ipi_req']); $i++)
                                                                @php
                                                                    $ipiPass = (int) $r['ipi_pass'];
                                                                    $ipiFail = (int) $r['ipi_np'];
                                                                    $ipiClass = $i <= $ipiPass ? 'is-done' : (($i > $ipiPass && $i <= ($ipiPass + $ipiFail)) ? 'is-fail' : '');
                                                                @endphp
                                                                <span class="ops-chain-done {{ $ipiClass }}"><i class="fas fa-check"></i></span>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        <div class="ops-simple-step ops-simple-step-final">
                                            <div class="ops-simple-head">
                                                <div class="ops-journey-final-circle-only {{ $inspectionCompleted ? 'ops-journey-final-circle-only-complete' : 'ops-journey-final-circle-only-pending' }}"
                                                    title="Status: {{ $inspectionStatusLabel }} | Updated: {{ $inspectionUpdatedLabel }} | Completed On: {{ $completedOnLabel }} | Last Inspection: {{ $lastInspectionLabel }}">
                                                    <i class="fas {{ $inspectionCompleted ? 'fa-check-circle' : ($inspectionStatusLabel === 'In Progress' ? 'fa-hourglass-half' : 'fa-pause-circle') }}"></i>
                                                </div>
                                                <div class="ops-simple-label">{{ $inspectionStatusLabel }}</div>
                                            </div>
                                            <div class="ops-simple-body">
                                                <div class="ops-final-status-date">Updated {{ $inspectionUpdatedLabel }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-lg-4 evt-side-col">
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

                <div class="row mt-2 evt-donut-row">
                    <div class="col-12 col-md-6 col-lg-6 mb-2">
                        <div class="donut-card">
                            <div class="donut-title">FAI</div>
                            <div class="erp-donut" style="--pct: {{ $faiPct }}; --tone: #22c55e;">
                                <div class="erp-donut-center">{{ $faiPct }}%</div>
                            </div>
                            <div class="donut-sub">Pass {{ $faiPassTotal }} / Req {{ $faiReqTotal }}</div>
                            <div class="donut-sub text-danger">No Pass: {{ $faiFailTotal }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-6">
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

        <div class="table-responsive evt-table-wrap mt-2">
            <table class="table table-sm table-hover mb-0 evt-table" id="orderEventsTable">
                <thead>
                    <tr>
                        <th class="col-date">Date</th>
                        <th class="col-type">Type</th>
                        <th class="col-operation">Operation</th>
                        <th class="col-operator">Operator</th>
                        <th class="col-results">Results</th>
                        <th class="col-sbis">SB/IS</th>
                        <th class="col-observation">Observation</th>
                        <th class="col-station">Station</th>
                        <th class="col-method">Method</th>
                        <th class="col-qty-insp">Qty Insp</th>
                        <th class="col-qty-process">Qty Process</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $e)
                        @php
                            $res = strtolower(trim((string) $e->results));
                            if (in_array($res, ['nopass', 'no_pass', 'fail'], true)) $res = 'no pass';
                            $isPass = $res === 'pass';
                            $fmtDate = $e->date ? \Illuminate\Support\Carbon::parse($e->date)->format('m/d/Y H:i') : '';
                            $typeLabel = strtoupper((string) $e->insp_type);
                            $resultLabel = $isPass ? 'Pass' : 'No Pass';
                            $eventAt = $e->created_at ? \Illuminate\Support\Carbon::parse($e->created_at) : ($e->date ? \Illuminate\Support\Carbon::parse($e->date) : null);
                            $completedAt = $order->inspection_endate ? \Illuminate\Support\Carbon::parse($order->inspection_endate) : null;
                            $addedAfterCompleted = $eventAt && $completedAt && $eventAt->greaterThan($completedAt);
                        @endphp
                        <tr class="{{ $addedAfterCompleted ? 'evt-row-post-complete' : '' }}"
                            data-id="{{ $e->id }}"
                            data-date="{{ $e->date ? \Illuminate\Support\Carbon::parse($e->date)->format('Y-m-d\\TH:i') : '' }}"
                            data-insp-type="{{ strtoupper((string) $e->insp_type) }}"
                            data-operation="{{ $e->operation }}"
                            data-operator="{{ $e->operator }}"
                            data-results="{{ $resultLabel }}"
                            data-sb-is="{{ $e->sb_is }}"
                            data-observation="{{ $e->observation }}"
                            data-station="{{ $e->station }}"
                            data-method="{{ $e->method }}"
                            data-qty-pcs="{{ (int) ($e->qty_pcs ?? 0) }}"
                            data-qty-process="{{ (int) ($e->qty_process ?? 0) }}"
                            data-post-complete="{{ $addedAfterCompleted ? '1' : '0' }}">
                            <td>
                                <div class="evt-field evt-field-text {{ $addedAfterCompleted ? 'evt-field-post-complete' : '' }}">
                                    <span>{{ $fmtDate }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="evt-field evt-field-select">{{ $typeLabel }}</div>
                            </td>
                            <td>
                                <div class="evt-field evt-field-text">{{ $e->operation }}</div>
                            </td>
                            <td>
                                <div class="evt-field evt-field-text">{{ $e->operator }}</div>
                            </td>
                            <td>
                                <div class="evt-field evt-field-select">{{ $resultLabel }}</div>
                            </td>
                            <td>
                                <div class="evt-field evt-field-text">{{ $e->sb_is }}</div>
                            </td>
                            <td class="evt-obs">
                                <div class="evt-field evt-field-textarea">{{ $e->observation }}</div>
                            </td>
                            <td>
                                <div class="evt-field evt-field-text">{{ $e->station }}</div>
                            </td>
                            <td>
                                <div class="evt-field evt-field-select">{{ $e->method }}</div>
                            </td>
                            <td>
                                <div class="evt-field evt-field-number">{{ (int) ($e->qty_pcs ?? 0) }}</div>
                            </td>
                            <td>
                                <div class="evt-field evt-field-number">{{ (int) ($e->qty_process ?? 0) }}</div>
                            </td>
                            <td>
                                <div class="evt-actions">
                                    <span class="evt-action-btn evt-action-ok" title="Done">
                                        <i class="fas fa-check"></i>
                                    </span>
                                    <button type="button"
                                        class="evt-action-btn evt-action-edit {{ $addedAfterCompleted ? '' : 'evt-action-disabled' }}"
                                        title="{{ $addedAfterCompleted ? 'Edit' : 'Locked' }}"
                                        data-row-id="{{ $e->id }}"
                                        {{ $addedAfterCompleted ? '' : 'disabled' }}>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button"
                                        class="evt-action-btn evt-action-delete {{ $addedAfterCompleted ? '' : 'evt-action-disabled' }}"
                                        title="{{ $addedAfterCompleted ? 'Delete' : 'Locked' }}"
                                        data-row-id="{{ $e->id }}"
                                        {{ $addedAfterCompleted ? '' : 'disabled' }}>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
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
        background: linear-gradient(180deg, #f8fbff 0%, #f1f6fc 100%);
        border-bottom: 1px solid rgba(15,23,42,.08);
        border-radius: 14px 14px 0 0;
        padding: .55rem .8rem;
    }
    .evt-page-card {
        border-radius: 14px;
        border: 1px solid #dbe4ee;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }
    .evt-title-icon {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        border: 1px solid rgba(59,130,246,.26);
        background: linear-gradient(180deg, rgba(59,130,246,.18) 0%, rgba(59,130,246,.12) 100%);
        color: #0d6efd;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.65);
    }
    .modal-header-like h5 {
        font-size: 1.12rem;
        font-weight: 500;
        color: #1f2937;
    }
    .modal-header-like small {
        font-size: .84rem;
        color: #64748b !important;
    }
    .evt-doc-icon {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: linear-gradient(180deg, #eef3f8 0%, #e7edf5 100%);
        border: 1px solid #d5dde7;
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
    .evt-main-body {
        padding-top: .7rem !important;
    }
    .evt-main-col,
    .evt-side-col {
        display: flex;
        flex-direction: column;
        gap: .35rem;
    }
    .evt-top-grid {
        row-gap: .35rem;
        align-items: start;
    }
    .evt-order-band {
        row-gap: .2rem;
        margin-bottom: .15rem;
    }
    .evt-left-panel {
        border: 1px solid #d8e0ea;
        border-radius: 16px;
        background: linear-gradient(180deg, #fcfdff 0%, #f7fafc 100%);
        padding: .7rem .85rem .8rem;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.8);
    }
    .evt-secondary-band {
        row-gap: .25rem;
        align-items: start;
        margin-bottom: .1rem;
    }
    .evt-secondary-band > .evt-side-col {
        padding-top: 0;
    }
    .evt-chart-col .row {
        row-gap: .25rem;
    }
    .evt-donut-row {
        row-gap: .25rem;
        margin-top: .1rem;
    }
    .evt-report-col {
        min-width: 0;
    }
    .evt-label {
        font-size: .78rem;
        text-transform: uppercase;
        font-weight: 900;
        letter-spacing: .04em;
        color: #223048;
        margin-bottom: .24rem;
    }
    .evt-readonly {
        background: linear-gradient(180deg, #edf2f8 0%, #e5ebf3 100%);
        border: 1px solid #d2dbe6;
        font-weight: 600;
        min-height: 40px;
        height: 40px;
        border-radius: 10px;
        color: #334155;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.55);
        font-size: .98rem;
    }
    .evt-note-view {
        min-height: 50px;
        height: auto;
        resize: vertical;
        white-space: pre-wrap;
        border-radius: 12px;
        background: linear-gradient(180deg, #edf3fa 0%, #e4ecf6 100%);
    }
    .packet-head {
        border-bottom: 1px solid rgba(15,23,42,.08);
        padding: 0 0 .34rem;
        margin-top: 0;
    }
    .packet-table-wrap {
        border: 1px solid #d8e0ea;
        border-radius: 14px;
        overflow: auto;
        background: #fff;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.04);
        margin-bottom: 0;
    }
    .packet-table thead th {
        background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
        color: #0f172a;
        font-size: .84rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .02em;
        white-space: nowrap;
    }
    .packet-table tbody td {
        font-size: .9rem;
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
        font-size: .84rem;
    }
    .pkt-pill-ipi {
        border-color: rgba(14,165,233,.45);
        background: rgba(14,165,233,.12);
        color: #0c4a6e;
    }
    .ops-journey {
        position: relative;
        border: 1px solid #d8e0ea;
        border-radius: 16px;
        background: linear-gradient(180deg, #fcfdff 0%, #f7fafc 100%);
        padding: .62rem .95rem .7rem;
        overflow: hidden;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.8);
    }
    .ops-journey-single {
        padding-bottom: .62rem;
    }
    .ops-journey-head {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: .75rem;
        margin-bottom: .5rem;
    }
    .ops-journey-title {
        font-size: .92rem;
        font-weight: 900;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: #334155;
    }
    .ops-legend {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
    }
    .ops-legend-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: .16rem .48rem;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .04em;
        border: 1px solid transparent;
    }
    .ops-legend-fai {
        color: #166534;
        background: rgba(34,197,94,.10);
        border-color: rgba(34,197,94,.24);
    }
    .ops-legend-ipi {
        color: #0c4a6e;
        background: rgba(14,165,233,.10);
        border-color: rgba(14,165,233,.24);
    }
    .ops-journey-track {
        display: block;
    }
    .ops-simple-track {
        position: relative;
        padding: .3rem .7rem .05rem .45rem;
        overflow-x: auto;
        overflow-y: hidden;
        scrollbar-width: thin;
    }
    .ops-simple-steps {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1.1rem;
        align-items: start;
        position: relative;
        min-width: max-content;
        padding-bottom: .2rem;
    }
    .ops-simple-steps-compact {
        gap: .72rem;
    }
    .ops-simple-steps-dense {
        gap: .45rem;
    }
    .ops-simple-step {
        position: relative;
        min-width: 180px;
        text-align: center;
        display: grid;
        justify-items: center;
        align-content: start;
    }
    .ops-simple-steps-compact .ops-simple-step {
        min-width: 156px;
    }
    .ops-simple-steps-dense .ops-simple-step {
        min-width: 138px;
    }
    .ops-simple-step:not(:last-child)::after {
        content: "";
        position: absolute;
        top: 45px;
        left: calc(50% + 20px);
        width: calc(100% - 40px);
        border-top: 3px dotted #93c5fd;
        opacity: .95;
    }
    .ops-simple-steps-compact .ops-simple-step:not(:last-child)::after {
        top: 39px;
        left: calc(50% + 17px);
        width: calc(100% - 34px);
    }
    .ops-simple-steps-dense .ops-simple-step:not(:last-child)::after {
        top: 37px;
        left: calc(50% + 15px);
        width: calc(100% - 30px);
        border-top-width: 2px;
    }
    .ops-simple-step:has(.ops-grid-node-ok):not(:last-child)::after {
        border-top-color: rgba(34,197,94,.55);
    }
    .ops-simple-step:has(.ops-grid-node-warn):not(:last-child)::after {
        border-top-color: rgba(245,158,11,.72);
    }
    .ops-simple-step-final:not(:last-child)::after {
        display: none;
    }
    .ops-simple-step-final {
        margin-top: 18px;
    }
    .ops-simple-steps-compact .ops-simple-step-final {
        margin-top: 14px;
    }
    .ops-simple-steps-dense .ops-simple-step-final {
        margin-top: 12px;
    }
    .ops-simple-head {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        gap: .34rem;
        position: relative;
        z-index: 1;
        padding: 0 .35rem;
        min-height: auto;
    }
    .ops-simple-label {
        font-size: .84rem;
        font-weight: 800;
        color: #1e293b;
        white-space: nowrap;
    }
    .ops-simple-steps-compact .ops-simple-label {
        font-size: .78rem;
    }
    .ops-simple-steps-dense .ops-simple-label {
        font-size: .74rem;
    }
    .ops-simple-body {
        display: grid;
        gap: .24rem;
        margin-top: .22rem;
        justify-items: center;
    }
    .ops-simple-steps-compact .ops-simple-body {
        gap: .18rem;
        margin-top: .28rem;
    }
    .ops-simple-steps-dense .ops-simple-body {
        gap: .14rem;
        margin-top: .22rem;
    }
    .ops-simple-row {
        display: flex;
        align-items: center;
        gap: .32rem;
        flex-wrap: wrap;
        justify-content: flex-start;
        width: 100%;
    }
    .ops-mini-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 18px;
        padding: 0 .42rem;
        border-radius: 999px;
        font-size: .62rem;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
        border: 1px solid transparent;
    }
    .ops-simple-steps-compact .ops-mini-pill {
        min-width: 24px;
        height: 16px;
        padding: 0 .32rem;
        font-size: .57rem;
    }
    .ops-simple-steps-dense .ops-mini-pill {
        min-width: 22px;
        height: 15px;
        padding: 0 .28rem;
        font-size: .54rem;
    }
    .ops-mini-pill-fai {
        color: #15803d;
        background: rgba(34,197,94,.10);
        border-color: rgba(34,197,94,.28);
    }
    .ops-mini-pill-ipi {
        color: #0369a1;
        background: rgba(14,165,233,.10);
        border-color: rgba(14,165,233,.28);
    }
    .ops-simple-metric {
        font-size: .68rem;
        font-weight: 900;
        color: #64748b;
        letter-spacing: .03em;
    }
    .ops-simple-steps-compact .ops-simple-metric {
        font-size: .62rem;
    }
    .ops-simple-steps-dense .ops-simple-metric {
        font-size: .58rem;
    }
    .ops-grid-node {
        width: 44px;
        height: 44px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #cbd5e1;
        background: transparent;
        font-size: 1.06rem;
        box-shadow: 0 3px 10px rgba(15,23,42,.06);
        vertical-align: top;
        position: relative;
        overflow: hidden;
    }
    .ops-simple-steps-compact .ops-grid-node {
        width: 36px;
        height: 36px;
        font-size: .92rem;
    }
    .ops-simple-steps-dense .ops-grid-node {
        width: 34px;
        height: 34px;
        font-size: .88rem;
        border-width: 2px;
    }
    .ops-simple-steps-compact .ops-grid-node::before {
        inset: 8px;
    }
    .ops-simple-steps-dense .ops-grid-node::before {
        inset: 7px;
    }
    .ops-grid-node::before {
        content: "";
        position: absolute;
        inset: 9px;
        border-radius: 999px;
        background: currentColor;
        opacity: .18;
    }
    .ops-grid-node i {
        position: relative;
        z-index: 1;
    }
    .ops-grid-node-ok {
        border-color: rgba(34,197,94,.7);
        color: #15803d;
        background: rgba(34,197,94,.12);
    }
    .ops-grid-node-warn {
        border-color: rgba(239,68,68,.72);
        color: #dc2626;
        background: rgba(239,68,68,.10);
    }
    .ops-grid-node-pending {
        border-color: rgba(148,163,184,.75);
        color: #64748b;
        background: rgba(148,163,184,.12);
    }
    .ops-grid-step:has(.ops-grid-node-ok):not(:last-child)::after {
        background: rgba(34,197,94,.6);
    }
    .ops-grid-mini {
        display: inline-flex;
        align-items: center;
        gap: .14rem;
        flex-wrap: wrap;
    }
    .ops-grid-mini-ipi {
        display: grid;
        grid-template-columns: repeat(5, 16px);
        gap: .14rem;
        justify-content: start;
        align-items: center;
    }
    .ops-chain-done {
        width: 16px;
        height: 16px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #cbd5e1;
        font-size: .52rem;
    }
    .ops-simple-steps-compact .ops-chain-done {
        width: 14px;
        height: 14px;
        font-size: .46rem;
    }
    .ops-simple-steps-compact .ops-grid-mini-ipi {
        grid-template-columns: repeat(5, 14px);
    }
    .ops-simple-steps-dense .ops-chain-done {
        width: 12px;
        height: 12px;
        font-size: .42rem;
    }
    .ops-simple-steps-dense .ops-grid-mini-ipi {
        grid-template-columns: repeat(5, 12px);
    }
    .ops-chain-done.is-done {
        border-color: rgba(34,197,94,.65);
        background: rgba(34,197,94,.12);
        color: #15803d;
    }
    .ops-chain-done.is-fail {
        border-color: rgba(239,68,68,.7);
        background: rgba(239,68,68,.12);
        color: #dc2626;
    }
    .ops-journey-final-circle-only {
        width: 58px;
        height: 58px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: 2px solid rgba(15,23,42,.10);
        box-shadow: 0 10px 22px rgba(15,23,42,.08);
        font-size: 1.22rem;
        margin-left: 0;
        flex: 0 0 auto;
        position: relative;
        overflow: hidden;
    }
    .ops-journey-final-circle-only::before {
        content: "";
        position: absolute;
        inset: 10px;
        border-radius: 999px;
        background: currentColor;
        opacity: .16;
    }
    .ops-journey-final-circle-only i {
        position: relative;
        z-index: 1;
    }
    .ops-journey-final-circle-only-complete {
        border-color: rgba(34,197,94,.45);
        color: #16a34a;
        background: rgba(34,197,94,.08);
    }
    .ops-journey-final-circle-only-pending {
        border-color: rgba(245,158,11,.45);
        color: #d97706;
        background: rgba(245,158,11,.08);
    }
    .ops-final-status-text {
        font-size: .7rem;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #334155;
        line-height: 1.1;
    }
    .ops-final-status-date {
        font-size: .62rem;
        font-weight: 700;
        color: #64748b;
        line-height: 1.1;
    }
    @media (max-width: 1399.98px) {
        .ops-simple-steps {
            padding-bottom: .3rem;
        }
        .ops-simple-step {
            min-width: 180px;
        }
        .ops-simple-step:not(:last-child)::after {
            width: calc(100% - 40px);
        }
    }
    .donut-card {
        border: 1px solid #d8e0ea;
        border-radius: 14px;
        background: #fbfdff;
        padding: .42rem .42rem;
        text-align: center;
        min-height: 142px;
    }
    .donut-title {
        font-weight: 800;
        font-size: .84rem;
        color: #0f172a;
        margin-bottom: .14rem;
        letter-spacing: .03em;
    }
    .erp-donut {
        --pct: 0;
        --tone: #22c55e;
        width: 92px;
        height: 92px;
        margin: 0 auto .28rem;
        border-radius: 50%;
        background: conic-gradient(var(--tone) calc(var(--pct) * 1%), #e2e8f0 0);
        display: grid;
        place-items: center;
    }
    .erp-donut-center {
        width: 66px;
        height: 66px;
        border-radius: 50%;
        background: #fff;
        border: 1px solid #dbe2ea;
        display: grid;
        place-items: center;
        font-weight: 800;
        color: #0f172a;
        font-size: .94rem;
    }
    .donut-sub {
        font-size: .76rem;
        color: #334155;
        line-height: 1.15;
    }

    .evt-table-wrap {
        border: 1px solid #d8e0ea;
        border-radius: 14px;
        overflow: auto;
        background: #fff;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    }
    .evt-table {
        table-layout: fixed;
        min-width: 1520px;
    }
    .evt-table thead th {
        background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
        color: #0f172a;
        font-weight: 800;
        font-size: .9rem;
        text-transform: uppercase;
        letter-spacing: .02em;
        border-bottom: 1px solid rgba(15, 23, 42, 0.12);
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 2;
    }
    .evt-table tbody td {
        font-size: .92rem;
        vertical-align: middle;
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        padding: .38rem .45rem;
    }
    .evt-table tbody tr:nth-child(even) td {
        background: rgba(248,250,252,.72);
    }
    .evt-table th.col-date,
    .evt-table td:nth-child(1) { width: 190px; min-width: 190px; }
    .evt-table th.col-type,
    .evt-table td:nth-child(2) { width: 92px; min-width: 92px; }
    .evt-table th.col-operation,
    .evt-table td:nth-child(3) { width: 108px; min-width: 108px; }
    .evt-table th.col-operator,
    .evt-table td:nth-child(4) { width: 108px; min-width: 108px; }
    .evt-table th.col-results,
    .evt-table td:nth-child(5) { width: 106px; min-width: 106px; }
    .evt-table th.col-sbis,
    .evt-table td:nth-child(6) { width: 240px; min-width: 240px; }
    .evt-table th.col-observation,
    .evt-table td:nth-child(7) { width: 240px; min-width: 240px; }
    .evt-table th.col-station,
    .evt-table td:nth-child(8) { width: 96px; min-width: 96px; }
    .evt-table th.col-method,
    .evt-table td:nth-child(9) { width: 132px; min-width: 132px; }
    .evt-table th.col-qty-insp,
    .evt-table td:nth-child(10) { width: 96px; min-width: 96px; }
    .evt-table th.col-qty-process,
    .evt-table td:nth-child(11) { width: 108px; min-width: 108px; }
    .evt-table th.col-actions,
    .evt-table td:nth-child(12) { width: 132px; min-width: 132px; }
    .evt-table tbody tr:nth-child(even) {
        background: rgba(248, 250, 252, 0.9);
    }
    .evt-obs {
        min-width: 260px;
        white-space: normal;
    }
    .evt-field {
        min-height: 37px;
        border-radius: 11px;
        border: 1px solid #d5dbe3;
        background: #eef3f8;
        color: #334155;
        display: flex;
        align-items: center;
        width: 100%;
        padding: .48rem .78rem;
        line-height: 1.15;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.55);
        font-size: .95rem;
    }
    .evt-field-post-complete {
        border-color: rgba(245, 158, 11, 0.28);
        box-shadow: inset 3px 0 0 rgba(245, 158, 11, 0.55), inset 0 1px 0 rgba(255,255,255,.55);
    }
    .evt-field-textarea {
        min-height: 37px;
        align-items: flex-start;
        white-space: normal;
        word-break: break-word;
    }
    .evt-field-select {
        position: relative;
        padding-right: 1.9rem;
        white-space: nowrap;
    }
    .evt-field-select::after {
        content: "";
        position: absolute;
        right: .8rem;
        top: 50%;
        width: .45rem;
        height: .45rem;
        border-right: 2px solid #475569;
        border-bottom: 2px solid #475569;
        transform: translateY(-65%) rotate(45deg);
        pointer-events: none;
    }
    .evt-field-number {
        justify-content: flex-start;
        min-width: 74px;
    }
    .evt-actions {
        display: flex;
        align-items: center;
        gap: .48rem;
        white-space: nowrap;
    }
    .evt-action-btn {
        width: 40px;
        height: 34px;
        border-radius: 9px;
        border: 1px solid #d5dbe3;
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 1px 0 rgba(15,23,42,.04);
        padding: 0;
    }
    .evt-action-btn:hover {
        background: #f8fafc;
    }
    .evt-action-ok i {
        color: #7ca68d;
    }
    .evt-action-edit i {
        color: #f59e0b;
    }
    .evt-action-delete i {
        color: #dc2626;
    }
    .evt-action-save i {
        color: #166534;
    }
    .evt-action-disabled {
        opacity: .45;
        cursor: not-allowed;
    }
    .evt-editing-row td {
        background: #f8fbff;
    }
    .evt-editing-row .evt-action-cancel i {
        color: #64748b;
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
        border-radius: 12px;
        font-weight: 700;
        border: 1px solid #d5dde7;
        background: #fff;
        color: #1f2937;
        min-height: 36px;
        padding: .4rem .88rem;
        box-shadow: 0 1px 0 rgba(15,23,42,.04);
        font-size: .96rem;
    }
    .btn-erp:hover {
        background: #f8fafc;
    }
    .btn-erp-primary i { color: #0d6efd; }
    .btn-erp-danger i { color: #dc2626; }
    .btn-erp-secondary i { color: #334155; }
    .evt-edit-control {
        width: 100%;
        height: 34px;
        min-height: 34px;
        border-radius: 12px;
        border: 1px solid #d5dbe3;
        background: #f8fafc;
        color: #334155;
        padding: .38rem .72rem;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.55);
        font-size: .96rem;
        font-weight: 500;
    }
    textarea.evt-edit-control {
        height: 34px;
        min-height: 34px;
        resize: none;
        overflow: hidden;
        line-height: 1.25;
        padding-top: .44rem;
        padding-bottom: .44rem;
    }
    .evt-edit-control:focus {
        outline: none;
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(59,130,246,.12);
        background: #fff;
    }
    .evt-edit-control[type="number"] {
        min-width: 74px;
    }
    .evt-draft-row td {
        background: #fff;
        padding-top: .3rem !important;
        padding-bottom: .3rem !important;
    }
    .evt-draft-row .evt-edit-control {
        background: #fff;
    }
    .evt-draft-row .evt-action-save i {
        color: #16a34a;
    }
    .evt-draft-row .evt-action-remove i {
        color: #dc2626;
    }
    .evt-draft-row .evt-edit-control[name="date"] {
        min-width: 178px;
    }
    .evt-editing-row .evt-edit-control[name="date"] {
        min-width: 178px;
    }
    .evt-draft-row .evt-edit-control[name="insp_type"] {
        min-width: 92px;
    }
    .evt-draft-row .evt-edit-control[name="operation"] {
        min-width: 112px;
    }
    .evt-draft-row .evt-edit-control[name="results"] {
        min-width: 104px;
    }
    .evt-draft-row .evt-edit-control[name="method"] {
        min-width: 138px;
    }
    .evt-draft-row .evt-edit-control[name="qty_pcs"],
    .evt-draft-row .evt-edit-control[name="qty_process"] {
        min-width: 72px;
        max-width: 72px;
    }
    .evt-draft-row .evt-actions {
        justify-content: flex-end;
    }

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

        /* Top area layout de dos columnas debajo de la banda superior */
        .card-body > .row:first-child {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 6px 0 !important;
            align-items: flex-start !important;
        }
        .card-body > .row:first-child > [class*="col-"] {
            flex: 0 0 100% !important;
            max-width: 100% !important;
            width: 100% !important;
            padding-right: 0 !important;
            padding-left: 0 !important;
        }
        .card-body > .row:nth-child(2) {
            display: flex !important;
            flex-wrap: nowrap !important;
            gap: 8px !important;
            align-items: flex-start !important;
        }
        .card-body > .row:nth-child(2) > .col-lg-8 {
            flex: 0 0 64% !important;
            max-width: 64% !important;
            width: 64% !important;
            padding-right: 4px !important;
        }
        .card-body > .row:nth-child(2) > .col-lg-4 {
            flex: 0 0 36% !important;
            max-width: 36% !important;
            width: 36% !important;
            padding-left: 4px !important;
            padding-right: 4px !important;
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
        const orderId = @json($order->id);
        const inspectorName = @json($inspectorName);
        const totalOps = @json($ops);
        const csrf = @json(csrf_token());
        const methods = ['Manual', 'Vmm/Manual', 'Visual', 'Vmm', 'Keyence', 'Keyence/Manual'];
        let operatorOptions = [];
        let stationOptions = [];
        const operatorListId = 'evtOperatorOptions';
        const stationListId = 'evtStationOptions';

        const doPrint = () => window.print();
        const btnPrint = document.getElementById('btnPrintOrderEvents');
        if (btnPrint) btnPrint.addEventListener('click', doPrint);

        const btnAdd = document.getElementById('btnAddInspectionTop');
        const tbody = document.querySelector('#orderEventsTable tbody');

        const opLabel = (i) => {
            if (i === 1) return '1st Op';
            if (i === 2) return '2nd Op';
            if (i === 3) return '3rd Op';
            return `${i}th Op`;
        };

        const ipiRequired = Math.max(0, (parseInt(@json($sampling), 10) || 0) - 1);

        const buildOptions = (rows, textKey) => rows.map(r => {
            const value = (r?.[textKey] || '').toString().trim();
            return value ? `<option value="${value.replace(/"/g, '&quot;')}">${value}</option>` : '';
        }).join('');

        const ensureDatalists = () => {
            let operatorList = document.getElementById(operatorListId);
            if (!operatorList) {
                operatorList = document.createElement('datalist');
                operatorList.id = operatorListId;
                document.body.appendChild(operatorList);
            }
            operatorList.innerHTML = buildOptions(operatorOptions, 'operator');

            let stationList = document.getElementById(stationListId);
            if (!stationList) {
                stationList = document.createElement('datalist');
                stationList.id = stationListId;
                document.body.appendChild(stationList);
            }
            stationList.innerHTML = buildOptions(stationOptions, 'station');
        };

        const loadLookupData = () => {
            const operatorReq = $.getJSON(`/operators/by-order/${orderId}`).then((rows) => {
                operatorOptions = Array.isArray(rows) ? rows : [];
            });
            const stationReq = $.getJSON(`/stations/by-order/${orderId}`).then((rows) => {
                stationOptions = Array.isArray(rows) ? rows : [];
            });
            return $.when(operatorReq, stationReq).always(() => {
                ensureDatalists();
            });
        };

        const getCompletedCounts = () => {
            const faiSum = new Map();
            const ipiSum = new Map();

            $(tbody).find('tr[data-id]').each(function () {
                const $row = $(this);
                const type = String($row.data('inspType') || '').toUpperCase();
                const op = String($row.data('operation') || '').trim();
                const results = String($row.data('results') || '').trim().toLowerCase();
                if (!op || results !== 'pass') return;

                const qty = parseInt($row.data('qtyPcs') || 0, 10) || 0;

                if (type === 'FAI') {
                    const faiQty = Math.min(1, qty || 0);
                    const spillToIpi = Math.max(0, (qty || 0) - faiQty);
                    faiSum.set(op, (faiSum.get(op) || 0) + faiQty);
                    if (spillToIpi > 0) {
                        ipiSum.set(op, (ipiSum.get(op) || 0) + spillToIpi);
                    }
                }

                if (type === 'IPI') {
                    ipiSum.set(op, (ipiSum.get(op) || 0) + qty);
                }
            });

            return { faiSum, ipiSum };
        };

        const getNextInspectionPair = () => {
            const ops = parseInt(totalOps || 0, 10) || 0;
            if (ops < 1) return null;

            const { faiSum, ipiSum } = getCompletedCounts();
            for (let i = 1; i <= ops; i++) {
                const op = opLabel(i);
                if ((faiSum.get(op) || 0) < 1) {
                    return { type: 'FAI', op };
                }
                if ((ipiSum.get(op) || 0) < ipiRequired) {
                    return { type: 'IPI', op };
                }
            }
            return null;
        };

        const buildOperationControl = (inspType = 'FAI', preferredOp = null) => {
            if (parseInt(totalOps || 0, 10) > 0) {
                const { faiSum, ipiSum } = getCompletedCounts();
                let labels = [];
                for (let i = 1; i <= totalOps; i++) labels.push(opLabel(i));

                if (preferredOp && labels.includes(preferredOp)) {
                    labels = [preferredOp].concat(labels.filter(v => v !== preferredOp));
                }

                const isFai = String(inspType).toUpperCase() === 'FAI';
                let options = '';
                labels.forEach((label) => {
                    const done = isFai
                        ? ((faiSum.get(label) || 0) >= 1)
                        : ((ipiSum.get(label) || 0) >= ipiRequired);
                    const text = done ? `${label} (done)` : label;
                    const selected = preferredOp && preferredOp === label ? 'selected' : '';
                    options += `<option value="${label}" ${selected}>${text}</option>`;
                });
                return `<select class="evt-edit-control" name="operation">${options}</select>`;
            }
            return `<input type="text" class="evt-edit-control" name="operation" value="">`;
        };

        const today = () => {
            const d = new Date();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            const hh = String(d.getHours()).padStart(2, '0');
            const mi = String(d.getMinutes()).padStart(2, '0');
            return `${d.getFullYear()}-${mm}-${dd}T${hh}:${mi}`;
        };

        const ensureNoDraft = () => {
            const existing = tbody.querySelector('.evt-draft-row, .evt-editing-row');
            if (existing) {
                const first = existing.querySelector('input, select, textarea');
                if (first) first.focus();
                return false;
            }
            return true;
        };

        const syncQtyProcess = ($row) => {
            const type = ($row.find('[name="insp_type"]').val() || '').toUpperCase();
            const $qty = $row.find('[name="qty_process"]');
            if (type === 'FAI') {
                $qty.prop('disabled', false).removeClass('d-none');
                if (!$qty.val()) $qty.val('1');
            } else {
                $qty.val('').prop('disabled', true).addClass('d-none');
            }
        };

        const autoSizeTextarea = (el) => {
            if (!el) return;
            el.style.height = '34px';
            el.style.height = `${Math.max(34, el.scrollHeight)}px`;
        };

        const autoSizeRowTextareas = ($row) => {
            $row.find('textarea.evt-edit-control').each(function () {
                autoSizeTextarea(this);
            });
        };

        const buildDraftRow = () => {
            const suggestion = getNextInspectionPair();
            const defaultType = suggestion?.type || 'FAI';
            const preferredOp = suggestion?.op || null;
            const opControl = buildOperationControl(defaultType, preferredOp);
            const operatorInput = `<input type="text" class="evt-edit-control" name="operator" list="${operatorListId}" autocomplete="off">`;
            const stationInput = `<input type="text" class="evt-edit-control" name="station" list="${stationListId}" autocomplete="off">`;
            const methodSelect = `<select class="evt-edit-control" name="method">${methods.map(m => `<option value="${m}">${m}</option>`).join('')}</select>`;
            const html = `
                <tr class="evt-draft-row">
                    <td><input type="datetime-local" class="evt-edit-control" name="date" value="${today()}"></td>
                    <td>
                        <select class="evt-edit-control" name="insp_type">
                            <option value="FAI" ${defaultType === 'FAI' ? 'selected' : ''}>FAI</option>
                            <option value="IPI" ${defaultType === 'IPI' ? 'selected' : ''}>IPI</option>
                        </select>
                    </td>
                    <td>${opControl}</td>
                    <td>${operatorInput}</td>
                    <td>
                        <select class="evt-edit-control" name="results">
                            <option value="pass">Pass</option>
                            <option value="no pass">No Pass</option>
                        </select>
                    </td>
                    <td><textarea class="evt-edit-control" name="sb_is" rows="1"></textarea></td>
                    <td><textarea class="evt-edit-control" name="observation" rows="1"></textarea></td>
                    <td>${stationInput}</td>
                    <td>${methodSelect}</td>
                    <td><input type="number" class="evt-edit-control" name="qty_pcs" min="1" value="1"></td>
                    <td><input type="number" class="evt-edit-control" name="qty_process" min="0" value="1"></td>
                    <td>
                        <div class="evt-actions">
                            <button type="button" class="evt-action-btn evt-action-save" title="Save">
                                <i class="fas fa-save"></i>
                            </button>
                            <button type="button" class="evt-action-btn evt-action-remove" title="Remove">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            tbody.insertAdjacentHTML('afterbegin', html);
            const $row = $(tbody).find('.evt-draft-row').first();
            syncQtyProcess($row);
            autoSizeRowTextareas($row);
            $row.find('[name="insp_type"]').on('change', function () {
                const selectedType = ($(this).val() || 'FAI').toString().trim();
                const nextSuggestion = getNextInspectionPair();
                const preferredForType = nextSuggestion && nextSuggestion.type === selectedType ? nextSuggestion.op : null;
                const newControl = buildOperationControl(selectedType, preferredForType);
                $row.find('[name="operation"]').closest('td').html(newControl);
                syncQtyProcess($row);
            });
            const first = $row.find('input, select').get(0);
            if (first) first.focus();
        };

        const saveDraftRow = ($row) => {
            const rowId = ($row.data('id') || '').toString().trim();
            const payload = {
                order_schedule_id: orderId,
                date: ($row.find('[name="date"]').val() || '').trim(),
                insp_type: ($row.find('[name="insp_type"]').val() || '').trim(),
                operation: ($row.find('[name="operation"]').val() || '').trim(),
                operator: ($row.find('[name="operator"]').val() || '').trim(),
                results: ($row.find('[name="results"]').val() || '').trim(),
                sb_is: ($row.find('[name="sb_is"]').val() || '').trim(),
                observation: ($row.find('[name="observation"]').val() || '').trim(),
                station: ($row.find('[name="station"]').val() || '').trim(),
                method: ($row.find('[name="method"]').val() || '').trim(),
                inspector: inspectorName,
                qty_pcs: ($row.find('[name="qty_pcs"]').val() || '1').trim(),
                qty_process: ($row.find('[name="qty_process"]').prop('disabled') ? '' : ($row.find('[name="qty_process"]').val() || '0').trim())
            };
            if (rowId) {
                payload.id = rowId;
            }

            if (!payload.date || !payload.insp_type || !payload.operation || !payload.operator || !payload.results || !payload.method) {
                Swal.fire('Missing data', 'Complete the required inspection fields, including operator.', 'warning');
                return;
            }

            $.ajax({
                url: '/qa/faisummary/store-single',
                type: 'POST',
                data: {
                    ...payload,
                    _token: csrf
                }
            }).done(function () {
                window.location.reload();
            }).fail(function (xhr) {
                const errors = xhr?.responseJSON?.errors || null;
                const firstError = errors ? Object.values(errors).flat()[0] : null;
                const msg = firstError || xhr?.responseJSON?.message || xhr?.responseJSON?.error || 'Could not save inspection.';
                Swal.fire('Error', msg, 'error');
            });
        };

        if (btnAdd) {
            btnAdd.addEventListener('click', function () {
                if (!ensureNoDraft()) return;
                loadLookupData().always(function () {
                    buildDraftRow();
                });
            });
        }

        $(document).on('click', '.evt-action-remove', function () {
            $(this).closest('tr').remove();
        });

        $(document).on('click', '.evt-action-save', function () {
            saveDraftRow($(this).closest('tr'));
        });

        const renderEditableRow = ($row) => {
            const rowId = ($row.data('id') || '').toString().trim();
            const date = ($row.data('date') || '').toString().trim();
            const inspType = ($row.data('inspType') || 'FAI').toString().trim();
            const operation = ($row.data('operation') || '').toString().trim();
            const operator = ($row.data('operator') || '').toString().trim();
            const results = (($row.data('results') || 'Pass').toString().trim().toLowerCase() === 'no pass') ? 'no pass' : 'pass';
            const sbIs = ($row.data('sbIs') || '').toString().trim();
            const observation = ($row.data('observation') || '').toString().trim();
            const station = ($row.data('station') || '').toString().trim();
            const method = ($row.data('method') || 'Manual').toString().trim();
            const qtyPcs = ($row.data('qtyPcs') || 1).toString().trim();
            const qtyProcess = ($row.data('qtyProcess') || '').toString().trim();

            const operatorInput = `<input type="text" class="evt-edit-control" name="operator" list="${operatorListId}" value="${operator.replace(/"/g, '&quot;')}" autocomplete="off">`;
            const stationInput = `<input type="text" class="evt-edit-control" name="station" list="${stationListId}" value="${station.replace(/"/g, '&quot;')}" autocomplete="off">`;
            const methodSelect = `<select class="evt-edit-control" name="method">${methods.map(m => `<option value="${m}" ${m === method ? 'selected' : ''}>${m}</option>`).join('')}</select>`;
            const opControl = parseInt(totalOps || 0, 10) > 0
                ? `<select class="evt-edit-control" name="operation">${Array.from({ length: totalOps }, (_, idx) => {
                    const label = opLabel(idx + 1);
                    return `<option value="${label}" ${label === operation ? 'selected' : ''}>${label}</option>`;
                }).join('')}</select>`
                : `<input type="text" class="evt-edit-control" name="operation" value="${operation.replace(/"/g, '&quot;')}">`;

            $row.addClass('evt-editing-row').removeClass('evt-row-post-complete');
            $row.html(`
                <td><input type="datetime-local" class="evt-edit-control" name="date" value="${date}"></td>
                <td>
                    <select class="evt-edit-control" name="insp_type">
                        <option value="FAI" ${inspType === 'FAI' ? 'selected' : ''}>FAI</option>
                        <option value="IPI" ${inspType === 'IPI' ? 'selected' : ''}>IPI</option>
                    </select>
                </td>
                <td>${opControl}</td>
                <td>${operatorInput}</td>
                <td>
                    <select class="evt-edit-control" name="results">
                        <option value="pass" ${results === 'pass' ? 'selected' : ''}>Pass</option>
                        <option value="no pass" ${results === 'no pass' ? 'selected' : ''}>No Pass</option>
                    </select>
                </td>
                <td><textarea class="evt-edit-control" name="sb_is" rows="1">${sbIs}</textarea></td>
                <td><textarea class="evt-edit-control" name="observation" rows="1">${observation}</textarea></td>
                <td>${stationInput}</td>
                <td>${methodSelect}</td>
                <td><input type="number" class="evt-edit-control" name="qty_pcs" min="1" value="${qtyPcs || '1'}"></td>
                <td><input type="number" class="evt-edit-control" name="qty_process" min="0" value="${qtyProcess}"></td>
                <td>
                    <div class="evt-actions">
                        <button type="button" class="evt-action-btn evt-action-save" title="Save">
                            <i class="fas fa-save"></i>
                        </button>
                        <button type="button" class="evt-action-btn evt-action-cancel" title="Cancel">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </td>
            `);
            $row.attr('data-id', rowId);
            syncQtyProcess($row);
            autoSizeRowTextareas($row);
            $row.find('[name="insp_type"]').on('change', function () {
                syncQtyProcess($row);
            });
        };

        $(document).on('click', '.evt-action-edit:not(.evt-action-disabled)', function () {
            if (!ensureNoDraft()) return;
            const $row = $(this).closest('tr');
            loadLookupData().always(function () {
                renderEditableRow($row);
            });
        });

        $(document).on('click', '.evt-action-cancel', function () {
            window.location.reload();
        });

        $(document).on('input', 'textarea.evt-edit-control[name="sb_is"], textarea.evt-edit-control[name="observation"]', function () {
            autoSizeTextarea(this);
        });

        $(document).on('click', '.evt-action-delete:not(.evt-action-disabled)', function () {
            const rowId = ($(this).data('rowId') || '').toString().trim();
            if (!rowId) return;
            Swal.fire({
                title: 'Delete inspection?',
                text: 'This post-complete inspection will be removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: `/qa/faisummary/delete/${rowId}`,
                    type: 'POST',
                    data: {
                        _token: csrf,
                        _method: 'DELETE'
                    }
                }).done(function () {
                    window.location.reload();
                }).fail(function (xhr) {
                    const msg = xhr?.responseJSON?.error || 'Could not delete inspection.';
                    Swal.fire('Error', msg, 'error');
                });
            });
        });
    })();
</script>
@endsection
