<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label><?php esc_html_e( 'Body', 'bookly' ) ?></label>
            <?php wp_editor( '', 'bookly-js-message', array(
                'textarea_name'  => 'notification[message]',
                'media_buttons'  => false,
                'editor_height'  => 250,
                'default_editor' => 'tinymce',
                'tinymce' => array(
                    'resize' => true,
                    'wp_autoresize_on' => true,
                )
            ) ) ?>
        </div>
    </div>
</div>
<?php static::renderTemplate( '_attach' ) ?>