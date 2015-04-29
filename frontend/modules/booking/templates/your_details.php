<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="ab-progress-tracker<?php if ( $this->payment_disabled ) echo ' ab-progress-tracker-four-steps'?>">
    <?php
        // Show Progress Tracker if enabled in settings
        if ( intval( get_option( 'ab_appearance_show_progress_tracker' ) == 1 ) ) {
            _e( $progress_tracker, 'ab' ) ;
        }
    ?>
</div>

<div style="margin-bottom: 15px!important;" class="ab-row-fluid"><?php echo $info_text ?></div>

<form class="ab-your-details-form ab-row-fluid">
  <div class="ab-details-list ab-left">
    <label class="ab-formLabel"><?php echo esc_html(get_option( 'ab_appearance_text_label_name' )); ?></label>
    <div class="ab-details-wrap">
      <input class="ab-full-name" type="text" value="<?php echo $userData->getName() ?>" maxlength="60"/>
    </div>
    <div class="ab-full-name-error ab-bold"></div>
  </div>
  <div class="ab-details-list ab-left">
    <label class="ab-formLabel"><?php echo esc_html(get_option( 'ab_appearance_text_label_phone' )); ?></label>
    <div class="ab-details-wrap">
      <input class="ab-user-phone" maxlength="30" type="text" value="<?php echo $userData->getPhone() ?>"/>
    </div>
    <div class="ab-user-phone-error ab-bold"></div>
  </div>
  <div class="ab-details-list ab-left">
    <label class="ab-formLabel"><?php echo esc_html(get_option( 'ab_appearance_text_label_email' )); ?></label>
    <div class="ab-details-wrap" style="margin-right: 0">
      <input class="ab-user-email" maxlength="40" type="text" value="<?php echo $userData->getEmail() ?>"/>
    </div>
    <div class="ab-user-email-error ab-bold"></div>
  </div>
  <div class="ab-clear"></div>
  <div class="ab-details-list ab-textarea">
    <label class="ab-formLabel"><?php echo esc_html(get_option( 'ab_appearance_text_label_notes' )); ?></label>
    <div style="margin-right: 2px">
      <textarea rows="6" class="ab-user-notes"><?php echo $userData->getNotes() ?></textarea>
    </div>
   </div>
</form>
<div class="ab-row-fluid ab-nav-steps ab-clear">
  <button class="ab-left ab-to-second-step ladda-button orange zoom-in" style="margin-right: 10px;">
	<span class="ab_label"><?php _e( 'Back', 'ab' ) ?></span><span class="spinner"></span>
  </button>
  <button class="ab-right ab-to-fourth-step ladda-button orange zoom-in">
	<span class="ab_label"><?php _e( 'Next', 'ab' ) ?></span><span class="spinner"></span>
  </button>
</div>