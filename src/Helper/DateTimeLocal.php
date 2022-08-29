<?php

namespace App\Helper;

use DateTime;
use DateTimeZone;

class DateTimeLocal extends DateTime
{
    public const DEFAULT_TIMEZONE = 'GMT-03:00';

    public function __construct(string $datetime = 'now', ?DateTimeZone $timezone = null)
    {
        parent::__construct($datetime, $timezone ?? new DateTimeZone(self::DEFAULT_TIMEZONE));
    }
}