<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="ab-booking-form">
    <!-- Progress Tracker-->
    <?php $step = 3; include '_progress_tracker.php'; ?>

    <div class="ab-row-fluid">
        <span data-inputclass="input-xxlarge" data-notes="<?php echo esc_attr( $this->render( '_codes', array( 'step' => 3, 'login' => false ), false ) ) ?>" data-placement="bottom" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_info_third_step' ) ) ?>" class="ab-text-info-third-preview ab-row-fluid ab_editable" id="ab-text-info-third" data-type="textarea"><?php echo esc_html( get_option( 'ab_appearance_text_info_third_step' ) ) ?></span>
    </div>
    <div class="ab-row-fluid">
        <span data-inputclass="input-xxlarge" data-title="<?php _e( 'Visible to non-logged in customers only', 'bookly' ) ?>" data-notes="<?php echo esc_attr( $this->render( '_codes', array( 'step' => 3, 'login' => true ), false ) ) ?>" data-placement="bottom" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_info_third_step_guest' ) ) ?>" class="ab-text-info-third-preview ab-row-fluid ab_editable" id="ab-text-info-third-guest" data-type="textarea"><?php echo esc_html( get_option( 'ab_appearance_text_info_third_step_guest' ) ) ?></span>
    </div>
    <form class="ab-third-step">
        <div class="ab-row-fluid ab-col-phone" style="height: 55px; overflow: visible;">
            <div class="ab-formGroup ab-left">
                <label data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_name' ) ) ?>" class="ab-formLabel text_name_label ab_editable" id="ab-text-label-name" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_label_name' ) ) ?></label>
                <div class="ab-formField">
                    <input class="ab-formElement" type="text" value="" maxlength="60" />
                </div>
            </div>
            <div class="ab-formGroup ab-left">
                <label data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_phone' ) ) ?>" class="ab-formLabel text_phone_label ab_editable" id="ab-text-label-phone" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_label_phone' ) ) ?></label>
                <div class="ab-formField">
                    <input class="ab-formElement ab-user-phone" maxlength="30" type="tel" value="" />
                </div>
            </div>
            <div class="ab-formGroup ab-left">
                <label data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_email' ) ) ?>" class="ab-formLabel text_email_label ab_editable" id="ab-text-label-email" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_label_email' ) ) ?></label>
                <div class="ab-formField" style="margin-right: 0">
                    <input class="ab-formElement" maxlength="40" type="text" value="" />
                </div>
            </div>
        </div>
    </form>
    <div class="ab-row-fluid last-row ab-nav-steps ab-clear">
        <button class="ab-left ab-to-second-step ab-btn ladda-button">
            <span><?php _e( 'Back', 'bookly' ) ?></span>
        </button>
        <button class="ab-right ab-to-fourth-step ab-btn ladda-button">
            <span><?php _e( 'Next', 'bookly' ) ?></span>
        </button>
    </div>
</div>
