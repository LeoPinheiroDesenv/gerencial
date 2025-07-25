<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaIfood extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'nome', 'status', 'id_ifood'
    ];
}
