<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model 
{
    protected $table = 'trx_coupon';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'couponcode',
        'description',
        'disc',
        'type',
        'createdate',
        'updateid',
        'status'
    ];
}