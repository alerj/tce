<?php

namespace App\Services\Importer;

use App\Data\Models\Pessoal as PessoalModel;
use Illuminate\Support\Str;
use League\Csv\Reader;
use League\Csv\ResultSet;

class Pessoal
{
    protected $file;

    protected $fileName;

    protected $year;

    protected $month;

    private function cleanString($string)
    {
        return str_replace(["\r", "\n", "\t"], ['', '', '', ''], $string);
    }

    public function import($year, $month, $file)
    {
        $this->year = $year;

        $this->month = $month;

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
        $reader = Reader::createFromPath($file, 'r');
        $reader->setDelimiter(';');
        $reader->setHeaderOffset(0);

        $result = [];

        foreach ($reader->getRecords() as $record) {
            $result[] = collect($record)
                ->mapWithKeys(function ($record, $column) {
                    return [Str::lower($column) => $record];
                })
                ->toArray();
        }

        return collect($result)->map(function ($data) {
            $data['matricula_sdv'] = substr(
                remove_punctuation($data['matricula']),
                0,
                6
            );

            return $data;
        });
    }

    private function removeNulls($record)
    {
        return collect($record)->filter(function ($value) {
            return filled($value);
        });
    }

    private function store()
    {
        PessoalModel::where('ano_referencia', $this->year)
            ->where('mes_referencia', $this->month)
            ->delete();

        $this->file->each(function ($record) {
            PessoalModel::create($this->removeNulls($record)->toArray());
        });
    }
}
