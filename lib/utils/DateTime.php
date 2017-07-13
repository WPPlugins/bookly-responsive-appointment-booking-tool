<?php
namespace BooklyLite\Lib\Utils;

/**
 * Class DateTime
 * @package BooklyLite\Lib\Utils
 */
class DateTime
{
    const FORMAT_MOMENT_JS         = 1;
    const FORMAT_JQUERY_DATEPICKER = 2;
    const FORMAT_PICKADATE         = 3;

    private static $week_days = array(
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    );

    /**
     * Get week day by day number (0 = Sunday, 1 = Monday...)
     *
     * @param $number
     * @return string
     */
    public static function getWeekDayByNumber( $number )
    {
        return isset( self::$week_days[ $number ] ) ? self::$week_days[ $number ] : '';
    }

    /**
     * Format ISO date (or seconds) according to WP date format setting.
     *
     * @param string|integer $iso_date
     * @return string
     */
    public static function formatDate( $iso_date )
    {
        return date_i18n( get_option( 'date_format' ), is_numeric( $iso_date ) ? $iso_date : strtotime( $iso_date, current_time( 'timestamp' ) ) );
    }

    /**
     * Format ISO time (or seconds) according to WP time format setting.
     *
     * @param string|integer $iso_time
     * @return string
     */
    public static function formatTime( $iso_time )
    {
        return date_i18n( get_option( 'time_format' ), is_numeric( $iso_time ) ? $iso_time : strtotime( $iso_time, current_time( 'timestamp' ) ) );
    }

    /**
     * Format ISO datetime according to WP date and time format settings.
     *
     * @param string $iso_date_time
     * @return string
     */
    public static function formatDateTime( $iso_date_time )
    {
        return self::formatDate( $iso_date_time ) . ' ' . self::formatTime( $iso_date_time );
    }

    /**
     * Apply time zone offset (in minutes) to the given ISO date and time
     * which is considered to be in WP time zone.
     *
     * @param $iso_date_time
     * @param integer $offset Offset in minutes
     * @param string $format  Output format
     * @return bool|string
     */
    public static function applyTimeZoneOffset( $iso_date_time, $offset, $format = 'Y-m-d H:i:s' )
    {
        $client_diff = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS + $offset * 60;

        return date( $format, strtotime( $iso_date_time ) - $client_diff );
    }

