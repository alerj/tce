<?php

namespace App\Services\Reports;

use App\Data\Models\Pagamento;

class TCE
{
    const FIELDS = [
        'cpf' => 'pagamento.cpf', // 'CPF do servidor'
        'matricula' => 'matricula_sdv', // 'Matrícula do servidor'
        'nome' => 'pagamento.nome', // 'Nome do servidor'
        'data_cessao' => 'data_cessao', // 'Data da cessão'
        'data_admissao' => 'data_admissao', // 'Data da admissão'
        'data_inatividade' => 'data_inatividade', // 'Data da inatividade'

        'ano_referencia' => 'ano_referencia', // 'Ano de referência da folha de pagamento'
        'mes_referencia' => 'mes_referencia', // 'Mês de referência da folha de pagamento'
        'tipo_folha' => 'tipo_folha', // 'Tipo da folha'
        'situacao_funcional' => 'situacao_funcional', // 'Situação funcional'
        'tipo_cargo' => 'tipo_cargo', // 'Tipo do cargo'
        'cargo' => 'cargo', // 'Nome do cargo'
        'orgao_cessao' => 'orgao_cessao', // 'Órgão de cessão'
        'funcao' => 'funcao', // 'Função gratificada'
    ];

    private function generate()
    {
        return Pagamento::join(
            'pessoal',
            'pessoal.matricula',
            '=',
            'pagamento.matricula_sdv'
        )
            ->select(
                collect(static::FIELDS)
                    ->values()
                    ->toArray()
            )
            ->get()
            ->prepend(collect(static::FIELDS)->keys());
    }

    public function toCsv($file)
    {
        file_put_contents(
            $file,
            $this->generate()
                ->map(function ($line) {
                    return collect($line->toArray())->implode(';');
                })
                ->implode("\r\n")
        );
    }
}
