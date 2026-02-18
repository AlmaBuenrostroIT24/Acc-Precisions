@extends('adminlte::page')

@section('title', 'Edit NCAR')


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
                <div class="erp-sticky">
                  <div class="card erp-nav">
                    <div class="card-header py-2">
                      <strong>Sections</strong>
                    </div>
                    <div class="card-body p-0">
                      <div class="list-group list-group-flush">
                        <a class="list-group-item list-group-item-action" href="#sec-other">Other</a>
                        <a class="list-group-item list-group-item-action" href="#sec-issue">Issue / Disposition</a>
                        <a class="list-group-item list-group-item-action" href="#sec-personnel">Personnel / Process</a>
                        <a class="list-group-item list-group-item-action" href="#sec-root">Root Cause / Corrective</a>
                      </div>
                    </div>
                  </div>

                  <div class="mt-2 erp-gray-fields">
                    <div class="form-group mb-0">
                      <label class="mb-1">Description</label>
                      <textarea name="nc_description" rows="4" class="form-control form-control-sm {{ $errors->has('nc_description') ? 'is-invalid' : '' }}">{{ old('nc_description', $ncar->nc_description ?? '') }}</textarea>
                      @if($errors->has('nc_description'))<div class="invalid-feedback">{{ $errors->first('nc_description') }}</div>@endif
                    </div>
                  </div>

                  <div class="mt-2 erp-gray-fields">
                    <div class="form-group">
                      <label class="mb-1">Stage</label>
                      <input name="stage" type="text" class="form-control form-control-sm {{ $errors->has('stage') ? 'is-invalid' : '' }}" value="{{ old('stage', $ncar->stage ?? '') }}">
                      @if($errors->has('stage'))<div class="invalid-feedback">{{ $errors->first('stage') }}</div>@endif
                    </div>

                    <div class="form-group mb-0">
                      <label class="mb-1">Location</label>
                      <input name="location" type="text" class="form-control form-control-sm {{ $errors->has('location') ? 'is-invalid' : '' }}" value="{{ old('location', $ncar->location ?? '') }}">
                      @if($errors->has('location'))<div class="invalid-feedback">{{ $errors->first('location') }}</div>@endif
                    </div>
                    <div class="form-group">
                      <label class="mb-1">Status</label>
                      <select name="status" class="form-control form-control-sm {{ $errors->has('status') ? 'is-invalid' : '' }}">
                        <option value="New" {{ $statusVal === 'new' ? 'selected' : '' }}>New</option>
                        <option value="Quality Review" {{ $statusVal === 'quality review' ? 'selected' : '' }}>Quality Review</option>
                        <option value="Engineering Review" {{ $statusVal === 'engineering review' ? 'selected' : '' }}>Engineering Review</option>
                        <option value="Closed" {{ $statusVal === 'closed' ? 'selected' : '' }}>Closed</option>
                      </select>
                      @if($errors->has('status'))<div class="invalid-feedback">{{ $errors->first('status') }}</div>@endif
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
                        <div class="col-12">
                          <div class="row">
                            <div class="col-md-2">
                              <div class="form-group">
                                <label>Delivery Qty</label>
                                <input name="delqty" type="number" step="any" class="form-control form-control-sm {{ $errors->has('delqty') ? 'is-invalid' : '' }}" value="{{ old('delqty', $ncar->delqty ?? '') }}">
                                @if($errors->has('delqty'))<div class="invalid-feedback">{{ $errors->first('delqty') }}</div>@endif
                              </div>
                            </div>

                            <div class="col-md-2">
                              <div class="form-group">
                                <label>Reject Qty</label>
                                <input name="rejqty" type="number" step="any" class="form-control form-control-sm {{ $errors->has('rejqty') ? 'is-invalid' : '' }}" value="{{ old('rejqty', $ncar->rejqty ?? '') }}">
                                @if($errors->has('rejqty'))<div class="invalid-feedback">{{ $errors->first('rejqty') }}</div>@endif
                              </div>
                            </div>

                            <div class="col-md-2">
                              <div class="form-group">
                                <label>Stock Qty</label>
                                <input name="stkqty" type="number" step="any" class="form-control form-control-sm {{ $errors->has('stkqty') ? 'is-invalid' : '' }}" value="{{ old('stkqty', $ncar->stkqty ?? '') }}">
                                @if($errors->has('stkqty'))<div class="invalid-feedback">{{ $errors->first('stkqty') }}</div>@endif
                              </div>
                            </div>

                            <div class="col-md-3">
                              <div class="form-group">
                                <label>¿Job Packet Copy?</label>
                                @php
                                $jobPktCopyRaw = old('jobpktcopy', $ncar->jobpktcopy ?? '');
                                $jobPktCopyNorm = strtolower(trim((string) $jobPktCopyRaw));
                                $jobPktCopyVal = $jobPktCopyNorm === '' ? '' : (in_array($jobPktCopyNorm, ['1', 'true', 'yes', 'y', 'on'], true) ? '1' : '0');
                                @endphp
                                <select name="jobpktcopy" class="form-control form-control-sm {{ $errors->has('jobpktcopy') ? 'is-invalid' : '' }}">
                                  <option value="" {{ $jobPktCopyVal === '' ? 'selected' : '' }}></option>
                                  <option value="1" {{ $jobPktCopyVal === '1' ? 'selected' : '' }}>Yes</option>
                                  <option value="0" {{ $jobPktCopyVal === '0' ? 'selected' : '' }}>No</option>
                                </select>
                                @if($errors->has('jobpktcopy'))<div class="invalid-feedback">{{ $errors->first('jobpktcopy') }}</div>@endif
                              </div>
                            </div>

                            <div class="col-md-3">
                              <div class="form-group">
                                <label>¿Travel & Insp. Completed?</label>
                                @php
                                $travInspRaw = old('travinsp', $ncar->travinsp ?? '');
                                $travInspNorm = strtolower(trim((string) $travInspRaw));
                                $travInspVal = $travInspNorm === '' ? '' : (in_array($travInspNorm, ['1', 'true', 'yes', 'y', 'on'], true) ? '1' : '0');
                                @endphp
                                <select name="travinsp" class="form-control form-control-sm {{ $errors->has('travinsp') ? 'is-invalid' : '' }}">
                                  <option value="" {{ $travInspVal === '' ? 'selected' : '' }}></option>
                                  <option value="1" {{ $travInspVal === '1' ? 'selected' : '' }}>Yes</option>
                                  <option value="0" {{ $travInspVal === '0' ? 'selected' : '' }}>No</option>
                                </select>
                                @if($errors->has('travinsp'))<div class="invalid-feedback">{{ $errors->first('travinsp') }}</div>@endif
                              </div>
                            </div>

                            <div class="col-md-3">
                              <div class="form-group">
                                <label>¿Sample Completed?</label>
                                @php
                                $sampleComplRaw = old('samplecompl', $ncar->samplecompl ?? '');
                                $sampleComplNorm = strtolower(trim((string) $sampleComplRaw));
                                $sampleComplVal = $sampleComplNorm === '' ? '' : (in_array($sampleComplNorm, ['1', 'true', 'yes', 'y', 'on'], true) ? '1' : '0');
                                @endphp
                                <select name="samplecompl" class="form-control form-control-sm {{ $errors->has('samplecompl') ? 'is-invalid' : '' }}">
                                  <option value="" {{ $sampleComplVal === '' ? 'selected' : '' }}></option>
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
                      </div>
                    </div>
                  </div>

                  <div id="sec-issue" class="card erp-section">
                    <div class="card-header py-2">
                      <strong>Issue / Disposition</strong>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-12 mb-3">
                          <div class="row">
                            <div class="col-12 col-md-2">
                              <div class="form-group">
                                <label>Relevant Function</label>
                                @php
                                $relevantFunctionVal = (string) old('relevantfunction', $ncar->relevantfunction ?? '');
                                $relevantFunctionVal = strtoupper(trim($relevantFunctionVal));
                                $relevantFunctionOpts = ['PLN', 'PUR', 'ENGR', 'PROD', 'QC', 'TM'];
                                @endphp
                                <select name="relevantfunction" class="form-control form-control-sm {{ $errors->has('relevantfunction') ? 'is-invalid' : '' }}">
                                  <option value=""></option>
                                  @foreach($relevantFunctionOpts as $opt)
                                  <option value="{{ $opt }}" {{ $relevantFunctionVal === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                  @endforeach
                                  @if($relevantFunctionVal !== '' && !in_array($relevantFunctionVal, $relevantFunctionOpts, true))
                                  <option value="{{ $relevantFunctionVal }}" selected>{{ $relevantFunctionVal }}</option>
                                  @endif
                                </select>
                                @if($errors->has('relevantfunction'))<div class="invalid-feedback">{{ $errors->first('relevantfunction') }}</div>@endif
                              </div>
                            </div>
                            <div class="col-12 col-md-2">
                              <div class="form-group">
                                <label>¿Containment?</label>
                                @php
                                $containReqRaw = old($containReqKey, $ncar->{$containReqKey} ?? '');
                                $containReqNorm = strtolower(trim((string) $containReqRaw));
                                $containReqVal = $containReqNorm === '' ? '' : (in_array($containReqNorm, ['1', 'true', 'yes', 'y', 'on'], true) ? '1' : '0');
                                @endphp
                                <select id="containmentReqSelect" name="{{ $containReqKey }}" class="form-control form-control-sm {{ $errors->has($containReqKey) ? 'is-invalid' : '' }}">
                                  <option value="" {{ $containReqVal === '' ? 'selected' : '' }}></option>
                                  <option value="1" {{ $containReqVal === '1' ? 'selected' : '' }}>Yes</option>
                                  <option value="0" {{ $containReqVal === '0' ? 'selected' : '' }}>No</option>
                                </select>
                                @if($errors->has($containReqKey))<div class="invalid-feedback">{{ $errors->first($containReqKey) }}</div>@endif
                              </div>
                            </div>

                            <div class="col-12 col-md-8" id="containmentFieldWrap">
                              <div class="form-group mb-0">
                                <label>Containment</label>
                                <textarea name="containment" rows="1" class="form-control form-control-sm erp-autogrow {{ $errors->has('containment') ? 'is-invalid' : '' }}">{{ old('containment', $ncar->containment ?? '') }}</textarea>
                                @if($errors->has('containment'))<div class="invalid-feedback">{{ $errors->first('containment') }}</div>@endif
                              </div>
                            </div>
                          </div>
                        </div>

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

                        <div class="col-12 col-lg-7 mb-3 mb-lg-0">
                          <div class="form-group mb-0">
                            <div class="erp-field-head">
                              <div class="erp-field-label">
                                <label class="mb-1">Discrepancy</label>
                                <small class="erp-help">Add rows as needed</small>
                              </div>
                              <button type="button" class="btn btn-sm erp-btn-add" id="addDiscrepancyBtn">
                                <i class="fas fa-plus mr-1"></i> Discrepancy
                              </button>
                            </div>

                            {{-- Store as JSON in the existing DB column (qa_ncar.discrepancy) --}}
                            <textarea name="discrepancy" id="discrepancyJson" class="d-none">{{ $discRaw }}</textarea>

                            <div class="erp-grid">
                              <div id="discrepanciesWrap" class="erp-grid-wrap">
                              @foreach($discItems as $it)
                              <div class="row discrepancy-row mb-2">
                                <div class="col-md-9">
                                  <textarea rows="1" class="form-control form-control-sm discrepancy-desc erp-autogrow" placeholder="Description">{{ $it['desc'] }}</textarea>
                                </div>
                                <div class="col-md-3">
                                  <div class="d-flex" style="gap:.5rem">
                                    <input type="number" step="any" class="form-control form-control-sm discrepancy-qty" placeholder="Qty" value="{{ $it['qty'] }}">
                                    <button type="button" class="btn btn-outline-danger btn-sm erp-btn-remove remove-discrepancy" title="Remove">
                                      <i class="fas fa-trash"></i>
                                    </button>
                                  </div>
                                </div>
                              </div>
                              @endforeach
                              </div>
                            </div>

                            @if($errors->has('discrepancy'))<div class="invalid-feedback d-block">{{ $errors->first('discrepancy') }}</div>@endif
                          </div>
                        </div>
                        <div class="col-12 col-lg-5 mt-3 mt-lg-0">
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
                            <div class="erp-field-head">
                              <div class="erp-field-label">
                                <label class="mb-1">Disposition / Correction</label>
                                <small class="erp-help">Add rows as needed</small>
                              </div>
                              <button type="button" class="btn btn-sm erp-btn-add" id="addDispositionBtn">
                                <i class="fas fa-plus mr-1"></i> Disposition
                              </button>
                            </div>

                            <textarea name="disposition" id="dispositionJson" class="d-none">{{ $dispRaw }}</textarea>

                            <div class="erp-grid">
                              <div id="dispositionsWrap" class="erp-grid-wrap">
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
                                <div class="col-md-5">
                                  <select class="form-control form-control-sm disposition-action">
                                    <option value=""></option>
                                    @foreach($dispOptions as $opt)
                                    <option value="{{ $opt }}" {{ $selected === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                  </select>
                                </div>
                                <div class="col-md-3">
                                  <input type="number" step="any" class="form-control form-control-sm disposition-accqty" placeholder="Acc Qty" value="{{ $it['accqty'] ?? '' }}">
                                </div>
                                <div class="col-md-4">
                                  <div class="d-flex" style="gap:.5rem">
                                    <input type="number" step="any" class="form-control form-control-sm disposition-rejqty" placeholder="Rej Qty" value="{{ $it['rejqty'] ?? '' }}">
                                    <button type="button" class="btn btn-outline-danger btn-sm erp-btn-remove remove-disposition" title="Remove">
                                      <i class="fas fa-trash"></i>
                                    </button>
                                  </div>
                                </div>
                              </div>
                              @endforeach
                              </div>
                            </div>

                            @if($errors->has('disposition'))<div class="invalid-feedback d-block">{{ $errors->first('disposition') }}</div>@endif
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
                        @php
                        $reqRootCauseValue = (string) old('reqrootcause', $ncar->reqrootcause ?? '');
                        $reqRootCauseNorm = strtolower(trim($reqRootCauseValue));
                        @endphp

                        <div class="col-md-10">
                          <div class="form-group">
                            <label>Issue Found By and ¿How?</label>
                            <input name="issuefoundbt" type="text" class="form-control form-control-sm {{ $errors->has('issuefoundbt') ? 'is-invalid' : '' }}" value="{{ old('issuefoundbt', $ncar->issuefoundbt ?? '') }}">
                            @if($errors->has('issuefoundbt'))<div class="invalid-feedback">{{ $errors->first('issuefoundbt') }}</div>@endif
                          </div>
                        </div>

                        <div class="col-md-2">
                          <div class="form-group">
                            <label> ¿Req Corrective Action?</label>
                            <select name="reqrootcause" class="form-control form-control-sm {{ $errors->has('reqrootcause') ? 'is-invalid' : '' }}">
                              <option value=""></option>
                              <option value="1" {{ in_array($reqRootCauseNorm, ['yes','y','1','true'], true) ? 'selected' : '' }}>Yes</option>
                              <option value="0" {{ in_array($reqRootCauseNorm, ['no','n','0','false'], true) ? 'selected' : '' }}>No</option>
                            </select>
                            @if($errors->has('reqrootcause'))<div class="invalid-feedback">{{ $errors->first('reqrootcause') }}</div>@endif
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Personnel Accounts</label>
                            <textarea name="personnelaccounts" rows="3" class="form-control form-control-sm {{ $errors->has('personnelaccounts') ? 'is-invalid' : '' }}">{{ old('personnelaccounts', $ncar->personnelaccounts ?? '') }}</textarea>
                            @if($errors->has('personnelaccounts'))<div class="invalid-feedback">{{ $errors->first('personnelaccounts') }}</div>@endif
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Note (Pre Root)</label>
                            <textarea name="noterpreroot" rows="3" class="form-control form-control-sm {{ $errors->has('noterpreroot') ? 'is-invalid' : '' }}">{{ old('noterpreroot', $ncar->noterpreroot ?? '') }}</textarea>
                            @if($errors->has('noterpreroot'))<div class="invalid-feedback">{{ $errors->first('noterpreroot') }}</div>@endif
                          </div>
                        </div>

                        <div class="col-12">
                          <div class="form-group">
                            @php
                            $personnelRaw = (string) old('personnelinvolved', $ncar->personnelinvolved ?? '');
                            $personnelItems = [];
                            $personnelDecoded = null;
                            $personnelTrim = trim($personnelRaw);
                            if ($personnelTrim !== '' && (str_starts_with($personnelTrim, '[') || str_starts_with($personnelTrim, '{'))) {
                            $personnelDecoded = json_decode($personnelTrim, true);
                            }

                            if (is_array($personnelDecoded)) {
                            if (array_is_list($personnelDecoded)) {
                            foreach ($personnelDecoded as $it) {
                            if (is_string($it)) {
                            $v = trim($it);
                            if ($v !== '') $personnelItems[] = ['operator' => $v, 'ope_position' => ''];
                            continue;
                            }
                            if (is_array($it)) {
                            $op = trim((string) ($it['operator'] ?? $it['name'] ?? $it['value'] ?? ''));
                            $pos = trim((string) ($it['ope_position'] ?? $it['position'] ?? $it['opePosition'] ?? ''));
                            if ($op !== '') $personnelItems[] = ['operator' => $op, 'ope_position' => $pos];
                            }
                            }
                            } else {
                            $op = trim((string) ($personnelDecoded['operator'] ?? $personnelDecoded['name'] ?? $personnelDecoded['value'] ?? ''));
                            $pos = trim((string) ($personnelDecoded['ope_position'] ?? $personnelDecoded['position'] ?? $personnelDecoded['opePosition'] ?? ''));
                            if ($op !== '') $personnelItems[] = ['operator' => $op, 'ope_position' => $pos];
                            }
                            } elseif ($personnelTrim !== '') {
                            $personnelItems[] = ['operator' => $personnelTrim, 'ope_position' => ''];
                            }

                            if (empty($personnelItems)) {
                            $personnelItems[] = ['operator' => '', 'ope_position' => ''];
                            }

                            $locStr = strtolower(trim((string) ($ncar->location ?? '')));
                            $opRows = \Illuminate\Support\Facades\DB::table('gen_operators as o')
                            ->leftJoin('gen_location as l', 'l.id', '=', 'o.location_id')
                            ->select(['o.operator', 'o.ope_position', 'l.location as location'])
                            ->when($locStr !== '', function ($q) use ($locStr) {
                            $q->whereRaw('LOWER(l.location) = ?', [$locStr]);
                            })
                            ->orderBy('o.operator')
                            ->get();

                            $operatorOptions = [];
                            $operatorPosMap = [];
                            foreach ($opRows as $r) {
                            $val = trim((string) ($r->operator ?? ''));
                            if ($val === '') continue;
                            $pos = trim((string) ($r->ope_position ?? ''));
                            $operatorPosMap[$val] = $pos;
                            $operatorOptions[] = ['value' => $val, 'pos' => $pos];
                            }
                            @endphp

                            <textarea name="personnelinvolved" id="personnelInvolvedJson" class="d-none">{{ $personnelRaw }}</textarea>

                            <div class="erp-field-head">
                              <div class="erp-field-label">
                                <label class="mb-1">Personnel Involved</label>
                                <small class="erp-help">Add rows as needed</small>
                              </div>
                              <button type="button" class="btn btn-sm erp-btn-add" id="addPersonnelBtn">
                                <i class="fas fa-plus mr-1"></i> Personnel
                              </button>
                            </div>

                            <select id="personnelOperatorTemplate" class="d-none">
                              <option value=""></option>
                              @foreach($operatorOptions as $opt)
                              <option value="{{ $opt['value'] }}" data-pos="{{ $opt['pos'] }}">{{ $opt['value'] }}</option>
                              @endforeach
                            </select>

                            <div class="erp-grid">
                              <div id="personnelInvolvedWrap" class="erp-grid-wrap">
                              @foreach($personnelItems as $it)
                              @php
                              $itOp = (string) ($it['operator'] ?? '');
                              $itPos = (string) ($it['ope_position'] ?? '');
                              if ($itPos === '' && $itOp !== '' && isset($operatorPosMap[$itOp])) {
                              $itPos = (string) $operatorPosMap[$itOp];
                              }
                              @endphp
                              <div class="row personnel-row mb-2">
                                <div class="col-md-7">
                                  <select class="form-control form-control-sm personnel-operator {{ $errors->has('personnelinvolved') ? 'is-invalid' : '' }}">
                                    <option value=""></option>
                                    @foreach($operatorOptions as $opt)
                                    <option value="{{ $opt['value'] }}" data-pos="{{ $opt['pos'] }}" {{ $itOp === (string)$opt['value'] ? 'selected' : '' }}>{{ $opt['value'] }}</option>
                                    @endforeach
                                  </select>
                                </div>
                                <div class="col-md-4">
                                  <input type="text" class="form-control form-control-sm personnel-position" value="{{ $itPos }}" readonly>
                                </div>
                                <div class="col-md-1">
                                  <div class="d-flex" style="gap:.5rem">
                                    <button type="button" class="btn btn-outline-danger btn-sm erp-btn-remove remove-personnel" title="Remove">
                                      <i class="fas fa-trash"></i>
                                    </button>
                                  </div>
                                </div>
                              </div>
                              @endforeach
                            </div>
                            </div>

                            @if($errors->has('personnelinvolved'))<div class="invalid-feedback d-block">{{ $errors->first('personnelinvolved') }}</div>@endif
                          </div>
                        </div>

                      </div> 
                    </div> 
                  </div> 

                  
                  <div id="sec-root" class="card erp-section">
                    <div class="card-header py-2">
                      <strong>Root Cause / Corrective</strong>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-12"> 
                          <div class="form-group"> 
                            <label>Root Cause</label> 
                            <textarea name="rootcause" rows="3" class="form-control form-control-sm {{ $errors->has('rootcause') ? 'is-invalid' : '' }}">{{ old('rootcause', $ncar->rootcause ?? '') }}</textarea> 
                            @if($errors->has('rootcause'))<div class="invalid-feedback">{{ $errors->first('rootcause') }}</div>@endif 
                          </div> 
                        </div> 

                        <div class="col-12">
                          <div class="form-group">
                            <label>Process Affected</label>
                            <textarea name="processaffected" rows="1" class="form-control form-control-sm {{ $errors->has('processaffected') ? 'is-invalid' : '' }}">{{ old('processaffected', $ncar->processaffected ?? '') }}</textarea>
                            @if($errors->has('processaffected'))<div class="invalid-feedback">{{ $errors->first('processaffected') }}</div>@endif
                          </div>
                        </div>
 
                        <div class="col-md-6"> 
                          <div class="form-group"> 
                            <label>Corrective Action</label> 
                            <textarea name="corrective" rows="4" class="form-control form-control-sm {{ $errors->has('corrective') ? 'is-invalid' : '' }}">{{ old('corrective', $ncar->corrective ?? '') }}</textarea> 
                            @if($errors->has('corrective'))<div class="invalid-feedback">{{ $errors->first('corrective') }}</div>@endif 
                          </div> 
                        </div> 
 
                        <div class="col-md-6"> 
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

      </form>
    </div>
  </div>
  @endsection

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

    /* Editable sections: colored headers (no functional changes) */ 
    .ncar-edit-page .erp-section { 
      --sec-accent: #94a3b8; 
    } 

    .ncar-edit-page #sec-other { --sec-accent: #0b5ed7; }
    .ncar-edit-page #sec-issue { --sec-accent: #f59e0b; }
    .ncar-edit-page #sec-personnel { --sec-accent: #7c3aed; }
    .ncar-edit-page #sec-root { --sec-accent: #16a34a; }

    .ncar-edit-page .erp-section > .card-header { 
      position: relative; 
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: .75rem;
      background: #fff; 
      border-bottom-color: rgba(15, 23, 42, 0.08); 
      padding: .55rem .75rem .55rem 18px; 
      overflow: hidden;
    } 

    .ncar-edit-page .erp-section > .card-header::before { 
      content: ''; 
      position: absolute; 
      left: 0; 
      top: 0; 
      bottom: 0; 
      width: 8px; 
      background: var(--sec-accent); 
    } 

    .ncar-edit-page .erp-section > .card-header::after {
      content: '';
      position: absolute;
      inset: 0;
      background: var(--sec-accent);
      opacity: 0.08;
      pointer-events: none;
    }

    .ncar-edit-page .erp-section.sec-status--none > .card-header::after {
      opacity: 0.10;
    }

    .ncar-edit-page .erp-section.sec-status--partial > .card-header::after,
    .ncar-edit-page .erp-section.sec-status--done > .card-header::after {
      opacity: 0.12;
    }

    .ncar-edit-page .erp-section > .card-header > * {
      position: relative;
      z-index: 1;
    }

    .ncar-edit-page .erp-section > .card-header strong {
      font-size: 0.95rem !important;
      letter-spacing: .03em;
      text-transform: uppercase;
    }

    /* Section buttons inherit accent */
    .ncar-edit-page .erp-section .erp-btn-add {
      border-color: rgba(15, 23, 42, 0.25) !important;
      box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06), 0 0 0 0 rgba(0,0,0,0);
    }
    .ncar-edit-page .erp-section .erp-btn-add:hover {
      background: rgba(255,255,255,0.92);
      box-shadow: 0 6px 16px rgba(15, 23, 42, 0.12), 0 0 0 3px rgba(148, 163, 184, 0.18);
      transform: translateY(-1px);
    }

    /* Validation clarity */
    .ncar-edit-page .form-control.is-invalid,
    .ncar-edit-page select.form-control.is-invalid,
    .ncar-edit-page textarea.form-control.is-invalid {
      border-color: rgba(220, 38, 38, 0.85) !important;
    }
    .ncar-edit-page .form-control.is-invalid:focus {
      box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.18) !important;
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

    /* Auto-grow textareas (Discrepancy) */
    .ncar-edit-page .erp-autogrow {
      resize: none;
      overflow: hidden;
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

    /* Left-side ERP fields: keep the default gray ERP look */
    .ncar-edit-page .erp-gray-fields .form-control,
    .ncar-edit-page .erp-gray-fields .custom-select,
    .ncar-edit-page .erp-gray-fields select.form-control {
      background: #f8fafc;
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

    .ncar-edit-page .erp-field-head {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: .5rem;
      flex-wrap: wrap;
      margin-bottom: .4rem;
    }

    .ncar-edit-page .erp-field-label {
      display: flex;
      flex-direction: column;
      gap: 2px;
      flex: 1 1 220px;
      min-width: 0;
    }

    .ncar-edit-page .erp-field-head label {
      margin: 0;
      line-height: 1.15;
    }

    .ncar-edit-page .erp-help {
      display: block;
      font-size: 0.78rem !important;
      line-height: 1.1;
      color: #64748b;
      font-weight: 600;
    }

    .ncar-edit-page .erp-btn-add {
      display: inline-flex;
      align-items: center;
      flex: 0 0 auto;
      white-space: nowrap;
      margin-left: auto;
      height: 34px;
      border-radius: 10px;
      font-weight: 800;
      border: 1px solid rgba(148, 163, 184, 0.65);
      background: #fff;
      color: #0f172a;
      box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
      padding: .35rem .75rem;
    }

    .ncar-edit-page .erp-btn-add i {
      color: #2563eb;
      font-size: 0.95rem;
    }

    .ncar-edit-page .erp-btn-add:hover {
      background: #f8fafc;
      border-color: rgba(100, 116, 139, 0.55);
      color: #0f172a;
    }

    .ncar-edit-page .erp-btn-add:focus {
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
    }

    .ncar-edit-page .erp-btn-remove { 
      width: 34px; 
      height: 34px; 
      padding: 0; 
      display: inline-flex; 
      align-items: center; 
      justify-content: center; 
      border-radius: 10px; 
    } 

    .ncar-edit-page .erp-btn-remove i {
      font-size: 0.95rem;
      line-height: 1;
    }

    /* ERP "table-like" dynamic rows (Discrepancy / Disposition / Personnel) */
    .ncar-edit-page .erp-grid {
      border: 1px solid rgba(15, 23, 42, 0.12);
      border-radius: 12px;
      overflow: hidden;
      background: #fff;
    }

    .ncar-edit-page .erp-grid-head {
      background: linear-gradient(180deg, #f7f9fc 0%, #edf1f6 100%);
      border-bottom: 1px solid rgba(15, 23, 42, 0.10);
      font-weight: 900;
      color: rgba(15, 23, 42, 0.78);
      letter-spacing: .02em;
      text-transform: uppercase;
      font-size: 0.74rem !important;
      padding: 8px 10px;
      margin: 0;
    }

    .ncar-edit-page .erp-grid-head > [class^="col-"],
    .ncar-edit-page .erp-grid-head > [class*=" col-"] {
      padding-left: 0.45rem;
      padding-right: 0.45rem;
    }

    .ncar-edit-page .erp-grid-wrap {
      padding: 0;
    }

    .ncar-edit-page .erp-grid-wrap .row.discrepancy-row,
    .ncar-edit-page .erp-grid-wrap .row.disposition-row,
    .ncar-edit-page .erp-grid-wrap .row.personnel-row {
      margin-left: 0;
      margin-right: 0;
      margin-bottom: 0 !important;
      padding: 8px 10px;
      border-bottom: 1px solid rgba(15, 23, 42, 0.08);
      align-items: center;
    }

    .ncar-edit-page .erp-grid-wrap .row.discrepancy-row:last-child,
    .ncar-edit-page .erp-grid-wrap .row.disposition-row:last-child,
    .ncar-edit-page .erp-grid-wrap .row.personnel-row:last-child {
      border-bottom: 0;
    }

    .ncar-edit-page .erp-grid-wrap .row.discrepancy-row:nth-child(even),
    .ncar-edit-page .erp-grid-wrap .row.disposition-row:nth-child(even),
    .ncar-edit-page .erp-grid-wrap .row.personnel-row:nth-child(even) {
      background: rgba(248, 250, 252, 0.85);
    }

    .ncar-edit-page .erp-grid-wrap .erp-btn-remove {
      align-self: center;
    }

    .ncar-edit-page .erp-grid-wrap .d-flex {
      align-items: center;
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
      position: relative;
      transition: background-color .12s ease, box-shadow .12s ease, border-color .12s ease;
    } 
 
    .ncar-edit-page .erp-nav .list-group-item.active { 
      background: rgba(59, 130, 246, 0.12); 
      color: #0f172a; 
    } 

    /* Nav status: partial (yellow) / done (green) */
    .ncar-edit-page .erp-nav .list-group-item.sec-status--none {
      background: rgba(148, 163, 184, 0.14);
      border-left: 6px solid rgba(100, 116, 139, 0.55);
      padding-left: .5rem;
    }

    .ncar-edit-page .erp-nav .list-group-item.sec-status--partial {
      background: rgba(245, 158, 11, 0.16);
      border-left: 6px solid rgba(245, 158, 11, 0.85);
      padding-left: .5rem;
    }

    .ncar-edit-page .erp-nav .list-group-item.sec-status--done {
      background: rgba(34, 197, 94, 0.16);
      border-left: 6px solid rgba(34, 197, 94, 0.85);
      padding-left: .5rem;
    }

    .ncar-edit-page .erp-nav .list-group-item.sec-status--none::after,
    .ncar-edit-page .erp-nav .list-group-item.sec-status--partial::after,
    .ncar-edit-page .erp-nav .list-group-item.sec-status--done::after {
      content: '';
      position: absolute;
      right: .55rem;
      top: 50%;
      width: 8px;
      height: 8px;
      border-radius: 999px;
      transform: translateY(-50%);
      box-shadow: 0 0 0 2px rgba(255,255,255,0.85);
    }

    .ncar-edit-page .erp-nav .list-group-item.sec-status--none::after {
      background: rgba(100, 116, 139, 0.75);
    }

    .ncar-edit-page .erp-nav .list-group-item.sec-status--partial::after {
      background: rgba(245, 158, 11, 0.95);
    }

    .ncar-edit-page .erp-nav .list-group-item.sec-status--done::after {
      background: rgba(34, 197, 94, 0.95);
    }

    .ncar-edit-page .erp-nav .list-group-item.active.sec-status--none,
    .ncar-edit-page .erp-nav .list-group-item.active.sec-status--partial,
    .ncar-edit-page .erp-nav .list-group-item.active.sec-status--done {
      box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.06);
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

  @push('js')
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
          '<div class="col-md-5">' +
          '<select class="form-control form-control-sm disposition-action">' + optionsHtml(action || '') + '</select>' +
          '</div>' +
          '<div class="col-md-3">' +
          '<input type="number" step="any" class="form-control form-control-sm disposition-accqty" placeholder="Acc Qty" />' +
          '</div>' +
          '<div class="col-md-4">' +
          '<div class="d-flex" style="gap:.5rem">' +
          '<input type="number" step="any" class="form-control form-control-sm disposition-rejqty" placeholder="Rej Qty" />' +
          '<button type="button" class="btn btn-outline-danger btn-sm erp-btn-remove remove-disposition" title="Remove"><i class="fas fa-trash"></i></button>' +
          '</div>' +
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
    var wrap = document.getElementById('personnelInvolvedWrap');
    var hidden = document.getElementById('personnelInvolvedJson');
    var addBtn = document.getElementById('addPersonnelBtn');
    var template = document.getElementById('personnelOperatorTemplate');
    if (!wrap || !hidden || !addBtn || !template) return;

    function normalizeText(value) {
      return (value == null ? '' : String(value)).trim();
    }

    function optionPosition(sel) {
      if (!sel) return '';
      var opt = sel.options && sel.selectedIndex >= 0 ? sel.options[sel.selectedIndex] : null;
      return opt && opt.dataset ? (opt.dataset.pos || '') : '';
    }

    function getRows() {
      return Array.prototype.slice.call(wrap.querySelectorAll('.personnel-row'));
    }

    function syncToHidden() {
      var items = getRows().map(function(row) {
        var sel = row.querySelector('.personnel-operator');
        var pos = row.querySelector('.personnel-position');
        var opVal = normalizeText(sel ? sel.value : '');
        var posVal = normalizeText(pos ? pos.value : '');
        return {
          operator: opVal,
          ope_position: posVal
        };
      }).filter(function(it) {
        return it.operator !== '';
      });
      hidden.value = JSON.stringify(items);
    }

    function addRow(value) {
      var row = document.createElement('div');
      row.className = 'row personnel-row mb-2';

      var colSel = document.createElement('div');
      colSel.className = 'col-md-7';

      var sel = document.createElement('select');
      sel.className = 'form-control form-control-sm personnel-operator';
      sel.innerHTML = template.innerHTML;
      if (value) sel.value = value;

      colSel.appendChild(sel);

      var colPos = document.createElement('div');
      colPos.className = 'col-md-4';
      var pos = document.createElement('input');
      pos.type = 'text';
      pos.className = 'form-control form-control-sm personnel-position';
      pos.readOnly = true;
      pos.value = optionPosition(sel);
      colPos.appendChild(pos);

      var colBtn = document.createElement('div');
      colBtn.className = 'col-md-1';
      colBtn.innerHTML =
        '<button type="button" class="btn btn-outline-danger btn-sm erp-btn-remove remove-personnel" title="Remove">' +
        '<i class="fas fa-trash"></i>' +
        '</button>';

      row.appendChild(colSel);
      row.appendChild(colPos);
      row.appendChild(colBtn);
      wrap.appendChild(row);

      syncToHidden();
    }

    wrap.addEventListener('change', function(e) {
      if (e.target && e.target.classList.contains('personnel-operator')) {
        var row = e.target.closest ? e.target.closest('.personnel-row') : null;
        if (row) {
          var pos = row.querySelector('.personnel-position');
          if (pos) pos.value = optionPosition(e.target);
        }
        syncToHidden();
      }
    });

    wrap.addEventListener('click', function(e) {
      var btn = e.target && (e.target.closest ? e.target.closest('.remove-personnel') : null);
      if (!btn) return;
      var row = btn.closest('.personnel-row');
      if (row) row.remove();
      if (getRows().length === 0) addRow('');
      syncToHidden();
    });

    addBtn.addEventListener('click', function() {
      addRow('');
    });

    syncToHidden();

    getRows().forEach(function(row) {
      var sel = row.querySelector('.personnel-operator');
      var pos = row.querySelector('.personnel-position');
      if (sel && pos && !normalizeText(pos.value)) {
        pos.value = optionPosition(sel);
      }
    });

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

      function autosizeTextarea(el) {
        if (!el) return;
        el.style.height = 'auto';
        el.style.height = el.scrollHeight + 'px';
      }

      function autosizeAll() {
        getRows().forEach(function(row) {
          autosizeTextarea(row.querySelector('.discrepancy-desc'));
        });
      }

      function addRow(desc, qty) {
        var row = document.createElement('div');
        row.className = 'row discrepancy-row mb-2';
        row.innerHTML =
          '<div class="col-md-9">' +
          '<textarea rows="1" class="form-control form-control-sm discrepancy-desc erp-autogrow" placeholder="Description"></textarea>' +
          '</div>' +
          '<div class="col-md-3">' +
          '<div class="d-flex" style="gap:.5rem">' +
          '<input type="number" step="any" class="form-control form-control-sm discrepancy-qty" placeholder="Qty" />' +
          '<button type="button" class="btn btn-outline-danger btn-sm erp-btn-remove remove-discrepancy" title="Remove">' +
          '<i class="fas fa-trash"></i>' +
          '</button>' +
          '</div>' +
          '</div>';

        wrap.appendChild(row);
        var descEl = row.querySelector('.discrepancy-desc');
        var qtyEl = row.querySelector('.discrepancy-qty');
        if (descEl) descEl.value = desc || '';
        if (qtyEl) qtyEl.value = qty || '';
        autosizeTextarea(descEl);
        syncToHidden();
      }

      wrap.addEventListener('input', function(e) {
        if (e.target && (e.target.classList.contains('discrepancy-desc') || e.target.classList.contains('discrepancy-qty'))) {
          if (e.target.classList.contains('discrepancy-desc')) {
            autosizeTextarea(e.target);
          }
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
      autosizeAll();

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
      function initContainmentAutogrow() {
        var req = document.getElementById('containmentReqSelect');
        var wrap = document.getElementById('containmentFieldWrap');
        var el = document.querySelector('textarea[name="containment"]');
        if (!req || !wrap || !el) return;

        function autosize() {
          el.style.height = 'auto';
          el.style.height = el.scrollHeight + 'px';
        }

        function syncVisibility() {
          var show = String(req.value) === '1';
          wrap.classList.toggle('d-none', !show);
          if (!show) {
            el.value = '';
            el.style.height = 'auto';
          } else {
            autosize();
          }
        }

        autosize();
        syncVisibility();
        el.addEventListener('input', autosize);
        window.addEventListener('resize', autosize);
        req.addEventListener('change', syncVisibility);
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initContainmentAutogrow);
      } else {
        initContainmentAutogrow();
      }
    })();
  </script>

  <script> 
    (function() { 
      function initErpSectionsNavScroll() { 
        var nav = document.querySelector('.ncar-edit-page .erp-nav'); 
        var scroller = document.getElementById('erpSectionsScroll'); 
        if (!nav || !scroller) return; 

      var secOther = document.getElementById('sec-other');
      var secIssue = document.getElementById('sec-issue');
      if (secOther && secIssue) {
        secOther.insertAdjacentElement('afterend', secIssue);
      }

      var secPersonnel = document.getElementById('sec-personnel');
      if (secIssue && secPersonnel) {
        secIssue.insertAdjacentElement('afterend', secPersonnel);
      }

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

  <script>
    (function() {
      function debounce(fn, wait) {
        var t = null;
        return function() {
          var args = arguments;
          clearTimeout(t);
          t = setTimeout(function() { fn.apply(null, args); }, wait || 120);
        };
      }

      function isFilled(el) {
        if (!el) return false;
        if (el.disabled || el.readOnly) return false;
        if (el.type && String(el.type).toLowerCase() === 'hidden') return false;
        if (el.classList && el.classList.contains('d-none')) return false;
        if (el.closest && el.closest('.d-none')) return false;

        var tag = (el.tagName || '').toLowerCase();
        var val = (el.value ?? '').toString();

        if (tag === 'select') return val.trim() !== '';
        if ((el.type || '').toLowerCase() === 'number') return val !== '';
        return val.trim() !== '';
      }

      function getEditableControls(sectionEl) {
        if (!sectionEl) return [];
        var ignoreNames = {
          jobpktcopy: true,
          travinsp: true,
          samplecompl: true,
          containmentreq: true,
          contaimentreq: true,
        };
        var controls = sectionEl.querySelectorAll('input, textarea, select');
        var out = [];
        for (var i = 0; i < controls.length; i++) {
          var el = controls[i];
          if (!el || el.disabled || el.readOnly) continue;
          if (el.name && ignoreNames[el.name]) continue;
          if (el.type && String(el.type).toLowerCase() === 'hidden') continue;
          if (el.closest && el.closest('select.d-none, textarea.d-none, input.d-none')) continue;
          if (el.classList && el.classList.contains('d-none')) continue;
          if (el.closest && el.closest('.d-none')) continue;
          // Skip hidden JSON storages
          if (el.id && /Json$/i.test(el.id)) continue;
          if (el.name && /^(discrepancy|disposition|personnelinvolved)$/.test(el.name) && el.classList.contains('d-none')) continue;
          out.push(el);
        }
        return out;
      }

      function computeSectionStatus(sectionEl) {
        var controls = getEditableControls(sectionEl);
        if (!controls.length) return 'none';
        var filled = 0;
        for (var i = 0; i < controls.length; i++) {
          if (isFilled(controls[i])) filled++;
        }
        if (filled === 0) return 'none';
        if (filled === controls.length) return 'done';
        return 'partial';
      }

      function applyNavStatus(nav, sectionId, status) {
        var a = nav.querySelector('a[href="#' + sectionId + '"]');
        if (!a) return;
        a.classList.toggle('sec-status--none', status === 'none');
        a.classList.toggle('sec-status--partial', status === 'partial');
        a.classList.toggle('sec-status--done', status === 'done');
      }

      function applySectionStatus(sectionEl, status) {
        if (!sectionEl) return;
        sectionEl.classList.toggle('sec-status--partial', status === 'partial');
        sectionEl.classList.toggle('sec-status--done', status === 'done');
        sectionEl.classList.toggle('sec-status--none', status === 'none');

        // Override per-section accent with the completion status colors.
        var accent = '#94a3b8'; // none = gray
        if (status === 'partial') accent = '#f59e0b'; // yellow
        if (status === 'done') accent = '#22c55e'; // green
        sectionEl.style.setProperty('--sec-accent', accent);
      }

      function initSectionCompletionNav() {
        var nav = document.querySelector('.ncar-edit-page .erp-nav');
        var scroller = document.getElementById('erpSectionsScroll');
        if (!nav || !scroller) return;

        var sectionIds = ['sec-other', 'sec-issue', 'sec-personnel', 'sec-root'];

        function updateAll() {
          sectionIds.forEach(function(id) {
            var sec = document.getElementById(id);
            var status = computeSectionStatus(sec);
            applyNavStatus(nav, id, status);
            applySectionStatus(sec, status);
          });
        }

        var updateDebounced = debounce(updateAll, 140);
        updateAll();

        scroller.addEventListener('input', updateDebounced, true);
        scroller.addEventListener('change', updateDebounced, true);
        scroller.addEventListener('click', updateDebounced, true);
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSectionCompletionNav);
      } else {
        initSectionCompletionNav();
      }
    })();
  </script>
  @endpush
