<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\OrderScheduleLog;     // 👈 importante
use Illuminate\Support\Facades\Auth; // 👈 importante
use Illuminate\Support\Facades\Log;  // 👈 importante


class OrderSchedule extends Model
{
    use HasFactory;
    // Especifica el nombre de la tabla si no sigue la convención plural
    protected $table = 'orders_schedule';

    // Campos que se pueden asignar de forma masiva
    protected $fillable = [
        'work_id',
        'was_work_id_null',
        'parent_id',
        'group_key',
        'co',
        'cust_po',
        'PN',
        'Part_description',
        'costumer',
        'qty',
        'wo_qty',
        'operation',
        'machines',
        'done',
        'status',
        'status_order',
        'sent_at',
        'target_date',
        'endate_mach',
        'target_mach',
        'machining_date',
        'status_at_endate_mach',
        'due_date',
        'days',
        'alert',
        'report',
        'station',
        'our_source',
        'notes',
        'location',
        'last_location',
        'priority',
        'assigned_to',
        'material_type',
        'process_time',
        'created_by',
        'canceled',
        'tracking_number',
        'revision',
        'total_fai',
        'total_ipi',
        'sampling',
        'status_inspection',
        'sampling_check',
        'inspection_date',
        'inspection_note',
        'group_wo_qty',
        'inspection_progress',
        'was_endsentat_modified',
        'ncr_number',
        'ncr_notes',
    ];

    // Casts para manejar tipos de datos correctamente
    protected $casts = [
        'done' => 'boolean',
        'alert' => 'boolean',
        'canceled' => 'boolean',
        'machining_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'endate_mach' => 'datetime',
        'inspection_endate' => 'datetime',
        'inspection_progress' => 'integer', // 👈 nuevo
        'canceled' => 'boolean',
    ];

    // Relaciones con el modelo User
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function machiningDateLogs()
    {
        return $this->hasMany(OrdMachiningDateLog::class, 'order_schedule_id');
    }

    public function faiSummaries()
    {
        return $this->hasMany(\App\Models\QaFaiSummary::class, 'order_schedule_id');
    }

    public function completedByUser()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    protected static function booted()
    {
        static::updated(function ($order) {

            // 🔍 DEBUG: Ver si el evento se dispara
            Log::info('OrderSchedule updated', [
                'id'      => $order->id,
                'changes' => $order->getChanges(),
                'original' => $order->getOriginal(),
                'user'    => Auth::id(),
            ]);

            $changes  = $order->getChanges();
            $original = $order->getOriginal();

            foreach ($changes as $field => $newValue) {

                if ($field === 'updated_at') continue;

                OrderScheduleLog::create([
                    'order_id'  => $order->id,
                    'user_id'   => Auth::id(),
                    'field'     => $field,
                    'old_value' => $original[$field] ?? null,
                    'new_value' => $newValue,
                ]);
            }
        });
    }
}
