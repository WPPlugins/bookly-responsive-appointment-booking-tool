<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AB_TimeChoiceWidget {
    /**
     * @var array
     */
    protected $values = array();

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct( array $options = array() )
    {
        // Handle widget options.
        $options = array_merge( array(
            'use_empty'   => true,
            'empty_value' => null,
            'type'        => 'from',
        ), $options );

        // Insert empty value if required.
        if ( $options['use_empty'] ) {
            $this->values[null] = $options['empty_value'];
        }

        $ts_length  = AB_Config::getTimeSlotLength();
        if ( isset( $options['bound'] ) ) {
            $time_start = AB_DateTimeUtils::timeToSeconds( $options['bound'][0] );
            $time_end   = AB_DateTimeUtils::timeToSeconds( $options['bound'][1] );
        } else {
            $time_start = AB_StaffScheduleItem::WORKING_START_TIME;
            $time_end   = AB_StaffScheduleItem::WORKING_END_TIME;
        }
        if ( $options['type'] == 'from' ) {
            $time_end -= $ts_length;    // Exclude last slot.
        } elseif ( $options['type'] == 'to' ) {
            $time_end *= 2;             // Create slots for 2 days.
        }

        // Run the loop.
        while ( $time_start <= $time_end ) {
            $this->values[ AB_DateTimeUtils::buildTimeString( $time_start ) ] = AB_DateTimeUtils::formatTime( $time_start );
            $time_start += $ts_length;
        }
    }

    /**
     * Render the widget.
     *
     * @param       $name
     * @param null  $value
     * @param array $attributes
     *
     * @return string
     */
    public function render( $name, $value = null, array $attributes = array() )
    {
        $options = '';
        $attributes_str = '';
        $exist_slot = false;
        foreach ( $this->values as $option_value => $option_text ) {
            if ( $exist_slot === false ) {
                if ( $value == $option_value ) {
                    $exist_slot = true;
                } elseif ( $value < $option_value ) {
                    $options .= sprintf(
                        '<option value="%s" selected="selected">%s</option>',
                        $value,
                        AB_DateTimeUtils::formatTime( $value )
                    );
                    $exist_slot = true;
                }
            }
            $options .= sprintf(
                '<option value="%s"%s>%s</option>',
                $option_value,
                selected( $value, $option_value, false ),
                $option_text
            );
        }
        foreach ( $attributes as $attr_name => $attr_value ) {
            $attributes_str .= sprintf( ' %s="%s"', $attr_name, $attr_value );
        }

        return sprintf( '<select name="%s" data-default_value="%s"%s>%s</select>', $name, $value, $attributes_str, $options );
    }

}