<?php

namespace App\Data\Models;

use Illuminate\Database\Eloquent\Model;

class Pessoal extends Model
{
    protected $table = 'pessoal';

    protected $fillable = [
        'matricula',
        'cpf',
        'nome',
        'data_cessao',
        'data_admissao',
        'data_inatividade',
        'orgao_cessao',
        'municipio_cessao',
        'cedido_para',
    ];
}
