<?php

namespace App\Data\Models;

use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    protected $table = 'pagamento';

    protected $fillable = [
        'matricula',
        'matricula_sdv',

        'ano_referencia',
        'mes_referencia',
        'tipo_folha',
        'situacao_funcional',
        'tipo_cargo',

        'nome',
        'uadm',
        'cpf',
        'cargo',
        'funcao',
        'rend_func',
        'comissao',
        'represent',
        'incorporado',
        'trienio',
        'abono',
        'ferias',
        'redutor',
        'previdencia',
        'ir',
        'total_liquido',
    ];
}
