<?php

namespace App\Models\Costing;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostingLog extends Model
{
    use HasFactory;

    protected $table = 'costing_logs';

    public const UPDATED_AT = null;

    protected $fillable = [
        'costing_id',
        'costing_operation_id',
        'action',
        'field_changed',
        'old_value',
        'new_value',
        'description',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function costing()
    {
        return $this->belongsTo(Costing::class, 'costing_id');
    }

    public function costingOperation()
    {
        return $this->belongsTo(CostingOperation::class, 'costing_operation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
