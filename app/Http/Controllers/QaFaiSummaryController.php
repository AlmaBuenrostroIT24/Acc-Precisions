<?php

namespace App\Http\Controllers;

use App\Models\QaFaiSummary;
use App\Models\OrderScheduleLog;
use Illuminate\Http\Request;
use App\Models\OrderSchedule;
use Illuminate\Support\Facades\Log;
use App\Models\QaSamplingPlan;
use Illuminate\Support\Facades\DB; // si no tienes modelo Status
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use PDF;
use App\Services\InspectionSummary;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use App\Exports\BladeTableExport;

class QaFaiSummaryController extends Controller
{

    /**
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++START+++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * ===================================================================================================================
     * 1. TAB "FAI/IPI SUMMARY" ------------Parts Revision----------------
     * ===================================================================================================================
     */
    //abr.01.26-(3.12 se completo el diseno faicomplete)
    //abr.03.26-(4.1 se acomodo el error de guardado de hora en el modal faisummary yla vizuallizacion de la alerta FAI no pass)

    public function partsrevision()
    {
        // Solo devuelve la vista; las tablas vendrán por AJAX
        return view('qa.faisummary.faisummary_partsrevision');
    }
    //	31009/1

    public function partsrevisionData(Request $request)
    {
        try {
            $bucket = $request->query('bucket');
            $draw = (int) $request->input('draw', 0);
            if (!in_array($bucket, ['empty', 'process'], true)) {
                return response()->json([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ]);
            }

            $user = auth()->user();

            $select = [
                'id',
                'parent_id',
                'work_id',
                'PN',
                'co',
                'cust_po',
                'Part_description',
                'operation',
                'wo_qty',
                'group_wo_qty',
                'due_date',
                'location',
                'status_inspection',
                'sampling',
                'sampling_check',
                'inspection_progress',
            ];

            // Campos opcionales (evita "Unknown column" si la DB no está migrada)
            foreach (['co', 'cust_po', 'costumer', 'qty', 'ncr_number', 'ncr_notes'] as $col) {
                if (Schema::hasColumn('orders_schedule', $col)) {
                    $select[] = $col;
                }
            }

            $hasQtyCol = Schema::hasColumn('orders_schedule', 'qty');
            if ($hasQtyCol) {
                // Para modal NCR: suma de qty de hijos (si existen)
                $select[] = DB::raw(
                    "(SELECT SUM(COALESCE(c.qty,0)) FROM orders_schedule c
                      WHERE c.parent_id = orders_schedule.id
                    ) as qty_children_sum"
                );
            }

            $hasNcrCols = Schema::hasColumn('orders_schedule', 'ncr_number') && Schema::hasColumn('orders_schedule', 'ncr_notes');

            $start = max(0, (int) $request->input('start', 0));
            $length = (int) $request->input('length', 15);
            if ($length <= 0) {
                $length = 15;
            }
            $searchValue = trim((string) data_get($request->all(), 'search.value', ''));
            $orderColIndex = (int) data_get($request->all(), 'order.0.column', ($bucket === 'empty' ? 2 : 3));
            $orderDir = strtolower((string) data_get($request->all(), 'order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

            $baseQuery = OrderSchedule::query()
                ->select($select)
                ->where('status', '<>', 'sent')
                ->where('status_order', '!=', 'inactive')
                ->where(function ($q) {
                    $q->whereNull('status_inspection')
                        ->orWhere('status_inspection', '<>', 'completed');
                })
                ->whereNull('parent_id')
                // 🔒 Siempre limitamos a Yarnell + Hearst
                ->whereRaw('LOWER(location) IN (?, ?)', ['yarnell', 'hearst'])

                // 👇 Filtros por rol, pero se saltan si el usuario tiene QAAll
                ->when(
                    $user && $user->hasRole('QAdmin') && !$user->hasRole('QA'),
                    fn($q) => $q->whereRaw('LOWER(location) = ?', ['yarnell'])
                )
                ->when(
                    $user && ($user->hasRole('QASupportHearst')) && !$user->hasRole('QA'),
                    fn($q) => $q->whereRaw('LOWER(location) = ?', ['hearst'])
                )

                // Buckets de inspección
                ->when(
                    $bucket === 'empty',
                    fn($q) => $q->where(
                        fn($w) =>
                        $w->whereNull('status_inspection')
                            ->orWhere('status_inspection', 'pending')
                    ),
                    fn($q) => $q->where('status_inspection', 'in_progress')
                );

            $recordsTotal = (clone $baseQuery)->count();

            if ($searchValue !== '') {
                $searchLike = '%' . $searchValue . '%';
                $baseQuery->where(function ($q) use ($searchLike) {
                    $q->where('PN', 'like', $searchLike)
                        ->orWhere('Part_description', 'like', $searchLike)
                        ->orWhere('work_id', 'like', $searchLike)
                        ->orWhereRaw("DATE_FORMAT(due_date, '%b/%d/%Y') like ?", [$searchLike])
                        ->orWhereRaw("DATE_FORMAT(due_date, '%Y-%m-%d') like ?", [$searchLike]);
                });
            }

            $recordsFiltered = (clone $baseQuery)->count();

            $orderMap = $bucket === 'empty'
                ? [0 => 'PN', 1 => 'work_id', 2 => 'due_date']
                : [0 => 'PN', 1 => 'work_id', 3 => 'due_date'];
            $orderColumn = $orderMap[$orderColIndex] ?? 'due_date';

            $rows = $baseQuery
                ->orderBy($orderColumn, $orderDir)
                ->offset($start)
                ->limit($length)
                ->get();

            $orderIds = ($bucket === 'process')
                ? $rows->pluck('id')->filter()->map(fn($v) => (int) $v)->values()->all()
                : [];

            // FAI por operación (pass/fail) para estado de timeline y bandera pendiente
            $faiPendingMap = [];
            $faiOpsMetaMap = [];
            $passAggMap = [];
            $qaQtyExpr = '1';
            if ($bucket === 'process' && !empty($orderIds)) {
                $qaQtyExpr = Schema::hasColumn('qa_faisummary', 'qty_pcs')
                    ? 'qty_pcs'
                    : (Schema::hasColumn('qa_faisummary', 'sample_idx')
                        ? 'sample_idx'
                        : '1');

                $faiOpRows = DB::table('qa_faisummary')
                    ->select([
                        'order_schedule_id',
                        'operation',
                        DB::raw("SUM(CASE WHEN LOWER(TRIM(COALESCE(results,''))) = 'pass' THEN 1 ELSE 0 END) as pass_cnt"),
                        DB::raw("SUM(CASE WHEN LOWER(TRIM(COALESCE(results,''))) IN ('no pass','nopass','no_pass','fail') THEN 1 ELSE 0 END) as fail_cnt"),
                    ])
                    ->whereIn('order_schedule_id', $orderIds)
                    ->whereRaw("UPPER(TRIM(COALESCE(insp_type,''))) = 'FAI'")
                    ->groupBy('order_schedule_id', 'operation')
                    ->get();

                foreach ($faiOpRows as $fr) {
                    $oid = (int) ($fr->order_schedule_id ?? 0);
                    $op = trim((string) ($fr->operation ?? ''));
                    $hasPass = (int) ($fr->pass_cnt ?? 0) > 0;
                    $hasFail = (int) ($fr->fail_cnt ?? 0) > 0;
                    if ($oid > 0 && $op !== '') {
                        $status = $hasPass ? 'ok' : ($hasFail ? 'fail' : 'pending');
                        if (!isset($faiOpsMetaMap[$oid])) {
                            $faiOpsMetaMap[$oid] = [];
                        }
                        $faiOpsMetaMap[$oid][$op] = [
                            'pass_cnt' => (int) ($fr->pass_cnt ?? 0),
                            'fail_cnt' => (int) ($fr->fail_cnt ?? 0),
                            'status' => $status,
                        ];
                    }
                    if ($oid > 0 && $hasFail && !$hasPass) {
                        $faiPendingMap[$oid] = true;
                    }
                }

                // Agregado de PASS por orden/op/tipo para evitar query por cada fila (N+1)
                $passAggRows = DB::table('qa_faisummary')
                    ->select([
                        'order_schedule_id',
                        'operation',
                        'insp_type',
                        DB::raw("SUM(COALESCE($qaQtyExpr,1)) as qty"),
                    ])
                    ->whereIn('order_schedule_id', $orderIds)
                    ->whereRaw('LOWER(results) = ?', ['pass'])
                    ->groupBy('order_schedule_id', 'operation', 'insp_type')
                    ->get();

                foreach ($passAggRows as $pr) {
                    $oid = (int) ($pr->order_schedule_id ?? 0);
                    $op = trim((string) ($pr->operation ?? ''));
                    if ($oid <= 0 || $op === '') {
                        continue;
                    }
                    $type = strtoupper(trim((string) ($pr->insp_type ?? '')));
                    if ($type !== 'FAI' && $type !== 'IPI') {
                        continue;
                    }
                    if (!isset($passAggMap[$oid])) {
                        $passAggMap[$oid] = [];
                    }
                    if (!isset($passAggMap[$oid][$op])) {
                        $passAggMap[$oid][$op] = ['FAI' => 0, 'IPI' => 0];
                    }
                    $passAggMap[$oid][$op][$type] += (int) ($pr->qty ?? 0);
                }
            }

            // NCAR status per order (internal/external) for UI indicator in Process table
            $ncarMap = [];
            if ($bucket === 'process' && Schema::hasTable('qa_ncar') && Schema::hasTable('qa_ncartype')) {
                if (!empty($orderIds)) {
                    $ncarRows = DB::table('qa_ncar as n')
                        ->join('qa_ncartype as t', 't.id', '=', 'n.ncartype_id')
                        ->select([
                            'n.id',
                            'n.order_id',
                            'n.ncar_no',
                            'n.nc_description',
                            'n.stage',
                            't.code as type_code', // INTERNAL / EXTERNAL
                        ])
                        ->whereIn('n.order_id', $orderIds)
                        ->orderByDesc('n.id')
                        ->get();

                    foreach ($ncarRows as $nr) {
                        $oid = (int) ($nr->order_id ?? 0);
                        if (!$oid) continue;

                        $code = strtoupper(trim((string) ($nr->type_code ?? '')));
                        if (!in_array($code, ['INTERNAL', 'EXTERNAL'], true)) continue;

                        if (!isset($ncarMap[$oid])) {
                            $ncarMap[$oid] = ['INTERNAL' => null, 'EXTERNAL' => null];
                        }

                        // Keep only latest per type (query is DESC by id)
                        if ($ncarMap[$oid][$code] === null) {
                            $ncarMap[$oid][$code] = $nr;
                        }
                    }
                }
            }


            $opKey = function (int $i): string {
                return match ($i) {
                    1 => '1st Op',
                    2 => '2nd Op',
                    3 => '3rd Op',
                    default => "{$i}th Op"
                };
            };

            $opLabel = function (int $i): string {
                return match ($i) {
                    1 => '1st',
                    2 => '2nd',
                    3 => '3rd',
                    default => "{$i}th"
                };
            };

            $updates = [];

            // 👇 capturamos &$updates por referencia
            $data = $rows->map(function ($r) use ($bucket, $opKey, $opLabel, $hasNcrCols, $ncarMap, $faiPendingMap, $faiOpsMetaMap, $passAggMap, $hasQtyCol, &$updates) {
                $pn   = trim((string) $r->PN);
                $desc = trim(\Illuminate\Support\Str::before((string) $r->Part_description, ','));
                $part = $pn . ' - ' . $desc;

                // 2025-12-17: enviar una fecha "sort" (YYYY-MM-DD) para que DataTables ordene correctamente
                // aunque el display sea M/d/Y.
                $dueSort = $r->due_date ? \Carbon\Carbon::parse($r->due_date)->format('Y-m-d') : null;
                $dueFormatted = $r->due_date ? \Carbon\Carbon::parse($r->due_date)->format('M/d/Y') : null;

                $sum = (int) ($r->group_wo_qty ?? 0);

                $btn = '<button class="btn btn-sm btn-erp-primary erp-table-btn"
              data-toggle="modal" data-target="#editModal"
              data-id="' . e($r->id) . '"
              data-workid="' . e($r->work_id) . '"
              data-woqty="' . e($sum) . '"
              data-operation="' . e($r->operation) . '"
              data-pn="' . e($r->PN) . '"
              data-description="' . e($r->Part_description) . '"
              data-sampling="' . e($r->sampling ?? 0) . '"
              data-sampling_check="' . e($r->sampling_check ?? 'Normal') . '">
              <i class="fas fa-edit"></i>
            </button>';

                $btnOther = '';
                if ($bucket === 'process') {
                    // NCAR indicator: internal => blue, external => orange
                    $orderId = (int) ($r->id ?? 0);
                    $info = $orderId && isset($ncarMap[$orderId]) ? $ncarMap[$orderId] : ['INTERNAL' => null, 'EXTERNAL' => null];
                    $ext = $info['EXTERNAL'] ?? null;
                    $int = $info['INTERNAL'] ?? null;

                    $hasExt = !!$ext;
                    $hasInt = !!$int;
                    $hasAny = $hasExt || $hasInt;

                    $btnClass = $hasExt ? 'btn-erp-warning' : ($hasInt ? 'btn-erp-primary' : 'btn-erp-secondary');
                    $hasBoth = $hasExt && $hasInt;
                    $btnToneClass = $hasBoth
                        ? 'btn-ncr--both'
                        : ($hasExt ? 'btn-ncr--external' : ($hasInt ? 'btn-ncr--internal' : 'btn-ncr--none'));
                    $typeLabel = $hasBoth ? 'External + Internal' : ($hasExt ? 'External' : ($hasInt ? 'Internal' : ''));
                    $ncarNoExt = $hasExt ? (string) ($ext->ncar_no ?? '') : '';
                    $ncarNoInt = $hasInt ? (string) ($int->ncar_no ?? '') : '';
                    // Para compatibilidad con el modal actual (un solo campo), mantenemos un número "principal"
                    // (prioriza External si existe).
                    $ncarNo = $hasExt ? $ncarNoExt : ($hasInt ? $ncarNoInt : '');
                    $ncarNotes = $hasExt ? (string) ($ext->nc_description ?? '') : ($hasInt ? (string) ($int->nc_description ?? '') : '');
                    $ncarType = $hasExt ? 'external' : ($hasInt ? 'internal' : '');
                    $ncarStage = $hasExt ? (string) ($ext->stage ?? '') : ($hasInt ? (string) ($int->stage ?? '') : '');
                    $editUrlExt = $hasExt ? route('nonconformance.ncar.edit', (int) ($ext->id ?? 0)) : '';
                    $editUrlInt = $hasInt ? route('nonconformance.ncar.edit', (int) ($int->id ?? 0)) : '';
                    $hasNcr = $hasNcrCols && !empty($r->ncr_number);
                    $title = !$hasAny
                        ? 'Create NCAR'
                        : ($hasBoth
                            ? ('External NCAR: ' . $ncarNoExt . ' | Internal NCAR: ' . $ncarNoInt)
                            : ($typeLabel . ' NCAR: ' . $ncarNo));

                    // Si la DB aún no tiene columnas NCR, dejamos el modal en modo "solo vista" (sin URL para guardar).
                    $postUrl = $hasNcrCols ? route('schedule.finished.ncr', $r->id) : '';

                    $qtyVal = '';
                    if ($hasQtyCol) {
                        $childSum = (int) ($r->qty_children_sum ?? 0);
                        $parentQty = is_numeric($r->qty ?? null) ? (int) $r->qty : 0;
                        $hasAnyQty = ($r->qty !== null) || ($childSum > 0);
                        $qtyVal = $hasAnyQty ? ($parentQty + $childSum) : '';
                    }

                    $btnOther = ' <button type="button" class="btn btn-sm ' . e($btnClass) . ' ' . e($btnToneClass) . ' erp-table-btn ml-1 btn-ncr ' . ($hasAny ? 'is-active' : '') . '"
                     title="' . e($title) . '"
                     data-id="' . e($r->id) . '"
                     data-url="' . e($postUrl) . '"
                     data-work-id="' . e($r->work_id) . '"
                     data-operation="' . e($r->operation ?? '') . '"
                     data-co="' . e($r->co ?? '') . '"
                     data-cust-po="' . e($r->cust_po ?? '') . '"
                     data-pn="' . e($r->PN ?? '') . '"
                     data-part-description="' . e($r->Part_description ?? '') . '"
                     data-customer="' . e($r->costumer ?? '') . '"
                     data-qty="' . e($qtyVal) . '"
                     data-wo-qty="' . e($sum) . '"
                     data-ncr-reviewer="' . e($r->ncr_reviewer ?? '') . '"
                     data-ncr-number="' . e($ncarNo) . '"
                     data-ncr-notes="' . e($ncarNotes) . '"
                     data-ncar-type="' . e($ncarType) . '"
                     data-ncar-stage="' . e($ncarStage) . '"
                     data-ncar-edit-url-external="' . e($editUrlExt) . '"
                     data-ncar-edit-url-internal="' . e($editUrlInt) . '">
                     <i class="fas fa-exclamation-triangle"></i>
                 </button>';
                }

                $row = [
                    'id'             => (int) $r->id,
                    'part'           => $part,
                    'work_id'        => trim((string) $r->work_id),
                    'has_fai_pending' => !empty($faiPendingMap[(int)($r->id ?? 0)]),
                    'fai_ops'        => '',
                    'actions'        => '<div class="btn-group btn-group-sm">' . $btn . $btnOther . '</div>',
                    'ops'            => (int) ($r->operation ?? 0),
                    'wo_qty'         => $sum,
                    'sampling'       => (int) ($r->sampling ?? 0),
                    'sampling_check' => (string) ($r->sampling_check ?? 'Normal'),
                    'due_date'       => $dueFormatted,
                    'due_date_sort'  => $dueSort,
                ];

                if ($bucket === 'process') {
                    $ops      = (int) ($r->operation ?? 0);
                    $sampling = (int) ($r->sampling  ?? 0);

                    // 👉 Caso especial: SIN operaciones y SIN sampling → SOLO leyenda "Done"
                    if ($ops === 0 && $sampling === 0) {
                        $pct = 100;

                        $row['fai_ops'] = '<span class="text-muted">N/A</span>';
                        $row['progress'] = '<span class="badge badge-info">Done</span>';
                        $row['progress_pct'] = $pct;

                        // Guardar en BD si cambió el porcentaje
                        if ((int)($r->inspection_progress ?? 0) !== $pct) {
                            $updates[] = [
                                'id' => (int) $r->id,
                                'inspection_progress' => $pct,
                            ];
                        }

                        // No calculamos nada más para este registro
                        return $row;
                    }

                    // 👉 Resto de casos: cálculo normal de progreso con barra
                    $ipiReq   = max(0, $sampling - 1);
                    $perOpReq = 1 + $ipiReq;
                    $totalReq = $ops * $perOpReq;
                    $done     = 0;

                    if ($ops > 0) {
                        $orderPass = $passAggMap[(int) ($r->id ?? 0)] ?? [];

                        for ($i = 1; $i <= $ops; $i++) {
                            $key = $opKey($i);
                            $faiQty = (int) ($orderPass[$key]['FAI'] ?? 0);
                            $ipiQty = (int) ($orderPass[$key]['IPI'] ?? 0);
                            $done += min($faiQty, 1) + min($ipiQty, $ipiReq);
                        }

                        $faiOpsNodes = [];
                        $currentOp = 0;
                        for ($i = 1; $i <= $ops; $i++) {
                            $key = $opKey($i);
                            $label = $opLabel($i);
                            $meta = $faiOpsMetaMap[(int) ($r->id ?? 0)][$key] ?? null;
                            $state = (string) ($meta['status'] ?? 'pending');
                            $passCnt = (int) ($meta['pass_cnt'] ?? 0);
                            $failCnt = (int) ($meta['fail_cnt'] ?? 0);
                            if ($state !== 'ok' && $currentOp === 0) {
                                $currentOp = $i;
                            }

                            $nodeClass = $state === 'ok' ? 'is-ok' : ($state === 'fail' ? 'is-fail' : 'is-pending');
                            $stateText = $state === 'ok' ? 'PASS' : ($state === 'fail' ? 'NO PASS' : 'PENDING');
                            $icon = $state === 'ok' ? '&#10003;' : ($state === 'fail' ? '&#10007;' : '&#9716;');
                            $isCurrent = ($currentOp > 0 && $currentOp === $i) ? ' is-current' : '';
                            $tip = $label . ' | ' . $stateText . ' | Pass: ' . $passCnt . ' | No Pass: ' . $failCnt;
                            $faiOpsNodes[] = '<span class="fai-op-node ' . $nodeClass . $isCurrent . '" title="' . e($tip) . '">'
                                . '<span class="fai-op-dot">' . $icon . '</span>'
                                . '<span class="fai-op-label">' . e($label) . '</span>'
                                . '</span>';
                        }
                        $row['fai_ops'] = '<div class="fai-op-timeline">' . implode('', $faiOpsNodes) . '</div>';
                    } else {
                        $row['fai_ops'] = '<span class="text-muted">N/A</span>';
                    }

                    $pct = $totalReq > 0 ? (int) round(($done / $totalReq) * 100) : 0;

                    $row['progress'] = '<div class="progress" data-order-id="' . e($r->id) . '" style="height:18px;">
                  <div class="progress-bar bg-secondary" role="progressbar"
                       style="width:0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>';
                    $row['progress_pct'] = $pct;

                    // guardar solo si cambió
                    if ((int) ($r->inspection_progress ?? 0) !== $pct) {
                        $updates[] = [
                            'id' => (int)$r->id,
                            'inspection_progress' => $pct,
                        ];
                    }
                }

                return $row;
            });

            // --- GUARDAR EN LOTE con UPDATE ... CASE (sin upsert) ---
            if (!empty($updates)) {
                $ids = array_column($updates, 'id');
                $caseSql = 'CASE id ';
                foreach ($updates as $u) {
                    $caseSql .= 'WHEN ' . (int)$u['id'] . ' THEN ' . (int)$u['inspection_progress'] . ' ';
                }
                $caseSql .= 'END';

                DB::table('orders_schedule')
                    ->whereIn('id', $ids)
                    ->update([
                        'inspection_progress' => DB::raw($caseSql),
                    ]);
            }

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data->values()->all(),
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\Throwable $e) {
            Log::error('partsrevisionData error', [
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'bucket' => $request->query('bucket'),
                'draw' => (int) $request->input('draw', 0),
                'start' => (int) $request->input('start', 0),
                'length' => (int) $request->input('length', 0),
                'search' => (string) data_get($request->all(), 'search.value', ''),
                'order' => $request->input('order', []),
                'user_id' => optional(auth()->user())->id,
            ]);
            $draw = (int) $request->input('draw', 0);
            return response()->json([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Server error',
            ], 500);
        }
    }



    public function updateOperation(Request $request, $id)
    {
        $request->validate([
            'operation'      => 'sometimes|integer|min:0',
            'sampling'       => 'sometimes|integer|min:0',
            'sampling_check' => 'sometimes|string|max:100',
        ]);

        if (!$request->hasAny(['operation', 'sampling', 'sampling_check'])) {
            return response()->json(['success' => false, 'message' => 'No fields to update.'], 422);
        }

        $order = OrderSchedule::findOrFail($id);

        // Guardar sampling_check si viene
        if ($request->has('sampling_check')) { // usa has() para permitir string vacío si algún día lo necesitas
            $order->sampling_check = $request->input('sampling_check');
        }

        // MUY IMPORTANTE: asignar operation y sampling aunque sean 0
        if ($request->has('operation')) {
            $order->operation = (int) $request->input('operation', 0);
        }
        if ($request->has('sampling')) {
            $order->sampling = (int) $request->input('sampling', 0);
        }

        // Recalcular totales si cambió operation o sampling (has() permite 0)
        if ($request->has('operation') || $request->has('sampling')) {
            $op  = (int) ($order->operation ?? 0);
            $smp = (int) ($order->sampling ?? 0);

            $order->total_fai = $op;          // 1 por operación
            $order->total_ipi = $op * $smp - $order->total_fai;   // operación * muestreo
        }

        $order->save();

        return response()->json([
            'success'        => true,
            'operation'      => (int) $order->operation,
            'sampling'       => (int) $order->sampling,
            'sampling_check' => (string) ($order->sampling_check ?? ''),
            'total_fai'      => (int) $order->total_fai,
            'total_ipi'      => (int) $order->total_ipi,
        ]);
    }

    public function storeSingle(Request $request)
    {
        //Log::info('storeSingle called', $request->all());
        $validated = $request->validate([
            'order_schedule_id' => 'required|exists:orders_schedule,id',
            'date' => 'required|date',
            'insp_type' => 'required|in:FAI,IPI',
            'operation' => 'required|string',
            'operator' => 'nullable|string',
            'results' => 'required|in:pass,no pass',
            'sb_is' => 'nullable|string',
            'observation' => 'nullable|string',
            'station' => 'nullable|string',
            'method' => 'required|in:Manual,Vmm/Manual,Visual,Vmm,Keyence,Keyence/Manual',
            'inspector' => 'required|string',
            'qty_pcs'   => 'nullable|integer|min:1',
            'qty_process' => 'nullable|integer|min:0',
        ]);

        // Default: si no envían qty_process, guardar 1 (solo para FAI)
        if (($validated['insp_type'] ?? null) === 'FAI' && (!array_key_exists('qty_process', $validated) || $validated['qty_process'] === null)) {
            $validated['qty_process'] = 1;
        }

        // Permite fecha sola o fecha+hora (datetime-local) y la guarda como datetime completo.
        $validated['date'] = \Carbon\Carbon::parse($validated['date'])->format('Y-m-d H:i:s');

        // 🔍 Obtener la ubicación del order_schedule
        $order = \App\Models\OrderSchedule::find($validated['order_schedule_id']);
        if ($order) {
            $validated['loc_inspection'] = $order->location; // 👈 Aquí copiamos location
        }

        if ($request->has('id')) {
            $row = \App\Models\QaFaiSummary::find($request->id);
            if (!$row) {
                return response()->json(['error' => 'Registro no encontrado'], 404);
            }
            $row->update($validated);
        } else {
            $row = \App\Models\QaFaiSummary::create($validated);
        }
        return response()->json(['success' => true, 'id' => $row->id]);
    }


    /*========== ordenar los FAI summary registrados==========*/
    public function getByOrder($orderScheduleId)
    {
        $rows = \App\Models\QaFaiSummary::where('order_schedule_id', $orderScheduleId)
            ->orderBy('date', 'desc') // más recientes primero 
            ->orderBy('id', 'desc') // desempate estable 
            ->get();
        return response()->json($rows);
    }

    public function get(Request $request)
    {
        $lotSize = max(1, (int) $request->query('lot_size', 0));
        $type    = strtolower(trim((string) $request->query('sampling_type', 'normal')));

        // normaliza alias
        $type = match (true) {
            in_array($type, ['t', 'tight', 'tightened'], true) => 'tightened',
            in_array($type, ['r', 'reduced'], true)          => 'reduced',
            default                                         => 'normal',
        };

        $qtyField = match ($type) {
            'tightened' => 'tightened_qty',
            'reduced'   => Schema::hasColumn('qa_sampling_plans', 'reduced_qty') ? 'reduced_qty' : 'normal_qty',
            default     => 'normal_qty',
        };

        $plan = QaSamplingPlan::query()
            ->where('min_qty', '<=', $lotSize)
            ->where(fn($q) => $q->where('max_qty', '>=', $lotSize)->orWhereNull('max_qty'))
            ->first()
            ?? QaSamplingPlan::query()->orderBy('min_qty', 'asc')->first();

        if (!$plan) {
            // Fallback: inspecciona todo el lote (ajústalo a tu política)
            return response()->json([
                'ok'            => true,
                'lot_size'      => $lotSize,
                'sampling_type' => $type,
                'sample_qty'    => $lotSize,
                'sample_size'   => $lotSize,
                'surface_qty'   => 0,
                'plan_id'       => null,
                'fallback'      => 'no-plan-row',
            ], 200);
        }

        $calc = function ($value, $isPercent) use ($lotSize) {
            if ($value === null) return 1;
            $n = $isPercent ? (int) ceil($lotSize * ((float)$value / 100)) : (int) $value;
            return max(1, min($n, $lotSize)); // clamp 1..lot
        };

        $baseQtyField = Schema::hasColumn($plan->getTable(), $qtyField) ? $qtyField : 'normal_qty';

        $sampleQty  = $calc($plan->{$baseQtyField}, (bool) $plan->is_percent);
        $surfaceQty = $calc($plan->surface_qty,     (bool) $plan->is_percent);

        return response()->json([
            'ok'            => true,
            'lot_size'      => $lotSize,
            'sampling_type' => $type,
            'sample_qty'    => $sampleQty,
            'sample_size'   => $sampleQty,   // alias
            'surface_qty'   => $surfaceQty,
            'plan_id'       => $plan->id,
            'fallback'      => null,
        ], 200);
    }


    public function byOrderStation($orderScheduleId)
    {
        $order = OrderSchedule::select('id', 'location')->findOrFail($orderScheduleId);
        $loc = strtolower($order->location ?? '');

        $rows = DB::table('gen_stations as s')
            ->join('gen_location as l', 'l.id', '=', 's.location_id')
            ->select('s.id', 's.station', 'l.location as location')
            ->whereRaw('LOWER(l.location) = ?', [$loc])
            ->orderBy('s.station')
            ->get();


        return response()->json($rows);
    }

    public function byOrderOperator($orderScheduleId)
    {
        $order = OrderSchedule::select('id', 'location')->findOrFail($orderScheduleId);
        $loc = strtolower($order->location ?? '');

        // usando query builder por si no tienes modelo:
        $rows = DB::table('gen_operators as o')
            ->join('gen_location as l', 'l.id', '=', 'o.location_id')
            ->select('o.id', 'o.operator', 'o.ope_position', 'l.location as location')
            ->whereRaw('LOWER(l.location) = ?', [strtolower($loc)])
            ->orderBy('o.operator')
            ->get();

        return response()->json($rows);
    }

    /**Inspection COMPLETED */
    public function updateStatusInspection(Request $request, $id)
    {
        $request->validate([
            'status_inspection' => 'required|in:pending,in_progress,completed',
            'inspection_note'   => 'nullable|string|max:500',
        ]);

        $order = OrderSchedule::findOrFail($id);

        $prev = strtolower((string) $order->status_inspection);
        $new  = strtolower($request->status_inspection);

        // Early return (opcional): si no cambia nada y no hay nota nueva
        if ($prev === $new && !$request->filled('inspection_note')) {
            return response()->json([
                'success'           => true,
                'status_inspection' => $order->status_inspection,
                'inspection_endate' => $order->inspection_endate,
                'completed_by'      => $order->completed_by,
            ]);
        }

        if ($request->filled('inspection_note')) {
            $order->inspection_note = $request->inspection_note;
        }

        $order->status_inspection = $new;

        // Transición a COMPLETED → sellar una vez
        if ($new === 'completed' && $prev !== 'completed') {
            $order->inspection_endate = $order->inspection_endate ?? now();
            $order->completed_by      = $order->completed_by ?? Auth::id();
            // Si se marca como Completed, el progreso debe ser 100% para reflejar el estado real en Schedule.
            $order->inspection_progress = 100;
        }

        // Reversión desde COMPLETED → limpiar (tú lo quieres así)
        if (in_array($new, ['pending', 'in_progress'], true) && $prev === 'completed') {
            $order->inspection_endate = null;
            $order->completed_by      = null;
        }

        $order->save();

        return response()->json([
            'success'           => true,
            'status_inspection' => $order->status_inspection,
            'inspection_endate' => $order->inspection_endate,
            'completed_by'      => $order->completed_by,
        ]);
    }

    public function destroy($id)
    {
        $row = QaFaiSummary::find($id);
        if (!$row) {
            return response()->json(['error' => 'Fila no encontrada'], 404);
        }
        $row->delete();
        return response()->json(['success' => true]);
    }


    /**Validar si la operacion ya esta validado en la base de datos */
    public function validateOps(Request $request, $orderId)
    {
        $ops = $request->input('ops');
        $order = OrderSchedule::findOrFail($orderId);

        return response()->json([
            'saved' => !empty($order->operation), // true si ya está guardado en BD
        ]);
    }


    /**
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++START+++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * ===================================================================================================================
     * 2. TAB "FAI/IPI SUMMARY" ------------Summary---------------
     * ===================================================================================================================
     */

    public function summary(Request $request)
    {
        $tbl   = (new \App\Models\QaFaiSummary)->getTable(); // 'qa_faisummary'
        $year  = $request->integer('year');
        $month = $request->integer('month');
        $day   = $request->input('day');

        // === Query base ===
        $q = \App\Models\QaFaiSummary::query()
            ->with(['orderSchedule:id,work_id,location,PN']);

        // 📅 Filtro de fechas (prioridad: día > año+mes > año > mes > mes actual)
        if ($day) {
            $q->whereDate('date', \Carbon\Carbon::parse($day)->toDateString());
        } elseif ($year && $month) {
            $q->whereYear('date', $year)->whereMonth('date', $month);
        } elseif ($year) {
            $q->whereYear('date', $year);
        } elseif ($month) {
            $q->whereYear('date', now()->year)->whereMonth('date', $month);
        } else {
            // Por defecto: mes actual
            $q->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]);
            $year  = now()->year;
            $month = now()->month;
        }

