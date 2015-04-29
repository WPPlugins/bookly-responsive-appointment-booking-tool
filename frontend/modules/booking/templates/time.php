<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php
    // Show Progress Tracker if enabled in settings
    if ( get_option( 'ab_appearance_show_progress_tracker' ) == 1 ) {
        echo $progress_tracker;
    }
?>

<?php if ( !empty ( $time ) ): ?>
<div class="ab-teaser ab-row-fluid"><div class="ab-desc"><?php echo $info_text ?></div></div>

<div class="ab-second-step">
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
</div>

<div class="ab-time-buttons ab-row-fluid ab-nav-steps ab-clear">
    <button class="ab-time-next ab-btn ab-right ladda-button orange zoom-in">
        <span class="ab_label">&gt;</span>
        <span class="spinner"></span>
    </button>
    <button class="ab-time-prev ab-btn ab-right ladda-button orange zoom-in"  style="display: none">
        <span class="ab_label">&lt;</span>
        <span class="spinner"></span>
    </button>
    <button class="ab-left ab-to-first-step ab-btn ladda-button orange zoom-in">
        <span class="ab_label"><?php _e( 'Back', 'ab' ) ?></span><span class="spinner"></span>
    </button>
</div>

<?php else: ?>
<h3><?php _e( 'No time is available for selected criteria.', 'ab' ) ?></h3>
<div class="ab-time-buttons ab-row-fluid ab-nav-steps ab-clear">
    <button class="ab-left ab-to-first-step ab-btn ladda-button orange zoom-in">
        <span class="ab_label"><?php _e( 'Back', 'ab' ) ?></span><span class="spinner"></span>
    </button>
</div>
<?php endif ?>