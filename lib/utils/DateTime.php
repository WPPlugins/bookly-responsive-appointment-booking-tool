<?php
namespace Bookly\Lib\Utils;

use Bookly\Lib;

/**
 * Class DateTime
 * @package Bookly\Lib\Utils
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

    private static $format_characters_day   = array( 'd', 'D', 'j', 'l', 'N', 'S', 'w', 'z' );
    private static $format_characters_month = array( 'F', 'm', 'M', 'n' );
    private static $format_characters_year  = array( 'o', 'Y', 'y' );

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
     * Apply time zone & time zone offset to the given ISO date and time
     * which is considered to be in WP time zone.
     *
     * @param         $iso_date_time
     * @param string  $time_zone
     * @param integer $offset Offset in minutes
     * @param string  $format Output format
     * @return false|string
     */
    public static function applyTimeZone( $iso_date_time, $time_zone, $offset, $format = 'Y-m-d H:i:s' )
    {
        $date = $iso_date_time;
        if ( $time_zone !== null ) {
            $date = date_format( date_timestamp_set( date_create( $time_zone ), date_create( $iso_date_time . ' ' . Lib\Config::getWPTimeZone() )->getTimestamp() ), $format );
        } elseif ( $offset !== null ) {
            $date = self::applyTimeZoneOffset( $iso_date_time, $offset, $format );
        }

        return $date;
    }

    /**
     * Apply time zone offset (in minutes) to the given ISO date and time
     * which is considered to be in WP time zone.
     *
     * @param $iso_date_time
     * @param integer $offset Offset in minutes
     * @param string $format  Output format
     * @return false|string
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
     * Convert number of seconds into string "[XX year] [XX month] [XX week] [XX day] [XX h] XX min".
     *
     * @param int $duration
     * @return string
     */
    public static function secondsToInterval( $duration )
    {
        $duration = (int) $duration;
        $month_in_seconds = 30 * DAY_IN_SECONDS;
        $years   = (int) ( $duration / YEAR_IN_SECONDS );
        $months  = (int) ( ( $duration % YEAR_IN_SECONDS ) / $month_in_seconds );
        $weeks   = (int) ( ( ( $duration % YEAR_IN_SECONDS ) % $month_in_seconds ) / WEEK_IN_SECONDS );
        $days    = (int) ( ( ( ( $duration % YEAR_IN_SECONDS ) % $month_in_seconds ) % WEEK_IN_SECONDS ) / DAY_IN_SECONDS );
        $hours   = (int) ( ( ( ( $duration % YEAR_IN_SECONDS ) % $month_in_seconds ) % DAY_IN_SECONDS ) / HOUR_IN_SECONDS );
        $minutes = (int) ( ( ( ( $duration % YEAR_IN_SECONDS ) % $month_in_seconds ) % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS );

        $parts = array();

        if ( $years > 0 ) {
            $parts[] = sprintf( _n( '%d year', '%d years', $years, 'bookly' ), $years );
        }
        if ( $months > 0 ) {
            $parts[] = sprintf( _n( '%d month', '%d months', $months, 'bookly' ), $months );
        }
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
     * Return formatted time interval
     *
     * @param string $start_time    like 08:00:00
     * @param string $end_time      like 18:45:00
     * @return string
     */
    public static function formatInterval( $start_time, $end_time )
    {
        return self::formatTime( self::timeToSeconds( $start_time ) ) . ' - ' . self::formatTime( self::timeToSeconds( $end_time ) );
    }

    /**
     * Guess timezone by offset in seconds.
     *
     * @param int $offset
     * @return string
     */
    public static function guessTimeZone( $offset )
    {
        // Fallback to offset.
        return sprintf(
            '%s%02d:%02d',
            $offset >= 0 ? '+' : '-',
            abs( $offset / HOUR_IN_SECONDS ),
            abs( $offset / MINUTE_IN_SECONDS ) % 60
        );
    }

    /**
     * Get date parts order according to current date format.
     *
     * @return array
     */
    public static function getDatePartsOrder()
    {
        $order       = array();
        $date_format = preg_replace( '/[^A-Za-z]/', '', get_option( 'date_format' ) );

        foreach ( str_split( $date_format ) as $character ) {
            switch ( true ) {
                case in_array( $character, self::$format_characters_day ):
                    $order[] = 'day';
                    break;
                case in_array( $character, self::$format_characters_month ):
                    $order[] = 'month';
                    break;
                case in_array( $character, self::$format_characters_year ):
                    $order[] = 'year';
                    break;
            }
        }

        $order = array_unique( $order );

        return count( $order ) == 3 ? $order : array( 'month', 'day', 'year' );
    }

    /**
     * @param string $date
     * @param string $format
     * @return bool
     */
    public static function validateDate( $date, $format = 'Y-m-d' )
    {
        $d = \DateTime::createFromFormat( $format, $date );

        return $d && $d->format( $format ) === $date;
    }

    /**
     * @param array $array
     * @return array
     */
    public static function dateRangeOptions( $array = array() )
    {
        return array_merge(
            array(
                'tomorrow'    => __( 'Tomorrow', 'bookly' ),
                'today'       => __( 'Today', 'bookly' ),
                'yesterday'   => __( 'Yesterday', 'bookly' ),
                'last_7'      => __( 'Last 7 days', 'bookly' ),
                'last_30'     => __( 'Last 30 days', 'bookly' ),
                'thisMonth'   => __( 'This month', 'bookly' ),
                'nextMonth'   => __( 'Next month', 'bookly' ),
                'customRange' => __( 'Custom range', 'bookly' ),
                'apply'       => __( 'Apply', 'bookly' ),
                'cancel'      => __( 'Cancel', 'bookly' ),
                'to'          => __( 'To', 'bookly' ),
                'from'        => __( 'From', 'bookly' ),
                'dateFormat'  => self::convertFormat( 'date', self::FORMAT_MOMENT_JS ),
                'firstDay'    => (int) get_option( 'start_of_week' ),
            ),
            $array
        );
    }

    /**
     * @param array $array
     * @return array
     */
    public static function datePickerOptions( $array = array() )
    {
        /** @var \WP_Locale $wp_locale */
        global $wp_locale;

        return array_merge(
            array(
                'dateFormat'      => self::convertFormat( 'date', self::FORMAT_JQUERY_DATEPICKER ),
                'monthNamesShort' => array_values( $wp_locale->month_abbrev ),
                'monthNames'      => array_values( $wp_locale->month ),
                'dayNamesMin'     => array_values( $wp_locale->weekday_abbrev ),
                'dayNamesShort'   => array_values( $wp_locale->weekday_abbrev ),
                'dayNames'        => array_values( $wp_locale->weekday ),
                'firstDay'        => (int) get_option( 'start_of_week' ),
            ),
            $array
        );
    }
}