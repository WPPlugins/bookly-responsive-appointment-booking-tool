<?php

/**
 * Class AB_DateTime
 *
 * @info Rewritten for PHP 5.2 compatibility
 */
class AB_DateTime extends DateTime {

    /**
     * Returns the difference between two DateTime objects
     *
     * @param DateTime $secondDate
     * @param bool $absolute
     * @return AB_DateInterval|bool|DateInterval
     */
    public function diff ( $secondDate, $absolute = false ) {
        $firstDateTimeStamp = $this->format( 'U' );
        $secondDateTimeStamp = $secondDate->format( 'U' );
        $rv = $secondDateTimeStamp - $firstDateTimeStamp;
        $di = new AB_DateInterval( $rv );

        return $di;
    } // diff

} // AB_DateTime