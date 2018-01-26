<?php
namespace BooklyLite\Lib;

/**
 * Class ICS
 * @package BooklyLite\Lib
 */
class ICS
{
    protected $data;

    /**
     * Constructor.
     *
     * @param NotificationCodes $codes
     */
    function __construct( NotificationCodes $codes )
    {
        $this->data = sprintf(
            "BEGIN:VCALENDAR\n"
            . "VERSION:2.0\n"
            . "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\n"
            . "CALSCALE:GREGORIAN\n"
            . "BEGIN:VEVENT\n"
            . "DTSTART:%s\n"
            . "DTEND:%s\n"
            . "SUMMARY:%s\n"
            . "DESCRIPTION:%s\n"
            . "END:VEVENT\n"
            . "END:VCALENDAR",
            $this->_formatDateTime( $codes->appointment_start ),
            $this->_formatDateTime( $codes->appointment_end ),
            $this->_escape( $codes->service_name ),
            $this->_escape( sprintf( "%s\n%s", $codes->service_name, $codes->staff_name ) )
        );
    }

    /**
     * Create ICS file.
     *
     * @return bool|string
     */
    public function create()
    {
        $path = tempnam( sys_get_temp_dir(), 'Bookly_' );

        if ( $path ) {
            $info = pathinfo( $path );
            $new_path = sprintf( '%s%s%s.ics', $info['dirname'], DIRECTORY_SEPARATOR, $info['filename'] );
            if ( rename( $path, $new_path ) ) {
                $path = $new_path;
            } else {
                $new_path = sprintf( '%s%s%s.ics', $info['dirname'], DIRECTORY_SEPARATOR, $info['basename'] );
                if ( rename( $path, $new_path ) ) {
                    $path = $new_path;
                }
            }
            file_put_contents( $path, $this->data );

            return $path;
        }

        return false;
    }

    /**
     * Format date and time.
     *
     * @param string $datetime
     * @return string
     */
    private function _formatDateTime( $datetime )
    {
        $datetime = date_create( $datetime );

        return $datetime->format( 'Ymd\THis' );
    }

    /**
     * Escape string.
     *
     * @param string $input
     * @return string
     */
    private function _escape( $input )
    {
        $input = preg_replace( '/([\,;])/','\\\$1', $input );
        $input = str_replace( "\n", "\\n", $input );
        $input = str_replace( "\r", "\\r", $input );

        return $input;
    }
}