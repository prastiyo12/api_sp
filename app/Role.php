<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model 
{
    protected $table = 'rb_users_role';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'role_menu'
    ];

}
