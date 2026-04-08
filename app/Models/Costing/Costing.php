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
        'qty_costing',
        'qty_material',
        'price_material',
        'total_material',
        'total_outsource',
        'total_time_order',
        'hrs_programming',
        'hrs_setup',
        'hrs_runtime',
        'hrs_runtimetotal',
        'hrs_actual',
        'hrs_variance',
        'total_labor',
        'sale_price',
        'price_pcs',
        'grandtotal_cost',
        'difference_cost',
        'percentage',
        'created_by',
        'updated_by',
        'deleted_by',
        'notes',
    ];

    protected $casts = [
        'qty_costing' => 'integer',
        'qty_material' => 'decimal:4',
        'price_material' => 'decimal:4',
        'total_material' => 'decimal:4',
        'total_outsource' => 'decimal:4',
        'total_time_order' => 'decimal:4',
        'hrs_programming' => 'decimal:4',
        'hrs_setup' => 'decimal:4',
        'hrs_runtime' => 'decimal:4',
        'hrs_runtimetotal' => 'decimal:4',
        'hrs_actual' => 'decimal:4',
        'hrs_variance' => 'decimal:4',
        'total_labor' => 'decimal:4',
        'sale_price' => 'decimal:4',
        'price_pcs' => 'decimal:4',
        'grandtotal_cost' => 'decimal:4',
        'difference_cost' => 'decimal:4',
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
