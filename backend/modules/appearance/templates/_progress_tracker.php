<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="ab-progress-tracker">
    <ul class="ab-progress-bar nav-3">
        <li class="ab-step-tabs ab-first active">
            <a href="javascript:void(0)">1. <span data-default="<?php echo get_option( 'ab_appearance_text_step_service' ); ?>" data-link-class="text_step_1" class="text_service ab_editable" id="ab-text-step-service" data-type="text" data-pk="1"><?php echo esc_html(get_option( 'ab_appearance_text_step_service' )) ?></span></a>
            <div class="step"></div>
        </li>
        <li class="ab-step-tabs<?php if ($step >= 2): ?> active<?php endif ?>">
            <a href="javascript:void(0)">2. <span data-default="<?php echo get_option( 'ab_appearance_text_step_time' ); ?>" data-link-class="text_step_2" class="text_time text_step_2 ab_editable" id="ab-text-step-time" data-type="text" data-pk="1"><?php echo esc_html(get_option( 'ab_appearance_text_step_time' )) ?></span></a>
            <div class="step"></div>
        </li>
        <li class="ab-step-tabs<?php if ($step >= 3): ?> active<?php endif ?>">
            <a href="javascript:void(0)">3. <span data-default="<?php echo get_option( 'ab_appearance_text_step_details' ); ?>" data-link-class="text_step_3" class="text_details text_step_3 ab_editable" id="ab-text-step-details" data-type="text" data-pk="1"><?php echo esc_html(get_option( 'ab_appearance_text_step_details' )) ?></span></a>
            <div class="step"></div>
        </li>
        <li class="ab-step-tabs<?php if ($step >= 4): ?> active<?php endif ?>">
            <a href="javascript:void(0)">4. <span data-default="<?php echo get_option( 'ab_appearance_text_step_payment' ); ?>" data-link-class="text_step_4" class="text_payment ab_editable" id="ab-text-step-payment" data-type="text" data-pk="1"><?php echo esc_html(get_option( 'ab_appearance_text_step_payment' )) ?></span></a>
            <div class="step"></div>
        </li>
        <li class="ab-step-tabs ab-last<?php if ($step >= 5): ?> active<?php endif ?>">
            <a href="javascript:void(0)">5. <span data-default="<?php echo get_option( 'ab_appearance_text_step_done' ); ?>" data-link-class="text_step_5" class="text_done ab_editable" id="ab-text-step-done" data-type="text" data-pk="1"><?php echo esc_html(get_option( 'ab_appearance_text_step_done' )) ?></span></a>
            <div class="step"></div>
        </li>
    </ul>
</div>