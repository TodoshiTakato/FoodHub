<?php

namespace App\Models\Traits;

use DateTimeInterface;
use DateTimeZone;

trait UsesUtcTimestamps
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->setTimezone(new DateTimeZone(timezone: 'UTC'))->format('Y-m-d H:i:s');
    }

    public function getTimezone(): string
    {
        return 'UTC';
    }
}
