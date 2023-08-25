<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dukan extends Model
{
    protected $table = 'dukans';
    protected $fillable = ['shop_name','owner_name','pan_number','contact_number','address','latitude','longitude','gst_number','icon','category_id','status','language_id'];

    public function category()
    {
        return $this->belongsTo('App\Models\Category', 'category_id')->withDefault();
    }

    public function language()
    {
        return $this->belongsTo('App\Models\Language','language_id')->withDefault();
    }
}
