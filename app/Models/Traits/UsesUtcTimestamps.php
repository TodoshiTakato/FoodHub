<?php

namespace App\Models\Traits;

use DateTimeInterface;

trait UsesUtcTimestamps
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    }

    public function getTimezone()
    {
        return 'UTC';
    }
}
