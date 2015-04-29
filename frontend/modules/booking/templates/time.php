<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="ab-progress-tracker<?php if ( $this->payment_disabled ) echo ' ab-progress-tracker-four-steps'?>">
    <?php
        // Show Progress Tracker if enabled in settings
        if ( intval( get_option( 'ab_appearance_show_progress_tracker' ) == 1 ) ) {
            _e( $progress_tracker, 'ab' ) ;
        }
    ?>
</div>

<div style="margin-bottom: 15px!important;" class="ab-teaser ab-row-fluid"><?php if ( $time ) { echo $info_text; } ?></div>

<div class="ab-time-list">
  <?php if ( $time ) : ?>
  <div class="ab-columnizer-wrap">
        <div class="ab-columnizer">
            <?php foreach ($time as $date => $hours) : ?>
                <?php foreach ($hours as $object) : ?>
                    <button data-staff_id="<?php echo $object->staff_id ?>" data-date="<?php if ( !$object->is_day ) echo $object->clean_date; ?>" class="<?php echo $object->is_day ? 'ab-available-day' : 'ab-available-hour ladda-button zoom-in' ?>" value="<?php echo $object->value ?>">
                        <?php if ( !$object->is_day ) : ?>
	                        <span class="ab_label"><i class="ab-hour-icon"><span></span></i><?php echo $object->label ?></span><span class="spinner"></span>
                        <?php else : ?>
                          <?php echo $object->label ?>
                        <?php endif ?>
                    </button>
                <?php endforeach ?>
            <?php endforeach ?>
        </div>
    </div>
    <?php else : ?>
        <?php _e( '<h3>The selected time is not available anymore. Please, choose another time slot.</h3>', 'ab' ) ?>
    <?php endif ?>
</div>
<div class="ab-time-buttons ab-row-fluid ab-nav-steps ab-clear">
    <?php if ( $time ) : ?>
		<button class="ab-time-no-resize ab-time-next ab-right ladda-button orange zoom-in">
      <span class="ab_label">&gt;</span>
      <span class="spinner"></span>
		</button>
    <button class="ab-time-no-resize ab-time-prev ab-right ladda-button orange zoom-in"  style="display: none">
      <span class="ab_label">&lt;</span>
      <span class="spinner"></span>
    </button>
    <?php endif ?>
	<button class="ab-time-no-resize ab-left ab-to-first-step ladda-button orange zoom-in">
		<span class="ab_label"><?php _e( 'Back', 'ab' ) ?></span><span class="spinner"></span>
	</button>
</div>
