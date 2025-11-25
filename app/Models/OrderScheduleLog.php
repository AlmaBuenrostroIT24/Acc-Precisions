<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class OrderScheduleLog extends Model
{

    protected $table = 'orders_schedule_logs'; // 👈 muy importante

    protected $fillable = [
        'order_id',
        'user_id',
        'field',
        'old_value',
        'new_value'
    ];

      public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
