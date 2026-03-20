<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $fillable = [
        'pregunta',
        'opcion_a',
        'opcion_b',
        'opcion_c',
        'opcion_d',
        'respuesta_correcta',
    ];
}
