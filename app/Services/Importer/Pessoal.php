<?php

namespace App\Services\Importer;

use App\Data\Models\Pessoal as PessoalModel;

class Pessoal
{
    protected $file;
    protected $fileName;

    public function import($file)
    {
        $this->readAll($file);

        $this->store();
    }

    private function readAll($file)
    {
        $this->fileName = $file;

        $this->file = $this->readFile($file);
    }

    private function readFile($file)
    {
        return collect(explode("\r", file_get_contents($file)))
            ->map(function ($line) {
                return $this->readLine($line);
            })
            ->slice(1);
    }

    private function readLine($line)
    {
        $line = explode(';', $line);

        return [
            'matricula' => $line[0],
            'cpf' => $line[1],
            'nome' => $line[2],
            'data_cessao' => $line[3],
            'data_admissao' => $line[4],
            'data_inatividade' => $line[5],
            // 'descricao' => $line[6],
            'orgao_cessao' => $line[7],
            'municipio_cessao' => $line[8],
            'cedido_para' => $line[9],
        ];
    }

    private function removeNulls($record)
    {
        return collect($record)->filter(function ($value) {
            return filled($value);
        });
    }

    private function store()
    {
        PessoalModel::truncate();

        $this->file->each(function ($record) {
            PessoalModel::create($this->removeNulls($record)->toArray());
        });
    }
}
