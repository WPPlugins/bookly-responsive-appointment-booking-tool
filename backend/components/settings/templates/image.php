<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="bookly-js-<?php echo esc_attr( $option_name ) ?>" class="bookly-thumb bookly-thumb-<?php echo $class ?> bookly-margin-right-lg">
    <input type="hidden" name="<?php echo $option_name ?>" data-default="<?php echo esc_attr( $option_value ) ?>" value="<?php echo esc_attr( $option_value ) ?>">
    <div class="bookly-flex-cell">
        <div class="form-group">
            <div class="bookly-js-image bookly-thumb bookly-thumb-<?php echo esc_attr( $class ) ?> bookly-margin-right-lg" style="<?php echo esc_attr( $img_style ) ?>" data-style="<?php echo esc_attr( $img_style ) ?>">
                <a class="dashicons dashicons-trash text-danger bookly-thumb-delete" href="javascript:void(0)" style="<?php echo esc_attr( $delete_style ) ?>" title="<?php esc_attr_e( 'Delete', 'bookly' ) ?>"></a>
                <div class="bookly-thumb-edit">
                    <div class="bookly-pretty"><label class="bookly-pretty-indicator bookly-thumb-edit-btn"><?php esc_html_e( 'Image', 'bookly' ) ?></label></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
jQuery(function ($) {
    $('#bookly-js-<?php echo $option_name ?> .bookly-pretty-indicator').on('click', function(){
        var frame = wp.media({
            library: {type: 'image'},
            multiple: false
        });
        frame.on('select', function () {
            var selection = frame.state().get('selection').toJSON(),
                img_src
            ;
            if (selection.length) {
                if (selection[0].sizes['full'] !== undefined) {
                    img_src = selection[0].sizes['full'].url;
                } else {
                    img_src = selection[0].url;
                }
                $('[name=<?php echo $option_name ?>]').val(selection[0].id);
                $('#bookly-js-<?php echo $option_name ?> .bookly-js-image').css({'background-image': 'url(' + img_src + ')', 'background-size': 'contain'});
                $('#bookly-js-<?php echo $option_name ?> .bookly-thumb-delete').show();
                $(this).hide();
            }
        });
        frame.open();
    });

    $('#bookly-js-<?php echo $option_name ?>')
        .on('click', '.bookly-thumb-delete', function () {
            var $thumb = $(this).closest('.bookly-js-image');
            $thumb.attr('style', '');
            $('[name=<?php echo $option_name ?>]').val('');
        });
});
</script>