<?php

namespace App\Models\Machines;

use Illuminate\Database\Eloquent\Model;

class MachineImage extends Model
{
    protected $table = 'machines_images';

    protected $fillable = [
        'machine_machinary_id',
        'image_path',
    ];

    public function machine()
    {
        return $this->belongsTo(MachineMachinary::class, 'machine_machinary_id');
    }
}
