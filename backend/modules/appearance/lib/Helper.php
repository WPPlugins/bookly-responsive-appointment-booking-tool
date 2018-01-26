<?php
namespace BooklyLite\Backend\Modules\Appearance\Lib;

class Helper
{
    /**
     * Render editable string (single line).
     *
     * @param array $options
     */
    public static function renderString(array $options )
    {
        self::_renderEditable( $options, 'span' );
    }

    /**
     * Render editable label.
     *
     * @param array $options
     */
    public static function renderLabel( array $options )
    {
        self::_renderEditable( $options, 'label' );
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

        printf( '<span class="bookly-js-editable bookly-js-option %s editable-pre-wrapped" data-type="bookly" data-fieldType="textarea" data-values="%s" data-codes="%s" data-title="%s" data-placement="%s">%s</span>',
            $option_name,
            esc_attr( json_encode( array( $option_name => $option_value ) ) ),
            esc_attr( $codes ),
            esc_attr( $title ),
            $placement,
            esc_html( $option_value )
        );
    }

    /**
     * Render editable element.
     *
     * @param array $options
     * @param string $tag
     */
    private static function _renderEditable( array $options, $tag )
    {
        $data = array();
        foreach ( $options as $option_name ) {
            $data[ $option_name ] = get_option( $option_name );
        }

        printf( '<%s class="bookly-js-editable bookly-js-option %s" data-type="bookly" data-values="%s">%s</%s>',
            $tag,
            $options[0],
            esc_attr( json_encode( $data ) ),
            esc_html( $data[ $options[0] ] ),
            $tag
        );
    }
}