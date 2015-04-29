<?php

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

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
    public function __construct( array $options = array() ) {
        // Handle widget options.
        $options = array_merge( array(
            'use_empty' => true,
            'empty_value' => null
        ), $options );

        // Insert empty value if required.
        if ( $options[ 'use_empty' ] ) {
            $this->values[ null ] = $options[ 'empty_value' ];
        }

        $tf         = get_option( 'time_format' );
        $ts_length  = get_option( 'ab_settings_time_slot_length' );
        $time_start = new AB_DateTime( AB_StaffScheduleItem::WORKING_START_TIME, new DateTimeZone( 'UTC' ) );
        $time_end   = new AB_DateTime( AB_StaffScheduleItem::WORKING_END_TIME, new DateTimeZone( 'UTC' ) );

        // Run the loop.
        while ( $time_start->format( 'U' ) <= $time_end->format( 'U' ) ) {
            $this->values[ $time_start->format( 'H:i:s' ) ] = $time_start->format( $tf );
            $time_start->modify( '+' . $ts_length . ' min' );
        }
        $this->values[ $time_end->format( 'H:i:s' ) ] = $time_end->format( $tf );
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
    public function render( $name, $value = null, array $attributes = array() ) {
        $options = array();
        $attributes_str = '';
        foreach ( $this->values as $option_value => $option_text ) {

            $selected = strval( $value ) == strval( $option_value );
            $options[ ] = sprintf(
                '<option value="%s"%s>%s</option>',
                $option_value,
                ($selected ? ' selected="selected"' : ''),
                $option_text
            );
        }
        foreach ( $attributes as $attr_name => $attr_value ) {
            $attributes_str .= sprintf( ' %s="%s"', $attr_name, $attr_value );
        }

        return sprintf( '<select name="%s"%s>%s</select>', $name, $attributes_str, implode( '', $options ) );
    }

    /**
     * @param $start
     * @param string $selected
     * @return array
     */
    public function renderOptions( $start, $selected = '' ) {
        $options = array();
        foreach ( $this->values as $option_value => $option_text ) {
            if ( $start && strval( $option_value ) < strval( $start ) ) continue;
            $options[ ] = sprintf(
                '<option value="%s"%s>%s</option>',
                $option_value,
                (strval( $selected ) == strval( $option_value ) ? 'selected="selected"' : ''),
                $option_text
            );
        }

        return $options;
    }
}