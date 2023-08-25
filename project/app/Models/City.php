<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
	protected $table = 'cities';
    protected $fillable = ['city','state_id','status'];
	
    public function state()
    {
    	return $this->belongsTo('App\Models\State')->withDefault();
    }

}
