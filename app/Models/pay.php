<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pay extends Model
{
    use HasFactory;
    public $table = 'pays';
    protected $fillable = [
        'id_user',
        'price',
        'spending',
        'status',
        'img',
    ];
    public $timestamps = false;
}
