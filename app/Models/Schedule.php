<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'visit_date',
        'visit_time',
        'status',
        'notes',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
