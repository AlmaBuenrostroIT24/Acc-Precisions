<div class="d-none" data-total-records="{{ $pnOrders->total() }}"></div>

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
                    <td class="text-center">
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
                    <td>
                        <strong>{{ $pnOrder->pn }}</strong>
                    </td>
                    <td>
                        <span class="costing-badge">{{ $pnOrder->total_orders }}</span>
                    </td>
                    <td>
                        {{ $pnOrder->customer_summary ?: 'N/A' }}
                        @if($pnOrder->customer_count > 2)
                            <span class="costing-muted">+{{ $pnOrder->customer_count - 2 }} more</span>
                        @endif
                    </td>
                    <td>{{ $pnOrder->latest_due_date ? \Carbon\Carbon::parse($pnOrder->latest_due_date)->format('Y-m-d') : 'N/A' }}</td>
                </tr>
                <tr id="{{ $detailId }}" class="pn-detail-row costing-detail-row d-none">
                    <td colspan="5" class="p-3">
                        <div class="costing-detail-panel">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered bg-white mb-0 costing-detail-table">
                                    <thead>
                                        <tr>
                                            <th>work_id</th>
                                            <th>co</th>
                                            <th>cust_po</th>
                                            <th>pn</th>
                                            <th>Part_description</th>
                                            <th>customer</th>
                                            <th class="text-right">qty</th>
                                            <th class="text-right">wo_qty</th>
                                            <th>operation</th>
                                            <th class="text-right">sale price</th>
                                            <th class="text-right">grandtotal cost</th>
                                            <th class="text-right">difference</th>
                                            <th class="text-right">cost pcs</th>
                                            <th>due_date</th>
                                            <th>Edit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pnOrder->orders as $order)
                                            <tr>
                                                <td class="{{ !empty($order->has_costing) ? 'text-success font-weight-bold' : '' }}">
                                                    {{ $order->work_id }}
                                                </td>
                                                <td>{{ $order->co }}</td>
                                                <td>{{ $order->cust_po }}</td>
                                                <td>{{ $order->pn }}</td>
                                                <td class="costing-description-cell">{{ $order->Part_description }}</td>
                                                <td>{{ $order->customer }}</td>
                                                <td class="text-right">{{ $order->qty }}</td>
                                                <td class="text-right">{{ $order->wo_qty }}</td>
                                                <td>{{ $order->operation }}</td>
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
                                                <td>{{ $order->due_date ? \Carbon\Carbon::parse($order->due_date)->format('Y-m-d') : '' }}</td>
                                                <td class="text-center">
                                                    <a
                                                        href="{{ route('costing.edit', $order->id) }}"
                                                        class="btn btn-sm btn-erp-primary erp-table-btn costing-edit-btn"
                                                        title="Edit costing"
                                                    >
                                                        <i class="fas fa-pen"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
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

<div class="costing-pagination">
    {{ $pnOrders->links('pagination::bootstrap-4') }}
</div>
