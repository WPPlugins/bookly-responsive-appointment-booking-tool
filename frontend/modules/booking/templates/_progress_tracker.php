<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $i = 1;
?>
<div class="ab-progress-tracker bookly-table">
    <?php if ( $skip_service_step == false ) : ?>
    <div <?php if ( $step >= 1 ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ . '. ' . \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_step_service' ) ?>
        <div class=step></div>
    </div>
    <?php endif ?>
    <?php if ( \BooklyLite\Lib\Config::isServiceExtrasEnabled() ) : ?>
        <div <?php if ( $step >= 2 ) : ?>class="active"<?php endif ?>>
            <?php echo $i ++ . '. ' . \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_step_extras' ) ?>
            <div class=step></div>
        </div>
    <?php endif ?>
    <div <?php if ( $step >= 3 ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ . '. ' . \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_step_time' ) ?>
        <div class=step></div>
    </div>
    <?php if ( \BooklyLite\Lib\Config::isRecurringAppointmentsEnabled() ) : ?>
        <div <?php if ( $step >= 4 ) : ?>class="active"<?php endif ?>>
            <?php echo $i ++ . '. ' . \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_step_repeat' ) ?>
            <div class=step></div>
        </div>
    <?php endif ?>
    <?php if ( $show_cart ) : ?>
        <div <?php if ( $step >= 5 ) : ?>class="active"<?php endif ?>>
            <?php echo $i ++ . '. ' . \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_step_cart' ) ?>
            <div class=step></div>
        </div>
    <?php endif ?>
    <div <?php if ( $step >= 6 ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ . '. ' . \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_step_details' ) ?>
        <div class=step></div>
    </div>
    <?php if ( $payment_disabled == false ) : ?>
        <div <?php if ( $step >= 7 ) : ?>class="active"<?php endif ?>>
            <?php echo $i ++ . '. ' . \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_step_payment' ) ?>
            <div class=step></div>
        </div>
    <?php endif ?>
    <div <?php if ( $step >= 8 ) : ?>class="active"<?php endif ?>>
        <?php echo $i ++ . '. ' . \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_step_done' ) ?>
        <div class=step></div>
    </div>
</div>