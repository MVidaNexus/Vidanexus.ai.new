<?php

namespace App\Helpers;

use Carbon\Carbon;

class ContentHelper
{
    /**
     * Convert a date/timestamp to a "Time Elapsed" string in Arabic.
     * e.g., "منذ ساعتين", "منذ 3 دقائق"
     * 
     * @param mixed $datetime
     * @param bool $full
     * @return string
     */
    public static function time_elapsed_string($datetime, $full = false)
    {
        Carbon::setLocale('ar');
        
        try {
            $now = Carbon::now();
            $ago = Carbon::parse($datetime);
            
            // If it's in the future or very recent
            if ($ago->gt($now)) {
                return 'الآن';
            }
            
            return $ago->diffForHumans();
        } catch (\Exception $e) {
            return $datetime;
        }
    }
}
