<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $i = 1;
?>
<div class="ab-progress-tracker bookly-table">
    <div class="active">
        <?php echo $i ++ ?>. <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_step_service' ), 'bookly-js-text-service' ) ?>
        <div class="step"></div>
    </div>
    <?php if ( \BooklyLite\Lib\Config::isServiceExtrasEnabled() ) : ?>
    <div <?php if ( $step >= 2 ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ ?>. <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_step_extras' ), 'bookly-js-text-extras' ) ?>
        <div class="step"></div>
    </div>
    <?php endif ?>
    <div <?php if ( $step >= 3 ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ ?>. <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_step_time' ), 'bookly-js-text-time' ) ?>
        <div class="step"></div>
    </div>
    <?php if ( \BooklyLite\Lib\Config::isRecurringAppointmentsEnabled() ) : ?>
        <div <?php if ( $step >= 4 ) : ?>class="active"<?php endif ?>>
            <?php echo $i ++ ?>. <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_step_repeat' ), 'bookly-js-text-repeat' ) ?>
            <div class=step></div>
        </div>
    <?php endif ?>
    <div <?php if ( $step >= 5 ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ ?>. <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_step_cart' ), 'bookly-js-text-cart' ) ?>
        <div class="step"></div>
    </div>
    <div <?php if ( $step >= 6 ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ ?>. <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_step_details' ), 'bookly-js-text-details' ) ?>
        <div class="step"></div>
    </div>
    <div <?php if ( $step >= 7 ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ ?>. <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_step_payment' ), 'bookly-js-text-payment' ) ?>
        <div class="step"></div>
    </div>
    <div <?php if ( $step >= 8 ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ ?>. <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_step_done' ), 'bookly-js-text-done' ) ?>
        <div class="step"></div>
    </div>
</div>
