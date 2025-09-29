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
        // 0) Rellenar work_id faltantes (NULL o '') con el work_id de referencia por PN
        //    (El de la orden más reciente con status <> 'sent' y work_id no vacío)
        DB::statement("
            UPDATE orders_schedule os
            JOIN (
              SELECT pn,
                     SUBSTRING_INDEX(
                       GROUP_CONCAT(work_id ORDER BY due_date DESC, id DESC SEPARATOR ','),
                       ',', 1
                     ) AS ref_work_id
              FROM orders_schedule
              WHERE status <> 'sent'
                AND work_id IS NOT NULL AND work_id <> ''
              GROUP BY pn
            ) w ON w.pn = os.pn
            SET os.work_id = w.ref_work_id
            WHERE os.status <> 'sent'
              AND (os.work_id IS NULL OR os.work_id = '')
              AND w.ref_work_id IS NOT NULL
        ");

        // 1) group_key (solo status <> 'sent' y sin padre)
        DB::statement("
            UPDATE orders_schedule
            SET group_key = CONCAT(PN, '#', COALESCE(NULLIF(work_id, ''), 'NO-WO'))
            WHERE status <> 'sent' AND parent_id IS NULL
        ");

        // 2) Re-etiquetar padre/hijos (solo status <> 'sent' y sin padre)
        DB::statement("
            UPDATE orders_schedule os
            JOIN (
              SELECT t.PN,
                     COALESCE(NULLIF(t.work_id, ''), 'NO-WO') AS g_work,
                     MAX(t.id) AS parent_id
              FROM orders_schedule t
              JOIN (
                 SELECT PN,
                        COALESCE(NULLIF(work_id, ''), 'NO-WO') AS g_work,
                        MAX(due_date) AS max_due
                 FROM orders_schedule
                 WHERE status <> 'sent' AND parent_id IS NULL
                 GROUP BY PN, COALESCE(NULLIF(work_id, ''), 'NO-WO')
              ) g
                ON g.PN = t.PN
               AND g.g_work = COALESCE(NULLIF(t.work_id, ''), 'NO-WO')
               AND t.due_date = g.max_due
              WHERE t.status <> 'sent' AND t.parent_id IS NULL
              GROUP BY t.PN, COALESCE(NULLIF(t.work_id, ''), 'NO-WO')
            ) p
              ON p.PN = os.PN
             AND p.g_work = COALESCE(NULLIF(os.work_id, ''), 'NO-WO')
            SET os.parent_id = CASE
              WHEN os.id = p.parent_id THEN NULL   -- padre
              ELSE p.parent_id                     -- hijo
            END
            WHERE os.status <> 'sent' AND os.parent_id IS NULL
        ");

        // 3) Guardar TOTAL del grupo en la fila PADRE (status <> 'sent')
        DB::statement("
            UPDATE orders_schedule p
            JOIN (
                SELECT
                    COALESCE(parent_id, id) AS grp_parent_id,
                    SUM(COALESCE(qty,0)) AS total_qty
                FROM orders_schedule
                WHERE status <> 'sent'
                GROUP BY COALESCE(parent_id, id)
            ) s
              ON s.grp_parent_id = p.id
            SET p.group_wo_qty = s.total_qty
            WHERE p.status <> 'sent'
              AND p.parent_id IS NULL   -- solo padres
        ");

        // 4) (Opcional) limpiar campo en hijos
        DB::statement("
            UPDATE orders_schedule
            SET group_wo_qty = NULL
            WHERE status <> 'sent' AND parent_id IS NOT NULL
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
            'notes'           => $row['notes'] ?? '',
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
