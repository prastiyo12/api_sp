<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model 
{
    protected $table = 'stp_dest';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'destid',
        'destname',
        'description',
        'loc_price_18above',
        'loc_price_18below',
        'int_price_18above',
        'int_price_18below',
        'loc_quota',
        'int_quota',
        'desttype',
        'status',
        'createdate',
        'updateid'
    ];

    public function prices()
    {
    	return $this->hasMany('App\DestinationPrice', 'destid', 'destid');
    }

    public function details()
    {
    	return $this->hasMany('App\DestinationDetail', 'destid', 'destid');
    }

    public function images()
    {
    	return $this->hasMany('App\DestinationImage', 'destid', 'destid');
    }
    
    public function review()
    {
    	return $this->hasMany('App\Review', 'destid', 'destid');
    }

}
