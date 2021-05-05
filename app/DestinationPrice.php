<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DestinationPrice extends Model 
{
    protected $table = 'stp_dest_price';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'destid',
        'price_type',
        'loc_price_18above',
        'loc_price_18below',
        'int_price_18above',
        'int_price_18below',
        'createdate',
        'updateid'
    ];
}
