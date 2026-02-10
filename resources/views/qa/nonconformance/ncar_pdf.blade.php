@php
  use Illuminate\Support\Carbon;

  $fmtDate = function ($raw) {
      $v = is_string($raw) ? trim($raw) : $raw;
      if (!$v) return '';
      try {
          return Carbon::parse($v)->format('m/d/Y');
      } catch (\Throwable $e) {
          return is_string($v) ? $v : '';
      }
  };

  $companyName = 'ACC Precision, Inc.';
  $companyAddr = '321 Hearst Drive Oxnard, CA 93030';
  $companyPhoneTel = 'Tel. 805-278-9801';
  $companyPhoneFax = 'Fax 805-278-9841';

  $reportDate = $fmtDate($ncar->ncar_date ?? $ncar->created_at ?? null);
  $ncarNo = (string) ($ncar->ncar_no ?? $ncar->id);

  $customerCompany = (string) (($ncar->order_customer ?? '') ?: ($ncar->ncar_customer ?? '') ?: ($ncar->costumer ?? ''));
  $contact = (string) (($ncar->contact ?? '') ?: ($ncar->ncar_contact ?? ''));
  $class = (string) (($ncar->class ?? '') ?: ($ncar->ncar_class ?? '') ?: ($ncar->type_name ?? ''));
  $poNo = (string) (($ncar->cust_po ?? '') ?: ($ncar->PO ?? '') ?: ($ncar->po ?? ''));

  $jobPktCopy = (string) ($ncar->jobpktcopy ?? $ncar->job_pkt_copy ?? '');
  $travInspCompl = (string) ($ncar->travinspcompl ?? $ncar->trav_insp_compl ?? '');
  $samplCompl = (string) ($ncar->samplcompl ?? $ncar->sampl_compl ?? '');
  $issueFound = (string) ($ncar->issue_found ?? $ncar->issuefound ?? '');
  $issueFoundDate = $fmtDate($ncar->issue_date ?? $ncar->issuefound_date ?? null);
  $spProcInvalid = (string) ($ncar->spprocinvalid ?? $ncar->sp_proc_invalid ?? '');
  $rodBy = (string) ($ncar->rodby ?? $ncar->rod_by ?? '');

  $containmentReq = (string) ($ncar->containment_req ?? $ncar->containmentreq ?? '');
  $actionBelowNotes = (string) ($ncar->action_below ?? $ncar->actionbelow ?? '');
  $accQty = (string) ($ncar->accqty ?? $ncar->acc_qty ?? '');
  $tmInit = (string) ($ncar->tminit ?? $ncar->tm_init ?? '');
  $tmSignoff = (string) ($ncar->tm_signoff ?? $ncar->tmsignoff ?? '');
  $tmSignoffDate = $fmtDate($ncar->tm_signoff_date ?? $ncar->tmsignoff_date ?? $ncar->tm_signoff_dt ?? null);
  $completionSignoff = (string) ($ncar->completion_signoff ?? $ncar->completionsignoff ?? '');
  $completionSignoffDate = $fmtDate($ncar->completion_signoff_date ?? $ncar->completionsignoff_date ?? $ncar->completion_signoff_dt ?? null);
  $otherPartProcessAffected = (string) ($ncar->otherpartprocessaffected ?? $ncar->other_part_process_affected ?? '');

  $pn = (string) ($ncar->PN ?? '');
  $rev = (string) ($ncar->order_revision ?? $ncar->revision ?? '');
  $partDesc = (string) ($ncar->Part_description ?? '');

  $woNo = (string) ($ncar->work_id ?? '');
  $woQty = (string) (($ncar->wo_qty ?? '') !== '' ? $ncar->wo_qty : ($ncar->order_qty ?? ''));
  $coQty = (string) (($ncar->coqty ?? '') !== '' ? $ncar->coqty : ($ncar->order_qty ?? ''));
  $delQty = (string) ($ncar->delqty ?? '');
  $rejQty = (string) ($ncar->rejqty ?? '');
  $stkQty = (string) ($ncar->stkqty ?? '');

  $ops = (string) ($ncar->order_operation ?? $ncar->operation ?? '');
  $location = (string) ($ncar->location ?? '');
  $refNo = (string) ($ncar->ref ?? '');

  $discrepancy = (string) (($ncar->discrepancy ?? '') ?: ($ncar->nc_description ?? ''));
  $discrepancyItems = [];
  $discRaw = trim((string) ($ncar->discrepancy ?? ''));
  $discDecoded = null;
  if ($discRaw !== '' && (str_starts_with($discRaw, '[') || str_starts_with($discRaw, '{'))) {
    $discDecoded = json_decode($discRaw, true);
  }
  if (is_array($discDecoded)) {
    if (array_is_list($discDecoded)) {
      foreach ($discDecoded as $it) {
        if (!is_array($it)) continue;
        $discrepancyItems[] = [
          'desc' => (string) ($it['desc'] ?? ''),
          'qty' => (string) ($it['qty'] ?? ''),
        ];
      }
    } else {
      $discrepancyItems[] = [
        'desc' => (string) ($discDecoded['desc'] ?? ''),
        'qty' => (string) ($discDecoded['qty'] ?? ''),
      ];
    }
  }
  if (empty($discrepancyItems)) {
    $discrepancyItems[] = [
      'desc' => $discrepancy,
      'qty' => (string) ($rejQty !== '' ? $rejQty : '1'),
    ];
  }
  $containment = (string) ($ncar->containment ?? '');
  $disposition = (string) ($ncar->disposition ?? '');
  $rootCause = (string) ($ncar->rootcause ?? '');
  $corrective = (string) ($ncar->corrective ?? '');
  $verification = (string) ($ncar->verification ?? '');
  $hasCorrective = trim($corrective) !== '';
  $hasVerification = trim($verification) !== '';

  $relevantFunction = (string) ($ncar->relevantfunction ?? '');
  $issueFoundBy = (string) ($ncar->issuefoundbt ?? '');
  $reqRootCause = (string) ($ncar->reqrootcause ?? '');
  $notePreRoot = (string) ($ncar->noterpreroot ?? '');
  $personnelAccounts = (string) ($ncar->personnelaccounts ?? '');
