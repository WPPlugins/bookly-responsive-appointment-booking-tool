<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * @var AB_UserBookingData $userData
 * @var string $progress_tracker
 * @var string $info_text
 */

// Show Progress Tracker if enabled in settings
if ( get_option( 'ab_appearance_show_progress_tracker' ) == 1 ) {
    echo $progress_tracker;
}

$current_user = wp_get_current_user();
?>

<div class="ab-row-fluid"><div class="ab-desc"><?php echo $info_text ?></div></div>

<form class="ab-third-step">
    <div class="ab-row-fluid">
        <div class="ab-formGroup ab-left">
            <label class="ab-formLabel"><?php echo esc_html(get_option( 'ab_appearance_text_label_name' )); ?></label>
            <div class="ab-formField">
                <input class="ab-formElement ab-full-name" type="text" value="<?php echo (!$userData->getName() && $current_user)? $current_user->display_name : $userData->getName() ?>" maxlength="60"/>
            </div>
            <div class="ab-full-name-error ab-label-error ab-bold"></div>
        </div>
        <div class="ab-formGroup ab-left">
            <label class="ab-formLabel"><?php echo esc_html(get_option( 'ab_appearance_text_label_phone' )); ?></label>
            <div class="ab-formField">
                <input class="ab-formElement ab-user-phone" maxlength="30" type="text" value="<?php echo $userData->getPhone() ?>"/>
            </div>
            <div class="ab-user-phone-error ab-label-error ab-bold"></div>
        </div>
        <div class="ab-formGroup ab-left">
            <label class="ab-formLabel"><?php echo esc_html(get_option( 'ab_appearance_text_label_email' )); ?></label>
            <div class="ab-formField" style="margin-right: 0">
                <input class="ab-formElement ab-user-email" maxlength="40" type="text" value="<?php echo (!$userData->getEmail() && $current_user)? $current_user->user_email : $userData->getEmail() ?>"/>
            </div>
            <div class="ab-user-email-error ab-label-error ab-bold"></div>
        </div>
    </div>
    <div class="ab-row-fluid">
        <div class="ab-formGroup ab-full ab-lastGroup">
            <label class="ab-formLabel"><?php echo esc_html(get_option( 'ab_appearance_text_label_notes' )); ?></label>
            <div class="ab-formField">
                <textarea rows="3" class="ab-formElement ab-user-notes"><?php echo $userData->getNotes() ?></textarea>
            </div>
        </div>
    </div>
</form>
<div class="ab-row-fluid ab-nav-steps ab-clear">
    <button class="ab-left ab-to-second-step ab-btn ladda-button orange zoom-in" style="margin-right: 10px;">
        <span class="ab_label"><?php _e( 'Back', 'ab' ) ?></span><span class="spinner"></span>
    </button>
    <button class="ab-right ab-to-fourth-step ab-btn ladda-button orange zoom-in">
        <span class="ab_label"><?php _e( 'Next', 'ab' ) ?></span><span class="spinner"></span>
    </button>
</div>