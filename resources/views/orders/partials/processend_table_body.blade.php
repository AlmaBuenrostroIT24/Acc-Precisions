             @foreach($ordersProcessend as $order)
             @php
             $status = strtolower($order->status);
             $statusClass = match($status) {
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
             'sent' => 'bg-status-sent',
             'ready' => 'bg-status-ready',
             'onhold' => 'bg-status-onhold',
             default => '',
             };
             @endphp
             <tr class="{{ $statusClass }} text-nowrap align-middle small">
                 <td>
                     @if ($order->last_location === 'Yarnell')
                     <span class="fw-bold text-dark d-block">Yarnell</span>
                     @endif
                     <span class="badge bg-warning text-dark d-block mt-1">
                         <i class="fas fa-map-marker-alt mr-1"></i>{{ $order->location }}
                     </span>
                 </td>
                 <td>{{ $order->work_id }}</td>
                 <td>{{ $order->PN }}</td>
                 <td style="font-size: 12px !important; line-height: 1.1; white-space: normal; word-break: break-word;">
                     {{ $order->Part_description }}
                 </td>
                 <td>{{ $order->costumer }}</td>
                 <td>{{ $order->qty }}</td>
                 <td>{{ $order->wo_qty }}</td>
                 <td style="min-width: 120px;">
                     <select class="form-control form-control-sm status-select"
                         style=" font-weight: bold; color: black;" data-id="{{ $order->id }}" data-location="{{ $order->location }}">
                         <option value="pending" {{ strtolower($order->status) === 'Pending' ? 'selected' : '' }}>Pending</option>
                         <option value="waitingformaterial" {{ strtolower($order->status) === 'waitingformaterial' ? 'selected' : '' }}>Wait Material</option>
                         <option value="cutmaterial" {{ strtolower($order->status) === 'cutmaterial' ? 'selected' : '' }}>Cut Material</option>
                         <option value="grinding" {{ strtolower($order->status) === 'grinding' ? 'selected' : '' }}>Grinding</option>
                         <option value="onrack" {{ strtolower($order->status) === 'onrack' ? 'selected' : '' }}>OnRack</option>
                         <option value="programming" {{ strtolower($order->status) === 'programming' ? 'selected' : '' }}>Programming</option>
                         <option value="setup" {{ strtolower($order->status) === 'setup' ? 'selected' : '' }}>SetUp</option>
                         <option value="machining" {{ strtolower($order->status) === 'machining' ? 'selected' : '' }}>Machining</option>
                         <option value="marking" {{ strtolower($order->status) === 'marking' ? 'selected' : '' }}>Marking</option>
                         <option value="deburring" {{ strtolower($order->status) === 'deburring' ? 'selected' : '' }}>Deburring</option>
                         <option value="qa" {{ strtolower($order->status) === 'qa' ? 'selected' : '' }}>QA</option>
                         <option value="outsource" {{ strtolower($order->status) === 'outsource' ? 'selected' : '' }}>OutSource</option>
                         <option value="assembly" {{ strtolower($order->status) === 'assembly' ? 'selected' : '' }}>Assembly</option>
                         <option value="shipping" {{ strtolower($order->status) === 'shipping' ? 'selected' : '' }}>Shipping</option>
                         <option value="sent" {{ strtolower($order->status) === 'sent' ? 'selected' : '' }}>Sent</option>
                         <option value="onhold" {{ strtolower($order->status) === 'onhold' ? 'selected' : '' }}>OnHold</option>
                         <option value="ready" {{ strtolower($order->status) === 'ready' ? 'selected' : '' }}>Ready</option>
                     </select>
                 </td>
                 <td>{{ optional($order->due_date)->format('M-d-y') }}</td>
             </tr>
             @endforeach