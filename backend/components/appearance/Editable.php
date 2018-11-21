<?php
namespace Bookly\Backend\Components\Appearance;

/**
 * Class Editable
 * @package Bookly\Backend\Components\Appearance
 */
class Editable
{
    /**
     * Render editable string (single line).
     *
     * @param array $options
     * @param bool $echo
     * @return string
     */
    public static function renderString( array $options, $echo = true )
    {
        return self::_renderEditable( $options, 'span', $echo );
    }

    /**
     * Render editable label.
     *
     * @param array $options
     * @param bool $echo
     * @return string
     */
    public static function renderLabel( array $options, $echo = true )
    {
        return self::_renderEditable( $options, 'label', $echo );
    }

    /**
     * Render editable text (multi-line).
     *
     * @param string $option_name
     * @param string $codes
     * @param string $placement
     * @param string $title
     */
    public static function renderText( $option_name, $codes = '', $placement = 'bottom', $title = '' )
    {
        $option_value = get_option( $option_name );

        printf( '<span class="bookly-js-editable bookly-js-option %s editable-pre-wrapped" data-type="bookly" data-fieldType="textarea" data-values="%s" data-codes="%s" data-title="%s" data-placement="%s" data-option="%s">%s</span>',
            $option_name,
            esc_attr( json_encode( array( $option_name => $option_value ) ) ),
            esc_attr( $codes ),
            esc_attr( $title ),
            $placement,
            $option_name,
            esc_html( $option_value )
        );
    }

    /**
     * Render editable number.
     *
     * @param string $option_name
     * @param int    $min
     * @param int    $step
     */
    public static function renderNumber( $option_name, $min = 1, $step = 1 )
    {
        $option_value = get_option( $option_name );

        printf( '<span class="bookly-js-editable bookly-js-option %s editable-pre-wrapped" data-type="bookly" data-fieldType="number" data-values="%s" data-min="%s" data-step="%s" data-option="%s">%s</span>',
            $option_name,
            esc_attr( json_encode( array( $option_name => $option_value ) ) ),
            esc_attr( $min ),
            esc_attr( $step ),
            $option_name,
            esc_html( $option_value )
        );
    }

    /**
     * Render editable element.
     *
     * @param array $options
     * @param string $tag
     * @param bool $echo
     * @return string|void
     */
    private static function _renderEditable( array $options, $tag, $echo = true )
    {
        $data = array();
        foreach ( $options as $option_name ) {
            $data[ $option_name ] = get_option( $option_name );
        }

        $main_option = $options[0];
        $class       = implode( ' ', $options );
        $data_values = esc_attr( json_encode( $data ) );
        $content     = esc_html( $data[ $options[0] ] );

        $template = '<{tag} class="bookly-js-editable bookly-js-option {class}" data-type="bookly" data-values="{data-values}" data-option="{data-option}">{content}</{tag}>';
        $html = strtr( $template, array(
            '{tag}'         => $tag,
            '{class}'       => $class,
            '{data-values}' => $data_values,
            '{data-option}' => $main_option,
            '{content}'     => $content,
        ) );

        if ( ! $echo ) {
            return $html;
        }

        echo $html;
    }
}