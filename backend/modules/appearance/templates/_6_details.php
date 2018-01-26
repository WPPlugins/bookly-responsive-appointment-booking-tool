<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use BooklyLite\Backend\Modules\Appearance\Components;
/** @var BooklyLite\Backend\Modules\Appearance\Lib\Helper $editable */
?>
<div class="bookly-form">
    <?php include '_progress_tracker.php' ?>

    <div class="bookly-box">
        <?php $editable::renderText( 'bookly_l10n_info_details_step', Components::getInstance()->renderCodes( array( 'step' => 6 ), false ) ) ?>
    </div>
    <div class="bookly-box">
        <?php $editable::renderText( 'bookly_l10n_info_details_step_guest', Components::getInstance()->renderCodes( array( 'step' => 6, 'extra_codes' => 1 ), false ), 'bottom', __( 'Visible to non-logged in customers only', 'bookly' ) ) ?>
    </div>
    <div class="bookly-box" id="bookly-js-show-login-form">
        <div class="bookly-btn bookly-inline-block">
            <?php $editable::renderString( array( 'bookly_l10n_step_details_button_login' ) ) ?>
        </div>
    </div>
    <div class="bookly-details-step">
        <div class="bookly-box bookly-table bookly-details-first-last-name" style="display: <?php echo get_option( 'bookly_cst_first_last_name' ) == 0 ? ' none' : 'table' ?>">
            <div class="bookly-form-group">
                <?php $editable::renderLabel( array( 'bookly_l10n_label_first_name', 'bookly_l10n_required_first_name', ) ) ?>
                <div>
                    <input type="text" value="" maxlength="60" />
                </div>
            </div>
            <div class="bookly-form-group">
                <?php $editable::renderLabel( array( 'bookly_l10n_label_last_name', 'bookly_l10n_required_last_name', ) ) ?>
                <div>
                    <input type="text" value="" maxlength="60" />
                </div>
            </div>
        </div>
        <div class="bookly-box bookly-table">
            <div class="bookly-form-group bookly-details-full-name" style="display: <?php echo get_option( 'bookly_cst_first_last_name' ) == 1 ? ' none' : 'block' ?>">
                <?php $editable::renderLabel( array( 'bookly_l10n_label_name', 'bookly_l10n_required_name', ) ) ?>
                <div>
                    <input type="text" value="" maxlength="60" />
                </div>
            </div>
            <div class="bookly-form-group">
                <?php $editable::renderLabel( array( 'bookly_l10n_label_phone', 'bookly_l10n_required_phone', ) ) ?>
                <div>
                    <input type="text" class="<?php if ( get_option( 'bookly_cst_phone_default_country' ) != 'disabled' ) : ?>bookly-user-phone<?php endif ?>" value="" />
                </div>
            </div>
            <div class="bookly-form-group">
                <?php $editable::renderLabel( array( 'bookly_l10n_label_email', 'bookly_l10n_required_email', ) ) ?>
                <div>
                    <input maxlength="40" type="text" value="" />
                </div>
            </div>
        </div>
        <div class="bookly-box" id="bookly-js-notes">
            <div class="bookly-form-group">
                <?php $editable::renderLabel( array( 'bookly_l10n_label_notes' ) ) ?>
                <div>
                    <textarea rows="3"></textarea>
                </div>
            </div>
        </div>
    </div>

    <?php \BooklyLite\Lib\Proxy\RecurringAppointments::renderAppearanceEditableInfoMessage() ?>

    <div class="bookly-box bookly-nav-steps">
        <div class="bookly-back-step bookly-js-back-step bookly-btn">
            <?php $editable::renderString( array( 'bookly_l10n_button_back' ) ) ?>
        </div>
        <div class="bookly-next-step bookly-js-next-step bookly-btn">
            <?php $editable::renderString( array( 'bookly_l10n_step_details_button_next' ) ) ?>
        </div>
    </div>
</div>
