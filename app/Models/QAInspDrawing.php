<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QAInspDrawing extends Model
{
    protected $table = 'qa_inspdrawings';

    protected $fillable = [
        'customer',
        'pn',
        'rev',
        'file_path',
        'img_width',
        'img_height',
    ];

    public function characteristics(): HasMany
    {
        // 👇 FORZAMOS la foreign key y local key
        return $this->hasMany(QAInspCharacteristic::class, 'drawing_id', 'id');
    }
}