    /**
     * From UTC0 datetime to WP timezone time
     *
     * @param string $iso_date_time  UTC0 time
     * @param string $format  Output format
     * @return string
     */
    public static function UTCToWPTimeZone( $iso_date_time, $format = 'Y-m-d H:i:s' )
    {
        return date( $format, strtotime( $iso_date_time ) + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
    }

    /**
     * Convert WordPress date and time format into requested JS format.
     *
     * @param string $source_format
     * @param int    $to
     * @return string
     */
    public static function convertFormat( $source_format, $to )
    {
        switch ( $source_format ) {
            case 'date':
                $php_format = get_option( 'date_format', 'Y-m-d' );
                break;
            case 'time':
                $php_format = get_option( 'time_format', 'H:i' );
                break;
            default:
                $php_format = $source_format;
        }

        switch ( $to ) {
            case self::FORMAT_MOMENT_JS:
                $replacements = array(
                    'd' => 'DD',   '\d' => '[d]',
                    'D' => 'ddd',  '\D' => '[D]',
                    'j' => 'D',    '\j' => 'j',
                    'l' => 'dddd', '\l' => 'l',
                    'N' => 'E',    '\N' => 'N',
                    'S' => 'o',    '\S' => '[S]',
                    'w' => 'e',    '\w' => '[w]',
                    'z' => 'DDD',  '\z' => '[z]',
                    'W' => 'W',    '\W' => '[W]',
                    'F' => 'MMMM', '\F' => 'F',
                    'm' => 'MM',   '\m' => '[m]',
                    'M' => 'MMM',  '\M' => '[M]',
                    'n' => 'M',    '\n' => 'n',
                    't' => '',     '\t' => 't',
                    'L' => '',     '\L' => 'L',
                    'o' => 'YYYY', '\o' => 'o',
                    'Y' => 'YYYY', '\Y' => 'Y',
                    'y' => 'YY',   '\y' => 'y',
                    'a' => 'a',    '\a' => '[a]',
                    'A' => 'A',    '\A' => '[A]',
                    'B' => '',     '\B' => 'B',
                    'g' => 'h',    '\g' => 'g',
                    'G' => 'H',    '\G' => 'G',
                    'h' => 'hh',   '\h' => '[h]',
                    'H' => 'HH',   '\H' => '[H]',
                    'i' => 'mm',   '\i' => 'i',
                    's' => 'ss',   '\s' => '[s]',
                    'u' => 'SSS',  '\u' => 'u',
                    'e' => 'zz',   '\e' => '[e]',
                    'I' => '',     '\I' => 'I',
                    'O' => '',     '\O' => 'O',
                    'P' => '',     '\P' => 'P',
                    'T' => '',     '\T' => 'T',
                    'Z' => '',     '\Z' => '[Z]',
                    'c' => '',     '\c' => 'c',
                    'r' => '',     '\r' => 'r',
                    'U' => 'X',    '\U' => 'U',
                    '\\' => '',
                );
                return strtr( $php_format, $replacements );

            case self::FORMAT_JQUERY_DATEPICKER:
                $replacements = array(
                    // Day
                    'd' => 'dd', '\d' => '\'d\'',
                    'j' => 'd',  '\j' => 'j',
                    'l' => 'DD', '\l' => 'l',
                    'D' => 'D',  '\D' => '\'D\'',
                    'z' => 'o',  '\z' => 'z',
                    // Month
                    'm' => 'mm', '\m' => '\'m\'',
                    'n' => 'm',  '\n' => 'n',
                    'F' => 'MM', '\F' => 'F',
                    // Year
                    'Y' => 'yy', '\Y' => 'Y',
                    'y' => 'y',  '\y' => '\'y\'',
                    // Others
                    'S' => '',   '\S' => 'S',
                    'o' => 'yy', '\o' => '\'o\'',
                    '\\' => '',
                );
                return str_replace( '\'\'', '', strtr( $php_format, $replacements ) );

            case self::FORMAT_PICKADATE:
                $replacements = array(
                    // Day
                    'd' => 'dd',   '\d' => '!d',
                    'D' => 'ddd',  '\D' => 'D',
                    'l' => 'dddd', '\l' => 'l',
                    'j' => 'd',    '\j' => 'j',
                    // Month
                    'm' => 'mm',   '\m' => '!m',
                    'M' => 'mmm',  '\M' => 'M',
                    'F' => 'mmmm', '\F' => 'F',
                    'n' => 'm',    '\n' => 'n',
                    // Year
                    'y' => 'yy',   '\y' => 'y',
                    'Y' => 'yyyy', '\Y' => 'Y',
                    // Others
                    'S' => '',     '\S' => 'S',
                    '\\' => '',
                );
                return strtr( $php_format, $replacements );
        }

        return $php_format;
    }

    public static function buildTimeString( $seconds, $show_seconds = true )
    {
        $hours    = (int) ( $seconds / 3600 );
        $seconds -= $hours * 3600;
        $minutes  = (int) ( $seconds / 60 );
        $seconds -= $minutes * 60;

        return $show_seconds
            ? sprintf( '%02d:%02d:%02d', $hours, $minutes, $seconds )
            : sprintf( '%02d:%02d', $hours, $minutes );
    }

    /**
     * Convert time in format H:i:s to seconds.
     *
     * @param $str
     * @return int
     */
    public static function timeToSeconds( $str )
    {
        $result = 0;
        $seconds = 3600;

        foreach ( explode( ':', $str ) as $part ) {
            $result += (int)$part * $seconds;
            $seconds /= 60;
        }

        return $result;
    }

    /**
     * Convert number of seconds into string "[XX week] [XX day] [XX h] XX min".
     *
     * @param int $duration
     * @return string
     */
    public static function secondsToInterval( $duration )
    {
        $weeks   = (int) ( $duration / WEEK_IN_SECONDS );
        $days    = (int) ( ( $duration % WEEK_IN_SECONDS ) / DAY_IN_SECONDS );
        $hours   = (int) ( ( $duration % DAY_IN_SECONDS ) / HOUR_IN_SECONDS );
        $minutes = (int) ( ( $duration % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS );

        $parts = array();

        if ( $weeks > 0 ) {
            $parts[] = sprintf( _n( '%d week', '%d weeks', $weeks, 'bookly' ), $weeks );
        }
        if ( $days > 0 ) {
            $parts[] = sprintf( _n( '%d day', '%d days', $days, 'bookly' ), $days );
        }
        if ( $hours > 0 ) {
            $parts[] = sprintf( __( '%d h', 'bookly' ), $hours );
        }
        if ( $minutes > 0 ) {
            $parts[] = sprintf( __( '%d min', 'bookly' ), $minutes );
        }

        return implode( ' ', $parts );
    }

    /**
     * Return formated time interval
     *
     * @param string $start_time    like 08:00:00
     * @param string $end_time      like 18:45:00
     * @return string
     */
    public static function formatInterval( $start_time, $end_time )
    {
        return self::formatTime( self::timeToSeconds( $start_time ) ) . ' - ' . self::formatTime( self::timeToSeconds( $end_time ) );
    }

}