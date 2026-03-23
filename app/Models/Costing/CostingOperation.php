<?php

namespace App\Models\Costing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CostingOperation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'costing_operations';

    protected $fillable = [
        'costing_id',
        'status',
        'name_operation',
        'resource_name',
        'time_programming',
        'time_setup',
        'runtime_pcs',
        'runtime_total',
        'total_time_operation',
        'labor_rate',
        'operation_cost',
        'created_by',
        'updated_by',
        'deleted_by',
        'notes',
    ];

    protected $casts = [
        'status' => 'string',
        'time_programming' => 'decimal:2',
        'time_setup' => 'decimal:2',
        'runtime_pcs' => 'decimal:4',
        'runtime_total' => 'decimal:2',
        'total_time_operation' => 'decimal:2',
        'labor_rate' => 'decimal:2',
        'operation_cost' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    public function costing()
    {
        return $this->belongsTo(Costing::class, 'costing_id');
    }

    public function logs()
    {
        return $this->hasMany(CostingLog::class, 'costing_operation_id');
    }
}
