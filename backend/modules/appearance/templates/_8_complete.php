<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use BooklyLite\Backend\Modules\Appearance\Components;
/** @var BooklyLite\Backend\Modules\Appearance\Lib\Helper $editable */
?>
<div class="bookly-form">
    <?php include '_progress_tracker.php' ?>
    <div class="bookly-box bookly-js-done-success">
        <?php $editable::renderText( 'bookly_l10n_info_complete_step', Components::getInstance()->renderCodes( array( 'step' => 8, 'extra_codes' => 1 ), false ) ) ?>
    </div>
    <div class="bookly-box bookly-js-done-limit-error collapse">
        <?php $editable::renderText( 'bookly_l10n_info_complete_step_limit_error', Components::getInstance()->renderCodes( array( 'step' => 8 ), false ) ) ?>
    </div>
    <div class="bookly-box bookly-js-done-processing collapse">
        <?php $editable::renderText( 'bookly_l10n_info_complete_step_processing', Components::getInstance()->renderCodes( array( 'step' => 8, 'extra_codes' => 1 ), false ) ) ?>
    </div>
</div>