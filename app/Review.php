<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Review extends Model 
{
    protected $table = 'trx_review';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'destid',
        'rate',
        'ulasan',
        'buyerid',
        'createdate'
    ];

    public function destination()
    {
    	return $this->belongsTo('App\Destination', 'destid', 'destid');
    }
}
