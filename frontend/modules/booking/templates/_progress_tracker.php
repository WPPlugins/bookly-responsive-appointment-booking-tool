<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="ab-progress-tracker<?php if ( $payment_disabled ) echo ' ab-progress-tracker-four-steps'?>">
    <ul class=ab-progress-bar>
        <li class="ab-step-tabs<?php if ( $booking_step >= 1 ): ?> active<?php endif ?> ab-first">
            <a href="javascript:void(0)">1. <?php echo esc_html( get_option( 'ab_appearance_text_step_service' ) ) ?></a>
            <div class=step></div>
        </li>
        <li class="ab-step-tabs<?php if ( $booking_step >= 2 ): ?> active<?php endif ?>">
            <a href="javascript:void(0)">2. <?php echo esc_html( get_option( 'ab_appearance_text_step_time' ) ) ?></a>
            <div class=step></div>
        </li>
        <li class="ab-step-tabs<?php if ( $booking_step >= 3 ): ?> active<?php endif ?>">
            <a href="javascript:void(0)">3. <?php echo esc_html( get_option( 'ab_appearance_text_step_details' ) ) ?></a>
            <div class=step></div>
        </li>
        <?php if ( $payment_disabled ): ?>
            <li class="ab-step-tabs<?php if ( $booking_step >= 4 ): ?> active<?php endif ?> ab-last">
                <a href="javascript:void(0)">4. <?php echo esc_html( get_option( 'ab_appearance_text_step_done' ) ) ?></a>
                <div class=step></div>
            </li>
        <?php else: ?>
            <li class="ab-step-tabs<?php if ( $booking_step >= 4 ): ?> active<?php endif ?>">
                <a href="javascript:void(0)">4. <?php echo esc_html( get_option( 'ab_appearance_text_step_payment' ) ) ?></a>
                <div class=step></div>
            </li>
            <li class="ab-step-tabs<?php if ( $booking_step >= 5 ): ?> active<?php endif ?> ab-last">
                <a href="javascript:void(0)">5. <?php echo esc_html( get_option( 'ab_appearance_text_step_done' ) ) ?></a>
                <div class=step></div>
            </li>
        <?php endif ?>
    </ul>
</div>