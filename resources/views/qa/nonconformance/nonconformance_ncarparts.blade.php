<!-- resources/views/orders/index_schedule.blade.php -->
@extends('adminlte::page')

@section('title', 'FAI Summary')

@section('content_header')
  <div class="d-flex align-items-center justify-content-between">
    <h1 class="mb-0">
      <i class="fas fa-exclamation-triangle text-danger mr-2"></i>
      Non-Conformance Reports
    </h1>
    <div>
      <a href="#" class="btn btn-dark btn-sm">
        <i class="fas fa-plus mr-1"></i> NCR
      </a>
    </div>
  </div>
@endsection


@section('content')


{{-- Tabs --}}




  <div class="row">
    <div class="col-lg-3">
      <div class="card kpi-card kpi-new mb-3">
        <div class="card-body d-flex align-items-center">
          <div class="kpi-icon mr-3"><i class="fas fa-info-circle"></i></div>
          <div><div class="small text-muted">New</div><div class="h3 mb-0" id="kpiNew">0</div></div>
        </div>
      </div>
      <div class="card kpi-card kpi-qa mb-3">
        <div class="card-body d-flex align-items-center">
          <div class="kpi-icon mr-3"><i class="fas fa-user"></i></div>
          <div><div class="small text-muted">Quality Review</div><div class="h3 mb-0" id="kpiQA">0</div></div>
        </div>
      </div>
      <div class="card kpi-card kpi-eng mb-3">
        <div class="card-body d-flex align-items-center">
          <div class="kpi-icon mr-3"><i class="fas fa-wrench"></i></div>
          <div><div class="small text-muted">Engineering Review</div><div class="h3 mb-0" id="kpiEng">0</div></div>
        </div>
      </div>
    </div>

    <div class="col-lg-9">
      <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
          <strong>Total By Cause</strong>
          <span class="h5 mb-0" id="kpiTotalCause">0</span>
        </div>
        <div class="card-body">
          <canvas id="chartByCause" height="110"></canvas>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
          <strong>Total</strong>
          <button class="btn btn-sm btn-light" disabled>Total</button>
        </div>
        <div class="card-body">
          <canvas id="chartTrend" height="110"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body pb-0">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="input-group" style="max-width:420px">
          <div class="input-group-prepend">
            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
          </div>
          <input id="tableSearch" type="text" class="form-control" placeholder="Search…">
        </div>
        <button class="btn btn-light" id="btnFilter"><i class="fas fa-filter mr-1"></i> Filter</button>
      </div>
    </div>
    <div class="table-responsive">
      <table id="ncrTable" class="table table-striped table-hover table-sm mb-0">
        <thead class="thead-light">
          <tr>
            <th>Number</th><th>Title</th><th>Created</th><th>Customers</th>
            <th>Reference Numbers</th><th>Type</th><th>Parts</th><th>Status</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>




<!--  {{-- Tab: By End Schedule --}}-->

@endsection



@section('css')
<!-- CSS complementario (puedes ponerlo en tu .css) -->
<style>
  .kpi-card { border: 0; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.05); }
  .kpi-icon { width: 42px; height: 42px; border-radius: 10px; display:flex; align-items:center; justify-content:center; font-size:18px; }
  .kpi-new .kpi-icon { background: #e8f6e8; color:#2e7d32; }
  .kpi-qa  .kpi-icon { background: #fff1e5; color:#ef6c00; }
  .kpi-eng .kpi-icon { background: #efe9ff; color:#6a1b9a; }
  .badge-status { border-radius: 999px; padding: .35rem .6rem; font-weight: 600; }
  .badge-closed { background: #e9f5ee; color:#1b5e20; }
  .badge-open   { background: #fff3f3; color:#b71c1c; }
</style>
@endsection


@push('js')

<script>
$(function(){
  // ===== DataTable =====
  const dt = $('#ncrTable').DataTable({
    ajax: '{{ route('nonconformance.data') }}',
    responsive: true,
    deferRender: true,
    pageLength: 10,
    order: [[0,'desc']],
    columns: [
      {data:'number'},
      {data:'title'},
      {data:'created', render:d=> d? new Date(d).toLocaleDateString():''},
      {data:'customer'},
      {data:'ref_numbers'},
      {data:'type'},
      {data:'parts'},
      {data:'status', render:s=>{
        const closed = String(s).toLowerCase()==='closed';
        return `<span class="badge badge-status ${closed?'badge-closed':'badge-open'}">${s}</span>`
      }}
    ]
  });
  $('#tableSearch').on('keyup', function(){ dt.search(this.value).draw(); });

  // ===== Charts =====
  let causeChart, trendChart;

  function gradient(ctx, top, bottom){
    const g = ctx.createLinearGradient(0,0,0,180);
    g.addColorStop(0, top); g.addColorStop(1, bottom); return g;
  }

  function initCharts(p){
    $('#kpiNew').text(p.kpis?.new ?? 0);
    $('#kpiQA').text(p.kpis?.quality_review ?? 0);
    $('#kpiEng').text(p.kpis?.engineering_review ?? 0);
    $('#kpiTotalCause').text((p.by_cause||[]).reduce((a,c)=>a+(c.value||0),0));

    const ctx1 = document.getElementById('chartByCause').getContext('2d');
    causeChart?.destroy();
    causeChart = new Chart(ctx1,{
      type:'bar',
      data:{
        labels:(p.by_cause||[]).map(i=>i.label),
        datasets:[{label:'Total', data:(p.by_cause||[]).map(i=>i.value),
          backgroundColor: gradient(ctx1,'rgba(96,165,250,.9)','rgba(96,165,250,.25)'), borderRadius:10}]
      },
      options:{plugins:{legend:{display:false}},scales:{x:{grid:{display:false}},y:{beginAtZero:true,ticks:{precision:0}}}}
    });

    const ctx2 = document.getElementById('chartTrend').getContext('2d');
    trendChart?.destroy();
    trendChart = new Chart(ctx2,{
      type:'line',
      data:{
        labels:(p.trend||[]).map(i=>new Date(i.x).toLocaleDateString(undefined,{month:'2-digit',day:'2-digit'})),
        datasets:[{label:'Total', data:(p.trend||[]).map(i=>i.y), fill:true, tension:.35,
          backgroundColor: gradient(ctx2,'rgba(124,58,237,.55)','rgba(124,58,237,.08)'),
          borderColor:'rgba(124,58,237,.8)', pointRadius:0}]
      },
      options:{plugins:{legend:{display:false}},scales:{x:{grid:{display:false}},y:{beginAtZero:true,ticks:{precision:0}}}}
    });
  }

  fetch('{{ route('nonconformance.stats') }}')
    .then(r=>r.json()).then(initCharts).catch(console.error);
});
</script>

@endpush