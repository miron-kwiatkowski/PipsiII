<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class users extends Authenticatable
{
    public $timestamps = false;
    use HasFactory;
    protected $table = 'users';
    protected $fillable = [
        'ID',
        'Email',
        'Name',
        'Password',
        'JoinDate',
        'PfpNum',
        'CurrentGame',
        'IsAdmin',
        'IsBanned',
        '_token',
        'Type',
    ];

    protected $hidden = [
        'Password',
        'remember_token',
    ];

    public function getAuthIdentifier()
    {
        return $this->Email;
    }

    public function getAuthEmail()
    {
        return $this->Email;
    }

    public function getAuthPassword()
    {
        //return Hash::make($this->Password);
        return $this->Password;
    }

}
