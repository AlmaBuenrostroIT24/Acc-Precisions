<div
    class="d-none"
    data-total-records="{{ $pnOrders->total() }}"
    data-summary-pn="{{ $summary['pn_count'] ?? $pnOrders->total() }}"
    data-summary-costed="{{ $summary['costed_pn_count'] ?? 0 }}"
    data-summary-notes="{{ $summary['notes_pn_count'] ?? 0 }}"
    data-summary-latest-due="{{ !empty($summary['latest_costing_date']) ? \Carbon\Carbon::parse($summary['latest_costing_date'])->format('M-d-Y') : 'N/A' }}"
    data-filter-with-costing="{{ !empty($withCosting) ? '1' : '0' }}"
    data-filter-with-notes="{{ !empty($withNotes) ? '1' : '0' }}"
></div>

<div class="table-responsive position-relative fai-table-shell">
    <table id="pnBreakdownTable" class="table table-sm table-hover align-middle w-100 fai-dt-table fai-summary-table">
        <thead>
            <tr>
                <th style="width: 70px;">Open</th>
                <th>PN</th>
                <th>Total orders</th>
                <th>Customers</th>
                <th>Latest due date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pnOrders as $pnOrder)
                @php($detailId = 'pn-detail-' . \Illuminate\Support\Str::slug($pnOrder->pn . '-' . $loop->index . '-' . $pnOrders->currentPage()))
                <tr class="{{ !empty($pnOrder->has_costing) ? 'costing-row-has-costing' : '' }}">
                    <td class="costing-open-cell">
                        <button
                            class="btn btn-sm costing-toggle-btn toggle-detail"
                            type="button"
                            data-target="#{{ $detailId }}"
                            aria-expanded="false"
                            title="Show order details"
                        >
                            <i class="fas fa-chevron-down label-show"></i>
                            <i class="fas fa-chevron-up label-hide d-none"></i>
                        </button>
                    </td>
                    <td class="costing-pn-cell">
                        <strong>{{ $pnOrder->pn }}</strong>
                    </td>
                    <td class="text-center">
                        <span class="costing-badge">{{ $pnOrder->total_orders }}</span>
                        @if($pnOrder->notes_count > 0)
                            <span
                                class="costing-note-trigger js-costing-notes-trigger ml-2"
                                data-pn="{{ $pnOrder->pn }}"
                                data-notes='@json($pnOrder->notes_items)'
                            >
                                {{ $pnOrder->notes_count }} note{{ $pnOrder->notes_count === 1 ? '' : 's' }}
                            </span>
                        @endif
                    </td>
                    <td>
                        {{ $pnOrder->customer_summary ?: 'N/A' }}
                        @if($pnOrder->customer_count > 2)
                            <span class="costing-muted">+{{ $pnOrder->customer_count - 2 }} more</span>
                        @endif
                    </td>
                    <td>
                        @if($pnOrder->latest_due_date)
                            <span class="costing-due-pill">{{ \Carbon\Carbon::parse($pnOrder->latest_due_date)->format('M-d-Y') }}</span>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                </tr>
                <tr id="{{ $detailId }}" class="pn-detail-row costing-detail-row d-none">
                    <td colspan="5" class="p-3">
                        @php($hasKitGroups = collect($pnOrder->display_groups ?? [])->contains(fn ($group) => ($group->type ?? null) === 'kit'))
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered bg-white mb-0 costing-detail-table {{ $hasKitGroups ? 'has-tree-column' : 'no-tree-column' }}">
                                <thead>
                                    <tr>
                                        @if($hasKitGroups)
                                            <th>tree</th>
                                        @endif
                                        <th>work_id</th>
                                        <th>co</th>
                                        <th>cust_po</th>
                                        <th>pn</th>
                                        <th>Part_description</th>
                                        <th>customer</th>
                                        <th class="costing-col-num">qty</th>
                                        <th class="costing-col-num">wo_qty</th>
                                        <th>operation</th>
                                        <th>Setup</th>
                                        <th class="costing-col-num">sale price</th>
                                        <th class="costing-col-num">total cost</th>
                                        <th class="costing-col-num">difference</th>
                                        <th class="costing-col-num">cost pcs</th>
                                        <th>due_date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pnOrder->display_groups ?? collect() as $displayGroupIndex => $displayGroup)
                                        @php($ordersToRender = collect([$displayGroup->header])->merge($displayGroup->items))
                                        @php($kitGroupId = $detailId . '-kit-' . $displayGroupIndex)
                                        @foreach($ordersToRender as $orderIndex => $order)
                                            @php($isKitHeader = $displayGroup->type === 'kit' && $displayGroup->header && $order->id === $displayGroup->header->id)
                                            @php($isKitChild = $displayGroup->type === 'kit' && !$isKitHeader)
                                            <tr
                                                class="{{ $isKitHeader ? 'costing-kit-header-row costing-kit-toggle-row is-open' : ($isKitChild ? 'costing-kit-child-row ' . $kitGroupId : '') }}"
                                                @if($isKitHeader)
                                                    data-kit-target=".{{ $kitGroupId }}"
                                                    aria-expanded="true"
                                                @endif
                                            >
                                                @if($hasKitGroups)
                                                    <td class="costing-tree-cell">
                                                        @if($isKitHeader)
                                                            <button type="button" class="costing-kit-toggle-btn" title="Toggle kit items">
                                                                <span class="costing-kit-tag">KIT</span>
                                                                <span class="costing-kit-chevron">
                                                                    <i class="fas fa-chevron-right"></i>
                                                                </span>
                                                            </button>
                                                        @elseif($isKitChild)
                                                            <span class="costing-kit-branch"><i class="fas fa-level-up-alt fa-rotate-90"></i></span>
                                                        @endif
                                                    </td>
                                                @endif
                                                <td class="costing-workid-cell {{ !empty($order->has_costing) ? 'text-success' : '' }}">
                                                    {{ $order->work_id }}
                                                </td>
                                                <td>{{ $order->co }}</td>
                                                <td>{{ $order->cust_po }}</td>
                                                <td class="costing-pn-detail-cell">{{ $order->pn }}</td>
                                                <td class="costing-description-cell">{{ $order->Part_description }}</td>
                                                <td class="costing-customer-cell">{{ $order->customer }}</td>
                                                <td class="text-right">{{ $order->qty }}</td>
                                                <td class="text-right">{{ $order->wo_qty }}</td>
                                                <td><span class="costing-operation-pill">{{ $order->operation }}</span></td>
                                                <td class="costing-setup-cell">
                                                    <span class="costing-setup-lines">{{ $order->setup_summary ?: 'N/A' }}</span>
                                                </td>
                                                <td class="text-right">${{ number_format((float) ($order->sale_price ?? 0), 2) }}</td>
                                                <td class="text-right">${{ number_format((float) ($order->grandtotal_cost ?? 0), 2) }}</td>
                                                @php($difference = (float) ($order->difference_cost ?? 0))
                                                <td class="text-right">
                                                    <span class="costing-diff-pill {{ $difference >= 0 ? 'is-positive' : 'is-negative' }}">
                                                        ${{ number_format($difference, 2) }}
                                                    </span>
                                                </td>
                                                <td class="text-right">
                                                    <span class="costing-cost-pill">
                                                        ${{ number_format((float) ($order->price_pcs ?? 0), 2) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($order->due_date)
                                                        {{ \Illuminate\Support\Str::ucfirst(str_replace('.', '', \Carbon\Carbon::parse($order->due_date)->locale('es')->translatedFormat('M-d-Y'))) }}
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <div class="costing-detail-actions">
                                                        <a
                                                            href="{{ route('costing.pdfSheet', $order->id) }}"
                                                            target="_blank"
                                                            class="btn btn-sm btn-erp-primary erp-table-btn costing-edit-btn costing-action-pdf"
                                                            title="Open costing PDF"
                                                        >
                                                            <i class="fas fa-print"></i>
                                                        </a>
                                                        <a
                                                            href="{{ route('costing.edit', $order->id) }}"
                                                            class="btn btn-sm btn-erp-primary erp-table-btn costing-edit-btn costing-action-edit"
                                                            title="Edit costing"
                                                        >
                                                            <i class="fas fa-pen"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No PN records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="costing-pagination-bar">
    <div class="costing-results-summary">
        Showing {{ $pnOrders->firstItem() ?? 0 }} to {{ $pnOrders->lastItem() ?? 0 }} of {{ $pnOrders->total() }} PN
    </div>
    <div class="costing-pagination">
        {{ $pnOrders->links('pagination::bootstrap-4') }}
    </div>
</div>
