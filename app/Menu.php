<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model 
{
    protected $table = 'stp_menu';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'id_role',
        'id_menu',
        'status'
    ];

}
