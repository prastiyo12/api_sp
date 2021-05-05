<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillingDetail extends Model 
{
    use \Awobaz\Compoships\Compoships;
    protected $table = 'trx_billing_dtl';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'billing_id',
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
    
    public function destination()
    {
    	return $this->hasOne('App\Destination', 'destid', 'destid');
    }
    
    public function dest()
    {
    	return $this->hasMany('App\Destination', 'destid', 'destid');
    }
    
    public function destination_price()
    {
    	return $this->hasMany('App\DestinationPrice', 'destid', 'destid');
    }
    
     public function destination_image()
    {
        return $this->hasMany('App\DestinationImage', 'destid', 'destid');
    }

    public function destination_detail()
    {
    	return $this->hasMany('App\DestinationDetail', 'destid', 'destid');
    }
    
    public function billing()
    {
    	return $this->belongsTo('App\Billing', 'billing_id', 'billing_id');
    }
    
    public function visitor()
    {
        return $this->hasMany('App\BillingVisitor', ['destid','billing_id','ticketdatefrom'], ['destid','billing_id','ticketdatefrom']);
    }
    
    public function ticket()
    {
    	return $this->hasMany('App\Ticket', ['buyerid','billing_id','destid'],['buyerid','billing_id','destid']);
    }
    
    public function review()
    {
    	return $this->hasOne('App\Review', 'billing_id','billing_id');
    }
    
   
}
