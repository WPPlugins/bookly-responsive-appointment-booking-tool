<?php
namespace Bookly\Backend\Components\Settings;

/**
 * Class Selects
 * @package Bookly\Backend\Components\Settings
 */
class Selects
{
    /**
     * Render multiple select (checkbox group).
     *
     * @param string $option_name
     * @param string $label
     * @param string $help
     * @param array  $options
     */
    public static function renderMultiple( $option_name, $label = null, $help = null, array $options = array() )
    {
        $values  = (array) get_option( $option_name );
        $control = '';
        foreach ( $options as $attr ) {
            $control .= strtr(
                '<div class="checkbox"><label><input type="checkbox" name="{name}[]" value="{value}"{checked} />{caption}</label></div>',
                array(
                    '{name}'    => $option_name,
                    '{value}'   => esc_attr( $attr[0] ),
                    '{checked}' => checked( in_array( $attr[0], $values ), true, false ),
                    '{caption}' => esc_html( $attr[1] ),
                )
            );
        }
        $control = "<div class=\"bookly-flags\" id=\"$option_name\">$control</div>";

        echo Inputs::buildControl( $option_name, $label, $help, $control );
    }

    /**
     * Render drop-down select.
     *
     * @param string $option_name
     * @param string $label
     * @param array  $options
     * @param string $help
     */
    public static function renderSingle( $option_name, $label = null, $help = null, array $options = array() )
    {
        if ( empty ( $options ) ) {
            $options = array(
                //  value        title              disabled
                array( 0, __( 'Disabled', 'bookly' ), 0 ),
                array( 1, __( 'Enabled', 'bookly' ),  0 ),
            );
        }

        $options_str = '';
        foreach ( $options as $attr ) {
            $options_str .= strtr(
                '<option value="{value}"{attr}>{caption}</option>',
                array(
                    '{value}'   => esc_attr( $attr[ 0 ] ),
                    '{attr}'    => empty ( $attr[ 2 ] )
                        ? selected( get_option( $option_name ), $attr[0], false )
                        : disabled( true, true, false ),
                    '{caption}' => esc_html( $attr[1] ),
                )
            );
        }

        $control = strtr(
            '<select id="{name}" class="form-control" name="{name}">{options}</select>',
            array(
                '{name}'    => $option_name,
                '{options}' => $options_str,
            )
        );

        echo Inputs::buildControl( $option_name, $label, $help, $control );
    }
}