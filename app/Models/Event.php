<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    protected $fillable = ['id', 'name', 'slug', 'start_at', 'end_at'];

}
