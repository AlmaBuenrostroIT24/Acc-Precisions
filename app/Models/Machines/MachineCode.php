<?php

namespace App\Models\Machines;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MachineCode extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'machines_codes';

    protected $fillable = [
        'code',
        'name',
        'location',
        'brand',
        'type_machine',
        'type_work',
        'status',
    ];

    protected $dates = ['deleted_at'];
}
