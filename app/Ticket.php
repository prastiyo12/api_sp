<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model 
{
    use \Awobaz\Compoships\Compoships;
    protected $table = 'trx_ticket';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'ticket_id',
        'receipt_id',
        'billing_id',
        'buyerid',
        'destid',
        'ticketdatefrom',
        'ticketdateto',
        'visitor_name',
        'id_number',
        'email',
        'phone',
        'status'
    ];
    
    public function billing_details()
    {
        return $this->belongsTo('App\BillingDetail', ['buyerid','billing_id','destid'],['buyerid','billing_id','destid']);
    }

}