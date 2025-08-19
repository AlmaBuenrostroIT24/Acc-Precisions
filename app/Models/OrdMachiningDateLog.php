<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdMachiningDateLog extends Model
{
    use HasFactory;

    protected $table = 'ord_machiningdatelogs';

    public $timestamps = true;

    protected $fillable = [
        'order_schedule_id',
        'previous_date',
        'new_date',
        'changed_at',
        'changed_by',
    ];
    

    public function order()
    {
        return $this->belongsTo(OrderSchedule::class, 'order_schedule_id');
    }
}
