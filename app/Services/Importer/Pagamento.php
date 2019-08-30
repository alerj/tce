<?php

namespace App\Services\Importer;

use Carbon\Carbon;
use GuzzleHttp\Client as Guzzle;
use App\Data\Models\Pessoal as PessoalModel;
use App\Data\Models\Pagamento as PagamentoModel;

class Pagamento
{
    const ORIGINAL_COLUMN = 7;

    const START_COLUMN = 3;

    const SPACE_SIZE = 1;

    protected $columns = [
        'datref' => 8,
        'matricula' => 11,
        'nome' => 36,
        'uadm' => 6,
        'cpf' => 11,
        'cargo' => 25,
        'funcao' => 28,
        'rend_func' => 15,
        'comissao' => 15,
        'represent' => 13,
        'incorporado' => 16,
        'trienio' => 15,
        'abono' => 15,
        'ferias' => 16,
        'redutor' => 15,
        'rubrica' => 13,
        'previdencia' => 14,
        'ir' => 14,
        'total_liquido' => 16
    ];

    protected $file;

    protected $fileName;

    protected $year;

    protected $month;

    protected $guzzle;

    public function __construct()
    {
        $this->instantiateGuzzle();
    }

    private function generateMatriculaFile()
    {
        $txt = '';

        $this->file->each(function ($line) use (&$txt) {
            $txt .= $line['matricula_sdv'] . "\r\n";
        });

        file_put_contents("{$this->fileName}.matricula.txt", $txt);
    }

    private function getFromPessoal($matricula)
    {
        $url = sprintf(
            'http://intrahom2008/SARH/eTCEWebApi/ConsultarFuncionarioPeriodo/values?matricula=%s&dataInicial=%s&dataFinal=%s',
            $this->matriculaWithoutDigit($matricula),
            Carbon::createFromDate($this->year, $this->month, 1),
            Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth()
        );

        $response = $this->guzzle->request('GET', $url, [
            'auth' => [
                config('app.webservice.username'),
                config('app.webservice.password'),
                'ntlm'
            ]
        ]);

        $body = json_decode((string) $response->getBody(), true);

        if (blank($body)) {
            throw new \Exception('GET error: ' . $url);
        }

        return $body[0];
    }

    public function import($year, $month, $file)
    {
        $this->year = $year;

        $this->month = $month;

        $this->readAll($file);

        $this->generateMatriculaFile();

        $this->store();

        // $this->storePessoal(); //// GET FROM WEBSERVICE - TEMPORARILY DEPRECATED ACCORDING TO EMAILS
    }

    private function instantiateGuzzle()
    {
        $this->guzzle = new Guzzle();
    }

    private function isMatricula($matricula)
    {
        return strlen($matricula) == 9 &&
            strlen(only_numbers($matricula)) === 7;
    }

    private function makeSituacaoFuncional(string $matricula)
    {
        switch ($matricula[0]) {
            case '2':
                return '01 - efetivos';
            case '3':
                return '12 - requisitados';
            case '4':
                return '03 - cargo comissionado extraquadro';
            case '5': // deputados
                return '01 - efetivos';
        }

        return null;
    }

    private function matriculaWithoutDigit($matricula)
    {
        return substr(only_numbers($matricula), 0, 6);
    }

    private function readAll($file)
    {
        $this->fileName = $file;

        $this->file = $this->readFile($file);
    }

    private function readField($line, $field)
    {
        $array = $this->readLineToArray($line, $field);

        return trim($array[$field]);
    }

    private function readFile($file)
    {
        return collect(file($file))
            ->filter(function ($line) {
                return $this->isMatricula($this->readMatricula($line));
            })
            ->map(function ($line) {
                return $this->readLine($line);
            });
    }

