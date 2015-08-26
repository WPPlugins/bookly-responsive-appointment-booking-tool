<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="ab-booking-form">
    <!-- Progress Tracker-->
    <?php $step = 5; include '_progress_tracker.php'; ?>
    <div class="ab-row-fluid">
      <span data-inputclass="input-xxlarge" data-link-class="ab-text-info-fifth" class="ab-text-info-fifth-preview ab_editable" data-notes="<?php echo esc_attr( $this->render( '_codes', array( 'step' => 5 ), false ) ) ?>" data-placement="bottom" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_info_fifth_step' ) ) ?>" id="ab-text-info-fifth" data-type="textarea"><?php echo nl2br( esc_html( get_option( 'ab_appearance_text_info_fifth_step' ) ) ) ?></span>
    </div>
</div>