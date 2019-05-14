<?php
namespace Bookly\Backend\Components\Controls;

/**
 * Class Buttons
 * @package Bookly\Backend\Components\Controls
 */
class Buttons
{
    /**
     * Render custom button.
     *
     * @param string $id
     * @param string $class
     * @param string $caption
     * @param array  $attributes
     * @param string $caption_template
     */
    public static function renderCustom( $id = null, $class = 'btn-success', $caption = null, array $attributes = array(), $caption_template = '{caption}' )
    {
        if ( $caption === null ) {
            $caption = __( 'Save', 'bookly' );
        }

        $caption = strtr( $caption_template, array( '{caption}' => esc_html( $caption ) ) );

        echo self::_createButton( 'button', $id, $class, null, $caption, $attributes );
    }

    /**
     * Render delete button.
     *
     * @param string $id
     * @param string $extra_class
     * @param string $caption
     * @param array  $attributes
     */
    public static function renderDelete( $id = 'bookly-delete', $extra_class = null, $caption = null, array $attributes = array() )
    {
        if ( $caption === null ) {
            $caption = __( 'Delete...', 'bookly' );
        }

        echo self::_createButton(
            'button',
            $id,
            'btn-danger',
            $extra_class,
            '<i class="fa fa-fw fa-trash"></i> ' . esc_html( $caption ),
            $attributes
        );
    }

    /**
     * Render Add button.
     *
     * @param string $id
     * @param string $extra_class
     * @param string $caption
     * @param array  $attributes
     */
    public static function renderAdd( $id = 'bookly-add', $extra_class = null, $caption = null, array $attributes = array() )
    {
        if ( $caption === null ) {
            $caption = __( 'Add...', 'bookly' );
        }

        echo self::_createButton(
            'button',
            $id,
            'btn-success',
            $extra_class,
            '<i class="fa fa-fw fa-plus"></i> ' . esc_html( $caption ),
            $attributes
        );
    }

    /**
     * Render reset button.
     *
     * @param string $id
     * @param string $extra_class
     * @param string $caption
     * @param array  $attributes
     */
    public static function renderReset( $id = null, $extra_class = null, $caption = null, array $attributes = array() )
    {
        if ( $caption === null ) {
            $caption = __( 'Reset', 'bookly' );
        }

        echo self::_createButton( 'reset', $id, 'btn-lg btn-default', $extra_class, esc_html( $caption ), $attributes );
    }

    /**
     * Render submit button.
     *
     * @param string $id
     * @param string $extra_class
     * @param string $caption
     * @param array  $attributes
     */
    public static function renderSubmit( $id = 'bookly-save', $extra_class = null, $caption = null, array $attributes = array() )
    {
        if ( $caption === null ) {
            $caption = __( 'Save', 'bookly' );
        }

        echo self::_createButton( 'submit', $id, 'btn-lg btn-success', $extra_class, esc_html( $caption ), $attributes );
    }

    /**
     * Create button.
     *
     * @param string $type
     * @param string $id
     * @param string $class
     * @param string $extra_class
     * @param string $caption_html
     * @param array  $attributes
     * @return string
     */
    private static function _createButton( $type, $id, $class, $extra_class, $caption_html, array $attributes )
    {
        $classes = array( 'btn ladda-button' );
        if ( $class != '' ) {
            $classes[] = $class;
        }
        if ( $extra_class != '' ) {
            $classes[] = $extra_class;
        }

        if ( $id !== null ) {
            $attributes['id'] = $id;
        }
        $attributes_str = '';
        foreach ( $attributes as $attr => $value ) {
            $attributes_str .= sprintf( ' %s="%s"', $attr, esc_attr( $value ) );
        }

        return strtr(
            '<button type="{type}" class="{class}" data-spinner-size="40" data-style="zoom-in"{attributes}><span class="ladda-label">{caption}</span></button>',
            array(
                '{type}'       => $type,
                '{class}'      => implode( ' ', $classes ),
                '{attributes}' => $attributes_str,
                '{caption}'    => $caption_html,
            )
        );
    }
}