<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model 
{
    protected $table = 'trx_receipt';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'receipt_id',
        'billing_id',
        'bank_name',
        'bank_no',
        'bank_acc',
        'remark',
        'amount',
        'status',
        'createdate'
    ];
}