<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model 
{
    protected $table = 'trx_billing';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'billing_id',
        'buyerid',
        'first_name',
        'last_name',
        'company_name',
        'country',
        'address',
        'city',
        'postcode',
        'email',
        'phone',
        'total_pax',
        'total_cost'
    ];

    public function details()
    {
    	return $this->hasMany('App\BillingDetail', 'billing_id', 'billing_id');
    }

    public function visitors()
    {
    	return $this->hasMany('App\BillingVisitor', 'billing_id', 'billing_id');
    }

}
