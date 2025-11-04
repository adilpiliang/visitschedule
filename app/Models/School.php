<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'kota',
        'kecamatan',
        'kelurahan',
        'address',
        'maps',
        'contact',
        'status',
        'pic',
    ];

    protected $casts = [
        'status' => 'string',
    ];
}
