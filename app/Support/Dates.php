<?php

namespace App\Support;

use Jenssegers\Date\Date;

trait Dates
{
    public function created_at_formatted()
    {
        return $this->formatDate($this->wrappedObject->created_at);
    }

    private function formatDate($date)
    {
        if (!$date) {
            return '';
        }

        Date::setLocale('pt_BR');

        return Date::parse($date)->format($this->getDateFormat());
    }

    private function getDateFormat()
    {
        return 'j F Y - H\hi';
    }

    public function updated_at_formatted()
    {
        return $this->wrappedObject->created_at ==
            $this->wrappedObject->updated_at
            ? ''
            : $this->formatDate($this->wrappedObject->updated_at);
    }
}
