<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Cart extends Model 
{
    use \Awobaz\Compoships\Compoships;
    protected $table = 'trx_cart';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'chatid',
        'buyerid',
        'destid',
        'ticketdatefrom',
        'ticketdateto',
        'loc_qty_18above',
        'loc_qty_18below',
        'int_qty_18above',
        'int_qty_18below',
        'createdate',
        'status'
    ];

    public function details()
    {
        return $this->hasMany('App\CartDetail', ['buyerid','destid','ticketdatefrom'],['buyerid','destid','ticketdatefrom']);
    }
    
    public function destination()
    {
        return $this->hasMany('App\Destination', 'destid', 'destid');
    }
    
    public function destination_image()
    {
        return $this->hasMany('App\DestinationImage', 'destid', 'destid');
    }
}
