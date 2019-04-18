<?php

namespace App\Services\Importer;

use App\Data\Models\Pagamento as PagamentoModel;

class Pagamento
{
    protected $file;
    protected $fileName;

    private function generateMatriculaFile()
    {
        $txt = '';

        $this->file->each(function ($line) use (&$txt) {
            $txt .= $line['matricula_sdv'] . "\r\n";
        });

        file_put_contents("{$this->fileName}.matricula.txt", $txt);
    }

    public function import($file)
    {
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
            'ano_referencia' => 2019,
            'mes_referencia' => 03,

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

            'nome' => ($matricula = $this->readField($line, 17, 52)),
            'uadm' => $this->readField($line, 54, 59),
            'cpf' => $this->readField($line, 61, 71),
            'cargo' => $this->readField($line, 73, 97),
            'funcao' => $this->readField($line, 99, 128),

            'rend_func' => $this->toFloat($this->readField($line, 131, 142)),
            'comissao' => $this->toFloat($this->readField($line, 144, 158)),
            'represent' => $this->toFloat($this->readField($line, 160, 172)),
            'incorporado' => $this->toFloat($this->readField($line, 174, 189)),
            'trienio' => $this->toFloat($this->readField($line, 191, 205)),
            'abono' => $this->toFloat($this->readField($line, 207, 221)),
            'ferias' => $this->toFloat($this->readField($line, 223, 238)),
            'redutor' => $this->toFloat($this->readField($line, 240, 254)),
            'previdencia' => $this->toFloat($this->readField($line, 256, 268)),
            'ir' => $this->toFloat($this->readField($line, 270, 283)),
            'total_liquido' => $this->toFloat(
                $this->readField($line, 285, 300)
            ),
        ];
    }

    /**
     * @param $line
     * @return string
     */
    private function readMatricula($line): string
    {
        return $this->readField($line, 7, 15);
    }

    private function removeNulls($record)
    {
        return collect($record)->filter(function ($value) {
            return filled($value);
        });
    }

    private function store()
    {
        PagamentoModel::truncate();

        $this->file->each(function ($record) {
            PagamentoModel::create($this->removeNulls($record)->toArray());
        });
    }

    private function toFloat(string $string)
    {
        $value = floatval(str_replace(',', '.', str_replace('.', '', $string)));

        return $value !== 0.0 ? $value : null;
    }
}
