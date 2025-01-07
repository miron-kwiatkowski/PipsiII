<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class gamesettings extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $table = 'gamesettings';
    protected $fillable = ['TimeReset','MinDistance','MaxDistance','PointsToQualify','LeaderboardDays'];
}
