<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QaSamplingPlan extends Model
{
    use HasFactory;
    protected $table = 'qa_samplingplans';
    
    protected $fillable = [
        'min_qty',
        'max_qty',
        'normal_qty',
        'tightened_qty',
        'surface_qty',
        'is_percent',
    ];
}
