<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" action="<?php echo add_query_arg( 'type', '_hours' ) ?>" class="ab-staff-form" id="business-hours">
    <?php if (isset($message_h)) : ?>
    <div class="alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <?php echo $message_h ?>
    </div>
    <?php endif ?>

    <?php $form = new AB_BusinessHoursForm(); ?>

    <table>
        <?php foreach ( array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ) as $day ): ?>
            <tr>
                <td><?php _e( ucfirst( $day ) ) ?></td>
                <td>
                    <?php echo $form->renderField( 'ab_settings_' . $day ); ?>
                    <span><?php _e( ' to ', 'ab' ) ?></span>
                    <?php echo $form->renderField( 'ab_settings_' . $day, false ); ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td></td>
            <td>
                <input type="submit" value="<?php _e( 'Save', 'ab' ) ?>" class="btn btn-info ab-update-button" />
                <a id="ab-hours-reset" href="javascript:void(0)"><?php _e( 'Reset', 'ab' ) ?></a>
            </td>
        </tr>
    </table>
</form>