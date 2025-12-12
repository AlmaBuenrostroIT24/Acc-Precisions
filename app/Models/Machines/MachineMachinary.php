<?php

namespace App\Models\Machines;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineMachinary extends Model
{
    use HasFactory;

    protected $table = 'machines_machinary';

    protected $fillable = [
        'machine_code_id',
        'model',
        'serial',
        'control_system',
        'made',
        'year',
        'machine_area',
        'massof_machine',
        'servo_batteries',
        'kw',
        'voltage',
        'tool_type',
        'tool_qty',
        'description',
    ];

    // 👇 Relacion con Code
    public function machineCode()
    {
        // Asegúrate de que MachineCode tiene este namespace
        return $this->belongsTo(MachineCode::class, 'machine_code_id');
    }

    // 👇 Relacion con Images
    public function images()
    {
        return $this->hasMany(MachineImage::class, 'machine_machinary_id');
    }
}
