<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    /** @var BooklyLite\Backend\Modules\Appearance\Lib\Helper $editable */
    $i = 1;
?>
<div class="bookly-progress-tracker bookly-table">
    <div class="active">
        <?php echo $i ++ ?>. <?php $editable::renderString( array( 'bookly_l10n_step_service' ) ) ?>
        <div class="step"></div>
    </div>
    <?php if ( \BooklyLite\Lib\Config::serviceExtrasEnabled() ) : ?>
    <div <?php if ( $step >= 2 ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ ?>. <?php $editable::renderString( array( 'bookly_l10n_step_extras' ) ) ?>
        <div class="step"></div>
    </div>
    <?php endif ?>
    <div <?php if ( $step >= 3 ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ ?>. <?php $editable::renderString( array( 'bookly_l10n_step_time' ) ) ?>
        <div class="step"></div>
    </div>
    <?php if ( \BooklyLite\Lib\Config::recurringAppointmentsEnabled() ) : ?>
        <div <?php if ( $step >= 4 ) : ?>class="active"<?php endif ?>>
            <?php echo $i ++ ?>. <?php $editable::renderString( array( 'bookly_l10n_step_repeat' ) ) ?>
            <div class=step></div>
        </div>
    <?php endif ?>
    <div <?php if ( $step >= 5 ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ ?>. <?php $editable::renderString( array( 'bookly_l10n_step_cart' ) ) ?>
        <div class="step"></div>
    </div>
    <div <?php if ( $step >= 6 ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ ?>. <?php $editable::renderString( array( 'bookly_l10n_step_details' ) ) ?>
        <div class="step"></div>
    </div>
    <div <?php if ( $step >= 7 ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ ?>. <?php $editable::renderString( array( 'bookly_l10n_step_payment' ) ) ?>
        <div class="step"></div>
    </div>
    <div <?php if ( $step >= 8 ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ ?>. <?php $editable::renderString( array( 'bookly_l10n_step_done' ) ) ?>
        <div class="step"></div>
    </div>
</div>
