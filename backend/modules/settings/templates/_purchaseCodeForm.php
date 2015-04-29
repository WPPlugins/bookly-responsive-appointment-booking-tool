<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form enctype="multipart/form-data" method="post" action="<?php echo add_query_arg( 'type', '_purchase_code' ) ?>" class="ab-staff-form" id="purchase_code">
    <?php if ( isset ( $message_pc ) ) : ?>
        <div class="alert">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?php echo $message_pc ?>
        </div>
    <?php endif ?>

    <table class="form-horizontal">
        <tr>
            <td><?php _e( 'Purchase Code', 'ab' ) ?></td>
            <td>
                <label for="purchase_code"></label>
                <input class="purchase-code" type="text" size="255" name="ab_envato_purchase_code" value="<?php echo get_option( 'ab_envato_purchase_code' ) ?>" />
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <input type="submit" value="<?php _e( 'Save', 'ab' ) ?>" class="btn btn-info ab-update-button" />
                <button class="ab-reset-form" type="reset"><?php _e( ' Reset ', 'ab' ) ?></button>
            </td>
        </tr>
    </table>
</form>