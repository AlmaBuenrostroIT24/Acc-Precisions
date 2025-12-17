<div id="table-wrapper" style="display: none;">
    <div class="table-responsive">
        <table id="orders_scheduleTable" class="table table-bordered  table-hover {{ request()->is('scheduleh') ? 'letra-grande' : '' }}" style="table-layout: fixed; width: 100%;">
            <thead class="table-light thead-custom">
                <tr>
                    <th>Id</th>
                    <th style="display:none;">LocationText</th> <!-- índice 1 -->
                    <th style="display:none;">StatusText</th> <!-- índice 2 -->
                    <th style="width: 70px;">LOCATION</th>
                    <th style="width: 55px;">WORK ID</th>
                    <th style="width: 60px;">PN</th>
                    <th style="width: 150px;">PART/DESCRIPTION</th>
                    <th style="width: 70px;">CUSTOMER</th>
                    <th style="width: 30px;">COQTY</th>
                    <th style="width: 40px;">WOQTY</th>
                    <th style="width: 100px;">STATUS</th>
                    <th style="width: 80px;">MAC. DATE</th>
                    <th style="display:none;">DueDateText</th> <!-- índice 2 -->
                    <th style="width: 80px;">DUE DATE</th>
                    <th style="width: 30px;">DAYS</th>
                    <th style="width: 50px;">ALERT</th>
                    <th style="width: 20px;">REP.</th>
                    <th style="width: 20px;">OUT</th>
                    <th style="width: 30px;">STATION</th>
                    <th style="width: 65px;">NOTES</th>
                    <th style="width: 40px;">ORD ID</th>
                    <th style="width: 60px;">CUST PO</th>
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
                    'cutmaterial' => 'bg-status-cutmaterial',
                    'grinding' => 'bg-status-grinding',
                    'onrack' => 'bg-status-onrack',
                    'programming' => 'bg-status-programming',
                    'setup' => 'bg-status-setup',
                    'machining' => 'bg-status-machining',
                    'marking' => 'bg-status-marking',
                    'deburring' => 'bg-status-deburring',
                    'qa' => 'bg-status-qa',
                    'outsource' => 'bg-status-outsource',
                    'assembly' => 'bg-status-assembly',
                    'shipping' => 'bg-status-shipping',
                    'ready' => 'bg-status-ready',
                    'onhold' => 'bg-status-onhold',
                    default => '',
                    };


                    $dias = $order->dias_restantes;
                    $color = $dias < 0 ? 'text-danger fw-bold' : ($dias <=2 ? 'text-warning fw-bold' : 'text-success fw-bold' );
                        $alertColor=$dias < 0 ? 'bg-danger' : ($dias <=2 ? 'bg-warning' : 'bg-success' );
                        $alertLabel=$dias < 0 ? 'Late' : ($dias <=2 ? 'Expedite' : 'On time' );
                        @endphp
                        <tr class="{{ $rowClass }}" data-order-id="{{ $order->id }}" id="row-{{ $order->id }}" data-priority="{{ $order->priority === 'yes' ? 'yes' : 'no' }}">
                        <td style="display: none;">{{ $order->id }}</td>
                        <!-- Columna oculta solo texto para filtro -->
                        <td id="hidden-location-{{ $order->id }}" style="display: none;">{{ strtolower($order->location) }}</td>
                        <td id="hidden-status-{{ $order->id }}" style="display:none;">{{ strtolower($order->status) }}</td>
                        <!----------------------------------------->
                        <td style="min-width: 90px;">
                            <select
                                name="location"
                                class="form-control form-control-sm location-select fw-bold text-capitalize"
                                style="width: 90px; font-weight: bold; color: black;"
                                data-id="{{ $order->id }}"
                                data-old-status="{{ strtolower($order->status) }}"
                                @if(auth()->check() && auth()->user()->hasAnyRole(['Deburring', 'QCShipping'])) disabled @endif
                                >
                                <option value="Floor" {{ $order->location === 'Floor' ? 'selected' : '' }}>Floor</option>
                                <option value="Yarnell" {{ $order->location === 'Yarnell' ? 'selected' : '' }}>Yarnell</option>
                                <option value="Hearst" {{ $order->location === 'Hearst' ? 'selected' : '' }}>Hearst</option>
                                <option value="Standby" {{ $order->location === 'Standby' ? 'selected' : '' }}>Standby</option> {{-- 2025-12-15: nueva ubicación para onhold --}}
                            </select>

                            <div class="last-location-label mt-1">
                                @if ($order->location === 'Standby' && $order->last_location)
                                <span class="badge bg-secondary text-light">
                                    <i class="fas fa-hourglass-half me-1"></i> {{ $order->last_location }}
                                </span>
                                @elseif ($order->last_location === 'Yarnell')
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-map-marker-alt me-1"></i> Yarnell
                                </span>
                                @endif
                            </div>
                        </td>
                        <td style="white-space: nowrap; width: 100px;" class="texsty">
                            @php
                            $isRestricted = auth()->check() && auth()->user()->hasAnyRole(['Deburring', 'QCShipping']);
                            $hasWorkId = isset($order->work_id) && trim($order->work_id) !== '';
                            // Editable si NO está restringido y (venía vacío o AHORA está vacío)
                            $canEdit = !$isRestricted && ($order->was_work_id_null || !$hasWorkId);
                            @endphp

                            @if ($canEdit)
                            <span
                                class="{{ $hasWorkId ? 'text-success fw-bold' : 'text-muted' }} editable-work-id text-decoration-underline"
                                data-id="{{ $order->id }}"
                                data-value="{{ $hasWorkId ? e($order->work_id) : '' }}"
                                style="cursor:pointer;">
                                {{ $hasWorkId ? $order->work_id : 'Add' }}
                            </span>
                            @else
                            {{-- Texto no editable; igual mostramos "Add" si ahora está vacío --}}
                            <span class="{{ $hasWorkId ? '' : 'text-muted' }}">
                                {{ $hasWorkId ? $order->work_id : 'Add' }}
                            </span>
                            @endif
                        </td>
                        <td class="texsty" style="min-width: 120px;">{{ $order->PN }}</td>
                        <td style="font-size: 11px;">{{ $order->Part_description }}</td>
                        <td class="texsty">{{ $order->costumer }}</td>
                        <td class="texsty">{{ $order->qty }}</td>
                        <td>
                            @php
                            $isRestricted = auth()->check() && auth()->user()->hasAnyRole(['Deburring', 'QCShipping']);
                            $hasValue = !is_null($order->wo_qty) && (string)$order->wo_qty !== '' && (int)$order->wo_qty !== 0;

                            // Deshabilitar si:
                            // 1) Es rol restringido Y ya tiene valor, O
                            // 2) Es un hijo (parent_id no es null)
                            $disabled = ($isRestricted && $hasValue) || !is_null($order->parent_id);
                            @endphp

                            <input
                                value="{{ $order->wo_qty == 0 || is_null($order->wo_qty) ? '' : $order->wo_qty }}"
                                data-id="{{ $order->id }}"
                                data-original="{{ $order->wo_qty }}"
                                data-parent-id="{{ $order->parent_id }}" {{-- 👈 opcional, útil para JS --}}
                                class="wo-qty-input form-control form-control-sm"
                                style="width: 60px; font-weight: bold; color: black;"
                                @if($disabled) disabled @endif>
                            @if (is_null($order->parent_id))
                                {{-- <div class="small text-muted mt-1">
                                    Group:
                                    <span class="cell-group-wo-qty">{{ (int) ($order->group_wo_qty ?? 0) }}</span>
                                </div> --}}
                            @endif
                        </td>
                        <td style="min-width: 120px;">
                            <select
                                class="form-control form-control-sm status-select"
                                style="font-weight: bold; color: black;"
                                data-id="{{ $order->id }}"
                                data-location="{{ $order->location }}"
                                data-old="{{ strtolower($order->status) }}">
                                @php
                                $user = auth()->user();
                                $cur = strtolower($order->status);

                                // Mapear permisos por rol
                                $roleAllowed = [
                                'QCShipping' => ['grinding','outsource','ready','shipping','sent'],
                                'Deburring' => ['qa','shipping'],
                                ];

                                // Detectar rol actual
                                $roleName = $user ? $user->getRoleNames()->first() : null;
                                $allowed = $roleAllowed[$roleName] ?? null;

                                $options = [
                                'pending' => 'Pending',
                                'waitingformaterial' => 'Wait Material',
                                'cutmaterial' => 'Cut Material',
                                'grinding' => 'Grinding',
                                'onrack' => 'OnRack',
                                'programming' => 'Programming',
                                'setup' => 'SetUp',
                                'machining' => 'Machining',
                                'marking' => 'Marking',
                                'deburring' => 'Deburring',
                                'qa' => 'QA',
                                'outsource' => 'OutSource',
                                'assembly' => 'Assembly',
                                'shipping' => 'Shipping',
                                'sent' => 'Sent',
                                'onhold' => 'OnHold',
                                'ready' => 'Ready',
                                ];
                                @endphp

                                @foreach($options as $value => $label)
                                @php
                                $selected = $cur === $value ? 'selected' : '';

                                // 🔒 Reglas:
                                // - Si hay $allowed definido para el rol,
                                // se habilita si está en allowed o si es el estatus actual.
                                // - Lo demás queda disabled.
                                $disabled = '';
                                if ($allowed !== null && !in_array($value, $allowed) && $cur !== $value) {
                                $disabled = 'disabled';
                                }
                                @endphp
                                <option value="{{ $value }}" {{ $selected }} {{ $disabled }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </td>


                        <td class="texsty">
                            @php
                            $isRestricted = auth()->check() && auth()->user()->hasAnyRole(['Deburring', 'QCShipping']);
                            $hasSource = $order->our_source;
                            $dateValue = optional($order->machining_date)->format('Y-m-d');
                            // 2025-12-15: Se muestra en tabla como Nov-25-2025
                            $dateLabel = optional($order->machining_date)->format('M-d-Y');
                            @endphp

                            <span
                                class="{{ $isRestricted ? '' : 'editable-machining-date text-decoration-underline' }}"
                                data-id="{{ $order->id }}"
                                data-enabled="{{ $hasSource ? '1' : '0' }}"
                                data-value="{{ $dateValue }}"
                                style="{{ $isRestricted || !$hasSource ? '' : 'cursor:pointer;' }}">
                                {{ $dateLabel }}
                            </span>
                        </td>
                        @php
                        $dueDateValue = optional($order->due_date)->format('Y-m-d');
                        $dueDateLabel = optional($order->due_date)->format('M-d-Y');
                        $updateDateLabel = $order->update_duedate
                        ? \Carbon\Carbon::parse($order->update_duedate)->format('M-d-Y')
                        : null;
                        @endphp
                        <td style="display:none;">{{ $dueDateValue }}</td>
                        <td>
                            <span class="editable-due-date text-decoration-underline"
                                data-id="{{ $order->id }}"
                                data-enabled="{{ $order->status === 'onhold' ? 1 : 0 }}"
                                data-value="{{ $dueDateValue }}"
                                style="{{ $order->status === 'onhold' ? 'cursor:pointer;' : '' }}">
                                {{ $dueDateLabel }} {{-- 2025-12-15: formato Nov-25-2025 --}}
                            </span>
                            @if ($updateDateLabel)
                            <div class="mt-1 update-duedate-badge-wrap">
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-history me-1"></i> {{ $updateDateLabel }}
                                </span>
                            </div>
                            @endif
                        </td>
                        <td id="dias-restantes-{{ $order->id }}" class="{{ $color }}" style="font-size: 16px ">
                            {{ $dias }} days
                        </td>
                        <td class="text-center">
                            <div id="alerta-{{ $order->id }}" class="progress" style="width: 80px; height: 30px;">
                                <div class="progress-bar {{ $alertColor }}" role="progressbar"
                                    style="width: 100%; height: 30px; line-height: 30px; font-size: 18px; font-weight: bold;">
                                    {{ $alertLabel }}
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <button
                                class="btn btn-sm toggle-report-btn {{ $order->report ? 'btn-primary' : 'btn-secondary' }}"
                                data-id="{{ $order->id }}"
                                data-value="{{ $order->report ? 1 : 0 }}"
                                @if(auth()->check() && auth()->user()->hasAnyRole(['Deburring', 'QCShipping'])) disabled @endif
                                >
                                <i class="fas {{ $order->report ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                            </button>
                        </td>

                        <td class="text-center">
                            <button
                                class="btn btn-sm toggle-source-btn {{ $order->our_source ? 'btn-primary' : 'btn-secondary' }}"
                                data-id="{{ $order->id }}"
                                data-value="{{ $order->our_source }}"
                                @if(auth()->check() && auth()->user()->hasAnyRole(['Deburring', 'QCShipping'])) disabled @endif
                                >
                                <i class="fas {{ $order->our_source ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                            </button>
                        </td>
                        <td class="texsty" style="white-space: nowrap; width: 100px;" data-location="{{ $order->location }}">
                            @php
                            $isRestricted = auth()->check() && auth()->user()->hasAnyRole(['Deburring', 'QCShipping']);
                            @endphp
                            <span
                                class="{{ $order->station ? 'text-success fw-bold' : 'text-muted' }} {{ $isRestricted ? '' : 'editable-station text-decoration-underline' }}"
                                data-id="{{ $order->id }}"
                                style="{{ $isRestricted ? '' : 'cursor:pointer;' }}">
                                {{ $order->station ?? 'N/A' }}
                            </span>
                        </td>
                        <td style="font-size: 12px;" class="notes-cell" data-id="{{ $order->id }}">
                            @php
                            $isRestricted = auth()->check() && auth()->user()->hasAnyRole(['Deburring', 'QCShipping']);
                            @endphp

                            @if (!empty($order->notes))
                            <span
                                @unless($isRestricted) class="open-notes-modal" style="cursor:pointer;" @endunless
                                data-id="{{ $order->id }}"
                                data-notes="{{ $order->notes }}"
                                title="{{ $order->notes }}"
                                data-bs-toggle="tooltip"
                                data-bs-placement="left">
                                {{ \Illuminate\Support\Str::limit($order->notes, 30) }}
                            </span>
                            @else
                            <span
                                @unless($isRestricted) class="open-notes-modal text-muted fst-italic" style="cursor:pointer;" @else class="text-muted fst-italic" @endunless
                                data-id="{{ $order->id }}"
                                data-notes=""
                                data-bs-toggle="tooltip"
                                data-bs-placement="left">
                                <i class="fas fa-plus-circle me-1"></i>
                                Note
                            </span>
                            @endif
                        </td>
                        <td class="texsty">{{ $order->co}}</td>
                        <td class="texsty">{{ $order->cust_po}}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="25" class="text-center">No hay órdenes registradas.</td>
                        </tr>
                        @endforelse
            </tbody>
        </table>
    </div>
</div>
