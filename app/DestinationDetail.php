<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DestinationDetail extends Model 
{
    protected $table = 'stp_dest';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'destid',
        'dest_photo',
        'status'
    ];

    public function prices()
    {
    	return $this->hasMany('App\DestinatonPrice', 'destid');
    }

    public function details()
    {
    	return $this->hasMany('App\DestinatonDetail', 'destid');
    }

}
