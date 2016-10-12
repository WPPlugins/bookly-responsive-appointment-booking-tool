<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="bookly-form">
    <?php include '_progress_tracker.php' ?>
    <div class="ab-row">
      <span data-inputclass="input-xxlarge" class="ab_editable" data-notes="<?php echo esc_attr( $this->render( '_codes', array( 'step' => 7 ), false ) ) ?>" data-placement="bottom" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_info_complete_step' ) ) ?>" id="ab-text-info-complete" data-type="textarea"><?php echo nl2br( esc_html( get_option( 'ab_appearance_text_info_complete_step' ) ) ) ?></span>
    </div>
</div>