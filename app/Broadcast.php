<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model 
{
    protected $table = 'trx_broadcast';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'id_type',
        'title',
        'content',
        'date_created',
        'icon',
        'status',
        'expire_date'
    ];

    public function details()
    {
    	return $this->hasMany('App\Broadcast', 'id', 'id');
    }

}