@endphp

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>NCAR {{ $ncarNo }}</title>
    <style>
      /* Let cells grow with content (may create more than 1 page). */
      /* Extra bottom margin to reserve space for the fixed footer */
      @page { margin: 18pt 12pt 84pt; size: letter portrait; }
      html, body { margin: 0; padding: 0; }
      body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #000; }

      /* Align header block with the table width/margins */
      .hdr { width: 96%; margin: 20pt auto 6pt; table-layout: fixed; }
      .hdr td { vertical-align: top; }

      .company { font-weight: 800; font-size: 14px; }
      .title { font-weight: 800; font-size: 14px; }
      .addr { text-align: right; font-size: 12px; line-height: 1.05; }
      .addr .small { font-size: 11px; font-weight: 700; }
      .hdr td:first-child { width: 58%; }
      .hdr td.addr { width: 42%; }

      /* Keep left/right margin so the table isn't glued to the page edge */
      table.grid { width: 96%; margin: 0 auto; border-collapse: collapse; table-layout: fixed; }
      table.grid td {
        border: 1px solid #000;
        padding: 0.8pt 1.2pt;
        vertical-align: top;
        line-height: 1.1;
        overflow: visible;
        word-break: break-word;
        overflow-wrap: anywhere;
      }
      table.grid td.ca-cell { padding-bottom: 0 !important; }

      /* Row height helpers: behave like minimum heights (cells can still grow with content). */
      tr.h18 td { height: 18pt; }
      tr.h22 td { height: 22pt; }
      tr.h34 td { height: 34pt; }
      tr.h44 td { height: 44pt; }
      tr.h52 td { height: 52pt; }
      tr.h70 td { height: 60pt; }
      tr.grow td { height: auto !important; }
      tr.grow { page-break-inside: auto; }

      .k { font-weight: 800; }
      .v { font-weight: 700; white-space: normal; }
      .shade { background: #d9eefb; }
      .shade-gray { background: #eef2f7; }
      .muted { color: #222; font-weight: 700; }

      /* Key/value cells (header gray rows) */
      .kv { font-size: 11px; }
      .kv-2 { font-size: 11px; }
      .kv-4 { font-size: 11px; }

      /* Spacer row repeated on each page (keeps top margin on page breaks) */
      thead { display: table-header-group; }
      tr.page-spacer td { border: 0 !important; padding: 0 !important; height: 10pt; background: transparent !important; }

      /* Inner table helper (avoid inheriting grid borders) */
      table.grid table.inner { width: 100%; border-collapse: collapse; table-layout: fixed; }
      table.grid table.inner td { border: 0 !important; padding: 0 !important; }
      table.grid table.inner td.r { text-align: right; white-space: nowrap; padding-left: 10pt !important; }

      /* Corrective Action sign-off row (draw only internal lines) */
      table.grid table.ca-sign { width: 100%; border-collapse: collapse; table-layout: fixed; display: inline-table; height: auto !important; }
      table.grid table.ca-sign tr { height: auto !important; }
      table.grid table.ca-sign td { border: 0 !important; padding: 0.6pt 1.2pt !important; vertical-align: middle; height: auto !important; line-height: 1.05; }
      table.grid table.ca-sign td:nth-child(2),
      table.grid table.ca-sign td:nth-child(3) { border-top: 1px solid #000 !important; }
      table.grid table.ca-sign td + td { border-left: 1px solid #000 !important; }
      table.grid table.ca-sign td.c { text-align: left; white-space: nowrap; }
      table.grid table.ca-sign tr.tall td { padding-top: 3.6pt !important; padding-bottom: 3.6pt !important; }

      /* Disposition block: keep as one outer grid row, draw only internal lines */
      table.grid table.dispblock { width: 100%; border-collapse: collapse; table-layout: fixed; }
      table.grid table.dispblock td { border: 0 !important; padding: 0.8pt 1.2pt !important; vertical-align: top; }
      table.grid table.dispblock td + td { border-left: 1px solid #000 !important; }
      table.grid table.dispblock tr.dispblock-head td { background: #d9eefb; }
      table.grid table.dispblock tr.dispblock-subhead td { background: #d9eefb; font-weight: 800; }
      table.grid table.dispblock tr.dispblock-body td { border-top: 1px solid #000 !important; }

      .pre { white-space: pre-wrap; }

      /* No clipping; allow content to expand rows */
      .clip { display: inline; }
      .clip--h34, .clip--h44, .clip--h58 { height: auto; }

      /* Allow page breaks naturally */
    </style>
  </head>
  <body>
    <div class="page">
      <div class="sheet">
        <table class="hdr">
          <tr>
            <td>
              <div class="company">{{ $companyName }}</div>
              <div class="title">Nonconformance and Corrective Action Report ( NCAR )</div>
            </td>
            <td class="addr">
              <div>{{ $companyAddr }}</div>
              <div class="small">{{ $companyPhoneTel }}</div>
              <div class="small">{{ $companyPhoneFax }}</div>
            </td>
          </tr>
        </table>

        <table class="grid">
          <colgroup>
            <col style="width:8.33%"><col style="width:8.33%"><col style="width:8.33%"><col style="width:8.33%">
            <col style="width:8.33%"><col style="width:8.33%"><col style="width:8.33%"><col style="width:8.33%">
            <col style="width:8.33%"><col style="width:8.33%"><col style="width:8.33%"><col style="width:8.33%">
          </colgroup>
          <thead>
            <tr class="page-spacer">
              <td colspan="12"></td>
            </tr>
          </thead>
          <tbody>

          <tr class="h18 shade-gray">
            <td class="k kv kv-2" colspan="2">Date:&nbsp;<span class="v">{{ $reportDate }}</span></td>
            <td class="k kv kv-2" colspan="2">NCAR:&nbsp;<span class="v">{{ $ncarNo }}</span></td>
            <td class="k kv kv-4" colspan="3">CoName:&nbsp;<span class="v">{{ $customerCompany }}</span></td>
            <td class="k kv kv-4" colspan="3">Contact:&nbsp;<span class="v">{{ $contact }}</span></td>
            <td class="k kv kv-2" colspan="2">Class:&nbsp;<span class="v">{{ $class }}</span></td>
          </tr>

          <tr class="h18">
            <td class="k" colspan="4">PN:&nbsp;<span class="v">{{ $pn }}{{ $rev ? (' / ' . $rev) : '' }}</span></td>
            <td class="k" colspan="4">Desc.:&nbsp;<span class="v">{{ $partDesc }}</span></td>
            <td class="k" colspan="2">PO#:&nbsp;<span class="v">{{ $poNo }}</span></td>
            <td class="k" colspan="2">Location:&nbsp;<span class="v">{{ $location }}</span></td>
          </tr>

          <tr class="h18 shade">
            <td class="k" colspan="2">WO#:&nbsp;<span class="v">{{ $woNo }}</span></td>
            <td class="k" colspan="2">WOQty.:&nbsp;<span class="v">{{ $woQty }}</span></td>
            <td class="k" colspan="2">COQty.:&nbsp;<span class="v">{{ $coQty }}</span></td>
            <td class="k" colspan="2">DelQty.:&nbsp;<span class="v">{{ $delQty }}</span></td>
            <td class="k" colspan="2">RejQty.:&nbsp;<span class="v">{{ $rejQty }}</span></td>
            <td class="k" colspan="2">Ref#:&nbsp;<span class="v">{{ $refNo }}</span></td>
          </tr>

          <tr class="h18 shade">
            <td class="k" colspan="3">OP# (Tot. # Ops):&nbsp;<span class="v">{{ $ops }}</span></td>
            <td class="k" colspan="3">JobPktCopy?:&nbsp;<span class="v">{{ $jobPktCopy }}</span></td>
            <td class="k" colspan="3">Trav.&amp;Insp.Compl.?:&nbsp;<span class="v">{{ $travInspCompl }}</span></td>
            <td class="k" colspan="3">SamplCompl:&nbsp;<span class="v">{{ $samplCompl }}</span></td>
          </tr>

          <tr class="h18 shade">
            <td class="k" colspan="5">
              <table class="inner" aria-hidden="true">
                <tr>
                  <td>Issue Prcs:&nbsp;<span class="v">{{ $issueFound }}</span></td>
                  <td class="r">Date:&nbsp;<span class="v">{{ $issueFoundDate }}</span></td>
                </tr>
              </table>
            </td>
            <td class="k" colspan="2">StkQty.:&nbsp;<span class="v">{{ $stkQty }}</span></td>
            <td class="k" colspan="3">SP Proc. Invalid.?:&nbsp;<span class="v">{{ $spProcInvalid }}</span></td>
            <td class="k" colspan="2">RodBy:&nbsp;<span class="v">{{ $rodBy }}</span></td>
          </tr>

          <tr class="h52 grow shade-gray">
            <td colspan="11">
              <div class="k">Discrepancy</div>
              <div class="v pre">
                @foreach($discrepancyItems as $it)
                  {{ $it['desc'] }}@if(!$loop->last){{ "\n" }}@endif
                @endforeach
              </div>
            </td>
            <td colspan="1">
              <div class="k">Qty.</div>
              <div class="v pre">
                @foreach($discrepancyItems as $it)
                  {{ trim((string)($it['qty'] ?? '')) !== '' ? $it['qty'] : ($rejQty !== '' ? $rejQty : '1') }}@if(!$loop->last){{ "\n" }}@endif
                @endforeach
              </div>
            </td>
          </tr>

          <tr class="h18 ">
            <td class="k" colspan="4">Containment Req.?:&nbsp;<span class="v">{{ $containmentReq }}</span></td>
            <td class="k" colspan="8">Containment:&nbsp;<span class="v pre">{{ $containment }}</span></td>
          </tr>

          <tr class="h18 shade-gray">
            <td colspan="12" >
              <table class="dispblock" aria-hidden="true">
                <colgroup>
                  <col style="width:8.33%"><col style="width:8.33%"><col style="width:8.33%"><col style="width:8.33%">
                  <col style="width:8.33%"><col style="width:8.33%"><col style="width:8.33%"><col style="width:8.33%">
                  <col style="width:8.33%"><col style="width:8.33%"><col style="width:8.33%"><col style="width:8.33%">
                </colgroup>
                <tr class="dispblock-head">
                  <td class="k" colspan="12">Disposition / Correction: <span class="muted">Use as is (Dev. Apprvl.) / Screen &amp; Rework / Remake / Credit / RTV / Scrap / Other</span></td>
                </tr>
                <tr class="dispblock-subhead">
                  <td colspan="8">Action below / Notes</td>
                  <td colspan="1">AccQty.</td>
                  <td colspan="1">RejQty.</td>
                  <td colspan="1">TM Init</td>
                  <td colspan="1">Date</td>
                </tr>
                <tr class="dispblock-body">
                  <td class="v pre" colspan="8">{{ $actionBelowNotes ?: $disposition }}</td>
                  <td class="v" colspan="1">{{ $accQty }}</td>
                  <td class="v" colspan="1">{{ $rejQty }}</td>
                  <td class="v" colspan="1">{{ $tmInit }}</td>
                  <td class="v" colspan="1"></td>
                </tr>
              </table>
            </td>
          </tr>

          <tr class="h18 ">
            <td class="k" colspan="6">Relevant Function (PLN/PU/ENG/RP/PROD/QC/TM):&nbsp;<span class="v">{{ $relevantFunction }}</span></td>
            <td class="k" colspan="3">Sign-off:&nbsp;<span class="v">&nbsp;</span></td>
            <td class="k" colspan="3">Date:&nbsp;<span class="v">&nbsp;</span></td>
          </tr>

          <tr class="h18 shade">
            <td class="k" colspan="6">Issue found by and how?:&nbsp;<span class="v">{{ $issueFoundBy }}</span></td>
            <td class="k" colspan="4">Req. root cause and corrective action?:&nbsp;<span class="v">{{ $reqRootCause }}</span></td>
            <td class="k" colspan="2">TM Init:&nbsp;<span class="v">{{ $tmInit }}</span></td>
          </tr>

          <tr class="h70 shade">
            <td colspan="12">
              <div class="k">Notes Pre-Root Cause (State Facts)</div>
              <div class="pre v">{{ $notePreRoot }}</div>
            </td>
          </tr>

          <tr class="h70 shade">
            <td colspan="12">
              <div class="k">Personnel Accounts (State Facts)</div>
              <div class="pre v">{{ $personnelAccounts }}</div>
            </td>
          </tr>

          <tr class="h18 shade">
            <td class="k" colspan="2">Personnel Involved</td>
            <td class="k" colspan="2">Position/Area</td>
            <td class="k" colspan="1">Init</td>
            <td class="k" colspan="1">Date</td>
            <td class="k" colspan="2">Personnel Involved</td>
            <td class="k" colspan="2">Position/Area</td>
            <td class="k" colspan="1">Init</td>
            <td class="k" colspan="1">Date</td>
          </tr>
          <tr class="h18">
            <td colspan="2">&nbsp;</td>
            <td colspan="2">&nbsp;</td>
            <td colspan="1">&nbsp;</td>
            <td colspan="1">&nbsp;</td>
            <td colspan="2">&nbsp;</td>
            <td colspan="2">&nbsp;</td>
            <td colspan="1">&nbsp;</td>
            <td colspan="1">&nbsp;</td>
          </tr>

          <tr class="h70 shade-gray">
            <td colspan="12">
              <div class="k">Root Cause</div>
              <div class="v pre">{{ $rootCause }}</div>
            </td>
          </tr>

          <tr class="h18">
            <td class="k" colspan="12">Other Part Process Affected?:&nbsp;<span class="v">{{ $otherPartProcessAffected }}</span></td>
          </tr>

          <tr class="h70 shade-gray grow">
            <td colspan="12" class="ca-cell">
              <div class="k">Corrective Action - CA</div>
              @if($hasCorrective)
                <div class="v pre">{{ $corrective }}</div>
              @endif
              <table class="ca-sign" aria-hidden="true" style="margin-top:{{ $hasCorrective ? '12pt' : '40pt' }};">
                <colgroup>
                  <col style="width:{{ $hasCorrective ? '50%' : '70%' }}">
                  <col style="width:{{ $hasCorrective ? '25%' : '15%' }}">
                  <col style="width:{{ $hasCorrective ? '25%' : '15%' }}">
                </colgroup>
                <tr class="ca-sign-row tall">
                  <td><span class="k">Evaluate:</span></td>
                  <td class="c"><span class="k">TM Sign-off</span>@if($tmSignoff)&nbsp;<span class="v">{{ $tmSignoff }}</span>@endif</td>
                  <td class="c"><span class="k">Date</span>@if($tmSignoffDate)&nbsp;<span class="v">{{ $tmSignoffDate }}</span>@endif</td>
                </tr>
              </table>
            </td>
          </tr>

          <tr class="h70 shade grow">
            <td colspan="12" class="ca-cell">
              <div class="k">Verification of effect of implemented actions (Closure)</div>
              @if($hasVerification)
                <div class="v pre">{{ $verification }}</div>
              @endif
              <table class="ca-sign" aria-hidden="true" style="margin-top:{{ $hasVerification ? '12pt' : '40pt' }};">
                <colgroup>
                  <col style="width:70%">
                  <col style="width:15%">
                  <col style="width:15%">
                </colgroup>
                <tr class="ca-sign-row tall">
                  <td>&nbsp;</td>
                  <td class="c"><span class="k">Completion Sign-off</span>@if($completionSignoff)&nbsp;<span class="v">{{ $completionSignoff }}</span>@endif</td>
                  <td class="c"><span class="k">Date</span>@if($completionSignoffDate)&nbsp;<span class="v">{{ $completionSignoffDate }}</span>@endif</td>
                </tr>
              </table>
            </td>
          </tr>
          </tbody>
        </table>

      </div>
    </div>
  </body>
</html>
