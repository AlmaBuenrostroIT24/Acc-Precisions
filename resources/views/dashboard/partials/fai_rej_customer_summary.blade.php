@if($totals->isEmpty())
    <span class="text-muted">No customer totals available.</span>
@else
    <div class="fai-customer-summary__chips">
        <button type="button" class="fai-customer-chip is-active" data-customer-filter="">
            <span class="fai-customer-chip__name">All</span>
        </button>
        @foreach($totals as $name => $count)
            <button type="button" class="fai-customer-chip" data-customer-filter="{{ $name }}">
                <span class="fai-customer-chip__name">{{ $name }}</span>
                <span class="fai-customer-chip__count">{{ (int) $count }}</span>
            </button>
        @endforeach
    </div>
@endif
