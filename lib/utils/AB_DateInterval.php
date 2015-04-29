<?php

/**
 * Class AB_DateInterval
 *
 * @info Rewritten for PHP 5.2 compatibility
 */
class AB_DateInterval {
    /**
     * @var int
     */
    public $y = 0;
    /**
     * @var int
     */
    public $m = 0;
    /**
     * @var int
     */
    public $d = 0;
    /**
     * @var int
     */
    public $h = 0;
    /**
     * @var int
     */
    public $i = 0;
    /**
     * @var int
     */
    public $s = 0;

    /**
     * Constructor.
     *
     * @param $time_to_convert
     */
    public function __construct ( $time_to_convert /* in seconds */ ) {
        $FULL_YEAR = 60*60*24*365.25;
        $FULL_MONTH = 60*60*24*(365.25/12);
        $FULL_DAY = 60*60*24;
        $FULL_HOUR = 60*60;
        $FULL_MINUTE = 60;
        $FULL_SECOND = 1;

        $seconds = 0;
        $minutes = 0;
        $hours = 0;
        $days = 0;
        $months = 0;
        $years = 0;

        while ( $time_to_convert >= $FULL_YEAR ) {
            $years ++;
            $time_to_convert = $time_to_convert - $FULL_YEAR;
        }

        while ( $time_to_convert >= $FULL_MONTH ) {
            $months ++;
            $time_to_convert = $time_to_convert - $FULL_MONTH;
        }

        while ( $time_to_convert >= $FULL_DAY ) {
            $days ++;
            $time_to_convert = $time_to_convert - $FULL_DAY;
        }

        while ( $time_to_convert >= $FULL_HOUR ) {
            $hours++;
            $time_to_convert = $time_to_convert - $FULL_HOUR;
        }

        while ( $time_to_convert >= $FULL_MINUTE ) {
            $minutes++;
            $time_to_convert = $time_to_convert - $FULL_MINUTE;
        }

        $seconds = $time_to_convert; // remaining seconds
        $this->y = $years;
        $this->m = $months;
        $this->d = $days;
        $this->h = $hours;
        $this->i = $minutes;
        $this->s = $seconds;
    } // __construct

} // AB_DateInterval