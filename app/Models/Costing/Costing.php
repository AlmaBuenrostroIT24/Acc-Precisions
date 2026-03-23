<?php

namespace App\Models\Costing;

use App\Models\OrderSchedule;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Costing extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'costings';

    protected $fillable = [
        'order_schedule_id',
        'status',
        'drawing_pdf_path',
        'quote_pdf_path',
        'type_material',
        'qty_material',
        'price_material',
        'total_material',
        'total_outsource',
        'total_time_order',
        'total_labor',
        'sale_price',
        'grandtotal_cost',
        'difference_cost',
        'percentage',
        'created_by',
        'updated_by',
        'deleted_by',
        'notes',
    ];

    protected $casts = [
        'qty_material' => 'decimal:2',
        'price_material' => 'decimal:2',
        'total_material' => 'decimal:2',
        'total_outsource' => 'decimal:2',
        'total_time_order' => 'decimal:2',
        'total_labor' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'grandtotal_cost' => 'decimal:2',
        'difference_cost' => 'decimal:2',
        'percentage' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    public function orderSchedule()
    {
        return $this->belongsTo(OrderSchedule::class, 'order_schedule_id');
    }

    public function operations()
    {
        return $this->hasMany(CostingOperation::class, 'costing_id');
    }

    public function logs()
    {
        return $this->hasMany(CostingLog::class, 'costing_id');
    }
}
