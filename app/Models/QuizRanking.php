<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizRanking extends Model
{
    protected $fillable = [
        'nombre_jugador',
        'puntos',
        'total_preguntas',
        'porcentaje',
        'tiempo',
    ];
}
