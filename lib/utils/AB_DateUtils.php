<?php

    class AB_DateUtils {
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
         *
         * @return string
         */
        public static function getWeekDayByNumber( $number ) {
            return isset( self::$week_days[ $number ] ) ? self::$week_days[ $number ] : '';
        }

        /**
         * Get week days
         *
         * @return array
         */
        public static function getWeekDays() {
            return self::$week_days;
        }

        /**
         * Get timestamp for a given day number
         *
         * @param $day_number
         * @param $now
         *
         * @return bool|int
         */
        public static function getTimestampForDayOfWeek( $day_number, $now = null ) {
            if ( null === $now ) {
                $now = time();
            }

            $result = false;
            $current_day_number = date_i18n( 'w', $now );

            if ( $day_number == $current_day_number ) {
                $result = strtotime( 'today', $now );
            } else if ( isset( self::$week_days[ $day_number ] ) ) {
                $result = strtotime( self::$week_days[ $day_number ] . ' this week', $now );
            }
            return $result;
        }

        /**
         * @param $timestamp
         * @param $tz_offset
         * @return DateTime
         */
        public static function getDateBasedOnUserTimezoneOffset( $timestamp, $tz_offset ) {
            return new DateTime( '@' . ( $timestamp + ( $tz_offset * HOUR_IN_SECONDS ) ) );
        }
    }