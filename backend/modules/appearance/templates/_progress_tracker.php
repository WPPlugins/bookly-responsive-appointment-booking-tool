<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $i = 1;
?>
<div class="ab-progress-tracker bookly-table">
    <div class="active">
        <?php echo $i ++ ?>. <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_step_service' ) ) ?>" data-mirror="text_service" class="text_service ab_editable" id="ab-text-step-service" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_step_service' ) ) ?></span>
        <div class="step"></div>
    </div>
    <?php if ( \BooklyLite\Lib\Config::extrasEnabled() ) : ?>
    <div <?php if ( ( $step >= 2 ) ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ ?>. <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_step_extras' ) ) ?>" data-mirror="text_extras" class="text_extras ab_editable" id="ab-text-step-extras" data-type="text"><?php echo esc_html(get_option( 'ab_appearance_text_step_extras' ) ) ?></span>
        <div class="step"></div>
    </div>
    <?php endif ?>
    <div <?php if ( $step >= 3 ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ ?>. <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_step_time' ) ) ?>" data-mirror="text_time" class="text_time ab_editable" id="ab-text-step-time" data-type="text"><?php echo esc_html(get_option( 'ab_appearance_text_step_time' ) ) ?></span>
        <div class="step"></div>
    </div>
    <div <?php if ( $step >= 4 ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ ?>. <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_step_cart' ) ) ?>" data-mirror="text_cart" class="text_cart ab_editable" id="ab-text-step-cart" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_step_cart' ) ) ?></span>
        <div class="step"></div>
    </div>
    <div <?php if ( $step >= 5 ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ ?>. <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_step_details' ) ) ?>" data-mirror="text_details" class="text_details ab_editable" id="ab-text-step-details" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_step_details' ) ) ?></span>
        <div class="step"></div>
    </div>
    <div <?php if ( $step >= 6 ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ ?>. <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_step_payment' ) ) ?>" data-mirror="text_payment" class="text_payment ab_editable" id="ab-text-step-payment" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_step_payment' ) ) ?></span>
        <div class="step"></div>
    </div>
    <div <?php if ( $step >= 7 ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ ?>. <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_step_done' ) ) ?>" data-mirror="text_done" class="text_done ab_editable" id="ab-text-step-done" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_step_done' ) ) ?></span>
        <div class="step"></div>
    </div>
</div>
