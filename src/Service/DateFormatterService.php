<?php

namespace App\Service;

use DateTime;
use IntlDateFormatter;

class DateFormatterService
{
    public function formatInFrench($dateString)
    {
        $locale = 'fr_FR';
        $date = new DateTime($dateString);
        $dateFormatter = new IntlDateFormatter($locale, IntlDateFormatter::LONG, IntlDateFormatter::NONE);
        return $dateFormatter->format($date);
    }
}
