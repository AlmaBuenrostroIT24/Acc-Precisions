@extends('adminlte::page')

@section('title', 'Costing Edit')

@section('css')
    <style>
        .costing-edit-shell,
        .costing-files-card {
            border: 1px solid #cfe0f5;
            background: #f8fbff;
            box-shadow: 0 1px 2px rgba(16, 24, 40, 0.06);
        }

        .costing-layout {
            display: grid;
            grid-template-columns: minmax(0, 8fr) minmax(320px, 4fr);
            gap: 16px;
        }

        .costing-edit-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .costing-header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .costing-edit-title,
        .costing-files-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .costing-top-card {
            border: 1px solid #dbe7f5;
            border-radius: 4px;
            background: #fff;
            padding: 14px 16px;
            margin-bottom: 18px;
        }

        .costing-top-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 36px;
        }

        .costing-info-col {
            display: grid;
            gap: 12px;
        }

        .costing-info-row {
            display: grid;
            grid-template-columns: 140px minmax(0, 1fr);
            gap: 10px;
            align-items: start;
        }

        .costing-info-label {
            font-size: 0.88rem;
            font-weight: 800;
            color: #111827;
            padding-top: 2px;
        }

        .costing-info-value {
            font-size: 0.98rem;
            color: #111827;
            min-height: 20px;
            word-break: break-word;
        }

        .costing-inline-input {
            width: 100%;
            border: 0;
            border-bottom: 1px solid #dbe7f5;
            outline: 0;
            background: transparent;
            color: #111827;
            padding: 0 0 2px;
            font-size: 0.98rem;
        }

        .costing-inline-textarea {
            min-height: 72px;
            resize: vertical;
        }

        .costing-table-wrap {
            border: 1px solid #bcd1ec;
            background: #fff;
            overflow-x: auto;
        }

        .costing-form-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .costing-form-table td,
        .costing-form-table th {
            border: 1px solid #bcd1ec;
            padding: 6px 8px;
            font-size: 0.94rem;
            color: #111827;
            vertical-align: middle;
            line-height: 1.2;
        }

        .costing-form-table td {
            overflow-wrap: anywhere;
        }

        .costing-form-table th:nth-child(1),
        .costing-form-table td:nth-child(1) {
            width: 16%;
        }

        .costing-form-table th:nth-child(2),
        .costing-form-table td:nth-child(2) {
            width: 16%;
        }

        .costing-form-table th:nth-child(3),
        .costing-form-table td:nth-child(3),
        .costing-form-table th:nth-child(4),
        .costing-form-table td:nth-child(4),
        .costing-form-table th:nth-child(5),
        .costing-form-table td:nth-child(5),
        .costing-form-table th:nth-child(6),
        .costing-form-table td:nth-child(6),
        .costing-form-table th:nth-child(7),
        .costing-form-table td:nth-child(7) {
            width: 14%;
            text-align: center;
        }

        .costing-form-table thead th {
            background: #f8fbff;
            color: #111827;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-size: 0.84rem;
            text-align: center;
            line-height: 1.15;
        }

        .costing-form-control {
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
            font-size: 0.94rem;
            color: #111827;
            padding: 0;
            line-height: 1.2;
        }

        .costing-form-control:focus,
        .costing-inline-input:focus {
            background: #eff6ff;
        }

        .costing-table-total-label {
            font-weight: 800;
            color: #111827;
            text-transform: uppercase;
        }

        .costing-form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .costing-summary-grid {
            display: grid;
            gap: 0;
            margin-top: 0;
            background: #fff;
        }

        .costing-summary-bottom {
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr);
            gap: 0;
            background: #fff;
            align-items: stretch;
        }

        .costing-summary-left,
        .costing-summary-right {
            min-width: 0;
        }

        .costing-summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .costing-summary-left .costing-summary-table {
            border: 1px solid #bcd1ec;
            border-top: 0;
        }

        .costing-table-total-row td {
            border: 1px solid #bcd1ec;
            padding: 6px 8px;
            font-size: 0.94rem;
            color: #111827;
            vertical-align: middle;
            line-height: 1.2;
            background: #fff;
        }

        .costing-summary-right {
            padding-left: 0;
        }

        .costing-summary-right .costing-summary-table {
            border: 1px solid #bcd1ec;
            height: 100%;
        }

        .costing-summary-table td,
        .costing-summary-table th {
            border: 1px solid #bcd1ec;
            padding: 6px 8px;
            font-size: 0.94rem;
            color: #111827;
            vertical-align: middle;
            line-height: 1.2;
        }

        .costing-summary-table tr:first-child td,
        .costing-summary-table tr:first-child th {
            border-top: 0;
        }

        .costing-summary-table td:first-child,
        .costing-summary-table th:first-child {
            border-left: 0;
        }

        .costing-summary-table td:last-child,
        .costing-summary-table th:last-child {
            border-right: 0;
        }

        .costing-summary-label {
            font-weight: 800;
            text-transform: uppercase;
            text-align: right;
            white-space: nowrap;
        }

        .costing-summary-value {
            font-weight: 700;
        }

        .costing-cell-positive {
            background: #dcfce7 !important;
        }

        .costing-cell-negative {
            background: #fee2e2 !important;
        }

        .costing-cell-warning {
            background: #fef3c7 !important;
        }

        .costing-summary-center {
            text-align: center;
        }

        .costing-summary-right-text {
            text-align: right;
        }

        .costing-summary-currency {
            width: 44px;
            text-align: center;
            font-weight: 700;
            white-space: nowrap;
        }

        .costing-summary-title {
            font-weight: 900;
            text-transform: uppercase;
            text-align: center;
            font-size: 1.28rem;
            letter-spacing: 0.03em;
        }

        .costing-notes-box {
            min-height: 108px;
            vertical-align: top !important;
        }

        .costing-notes-label {
            display: block;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .costing-file-box {
            border: 1px dashed #93c5fd;
            background: #fff;
            border-radius: 6px;
            padding: 14px;
            margin-top: 12px;
        }

        .costing-file-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .costing-file-label {
            font-size: 0.88rem;
            font-weight: 800;
            color: #111827;
            text-transform: uppercase;
            margin: 0;
        }

        .costing-file-help {
            font-size: 0.92rem;
            color: #64748b;
            margin-top: 8px;
        }

        .costing-file-input {
            display: block;
            width: auto;
            max-width: 220px;
            font-size: 0.88rem;
            color: #334155;
        }

        .costing-file-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .costing-file-preview {
            width: 100%;
            height: 280px;
            border: 1px solid #dbe7f5;
            border-radius: 6px;
            background: #fff;
            margin-top: 12px;
        }

        .costing-file-thumb {
            display: block;
            margin-top: 12px;
            border: 1px solid #dbe7f5;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            text-decoration: none;
            color: inherit;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .costing-file-thumb:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
            text-decoration: none;
            color: inherit;
        }

        .costing-file-thumb-frame {
            width: 100%;
            height: 390px;
            border: 0;
            display: block;
            pointer-events: none;
            background: #fff;
        }

        .costing-file-thumb-meta {
            display: none;
        }

        .costing-row-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-bottom: 10px;
        }

        .costing-duration-input {
            display: none;
        }

        .costing-duration-trigger {
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
            color: #111827;
            font-size: 0.92rem;
            line-height: 1.2;
            padding: 0;
            text-align: center;
            cursor: pointer;
        }

        .costing-duration-trigger:focus,
        .costing-duration-trigger:hover {
            background: #eff6ff;
        }

        .costing-duration-trigger[disabled] {
            cursor: default;
        }

        .costing-duration-panel {
            position: fixed;
            min-width: 148px;
            border: 1px solid #bcd1ec;
            border-radius: 6px;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.14);
            padding: 10px;
            z-index: 2050;
            display: none;
        }

        .costing-duration-panel.is-open {
            display: block;
        }

        .costing-duration-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .costing-duration-group {
            display: grid;
            gap: 4px;
        }

        .costing-duration-group label {
            margin: 0;
            font-size: 0.72rem;
            font-weight: 800;
            color: #111827;
            text-transform: uppercase;
            text-align: center;
        }

        .costing-duration-group select {
            width: 100%;
            border: 1px solid #dbe7f5;
            border-radius: 4px;
            outline: 0;
            background: #fff;
            color: #111827;
            font-size: 0.9rem;
            line-height: 1.2;
            padding: 4px 6px;
        }

        .costing-duration-group select:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 2px rgba(147, 197, 253, 0.18);
        }

        .costing-summary-readonly {
            background: transparent !important;
            pointer-events: none;
        }

        .costing-locked-cell {
            color: #111827;
            font-weight: 500;
        }

        .costing-save-status {
            min-height: 20px;
            font-size: 0.88rem;
            color: #475569;
        }

        .costing-logs-modal .modal-content {
            border: 1px solid #cfe0f5;
            border-radius: 10px;
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.16);
            overflow: hidden;
        }

        .costing-logs-modal .modal-header {
            background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
            border-bottom: 1px solid #dbe7f5;
            padding: 14px 18px;
        }

        .costing-logs-modal .modal-title {
            font-size: 1rem;
            font-weight: 800;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .costing-logs-modal .modal-body {
            background: #f8fbff;
            padding: 16px;
        }

        .costing-pdf-modal .modal-dialog {
            max-width: 1100px;
        }

        .costing-pdf-modal .modal-content {
            border: 1px solid #cfe0f5;
            border-radius: 10px;
            overflow: hidden;
        }

        .costing-pdf-modal .modal-header {
            background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
            border-bottom: 1px solid #dbe7f5;
        }

        .costing-pdf-modal .modal-body {
            padding: 0;
            background: #e2e8f0;
        }

        .costing-pdf-frame {
            width: 100%;
            height: 78vh;
            border: 0;
            display: block;
            background: #fff;
        }

        @media (max-width: 991.98px) {
            .costing-layout {
                grid-template-columns: 1fr;
            }

            .costing-top-grid {
                grid-template-columns: 1fr;
            }

            .costing-info-row {
                grid-template-columns: 120px minmax(0, 1fr);
            }

            .costing-summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@stop

@section('content')
    @php
        $formatHours = function ($value) {
            $value = (float) ($value ?? 0);
            $totalSeconds = (int) round($value * 3600);
            $hours = intdiv($totalSeconds, 3600);
            $minutes = intdiv($totalSeconds % 3600, 60);
            $seconds = $totalSeconds % 60;

            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        };

        $operationRows = $operations->values();
        if ($operationRows->isEmpty()) {
            $operationRows = collect([(object) [
                'name_operation' => '',
                'resource_name' => '',
                'time_programming' => 0,
                'time_setup' => 0,
                'runtime_pcs' => 0,
                'runtime_total' => 0,
                'total_time_operation' => 0,
            ]]);
        }
        $sumProgramming = $operations->sum(fn ($operation) => (float) ($operation->time_programming ?? 0));
        $sumSetup = $operations->sum(fn ($operation) => (float) ($operation->time_setup ?? 0));
        $sumRuntimePcs = $operations->sum(fn ($operation) => (float) ($operation->runtime_pcs ?? 0));
        $sumRuntimeTotal = $operations->sum(fn ($operation) => (float) ($operation->runtime_total ?? 0));
        $sumTotalTimeOperation = $operations->sum(fn ($operation) => (float) ($operation->total_time_operation ?? 0));
    @endphp

    <div class="row mx-0">
        <div class="col-12 px-0">
            <div class="costing-layout">
                <div class="card costing-edit-shell mb-0">
                    <div class="card-body">
                        <div class="costing-edit-header">
                            <div class="costing-edit-title">Quote</div>
                            <div class="costing-header-actions">
                                <button type="button" class="btn btn-outline-info btn-sm" id="openCostingLogs">History</button>
                                <a href="{{ route('costing') }}" class="btn btn-outline-secondary btn-sm">Back</a>
                            </div>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success py-2 px-3">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger py-2 px-3">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('costing.update', $order) }}" id="costingForm" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="costing-top-card">
                                <div class="costing-top-grid">
                                    <div class="costing-info-col">
                                        <div class="costing-info-row">
                                            <div class="costing-info-label">Customer:</div>
                                            <div class="costing-info-value">{{ $order->costumer ?: 'N/A' }}</div>
                                        </div>
                                        <div class="costing-info-row">
                                            <div class="costing-info-label">WO#:</div>
                                            <div class="costing-info-value">{{ $order->work_id ?: 'N/A' }}</div>
                                        </div>
                                        <div class="costing-info-row">
                                            <div class="costing-info-label">WO Qty:</div>
                                            <div class="costing-info-value">{{ $order->wo_qty ?? 'N/A' }}</div>
                                        </div>
                                        <div class="costing-info-row">
                                            <div class="costing-info-label">CO:</div>
                                            <div class="costing-info-value">{{ $order->co ?: 'N/A' }}</div>
                                        </div>
                                        <div class="costing-info-row">
                                            <div class="costing-info-label">Cust PO:</div>
                                            <div class="costing-info-value">{{ $order->cust_po ?: 'N/A' }}</div>
                                        </div>
                                        <div class="costing-info-row">
                                            <div class="costing-info-label">Qty:</div>
                                            <div class="costing-info-value">{{ $order->qty ?? 'N/A' }}</div>
                                        </div>
                                        <div class="costing-info-row">
                                            <div class="costing-info-label">Part Description:</div>
                                            <div class="costing-info-value">{{ $order->Part_description ?: 'N/A' }}</div>
                                        </div>
                                    </div>

                                    <div class="costing-info-col">
                                        <div class="costing-info-row">
                                            <div class="costing-info-label">PN:</div>
                                            <div class="costing-info-value">{{ $order->PN ?: 'N/A' }}</div>
                                        </div>
                                        <div class="costing-info-row">
                                            <div class="costing-info-label">Revision:</div>
                                            <div class="costing-info-value">{{ $order->revision ?: 'N/A' }}</div>
                                        </div>
                                        <div class="costing-info-row">
                                            <div class="costing-info-label">Date:</div>
                                            <div class="costing-info-value">{{ optional($order->due_date)->format('Y-m-d') ?: 'N/A' }}</div>
                                        </div>
                                        <div class="costing-info-row">
                                            <div class="costing-info-label">Material Type:</div>
                                            <div class="costing-info-value">
                                                <input class="costing-inline-input" type="text" name="type_material" value="{{ old('type_material', $costing->type_material ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="costing-info-row">
                                            <div class="costing-info-label">Quote Notes:</div>
                                            <div class="costing-info-value">
                                                <textarea class="costing-inline-input costing-inline-textarea" name="notes">{{ old('notes', $costing->notes ?? '') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="costing-row-actions">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addOperationRow">Add Row</button>
                                <button type="button" class="btn btn-outline-danger btn-sm" id="removeOperationRow">Remove Row</button>
                            </div>

                            <div class="costing-table-wrap">
                                <table class="costing-form-table">
                                    <thead>
                                        <tr>
                                            <th>OP Description</th>
                                            <th>Resource ID</th>
                                            <th>Programming</th>
                                            <th>Setup</th>
                                            <th>Run Time * Pcs</th>
                                            <th>Run Time Total</th>
                                            <th>Total Time Hrs</th>
                                        </tr>
                                    </thead>
                                    <tbody id="operationsTableBody">
                                        @foreach($operationRows as $index => $operation)
                                            <tr class="operation-row">
                                                <td>
                                                    @if($index === 0)
                                                        <input
                                                            class="costing-form-control"
                                                            type="hidden"
                                                            name="operations[{{ $index }}][name_operation]"
                                                            value="Traveler Process"
                                                        >
                                                        <div class="costing-locked-cell">Traveler Process</div>
                                                    @else
                                                        <input
                                                            class="costing-form-control"
                                                            type="text"
                                                            name="operations[{{ $index }}][name_operation]"
                                                            value="{{ old("operations.$index.name_operation", $operation->name_operation ?? 'OP' . $index) }}"
                                                        >
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($index === 0)
                                                        <input
                                                            class="costing-form-control"
                                                            type="hidden"
                                                            name="operations[{{ $index }}][resource_name]"
                                                            value=""
                                                        >
                                                        <div class="costing-locked-cell">-----</div>
                                                    @else
                                                        <input
                                                            class="costing-form-control"
                                                            type="text"
                                                            name="operations[{{ $index }}][resource_name]"
                                                            value="{{ old("operations.$index.resource_name", $operation->resource_name ?? '') }}"
                                                        >
                                                    @endif
                                                </td>
                                                <td>
                                                    <input
                                                        class="costing-form-control costing-duration-input js-duration-field"
                                                        type="hidden"
                                                        name="operations[{{ $index }}][time_programming]"
                                                        value="{{ old("operations.$index.time_programming", $formatHours($operation->time_programming ?? 0)) }}"
                                                    >
                                                </td>
                                                <td>
                                                    <input
                                                        class="costing-form-control costing-duration-input js-duration-field"
                                                        type="hidden"
                                                        name="operations[{{ $index }}][time_setup]"
                                                        value="{{ old("operations.$index.time_setup", $formatHours($operation->time_setup ?? 0)) }}"
                                                    >
                                                </td>
                                                <td>
                                                    <input
                                                        class="costing-form-control costing-duration-input js-duration-field"
                                                        type="hidden"
                                                        name="operations[{{ $index }}][runtime_pcs]"
                                                        value="{{ old("operations.$index.runtime_pcs", $formatHours($operation->runtime_pcs ?? 0)) }}"
                                                    >
                                                </td>
                                                <td>
                                                    <input
                                                        class="costing-form-control costing-duration-input js-duration-field js-runtime-total-field"
                                                        type="hidden"
                                                        name="operations[{{ $index }}][runtime_total]"
                                                        value="{{ old("operations.$index.runtime_total", $formatHours($operation->runtime_total ?? 0)) }}"
                                                    >
                                                </td>
                                                <td>
                                                    <input
                                                        class="costing-form-control costing-duration-input js-duration-field js-row-total-time"
                                                        type="hidden"
                                                        name="operations[{{ $index }}][total_time_operation]"
                                                        value="{{ old("operations.$index.total_time_operation", $index === 0 ? '1:00:00' : $formatHours($operation->total_time_operation ?? 0)) }}"
                                                    >
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="costing-table-total-row">
                                            <td class="costing-summary-label" colspan="2">Total Times:</td>
                                            <td class="costing-summary-center">
                                                <input class="costing-form-control costing-summary-center costing-summary-readonly" type="text" name="sum_programming" value="{{ old('sum_programming', $formatHours($sumProgramming)) }}" readonly>
                                            </td>
                                            <td class="costing-summary-center">
                                                <input class="costing-form-control costing-summary-center costing-summary-readonly" type="text" name="sum_setup" value="{{ old('sum_setup', $formatHours($sumSetup)) }}" readonly>
                                            </td>
                                            <td class="costing-summary-center">
                                                <input class="costing-form-control costing-summary-center costing-summary-readonly" type="text" name="sum_runtime_pcs" value="{{ old('sum_runtime_pcs', $formatHours($sumRuntimePcs)) }}" readonly>
                                            </td>
                                            <td class="costing-summary-center">
                                                <input class="costing-form-control costing-summary-center costing-summary-readonly" type="text" name="sum_runtime_total" value="{{ old('sum_runtime_total', $formatHours($sumRuntimeTotal)) }}" readonly>
                                            </td>
                                            <td class="costing-summary-center costing-summary-value">
                                                <input class="costing-form-control costing-summary-center costing-summary-value costing-summary-readonly" type="text" name="total_time_order" value="{{ old('total_time_order', $formatHours($costing->total_time_order ?? $sumTotalTimeOperation)) }}" readonly>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="costing-summary-grid">
                                <div class="costing-summary-bottom">
                                    <div class="costing-summary-left">
                                        <table class="costing-summary-table">
                                            <tr>
                                                <td class="costing-summary-label">Total Labor:</td>
                                                <td class="costing-summary-currency">$</td>
                                                <td colspan="4">
                                                    <input class="costing-form-control costing-summary-right-text costing-summary-readonly" type="text" inputmode="decimal" name="total_labor" value="{{ old('total_labor', $costing->total_labor ?? 4970.00) }}" readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="costing-summary-label">Total Materials:</td>
                                                <td class="costing-summary-currency">$</td>
                                                <td colspan="4">
                                                    <input class="costing-form-control costing-summary-right-text" type="text" inputmode="decimal" name="total_material" value="{{ old('total_material', $costing && $costing->total_material > 0 ? $costing->total_material : '') }}">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="costing-summary-label">Total Outsource Process:</td>
                                                <td class="costing-summary-currency">$</td>
                                                <td colspan="4">
                                                    <input class="costing-form-control costing-summary-right-text" type="text" name="total_outsource" value="{{ old('total_outsource', $costing && $costing->total_outsource > 0 ? $costing->total_outsource : '') }}">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="costing-notes-box">
                                                    <span class="costing-notes-label">Notes:</span>
                                                    <textarea class="costing-form-control" name="notes_bottom" rows="3">{{ old('notes_bottom', $costing->notes ?? 'Programming to 5 Axes') }}</textarea>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <div class="costing-summary-right">
                                        <table class="costing-summary-table">
                                            <tr>
                                                <td colspan="4" class="costing-summary-title">Final Comparation</td>
                                            </tr>
                                            <tr>
                                                <td class="costing-summary-label">Sale Price:</td>
                                                <td class="costing-summary-center">$</td>
                                                <td colspan="2">
                                                    <input class="costing-form-control costing-summary-right-text costing-summary-value" type="text" inputmode="decimal" name="sale_price" value="{{ old('sale_price', $costing && $costing->sale_price > 0 ? $costing->sale_price : '') }}">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="costing-summary-label">Grandtotal Cost:</td>
                                                <td class="costing-summary-center">$</td>
                                                <td colspan="2">
                                                    <input class="costing-form-control costing-summary-right-text costing-summary-value costing-summary-readonly" type="text" inputmode="decimal" name="grandtotal_cost" value="{{ old('grandtotal_cost', $costing->grandtotal_cost ?? 5210.00) }}" readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="costing-summary-label">Cost Pcs:</td>
                                                <td class="costing-summary-center">$</td>
                                                <td colspan="2" class="costing-cell-warning">
                                                    <input class="costing-form-control costing-summary-right-text costing-summary-value costing-summary-readonly" type="text" inputmode="decimal" name="price_pcs" value="{{ old('price_pcs', $costing && $costing->price_pcs > 0 ? $costing->price_pcs : '') }}" readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="costing-summary-label">Difference</td>
                                                <td class="costing-summary-center">$</td>
                                                <td colspan="2">
                                                    <input class="costing-form-control costing-summary-right-text costing-summary-value costing-summary-readonly" type="text" inputmode="decimal" name="difference_cost" value="{{ old('difference_cost', $costing->difference_cost ?? 1040.00) }}" readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="costing-summary-label">Result:</td>
                                                <td class="costing-summary-center">$</td>
                                                <td>
                                                    <input class="costing-form-control costing-summary-right-text costing-summary-value costing-summary-readonly" type="text" inputmode="decimal" name="percentage" value="{{ old('percentage', $costing->percentage ?? 16.64) }}" readonly>
                                                </td>
                                                <td class="costing-summary-center">%</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="costing-form-actions">
                                <div class="costing-save-status" id="costingSaveStatus">Use Save to store costing and operations for this order.</div>
                                <div>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card costing-files-card mb-0">
                    <div class="card-body">
                        <div class="costing-files-title">Files</div>

                        <div class="costing-file-box">
                            <div class="costing-file-head">
                                <label class="costing-file-label" for="drawing_pdf">Drawing PDF</label>
                                <input id="drawing_pdf" type="file" class="costing-file-input" name="drawing_pdf" accept="application/pdf" form="costingForm">
                            </div>
                            @if($costing?->drawing_pdf_path)
                                <a
                                    class="costing-file-thumb js-costing-pdf-preview"
                                    href="{{ asset('storage/' . $costing->drawing_pdf_path) }}"
                                    data-pdf-url="{{ asset('storage/' . $costing->drawing_pdf_path) }}"
                                    data-pdf-title="Drawing PDF"
                                >
                                    <iframe
                                        class="costing-file-thumb-frame"
                                        src="{{ asset('storage/' . $costing->drawing_pdf_path) }}#page=1&zoom=page-fit&toolbar=0&navpanes=0&scrollbar=0"
                                        title="Drawing PDF Preview"
                                    ></iframe>
                                </a>
                            @endif
                        </div>

                        <div class="costing-file-box">
                            <div class="costing-file-head">
                                <label class="costing-file-label" for="quote_pdf">Quote PDF</label>
                                <input id="quote_pdf" type="file" class="costing-file-input" name="quote_pdf" accept="application/pdf" form="costingForm">
                            </div>
                            @if($costing?->quote_pdf_path)
                                <a
                                    class="costing-file-thumb js-costing-pdf-preview"
                                    href="{{ asset('storage/' . $costing->quote_pdf_path) }}"
                                    data-pdf-url="{{ asset('storage/' . $costing->quote_pdf_path) }}"
                                    data-pdf-title="Quote PDF"
                                >
                                    <iframe
                                        class="costing-file-thumb-frame"
                                        src="{{ asset('storage/' . $costing->quote_pdf_path) }}#page=1&zoom=page-fit&toolbar=0&navpanes=0&scrollbar=0"
                                        title="Quote PDF Preview"
                                    ></iframe>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade costing-logs-modal" id="costingLogsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Costing History</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="costingLogsContent">
                    <div class="text-center text-muted py-4">Loading history...</div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade costing-pdf-modal" id="costingPdfModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="costingPdfModalTitle">PDF Preview</h5>
                    <div class="d-flex align-items-center" style="gap:8px;">
                        <a href="#" target="_blank" class="btn btn-outline-primary btn-sm" id="costingPdfOpenTab">Open in new tab</a>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <div class="modal-body">
                    <iframe id="costingPdfFrame" class="costing-pdf-frame" src="" title="PDF Preview"></iframe>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function () {
            const $tableBody = $('#operationsTableBody');
            const orderQty = {{ (int) ($order->qty ?? 0) }};
            const orderWoQty = {{ (float) ($order->wo_qty ?? 0) }};
            const laborRatePerHour = 120;
            let $activeDurationField = null;

            const $durationPanel = $(`
                <div class="costing-duration-panel js-global-duration-panel">
                    <div class="costing-duration-grid">
                        <div class="costing-duration-group">
                            <label>Hr</label>
                            <select class="js-duration-hours"></select>
                        </div>
                        <div class="costing-duration-group">
                            <label>Min</label>
                            <select class="js-duration-minutes"></select>
                        </div>
                    </div>
                </div>
            `);

            $('body').append($durationPanel);

            function nextRowIndex() {
                return $tableBody.find('.operation-row').length;
            }

            function opLabel(index) {
                return `OP${index}`;
            }

            function normalizeOperationLabels() {
                $tableBody.find('.operation-row').each(function (index) {
                    const $input = $(this).find('input[name$="[name_operation]"]');
                    if (index === 0) {
                        $input.val('Traveler Process');
                        return;
                    }

                    if (!$input.val() || /^OP\d+$/i.test($input.val().trim())) {
                        $input.val(opLabel(index));
                    }
                });
            }

            function normalizeTime(value) {
                const raw = String(value || '').trim();
                if (!raw) return '0:00:00';

                if (/^\d+:\d{2}:\d{2}$/.test(raw)) {
                    const [hours, minutes, seconds] = raw.split(':').map(Number);
                    return `${hours}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                }

                if (/^\d+(\.\d+)?$/.test(raw)) {
                    return decimalHoursToTime(parseFloat(raw));
                }

                return '0:00:00';
            }

            function timeToSeconds(value) {
                const normalized = normalizeTime(value);
                const [hours, minutes, seconds] = normalized.split(':').map(Number);
                return (hours * 3600) + (minutes * 60) + seconds;
            }

            function secondsToTime(totalSeconds) {
                const safeSeconds = Math.max(0, parseInt(totalSeconds || 0, 10));
                const hours = Math.floor(safeSeconds / 3600);
                const minutes = Math.floor((safeSeconds % 3600) / 60);
                const seconds = safeSeconds % 60;
                return `${hours}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }

            function decimalHoursToTime(value) {
                return secondsToTime(Math.round((parseFloat(value || 0) || 0) * 3600));
            }

            function displayTime(value) {
                const normalized = normalizeTime(value);
                const [hours, minutes] = normalized.split(':');
                return `${hours}:${minutes}`;
            }

            function formatMoney(value) {
                const number = parseFloat(value || 0) || 0;
                return number.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
            }

            function parseMoney(value) {
                return parseFloat(String(value || '').replace(/,/g, '').trim()) || 0;
            }

            function paintValueState($input, value) {
                const $cell = $input.closest('td');
                $cell.removeClass('costing-cell-positive costing-cell-negative');

                if (value > 0) {
                    $cell.addClass('costing-cell-positive');
                } else if (value < 0) {
                    $cell.addClass('costing-cell-negative');
                }
            }

            function buildDurationOptions(max, selected) {
                let html = '';
                for (let i = 0; i <= max; i += 1) {
                    const rawValue = String(i);
                    const label = i < 10 ? `0${i}` : rawValue;
                    html += `<option value="${rawValue}"${i === selected ? ' selected' : ''}>${label}</option>`;
                }
                return html;
            }

            function buildDurationControl(value, readonly = false) {
                const normalized = normalizeTime(value);

                return `
                    <div class="costing-duration-cell">
                        <button type="button" class="costing-duration-trigger js-duration-trigger"${readonly ? ' disabled' : ''}>${displayTime(normalized)}</button>
                    </div>
                `;
            }

            function setDurationTriggerReadonly($field, readonly) {
                const $trigger = $field.siblings('.costing-duration-cell').find('.js-duration-trigger');
                $trigger.prop('disabled', readonly);
            }

            function canEditRowTotal($row) {
                if (!$row || !$row.length) return false;
                if ($row.index() === 0) return false;

                const programming = timeToSeconds($row.find('input[name$="[time_programming]"]').val());
                const setup = timeToSeconds($row.find('input[name$="[time_setup]"]').val());
                const runtimeTotal = timeToSeconds($row.find('input[name$="[runtime_total]"]').val());

                return programming === 0 && setup === 0 && runtimeTotal === 0;
            }

            function syncDurationField($field) {
                if (!$field || !$field.length) return;

                const hours = parseInt($durationPanel.find('.js-duration-hours').val() || '0', 10);
                const minutes = parseInt($durationPanel.find('.js-duration-minutes').val() || '0', 10);
                const formatted = `${hours}:${String(minutes).padStart(2, '0')}:00`;
                $field.val(formatted);
                $field.siblings('.costing-duration-cell').find('.js-duration-trigger').text(displayTime(formatted));
            }

            function closeDurationPanel() {
                $durationPanel.removeClass('is-open');
                $activeDurationField = null;
            }

            function openDurationPanel($field) {
                const normalized = normalizeTime($field.val());
                const [hours, minutes] = normalized.split(':').map(Number);
                const $trigger = $field.siblings('.costing-duration-cell').find('.js-duration-trigger');
                const triggerRect = $trigger[0].getBoundingClientRect();
                const panelWidth = 148;
                const panelHeight = 104;
                let left = triggerRect.left + (triggerRect.width / 2) - (panelWidth / 2);
                let top = triggerRect.bottom + 6;

                if (left < 8) left = 8;
                if (left + panelWidth > window.innerWidth - 8) {
                    left = window.innerWidth - panelWidth - 8;
                }

                if (top + panelHeight > window.innerHeight - 8) {
                    top = Math.max(8, triggerRect.top - panelHeight - 6);
                }

                $durationPanel.find('.js-duration-hours').html(buildDurationOptions(99, hours));
                $durationPanel.find('.js-duration-minutes').html(buildDurationOptions(59, minutes));
                $durationPanel.css({ left: `${left}px`, top: `${top}px` }).addClass('is-open');
                $activeDurationField = $field;
            }

            function renderDurationPickers(scope) {
                $(scope).find('.js-duration-field').each(function () {
                    const $field = $(this);
                    const isFirstRow = $field.closest('.operation-row').index() === 0;
                    const readonly = isFirstRow || $field.hasClass('js-runtime-total-field');
                    $field.val(normalizeTime($field.val()));

                    if (!$field.siblings('.costing-duration-cell').length) {
                        $field.after(buildDurationControl($field.val(), readonly));
                    }
                });

                $(scope).find('.js-duration-trigger')
                    .off('click.costingDuration')
                    .on('click.costingDuration', function (event) {
                        event.stopPropagation();
                        const $field = $(this).closest('td').find('.js-duration-field');

                        if ($activeDurationField && $activeDurationField[0] === $field[0] && $durationPanel.hasClass('is-open')) {
                            closeDurationPanel();
                            return;
                        }

                        openDurationPanel($field);
                    });
            }

            function updateRowTotals($row) {
                if (!$row || !$row.length) return;

                const rowIndex = $row.index();
                const $totalField = $row.find('.js-row-total-time');
                const $runtimeTotalField = $row.find('.js-runtime-total-field');

                if (rowIndex === 0) {
                    if (!$totalField.val() || normalizeTime($totalField.val()) === '0:00:00') {
                        $totalField.val('1:00:00');
                    } else {
                        $totalField.val(normalizeTime($totalField.val()));
                    }
                    $totalField.siblings('.costing-duration-cell').find('.js-duration-trigger').text(displayTime($totalField.val()));
                    setDurationTriggerReadonly($totalField, false);
                    return;
                }

                const programming = timeToSeconds($row.find('input[name$="[time_programming]"]').val());
                const setup = timeToSeconds($row.find('input[name$="[time_setup]"]').val());
                const runtimePcs = timeToSeconds($row.find('input[name$="[runtime_pcs]"]').val());
                const runtimeTotal = runtimePcs * orderQty;

                $runtimeTotalField.val(secondsToTime(runtimeTotal));
                $runtimeTotalField.siblings('.costing-duration-cell').find('.js-duration-trigger').text(displayTime($runtimeTotalField.val()));
                setDurationTriggerReadonly($runtimeTotalField, true);

                if (canEditRowTotal($row)) {
                    setDurationTriggerReadonly($totalField, false);
                    return;
                }

                const rowTotal = programming + setup + runtimeTotal;

                $totalField.val(secondsToTime(rowTotal));
                $totalField.siblings('.costing-duration-cell').find('.js-duration-trigger').text(displayTime($totalField.val()));
                setDurationTriggerReadonly($totalField, true);
            }

            function updateSummaryTotals() {
                let sumProgramming = 0;
                let sumSetup = 0;
                let sumRuntimePcs = 0;
                let sumRuntimeTotal = 0;
                let sumTotalTimeOrder = 0;

                $tableBody.find('.operation-row').each(function () {
                    sumProgramming += timeToSeconds($(this).find('input[name$="[time_programming]"]').val());
                    sumSetup += timeToSeconds($(this).find('input[name$="[time_setup]"]').val());
                    sumRuntimePcs += timeToSeconds($(this).find('input[name$="[runtime_pcs]"]').val());
                    sumRuntimeTotal += timeToSeconds($(this).find('input[name$="[runtime_total]"]').val());
                    sumTotalTimeOrder += timeToSeconds($(this).find('input[name$="[total_time_operation]"]').val());
                });

                $('input[name="sum_programming"]').val(secondsToTime(sumProgramming));
                $('input[name="sum_setup"]').val(secondsToTime(sumSetup));
                $('input[name="sum_runtime_pcs"]').val(secondsToTime(sumRuntimePcs));
                $('input[name="sum_runtime_total"]').val(secondsToTime(sumRuntimeTotal));
                $('input[name="total_time_order"]').val(secondsToTime(sumTotalTimeOrder));
                $('input[name="total_labor"]').val(formatMoney((sumTotalTimeOrder / 3600) * laborRatePerHour));
                updateCostSummary();
            }

            function updateCostSummary() {
                const totalLabor = parseMoney($('input[name="total_labor"]').val());
                const totalMaterial = parseMoney($('input[name="total_material"]').val());
                const totalOutsource = parseMoney($('input[name="total_outsource"]').val());
                const grandTotal = totalLabor + totalMaterial + totalOutsource;
                const salePrice = parseMoney($('input[name="sale_price"]').val());
                const difference = salePrice - grandTotal;
                const result = salePrice > 0 ? (difference / salePrice) * 100 : 0;
                const costPcs = orderWoQty > 0 ? grandTotal / orderWoQty : 0;

                $('input[name="grandtotal_cost"]').val(formatMoney(grandTotal));
                $('input[name="price_pcs"]').val(costPcs > 0 ? formatMoney(costPcs) : '');
                $('input[name="difference_cost"]').val(formatMoney(difference));
                $('input[name="percentage"]').val(result.toFixed(2));
                paintValueState($('input[name="difference_cost"]'), difference);
                paintValueState($('input[name="percentage"]'), result);
            }

            function buildRow(index) {
                const isFirstRow = index === 0;
                return `
                    <tr class="operation-row">
                        <td><input class="costing-form-control" type="text" name="operations[${index}][name_operation]" value="${isFirstRow ? 'Traveler Process' : opLabel(index)}"></td>
                        <td><input class="costing-form-control" type="text" name="operations[${index}][resource_name]" value=""></td>
                        <td><input class="costing-form-control costing-duration-input js-duration-field" type="hidden" name="operations[${index}][time_programming]" value="0:00:00"></td>
                        <td><input class="costing-form-control costing-duration-input js-duration-field" type="hidden" name="operations[${index}][time_setup]" value="0:00:00"></td>
                        <td><input class="costing-form-control costing-duration-input js-duration-field" type="hidden" name="operations[${index}][runtime_pcs]" value="0:00:00"></td>
                        <td><input class="costing-form-control costing-duration-input js-duration-field js-runtime-total-field" type="hidden" name="operations[${index}][runtime_total]" value="0:00:00"></td>
                        <td><input class="costing-form-control costing-duration-input js-duration-field js-row-total-time" type="hidden" name="operations[${index}][total_time_operation]" value="${isFirstRow ? '1:00:00' : '0:00:00'}"></td>
                    </tr>
                `;
            }

            $('#addOperationRow').on('click', function () {
                const $row = $(buildRow(nextRowIndex()));
                $tableBody.append($row);
                normalizeOperationLabels();
                renderDurationPickers($row);
                updateRowTotals($row);
                updateSummaryTotals();
            });

            $('#removeOperationRow').on('click', function () {
                const $rows = $tableBody.find('.operation-row');
                if ($rows.length > 1) {
                    $rows.last().remove();
                    normalizeOperationLabels();
                    updateSummaryTotals();
                }
            });

            $durationPanel.on('click', function (event) {
                event.stopPropagation();
            });

            $durationPanel.find('select').on('change', function () {
                if (!$activeDurationField || !$activeDurationField.length) return;

                syncDurationField($activeDurationField);
                const $row = $activeDurationField.closest('.operation-row');
                updateRowTotals($row);
                updateSummaryTotals();
            });

            $(window).on('resize scroll', function () {
                closeDurationPanel();
            });

            $(document).on('click', function () {
                closeDurationPanel();
            });

            $('#openCostingLogs').on('click', function () {
                const $modal = $('#costingLogsModal');
                const $content = $('#costingLogsContent');

                $content.html('<div class="text-center text-muted py-4">Loading history...</div>');
                $modal.modal('show');

                $.get(@json(route('costing.logs', $order)))
                    .done(function (html) {
                        $content.html(html);
                    })
                    .fail(function () {
                        $content.html('<div class="alert alert-danger mb-0">Unable to load history.</div>');
                    });
            });

            $(document).on('click', '.js-costing-pdf-preview', function (event) {
                event.preventDefault();

                const pdfUrl = $(this).data('pdf-url');
                const pdfTitle = $(this).data('pdf-title') || 'PDF Preview';

                $('#costingPdfModalTitle').text(pdfTitle);
                $('#costingPdfFrame').attr('src', pdfUrl);
                $('#costingPdfOpenTab').attr('href', pdfUrl);
                $('#costingPdfModal').modal('show');
            });

            $('#costingPdfModal').on('hidden.bs.modal', function () {
                $('#costingPdfFrame').attr('src', '');
            });

            $('input[name="total_material"], input[name="sale_price"], input[name="price_pcs"], input[name="grandtotal_cost"], input[name="difference_cost"], input[name="percentage"]').each(function () {
                const $input = $(this);
                if ($input.attr('name') !== 'percentage' && $input.val().trim() !== '') {
                    $input.val(formatMoney($input.val()));
                }
            });

            $('input[name="total_material"], input[name="total_outsource"], input[name="sale_price"], input[name="price_pcs"]').on('blur', function () {
                const $input = $(this);
                if ($input.val().trim() !== '') {
                    $input.val(formatMoney($input.val()));
                }
                updateCostSummary();
            });

            normalizeOperationLabels();
            renderDurationPickers(document);
            $tableBody.find('.operation-row').each(function () {
                updateRowTotals($(this));
            });
            updateSummaryTotals();
            updateCostSummary();
        });
    </script>
@stop
