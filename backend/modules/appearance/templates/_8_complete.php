<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="bookly-form">
    <?php include '_progress_tracker.php' ?>
    <div class="bookly-box">
      <span class="bookly-editable" data-notes="<?php echo esc_attr( $this->render( '_codes', array( 'step' => 8 ), false ) ) ?>" data-placement="bottom" data-option-default="<?php form_option( 'bookly_l10n_info_complete_step' ) ?>" id="bookly_l10n_info_complete_step" data-type="textarea"><?php echo nl2br( esc_html( get_option( 'bookly_l10n_info_complete_step' ) ) ) ?></span>
    </div>
</div>