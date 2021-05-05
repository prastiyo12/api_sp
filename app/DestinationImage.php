<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DestinationImage extends Model 
{
    protected $table = 'stp_dest_dtl';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'destid',
        'destphoto',
        'status',
        'createdate',
        'updateid'
    ];

}
