<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crop extends Model
{
    protected $table = 'crops';
    protected $fillable = ['name','slug','icon','language_id'];
}
