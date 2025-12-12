<?php

namespace App\Models\Machines;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineCode extends Model
{
    use HasFactory;

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

    public function machineries()
    {
        return $this->hasMany(MachineMachinary::class, 'machine_code_id');
    }
}
