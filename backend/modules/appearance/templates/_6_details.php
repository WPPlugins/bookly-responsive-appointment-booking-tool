<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components;
use Bookly\Backend\Components\Appearance\Codes;
use Bookly\Backend\Components\Appearance\Editable;
use Bookly\Backend\Modules\Appearance\Proxy;
/** @var array $userData */
?>
<div class="bookly-form">
    <?php include '_progress_tracker.php' ?>

    <div class="bookly-box">
        <?php Editable::renderText( 'bookly_l10n_info_details_step', Codes::getHtml( 6 ) ) ?>
    </div>
    <div class="bookly-box">
        <?php Editable::renderText( 'bookly_l10n_info_details_step_guest', Codes::getHtml( 6, true ), 'bottom', __( 'Visible to non-logged in customers only', 'bookly' ) ) ?>
    </div>
    <div class="bookly-box bookly-guest">
        <div class="bookly-btn" id="bookly-login-button">
            <?php Editable::renderString( array( 'bookly_l10n_step_details_button_login' ) ) ?>
        </div>
        <?php Proxy\Pro::renderFacebookButton() ?>
    </div>
    <div class="bookly-details-step">

        <div class="bookly-box bookly-table bookly-js-details-first-last-name<?php echo ! get_option( 'bookly_cst_first_last_name' ) ? ' collapse' : '' ?>">
            <div class="bookly-form-group">
                <?php Editable::renderLabel( array( 'bookly_l10n_label_first_name', 'bookly_l10n_required_first_name', ) ) ?>
                <div>
                    <input type="text" value="" maxlength="60" />
                </div>
            </div>
            <div class="bookly-form-group">
                <?php Editable::renderLabel( array( 'bookly_l10n_label_last_name', 'bookly_l10n_required_last_name', ) ) ?>
                <div>
                    <input type="text" value="" maxlength="60" />
                </div>
            </div>
        </div>

        <div class="bookly-box bookly-table">
            <div class="bookly-form-group bookly-js-details-full-name<?php echo get_option( 'bookly_cst_first_last_name' ) ? ' collapse' : '' ?>">
                <?php Editable::renderLabel( array( 'bookly_l10n_label_name', 'bookly_l10n_required_name', ) ) ?>
                <div>
                    <input type="text" value="" maxlength="60" />
                </div>
            </div>
            <div class="bookly-form-group bookly-js-details-phone">
                <?php Editable::renderLabel( array( 'bookly_l10n_label_phone', 'bookly_l10n_required_phone', ) ) ?>
                <div>
                    <input type="text" class="bookly-animate<?php if ( get_option( 'bookly_cst_phone_default_country' ) != 'disabled' ) : ?> bookly-user-phone<?php endif ?>" value="" />
                </div>
            </div>
            <div class="bookly-form-group bookly-js-details-email<?php echo ! get_option( 'bookly_cst_first_last_name' ) && get_option( 'bookly_app_show_email_confirm' ) ? ' collapse' : '' ?>">
                <?php Editable::renderLabel( array( 'bookly_l10n_label_email', 'bookly_l10n_required_email' ) ) ?>
                <div>
                    <input class="bookly-animate" maxlength="40" type="text" value="" />
                </div>
            </div>
            <div class="bookly-form-group bookly-js-details-confirm<?php echo ! get_option( 'bookly_cst_first_last_name' ) || ! get_option( 'bookly_app_show_email_confirm' ) ? ' collapse' : '' ?>">
                <?php Editable::renderLabel( array( 'bookly_l10n_label_email_confirm', 'bookly_l10n_email_confirm_not_match' ) ) ?>
                <div>
                    <input maxlength="40" type="text" value="" />
                </div>
            </div>
        </div>

        <div class="bookly-box bookly-table bookly-js-details-email-confirm<?php echo get_option( 'bookly_cst_first_last_name' ) || ! get_option( 'bookly_app_show_email_confirm' ) ? ' collapse' : '' ?>">
            <div class="bookly-form-group">
                <?php Editable::renderLabel( array( 'bookly_l10n_label_email', 'bookly_l10n_required_email' ) ) ?>
                <div>
                    <input maxlength="40" type="text" value="" />
                </div>
            </div>
            <div class="bookly-form-group">
                <?php Editable::renderLabel( array( 'bookly_l10n_label_email_confirm', 'bookly_l10n_email_confirm_not_match' ) ) ?>
                <div>
                    <input maxlength="40" type="text" value="" />
                </div>
            </div>
        </div>

        <?php Components\Appearance\Proxy\Pro::renderAddress() ?>
        <?php Components\Appearance\Proxy\Pro::renderBirthday() ?>
        <?php Proxy\CustomerInformation::renderCustomerInformation() ?>
        <?php Proxy\CustomFields::renderCustomFields() ?>

        <div class="bookly-box" id="bookly-js-notes">
            <div class="bookly-form-group">
                <?php Editable::renderLabel( array( 'bookly_l10n_label_notes' ) ) ?>
                <div>
                    <textarea rows="3"></textarea>
                </div>
            </div>
        </div>

        <?php Proxy\Files::renderAppearance() ?>
    </div>

    <?php Proxy\RecurringAppointments::renderInfoMessage() ?>

    <div class="bookly-box bookly-nav-steps">
        <div class="bookly-back-step bookly-js-back-step bookly-btn">
            <?php Editable::renderString( array( 'bookly_l10n_button_back' ) ) ?>
        </div>
        <div class="<?php echo get_option( 'bookly_app_align_buttons_left' ) ? 'bookly-left' : 'bookly-right' ?>">
            <div class="bookly-next-step bookly-js-next-step bookly-btn">
                <?php Editable::renderString( array( 'bookly_l10n_step_details_button_next' ) ) ?>
            </div>
        </div>
    </div>
</div>
