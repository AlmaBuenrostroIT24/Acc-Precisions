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

  .ncar-edit-page .form-group {
    margin-bottom: .65rem;
  }

  .ncar-edit-page .form-control,
  .ncar-edit-page .custom-select,
  .ncar-edit-page select.form-control {
    border: 1px solid #d6dbe3;
    border-radius: 12px;
    padding: 8px 12px;
    background: #f8fafc;
    box-shadow: none;
    color: #0f172a;
    font-weight: 600;
    height: 40px;
    line-height: 1.15;
  }

  .ncar-edit-page textarea.form-control {
    height: auto;
    min-height: 40px;
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

  /* Editable section inputs: keep them white (not gray) */
  .ncar-edit-page .erp-section .form-control,
  .ncar-edit-page .erp-section .custom-select,
  .ncar-edit-page .erp-section select.form-control {
    background: #fff;
  }

  .ncar-edit-page .form-control:focus {
    border-color: #94a3b8;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(148, 163, 184, 0.25);
    outline: none;
  }

  .ncar-edit-page .btn-group-sm>.btn,
  .ncar-edit-page .btn-sm {
    border-radius: 10px;
    font-weight: 700;
  }

  .ncar-edit-page .alert {
    border-radius: 12px;
    border: 1px solid rgba(16, 185, 129, 0.25);
  }

  .ncar-edit-page .section-meta {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    color: #64748b;
    font-weight: 800;
    font-size: 0.85rem !important;
    white-space: nowrap;
  }

  /* ERP layout helpers */
  .ncar-edit-page {
    background: #f4f6f9;
    padding: 8px 0 18px;
  }

  .ncar-edit-page .erp-sticky {
    position: sticky;
    top: 12px;
  }

  .ncar-edit-page .erp-nav .list-group-item {
    border: 0;
    border-bottom: 1px solid rgba(15, 23, 42, 0.08);
    padding: .45rem .6rem;
    font-weight: 800;
    color: #0f172a;
    font-size: 0.9rem !important;
  }

  .ncar-edit-page .erp-nav .list-group-item.active {
    background: rgba(59, 130, 246, 0.12);
    color: #0f172a;
  }

  .ncar-edit-page .erp-section {
    border-radius: 12px;
    margin-bottom: 12px;
  }

  .ncar-edit-page .erp-section .card-header {
    background: #fff;
    border-bottom: 1px solid rgba(15, 23, 42, 0.08);
  }

  .ncar-edit-page .erp-section .card-body {
    padding-top: .75rem;
    padding-bottom: .25rem;
  }

  /* Scroll only the sections area when using the sticky "Sections" nav */
  .ncar-edit-page .erp-sections-scroll {
    max-height: calc(100vh - 320px);
    overflow-y: auto;
    overscroll-behavior: contain;
    padding-right: 4px;
    scroll-behavior: smooth;
    scroll-padding-top: 12px;
  }

  .ncar-edit-page .erp-section {
    scroll-margin-top: 12px;
  }

  .ncar-edit-page .erp-actions {
    position: sticky;
    bottom: 0;
    z-index: 20;
  }
</style>
@endsection


@section('content')
<div class="ncar-edit-page">
  @if(session('status'))
  <div class="alert alert-success py-2 px-3">{{ session('status') }}</div>
  @endif

  <div class="row">
    {{-- Izquierda: datos NO editables --}}
    <div class="col-xl-3 col-lg-4">
      <div class="card erp-sticky">
        <div class="card-header d-flex align-items-center justify-content-between">
          <strong>Info</strong>
          <h1 class="mb-0">
            NCAR: {{ $ncar->ncar_no ?? $ncar->id }}
          </h1>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-6">
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
            <div class="col-md-6">
              <div class="form-group">
                <label>NCAR Date</label>
                <input type="text" class="form-control form-control-sm" value="{{ $ncar->ncar_date ?? $ncar->date ?? '' }}" readonly>
              </div>
            </div>

            <div class="col-6">
              <div class="form-group">
                <label>Contact</label>
                <input type="text" class="form-control form-control-sm" value="{{ $ncar->contact ?? $ncar->ncar_contact ?? '' }}" readonly>
              </div>
            </div>
          </div>

          <hr class="my-2">

          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label>Order Customer</label>
                <input type="text" class="form-control form-control-sm" value="{{ $ncar->order_customer ?? $ncar->ncar_customer }}" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Work ID</label>
                <input type="text" class="form-control form-control-sm" value="{{ $ncar->work_id }}" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Operation</label>
                <input type="text" class="form-control form-control-sm" value="{{ $ncar->order_operation ?? $ncar->operation ?? '' }}" readonly>
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
                <label>WO Qty</label>
                <input type="text" class="form-control form-control-sm" value="{{ $ncar->wo_qty ?? '' }}" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>PO</label>
                <input type="text" class="form-control form-control-sm" value="{{ $ncar->cust_po }}" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>CO Qty</label>
                <input type="text" class="form-control form-control-sm" value="{{ $ncar->order_qty ?? '' }}" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>CO</label>
                <input type="text" class="form-control form-control-sm" value="{{ $ncar->co }}" readonly>
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

    {{-- Derecha: 1 contenedor editable --}}
    <div class="col-xl-9 col-lg-8">
      <form method="POST" action="{{ route('nonconformance.ncar.update', ['id' => $ncar->id]) }}">
        @csrf
        @method('PUT')

        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Edit</strong>
            <a href="{{ route('nonconformance.ncar.pdf', ['id' => $ncar->id]) }}" target="_blank" rel="noopener" class="btn btn-danger btn-sm">
              <i class="fas fa-file-pdf mr-1"></i> PDF
            </a>
            <div class="d-flex" style="gap:.5rem">
              <a href="{{ route('nonconformance.ncarparts') }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Back
              </a>
            </div>
          </div>
          <div class="card-body">
            @php
            $statusVal = strtolower((string) old('status', $ncar->status ?? 'New'));
            $containReqKey = in_array('containmentreq', $columns ?? [], true) ? 'containmentreq' : 'contaimentreq';
            @endphp

            <div class="row">
              <div class="col-lg-2 mb-3">
                <div class="card erp-nav erp-sticky">
                  <div class="card-header py-2">
                    <strong>Sections</strong>
                  </div>
                  <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                      <a class="list-group-item list-group-item-action" href="#sec-other">Other</a>
                      <a class="list-group-item list-group-item-action" href="#sec-general">General</a>
                      <a class="list-group-item list-group-item-action" href="#sec-issue">Issue / Disposition</a>
                      <a class="list-group-item list-group-item-action" href="#sec-containment">Containment</a>
                      <a class="list-group-item list-group-item-action" href="#sec-root">Root Cause</a>
                      <a class="list-group-item list-group-item-action" href="#sec-personnel">Personnel / Process</a>
                      <a class="list-group-item list-group-item-action" href="#sec-corrective">Corrective / Verification</a>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-lg-10">
                <div class="erp-sections-scroll" id="erpSectionsScroll">

                <div id="sec-other" class="card erp-section">
                  <div class="card-header py-2">
                    <strong>Other</strong>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-lg-10">
                        <div class="row">
                          <div class="col-md-1">
                            <div class="form-group">
                              <label>Delivery Qty</label>
                              <input name="delqty" type="number" step="any" class="form-control form-control-sm {{ $errors->has('delqty') ? 'is-invalid' : '' }}" value="{{ old('delqty', $ncar->delqty ?? '') }}">
                              @if($errors->has('delqty'))<div class="invalid-feedback">{{ $errors->first('delqty') }}</div>@endif
                            </div>
                          </div>

                          <div class="col-md-1">
                            <div class="form-group">
                              <label>Reject Qty</label>
                              <input name="rejqty" type="number" step="any" class="form-control form-control-sm {{ $errors->has('rejqty') ? 'is-invalid' : '' }}" value="{{ old('rejqty', $ncar->rejqty ?? '') }}">
                              @if($errors->has('rejqty'))<div class="invalid-feedback">{{ $errors->first('rejqty') }}</div>@endif
                            </div>
                          </div>

                          <div class="col-md-1">
                            <div class="form-group">
                              <label>Stock Qty</label>
                              <input name="stkqty" type="number" step="any" class="form-control form-control-sm {{ $errors->has('stkqty') ? 'is-invalid' : '' }}" value="{{ old('stkqty', $ncar->stkqty ?? '') }}">
                              @if($errors->has('stkqty'))<div class="invalid-feedback">{{ $errors->first('stkqty') }}</div>@endif
                            </div>
                          </div>

                          <div class="col-md-2">
                            <div class="form-group">
                              <label>¿Job Packet Copy?</label>
                              @php
                              $jobPktCopyRaw = old('jobpktcopy', $ncar->jobpktcopy ?? '0');
                              $jobPktCopyNorm = strtolower(trim((string) $jobPktCopyRaw));
                              $jobPktCopyVal = in_array($jobPktCopyNorm, ['1', 'true', 'yes', 'y', 'on'], true) ? '1' : '0';
                              @endphp
                              <select name="jobpktcopy" class="form-control form-control-sm {{ $errors->has('jobpktcopy') ? 'is-invalid' : '' }}">
                                <option value="1" {{ $jobPktCopyVal === '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ $jobPktCopyVal === '0' ? 'selected' : '' }}>No</option>
                              </select>
                              @if($errors->has('jobpktcopy'))<div class="invalid-feedback">{{ $errors->first('jobpktcopy') }}</div>@endif
                            </div>
                          </div>

                          <div class="col-md-2">
                            <div class="form-group">
                              <label>¿Travel & Insp. Completed?</label>
                              @php
                              $travInspRaw = old('travinsp', $ncar->travinsp ?? '0');
                              $travInspNorm = strtolower(trim((string) $travInspRaw));
                              $travInspVal = in_array($travInspNorm, ['1', 'true', 'yes', 'y', 'on'], true) ? '1' : '0';
                              @endphp
                              <select name="travinsp" class="form-control form-control-sm {{ $errors->has('travinsp') ? 'is-invalid' : '' }}">
                                <option value="1" {{ $travInspVal === '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ $travInspVal === '0' ? 'selected' : '' }}>No</option>
                              </select>
                              @if($errors->has('travinsp'))<div class="invalid-feedback">{{ $errors->first('travinsp') }}</div>@endif
                            </div>
                          </div>

                          <div class="col-md-2">
                            <div class="form-group">
                              <label>¿Sample Completed?</label>
                              @php
                              $sampleComplRaw = old('samplecompl', $ncar->samplecompl ?? '0');
                              $sampleComplNorm = strtolower(trim((string) $sampleComplRaw));
                              $sampleComplVal = in_array($sampleComplNorm, ['1', 'true', 'yes', 'y', 'on'], true) ? '1' : '0';
                              @endphp
                              <select name="samplecompl" class="form-control form-control-sm {{ $errors->has('samplecompl') ? 'is-invalid' : '' }}">
                                <option value="1" {{ $sampleComplVal === '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ $sampleComplVal === '0' ? 'selected' : '' }}>No</option>
                              </select>
                              @if($errors->has('samplecompl'))<div class="invalid-feedback">{{ $errors->first('samplecompl') }}</div>@endif
                            </div>
                          </div>

                          <div class="col-md-4">
                            <div class="form-group">
                              <label>Issue Process</label>
                              <input name="isuueprcs" type="text" class="form-control form-control-sm {{ $errors->has('isuueprcs') ? 'is-invalid' : '' }}" value="{{ old('isuueprcs', $ncar->isuueprcs ?? '') }}">
                              @if($errors->has('isuueprcs'))<div class="invalid-feedback">{{ $errors->first('isuueprcs') }}</div>@endif
                            </div>
                          </div>

                          <div class="col-md-4">
                            <div class="form-group">
                              <label>SP Process Invalid</label>
                              <input name="spprocsinvld" type="text" class="form-control form-control-sm {{ $errors->has('spprocsinvld') ? 'is-invalid' : '' }}" value="{{ old('spprocsinvld', $ncar->spprocsinvld ?? '') }}">
                              @if($errors->has('spprocsinvld'))<div class="invalid-feedback">{{ $errors->first('spprocsinvld') }}</div>@endif
                            </div>
                          </div>

                          <div class="col-md-4">
                            <div class="form-group">
                              <label>Rcd By</label>
                              <input name="rcdrby" type="text" class="form-control form-control-sm {{ $errors->has('rcdrby') ? 'is-invalid' : '' }}" value="{{ old('rcdrby', $ncar->rcdrby ?? '') }}">
                              @if($errors->has('rcdrby'))<div class="invalid-feedback">{{ $errors->first('rcdrby') }}</div>@endif
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-2">
                        <div class="row">
                          <div class="col-12">
                            <div class="form-group">
                              <label>Status</label>
                              <select name="status" class="form-control form-control-sm {{ $errors->has('status') ? 'is-invalid' : '' }}">
                                <option value="New" {{ $statusVal === 'new' ? 'selected' : '' }}>New</option>
                                <option value="Quality Review" {{ $statusVal === 'quality review' ? 'selected' : '' }}>Quality Review</option>
                                <option value="Engineering Review" {{ $statusVal === 'engineering review' ? 'selected' : '' }}>Engineering Review</option>
                                <option value="Closed" {{ $statusVal === 'closed' ? 'selected' : '' }}>Closed</option>
                              </select>
                              @if($errors->has('status'))<div class="invalid-feedback">{{ $errors->first('status') }}</div>@endif
                            </div>
                          </div>

                          <div class="col-12">
                            <div class="form-group">
                              <label>Stage</label>
                              <input name="stage" type="text" class="form-control form-control-sm {{ $errors->has('stage') ? 'is-invalid' : '' }}" value="{{ old('stage', $ncar->stage ?? '') }}">
                              @if($errors->has('stage'))<div class="invalid-feedback">{{ $errors->first('stage') }}</div>@endif
                            </div>
                          </div>

                          <div class="col-12">
                            <div class="form-group mb-0">
                              <label>Location</label>
                              <input name="location" type="text" class="form-control form-control-sm {{ $errors->has('location') ? 'is-invalid' : '' }}" value="{{ old('location', $ncar->location ?? '') }}">
                              @if($errors->has('location'))<div class="invalid-feedback">{{ $errors->first('location') }}</div>@endif
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="sec-general" class="card erp-section">
                  <div class="card-header py-2">
                    <strong>General</strong>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-12">
                        <div class="row">
                          <div class="col-md-4">
                            <div class="form-group">
                              <label>Ref</label>
                              <input name="ref" type="text" class="form-control form-control-sm {{ $errors->has('ref') ? 'is-invalid' : '' }}" value="{{ old('ref', $ncar->ref ?? '') }}">
                              @if($errors->has('ref'))<div class="invalid-feedback">{{ $errors->first('ref') }}</div>@endif
                            </div>
                          </div>


                          <div class="col-md-4">
                            <div class="form-group">
                              <label>Desc</label>
                              <input name="desc" type="text" class="form-control form-control-sm {{ $errors->has('desc') ? 'is-invalid' : '' }}" value="{{ old('desc', $ncar->desc ?? '') }}">
                              @if($errors->has('desc'))<div class="invalid-feedback">{{ $errors->first('desc') }}</div>@endif
                            </div>
                          </div>

                          <div class="col-md-4">
                            <div class="form-group">
                              <label>Date</label>
                              <input name="date" type="date" class="form-control form-control-sm {{ $errors->has('date') ? 'is-invalid' : '' }}" value="{{ substr((string) old('date', $ncar->date ?? ''), 0, 10) }}">
                              @if($errors->has('date'))<div class="invalid-feedback">{{ $errors->first('date') }}</div>@endif
                            </div>
                          </div>


                          <div class="col-md-4">
                            <div class="form-group">
                              <label>Date Issue</label>
                              <input name="dateissue" type="date" class="form-control form-control-sm {{ $errors->has('dateissue') ? 'is-invalid' : '' }}" value="{{ substr((string) old('dateissue', $ncar->dateissue ?? ''), 0, 10) }}">
                              @if($errors->has('dateissue'))<div class="invalid-feedback">{{ $errors->first('dateissue') }}</div>@endif
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="sec-issue" class="card erp-section">
                  <div class="card-header py-2">
                    <strong>Issue / Disposition</strong>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      @php
                      $discRaw = (string) old('discrepancy', $ncar->discrepancy ?? '');
                      $discItems = [];
                      $decoded = null;

                      if ($discRaw !== '' && (str_starts_with(ltrim($discRaw), '[') || str_starts_with(ltrim($discRaw), '{'))) {
                      $decoded = json_decode($discRaw, true);
                      }

                      if (is_array($decoded)) {
                      if (array_is_list($decoded)) {
                      foreach ($decoded as $it) {
                      if (!is_array($it)) continue;
                      $discItems[] = [
                      'desc' => (string) ($it['desc'] ?? ''),
                      'qty' => (string) ($it['qty'] ?? ''),
                      ];
                      }
                      } else {
                      $discItems[] = [
                      'desc' => (string) ($decoded['desc'] ?? ''),
                      'qty' => (string) ($decoded['qty'] ?? ''),
                      ];
                      }
                      } elseif (trim($discRaw) !== '') {
                      $discItems[] = ['desc' => $discRaw, 'qty' => ''];
                      }

                      if (empty($discItems)) {
                      $discItems[] = ['desc' => '', 'qty' => ''];
                      }
                      @endphp

                      <div class="col-12">
                        <div class="form-group mb-0">
                          <div class="d-flex align-items-center justify-content-between">
                            <label class="mb-1">Discrepancy</label>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addDiscrepancyBtn">
                              <i class="fas fa-plus mr-1"></i> Add discrepancy
                            </button>
                          </div>

                          {{-- Store as JSON in the existing DB column (qa_ncar.discrepancy) --}}
                          <textarea name="discrepancy" id="discrepancyJson" class="d-none">{{ $discRaw }}</textarea>

                          <div id="discrepanciesWrap">
                            @foreach($discItems as $it)
                            <div class="row discrepancy-row mb-2">
                              <div class="col-md-10">
                                <textarea rows="1" class="form-control form-control-sm discrepancy-desc" placeholder="Description">{{ $it['desc'] }}</textarea>
                              </div>
                              <div class="col-md-2">
                                <div class="d-flex" style="gap:.5rem">
                                  <input type="number" step="any" class="form-control form-control-sm discrepancy-qty" placeholder="Qty" value="{{ $it['qty'] }}">
                                  <button type="button" class="btn btn-outline-danger btn-sm remove-discrepancy" title="Remove">
                                    <i class="fas fa-trash"></i>
                                  </button>
                                </div>
                              </div>
                            </div>
                            @endforeach
                          </div>

                          @if($errors->has('discrepancy'))<div class="invalid-feedback d-block">{{ $errors->first('discrepancy') }}</div>@endif
                        </div>
                      </div>
                      <div class="col-12">
                        <div class="form-group">
                          <label>Description</label>
                          <textarea name="nc_description" rows="4" class="form-control form-control-sm {{ $errors->has('nc_description') ? 'is-invalid' : '' }}">{{ old('nc_description', $ncar->nc_description ?? '') }}</textarea>
                          @if($errors->has('nc_description'))<div class="invalid-feedback">{{ $errors->first('nc_description') }}</div>@endif
                        </div>
                      </div>

                      <div class="col-12">
                        @php
                        $dispRaw = (string) old('disposition', $ncar->disposition ?? '');
                        $dispItems = [];
                        $dispDecoded = null;
                        $dispTrim = trim($dispRaw);
                        if ($dispTrim !== '' && (str_starts_with($dispTrim, '[') || str_starts_with($dispTrim, '{'))) {
                        $dispDecoded = json_decode($dispTrim, true);
                        }

                        $normalizeKey = function ($v): string {
                        return strtolower(trim((string) $v));
                        };

                        if (is_array($dispDecoded)) {
                        if (array_is_list($dispDecoded)) {
                        foreach ($dispDecoded as $it) {
                        if (!is_array($it)) continue;
                        $dispItems[] = [
                        'action' => (string) ($it['action'] ?? $it['type'] ?? $it['desc'] ?? ''),
                        'accqty' => (string) ($it['accqty'] ?? $it['acc_qty'] ?? ''),
                        'rejqty' => (string) ($it['rejqty'] ?? $it['rej_qty'] ?? ''),
                        ];
                        }
                        } else {
                        $dispItems[] = [
                        'action' => (string) ($dispDecoded['action'] ?? $dispDecoded['type'] ?? $dispDecoded['desc'] ?? ''),
                        'accqty' => (string) ($dispDecoded['accqty'] ?? $dispDecoded['acc_qty'] ?? ''),
                        'rejqty' => (string) ($dispDecoded['rejqty'] ?? $dispDecoded['rej_qty'] ?? ''),
                        ];
                        }
                        } elseif ($dispTrim !== '') {
                        $dispItems[] = [
                        'action' => $dispRaw,
                        'accqty' => '',
                        'rejqty' => '',
                        ];
                        }

                        if (empty($dispItems)) {
                        $dispItems[] = ['action' => '', 'accqty' => '', 'rejqty' => ''];
                        }

                        $dispOptions = [
                        'Screen & Rework',
                        'Remake',
                        'Credit',
                        'RTV',
                        'Scrap',
                        'Other',
                        ];
                        @endphp

                        <div class="form-group mb-0">
                          <div class="d-flex align-items-center justify-content-between">
                            <label class="mb-1">Disposition / Correction</label>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addDispositionBtn">
                              <i class="fas fa-plus mr-1"></i> Add
                            </button>
                          </div>

                          <textarea name="disposition" id="dispositionJson" class="d-none">{{ $dispRaw }}</textarea>

                          <div id="dispositionsWrap">
                            @foreach($dispItems as $it)
                            @php
                            $selected = '';
                            $k = $normalizeKey($it['action'] ?? '');
                            foreach ($dispOptions as $opt) {
                            if ($normalizeKey($opt) === $k) {
                            $selected = $opt;
                            break;
                            }
                            }
                            @endphp
                            <div class="row disposition-row mb-2">
                              <div class="col-md-6">
                                <select class="form-control form-control-sm disposition-action">
                                  <option value=""></option>
                                  @foreach($dispOptions as $opt)
                                  <option value="{{ $opt }}" {{ $selected === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                  @endforeach
                                </select>
                              </div>
                              <div class="col-md-2">
                                <input type="number" step="any" class="form-control form-control-sm disposition-accqty" placeholder="Acc Qty" value="{{ $it['accqty'] ?? '' }}">
                              </div>
                              <div class="col-md-2">
                                <input type="number" step="any" class="form-control form-control-sm disposition-rejqty" placeholder="Rej Qty" value="{{ $it['rejqty'] ?? '' }}">
                              </div>
                              <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-disposition" title="Remove">
                                  <i class="fas fa-trash"></i>
                                </button>
                              </div>
                            </div>
                            @endforeach
                          </div>

                          @if($errors->has('disposition'))<div class="invalid-feedback d-block">{{ $errors->first('disposition') }}</div>@endif
                        </div>
                      </div>


                    </div>

                    <script>
                      (function() {
                        var wrap = document.getElementById('dispositionsWrap');
                        var hidden = document.getElementById('dispositionJson');
                        var addBtn = document.getElementById('addDispositionBtn');
                        if (!wrap || !hidden || !addBtn) return;

                        var options = [
                          'Screen & Rework',
                          'Remake',
                          'Credit',
                          'RTV',
                          'Scrap',
                          'Other',
                        ];

                        function normalizeText(value) {
                          return (value == null ? '' : String(value)).trim();
                        }

                        function getRows() {
                          return Array.prototype.slice.call(wrap.querySelectorAll('.disposition-row'));
                        }

                        function syncToHidden() {
                          var items = getRows().map(function(row) {
                            var actionEl = row.querySelector('.disposition-action');
                            var accEl = row.querySelector('.disposition-accqty');
                            var rejEl = row.querySelector('.disposition-rejqty');
                            return {
                              action: normalizeText(actionEl ? actionEl.value : ''),
                              accqty: normalizeText(accEl ? accEl.value : ''),
                              rejqty: normalizeText(rejEl ? rejEl.value : ''),
                            };
                          }).filter(function(it) {
                            return it.action !== '' || it.accqty !== '' || it.rejqty !== '';
                          });

                          if (items.length === 0) {
                            items = [{
                              action: '',
                              accqty: '',
                              rejqty: ''
                            }];
                          }

                          hidden.value = JSON.stringify(items);
                        }

                        function optionsHtml(selected) {
                          var html = '<option value=""></option>';
                          options.forEach(function(opt) {
                            var sel = (selected === opt) ? ' selected' : '';
                            html += '<option value="' + opt.replace(/\"/g, '&quot;') + '"' + sel + '>' + opt + '</option>';
                          });
                          return html;
                        }

                        function addRow(action, accqty, rejqty) {
                          var row = document.createElement('div');
                          row.className = 'row disposition-row mb-2';
                          row.innerHTML =
                            '<div class="col-md-6">' +
                            '<select class="form-control form-control-sm disposition-action">' + optionsHtml(action || '') + '</select>' +
                            '</div>' +
                            '<div class="col-md-2">' +
                            '<input type="number" step="any" class="form-control form-control-sm disposition-accqty" placeholder="Acc Qty" />' +
                            '</div>' +
                            '<div class="col-md-2">' +
                            '<input type="number" step="any" class="form-control form-control-sm disposition-rejqty" placeholder="Rej Qty" />' +
                            '</div>' +
                            '<div class="col-md-2">' +
                            '<button type="button" class="btn btn-outline-danger btn-sm remove-disposition" title="Remove"><i class="fas fa-trash"></i></button>' +
                            '</div>';

                          wrap.appendChild(row);
                          var accEl = row.querySelector('.disposition-accqty');
                          var rejEl = row.querySelector('.disposition-rejqty');
                          if (accEl) accEl.value = accqty || '';
                          if (rejEl) rejEl.value = rejqty || '';
                          syncToHidden();
                        }

                        wrap.addEventListener('input', function(e) {
                          if (!e.target) return;
                          if (
                            e.target.classList.contains('disposition-action') ||
                            e.target.classList.contains('disposition-accqty') ||
                            e.target.classList.contains('disposition-rejqty')
                          ) {
                            syncToHidden();
                          }
                        });

                        wrap.addEventListener('change', function(e) {
                          if (e.target && e.target.classList.contains('disposition-action')) {
                            syncToHidden();
                          }
                        });

                        wrap.addEventListener('click', function(e) {
                          var btn = e.target && (e.target.closest ? e.target.closest('.remove-disposition') : null);
                          if (!btn) return;
                          var row = btn.closest('.disposition-row');
                          if (row) row.remove();
                          if (getRows().length === 0) addRow('', '', '');
                          syncToHidden();
                        });

                        addBtn.addEventListener('click', function() {
                          addRow('', '', '');
                        });

                        syncToHidden();

                        var form = addBtn.closest('form');
                        if (form) {
                          form.addEventListener('submit', function() {
                            syncToHidden();
                          });
                        }
                      })();
                    </script>

                    <script>
                      (function() {
                        var wrap = document.getElementById('discrepanciesWrap');
                        var hidden = document.getElementById('discrepancyJson');
                        var addBtn = document.getElementById('addDiscrepancyBtn');
                        if (!wrap || !hidden || !addBtn) return;

                        function normalizeText(value) {
                          return (value == null ? '' : String(value)).trim();
                        }

                        function getRows() {
                          return Array.prototype.slice.call(wrap.querySelectorAll('.discrepancy-row'));
                        }

                        function syncToHidden() {
                          var items = getRows().map(function(row) {
                            var descEl = row.querySelector('.discrepancy-desc');
                            var qtyEl = row.querySelector('.discrepancy-qty');
                            return {
                              desc: normalizeText(descEl ? descEl.value : ''),
                              qty: normalizeText(qtyEl ? qtyEl.value : ''),
                            };
                          }).filter(function(it) {
                            return it.desc !== '' || it.qty !== '';
                          });

                          if (items.length === 0) {
                            items = [{
                              desc: '',
                              qty: ''
                            }];
                          }

                          hidden.value = JSON.stringify(items);
                        }

                        function addRow(desc, qty) {
                          var row = document.createElement('div');
                          row.className = 'row discrepancy-row mb-2';
                          row.innerHTML =
                            '<div class="col-md-9">' +
                            '<textarea rows="1" class="form-control form-control-sm discrepancy-desc" placeholder="Description"></textarea>' +
                            '</div>' +
                            '<div class="col-md-3">' +
                            '<div class="d-flex" style="gap:.5rem">' +
                            '<input type="number" step="any" class="form-control form-control-sm discrepancy-qty" placeholder="Qty" />' +
                            '<button type="button" class="btn btn-outline-danger btn-sm remove-discrepancy" title="Remove">' +
                            '<i class="fas fa-trash"></i>' +
                            '</button>' +
                            '</div>' +
                            '</div>';

                          wrap.appendChild(row);
                          var descEl = row.querySelector('.discrepancy-desc');
                          var qtyEl = row.querySelector('.discrepancy-qty');
                          if (descEl) descEl.value = desc || '';
                          if (qtyEl) qtyEl.value = qty || '';
                          syncToHidden();
                        }

                        wrap.addEventListener('input', function(e) {
                          if (e.target && (e.target.classList.contains('discrepancy-desc') || e.target.classList.contains('discrepancy-qty'))) {
                            syncToHidden();
                          }
                        });

                        wrap.addEventListener('click', function(e) {
                          var btn = e.target && (e.target.closest ? e.target.closest('.remove-discrepancy') : null);
                          if (!btn) return;
                          var row = btn.closest('.discrepancy-row');
                          if (row) row.remove();
                          if (getRows().length === 0) addRow('', '');
                          syncToHidden();
                        });

                        addBtn.addEventListener('click', function() {
                          addRow('', '');
                        });

                        syncToHidden();

                        var form = addBtn.closest('form');
                        if (form) {
                          form.addEventListener('submit', function() {
                            syncToHidden();
                          });
                        }
                      })();
                    </script>
                  </div>
                </div>

                <div id="sec-containment" class="card erp-section">
                  <div class="card-header py-2">
                    <strong>Containment</strong>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label>Containment Req.?</label>
                          <input name="{{ $containReqKey }}" type="text" class="form-control form-control-sm {{ $errors->has($containReqKey) ? 'is-invalid' : '' }}" value="{{ old($containReqKey, $ncar->{$containReqKey} ?? '') }}">
                          @if($errors->has($containReqKey))<div class="invalid-feedback">{{ $errors->first($containReqKey) }}</div>@endif
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-group mb-0">
                          <label>Containment</label>
                          <textarea name="containment" rows="4" class="form-control form-control-sm {{ $errors->has('containment') ? 'is-invalid' : '' }}">{{ old('containment', $ncar->containment ?? '') }}</textarea>
                          @if($errors->has('containment'))<div class="invalid-feedback">{{ $errors->first('containment') }}</div>@endif
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="sec-root" class="card erp-section">
                  <div class="card-header py-2">
                    <strong>Root Cause</strong>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-12">
                        <div class="form-group">
                          <label>Relevant Function</label>
                          <textarea name="relevantfunction" rows="3" class="form-control form-control-sm {{ $errors->has('relevantfunction') ? 'is-invalid' : '' }}">{{ old('relevantfunction', $ncar->relevantfunction ?? '') }}</textarea>
                          @if($errors->has('relevantfunction'))<div class="invalid-feedback">{{ $errors->first('relevantfunction') }}</div>@endif
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-group">
                          <label>Issue Found By</label>
                          <textarea name="issuefoundbt" rows="2" class="form-control form-control-sm {{ $errors->has('issuefoundbt') ? 'is-invalid' : '' }}">{{ old('issuefoundbt', $ncar->issuefoundbt ?? '') }}</textarea>
                          @if($errors->has('issuefoundbt'))<div class="invalid-feedback">{{ $errors->first('issuefoundbt') }}</div>@endif
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-group">
                          <label>Req Root Cause</label>
                          <textarea name="reqrootcause" rows="2" class="form-control form-control-sm {{ $errors->has('reqrootcause') ? 'is-invalid' : '' }}">{{ old('reqrootcause', $ncar->reqrootcause ?? '') }}</textarea>
                          @if($errors->has('reqrootcause'))<div class="invalid-feedback">{{ $errors->first('reqrootcause') }}</div>@endif
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-group">
                          <label>Root Cause</label>
                          <textarea name="rootcause" rows="4" class="form-control form-control-sm {{ $errors->has('rootcause') ? 'is-invalid' : '' }}">{{ old('rootcause', $ncar->rootcause ?? '') }}</textarea>
                          @if($errors->has('rootcause'))<div class="invalid-feedback">{{ $errors->first('rootcause') }}</div>@endif
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-group mb-0">
                          <label>Note (Pre Root)</label>
                          <textarea name="noterpreroot" rows="3" class="form-control form-control-sm {{ $errors->has('noterpreroot') ? 'is-invalid' : '' }}">{{ old('noterpreroot', $ncar->noterpreroot ?? '') }}</textarea>
                          @if($errors->has('noterpreroot'))<div class="invalid-feedback">{{ $errors->first('noterpreroot') }}</div>@endif
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="sec-personnel" class="card erp-section">
                  <div class="card-header py-2">
                    <strong>Personnel / Process</strong>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-12">
                        <div class="form-group">
                          <label>Personnel Accounts</label>
                          <textarea name="personnelaccounts" rows="3" class="form-control form-control-sm {{ $errors->has('personnelaccounts') ? 'is-invalid' : '' }}">{{ old('personnelaccounts', $ncar->personnelaccounts ?? '') }}</textarea>
                          @if($errors->has('personnelaccounts'))<div class="invalid-feedback">{{ $errors->first('personnelaccounts') }}</div>@endif
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-group">
                          <label>Personnel Involved</label>
                          <textarea name="personnelinvolved" rows="3" class="form-control form-control-sm {{ $errors->has('personnelinvolved') ? 'is-invalid' : '' }}">{{ old('personnelinvolved', $ncar->personnelinvolved ?? '') }}</textarea>
                          @if($errors->has('personnelinvolved'))<div class="invalid-feedback">{{ $errors->first('personnelinvolved') }}</div>@endif
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-group mb-0">
                          <label>Process Affected</label>
                          <textarea name="processaffected" rows="3" class="form-control form-control-sm {{ $errors->has('processaffected') ? 'is-invalid' : '' }}">{{ old('processaffected', $ncar->processaffected ?? '') }}</textarea>
                          @if($errors->has('processaffected'))<div class="invalid-feedback">{{ $errors->first('processaffected') }}</div>@endif
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="sec-corrective" class="card erp-section">
                  <div class="card-header py-2">
                    <strong>Corrective / Verification</strong>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-12">
                        <div class="form-group">
                          <label>Corrective Action</label>
                          <textarea name="corrective" rows="4" class="form-control form-control-sm {{ $errors->has('corrective') ? 'is-invalid' : '' }}">{{ old('corrective', $ncar->corrective ?? '') }}</textarea>
                          @if($errors->has('corrective'))<div class="invalid-feedback">{{ $errors->first('corrective') }}</div>@endif
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-group mb-0">
                          <label>Verification</label>
                          <textarea name="verification" rows="4" class="form-control form-control-sm {{ $errors->has('verification') ? 'is-invalid' : '' }}">{{ old('verification', $ncar->verification ?? '') }}</textarea>
                          @if($errors->has('verification'))<div class="invalid-feedback">{{ $errors->first('verification') }}</div>@endif
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                </div>
              </div>
            </div>
          </div>
          <div class="card-footer bg-white erp-actions" style="border-top:1px solid rgba(15,23,42,.10);">
            <div class="d-flex justify-content-end" style="gap:.5rem">
              <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-save mr-1"></i> Save
              </button>
            </div>
          </div>
        </div>

        <script>
          (function() {
            function initErpSectionsNavScroll() {
              var nav = document.querySelector('.ncar-edit-page .erp-nav');
              var scroller = document.getElementById('erpSectionsScroll');
              if (!nav || !scroller) return;

              var footer = document.querySelector('.ncar-edit-page .erp-actions');

              function setScrollerMaxHeight() {
                var rect = scroller.getBoundingClientRect();
                var footerH = footer ? footer.offsetHeight : 0;
                var available = window.innerHeight - rect.top - footerH - 80;
                if (available < 220) available = 220;
                scroller.style.maxHeight = available + 'px';
              }

              setScrollerMaxHeight();
              window.addEventListener('resize', setScrollerMaxHeight);

              nav.querySelectorAll('a[href^=\"#sec-\"]').forEach(function(a) {
                a.addEventListener('click', function(e) {
                  var href = a.getAttribute('href');
                  if (!href) return;

                  var target = document.querySelector(href);
                  if (!target) return;

                  if (scroller.contains(target)) {
                    e.preventDefault();
                    nav.querySelectorAll('.list-group-item.active').forEach(function(el) {
                      el.classList.remove('active');
                    });
                    a.classList.add('active');
                    target.scrollIntoView({
                      behavior: 'smooth',
                      block: 'start'
                    });
                  }
                });
              });
            }

            if (document.readyState === 'loading') {
              document.addEventListener('DOMContentLoaded', initErpSectionsNavScroll);
            } else {
              initErpSectionsNavScroll();
            }
          })();
        </script>

      </form>
    </div>
  </div>
  @endsection
