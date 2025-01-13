<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class puzzles extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $table = 'puzzles';
    protected $fillable = [
        'IMGSource',
        'IMGDesc',
        'Xvalue',
        'Yvalue',
        'Difficulty',
    ];
}
