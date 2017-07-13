<?php
namespace BooklyLite\Backend\Modules\Appearance\Lib;

class Helper
{
    /**
     * Render ui for editing frontend messages. <span>
     *
     * @param array $data           l10n option name
     * @param null  $mirror_class   the updated value will be displayed also for the objects with css class
     */
    public static function renderSpan( array $data, $mirror_class = null )
    {
        $id      = $data[0];
        $options = array();
        foreach ( $data as $option_name ) {
            $options[ $option_name ] = get_option( $option_name );
        }
        if ( count( $options ) > 1 ) {
            printf( '<span id="%s" data-options-default=\'%s\' data-type="multiple"%s%s>%s</span>',
                $id, json_encode( $options ), $mirror_class ? ' class="' . $mirror_class . '"' : '',
                $mirror_class ? ' data-mirror="' . $mirror_class . '"' : '', esc_html( $options[ $id ] )
            );
        } else {
            printf( '<span id="%s" data-option-default=\'%s\' data-type="text" class="bookly-editable%s"%s>%s</span>',
                $id, esc_attr( $options[ $id ] ), $mirror_class ? ' ' . $mirror_class : '',
                $mirror_class ? ' data-mirror="' . $mirror_class . '"' : '', esc_html( $options[ $id ] )
            );
        }
    }

    /**
     * Render ui for editing frontend messages. <label>
     *
     * @param array $data           l10n option name
     * @param null  $mirror_class   the updated value will be displayed also for the objects with css class
     */
    public static function renderLabel( array $data, $mirror_class = null )
    {
        $id      = $data[0];
        $options = array();
        foreach ( $data as $option_name ) {
            $options[ $option_name ] = get_option( $option_name );
        }
        if ( count( $options ) > 1 ) {
            printf( '<label id="%s" data-type="multiple" data-options-default=\'%s\'%s%s>%s</label>',
                $id, json_encode( $options ), $mirror_class ? ' class="' . $mirror_class . '"' : '',
                $mirror_class ? ' data-mirror="' . $mirror_class . '"' : '', esc_html( $options[ $id ] )
            );
        } else {
            printf( '<label id="%s" data-option-default=\'%s\' data-type="text" class="bookly-editable%s"%s>%s</label>',
                $id, esc_attr( $options[ $id ] ), $mirror_class ? ' ' . $mirror_class : '',
                $mirror_class ? ' data-mirror="' . $mirror_class . '"' : '', esc_html( $options[ $id ] )
            );
        }
    }

}