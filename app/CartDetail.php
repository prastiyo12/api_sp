<?php

namespace App;

use Illuminate\Database\Eloquent\Model;



class CartDetail extends Model 
{
    use \Awobaz\Compoships\Compoships;
    protected $table = 'trx_cart_dtl';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'buyerid',
        'destid',
        'visitor_name',
        'id_number',
        'email',
        'phone',
        'status'
    ];
    
    public function cart()
    {
        return $this->belongsTo('App\Cart', ['buyerid','destid','ticketdatefrom'],['buyerid','destid','ticketdatefrom']);
    }
}