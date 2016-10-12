<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php foreach ( $custom_fields as $field_id ) : ?>
    <td>
        <?php if ( array_key_exists( $field_id, $app['custom_fields'] ) ) : ?>
            <?php echo $app['custom_fields'][ $field_id ] ?>
        <?php endif ?>
    </td>
<?php endforeach ?>