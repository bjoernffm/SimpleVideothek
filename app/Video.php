<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    public function getFormattedLengthAttribute()
    {
        return $this->formatTime($this->length);
    }
    
    protected function formatTime($secondsParam)
    {
        $seconds = $secondsParam % 60;
        $minutes = ($secondsParam/60) % 60;
        $hours = ($secondsParam/3600) % 24;
        $days = ($secondsParam/86400) % 365;

        if ($days > 0) {
            return sprintf('%03d:%02d:%02d:%02d', $days, $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