        // === Ordenar últimos registrados primero
        $inspections = $q->orderByDesc("$tbl.date")
            ->orderByDesc("$tbl.id")
            ->get();

        // === Stats para el dashboard ===
        $statsQuery = \App\Models\QaFaiSummary::query();

        if ($day) {
            $statsQuery->whereDate('date', \Carbon\Carbon::parse($day)->toDateString());
        } elseif ($year && $month) {
            $statsQuery->whereYear('date', $year)->whereMonth('date', $month);
        } elseif ($year) {
            $statsQuery->whereYear('date', $year);
        } elseif ($month) {
            $statsQuery->whereYear('date', now()->year)->whereMonth('date', $month);
        } else {
            $statsQuery->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]);
            $year  = now()->year;
            $month = now()->month;
        }

        $monthTotal = (clone $statsQuery)->count();
        $monthPass  = (clone $statsQuery)->whereRaw('LOWER(TRIM(results)) = ?', ['pass'])->count();
        $monthFail  = (clone $statsQuery)->whereRaw('LOWER(TRIM(results)) IN ("fail","no pass","nopass","no_pass")')->count();
        $passRate   = $monthTotal ? number_format(($monthPass * 100) / $monthTotal, 2, '.', '') : '0.00';

        $monthStats = [
            'year'  => $year,
            'month' => $month,
            'total' => $monthTotal,
            'pass'  => $monthPass,
            'fail'  => $monthFail,
            'rate'  => $passRate,
        ];

        return view('qa.faisummary.faisummary_summary', compact(
            'inspections',
            'year',
            'month',
            'day',
            'monthStats'
        ));
    }

    public function general(Request $request)
    {
        $data = $this->getFaiSummaryData($request);

        // ALERTAS: evaluar el �ltimo FAI por operaci�n. Si alguna operaci�n queda en No Pass,
        // la orden sigue alertando aunque otra operaci�n m�s reciente tenga Pass.
        $failedOrders = QaFaiSummary::with('orderSchedule')
            ->whereRaw("UPPER(TRIM(insp_type)) = 'FAI'")
            ->whereHas('orderSchedule', function ($q) {
                $q->whereRaw("LOWER(TRIM(COALESCE(status_inspection,''))) <> 'completed'");
            })
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get()
            ->groupBy('order_schedule_id')
            ->map(function ($orderGroup) {
                $pendingByOp = $orderGroup
                    ->groupBy(function ($row) {
                        return strtoupper(trim((string) ($row->operation ?? '')));
                    })
                    ->map(function ($opGroup) {
                        return $opGroup->first();
                    })
                    ->filter(function ($latestByOp) {
                        $result = strtolower(trim((string) ($latestByOp->results ?? '')));
                        return in_array($result, ['fail', 'no pass', 'nopass', 'no_pass'], true);
                    })
                    ->sort(function ($a, $b) {
                        $ad = strtotime((string) ($a->date ?? '')) ?: 0;
                        $bd = strtotime((string) ($b->date ?? '')) ?: 0;
                        if ($ad === $bd) {
                            return ((int) ($b->id ?? 0)) <=> ((int) ($a->id ?? 0));
                        }
                        return $bd <=> $ad;
                    })
                    ->values();

                return $pendingByOp->first();
            })
            ->filter()
            ->values();

        $data['failedOrders'] = $failedOrders;

        return view('qa.faisummary.faisummary_summary', $data);
    }

    public function generalData(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 14);
        if ($length < 1) $length = 14;
        if ($length > 200) $length = 200;

        $globalSearch = trim((string) $request->input('search.value', ''));
        $focusOrderId = (int) $request->input('focus_order_id', 0);
        $focusWorkId = trim((string) $request->input('focus_work_id', ''));
        $focusPn = trim((string) $request->input('focus_pn', ''));
        $focusMonths = (int) $request->input('focus_months', 12);
        $focusMonths = max(1, min($focusMonths, 60));

        $buildBase = function () {
            return DB::table('qa_faisummary as qfs')
                ->leftJoin('orders_schedule as os', 'os.id', '=', 'qfs.order_schedule_id');
        };

        $applyBaseFilters = function ($q, bool $ignoreDateForSearch = false) use (
            $request,
            $focusOrderId,
            $focusWorkId,
            $focusPn,
            $focusMonths
        ) {
            if ($request->filled('operator')) {
                $q->where('qfs.operator', $request->input('operator'));
            }
            if ($request->filled('inspector')) {
                $q->where('qfs.inspector', $request->input('inspector'));
            }
            if ($request->filled('location')) {
                $q->where('qfs.loc_inspection', $request->input('location'));
            }

            if ($focusOrderId > 0) {
                $focusOrder = OrderSchedule::query()->select('id', 'work_id', 'PN')->find($focusOrderId);
                if ($focusOrder) {
                    $focusWork = trim((string) $focusOrder->work_id);
                    $focusPart = trim((string) $focusOrder->PN);
                    $q->where(function ($w) use ($focusOrderId, $focusWork, $focusPart) {
                        $w->where('qfs.order_schedule_id', $focusOrderId);
                        if ($focusWork !== '' || $focusPart !== '') {
                            $w->orWhere(function ($x) use ($focusWork, $focusPart) {
                                if ($focusWork !== '') $x->where('os.work_id', $focusWork);
                                if ($focusPart !== '') $x->where('os.PN', $focusPart);
                            });
                        }
                    });
                } else {
                    $q->where('qfs.order_schedule_id', $focusOrderId);
                }
            } elseif ($focusWorkId !== '' || $focusPn !== '') {
                $q->where(function ($w) use ($focusWorkId, $focusPn) {
                    if ($focusWorkId !== '') $w->where('os.work_id', $focusWorkId);
                    if ($focusPn !== '') $w->where('os.PN', $focusPn);
                });
            }

            if ($focusOrderId > 0 || $focusWorkId !== '' || $focusPn !== '') {
                $q->where('qfs.date', '>=', now()->subMonthsNoOverflow($focusMonths)->startOfDay());
            }

            if ($ignoreDateForSearch) {
                return;
            }

            if ($request->filled('day')) {
                $q->whereDate('qfs.date', $request->input('day'));
                return;
            }

            if ($request->filled('year')) {
                $q->whereYear('qfs.date', (int) $request->input('year'));
            }
            if ($request->filled('month')) {
                $q->whereMonth('qfs.date', (int) $request->input('month'));
            }

            if (!$request->filled('year') && !$request->filled('month') && $focusOrderId <= 0 && $focusWorkId === '' && $focusPn === '') {
                $q->whereBetween('qfs.date', [now()->startOfMonth(), now()->endOfMonth()]);
            }
        };

        $recordsTotalQuery = $buildBase();
        $applyBaseFilters($recordsTotalQuery, false);
        $recordsTotal = (int) $recordsTotalQuery->count('qfs.id');

        $filteredQuery = $buildBase();
        $applyBaseFilters($filteredQuery, $globalSearch !== '');

        if ($globalSearch !== '') {
            $like = '%' . $globalSearch . '%';
            $filteredQuery->where(function ($w) use ($like) {
                $w->where('qfs.operator', 'like', $like)
                    ->orWhere('qfs.inspector', 'like', $like)
                    ->orWhere('qfs.loc_inspection', 'like', $like)
                    ->orWhere('qfs.operation', 'like', $like)
                    ->orWhere('qfs.insp_type', 'like', $like)
                    ->orWhere('qfs.results', 'like', $like)
                    ->orWhere('qfs.station', 'like', $like)
                    ->orWhere('qfs.method', 'like', $like)
                    ->orWhere('qfs.sb_is', 'like', $like)
                    ->orWhere('qfs.observation', 'like', $like)
                    ->orWhere('os.work_id', 'like', $like)
                    ->orWhere('os.PN', 'like', $like);
            });
        }

        $columnFilters = (array) $request->input('columns', []);
        $colMap = [
            0 => 'qfs.date',
            1 => 'os.PN',
            2 => 'os.work_id',
            3 => 'qfs.insp_type',
            4 => 'qfs.operation',
            5 => 'qfs.operator',
            6 => 'qfs.results',
            9 => 'qfs.station',
            10 => 'qfs.method',
            11 => 'qfs.qty_pcs',
            12 => 'qfs.inspector',
            13 => 'qfs.loc_inspection',
        ];
        $applyColumnFilters = function ($q, ?int $skipIdx = null) use ($columnFilters, $colMap) {
            foreach ($columnFilters as $idx => $meta) {
                $idx = (int) $idx;
                if ($skipIdx !== null && $idx === $skipIdx) continue;

                $val = trim((string) data_get($meta, 'search.value', ''));
                if ($val === '' || !isset($colMap[$idx])) continue;

                if ($idx === 0) {
                    $q->whereRaw("DATE_FORMAT(qfs.date, '%b-%d-%y') = ?", [$val]);
                    continue;
                }
                if ($idx === 6) {
                    $result = strtolower(trim(strip_tags($val)));
                    if ($result === '') continue;

                    $passVals = ['pass', 'ok', 'p'];
                    $failVals = ['no pass', 'nopass', 'no_pass', 'fail', 'np', 'no', 'f'];
                    if (in_array($result, $passVals, true)) {
                        $q->whereIn(DB::raw("LOWER(TRIM(COALESCE(qfs.results,'')))"), $passVals);
                    } elseif (in_array($result, $failVals, true)) {
                        $q->whereIn(DB::raw("LOWER(TRIM(COALESCE(qfs.results,'')))"), $failVals);
                    } else {
                        $q->whereRaw('LOWER(TRIM(COALESCE(qfs.results, ""))) = ?', [$result]);
                    }
                    continue;
                }
                $q->where($colMap[$idx], $val);
            }
        };
        $applyColumnFilters($filteredQuery);

        $recordsFiltered = (int) (clone $filteredQuery)->count('qfs.id');

        $orderColIdx = (int) data_get($request->input('order', []), '0.column', 0);
        $orderDir = strtolower((string) data_get($request->input('order', []), '0.dir', 'desc'));
        $orderDir = $orderDir === 'asc' ? 'asc' : 'desc';
        $orderMap = [
            0 => 'qfs.date',
            1 => 'os.PN',
            2 => 'os.work_id',
            3 => 'qfs.insp_type',
            4 => 'qfs.operation',
            5 => 'qfs.operator',
            6 => 'qfs.results',
            7 => 'qfs.sb_is',
            8 => 'qfs.observation',
            9 => 'qfs.station',
            10 => 'qfs.method',
            11 => 'qfs.qty_pcs',
            12 => 'qfs.inspector',
            13 => 'qfs.loc_inspection',
        ];
        $orderCol = $orderMap[$orderColIdx] ?? 'qfs.date';

        $rows = $filteredQuery
            ->select([
                'qfs.id',
                'qfs.date',
                'qfs.insp_type',
                'qfs.operation',
                'qfs.operator',
                'qfs.results',
                'qfs.sb_is',
                'qfs.observation',
                'qfs.station',
                'qfs.method',
                'qfs.qty_pcs',
                'qfs.inspector',
                'qfs.loc_inspection',
                'os.PN as part_number',
                'os.work_id as work_id',
            ])
            ->orderBy($orderCol, $orderDir)
            ->offset($start)
            ->limit($length)
            ->get();

        $data = $rows->map(function ($r) {
            $dt = $r->date ? Carbon::parse($r->date) : null;
            $dateDisplay = $dt ? $dt->format('M-d-y') . ' <span class="badge badge-light">' . $dt->format('H:i') . '</span>' : '';
            $isFAI = strcasecmp(trim((string) $r->insp_type), 'FAI') === 0;
            $isPass = strcasecmp(trim((string) $r->results), 'pass') === 0;

            return [
                'date' => [
                    'display' => $dateDisplay,
                    'sort' => $dt ? $dt->format('Y-m-d H:i:s') : '',
                    'filter' => $dt ? $dt->format('M-d-y H:i') : '',
                ],
                'part_revision' => e((string) ($r->part_number ?? '')),
                'job' => e((string) ($r->work_id ?? '')),
                'type' => '<span class="badge erp-type-badge ' . ($isFAI ? 'erp-type-fai' : 'erp-type-ipi') . '">' . e((string) $r->insp_type) . '</span>',
                'opet' => e((string) ($r->operation ?? '')),
                'operator' => e((string) ($r->operator ?? '')),
                'result' => '<span class="badge erp-result-badge ' . ($isPass ? 'erp-result-pass' : 'erp-result-fail') . '">' . e(ucfirst((string) $r->results)) . '</span>',
                'sb_is' => e((string) ($r->sb_is ?? '')),
                'observation' => e((string) ($r->observation ?? '')),
                'station' => e((string) ($r->station ?? '')),
                'method' => e((string) ($r->method ?? '')),
                'qty_insp' => (string) ($r->qty_pcs ?? ''),
                'inspector' => e((string) ($r->inspector ?? '')),
                'location' => e((string) ($r->loc_inspection ?? '')),
            ];
        })->values();

        // Header filter options must come from the full filtered dataset (not current page).
        $buildOptionsQuery = function (?int $skipIdx = null) use (
            $buildBase,
            $applyBaseFilters,
            $globalSearch,
            $applyColumnFilters
        ) {
            $q = $buildBase();
            $applyBaseFilters($q, $globalSearch !== '');

            if ($globalSearch !== '') {
                $like = '%' . $globalSearch . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('qfs.operator', 'like', $like)
                        ->orWhere('qfs.inspector', 'like', $like)
                        ->orWhere('qfs.loc_inspection', 'like', $like)
                        ->orWhere('qfs.operation', 'like', $like)
                        ->orWhere('qfs.insp_type', 'like', $like)
                        ->orWhere('qfs.results', 'like', $like)
                        ->orWhere('qfs.station', 'like', $like)
                        ->orWhere('qfs.method', 'like', $like)
                        ->orWhere('qfs.sb_is', 'like', $like)
                        ->orWhere('qfs.observation', 'like', $like)
                        ->orWhere('os.work_id', 'like', $like)
                        ->orWhere('os.PN', 'like', $like);
                });
            }

            $applyColumnFilters($q, $skipIdx);
            return $q;
        };

        $toOptionRows = function ($rows) {
            return collect($rows)->map(function ($r) {
                return [
                    'value' => (string) ($r->v ?? ''),
                    'count' => (int) ($r->c ?? 0),
                ];
            })->filter(fn($x) => trim((string) $x['value']) !== '')->values();
        };

        $filterOptions = [
            'date' => $toOptionRows(
                (clone $buildOptionsQuery(0))
                    ->selectRaw("DATE_FORMAT(qfs.date, '%b-%d-%y') as v, COUNT(*) as c")
                    ->whereNotNull('qfs.date')
                    ->groupBy('v')
                    ->orderByRaw('MAX(qfs.date) DESC')
                    ->get()
            ),

            'type' => $toOptionRows(
                (clone $buildOptionsQuery(3))
                    ->selectRaw("TRIM(COALESCE(qfs.insp_type,'')) as v, COUNT(*) as c")
                    ->whereRaw("TRIM(COALESCE(qfs.insp_type,'')) <> ''")
                    ->groupBy('v')
                    ->orderBy('v')
                    ->get()
            ),

            'operation' => $toOptionRows(
                (clone $buildOptionsQuery(4))
                    ->selectRaw("TRIM(COALESCE(qfs.operation,'')) as v, COUNT(*) as c")
                    ->whereRaw("TRIM(COALESCE(qfs.operation,'')) <> ''")
                    ->groupBy('v')
                    ->orderByRaw("CAST(v AS UNSIGNED) ASC, v ASC")
                    ->get()
            ),

            'result' => $toOptionRows(
                (clone $buildOptionsQuery(6))
                    ->selectRaw("
                        CASE
                            WHEN LOWER(TRIM(COALESCE(qfs.results,''))) IN ('pass','ok','p') THEN 'pass'
                            WHEN LOWER(TRIM(COALESCE(qfs.results,''))) IN ('no pass','nopass','no_pass','fail','np','no','f') THEN 'no pass'
                            ELSE LOWER(TRIM(COALESCE(qfs.results,'')))
                        END as v,
                        COUNT(*) as c
                    ")
                    ->whereRaw("TRIM(COALESCE(qfs.results,'')) <> ''")
                    ->groupBy('v')
                    ->orderByRaw("FIELD(v, 'pass', 'no pass'), v")
                    ->get()
            ),

            'station' => $toOptionRows(
                (clone $buildOptionsQuery(9))
                    ->selectRaw("TRIM(COALESCE(qfs.station,'')) as v, COUNT(*) as c")
                    ->whereRaw("TRIM(COALESCE(qfs.station,'')) <> ''")
                    ->groupBy('v')
                    ->orderBy('v')
                    ->get()
            ),

            'operator' => $toOptionRows(
                (clone $buildOptionsQuery(5))
                    ->selectRaw("TRIM(COALESCE(qfs.operator,'')) as v, COUNT(*) as c")
                    ->whereRaw("TRIM(COALESCE(qfs.operator,'')) <> ''")
                    ->groupBy('v')
                    ->orderBy('v')
                    ->get()
            ),

            'method' => $toOptionRows(
                (clone $buildOptionsQuery(10))
                    ->selectRaw("TRIM(COALESCE(qfs.method,'')) as v, COUNT(*) as c")
                    ->whereRaw("TRIM(COALESCE(qfs.method,'')) <> ''")
                    ->groupBy('v')
                    ->orderBy('v')
                    ->get()
            ),

            'qty_insp' => $toOptionRows(
                (clone $buildOptionsQuery(11))
                    ->selectRaw("TRIM(COALESCE(CAST(qfs.qty_pcs AS CHAR),'')) as v, COUNT(*) as c")
                    ->whereRaw("TRIM(COALESCE(CAST(qfs.qty_pcs AS CHAR),'')) <> ''")
                    ->groupBy('v')
                    ->orderByRaw('CAST(v AS UNSIGNED) ASC, v ASC')
                    ->get()
            ),

            'inspector' => $toOptionRows(
                (clone $buildOptionsQuery(12))
                    ->selectRaw("TRIM(COALESCE(qfs.inspector,'')) as v, COUNT(*) as c")
                    ->whereRaw("TRIM(COALESCE(qfs.inspector,'')) <> ''")
                    ->groupBy('v')
                    ->orderBy('v')
                    ->get()
            ),

            'location' => $toOptionRows(
                (clone $buildOptionsQuery(13))
                    ->selectRaw("TRIM(COALESCE(qfs.loc_inspection,'')) as v, COUNT(*) as c")
                    ->whereRaw("TRIM(COALESCE(qfs.loc_inspection,'')) <> ''")
                    ->groupBy('v')
                    ->orderBy('v')
                    ->get()
            ),
        ];

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
            'filterOptions' => $filterOptions,
        ]);
    }



    /**
     * 🔁 Reutiliza esta función para vista, Excel y PDF
     */
    protected function getFaiSummaryData(Request $request): array
    {
        $query = QaFaiSummary::with('orderSchedule');
        $focusOrderId = (int) $request->input('focus_order_id', 0);
        $focusWorkId = trim((string) $request->input('focus_work_id', ''));
        $focusPn = trim((string) $request->input('focus_pn', ''));
        $focusMonths = (int) $request->input('focus_months', 12);
        $focusMonths = max(1, min($focusMonths, 60)); // configurable (1..60)
        $globalSearch = trim((string) $request->input('q', ''));

        // === filtros EXACTOS como los del formulario ===
        if ($request->filled('operator')) {
            $query->where('operator', $request->operator);
        }

        if ($request->filled('inspector')) {
            $query->where('inspector', $request->inspector);
        }

        if ($request->filled('location')) {
            $query->where('loc_inspection', $request->location);
        }

        // Filtro proveniente de chips FAILED FAI ALERTS (historial completo de la orden).
        if ($focusOrderId > 0) {
            $focusOrder = OrderSchedule::query()
                ->select('id', 'work_id', 'PN')
                ->find($focusOrderId);

            if ($focusOrder) {
                $focusWork = trim((string) $focusOrder->work_id);
                $focusPart = trim((string) $focusOrder->PN);

                $query->where(function ($q) use ($focusOrderId, $focusWork, $focusPart) {
                    // Match exacto por id y también por job+part para incluir histórico de meses anteriores.
                    $q->where('order_schedule_id', $focusOrderId)
                        ->orWhereHas('orderSchedule', function ($os) use ($focusWork, $focusPart) {
                            if ($focusWork !== '') {
                                $os->where('work_id', $focusWork);
                            }
                            if ($focusPart !== '') {
                                $os->where('PN', $focusPart);
                            }
                        });
                });
            } else {
                $query->where('order_schedule_id', $focusOrderId);
            }
        } elseif ($focusWorkId !== '' || $focusPn !== '') {
            $query->whereHas('orderSchedule', function ($q) use ($focusWorkId, $focusPn) {
                if ($focusWorkId !== '') $q->where('work_id', $focusWorkId);
                if ($focusPn !== '') $q->where('PN', $focusPn);
            });
        }

        // Cuando el filtro viene de chip, limitar histórico para mantener rendimiento.
        if ($focusOrderId > 0 || $focusWorkId !== '' || $focusPn !== '') {
            $query->where('date', '>=', now()->subMonthsNoOverflow($focusMonths)->startOfDay());
        }

        // Filtros de fecha (year/month/day) según tu lógica actual
        if ($globalSearch !== '') {
            $query->where(function ($q) use ($globalSearch) {
                $like = '%' . $globalSearch . '%';
                $q->where('operator', 'like', $like)
                    ->orWhere('inspector', 'like', $like)
                    ->orWhere('loc_inspection', 'like', $like)
                    ->orWhere('operation', 'like', $like)
                    ->orWhere('insp_type', 'like', $like)
                    ->orWhere('results', 'like', $like)
                    ->orWhere('station', 'like', $like)
                    ->orWhere('method', 'like', $like)
                    ->orWhere('sb_is', 'like', $like)
                    ->orWhere('observation', 'like', $like)
                    ->orWhereHas('orderSchedule', function ($os) use ($like) {
                        $os->where('work_id', 'like', $like)
                            ->orWhere('PN', 'like', $like);
                    });
            });
        } else {
            if ($request->filled('year')) {
                $query->whereYear('date', $request->year);
            }

            if ($request->filled('month')) {
                $query->whereMonth('date', $request->month);
            }

            if ($request->filled('day')) {
                $query->whereDate('date', $request->day);
            } elseif (!$request->filled('year') && !$request->filled('month') && $focusOrderId <= 0 && $focusWorkId === '' && $focusPn === '') {
                // Rendimiento: por defecto limitar al mes actual y evitar cargar todo el historial.
                $query->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]);
            }
        }

        $inspections = $query->orderByDesc('date')->get();
        $faiCount = $inspections->filter(function ($row) {
            return strcasecmp(trim((string) $row->insp_type), 'FAI') === 0;
        })->count();
        $ipiCount = $inspections->filter(function ($row) {
            return strcasecmp(trim((string) $row->insp_type), 'IPI') === 0;
        })->count();

        // === KPIs (respetar filtros de fecha: day / month / year) ===
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);
        $day   = trim((string) $request->input('day', ''));

        $statsQuery = QaFaiSummary::query();
        if ($day !== '') {
            $statsQuery->whereDate('date', Carbon::parse($day)->toDateString());
            $year = (int) Carbon::parse($day)->year;
            $month = (int) Carbon::parse($day)->month;
        } elseif ($request->filled('year') && $request->filled('month')) {
            $statsQuery->whereYear('date', $year)->whereMonth('date', $month);
        } elseif ($request->filled('year')) {
            $statsQuery->whereYear('date', $year);
        } elseif ($request->filled('month')) {
            $statsQuery->whereYear('date', now()->year)->whereMonth('date', $month);
            $year = (int) now()->year;
        } else {
            $statsQuery->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]);
            $year = (int) now()->year;
            $month = (int) now()->month;
        }

        $total = (clone $statsQuery)->count();
        $pass  = (clone $statsQuery)->whereRaw('LOWER(TRIM(results)) = ?', ['pass'])->count();
        $fail  = (clone $statsQuery)->whereRaw('LOWER(TRIM(results)) IN ("fail","no pass","nopass","no_pass")')->count();
        $passFai = (clone $statsQuery)
            ->whereRaw('LOWER(TRIM(results)) = ?', ['pass'])
            ->whereRaw("UPPER(TRIM(COALESCE(insp_type,''))) = 'FAI'")
            ->count();
        $passIpi = (clone $statsQuery)
            ->whereRaw('LOWER(TRIM(results)) = ?', ['pass'])
            ->whereRaw("UPPER(TRIM(COALESCE(insp_type,''))) = 'IPI'")
            ->count();
        $failFai = (clone $statsQuery)
            ->whereRaw('LOWER(TRIM(results)) IN ("fail","no pass","nopass","no_pass")')
            ->whereRaw("UPPER(TRIM(COALESCE(insp_type,''))) = 'FAI'")
            ->count();
        $failIpi = (clone $statsQuery)
            ->whereRaw('LOWER(TRIM(results)) IN ("fail","no pass","nopass","no_pass")')
            ->whereRaw("UPPER(TRIM(COALESCE(insp_type,''))) = 'IPI'")
            ->count();

        $rate = $total > 0 ? number_format(($pass * 100) / $total, 2, '.', '') : '0.00';

        $monthStats = [
            'total' => $total,
            'pass'  => $pass,
            'fail'  => $fail,
            'pass_fai' => $passFai,
            'pass_ipi' => $passIpi,
            'fail_fai' => $failFai,
            'fail_ipi' => $failIpi,
            'rate'  => $rate,
            'fai'   => $faiCount,
            'ipi'   => $ipiCount,
            'month' => $month,
            'year'  => $year,
        ];

        return compact('inspections', 'monthStats');
    }

    // ================== EXCEL ==================
    public function exportFai14(Request $request)
    {
        $data = $this->getFaiSummaryData($request); // lo que ya tienes

        $export = new BladeTableExport(
            view: 'qa.faisummary.faisummary_summaryexcel',
            data: $data,
            title: 'FAI / IPI Summary Report',
            columnFormats: [
                'A' => NumberFormat::FORMAT_DATE_DATETIME,
                'L' => NumberFormat::FORMAT_NUMBER, // Qty Insp.
            ],
            centerCols: ['B', 'C', 'D', 'E', 'F', 'G', 'J', 'K', 'M', 'N'],
            rightCols: ['L'],
            wrapCols: ['H', 'I'], // SB/IS y Observation
        );

        return Excel::download($export, 'FAI_Summary_' . now()->format('Ymd_His') . '.xlsx');
    }

    // ================== PDF ==================
    public function exportPdf(Request $request)
    {
        $data = $this->getFaiSummaryData($request);

        /** @var \Illuminate\Support\Collection $inspections */
        $inspections = $data['inspections'];

        // 📊 RESUMEN basado SOLO en los registros del PDF
        $total = $inspections->count();

        $pass = $inspections->filter(function ($row) {
            return strcasecmp(trim((string)$row->results), 'pass') === 0;
        })->count();

        $fail = $inspections->filter(function ($row) {
            return strcasecmp(trim((string)$row->results), 'no pass') === 0;
        })->count();

        $rate = $total > 0 ? number_format(($pass * 100) / $total, 2, '.', '') : '0.00';

        $pdfStats = [
            'total' => $total,
            'pass'  => $pass,
            'fail'  => $fail,
            'rate'  => $rate,
        ];

        // 👇 estos vienen de los filtros del formulario
        $month = $request->input('month');   // puede ser null
        $year  = $request->input('year');    // puede ser null

        $generatedAt = now();
        $user        = auth()->user();

        $pdf = Pdf::loadView('qa.faisummary.faisummary_summarypdf', [
            'inspections' => $inspections,
            'pdfStats'    => $pdfStats,
            'generatedAt' => $generatedAt,
            'user'        => $user,

            // 👉 los mandamos a la vista para el Period:
            'month'       => $month,
            'year'        => $year,
        ])->setPaper('letter', 'landscape');

        return $pdf->stream('FAI_Summary_' . $generatedAt->format('Ymd_His') . '.pdf');
    }


    /**
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++START+++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * ===================================================================================================================
     * 3. TAB "FAI/IPI SUMMARY" ------------Completed FAI Summary----------------
     * ===================================================================================================================
     */


    public function faicompleted(Request $request)
    {
        $year  = $request->integer('year');   // null/0 si no viene
        $month = $request->integer('month');  // null/0 si no viene
        $day   = $request->input('day');      // string o null

        $kpiRows = $this->baseCompletedQuery($request, true, true)->get();
        $kpis = $this->computeCompletedKpis($kpiRows);
        $locations = OrderSchedule::query()
            ->whereNull('parent_id')
            ->where('status_inspection', 'completed')
            ->whereNotNull('location')
            ->distinct()
            ->orderBy('location')
            ->pluck('location')
            ->filter()
            ->values();

        return view('qa.faisummary.faisummary_completed', compact(
            'kpis',
            'locations',
            'year',
            'month',
            'day'
        ));
    }

    public function faicompletedData(Request $request)
    {
        try {
            $draw = (int) $request->input('draw', 1);
            $start = max(0, (int) $request->input('start', 0));
            $length = (int) $request->input('length', 10);
            $length = $length > 0 ? min($length, 100) : 10;

            $recordsTotal = (clone $this->baseCompletedQuery($request, false, false))->count();
            $filteredBase = $this->baseCompletedQuery($request, true, false);
            $kpis = $this->computeCompletedKpis((clone $filteredBase)->get());
            $query = $this->baseCompletedQuery($request, true, true);
            $recordsFiltered = (clone $query)->count();
            $rows = $query->skip($start)->take($length)->get();

            $data = $rows->map(function ($o) {
                $sampling = (int) ($o->sampling ?? 0);
                $ops = (int) ($o->operation ?? 0);
                $summaryTotals = app(InspectionSummary::class)->summarize($o, $ops, $sampling)['totals'] ?? [];
                $faiReq = (int) ($summaryTotals['faiReq'] ?? (int) ($o->total_fai ?? 0));
                $ipiReq = (int) ($summaryTotals['ipiReq'] ?? (int) ($o->total_ipi ?? 0));
                $faiPass = (int) ($summaryTotals['faiPass'] ?? 0);
                $ipiPass = (int) ($summaryTotals['ipiPass'] ?? 0);
                $isNoInspection = $sampling === 0 || $ops === 0 || ($faiReq === 0 && $ipiReq === 0);
                $isCompleted = !$isNoInspection && ($faiReq === 0 || $faiPass >= $faiReq) && ($ipiReq === 0 || $ipiPass >= $ipiReq);
                $totalReq = $faiReq + $ipiReq;
                $overall = $isNoInspection ? 0 : ($totalReq > 0 ? (int) round((($faiPass + $ipiPass) / $totalReq) * 100) : 100);
                $barClass = $isCompleted ? 'bg-success' : ($overall >= 75 ? 'bg-info' : ($overall >= 50 ? 'bg-warning' : 'bg-danger'));

                $date = optional($o->inspection_endate);
                $dateHtml = $date
                    ? '<div class="fai-date-cell"><span class="fai-date-main">' . e($date->format('M-d-y')) . '</span><span class="fai-date-time">' . e($date->format('H:i')) . '</span></div>'
                    : '';

                $inspectionNote = trim($this->sanitizeUtf8($o->inspection_note ?? ''));

                if ($isNoInspection) {
                    $progressHtml = '<span class="badge" style="background:#6c757d;color:white;padding:4px 10px;border-radius:6px;font-weight:600;display:inline-flex;align-items:center;gap:6px;"><i class="fas fa-exclamation"></i>No Inspection</span>';
                    if ($inspectionNote !== '') {
                        $completedByName = trim($this->sanitizeUtf8(optional($o->completedByUser)->name ?? ''));
                        $completedOn = $date ? $date->format('m/d/Y') : '';
                        $completedInfo = trim($completedByName . ($completedOn !== '' ? ' ( ' . $completedOn . ' )' : ''));
                        $progressHtml .= ' <button type="button" class="fai-note-chip btn-open-note ml-1" data-note="' . e($inspectionNote) . '" data-completed-by="' . e($completedInfo) . '" title="Inspection note">Note</button>';
                    }
                } else {
                    $progressHtml = '<div class="progress" style="height:18px;" title="FAI ' . e($faiPass . '/' . $faiReq) . ' | IPI ' . e($ipiPass . '/' . $ipiReq) . '"><div class="progress-bar ' . e($barClass) . '" style="width: ' . e($overall) . '%;" aria-valuenow="' . e($overall) . '" aria-valuemin="0" aria-valuemax="100">' . e($overall) . '%</div></div>';
                    $progressHtml .= $isCompleted
                        ? '<span class="badge badge-success mt-1"><i class="fas fa-check"></i> Done</span>'
                        : '<small class="text-muted d-block mt-1">FAI ' . e($faiPass . '/' . $faiReq) . ' | IPI ' . e($ipiPass . '/' . $ipiReq) . '</small>';
                }

                $pdfUrl = route('qa.faisummary.pdf', $o->id);
                $eventsUrl = route('faisummary.completed.events', $o->id);
                $actionHtml = '<div class="btn-group btn-group-sm" role="group">'
                    . '<a href="#" class="btn btn-danger btn-open-pdf" data-pdf-url="' . e($pdfUrl) . '"><i class="fas fa-print"></i></a>'
                    . '<a href="' . e($pdfUrl) . '?download=1" class="btn btn-info"><i class="fas fa-download"></i></a>'
                    . '<a href="#" class="btn btn-warning btn-edit-pdf" data-id="' . e($o->id) . '"><i class="fas fa-reply"></i></a>'
                    . '<a href="' . e($eventsUrl) . '" class="btn btn-primary btn-edit-row" data-id="' . e($o->id) . '" title="Edit"><i class="fas fa-edit"></i></a>'
                    . '</div>';

                return [
                    'row_id' => 'row-' . $o->id,
                    'id' => (int) $o->id,
                    'date' => $dateHtml,
                    'location' => e(ucfirst($this->sanitizeUtf8($o->location))),
                    'work_id' => e($this->sanitizeUtf8($o->work_id)),
                    'pn' => e($this->sanitizeUtf8($o->PN)),
                    'co' => e($this->sanitizeUtf8($o->co)),
                    'cust_po' => e($this->sanitizeUtf8($o->cust_po)),
                    'description' => e($this->sanitizeUtf8($o->Part_description)),
                    'sampling_check' => e(ucfirst($this->sanitizeUtf8($o->sampling_check))),
                    'group_wo_qty' => (int) ($o->group_wo_qty ?? 0),
                    'sampling' => (int) ($o->sampling ?? 0),
                    'operation' => (int) ($o->operation ?? 0),
                    'total_fai' => (int) ($o->total_fai ?? 0),
                    'total_ipi' => (int) ($o->total_ipi ?? 0),
                    'progress' => $progressHtml,
                    'action' => $actionHtml,
                    'progress_pct' => $overall,
                ];
            })->values()->all();

            return response()->json(['draw' => $draw, 'recordsTotal' => $recordsTotal, 'recordsFiltered' => $recordsFiltered, 'data' => $data, 'meta' => ['kpis' => $kpis]], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\Throwable $e) {
            Log::error('faicompletedData error', ['msg' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'draw' => (int) $request->input('draw', 0), 'start' => (int) $request->input('start', 0), 'length' => (int) $request->input('length', 0), 'search' => (string) data_get($request->all(), 'search.value', ''), 'user_id' => optional(auth()->user())->id]);
            return response()->json(['draw' => (int) $request->input('draw', 0), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Server error'], 500);
        }
    }

    protected function sanitizeUtf8($value): string
    {
        if ($value === null) {
            return '';
        }

        $text = (string) $value;
        if ($text === '') {
            return '';
        }

        if (!preg_match('//u', $text)) {
            $converted = @mb_convert_encoding($text, 'UTF-8', 'UTF-8, Windows-1252, ISO-8859-1');
            if (is_string($converted) && $converted !== '') {
                $text = $converted;
            }
        }

        $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
        return is_string($clean) ? $clean : $text;
    }
    public function updateStatus(Request $request, OrderSchedule $order)
    {
        $request->validate([
            'status_inspection' => 'required|string'
        ]);

        $order->status_inspection = $request->status_inspection;
        $order->save();

        return response()->json(['success' => true]);
    }



    public function pdf(OrderSchedule $order)
    {
        // 0) Normaliza: si llaman con un hijo, usa el padre del grupo
        $parentId = $order->parent_id ?: $order->id;

        $generatedAt = now('America/Los_Angeles');
        $user = auth()->user();

        // === Total qty padre + hijos ===
        $totalQty = \App\Models\OrderSchedule::query()
            ->where(function ($q) use ($parentId, $order) {
                $q->where('id', $parentId)      // el padre
                    ->orWhere('parent_id', $parentId); // todos los hijos
            })
            ->sum('qty');

        // 1) Header base del PDF
        $header = [
            'id'                => $order->id,
            'work_id'           => $order->work_id,
            'pn'                => $order->PN,
            'description'       => $order->Part_description,
            'location'          => ucfirst($order->location ?? ''),
            'co'                => $order->co ?? '',
            'cust_po'           => $order->cust_po ?? '',
            'costumer'          => $order->costumer ?? '',
            'qty'               => $totalQty,   // 👈 suma padre + hijos
            'group_wo_qty'      => $order->group_wo_qty ?? 0,
            'due_date'          => $order->due_date,
            'operation'         => (int)($order->operation === 'default_value' ? 0 : ($order->operation ?? 0)),
            'sampling'          => (int)($order->sampling ?? 0),
            'sampling_check'    => $order->sampling_check ?? '',
            'total_fai'         => (int)($order->total_fai ?? 0),
            'total_ipi'         => (int)($order->total_ipi ?? 0),
            'status_inspection' => $order->status_inspection,
            'inspection_note' => $order->inspection_note,
        ];

        // 2) Líneas "DELIVER ... PIECES BY ... PO#..." sólo de HIJOS (agrupado por due_date+cust_po)
        $deliveries = \App\Models\OrderSchedule::query()
            ->where('parent_id', $parentId)
            ->selectRaw("
            due_date,
            COALESCE(cust_po,'') AS cust_po,
            SUM(COALESCE(wo_qty, qty, 0)) AS pieces
        ")
            ->groupBy('due_date', 'cust_po')
            ->orderBy('due_date')
            ->get()
            ->map(function ($r) {
                return [
                    'date'    => $r->due_date ? \Carbon\Carbon::parse($r->due_date)->format('m/d/Y') : '—',
                    'cust_po' => $r->cust_po ?: '—',
                    'pieces'  => (int) $r->pieces,
                ];
            })
            ->values()
            ->all();

        // (OPCIONAL) agregar también una línea del PADRE
        if ($header['due_date'] || $header['cust_po']) {
            $deliveries[] = [
                'date'    => $header['due_date'] ? \Carbon\Carbon::parse($header['due_date'])->format('m/d/Y') : '—',
                'cust_po' => $header['cust_po'] ?: '—',
                'pieces'  => (int) ($order->qty ?? $order->qty ?? 0),
            ];
        }

        // 3) Fechas de due_date de los hijos (lista y rango)
        $childrenDueDates = \App\Models\OrderSchedule::query()
            ->where('parent_id', $parentId)
            ->where('status', '<>', 'sent')           // opcional
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->distinct()
            ->pluck('due_date')
            ->all();

        $childrenDueDatesFmt = array_map(
            fn($d) => \Carbon\Carbon::parse($d)->format('m/d/Y'),
            $childrenDueDates
        );

        $header['children_due_dates_fmt'] = $childrenDueDatesFmt;
        $header['min_child_due'] = $childrenDueDates ? \Carbon\Carbon::parse($childrenDueDates[0])->format('m/d/Y') : null;
        $header['max_child_due'] = $childrenDueDates ? \Carbon\Carbon::parse(end($childrenDueDates))->format('m/d/Y') : null;
        $header['deliveries'] = $deliveries;

        // 4) Filas de inspección
        $rows = QaFaiSummary::where('order_schedule_id', $order->id)
            ->orderBy('date')
            ->orderBy('insp_type')
            ->orderBy('operation')
            ->get([
                'date',
                'insp_type',
                'operation',
                'operator',
                'results',
                'sb_is',
                'observation',
                'qty_pcs',
                'station',
                'method',
                'inspector',
            ]);

        $generatedAt = now('America/Los_Angeles');

        // 5) Resumen
        $summary = app(InspectionSummary::class)->summarize(
            $order,
            $header['operation'],
            $header['sampling']
        );

        // 6) Render PDF
        $pdf = PDF::loadView('qa.faisummary.faisummary_pdf', [
            'order'       => $order,
            'header'      => $header,
            'rows'        => $rows,
            'summary'     => $summary,
            'generatedAt' => $generatedAt,
            'user'        => $user,   // 👈 pasar a la vista
        ])->setPaper('letter', 'landscape');

        $dompdf = $pdf->getDomPDF();
        $dompdf->render();

        $canvas      = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();
        $font = $fontMetrics->get_font('DejaVu Sans', 'normal');
        $size = 10;

        $w = $canvas->get_width();
        $canvas->page_text($w - 70, 5, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, $size, [0, 0, 0]);

        $filename = 'FAI_' . str_replace(['/', '\\'], '-', (string)$order->work_id) . '.pdf';
        // Si viene ?download=1 => descarga, si no => stream en navegador
        if (request()->boolean('download')) {
            return $dompdf->stream($filename, ['Attachment' => true]);
        }
        return $dompdf->stream($filename, ['Attachment' => false]);
    }

    public function completedView(OrderSchedule $order)
    {
        $summary = app(InspectionSummary::class)->summarize($order, $order->operation, $order->sampling);

        return view('qa.faisummary.completed', [
            'order' => $order,
            'summary' => $summary,
        ]);
    }

    public function completedOrderEvents(OrderSchedule $order)
    {
        $order->load([
            'faiSummaries' => function ($q) {
                $q->orderByDesc('date')->orderByDesc('id');
            }
        ]);

        $noteLogs = OrderScheduleLog::query()
            ->with('user:id,name')
            ->where('order_id', $order->id)
            ->whereIn('field', ['inspection_note', 'notes'])
            ->orderByDesc('id')
            ->get();

        $summary = app(InspectionSummary::class)->summarize(
            $order,
            (int) ($order->operation ?? 0),
            (int) ($order->sampling ?? 0)
        );

        return view('qa.faisummary.faisummary_completed_order_events', [
            'order' => $order,
            'events' => $order->faiSummaries,
            'summary' => $summary,
            'noteLogs' => $noteLogs,
        ]);
    }

    protected function baseCompletedQuery(Request $request, bool $includeSearch = true, bool $includeStateFilter = false)
    {
        $q = OrderSchedule::query()
            ->select([
                'id',
                'parent_id',
                'work_id',
                'PN',
                'co',
                'cust_po',
                'Part_description',
                'operation',
                'wo_qty',
                'group_wo_qty',
                'location',
                'status_inspection',
                'total_fai',
                'total_ipi',
                'sampling',
                'sampling_check',
                'completed_by',
                'inspection_note',
                'inspection_endate'
            ])
            ->whereNull('parent_id')
            ->where('status_inspection', 'completed')
            ->orderByDesc('inspection_endate')
            ->with(['completedByUser:id,name'])
            ->withSum([
                'faiSummaries as fai_pass_qty' => function ($q) {
                    $q->where('insp_type', 'FAI')->where('results', 'pass');
                },
            ], 'qty_pcs')
            ->withSum([
                'faiSummaries as ipi_pass_qty' => function ($q) {
                    $q->where('insp_type', 'IPI')->where('results', 'pass');
                },
            ], 'qty_pcs');

        $year = $request->integer('year');
        $month = $request->integer('month');
        $day = trim((string) $request->input('day', ''));
        $location = trim((string) $request->input('location', ''));
        $search = trim((string) ($request->input('search.value') ?? $request->get('q', '')));

        if ($day !== '') {
            $q->whereDate('inspection_endate', \Carbon\Carbon::parse($day)->toDateString());
        } elseif ($year && $month) {
            $q->whereYear('inspection_endate', $year)->whereMonth('inspection_endate', $month);
        } elseif ($year && !$month) {
            $q->whereYear('inspection_endate', $year);
        } elseif (!$year && $month) {
            $q->whereYear('inspection_endate', now()->year)->whereMonth('inspection_endate', $month);
        }

        if ($location !== '') {
            $q->where('location', $location);
        }

        if ($includeSearch && $search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('work_id', 'like', "%{$search}%" )
                    ->orWhere('PN', 'like', "%{$search}%" )
                    ->orWhere('co', 'like', "%{$search}%" )
                    ->orWhere('cust_po', 'like', "%{$search}%" )
                    ->orWhere('Part_description', 'like', "%{$search}%" )
                    ->orWhere('location', 'like', "%{$search}%" );
            });
        }

        if ($includeStateFilter) {
            $onlyCompleted = $request->boolean('only_completed');
            $onlyIncomplete = $request->boolean('only_incomplete');
            $onlyNoInspection = $request->boolean('only_no_inspection');

            $noInspectionSql = '(COALESCE(sampling,0) = 0 OR COALESCE(operation,0) = 0 OR (COALESCE(total_fai,0) = 0 AND COALESCE(total_ipi,0) = 0))';
            $completedSql = '((COALESCE(total_fai,0) = 0 OR COALESCE(fai_pass_qty,0) >= COALESCE(total_fai,0)) AND (COALESCE(total_ipi,0) = 0 OR COALESCE(ipi_pass_qty,0) >= COALESCE(total_ipi,0)))';

            if ($onlyCompleted) {
                $q->havingRaw("NOT {$noInspectionSql} AND {$completedSql}");
            } elseif ($onlyIncomplete) {
                $q->havingRaw("NOT {$noInspectionSql} AND NOT {$completedSql}");
            } elseif ($onlyNoInspection) {
                $q->havingRaw($noInspectionSql);
            }
        }

        return $q;
    }

    protected function computeCompletedKpis($rows): array
    {
        $total = 0;
        $done = 0;
        $noInspection = 0;

        foreach ($rows as $row) {
            $total++;
            $sampling = (int) ($row->sampling ?? 0);
            $ops = (int) ($row->operation ?? 0);

            if ($sampling === 0 || $ops === 0 || ((int) ($row->total_fai ?? 0) === 0 && (int) ($row->total_ipi ?? 0) === 0)) {
                $noInspection++;
                continue;
            }

            $faiReq = (int) ($row->total_fai ?? 0);
            $ipiReq = (int) ($row->total_ipi ?? 0);
            $faiPass = (int) ($row->fai_pass_qty ?? 0);
            $ipiPass = (int) ($row->ipi_pass_qty ?? 0);

            if (($faiReq === 0 || $faiPass >= $faiReq) && ($ipiReq === 0 || $ipiPass >= $ipiReq)) {
                $done++;
            }
        }

        return [
            'total' => $total,
            'completed' => $done,
            'no_inspection' => $noInspection,
            'incomplete' => max(0, $total - $done - $noInspection),
        ];
    }
    public function exportCompletedExcel(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'strlen');

        if (!empty($ids)) {
            $rows = OrderSchedule::query()
                ->select([
                    'id',
                    'parent_id',
                    'work_id',
                    'PN',
                    'Part_description',
                    'operation',
                    'wo_qty',
                    'group_wo_qty',
                    'location',
                    'status_inspection',
                    'total_fai',
                    'total_ipi',
                    'sampling',
                    'sampling_check',
                    'inspection_endate'
                ])
                ->whereIn('id', $ids)
                ->withSum([
                    'faiSummaries as fai_pass_qty' => function ($q) {
                        $q->where('insp_type', 'FAI')
                            ->whereRaw('LOWER(results) = "pass"');
                    },
                ], 'qty_pcs')
                ->withSum([
                    'faiSummaries as ipi_pass_qty' => function ($q) {
                        $q->where('insp_type', 'IPI')
                            ->whereRaw('LOWER(results) = "pass"');
                    },
                ], 'qty_pcs')
                ->orderByDesc('inspection_endate')
                ->get();
        } else {
            $rows = $this->baseCompletedQuery($request, true, true)->get();
        }

        // ============================
        // EXPORT REUTILIZABLE (12 columnas)
        // ============================

        return Excel::download(
            new \App\Exports\BladeTableExport(
                view: 'qa.faisummary.excel_completed_table',
                data: ['rows' => $rows],

                title: 'FAI / IPI Completed Report',

                // ==== FORMATOS DE CADA COLUMNA (A..L = 12 columnas) ====
                columnFormats: [
                    'A' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME, // fecha
                    'G' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,        // wo_qty
                    'H' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,        // group_wo_qty
                    'K' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,        // total_fai
                    'L' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE,        // total_ipi
                ],

                // ==== COLUMNAS PARA CENTRAR ====
                centerCols: ['B', 'C', 'D', 'F', 'G', 'I', 'J'],

                // ==== COLUMNAS NUMÉRICAS A LA DERECHA ====
                rightCols: ['H', 'K', 'L'],

                // ==== ENVOLVER TEXTO (si aplica) ====
                wrapCols: ['F'], // Part_description (si es larga)
            ),

            'FAI_Completed_' . now()->format('Ymd_His') . '.xlsx'
        );
    }


    public function exportCompletedPdf(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'strlen');

        if (!empty($ids)) {
            $rows = OrderSchedule::query()
                ->select([
                    'id',
                    'parent_id',
                    'work_id',
                    'PN',
                    'Part_description',
                    'operation',
                    'wo_qty',
                    'group_wo_qty',
                    'location',
                    'status_inspection',
                    'total_fai',
                    'total_ipi',
                    'sampling',
                    'sampling_check',
                    'inspection_endate'
                ])
                ->whereIn('id', $ids)
                ->withSum([
                    'faiSummaries as fai_pass_qty' => function ($q) {
                        $q->where('insp_type', 'FAI')->whereRaw('LOWER(results) = "pass"');
                    },
                ], 'qty_pcs')
                ->withSum([
                    'faiSummaries as ipi_pass_qty' => function ($q) {
                        $q->where('insp_type', 'IPI')->whereRaw('LOWER(results) = "pass"');
                    },
                ], 'qty_pcs')
                ->orderByDesc('inspection_endate')
                ->get();
        } else {
            $rows = $this->baseCompletedQuery($request, true, true)->get();
        }

        $logoPath = public_path('vendor/adminlte/dist/img/accl.png'); // coloca tu logo aquí
        $generatedAt = now()->format('F j, Y g:i A');
        $userName = auth()->user()->name ?? 'System';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('qa.faisummary.pdf_completed_table', [
            'rows'         => $rows,
            'logoPath'     => $logoPath,
            'generated_at' => $generatedAt,
            'user'         => $userName,
        ])->setPaper('letter', 'landscape');

        return $pdf->stream('FAI_Completed.pdf');
    }


    /**
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++START+++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * ===================================================================================================================
     * 4. TAB "FAI/IPI SUMMARY" ------------Reject FAI Orders---------------
     * ===================================================================================================================
     */

    public function rejectedfaiorders(Request $request)
    {
        // Reutilizamos tu helper
        $data = $this->getFaiSummaryData($request);

        /** @var \Illuminate\Support\Collection $inspections */
        $inspections = $data['inspections'];

        $failedOrders = $inspections

            // A) Solo inspecciones FAI y con orden asociada
            ->filter(function ($i) {
                return $i->orderSchedule
                    && strcasecmp(trim((string)$i->insp_type), 'FAI') === 0;
            })

            // B) Agrupar por orden
            ->groupBy(function ($i) {
                return $i->order_schedule_id;
            })

            // C) Para cada orden:
            //    - Ver si tiene AL MENOS UN FAI no pass
            //    - Si sí, regresamos un registro para representar la fila (ej. el último FAI)
            ->map(function ($group) {
                $hasNonPass = $group->contains(function ($insp) {
                    $result = strtolower(trim((string) $insp->results));
                    return in_array($result, [
                        'fail',
                        'no pass',
                        'nopass',
                        'no_pass'
                    ], true);
                });

                if (! $hasNonPass) {
                    return null; // Esta orden nunca tuvo FAI no pass → no se incluye
                }

                // Puedes elegir qué inspección mostrar:
                // 1) El ÚLTIMO FAI (por fecha)
                return $group->sortByDesc('date')->first();

                // o 2) El ÚLTIMO FAI NO PASS (descomenta esto si prefieres eso)
                /*
            return $group->filter(function ($insp) {
                    $result = strtolower(trim((string) $insp->results));
                    return in_array($result, ['fail', 'no pass', 'nopass', 'no_pass'], true);
                })
                ->sortByDesc('date')
                ->first();
            */
            })

            // Quitar los null (órdenes sin FAI no pass)
            ->filter()

            ->values();

        // Puedes mandar solo esto, o también otros datos de $data si quieres
        return view('qa.faisummary.faisummary_rejectedfaiorders', [
            'failedOrders' => $failedOrders,
        ]);
    }

    public function orderInspections($orderId)
    {
        // Todas las inspecciones de esa orden (FAI + IPI, pass + no pass)
        $inspections = QaFaiSummary::with('orderSchedule')
            ->where('order_schedule_id', $orderId)
            ->orderBy('date', 'desc')
            ->get();

        // Devolvemos solo el HTML de las filas para el modal
        $html = view('qa.faisummary.faisummary_rejectedfairows', compact('inspections'))->render();

        return response()->json([
            'html' => $html,
        ]);
    }


    /**
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++START+++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * ===================================================================================================================
     * 5. TAB "FAI/IPI SUMMARY" ------------FAI Summary Statistics---------------
     * ===================================================================================================================
     */


    // Mostrar listado de registros
    public function faistatistics()
    {

        return view('qa.faisummary.faisummary_statistics');
    }


    public function faistatisticsData(Request $request)
    {
        $year = (int) ($request->integer('year') ?: now()->format('Y'));

        $rows = DB::table('qa_faisummary')
            ->whereYear('created_at', $year) // 👈 aquí
            ->selectRaw('
            QUARTER(`created_at`) as q, -- 👈 y aquí
            COUNT(*) as total,
            SUM(CASE WHEN LOWER(TRIM(results)) IN ("pass","ok") THEN 1 ELSE 0 END) as pass_cnt,
            SUM(CASE WHEN LOWER(TRIM(results)) IN ("no pass","fail","np") THEN 1 ELSE 0 END) as fail_cnt
        ')
            ->groupBy('q')
            ->orderBy('q')
            ->get();

        $quarters = collect([1, 2, 3, 4])->map(function ($q) use ($rows) {
            $r = $rows->firstWhere('q', $q);
            $total = (int) ($r->total ?? 0);
            $pass  = (int) ($r->pass_cnt ?? 0);
            $fail  = (int) ($r->fail_cnt ?? 0);
            return [
                'quarter'  => "Q{$q}",
                'total'    => $total,
                'pass'     => $pass,
                'fail'     => $fail,
                'pass_pct' => $total ? round($pass * 100 / $total, 2) : 0,
                'fail_pct' => $total ? round($fail * 100 / $total, 2) : 0,
            ];
        });

        // Totales por tipo de inspección (FAI / IPI)
        $typesRaw = DB::table('qa_faisummary')
            ->whereYear('created_at', $year)
            ->selectRaw("
                UPPER(TRIM(COALESCE(insp_type, 'N/A'))) as insp_type,
                COUNT(*) as total,
                SUM(CASE WHEN LOWER(TRIM(results)) IN ('pass','ok') THEN 1 ELSE 0 END) as pass_cnt,
                SUM(CASE WHEN LOWER(TRIM(results)) IN ('no pass','fail','np','no') THEN 1 ELSE 0 END) as fail_cnt
            ")
            ->groupBy('insp_type')
            ->get();

        $types = $typesRaw->map(function ($r) {
            $total = (int) ($r->total ?? 0);
            $pass  = (int) ($r->pass_cnt ?? 0);
            $fail  = (int) ($r->fail_cnt ?? 0);
            return [
                'type'     => $r->insp_type ?: 'N/A',
                'total'    => $total,
                'pass'     => $pass,
                'fail'     => $fail,
                'pass_pct' => $total ? round($pass * 100 / $total, 2) : 0,
                'fail_pct' => $total ? round($fail * 100 / $total, 2) : 0,
            ];
        });

        $global = [
            'year'  => $year,
            'total' => $quarters->sum('total'),
            'pass'  => $quarters->sum('pass'),
            'fail'  => $quarters->sum('fail'),
        ];
        $global['pass_pct'] = $global['total'] ? round($global['pass'] * 100 / $global['total'], 2) : 0;
        $global['fail_pct'] = $global['total'] ? round($global['fail'] * 100 / $global['total'], 2) : 0;

        return response()->json([
            'quarters' => $quarters,
            'global'   => $global,
            'types'    => $types,
        ]);
    }


    public function faistatisticsBy(Request $request)
    {
        $group = strtolower($request->get('group', 'operator')); // 'operator' | 'inspector'
        abort_unless(in_array($group, ['operator', 'inspector'], true), 400);

        // 1) Rango: prioriza start/end; si no, usa period/anchor
        $startRaw = $request->get('start');
        $endRaw   = $request->get('end');

        if ($startRaw && $endRaw) {
            try {
                $startC = \Carbon\Carbon::parse($startRaw);
                $endC   = \Carbon\Carbon::parse($endRaw);

                // Si no traen hora explícita, amplia a todo el día
                if (!preg_match('/\d{2}:\d{2}/', $startRaw)) $startC = $startC->startOfDay();
                if (!preg_match('/\d{2}:\d{2}/', $endRaw))   $endC   = $endC->endOfDay();

                // sanity: si end < start, intercambia
                if ($endC->lt($startC)) [$startC, $endC] = [$endC, $startC];

                $start = $startC->format('Y-m-d H:i:s');
                $end   = $endC->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
                return response()->json(['error' => 'Invalid start/end'], 422);
            }
        } else {
            $period = strtolower($request->get('period', 'year'));     // day|week|month|quarter|year
            $anchor = $request->get('anchor', now()->toDateString());  // yyyy-mm-dd
            // <<< asegúrate que periodBounds devuelva DATETIME (no toDateString)
            [$start, $end] = $this->periodBounds($period, $anchor);    // 'Y-m-d H:i:s'
        }

        $nameFilter = trim((string) $request->get('name', ''));

        // 2) Columna a agrupar
        $col = $group === 'inspector' ? 'inspector' : 'operator';
        $nameExpr = "COALESCE(NULLIF(TRIM($col),''), '(Sin $group)')";

        // 3) Query (usa la columna de fecha correcta: 'created_at' o 'date')
        $q = DB::table('qa_faisummary')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("$nameExpr AS name")
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("SUM(CASE WHEN LOWER(TRIM(results)) IN ('pass','ok','p') THEN 1 ELSE 0 END) AS pass_cnt")
            ->selectRaw("SUM(CASE WHEN LOWER(TRIM(results)) IN ('no pass','fail','np','f','no') THEN 1 ELSE 0 END) AS fail_cnt")
            ->groupBy(DB::raw($nameExpr));

        if ($nameFilter !== '') {
            $q->having('name', '=', $nameFilter);
        }

        $rows = $q->orderByDesc('total')->get();

        $data = $rows->map(function ($r) {
            $t = (int)($r->total ?? 0);
            $p = (int)($r->pass_cnt ?? 0);
            $f = (int)($r->fail_cnt ?? 0);
            return [
                'name'     => $r->name,
                'total'    => $t,
                'pass'     => $p,
                'fail'     => $f,
                'pass_pct' => $t ? round($p * 100 / $t, 2) : 0.0,
                'fail_pct' => $t ? round($f * 100 / $t, 2) : 0.0,
            ];
        })->values();

        return response()->json([
            'group' => $group,
            'start' => $start, // con hora
            'end'   => $end,   // con hora
            'rows'  => $data,
        ]);
    }


    /**
     * Calcula rango por periodo respecto a anchor (Y-m-d).
     */
    protected function periodBounds(string $period, ?string $anchorDate): array
    {
        $anchor = $anchorDate ? Carbon::parse($anchorDate) : now();

        switch (strtolower($period)) {
            case 'day':
                $start = $anchor->copy()->startOfDay();
                $end   = $anchor->copy()->endOfDay();
                break;
            case 'week':
                // Si quieres ISO (lunes-domingo) asegúrate que Carbon esté configurado con weekStartsAt = Monday
                $start = $anchor->copy()->startOfWeek();
                $end   = $anchor->copy()->endOfWeek();
                break;
            case 'month':
                $start = $anchor->copy()->startOfMonth();
                $end   = $anchor->copy()->endOfMonth();
                break;
            case 'quarter':
            case 'trimester':
                $start = $anchor->copy()->firstOfQuarter();
                $end   = $anchor->copy()->lastOfQuarter()->endOfDay();
                break;
            case 'year':
            default:
                $start = $anchor->copy()->startOfYear();
                $end   = $anchor->copy()->endOfYear();
                break;
        }
        return [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')];
    }


    public function faistatisticsByQuarterOperator(Request $request)
    {
        $year     = (int) $request->get('year', now()->year);
        $operator = $request->get('operator'); // <- opcional (nombre tal como viene en la tabla)

        // Base query
        $base = DB::table('qa_faisummary')
            ->whereYear('created_at', $year);

        if (!empty($operator)) {
            $base->where('operator', $operator);
        }

        // 1) Datos por operador × quarter
        $q = (clone $base)
            ->selectRaw("
            COALESCE(NULLIF(operator, ''), '(Sin operador)') as operator,
            QUARTER(created_at) as quarter,
            COUNT(*) as total,
            SUM(results = 'pass')    as pass,
            SUM(results = 'no pass') as fail
        ")
            ->groupBy('operator', 'quarter')
            ->orderBy('operator')
            ->get();

        // Normaliza a matriz [operator][quarter]
        $rows = [];
        foreach ($q as $r) {
            $op = $r->operator;
            $rows[$op][$r->quarter] = [
                'total'    => (int) $r->total,
                'pass'     => (int) $r->pass,
                'fail'     => (int) $r->fail,
                'pass_pct' => $r->total ? ($r->pass * 100 / $r->total) : 0,
                'fail_pct' => $r->total ? ($r->fail * 100 / $r->total) : 0,
            ];
        }

        // 2) Lista de operadores disponibles (para poblar el <select>)
        //    La basamos en el mismo filtro de año, y si llegó 'operator', igual
        //    devolvemos la lista completa de ese año (para que el select siga mostrando todos).
        $operators = (clone $base)
            ->whereNotNull('operator')
            ->where('operator', '<>', '')
            ->distinct()
            ->orderBy('operator')
            ->pluck('operator')
            ->values(); // array simple

        return response()->json([
            'rows'      => $rows,
            'operators' => $operators,
            'year'      => $year,
            'operator'  => $operator,
        ]);
    }

    public function nextNcarNumber(Request $request)
    {
        $ncartypeId = (int) $request->query('ncartype_id', 0);
        if ($ncartypeId > 0) {
            $row = DB::table('qa_ncartype as t')
                ->join('qa_ncar_counter as c', 'c.ncartype_id', '=', 't.id')
                ->select([
                    't.id as ncartype_id',
                    't.code',
                    't.prefix',
                    'c.next_number',
                ])
                ->where('t.id', $ncartypeId)
                ->first();

            if (!$row) {
                return response()->json([
                    'success' => false,
                    'message' => 'NCAR type/counter not found.',
                ], 404);
            }

            return response()->json([
                'success'     => true,
                'type'        => null,
                'code'        => $row->code,
                'ncartype_id' => (int) $row->ncartype_id,
                'prefix'      => (string) $row->prefix,
                'next_number' => (int) $row->next_number,
                'ncar_no'     => ((string) $row->prefix) . ((int) $row->next_number),
            ]);
        }

        $type = strtolower((string) $request->query('type', ''));

        $map = [
            'internal' => 'INTERNAL',
            'external' => 'EXTERNAL',
            'customer' => 'CUSTOMER',
            'qa'       => 'QA',
        ];

        if (!isset($map[$type])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid NCAR type.',
            ], 422);
        }

        $code = $map[$type];

        $row = DB::table('qa_ncartype as t')
            ->join('qa_ncar_counter as c', 'c.ncartype_id', '=', 't.id')
            ->select([
                't.id as ncartype_id',
                't.code',
                't.prefix',
                'c.next_number',
            ])
            ->where('t.code', $code)
            ->first();

        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'NCAR type/counter not found.',
            ], 404);
        }

        return response()->json([
            'success'     => true,
            'type'        => $type,
            'code'        => $row->code,
            'ncartype_id' => (int) $row->ncartype_id,
            'prefix'      => (string) $row->prefix,
            'next_number' => (int) $row->next_number,
            'ncar_no'     => ((string) $row->prefix) . ((int) $row->next_number),
        ]);
    }

    public function ncarTypes()
    {
        $types = DB::table('qa_ncartype')
            ->select(['id', 'code', 'name', 'prefix'])
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => (int) $r->id,
                    'code' => (string) $r->code,
                    'name' => (string) $r->name,
                    'prefix' => (string) $r->prefix,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    public function ncarStages(Request $request)
    {
        if (!Schema::hasTable('qa_ncar_stage')) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $ncartypeId = (int) $request->query('ncartype_id', 0);
        $code = strtoupper(trim((string) $request->query('code', '')));
        $includeInactive = (bool) $request->query('include_inactive', false);

        $q = DB::table('qa_ncar_stage as s')
            ->select(['s.id', 's.ncartype_id', 's.stage', 's.is_active']);

        if (!$includeInactive) {
            $q->where('s.is_active', 1);
        }

        if ($ncartypeId > 0) {
            $q->where('s.ncartype_id', $ncartypeId);
        } elseif ($code !== '') {
            $q->join('qa_ncartype as t', 't.id', '=', 's.ncartype_id')
                ->whereRaw('UPPER(TRIM(t.code)) = ?', [$code]);
        } else {
            return response()->json(['success' => true, 'data' => []]);
        }

        $rows = $q
            ->orderBy('s.stage')
            ->get()
            ->map(fn($r) => [
                'id' => (int) $r->id,
                'ncartype_id' => (int) $r->ncartype_id,
                'stage' => (string) $r->stage,
                'is_active' => (int) ($r->is_active ?? 0),
            ])
            ->values();

        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function storeNcarStage(Request $request)
    {
        if (!Schema::hasTable('qa_ncar_stage')) {
            return response()->json(['success' => false, 'message' => 'qa_ncar_stage table not found.'], 422);
        }

        $data = $request->validate([
            'ncartype_id' => ['required', 'integer'],
            'stage' => ['required', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $typeId = (int) $data['ncartype_id'];
        $existsType = Schema::hasTable('qa_ncartype') && DB::table('qa_ncartype')->where('id', $typeId)->exists();
        if (!$existsType) {
            return response()->json(['success' => false, 'message' => 'NCAR type not found.'], 422);
        }

        $stage = trim((string) $data['stage']);
        $isActive = array_key_exists('is_active', $data) ? (int) ((bool) $data['is_active']) : 1;

        try {
            $id = DB::table('qa_ncar_stage')->insertGetId([
                'ncartype_id' => $typeId,
                'stage' => $stage,
                'is_active' => $isActive,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'id' => (int) $id]);
        } catch (\Illuminate\Database\QueryException $e) {
            $sqlState = (string) ($e->errorInfo[0] ?? '');
            if ($sqlState === '23000') {
                return response()->json(['success' => false, 'message' => 'Stage already exists for this NCAR type.'], 422);
            }
            return response()->json(['success' => false, 'message' => 'Could not save stage.'], 500);
        }
    }

    public function updateNcarStage(Request $request, $id)
    {
        if (!Schema::hasTable('qa_ncar_stage')) {
            return response()->json(['success' => false, 'message' => 'qa_ncar_stage table not found.'], 422);
        }

        $stageId = (int) $id;
        $row = DB::table('qa_ncar_stage')->select(['id', 'ncartype_id'])->where('id', $stageId)->first();
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Stage not found.'], 404);
        }

        $data = $request->validate([
            'ncartype_id' => ['nullable', 'integer'],
            'stage' => ['required', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (!empty($data['ncartype_id']) && (int) $data['ncartype_id'] !== (int) $row->ncartype_id) {
            return response()->json(['success' => false, 'message' => 'NCAR type mismatch.'], 422);
        }

        $stage = trim((string) $data['stage']);
        $isActive = array_key_exists('is_active', $data) ? (int) ((bool) $data['is_active']) : 1;

        try {
            DB::table('qa_ncar_stage')
                ->where('id', $stageId)
                ->update([
                    'stage' => $stage,
                    'is_active' => $isActive,
                    'updated_at' => now(),
                ]);

            return response()->json(['success' => true]);
        } catch (\Illuminate\Database\QueryException $e) {
            $sqlState = (string) ($e->errorInfo[0] ?? '');
            if ($sqlState === '23000') {
                return response()->json(['success' => false, 'message' => 'Stage already exists for this NCAR type.'], 422);
            }
            return response()->json(['success' => false, 'message' => 'Could not update stage.'], 500);
        }
    }

	    public function storeNcar(Request $request)
	    {
	        $data = $request->validate([
	            'order_id' => ['nullable', 'integer'],
	            'type' => ['nullable', 'string'],
	            'ncartype_id' => ['nullable', 'integer'],
	            'stage' => ['required', 'string', 'max:120'],
	            'ncar_date' => ['nullable', 'date'],
	            'nc_description' => ['required', 'string'],
	            'contact' => ['nullable', 'string', 'max:120'],
	            'ncar_class' => ['nullable', 'string', 'max:120'],
	            'ncar_customer' => ['nullable', 'string', 'max:180'],
	            'ref' => ['nullable', 'string', 'max:120'],
	        ]);

        $typeRow = null;
        if (!empty($data['ncartype_id'])) {
            $typeRow = DB::table('qa_ncartype')
                ->select(['id', 'prefix', 'code', 'name'])
                ->where('id', (int) $data['ncartype_id'])
                ->first();
        } else {
            $type = strtolower(trim((string) ($data['type'] ?? '')));
            $map = [
                'internal' => 'INTERNAL',
                'external' => 'EXTERNAL',
                'customer' => 'CUSTOMER',
                'qa'       => 'QA',
            ];
            if (!isset($map[$type])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid NCAR type.',
                ], 422);
            }

            $typeRow = DB::table('qa_ncartype')
                ->select(['id', 'prefix', 'code', 'name'])
                ->where('code', $map[$type])
                ->first();
        }

        if (!$typeRow) {
            return response()->json([
                'success' => false,
                'message' => 'NCAR type not found in qa_ncartype.',
            ], 404);
        }

        $order = null;
        if (!empty($data['order_id'])) {
            $orderSelect = ['id', 'work_id', 'location'];
            foreach (['co', 'cust_po', 'PN', 'Part_description', 'costumer', 'qty'] as $col) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('orders_schedule', $col)) {
                    $orderSelect[] = $col;
                }
            }
            $order = DB::table('orders_schedule')
                ->select($orderSelect)
                ->where('id', (int) $data['order_id'])
                ->first();
        }

        $insert = [
            'order_id' => $order ? (int) $order->id : null,
            'ncartype_id' => (int) $typeRow->id,
            'ncar_no' => '',
            'ncar_date' => $data['ncar_date'] ?? null,
            'status' => 'New',
            'ncar_customer' => null,
            'stage' => $data['stage'] ?? null,
            'nc_description' => $data['nc_description'] ?? null,
            'location' => $order ? (string) ($order->location ?? '') : null,
        ];

	        // Customer
	        $ncarCustomer = trim((string) ($data['ncar_customer'] ?? ''));
	        if ($ncarCustomer === '' && $order) {
	            $ncarCustomer = trim((string) ($order->costumer ?? ''));
	        }
	        if ($ncarCustomer !== '') {
	            $insert['ncar_customer'] = $ncarCustomer;
	        }

	        // Ref (Customer NCAR reference)
	        $ref = trim((string) ($data['ref'] ?? ''));
	        if ($ref !== '' && \Illuminate\Support\Facades\Schema::hasColumn('qa_ncar', 'ref')) {
	            $insert['ref'] = $ref;
	        }

        // Contact (para el header del NCAR)
        $contact = trim((string) ($data['contact'] ?? ''));
        if ($contact === '') {
            $contact = trim((string) (auth()->user()->name ?? auth()->user()->email ?? ''));
        }
        if ($contact !== '') {
            if (\Illuminate\Support\Facades\Schema::hasColumn('qa_ncar', 'contact')) {
                $insert['contact'] = $contact;
            } elseif (\Illuminate\Support\Facades\Schema::hasColumn('qa_ncar', 'ncar_contact')) {
                $insert['ncar_contact'] = $contact;
            }
        }

        // Class (Internal/External NCAR label)
        $ncarClass = trim((string) ($data['ncar_class'] ?? ''));
        if ($ncarClass === '') {
            $ncarClass = trim((string) ($typeRow->name ?? ''));
        }
        if ($ncarClass !== '') {
            if (\Illuminate\Support\Facades\Schema::hasColumn('qa_ncar', 'ncar_class')) {
                $insert['ncar_class'] = $ncarClass;
            } elseif (\Illuminate\Support\Facades\Schema::hasColumn('qa_ncar', 'class')) {
                $insert['class'] = $ncarClass;
            }
        }

        // Guardar qty si viene numérica desde orders_schedule (si no, dejar null)
        if (
            $order &&
            \Illuminate\Support\Facades\Schema::hasColumn('qa_ncar', 'qty') &&
            \Illuminate\Support\Facades\Schema::hasColumn('orders_schedule', 'qty')
        ) {
            $parentQty = is_numeric($order->qty ?? null) ? (int) $order->qty : 0;
            $childSum = (int) (DB::table('orders_schedule')
                ->where('parent_id', (int) $order->id)
                ->selectRaw('SUM(COALESCE(qty,0)) as s')
                ->value('s') ?? 0);

            $totalQty = $parentQty + $childSum;
            $insert['qty'] = $totalQty > 0 ? $totalQty : null;
        }

        $id = DB::table('qa_ncar')->insertGetId($insert);

        $ncar = DB::table('qa_ncar as n')
            ->join('qa_ncartype as t', 't.id', '=', 'n.ncartype_id')
            ->select([
                'n.id',
                'n.ncar_no',
                'n.created_at',
                'n.ncar_date',
                'n.status',
                't.name as type_name',
            ])
            ->where('n.id', $id)
            ->first();

        return response()->json([
            'success' => true,
            'id' => (int) $ncar->id,
            'ncar_no' => (string) $ncar->ncar_no,
            'edit_url' => route('nonconformance.ncar.edit', ['id' => (int) $ncar->id]),
            'created_at' => (string) $ncar->created_at,
            'ncar_date' => $ncar->ncar_date,
            'status' => $ncar->status,
            'type' => $ncar->type_name,
        ]);
    }
}