    private function readLine($line)
    {
        return [
            'ano_referencia' => $this->year,

            'mes_referencia' => $this->month,

            'tipo_folha' => '1',

            'situacao_funcional' => substr(
                $this->makeSituacaoFuncional(
                    $matricula = $this->readMatricula($line)
                ),
                0,
                2
            ),

            'tipo_cargo' => '07', // outros

            'matricula' => $matricula,

            'matricula_sdv' => substr(remove_punctuation($matricula), 0, 6),

            'nome' => $this->readField($line, 'nome'),

            'uadm' => $this->readField($line, 'uadm'),

            'cpf' => $this->readField($line, 'cpf'),

            'cargo' => $this->readField($line, 'cargo'),

            'funcao' => $this->readField($line, 'funcao'),

            'rend_func' => $this->toFloat($this->readField($line, 'rend_func')),

            'comissao' => $this->toFloat($this->readField($line, 'comissao')),

            'represent' => $this->toFloat($this->readField($line, 'represent')),

            'incorporado' => $this->toFloat(
                $this->readField($line, 'incorporado')
            ),

            'trienio' => $this->toFloat($this->readField($line, 'trienio')),

            'abono' => $this->toFloat($this->readField($line, 'abono')),

            'ferias' => $this->toFloat($this->readField($line, 'ferias')),

            'redutor' => $this->toFloat($this->readField($line, 'redutor')),

            'previdencia' => $this->toFloat(
                $this->readField($line, 'previdencia')
            ),

            'ir' => $this->toFloat($this->readField($line, 'ir')),

            'total_liquido' => $this->toFloat(
                $this->readField($line, 'total_liquido')
            )
        ];
    }

    private function readLineToArray($line, $field)
    {
        $line = substr($line, static::START_COLUMN - 1);

        $array = [];

        $position = 0;

        foreach ($this->columns as $key => $size) {
            $array[$key] = substr($line, $position, $size);

            $position += $size + static::SPACE_SIZE;
        }

        return $array;
    }

    /**
     * @param $line
     * @return string
     */
    private function readMatricula($line): string
    {
        if (
            $this->isMatricula(
                $matricula = $this->readField($line, 'matricula')
            )
        ) {
            dump($matricula);
        }

        return $matricula;
    }

    private function removeNulls($record)
    {
        return collect($record)->filter(function ($value) {
            return filled($value);
        });
    }

    private function store()
    {
        PagamentoModel::where('ano_referencia', $this->year)
            ->where('mes_referencia', $this->month)
            ->delete();

        $this->file->each(function ($record) {
            PagamentoModel::create($this->removeNulls($record)->toArray());
        });
    }

    private function storePessoal()
    {
        PessoalModel::where('ano_referencia', $this->year)
            ->where('mes_referencia', $this->month)
            ->delete();

        $this->file->each(function ($record) {
            $pessoa = $this->getFromPessoal($record['matricula']);

            $pessoa = collect([
                'matricula' => $pessoa['MATRICULA'],
                'cpf' => $pessoa['CPF'],
                'nome' => $pessoa['NOME'],
                'data_cessao' => $pessoa['DATA_CESSAO'],
                'data_admissao' => $pessoa['DATA_ADMISSAO'],
                'data_inatividade' => $pessoa['DATA_INATIVIDADE'],
                'orgao_cessao' => $pessoa['ORGAO_CESSAO'],
                //                'descricao' => $pessoa[''], /// tinha no arquivo anterior mas no webservice não tem mais
                //                'municipio_cessao' => $pessoa[''],
                //                'cedido_para' => $pessoa[''],
                'ano_referencia' => $pessoa['ANO_REFERENCIA'],
                'mes_referencia' => $pessoa['MES_REFERENCIA'],
                'tipo_folha' => $pessoa['MES_REFERENCIA'],
                'situacao_funcionar' => $pessoa['SITUACAO_FUNCIONAL'],
                'tipo_cargo' => $pessoa['TIPO_CARGO'],
                'cargo' => $pessoa['CARGO'],
                'funcao' => $pessoa['FUNCAO']
            ]);

            PessoalModel::create($this->removeNulls($pessoa)->toArray());

            dump('Pessoa: ' . $pessoa['matricula'] . ' - ' . $pessoa['nome']);
        });
    }

    private function toFloat(string $string)
    {
        $value = floatval(str_replace(',', '.', str_replace('.', '', $string)));

        return $value !== 0.0 ? $value : null;
    }

    public function shift($value)
    {
        return $value + (static::START_COLUMN - static::ORIGINAL_COLUMN);
    }
}
