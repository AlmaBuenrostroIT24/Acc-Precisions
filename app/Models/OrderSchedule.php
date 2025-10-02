<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


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
        'enddate_mach',
        'target_mach',
        'machining_date',
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
        'inspection_date'
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
}
