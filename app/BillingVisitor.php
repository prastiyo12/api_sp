<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillingVisitor extends Model 
{
    use \Awobaz\Compoships\Compoships;
    protected $table = 'trx_billing_visitor';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'billing_id',
        'buyerid',
        'visitor_name',
        'id_number',
        'email',
        'phone',
        'status'
    ];
    
    public function visitor_detail()
    {
        return $this->hasOne('App\BillingVisitor', 'buyerid', 'buyerid');
    }
    
    public function billing_detail()
    {
        return $this->belongsTo('App\BillingDetail', ['destid','billing_id'], ['destid','billing_id']);
    }
}
