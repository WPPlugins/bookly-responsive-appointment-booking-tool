<?php
namespace Bookly\Backend\Components\Settings;

/**
 * Class Inputs
 * @package Bookly\Backend\Components\Settings
 */
class Inputs
{
    /**
     * Render numeric input.
     *
     * @param string   $option_name
     * @param string   $label
     * @param string   $help
     * @param int|null $min
     * @param int|null $step
     * @param int|null $max
     */
    public static function renderNumber( $option_name, $label, $help, $min = null, $step = null, $max = null )
    {
        $control = strtr(
            '<input type="number" id="{name}" class="form-control" name="{name}" value="{value}"{min}{max}{step} />',
            array(
                '{name}'  => esc_attr( $option_name ),
                '{value}' => esc_attr( get_option( $option_name ) ),
                '{min}'   => $min !== null ? ' min="' . $min . '"' : '',
                '{max}'   => $max !== null ? ' max="' . $max . '"' : '',
                '{step}'  => $step !== null ? ' step="' . $step . '"' : '',
            )
        );

        echo self::buildControl( $option_name, $label, $help, $control );
    }

    /**
     * Render text input.
     *
     * @param string      $option_name
     * @param string      $label
     * @param string|null $help
     */
    public static function renderText( $option_name, $label, $help = null )
    {
        $control = strtr(
            '<input type="text" id="{name}" class="form-control" name="{name}" value="{value}" />',
            array(
                '{name}'  => esc_attr( $option_name ),
                '{value}' => esc_attr( get_option( $option_name ) ),
            )
        );

        echo self::buildControl( $option_name, $label, $help, $control );
    }

    /**
     * Render text area input.
     *
     * @param string      $option_name
     * @param string      $label
     * @param string|null $help
     * @param int         $rows
     */
    public static function renderTextArea( $option_name, $label, $help = null, $rows = 9 )
    {
        $control = strtr(
            '<textarea id="{name}" name="{name}" class="form-control" rows="{rows}" placeholder="{placeholder}">{value}</textarea>',
            array(
                '{name}'  => esc_attr( $option_name ),
                '{value}' => esc_textarea( get_option( $option_name ) ),
                '{rows}'  => $rows,
                '{placeholder}' => esc_attr__( 'Enter a value', 'bookly' ),
            )
        );

        echo self::buildControl( $option_name, $label, $help, $control );
    }

    /**
     * Build setting control.
     *
     * @param string $option_name
     * @param string $label
     * @param string $help
     * @param string $control_html
     * @return string
     */
    public static function buildControl( $option_name, $label, $help, $control_html )
    {
        return strtr(
            '<div class="form-group">{label}{help}{control}</div>',
            array(
                '{label}'   => $label != '' ? sprintf( '<label for="%s">%s</label>', $option_name, $label ) : '',
                '{help}'    => $help  != '' ? sprintf( '<p class="help-block">%s</p>', $help ) : '',
                '{control}' => $control_html,
            )
        );
    }
}