<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Childcategory extends Model
{
    protected $fillable = ['subcategory_id','name','slug','language_id', 'icon'];
    public $timestamps = false;

    public function subcategory()
    {
    	return $this->belongsTo('App\Models\Subcategory')->withDefault();
    }

    public function products()
    {
        return $this->hasMany('App\Models\Product');
    }
    public function language()
    {
    	return $this->belongsTo('App\Models\Language','language_id')->withDefault();
    }  


    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = str_replace(' ', '-', $value);
    }

    public function attributes() {
        return $this->morphMany('App\Models\Attribute', 'attributable');
    }
}
