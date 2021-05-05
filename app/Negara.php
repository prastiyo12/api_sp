<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Negara extends Model 
{
    protected $table = 'stp_country';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'country_code',
        'country_name'
    ];

}
