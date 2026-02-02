@extends('adminlte::page')

@section('title', 'Edit NCAR')

@section('css')
  <style>
    .ncar-edit-page,
    .ncar-edit-page * {
      font-size: 14px !important;
    }

    .ncar-edit-page .card {
      border-radius: 12px;
      border: 1px solid rgba(15, 23, 42, 0.12);
      box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
    }

    .ncar-edit-page .card-header {
      border-bottom: 1px solid rgba(15, 23, 42, 0.10);
      background: linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%);
      color: #0f172a;
      font-weight: 800;
      letter-spacing: .02em;
    }

    .ncar-edit-page .erp-ro-badge {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      padding: 2px 10px;
      border-radius: 999px;
      border: 1px solid rgba(148, 163, 184, 0.55);
      background: rgba(148, 163, 184, 0.12);
      color: #475569;
      font-weight: 800;
      font-size: 0.82rem !important;
      line-height: 1;
      white-space: nowrap;
    }

    .ncar-edit-page .erp-ro-badge i {
      font-size: 0.9rem !important;
    }

    .ncar-edit-page .form-group label {
      font-weight: 700;
      color: rgba(15, 23, 42, 0.80);
      margin-bottom: .35rem;
    }

    .ncar-edit-page .form-control,
    .ncar-edit-page .custom-select,
    .ncar-edit-page select.form-control {
      border: 1px solid #c5c9d2;
      border-radius: 10px;
      padding: 6px 10px;
      background: #fff;
      box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.06);
      color: #0f172a;
      font-weight: 600;
      height: 34px;
      line-height: 1.2;
    }

    .ncar-edit-page textarea.form-control {
      height: auto;
      min-height: 34px;
      font-weight: 500;
    }

    /* Info (solo lectura): que se note que NO es editable */
    .ncar-edit-page .form-control[readonly],
    .ncar-edit-page textarea.form-control[readonly],
    .ncar-edit-page .form-control:disabled,
    .ncar-edit-page textarea.form-control:disabled {
      background: rgba(241, 245, 249, 0.9) !important;
      color: #0f172a !important;
      box-shadow: none !important;
      cursor: not-allowed;
    }

    .ncar-edit-page .form-control:focus {
      border-color: #94a3b8;
      box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.25);
      outline: none;
    }

    .ncar-edit-page .btn-group-sm > .btn,
    .ncar-edit-page .btn-sm {
      border-radius: 10px;
      font-weight: 700;
    }

    .ncar-edit-page .alert {
      border-radius: 12px;
      border: 1px solid rgba(16, 185, 129, 0.25);
    }
  </style>
@endsection

@section('content_header')
  <div class="d-flex align-items-center justify-content-between">
    <h1 class="mb-0">
      <i class="fas fa-edit text-primary mr-2"></i>
      Edit NCAR: {{ $ncar->ncar_no ?? $ncar->id }}
    </h1>
    <div class="d-flex" style="gap:.5rem">
      <a href="{{ route('nonconformance.ncarparts') }}" class="btn btn-light btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
      </a>
      <a href="{{ route('nonconformance.ncar.pdf', ['id' => $ncar->id]) }}" target="_blank" rel="noopener" class="btn btn-danger btn-sm">
        <i class="fas fa-file-pdf mr-1"></i> PDF
      </a>
    </div>
  </div>
@endsection

