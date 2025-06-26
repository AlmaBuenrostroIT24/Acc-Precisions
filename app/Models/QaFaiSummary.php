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
        'num_operation',
        'insp_type',
        'operation',
        'operator',
        'results',
        'sb_is',
        'observation',
        'station',
        'method',
        'inspector',
        'part_rev',
        'job',
        'status_operation',
        'order_schedule_id',
    ];

    // Relación con OrderSchedule (si existe ese modelo)
    public function orderSchedule()
    {
        return $this->belongsTo(OrderSchedule::class);
    }
}
