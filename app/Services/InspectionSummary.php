<?php

namespace App\Services;

use App\Models\QaFaiSummary;
use App\Models\OrderSchedule;

class InspectionSummary
{
  public function summarize(OrderSchedule $order, ?int $operations = null, ?int $sampling = null): array
  {
    $operations = $operations ?? (int)($order->operation ?? 0);
    $sampling   = $sampling   ?? (int)($order->sampling  ?? 0);

    if ($operations < 0) $operations = 0;
    if ($sampling   < 0) $sampling   = 0;

    // 👇 Requisito real por operación
    $ipiReqPerOp = max(0, $sampling - 1);

    $faiPass = [];
    $faiFail = [];
    $ipiPass = [];
    $ipiFail = [];

    $rows = QaFaiSummary::query()
      ->where('order_schedule_id', $order->id)
      ->get(['insp_type', 'operation', 'results', 'qty_pcs']);

    $maxOpNum = 0;

    foreach ($rows as $r) {
      $type = strtoupper((string)$r->insp_type);

      $res = strtolower(trim((string)$r->results));
      if ($res === 'nopass' || $res === 'no_pass' || $res === 'fail') $res = 'no pass';
      if (!in_array($res, ['pass', 'no pass'], true)) continue;

      $rawOp = trim((string)$r->operation);
      if ($rawOp === '') continue;

      // Normaliza "1st Op" -> "1st"
      $op = str_ends_with($rawOp, ' Op') ? substr($rawOp, 0, -3) : $rawOp;

      $numFromOp = $this->ordinalToInt($op);
      if ($numFromOp > $maxOpNum) $maxOpNum = $numFromOp;

      $qty = (int)($r->qty_pcs ?? 0);
      // if ($qty <= 0) $qty = 1; // si quieres asegurar mínimo 1, descomenta

      if ($type === 'FAI') {
        // FAI cuenta como 1; el excedente cuenta como IPI (pass) para la misma operación.
        $faiQty = min(1, max(0, $qty));
        $spillToIpi = max(0, $qty - $faiQty);

        if ($res === 'pass') {
          $faiPass[$op] = ($faiPass[$op] ?? 0) + $faiQty;
          if ($spillToIpi > 0) $ipiPass[$op] = ($ipiPass[$op] ?? 0) + $spillToIpi;
        } else {
          $faiFail[$op] = ($faiFail[$op] ?? 0) + $faiQty;
        }
      } elseif ($type === 'IPI') {
        if ($res === 'pass') $ipiPass[$op] = ($ipiPass[$op] ?? 0) + $qty;
        else                 $ipiFail[$op] = ($ipiFail[$op] ?? 0) + $qty;
      }
    }

    if ($operations === 0 && $maxOpNum > 0) $operations = $maxOpNum;

    $sum = fn(array $m) => array_sum(array_values($m));
    $faiPassTotal = $sum($faiPass);
    $faiFailTotal = $sum($faiFail);
    $ipiPassTotal = $sum($ipiPass);
    $ipiFailTotal = $sum($ipiFail);

    $faiRealizados = $faiPassTotal + $faiFailTotal;
    $ipiRealizados = $ipiPassTotal + $ipiFailTotal;

    $faiReqTotal = $operations * 1;
    $ipiReqTotal = $operations * $ipiReqPerOp; // 👈 total requerido corregido

    $faiPct = $faiReqTotal > 0 ? round(($faiPassTotal / $faiReqTotal) * 100, 1) : 0.0;
    $ipiPct = $ipiReqTotal > 0 ? round(($ipiPassTotal / $ipiReqTotal) * 100, 1) : 0.0;

    $lines = [];
    $faltantes = false;

    // Íconos compatibles con PDF/DejaVu
    $ICON_OK   = '&#10003;'; // ✓
    $ICON_FAIL = '&#10007;'; // ✗
    $ICON_WARN = '&#9888;';  // ⚠

    for ($i = 1; $i <= $operations; $i++) {
      $op = $this->ordinalSuffix($i);

      $fPass = $faiPass[$op] ?? 0;
      $fFail = $faiFail[$op] ?? 0;
      $iPass = $ipiPass[$op] ?? 0;
      $iFail = $ipiFail[$op] ?? 0;

      $faiReq = 1;
      $ipiReq = $ipiReqPerOp; // 👈 por operación

      $fDone = $fPass + $fFail;
      $iDone = $iPass + $iFail;

      $faiOk = $fPass >= $faiReq;
      $ipiOk = $iPass >= $ipiReq;

      $global = (!$faiOk && !$ipiOk) ? $ICON_FAIL : (($faiOk && $ipiOk) ? $ICON_OK : $ICON_WARN);

      // Texto compacto para celdas
      $faiTxt = ($faiOk
        ? "({$ICON_OK})  Pass:({$fPass} for {$faiReq}), No Pass:{$fFail}, Completed:{$fDone}"
        : "({$ICON_FAIL}) Pass:({$fPass} for {$faiReq}), No Pass:{$fFail}, Completed:{$fDone}");

      $ipiTxt = ($ipiOk
        ? "({$ICON_OK})  Pass:({$iPass} for {$ipiReq}), No Pass:{$iFail}, Completed:{$iDone}"
        : "({$ICON_FAIL})  Pass:({$iPass} for {$ipiReq}), No Pass:{$iFail}, Completed:{$iDone}");

      if (!$faiOk || !$ipiOk) $faltantes = true;

      $lines[] = [
        'op'     => $op,
        'global' => $global,
        'fai'    => $faiTxt,
        'ipi'    => $ipiTxt,
        'done'   => ($faiOk && $ipiOk),
      ];
    }

    // ===== Render como TABLA =====
    $tableRows = '';
    foreach ($lines as $l) {
      $tableRows .= "
        <tr>
          <td style='text-align:center; width:34px;'>{$l['global']}</td>
          <td style='white-space:nowrap; width:52px;'><strong>{$l['op']}</strong></td>
          <td>{$l['fai']}</td>
          <td>{$l['ipi']}</td>
        </tr>";
    }

    $tableHtml = "
    <div style='margin-top:10px; page-break-inside: avoid;'>
      <table style='width:100%; border-collapse:collapse; font-family:DejaVu Sans, Arial; font-size:12px;'>
        <thead>
          <tr>
            <th style='border:1px solid #ccc; padding:4px 6px; text-align:center; width:34px;'>∑</th>
            <th style='border:1px solid #ccc; padding:4px 6px; text-align:left;  width:52px;'>Op</th>
            <th style='border:1px solid #ccc; padding:4px 6px; text-align:left;'>FAI</th>
            <th style='border:1px solid #ccc; padding:4px 6px; text-align:left;'>IPI</th>
          </tr>
        </thead>
        <tbody>
          " . str_replace('<tr>', "<tr style='border:1px solid #ccc;'>", $tableRows) . "
        </tbody>
      </table>
    </div>";

    $summaryHtml = "
    <div style='margin-top:10px; page-break-inside: avoid;'>
      <table style='width:100%; border-collapse:collapse; font-family:DejaVu Sans, Arial; font-size:12px;'>
        <thead>
          <tr>
            <th colspan='5' style='border:1px solid #ccc; padding:4px 6px; text-align:left; background:#f2f2f2;'>
              — FAI/IPI Inspection Packet Summary —
            </th>
          </tr>
          <tr>
            <th style='border:1px solid #ccc; padding:4px 6px; width:40px;'>Type</th>
            <th style='border:1px solid #ccc; padding:4px 6px;'>Req’d</th>
            <th style='border:1px solid #ccc; padding:4px 6px;'>Completed </th>
            <th style='border:1px solid #ccc; padding:4px 6px;'>Fail</th>
            <th style='border:1px solid #ccc; padding:4px 6px;'>(%)</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style='border:1px solid #ccc; padding:4px 6px;'><strong>FAI</strong></td>
            <td style='border:1px solid #ccc; padding:4px 6px;'>{$faiReqTotal}</td>
            <td style='border:1px solid #ccc; padding:4px 6px;'>{$faiPassTotal}</td>
            <td style='border:1px solid #ccc; padding:4px 6px;'>{$faiFailTotal}</td>
            <td style='border:1px solid #ccc; padding:4px 6px;'>{$faiPct}%</td>
          </tr>
          <tr>
            <td style='border:1px solid #ccc; padding:4px 6px;'><strong>IPI</strong></td>
            <td style='border:1px solid #ccc; padding:4px 6px;'>{$ipiReqTotal}</td>
            <td style='border:1px solid #ccc; padding:4px 6px;'>{$ipiPassTotal}</td>
            <td style='border:1px solid #ccc; padding:4px 6px;'>{$ipiFailTotal}</td>
            <td style='border:1px solid #ccc; padding:4px 6px;'>{$ipiPct}%</td>
          </tr>
        </tbody>
      </table>
    </div>";

    $html = $tableHtml . $summaryHtml;

    return [
      'lines' => $lines,
      'totals' => [
        'faiPass' => $faiPassTotal,
        'faiFail' => $faiFailTotal,
        'ipiPass' => $ipiPassTotal,
        'ipiFail' => $ipiFailTotal,
        'faiReq'  => $faiReqTotal,
        'ipiReq'  => $ipiReqTotal, // 👈 ya con sampling-1
        'faiPct'  => $faiPct,
        'ipiPct'  => $ipiPct,
      ],
      'has_missing' => $faltantes,
      'html'        => $html,       // tabla + resumen
      'html_table'  => $tableHtml,  // solo tabla si la necesitas
    ];
  }



  private function ordinalSuffix(int $n): string
  {
    $j = $n % 10;
    $k = $n % 100;
    if ($j == 1 && $k != 11) return $n . 'st';
    if ($j == 2 && $k != 12) return $n . 'nd';
    if ($j == 3 && $k != 13) return $n . 'rd';
    return $n . 'th';
  }

  private function ordinalToInt(string $op): int
  {
    // "2nd" -> 2; "10th" -> 10; tolera espacios
    $op = trim($op);
    if ($op === '') return 0;
    // quita sufijos st/nd/rd/th si vienen
    if (preg_match('/^(\d+)\s*(st|nd|rd|th)$/i', $op, $m)) {
      return (int)$m[1];
    }
    // si viene tal cual número
    if (ctype_digit($op)) return (int)$op;
    return 0;
  }
}
