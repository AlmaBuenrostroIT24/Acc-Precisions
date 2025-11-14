<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QaFaiSummary extends Model
{
    use HasFactory;

    protected $table = 'qa_faisummary';

    protected $fillable = [
        'date',
        'insp_type',
        'operation',
        'operator',
        'results',
        'sb_is',
        'observation',
        'station',
        'method',
        'qty_pcs',
        'inspector',
        'status_operation',
        'order_schedule_id',
        'loc_inspection',
    ];

    // Relación con OrderSchedule (si existe ese modelo)
    public function orderSchedule()
    {
        return $this->belongsTo(OrderSchedule::class);
    }

    
}
