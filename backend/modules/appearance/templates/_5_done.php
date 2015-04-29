<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="ab-booking-form" style="overflow: hidden">
    <!-- Progress Tracker-->
    <?php $step = 5; include '_progress_tracker.php'; ?>
    <div style="margin-bottom: 15px!important;" class="ab-row-fluid">
      <span data-inputclass="input-xxlarge" data-link-class="ab-text-info-fifth" class="ab-text-info-fifth-preview ab_editable" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_info_fifth_step' ) ) ?>" id="ab-text-info-fifth" data-type="textarea" data-pk="1"><?php echo nl2br( esc_html( get_option( 'ab_appearance_text_info_fifth_step' ) ) ) ?></span>
    </div>
</div>

<!-- fifth step options -->
<div class="ab-fifth-step-options">
    <div class="ab-booking-details">
    </div>
</div>