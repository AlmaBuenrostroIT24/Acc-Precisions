<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QAInspCharacteristic extends Model
{
    protected $table = 'qa_inspcharacteristics';

    protected $fillable = [
        'drawing_id',
        'char_no',
        'x',
        'y',
        'reference_location',
        'characteristic_designator',
        'requirement',
        'results',
        'tooling',
        'non_conformance_number',
        'comments',
    ];

    protected $casts = [
        'x'       => 'float',
        'y'       => 'float',
        'char_no' => 'int',
    ];

    public function drawing(): BelongsTo
    {
        // 👇 FORZAMOS también la foreign key aquí
        return $this->belongsTo(QAInspDrawing::class, 'drawing_id', 'id');
    }
}
