<?php

namespace App\Services;

use App\Models\OrderSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrderScheduleImportService
{
    public int $importedCount = 0;

    protected $columnsToRemove = [
        'company_name',
        'run_comments',
        'customer_name',
        'part_product_code',
        'sales_rep',
        'promise_date',
        'promise_del_date',
        'due_year',
        'due_month',
        'line_unit_price',
        'line_disc_unit_price',
        'line_trade_disc_pct',
        'line_cust_part_id',
        'line_gl_rev_acct_id',
        'line_backorder_qty',
        'line_selling_um',
        'backorder_amount',
        'edi_release_no',
        'line_no',
        'lot_id',
        'split_id',
        'co_user_1',
        'co_user_2',
        'co_user_3',
        'co_user_4',
        'co_user_5',
        'co_user_6',
        'co_user_7',
        'co_user_8',
        'co_user_9',
        'co_user_10',
        'col_user_1',
        'col_user_2',
        'col_user_3',
        'col_user_4',
        'col_user_5',
        'col_user_6',
        'col_user_7',
        'col_user_8',
        'col_user_9',
        'col_user_10',
        'run_comments2',
        'site_id',
        'multi_site',
        'site_name',
        'entity_id',
        'entity_name',
        'entity_sites',
        'total_text_site',
        'total_text_entity',
        'total_text_rpt',
        'multi_sites_in_rpt',
        'multi_entities_in_rpt',
        'not_native_currency',
        'print_native_currency',
        'report_currency',
        'run_comments3',
        'order_backorder_amount',
        'order_currency_id',
        'site_total',
        'entity_total',
        'report_total',
        'prepay_amount',
        'cur_amount',
        'cur_line_unit_price',
        'cur_line_disc_unit_price',
        'cur_order_total',
        'cur_backorder_amount',
        'cur_site_total',
        'cur_site_backorder_amount',
        'cur_site_total_prepay',
        'cur_entity_total',
        'cur_entity_backorder_amount',
        'cur_entity_total_prepay',
        'cur_report_total',
        'cur_report_backorder_amount',
        'cur_report_total_prepay',
        'amount_pending',
        'cur_amount_pending',
        'prepay_invoices',
        'prepay_amount',
        'tot_amount_pending',
        'total_prepay',
        'report_total_prepay',
        'no_backorder',
        'cur_break_total',
        'cur_break_backorder_amount',
        'cur_break_total_prepay',
    ];

public function relabelParents(): void
    {
        DB::transaction(function () {

            // 1) group_key para todos los no 'sent' (puedes dejarlo como PN#work_id)
            DB::statement("
            UPDATE orders_schedule
            SET group_key = CONCAT(PN, '#', COALESCE(NULLIF(work_id,''), 'NO-WO'))
            WHERE LOWER(status) <> 'sent'
        ");

            // 2.0) IMPORTANTE: resetear parent_id para re-agrupar desde cero (solo no 'sent')
            DB::statement("
            UPDATE orders_schedule
            SET parent_id = NULL
            WHERE LOWER(status) <> 'sent'
        ");

            // 2) Asignar UN solo padre por PN (ignora work_id)
    DB::statement("
UPDATE orders_schedule os
JOIN (
  SELECT t.PN, MAX(t.id) AS parent_id
  FROM orders_schedule t
  JOIN (
     SELECT PN,
            MAX(
              COALESCE(
                STR_TO_DATE(due_date, '%Y-%m-%d'),
                STR_TO_DATE(due_date, '%b-%d-%y'),
                STR_TO_DATE(due_date, '%m/%d/%Y')
              )
            ) AS max_due
     FROM orders_schedule
     WHERE LOWER(status) <> 'sent'
     GROUP BY PN
  ) g ON g.PN = t.PN
     AND COALESCE(
           STR_TO_DATE(t.due_date, '%Y-%m-%d'),
           STR_TO_DATE(t.due_date, '%b-%d-%y'),
           STR_TO_DATE(t.due_date, '%m/%d/%Y'),
           STR_TO_DATE('1970-01-01', '%Y-%m-%d')
         ) = g.max_due
  WHERE LOWER(t.status) <> 'sent'
  GROUP BY t.PN
) p ON p.PN = os.PN
SET os.parent_id = CASE
  WHEN os.id = p.parent_id THEN NULL
  ELSE p.parent_id
END
WHERE LOWER(os.status) <> 'sent'
");


            // 2.5) Copiar qty → wo_qty SOLO en hijos con wo_qty vacío/0
DB::statement("
UPDATE orders_schedule c
LEFT JOIN (
  /* Penúltima fecha (DESC) por grupo raíz: padre + hijos */
  SELECT pen.id AS penultimate_id, pen.grp_id
  FROM (
    SELECT
      x.id,
      COALESCE(x.parent_id, x.id) AS grp_id,
      ROW_NUMBER() OVER (
        PARTITION BY COALESCE(x.parent_id, x.id)
        ORDER BY
          COALESCE(
            STR_TO_DATE(x.due_date, '%Y-%m-%d'),
            STR_TO_DATE(x.due_date, '%b-%d-%y'),
            STR_TO_DATE(x.due_date, '%m/%d/%Y'),
            STR_TO_DATE('1970-01-01', '%Y-%m-%d')
          ) DESC,
          x.id DESC
      ) AS rn
    FROM orders_schedule x
    WHERE x.parent_id IS NOT NULL OR x.parent_id IS NULL
  ) pen
  WHERE pen.rn = 2  -- penúltima del grupo completo
) p ON p.grp_id = c.parent_id  -- los hijos comparten grp_id = parent_id
SET c.wo_qty = COALESCE(c.qty, 0)
WHERE c.parent_id IS NOT NULL
  AND (c.wo_qty IS NULL OR c.wo_qty = 0)
  AND (p.penultimate_id IS NULL OR c.id <> p.penultimate_id)
");


            // 3) Guardar TOTAL del grupo en el PADRE (recalcular SIEMPRE)
            DB::statement("
            UPDATE orders_schedule p
            JOIN (
                SELECT COALESCE(parent_id, id) AS grp_parent_id,
                       SUM(COALESCE(wo_qty,0)) AS total_qty
                FROM orders_schedule
                GROUP BY COALESCE(parent_id, id)
            ) s  ON s.grp_parent_id = p.id
            SET p.group_wo_qty = s.total_qty
            WHERE p.parent_id IS NULL
        ");

            // 4) Limpiar total en hijos (si tienen algo)
            DB::statement("
            UPDATE orders_schedule
            SET group_wo_qty = NULL
            WHERE parent_id IS NOT NULL
              AND group_wo_qty IS NOT NULL
        ");
        });
    }








    public function cleanAndPrepareRow(array $row): ?OrderSchedule
    {
        // Eliminar columnas innecesarias
        foreach ($this->columnsToRemove as $column) {
            unset($row[$column]);
        }

        // Eliminar columnas con valor null
        foreach ($row as $key => $value) {
            if (is_null($value)) {
                unset($row[$key]);
            }
        }

        // Formatear fecha
        if (isset($row['due_date'])) {
            $dateString = $row['due_date'];

            $dateString = preg_replace('/^(\d{4}-\d{2}-\d{2})-(\d{2}\.\d{2}\.\d{2}\.\d+)$/', '$1 $2', $dateString);
            $dateString = str_replace('.', ':', $dateString);

            $date = \DateTime::createFromFormat('Y-m-d H:i:s:u', $dateString);

            $row['due_date'] = $date ? $date->format('Y-m-d H:i:s.u') : null;
        }

        // Validar campos mínimos necesarios
        foreach (['part_id', 'misc_reference', 'due_date'] as $key) {
            if (!isset($row[$key])) {
                return null;
            }
        }
        // Validación mínima antes de insertar
        if (!isset($row['part_id'], $row['misc_reference'], $row['due_date'])) {
            return null;
        }

        // Verificar si ya existe en base de datos
        $exists = OrderSchedule::where('PN', $row['part_id'])
            ->where('Part_description', $row['misc_reference'])
            ->where('due_date', $row['due_date'])
            ->exists();

        if ($exists) {
            return null;
        }

        $this->importedCount++; // Incrementar contador

        // Calcular diferencia de días
        $dueDate = Carbon::parse($row['due_date']);
        $days = Carbon::now()->diffInDays($dueDate, false);
        $alert = $days < 0 ? '1' : '0';
        // Calcular el work_id
        $workId = isset($row['work_order_id']) && !empty($row['work_order_id'])
            ? substr($row['work_order_id'], 2)
            : null;

        // Crear modelo listo para guardar
        return new OrderSchedule([
            'work_id'          => $workId,
            'was_work_id_null' => is_null($workId), // 👈 aquí lo asignas según si fue null
            'PN'              => $row['part_id'],
            'Part_description' => $row['misc_reference'],
            'costumer'        => $row['customer_id'],
            'qty'             => $row['line_qty'],
            'co'             => $row['order_id'],
            'cust_po'             => $row['customer_po_ref'],
            'operation'       => $row['operation'] ?? '0',
            'machines'        => $row['machines'] ?? 'default_value',
            'done'            => $row['done'] ?? '1',
            'status'          => $row['status'] ?? 'pending',
            'status_order'    => $row['status_order'] ?? 'active',
            'machining_date' => isset($row['due_date']) ? Carbon::parse($row['due_date'])->copy()->subWeekdays(5) : null,
            'due_date'        => $row['due_date'],
            'days'            => $days,
            'alert'           => $alert,
            'report'          => $row['report'] ?? '0',
            'our_source'      => $row['our_source'] ?? '0',
            'assigned_to'     => $row['station'] ?? '""',
            'notes'   => $row['notes'] ?? '',
            'location'        => $row['location'] ?? 'Floor',
            'priority'        => $row['priority'] ?? 'no',
            'assigned_to'     => $row['assigned_to'] ?? '1',
            'material_type'   => $row['material_type'] ?? 'default_value',
            'process_time'    => $row['process_time'] ?? '23',
            'canceled'        => $row['canceled'] ?? '1',
            'tracking_number' => $row['tracking_number'] ?? 'default_value',
            'revision'        => $row['revision'] ?? 'default_value',
            'total_fai'        => $row['total_fai'] ?? '0',
            'total_ipi'        => $row['total_ipi'] ?? '0',
            'sampling'        => $row['sampling'] ?? '0',
            'status_inspection'        => $row['status_inspection'] ?? 'pending',
            'created_by'      => auth()->check() ? auth()->id() : null,
        ]);
    }
}