@section('content')
  <div class="ncar-edit-page">
  @if(session('status'))
    <div class="alert alert-success py-2 px-3">{{ session('status') }}</div>
  @endif

  @php
    $readonly = collect(['id','ncar_no','order_id','ncartype_id','created_at','updated_at','ncar_customer'])->map(fn($v)=>strtolower($v))->all();

    $preferredOrder = [
      'status','stage','location','ref','ncar_customer',
      'nc_description','disposition',
      'contact','class','desc',
      'delqty','rejqty','qty','stkqty',
      'jobpktcopy','travinsp','samplecompl','isuueprcs','spprocsinvld',
      'rcdrby','discrepancy',
      'contaimentreq','containmentreq','containment',
      'relevantfunction','issuefoundbt','reqrootcause','rootcause','noterpreroot',
      'personnelaccounts','personnelinvolved','processaffected',
      'corrective','verification',
      'date','ncar_date','dateissue',
    ];

    $cols = collect($columns ?? [])->map(fn($c)=> (string)$c)->values();
    $editableCols = $cols->filter(fn($c)=> !in_array(strtolower($c), $readonly, true))->values();

    $ordered = collect($preferredOrder)
      ->filter(fn($c)=> $editableCols->contains($c))
      ->merge($editableCols->reject(fn($c)=> in_array($c, $preferredOrder, true)))
      ->values();

    $labelFor = function(string $col): string {
      $map = [
        'ncar_no' => 'NCAR Number',
        'ncar_customer' => 'Customer',
        'nc_description' => 'Description',
      ];
      if (isset($map[$col])) return $map[$col];
      $s = str_replace('_', ' ', $col);
      return ucwords($s);
    };

    $isLongText = function(string $col): bool {
      return in_array($col, [
        'nc_description','disposition','discrepancy','rootcause','corrective','verification','containment',
        'noterpreroot','personnelaccounts','personnelinvolved','processaffected','relevantfunction','issuefoundbt','reqrootcause',
      ], true);
    };

    $isDate = function(string $col): bool {
      return str_contains(strtolower($col), 'date');
    };

    $normalizeDateValue = function($v): string {
      $s = trim((string)($v ?? ''));
      if ($s === '') return '';
      // Si viene con hora, tomar solo YYYY-MM-DD
      return strlen($s) >= 10 ? substr($s, 0, 10) : $s;
    };
  @endphp

  <div class="row">
    {{-- Izquierda: datos NO editables --}}
    <div class="col-lg-5">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <strong>Info</strong>
          <span class="erp-ro-badge" title="Estos campos no se pueden editar">
            <i class="fas fa-lock"></i> No editable
          </span>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label>NCAR Number</label>
                <input type="text" class="form-control form-control-sm" value="{{ $ncar->ncar_no }}" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Type</label>
                <input type="text" class="form-control form-control-sm" value="{{ $ncar->type_name }}" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Created</label>
                <input type="text" class="form-control form-control-sm" value="{{ $ncar->created_at }}" readonly>
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
                <label>Order Customer</label>
                <input type="text" class="form-control form-control-sm" value="{{ $ncar->order_customer ?? $ncar->ncar_customer }}" readonly>
              </div>
            </div>
          </div>

          <hr class="my-2">

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Work ID</label>
                <input type="text" class="form-control form-control-sm" value="{{ $ncar->work_id }}" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>PN</label>
                <input type="text" class="form-control form-control-sm" value="{{ $ncar->PN }}" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>CO</label>
                <input type="text" class="form-control form-control-sm" value="{{ $ncar->co }}" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>PO</label>
                <input type="text" class="form-control form-control-sm" value="{{ $ncar->cust_po }}" readonly>
              </div>
            </div>
          </div>

          @if(!empty($ncar->Part_description))
            <div class="form-group mb-0">
              <label>Part Description</label>
              <textarea class="form-control form-control-sm" rows="2" readonly>{{ $ncar->Part_description }}</textarea>
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- Derecha: datos editables --}}
    <div class="col-lg-7">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <strong>Edit</strong>
          <a href="{{ route('nonconformance.ncar.pdf', ['id' => $ncar->id]) }}" target="_blank" rel="noopener" class="btn btn-danger btn-sm">
            <i class="fas fa-file-pdf mr-1"></i> PDF
          </a>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('nonconformance.ncar.update', ['id' => $ncar->id]) }}">
            @csrf
            @method('PUT')

            <div class="row">
              @foreach($ordered as $col)
                @php
                  $key = (string) $col;
                  $val = old($key, $ncar->{$key} ?? null);
                  $isLong = $isLongText($key);
                  $isDateCol = $isDate($key);
                @endphp

                @if($isLong)
                  <div class="col-12">
                    <div class="form-group">
                      <label>{{ $labelFor($key) }}</label>
                      <textarea name="{{ $key }}" rows="4" class="form-control form-control-sm @error($key) is-invalid @enderror">{{ old($key, $ncar->{$key} ?? '') }}</textarea>
                      @error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                  </div>
                @else
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>{{ $labelFor($key) }}</label>
                      @if($key === 'status')
                        <select name="status" class="form-control form-control-sm @error('status') is-invalid @enderror">
                          @php($s = strtolower((string)($val ?? 'new')))
                          <option value="New" {{ $s === 'new' ? 'selected' : '' }}>New</option>
                          <option value="Quality Review" {{ $s === 'quality review' ? 'selected' : '' }}>Quality Review</option>
                          <option value="Engineering Review" {{ $s === 'engineering review' ? 'selected' : '' }}>Engineering Review</option>
                          <option value="Closed" {{ $s === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                      @else
                        <input
                          name="{{ $key }}"
                          type="{{ $isDateCol ? 'date' : 'text' }}"
                          class="form-control form-control-sm @error($key) is-invalid @enderror"
                          value="{{ $isDateCol ? $normalizeDateValue($val) : (string)($val ?? '') }}"
                        >
                        @error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror
                      @endif
                    </div>
                  </div>
                @endif
              @endforeach
            </div>

            <div class="d-flex justify-content-end" style="gap:.5rem">
              <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-save mr-1"></i> Save
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
