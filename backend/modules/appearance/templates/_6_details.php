<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="bookly-form">
    <?php include '_progress_tracker.php' ?>

    <div class="bookly-box">
        <span data-notes="<?php echo esc_attr( $this->render( '_codes', array( 'step' => 6, 'login' => false ), false ) ) ?>" data-placement="bottom" data-option-default="<?php form_option( 'bookly_l10n_info_details_step' ) ?>" class="bookly-editable" id="bookly_l10n_info_details_step" data-type="textarea"><?php echo esc_html( get_option( 'bookly_l10n_info_details_step' ) ) ?></span>
    </div>
    <div class="bookly-box">
        <span data-title="<?php _e( 'Visible to non-logged in customers only', 'bookly' ) ?>" data-notes="<?php echo esc_attr( $this->render( '_codes', array( 'step' => 6, 'login' => true ), false ) ) ?>" data-placement="bottom" data-option-default="<?php form_option( 'bookly_l10n_info_details_step_guest' ) ?>" class="bookly-editable" id="bookly_l10n_info_details_step_guest" data-type="textarea"><?php echo esc_html( get_option( 'bookly_l10n_info_details_step_guest' ) ) ?></span>
    </div>
    <div class="ab-details-step">
        <div class="bookly-box bookly-table">
            <div class="ab-formGroup">
                <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderLabel( array( 'bookly_l10n_label_name', 'bookly_l10n_required_name', ) ) ?>
                <div>
                    <input type="text" value="" maxlength="60" />
                </div>
            </div>
            <div class="ab-formGroup">
                <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderLabel( array( 'bookly_l10n_label_phone', 'bookly_l10n_required_phone', ) ) ?>
                <div>
                    <input type="text" class="<?php if ( get_option( 'bookly_cst_phone_default_country' ) != 'disabled' ) : ?>ab-user-phone<?php endif ?>" value="" />
                </div>
            </div>
            <div class="ab-formGroup">
                <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderLabel( array( 'bookly_l10n_label_email', 'bookly_l10n_required_email', ) ) ?>
                <div>
                    <input maxlength="40" type="text" value="" />
                </div>
            </div>
        </div>
    </div>

    <?php do_action( 'bookly_recurring_appointments_render_editable_message_info' ) ?>

    <div class="bookly-box bookly-nav-steps">
        <div class="bookly-back-step ab-btn">
            <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_button_back' ), 'bookly-js-text-back' ) ?>
        </div>
        <div class="bookly-next-step ab-btn">
            <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_button_next' ), 'bookly-js-text-next' ) ?>
        </div>
    </div>
</div>
