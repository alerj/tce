<?php

namespace App\Services\Importer;

use App\Data\Models\Pagamento as PagamentoModel;

class Pagamento
{
    const ORIGINAL_COLUMN = 7;

    const START_COLUMN = 5;

    protected $file;

    protected $fileName;

    protected $year;

    protected $month;

    private function generateMatriculaFile()
    {
        $txt = '';

        $this->file->each(function ($line) use (&$txt) {
            $txt .= $line['matricula_sdv'] . "\r\n";
        });

        file_put_contents("{$this->fileName}.matricula.txt", $txt);
    }

    public function import($year, $month, $file)
    {
        $this->year = $year;

        $this->month = $month;

        $this->readAll($file);

        $this->generateMatriculaFile();

        $this->store();
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

    private function readAll($file)
    {
        $this->fileName = $file;

        $this->file = $this->readFile($file);
    }

    private function readField($line, int $start, int $end)
    {
        return trim(substr($line, $start - 1, $end - $start + 1));
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

            'nome' => ($matricula = $this->readField(
                $line,
                $this->shift(17),
                $this->shift(52)
            )),

            'uadm' => $this->readField(
                $line,
                $this->shift(54),
                $this->shift(59)
            ),

            'cpf' => $this->readField(
                $line,
                $this->shift(61),
                $this->shift(71)
            ),

            'cargo' => $this->readField(
                $line,
                $this->shift(73),
                $this->shift(97)
            ),

            'funcao' => $this->readField(
                $line,
                $this->shift(99),
                $this->shift(128)
            ),

            'rend_func' => $this->toFloat(
                $this->readField($line, $this->shift(131), $this->shift(142))
            ),

            'comissao' => $this->toFloat(
                $this->readField($line, $this->shift(144), $this->shift(158))
            ),

            'represent' => $this->toFloat(
                $this->readField($line, $this->shift(160), $this->shift(172))
            ),

            'incorporado' => $this->toFloat(
                $this->readField($line, $this->shift(174), $this->shift(189))
            ),

            'trienio' => $this->toFloat(
                $this->readField($line, $this->shift(191), $this->shift(205))
            ),

            'abono' => $this->toFloat(
                $this->readField($line, $this->shift(207), $this->shift(221))
            ),

            'ferias' => $this->toFloat(
                $this->readField($line, $this->shift(223), $this->shift(238))
            ),

            'redutor' => $this->toFloat(
                $this->readField($line, $this->shift(240), $this->shift(254))
            ),

            'previdencia' => $this->toFloat(
                $this->readField($line, $this->shift(256), $this->shift(268))
            ),

            'ir' => $this->toFloat(
                $this->readField($line, $this->shift(270), $this->shift(283))
            ),

            'total_liquido' => $this->toFloat(
                $this->readField($line, $this->shift(285), $this->shift(300))
            ),
        ];
    }

    /**
     * @param $line
     * @return string
     */
    private function readMatricula($line): string
    {
        dump($this->readField($line, $this->shift(7), $this->shift(15)));
        return $this->readField($line, $this->shift(7), $this->shift(15));
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
