<div class="table-responsive">
    <table id="orders_scheduleTable" class="table table-striped table-bordered" style="table-layout: fixed; width: 100%;">
        <thead class="table-light thead-custom">
            <tr>
                <th style="width: 15px; display:none;">Id</th>
                <th style="display:none;">LocationText</th> <!-- índice 1 -->
                <th style="display:none;">StatusText</th> <!-- índice 2 -->
                <th style="width: 65px;">LOCATION</th>
                <th style="width: 55px;">WORK ID</th>
                <th style="width: 60px;">PN</th>
                <th style="width: 100px;">PART/DESCRIPTION</th>
                <th style="width: 80px;">CUSTOMER</th>
                <th style="width: 45px;">CO QTY</th>
                <th style="width: 45px;">WO QTY</th>
                <th style="width: 90px;">STATUS</th>
                <th style="width: 75px;">MACH. DATE</th>
                <th style="display:none;">StatusText</th> <!-- índice 2 -->
                <th style="width: 60px;">DUE DATE</th>
                <th style="width: 40px;">DAYS</th>
                <th style="width: 60px;">ALERT</th>
                <th style="width: 30px;">REP.</th>
                <th style="width: 30px;">OUT</th>
                <th style="width: 50px;">STATION</th>
                <th style="width: 70px;">NOTES</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            @php
            $machining_date = $order->machining_date;
            $days = $machining_date ? \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($machining_date), false) : null;
            $alert = $days !== null && $days < 0 ? 1 : 0;


                $date=\Carbon\Carbon::parse($order->due_date)->copy();
                $days = 3;
                while ($days > 0) {
                $date->subDay();
                if (!$date->isWeekend()) {
                $days--;
                }
                }


                $status = strtolower($order->status);
                $rowClass = match($status) {
                'pending' => 'bg-status-pending',
                'waitingformaterial' => 'bg-status-waitingformaterial',
                'onrack' => 'bg-status-onrack',
                'onhold' => 'bg-status-onhold',
                'setup' => 'bg-status-setup',
                'shipping' => 'bg-status-shipping',
                'machining' => 'bg-status-machining',
                'outsource' => 'bg-status-outsource',
                'qa' => 'bg-status-qa',
                'deburring' => 'bg-status-deburring',
                default => '',
                };


                $dias = $order->dias_restantes;
                $color = $dias < 0 ? 'text-danger fw-bold' : ($dias <=2 ? 'text-warning fw-bold' : 'text-success fw-bold' );
                    $alertColor=$dias < 0 ? 'bg-danger' : ($dias <=2 ? 'bg-warning' : 'bg-success' );
                    $alertLabel=$dias < 0 ? 'Late' : ($dias <=2 ? 'Expedite' : 'On time' );
                    @endphp
                    <tr class="{{ $rowClass }}" data-order-id="{{ $order->id }}" id="row-{{ $order->id }}">
                    <td style="display: none;">{{ $order->id }}</td>
                    <!-- Columna oculta solo texto para filtro -->
                    <td id="hidden-location-{{ $order->id }}" style="display: none;">{{ strtolower($order->location) }}</td>
                    <td id="hidden-status-{{ $order->id }}" style="display:none;">{{ strtolower($order->status) }}</td>
                    <!----------------------------------------->
                    <td style="min-width: 90px;">
                        <select class="form-control form-control-sm location-select fw-bold text-capitalize" style="width: 80px; font-weight: bold; color: black;" data-id="{{ $order->id }}">
                            <option value="Floor" {{ $order->location === 'Floor' ? 'selected' : '' }}>Floor</option>
                            <option value="Yarnell" {{ $order->location === 'Yarnell' ? 'selected' : '' }}>Yarnell</option>
                            <option value="Hearst" {{ $order->location === 'Hearst' ? 'selected' : '' }}>Hearst</option>
                        </select>
                    </td>
                    <td style="white-space: nowrap; width: 100px;">
                        @if ($order->was_work_id_null)
                        <span class="editable-work-id text-decoration-underline {{ $order->work_id ? 'text-success fw-bold' : 'text-muted' }}"
                            data-id="{{ $order->id }}"
                            data-value="{{ $order->work_id ?? '' }}"
                            style="cursor:pointer;">
                            {{ $order->work_id ?? 'Add' }}
                        </span>
                        @else
                        {{ $order->work_id }}
                        @endif
                    </td>
                    <td style="min-width: 120px;">{{ $order->PN }}</td>
                    <td style="font-size: 11px;">{{ $order->Part_description }}</td>
                    <td>{{ $order->costumer }}</td>
                    <td>{{ $order->qty }}</td>
                    <td>
                        <input value="{{ $order->wo_qty }}"
                            data-id="{{ $order->id }}"
                            class="wo-qty-input form-control form-control-sm"
                            style="width: 60px; font-weight: bold; color: black;">
                    </td>
                    <td style="min-width: 120px;">
                        <select class="form-control form-control-sm status-select"
                            style=" font-weight: bold; color: black;" data-id="{{ $order->id }}" data-location="{{ $order->location }}">
                            <option value="Pending" {{ strtolower($order->status) === 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="waitingformaterial" {{ strtolower($order->status) === 'waitingformaterial' ? 'selected' : '' }}>Wait Material</option>
                            <option value="onrack" {{ strtolower($order->status) === 'onrack' ? 'selected' : '' }}>OnRack</option>
                            <option value="setup" {{ strtolower($order->status) === 'setup' ? 'selected' : '' }}>SetUp</option>
                            <option value="machining" {{ strtolower($order->status) === 'machining' ? 'selected' : '' }}>Machining</option>
                            <option value="outsource" {{ strtolower($order->status) === 'outsource' ? 'selected' : '' }}>OutSource</option>
                            <option value="qa" {{ strtolower($order->status) === 'qa' ? 'selected' : '' }}>QA</option>
                            <option value="deburring" {{ strtolower($order->status) === 'deburring' ? 'selected' : '' }}>Deburring</option>
                            <option value="shipping" {{ strtolower($order->status) === 'shipping' ? 'selected' : '' }}>Shipping</option>
                            <option value="sent" {{ strtolower($order->status) === 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="onhold" {{ strtolower($order->status) === 'onhold' ? 'selected' : '' }}>OnHold</option>
                        </select>
                    </td>
                    <td>{{ strtolower($date->format('M-d-y')) }}</td>
                    <td style="display:none;">{{ optional($order->due_date)->format('Y-m-d') }}</td>
                    <td style="min-width: 70px;">{{ strtolower(optional($order->due_date)->format('M-d-y')) }}</td>
                    <td id="dias-restantes-{{ $order->id }}" class="{{ $color }}">
                        {{ $dias }} days
                    </td>

                    <td >
                        <div id="alerta-{{ $order->id }}" class="progress" style="width: 80px; height: 30px;">
                            <div class="progress-bar {{ $alertColor }}" role="progressbar"
                                style="width: 100%; height: 30px; line-height: 30px; font-size: 18px; font-weight: bold;">
                                {{ $alertLabel }}
                            </div>
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-sm toggle-report-btn {{ $order->report ? 'btn-primary' : 'btn-secondary' }}"
                            data-id="{{ $order->id }}"
                            data-value="{{ $order->report ? 1 : 0 }}">
                            <i class="fas {{ $order->report ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                        </button>
                    </td>
                    <td>
                        <button class="btn btn-sm toggle-source-btn {{ $order->our_source ? 'btn-primary' : 'btn-secondary' }}"
                            data-id="{{ $order->id }}"
                            data-value="{{ $order->our_source }}">
                            <i class="fas {{ $order->our_source ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                        </button>
                    </td>
                    <td style="white-space: nowrap; width: 100px;" data-location="{{ $order->location }}">
                        <span class="editable-station text-decoration-underline {{ $order->station ? 'text-success fw-bold' : 'text-muted' }}"
                            data-id="{{ $order->id }}" style="cursor:pointer;">
                            {{ $order->station ?? 'N/A' }}
                        </span>
                    </td>
                    <td style="font-size: 12px;" class="notes-cell" data-id="{{ $order->id }}">
                        @if (!empty($order->notes))
                        <span class="open-notes-modal" data-id="{{ $order->id }}" data-notes="{{ $order->notes }}" style="cursor:pointer;" title="{{ $order->notes }}" data-bs-toggle="tooltip" data-bs-placement="left">
                            {{ \Illuminate\Support\Str::limit($order->notes, 30) }}
                        </span>
                        @else
                        <span class="open-notes-modal text-muted fst-italic"
                            data-id="{{ $order->id }}" data-notes="" style="cursor:pointer;" data-bs-toggle="tooltip" data-bs-placement="left">
                            <i class="fas fa-plus-circle me-1"></i>
                            Note
                        </span>
                        @endif
                    </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="25" class="text-center">No hay órdenes registradas.</td>
                    </tr>
                    @endforelse
        </tbody>
    </table>
</div>