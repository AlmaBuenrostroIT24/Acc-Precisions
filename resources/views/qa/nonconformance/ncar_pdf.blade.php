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
  $completionSignoff = (string) ($ncar->completion_signoff ?? $ncar->completionsignoff ?? '');
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
  $containment = (string) ($ncar->containment ?? '');
  $disposition = (string) ($ncar->disposition ?? '');
  $rootCause = (string) ($ncar->rootcause ?? '');
  $corrective = (string) ($ncar->corrective ?? '');
  $verification = (string) ($ncar->verification ?? '');

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
      @page { margin: 18pt 12pt 44pt; size: letter portrait; }
      html, body { margin: 0; padding: 0; }
      body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #000; }

      /* Align header block with the table width/margins */
      .hdr { width: 96%; margin: 6pt auto 6pt; table-layout: fixed; }
      .hdr td { vertical-align: top; }

      .company { font-weight: 800; font-size: 12px; }
      .title { font-weight: 800; font-size: 12px; }
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

      .k { font-weight: 800; }
      .v { font-weight: 700; white-space: normal; }
      .shade { background: #d9eefb; }
      .muted { color: #222; font-weight: 700; }

      .pre { white-space: pre-wrap; }

      /* No clipping; allow content to expand rows */
      .clip { display: inline; }
      .clip--h34, .clip--h44, .clip--h58 { height: auto; }

      /* Fixed footer: always at the bottom of the page (repeats on each page in Dompdf). */
      .foot {
        position: fixed;
        left: 12pt;
        right: 12pt;
        bottom: 12pt;
      }
      .foot-inner {
        width: 96%;
        margin: 0 auto;
        font-size: 12px;
      }
      .foot .l { float: left; }
      .foot .r { float: right; }
      .clearfix { clear: both; }

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

          <tr class="h18 shade">
            <td class="k">Date</td>
            <td class="v">{{ $reportDate }}</td>
            <td class="k">NCAR</td>
            <td class="v">{{ $ncarNo }}</td>
            <td class="k">CoName</td>
            <td class="v" colspan="3">{{ $customerCompany }}</td>
            <td class="k">Contact</td>
            <td class="v">{{ $contact }}</td>
            <td class="k">Class</td>
            <td class="v">{{ $class }}</td>
          </tr>

          <tr class="h18 shade">
            <td class="k">PN</td>
            <td class="v" colspan="3">{{ $pn }}{{ $rev ? (' / ' . $rev) : '' }}</td>
            <td class="k">Desc.</td>
            <td class="v" colspan="3">{{ $partDesc }}</td>
            <td class="k">PO#</td>
            <td class="v">{{ $poNo }}</td>
            <td class="k">Location</td>
            <td class="v">{{ $location }}</td>
          </tr>

          <tr class="h18 shade">
            <td class="k">WO#</td>
            <td class="v">{{ $woNo }}</td>
            <td class="k">WOQty.</td>
            <td class="v">{{ $woQty }}</td>
            <td class="k">COQty.</td>
            <td class="v">{{ $coQty }}</td>
            <td class="k">DelQty.</td>
            <td class="v">{{ $delQty }}</td>
            <td class="k">RejQty.</td>
            <td class="v">{{ $rejQty }}</td>
            <td class="k">Ref#</td>
            <td class="v">{{ $refNo }}</td>
          </tr>

          <tr class="h18 shade">
            <td class="k" colspan="2">OP# (Tot. # Ops)</td>
            <td class="v">{{ $ops }}</td>
            <td class="k">JobPktCopy?</td>
            <td class="v">{{ $jobPktCopy }}</td>
            <td class="k" colspan="2">Trav.&amp;Insp.Compl.?</td>
            <td class="v">{{ $travInspCompl }}</td>
            <td class="k">SamplCompl</td>
            <td class="v">{{ $samplCompl }}</td>
            <td class="k">RodBy</td>
            <td class="v">{{ $rodBy }}</td>
          </tr>

          <tr class="h18 shade">
            <td class="k" colspan="2">Issue Found</td>
            <td class="v" colspan="3">{{ $issueFound }}</td>
            <td class="k">Date</td>
            <td class="v">{{ $issueFoundDate }}</td>
            <td class="k">StkQty.</td>
            <td class="v">{{ $stkQty }}</td>
            <td class="k" colspan="2">SP Proc. Invalid.?</td>
            <td class="v">{{ $spProcInvalid }}</td>
          </tr>

          <tr class="h18 shade">
            <td class="k" colspan="11">Discrepancy</td>
            <td class="k">Qty.</td>
          </tr>
          <tr class="h52">
            <td class="v pre" colspan="11">{{ $discrepancy }}</td>
            <td class="v">{{ $rejQty ?: '1' }}</td>
          </tr>

          <tr class="h18 shade">
            <td class="k" colspan="2">Containment Req.?</td>
            <td class="v">{{ $containmentReq }}</td>
            <td class="k">Containment:</td>
            <td class="v pre" colspan="8">{{ $containment }}</td>
          </tr>

          <tr class="h18 shade">
            <td class="k" colspan="12">Disposition / Correction: <span class="muted">Use as is (Dev. Apprvl.) / Screen &amp; Rework / Remake / Credit / RTV / Scrap / Other</span></td>
          </tr>
          <tr class="h22 shade">
            <td class="k" colspan="8">Action below / Notes</td>
            <td class="k">AccQty.</td>
            <td class="k">RejQty.</td>
            <td class="k">TM Init</td>
            <td class="k">Date</td>
          </tr>
          <tr class="h34">
            <td class="v pre" colspan="8">{{ $actionBelowNotes ?: $disposition }}</td>
            <td class="v">{{ $accQty }}</td>
            <td class="v">{{ $rejQty }}</td>
            <td class="v">{{ $tmInit }}</td>
            <td class="v"></td>
          </tr>

          <tr class="h18 shade">
            <td class="k" colspan="6">Relevant Function (PLN/PU/ENG/RP/PROD/QC/TM)</td>
            <td class="k" colspan="3">Sign-off</td>
            <td class="k" colspan="3">Date</td>
          </tr>
          <tr class="h18">
            <td class="v" colspan="6">{{ $relevantFunction }}</td>
            <td class="v" colspan="3"></td>
            <td class="v" colspan="3"></td>
          </tr>

          <tr class="h18 shade">
            <td class="k" colspan="6">Issue found by and how?</td>
            <td class="k" colspan="4">Req. root cause and corrective action?</td>
            <td class="k" colspan="2">TM Init</td>
          </tr>
          <tr class="h18">
            <td class="v" colspan="6">{{ $issueFoundBy }}</td>
            <td class="v" colspan="4">{{ $reqRootCause }}</td>
            <td class="v" colspan="2">{{ $tmInit }}</td>
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
            <td class="k" colspan="12">Root Cause</td>
          </tr>
          <tr class="h52">
            <td class="v pre" colspan="12">{{ $rootCause }}</td>
          </tr>

          <tr class="h18 shade">
            <td class="k" colspan="4">Other Part Process Affected?</td>
            <td class="v" colspan="8">{{ $otherPartProcessAffected }}</td>
          </tr>

          <tr class="h18 shade">
            <td class="k" colspan="12">Corrective Action - CA</td>
          </tr>
          <tr class="h52">
            <td class="v pre" colspan="12">{{ $corrective }}</td>
          </tr>

          <tr class="h18">
            <td colspan="9"></td>
            <td class="k" colspan="2">TM Sign-off</td>
            <td class="v">{{ $tmSignoff }}</td>
          </tr>

          <tr class="h18 shade">
            <td class="k" colspan="12">Verification of effect of implemented actions (Closure)</td>
          </tr>
          <tr class="h52">
            <td class="v pre" colspan="12">{{ $verification }}</td>
          </tr>

          <tr class="h18">
            <td colspan="9"></td>
            <td class="k" colspan="2">Completion Sign-off</td>
            <td class="v">{{ $completionSignoff }}</td>
          </tr>
        </table>

        <div class="foot">
          <div class="foot-inner">
            <div class="l">F-870-001 Rev. D&nbsp;&nbsp;LA Authorized</div>
            <div class="r">Page 1 of 1</div>
            <div class="clearfix"></div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
