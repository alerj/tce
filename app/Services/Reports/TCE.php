<?php

namespace App\Services\Reports;

use App\Data\Models\Pagamento;
use App\Data\Models\Pessoal;

class TCE
{
    const FIELDS = [
        'cpf' => 'pagamento.cpf', // 'CPF do servidor'
        'matricula' => 'pagamento.matricula_sdv', // 'Matrícula do servidor'
        'nome' => 'pagamento.nome', // 'Nome do servidor'
        'data_cessao' => 'pessoal.data_cessao', // 'Data da cessão'
        'data_admissao' => 'pessoal.data_admissao', // 'Data da admissão'
        'data_inatividade' => 'pessoal.data_inatividade', // 'Data da inatividade'

        'ano_referencia' => 'pagamento.ano_referencia', // 'Ano de referência da folha de pagamento'
        'mes_referencia' => 'pagamento.mes_referencia', // 'Mês de referência da folha de pagamento'
        'tipo_folha' => 'pagamento.tipo_folha', // 'Tipo da folha'
        'situacao_funcional' => 'pagamento.situacao_funcional', // 'Situação funcional'
        'tipo_cargo' => 'pessoal.tipo_cargo', // 'Tipo do cargo'
        'cargo' => 'pessoal.cargo', // 'Nome do cargo'
        'orgao_cessao' => 'pessoal.orgao_cessao', // 'Órgão de cessão'
        'funcao' => 'pessoal.funcao' // 'Função gratificada'
    ];

    private function generate($year, $month)
    {
        return Pagamento::join(
            'pessoal',
            'pessoal.matricula_sdv',
            '=',
            'pagamento.matricula_sdv'
        )
            ->where('pagamento.ano_referencia', $year)
            ->where('pagamento.mes_referencia', $month)
            ->where('pessoal.ano_referencia', $year)
            ->where('pessoal.mes_referencia', $month)
            ->select(
                collect(static::FIELDS)
                    ->values()
                    ->toArray()
            )
            ->get()
            ->prepend(collect(static::FIELDS)->keys());
    }

    public function toCsv($year, $month, $file)
    {
        file_put_contents(
            $file,
            $this->generate($year, $month)
                ->map(function ($line) {
                    return collect($line->toArray())->implode(';');
                })
                ->implode("\r\n")
        );
    }

    public function listInactive($year, $month, $command)
    {
        Pessoal::where('pessoal.ano_referencia', $year)
            ->where('pessoal.mes_referencia', $month)
            ->whereNotNull('pessoal.data_inatividade')
            ->get()
            ->each(function ($person) use ($command) {
                $command->info(
                    "{$person['matricula']} - {$person['nome']} - {$person['data_inatividade']}"
                );
            });
    }
}
