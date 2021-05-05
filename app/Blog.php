<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model 
{
    protected $table = 'trx_blog';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'blogid',
        'description',
        'tag',
        'type',
        'path_cover',
        'createdate',
        'updateid',
        'status'
    ];

    public function details()
    {
    	return $this->hasMany('App\Broadcast', 'id', 'id');
    }

}
