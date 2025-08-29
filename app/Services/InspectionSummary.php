<?php

namespace App\Services;

use App\Models\QaFaiSummary;
use App\Models\OrderSchedule;

class InspectionSummary
{
    public function summarize(OrderSchedule $order, ?int $operations = null, ?int $sampling = null): array
    {
        $operations = $operations ?? (int)($order->operation ?? 0);
        $sampling   = $sampling   ?? (int)($order->sampling ?? 0);

        if ($operations < 0) $operations = 0;
        if ($sampling   < 0) $sampling   = 0;

        $faiPass = [];
        $faiFail = [];
        $ipiPass = [];
        $ipiFail = [];

        $rows = QaFaiSummary::query()
            ->where('order_schedule_id', $order->id)
            ->get(['insp_type', 'operation', 'results', 'qty_pcs']);

        // Si no hay ops en header, dedúcelas del máximo ordinal encontrado
        $maxOpNum = 0;

        foreach ($rows as $r) {
            $type = strtoupper((string)$r->insp_type);

            // Normalizar resultados
            $res = strtolower(trim((string)$r->results));
            if ($res === 'nopass' || $res === 'no_pass' || $res === 'fail') {
                $res = 'no pass';
            }
            if (!in_array($res, ['pass', 'no pass'], true)) {
                continue; // ignora otros estados
            }

            // Normalizar operación: "2nd Op" -> "2nd"
            $rawOp = trim((string)$r->operation);
            if ($rawOp === '') continue;

            if (str_ends_with($rawOp, ' Op')) {
                $op = substr($rawOp, 0, -3);
            } else {
                $op = $rawOp;
            }

            // Actualiza maxOpNum para fallback de $operations
            $numFromOp = $this->ordinalToInt($op); // "2nd" -> 2; si no reconoce, 0
            if ($numFromOp > $maxOpNum) $maxOpNum = $numFromOp;

            // Cantidad de piezas (si tu modelo no guarda qty, puedes usar 1)
            $qty = (int)($r->qty_pcs ?? 0);
            // Si tu diseño significa "una fila = 1 pieza", descomenta:
            // if ($qty <= 0) $qty = 1;

            if ($type === 'FAI') {
                if ($res === 'pass') $faiPass[$op] = ($faiPass[$op] ?? 0) + $qty;
                else                 $faiFail[$op] = ($faiFail[$op] ?? 0) + $qty;
            } elseif ($type === 'IPI') {
                if ($res === 'pass') $ipiPass[$op] = ($ipiPass[$op] ?? 0) + $qty;
                else                 $ipiFail[$op] = ($ipiFail[$op] ?? 0) + $qty;
            }
        }

        if ($operations === 0 && $maxOpNum > 0) {
            $operations = $maxOpNum; // fallback inteligente
        }

        $sum = fn(array $m) => array_sum(array_values($m));
        $faiPassTotal = $sum($faiPass);
        $faiFailTotal = $sum($faiFail);
        $ipiPassTotal = $sum($ipiPass);
        $ipiFailTotal = $sum($ipiFail);

        $faiRealizados = $faiPassTotal + $faiFailTotal;
        $ipiRealizados = $ipiPassTotal + $ipiFailTotal;

        $faiReqTotal = $operations * 1;
        $ipiReqTotal = $operations * $sampling;

        $faiPct = $faiReqTotal > 0 ? round(($faiPassTotal / $faiReqTotal) * 100, 1) : 0.0;
        $ipiPct = $ipiReqTotal > 0 ? round(($ipiPassTotal / $ipiReqTotal) * 100, 1) : 0.0;

        $lines = [];
        $faltantes = false;

        // Íconos compatibles con DejaVu Sans
        $ICON_OK   = '&#10003;'; // ✓  U+2713
        $ICON_FAIL = '&#10007;'; // ✗  U+2717
        $ICON_WARN = '&#9888;';        // o &#9650; (▲)

        for ($i = 1; $i <= $operations; $i++) {
            $op = $this->ordinalSuffix($i); // "1st","2nd",...

            $fPass = $faiPass[$op] ?? 0;
            $fFail = $faiFail[$op] ?? 0;
            $iPass = $ipiPass[$op] ?? 0;
            $iFail = $ipiFail[$op] ?? 0;

            $faiReq = 1;
            $ipiReq = $sampling;

            $fDone = $fPass + $fFail;
            $iDone = $iPass + $iFail;

            $faiOk = $fPass >= $faiReq;
            $ipiOk = $iPass >= $ipiReq;

            $global = (!$faiOk && !$ipiOk) ? $ICON_FAIL : (($faiOk && $ipiOk) ? $ICON_OK : $ICON_WARN);

            $faiTxt = ($faiOk
                ? "{$ICON_OK} <strong>FAI:</strong> P:({$fPass}/{$faiReq}), NP:{$fFail}, Done:{$fDone})"
                : "{$ICON_FAIL} <strong>FAI:</strong> P:({$fPass}/{$faiReq}), NP:{$fFail}, Done:{$fDone})");

            $ipiTxt = ($ipiOk
                ? "{$ICON_OK} <strong>IPI:</strong> P:({$iPass}/{$ipiReq}), NP:{$iFail}, Done:{$iDone})"
                : "{$ICON_FAIL} <strong>IPI:</strong> P:({$iPass}/{$ipiReq}), NP:{$iFail}, Done:{$iDone})");

            if (!$faiOk || !$ipiOk) $faltantes = true;

            $lines[] = [
                'op'     => $op,
                'global' => $global,
                'fai'    => $faiTxt,
                'ipi'    => $ipiTxt,
                'done'   => ($faiOk && $ipiOk),
            ];
        }

        $htmlLines = array_map(fn($l) => "{$l['global']} {$l['op']} | {$l['fai']} | {$l['ipi']}", $lines);

        $html  = implode('<br>', $htmlLines);
        $html .= '<br><br><strong>— FAI/IPI Inspection Packet Summary —</strong><br>';
        $html .= "FAI → P:{$faiPassTotal}, NP:{$faiFailTotal}, Need:{$faiReqTotal}, Done:{$faiRealizados} ({$faiPct}%)<br>";
        $html .= "IPI → P:{$ipiPassTotal}, NP:{$ipiFailTotal}, Need:{$ipiReqTotal}, Done:{$ipiRealizados} ({$ipiPct}%)";

        return [
            'lines' => $lines,
            'totals' => [
                'faiPass' => $faiPassTotal,
                'faiFail' => $faiFailTotal,
                'ipiPass' => $ipiPassTotal,
                'ipiFail' => $ipiFailTotal,
                'faiReq'  => $faiReqTotal,
                'ipiReq'  => $ipiReqTotal,
                'faiPct'  => $faiPct,
                'ipiPct'  => $ipiPct,
            ],
            'has_missing' => $faltantes,
            'html' => $html,
        ];
    }

    private function ordinalSuffix(int $n): string
    {
        $j = $n % 10; $k = $n % 100;
        if ($j == 1 && $k != 11) return $n.'st';
        if ($j == 2 && $k != 12) return $n.'nd';
        if ($j == 3 && $k != 13) return $n.'rd';
        return $n.'th';
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