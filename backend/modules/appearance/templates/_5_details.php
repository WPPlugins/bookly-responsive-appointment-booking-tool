<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="bookly-form">
    <?php include '_progress_tracker.php' ?>

    <div class="ab-row">
        <span data-inputclass="input-xxlarge" data-notes="<?php echo esc_attr( $this->render( '_codes', array( 'step' => 5, 'login' => false ), false ) ) ?>" data-placement="bottom" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_info_details_step' ) ) ?>" class="ab_editable" id="ab-text-info-details" data-type="textarea"><?php echo esc_html( get_option( 'ab_appearance_text_info_details_step' ) ) ?></span>
    </div>
    <div class="ab-row">
        <span data-inputclass="input-xxlarge" data-title="<?php _e( 'Visible to non-logged in customers only', 'bookly' ) ?>" data-notes="<?php echo esc_attr( $this->render( '_codes', array( 'step' => 5, 'login' => true ), false ) ) ?>" data-placement="bottom" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_info_details_step_guest' ) ) ?>" class="ab_editable" id="ab-text-info-details-guest" data-type="textarea"><?php echo esc_html( get_option( 'ab_appearance_text_info_details_step_guest' ) ) ?></span>
    </div>
    <div class="ab-details-step">
        <div class="ab-row bookly-table">
            <div class="ab-formGroup">
                <label>
                    <span
                        data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_name' ) ) ?>"
                        data-default-error="<?php echo esc_attr( get_option( 'ab_appearance_text_required_name' ) ) ?>"
                        id="ab-text-label-name"
                        data-type="multiple"
                        data-option-id="ab_appearance_text_required_name"><?php echo esc_html( get_option( 'ab_appearance_text_label_name' ) ) ?></span>
                </label>

                <div>
                    <input type="text" value="" maxlength="60" />
                </div>
            </div>
            <div class="ab-formGroup">
                <label>
                    <span
                        data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_phone' ) ) ?>"
                        data-default-error="<?php echo esc_attr( get_option( 'ab_appearance_text_required_phone' ) ) ?>"
                        id="ab-text-label-phone"
                        data-type="multiple"
                        data-option-id="ab_appearance_text_required_phone"><?php echo esc_html( get_option( 'ab_appearance_text_label_phone' ) ) ?></span>
                </label>
                <div>
                    <input type="text" class="<?php if ( get_option( 'ab_settings_phone_default_country' ) != 'disabled' ) : ?>ab-user-phone<?php endif ?>" value="" />
                </div>
            </div>
            <div class="ab-formGroup">
                <label>
                    <span
                        data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_email' ) ) ?>"
                        data-default-error="<?php echo esc_attr( get_option( 'ab_appearance_text_required_email' ) ) ?>"
                        id="ab-text-label-email"
                        data-type="multiple"
                        data-option-id="ab_appearance_text_required_email"><?php echo esc_html( get_option( 'ab_appearance_text_label_email' ) ) ?></span>
                </label>
                <div>
                    <input maxlength="40" type="text" value="" />
                </div>
            </div>
        </div>
    </div>
    <div class="ab-row ab-nav-steps">
        <div class="ab-left ab-back-step ab-btn">
            <span class="text_back ab_editable" id="ab-text-button-back" data-mirror="text_back" data-type="text" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_button_back' ) ) ?>"><?php echo esc_html( get_option( 'ab_appearance_text_button_back' ) ) ?></span>
        </div>
        <div class="ab-right ab-next-step ab-btn">
            <span class="text_next ab_editable" id="ab-text-button-next" data-mirror="text_next" data-type="text" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_button_next' ) ) ?>"><?php echo esc_html( get_option( 'ab_appearance_text_button_next' ) ) ?></span>
        </div>
    </div>
</div>
