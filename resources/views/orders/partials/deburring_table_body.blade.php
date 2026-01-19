 @foreach($ordersDeburring as $order)
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

 $locKey = strtolower(trim((string) ($order->location ?? '')));
 $locKey = preg_replace('/[^a-z0-9]+/', '', $locKey);
 @endphp
 <tr class="{{ $statusClass }} text-nowrap align-middle small" data-status="{{ strtolower((string) $order->status) }}">
     <td>
         <span class="erp-location-pill erp-location-pill--{{ $locKey ?: 'hearst' }}">
             <i class="fas fa-map-marker-alt"></i>{{ $order->location }}
         </span>
         @if ($order->last_location === 'Yarnell')
             <span class="erp-location-note">Yarnell</span>
         @endif
      </td>
     <td>
         <span class="erp-ellipsis-1" title="{{ $order->work_id }}">{{ $order->work_id }}</span>
     </td>
     <td>
         <span class="erp-ellipsis-1" title="{{ $order->PN }}">{{ $order->PN }}</span>
     </td>
     <td style="font-size: 12px !important; line-height: 1.1; white-space: normal; word-break: break-word;">
         <span class="erp-ellipsis-2" title="{{ $order->Part_description }}">{{ $order->Part_description }}</span>
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
     <td>
         <span style="display: none">{{ \Carbon\Carbon::parse($order->due_date)->format('Y-m-d') }}</span>
         {{ \Carbon\Carbon::parse($order->due_date)->format('m/d/Y') }}
     </td>

 </tr>
 @endforeach
