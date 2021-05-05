<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model 
{
    protected $table = 'stp_gallery';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'path',
        'description',
        'type',
        'createdate',
        'updateid',
        'status'
    ];

}
