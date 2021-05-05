<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DestinationType extends Model 
{
    protected $table = 'stp_dest_type';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'desttype'
    ];

    public function destination()
    {
    	return $this->hasMany('App\Destination', 'desttype', 'id');
    }

}
