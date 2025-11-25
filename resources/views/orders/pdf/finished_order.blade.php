<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Order {{ $order->work_id }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
        }

        /* ===== ESTILO PROFESIONAL ===== */
        .table-clean {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }

        .table-clean th {
            background: #e9eef5;
            color: #1a1a1a;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #c9d1dc;
            width: 180px;
        }

        .table-clean td {
            border: 1px solid #c9d1dc;
            padding: 6px 8px;
            background: #fff;
        }

        .table-clean tr:nth-child(even) td {
            background: #f7f9fc;
        }

        /* Sección estilo */
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 18px;
            background: #2d4f8b;
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
        }

        h1 {
            font-size: 22px;
            margin-bottom: 8px;
            color: #2d4f8b;
        }
    </style>
</head>

<body>

    <h1>Completed Order Report</h1>

    <!-- =======================================
         INFORMACIÓN PRINCIPAL DE LA ORDEN
    ======================================== -->
    <h2 class="section-title">Order Information</h2>

    <table class="table-clean">
        <tr>
            <th>Work ID</th>
            <td>{{ $order->work_id }}</td>
            <th>Location</th>
            <td>{{ $order->location }}</td>
        </tr>

        <tr>
            <th>Customer</th>
            <td>{{ $order->costumer }}</td>
            <th>PN</th>
            <td>{{ $order->PN }}</td>
        </tr>

        <tr>
            <th>Description</th>
            <td colspan="3">{{ $order->Part_description }}</td>
        </tr>

        <tr>
            <th>CO Qty</th>
            <td>{{ $order->qty }}</td>
            <th>WO Qty</th>
            <td>{{ $order->wo_qty }}</td>
        </tr>

        <tr>
            <th>Due Date</th>
            <td>{{ optional($order->due_date)->format('M-d-y') }}</td>
            <th>End Date</th>
            <td>{{ optional($order->sent_at)->format('M-d-y H:i') }}</td>
        </tr>
        <tr>
            <th>Report</th>
            <td>{{ $order->report ? 'Yes' : 'No' }}</td>
            <th>Out/Source</th>
            <td>{{ $order->our_source ? 'Yes' : 'No' }}</td>
        </tr>
        
        <tr>
            <th>Notes</th>
            <td colspan="3">{{ $order->notes }}</td>
        </tr>

        <tr>
            <th>Target</th>
            <td colspan="3">
                @if ($order->target_date < 0)
                    {{ $order->target_date }} Late
                    @elseif ($order->target_date == 0)
                    {{ $order->target_date }} On time
                    @elseif ($order->target_date > 0)
                    {{ $order->target_date }} Early
                    @else
                    -
                    @endif
            </td>
        </tr>
    </table>



    <!-- =======================================
         HISTORIAL DE CAMBIOS (LOGS)
    ======================================== -->
    <h2 class="section-title">Change Log History</h2>

    <table class="table-clean">
        <thead>
            <tr>
                <th style="width: 120px;">Date</th>
                <th style="width: 50px;">User</th>
                <th style="width: 120px;">Field</th>
                <th>Old Value</th>
                <th>New Value</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($logs as $log)
            <tr>
                <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ optional($log->user)->name ?? 'Unknown' }}</td>
                <td>{{ $log->field }}</td>
                <td>{{ $log->old_value }}</td>
                <td>{{ $log->new_value }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;">No logs found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- FOOTER -->
    <p style="margin-top: 20px; font-size: 10px; text-align:right;">
        Generated on: {{ now()->format('Y-m-d H:i') }}
    </p>

</body>

</html>