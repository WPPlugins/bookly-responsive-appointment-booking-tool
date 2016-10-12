<?php
namespace BooklyLite\Backend\Modules\Settings\Forms;

use BooklyLite\Lib;

/**
 * Class BusinessHours
 * @package BooklyLite\Backend\Modules\Settings\Forms
 */
class BusinessHours extends Lib\Base\Form
{
    public function __construct()
    {
        $this->setFields( array(
            'ab_settings_monday_start',
            'ab_settings_monday_end',
            'ab_settings_tuesday_start',
            'ab_settings_tuesday_end',
            'ab_settings_wednesday_start',
            'ab_settings_wednesday_end',
            'ab_settings_thursday_start',
            'ab_settings_thursday_end',
            'ab_settings_friday_start',
            'ab_settings_friday_end',
            'ab_settings_saturday_start',
            'ab_settings_saturday_end',
            'ab_settings_sunday_start',
            'ab_settings_sunday_end',
        ) );
    }

    public function save()
    {
        foreach ( $this->data as $field => $value ) {
            update_option( $field, $value );
        }
    }

    /**
     * @param string $field_name
     * @param bool $is_start
     * @return string
     */
    public function renderField( $field_name = 'ab_settings_monday', $is_start = true )
    {
        $ts_length      = Lib\Config::getTimeSlotLength();
        $time_output    = Lib\Entities\StaffScheduleItem::WORKING_START_TIME;
        $time_end       = Lib\Entities\StaffScheduleItem::WORKING_END_TIME;
        $option_name    = $field_name . ( $is_start ? '_start' : '_end' );
        $class_name     = $is_start ? 'select_start' : 'select_end hide-on-non-working-day';
        $selected_value = get_option( $option_name );
        $selected_seconds = Lib\Utils\DateTime::timeToSeconds( $selected_value );
        $output         = "<select style='display:inline-block' class='form-control ab-auto-w {$class_name}' name={$option_name}>";

        if ( $is_start ) {
            $output .= '<option value="">' . __( 'OFF', 'bookly' ) . '</option>';
            $time_end -= $ts_length;
        }
        $value_added = false;
        while ( $time_output <= $time_end ) {
            if ( $value_added === false ) {
                if ( $selected_seconds == $time_output ) {
                    $value_added = true;
                } elseif ( $selected_seconds < $time_output ) {
                    $output  .= "<option value='{$selected_value}' selected='selected'>{$selected_value}</option>";
                    $value_added = true;
                }
            }

            $value    = Lib\Utils\DateTime::buildTimeString( $time_output, false );
            $op_name  = Lib\Utils\DateTime::formatTime( $time_output );
            $output  .= "<option value='{$value}'" . selected( $value, $selected_value, false ) . ">{$op_name}</option>";
            $time_output += $ts_length;
        }

        $output .= '</select>';

        return $output;
    }

}