<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="ab-row-fluid">
    <div class="ab-formGroup ab-left">
        <label class="ab-formLabel"><?php _e( 'Credit Card Number', 'ab' ) ?></label>
        <div class="ab-formField">
            <input class="ab-formElement ab-full-name" type="text" name="ab_card_number">
        </div>
    </div>
    <div class="ab-formGroup ab-left" style="width: auto;">
        <label class="ab-formLabel"><?php _e( 'Expiration Date', 'ab' ) ?></label>
        <div class="ab-formField">
            <select class="ab-formElement ab-full-name" style="width: 40px;float: left;" name="ab_card_month">
                <?php for ( $i = 1; $i <= 12; ++ $i ): ?>
                    <option value="<?php echo $i ?>"><?php printf( '%02d', $i ) ?></option>
                <?php endfor; ?>
            </select>
            <select class="ab-formElement ab-full-name" style="width: 60px;float: left; margin-left: 10px;" name="ab_card_year">
                <?php for ( $i = date('Y'); $i <= date('Y')+10; ++ $i ): ?>
                    <option value="<?php echo $i ?>"><?php echo $i ?></option>
                <?php endfor ?>
            </select>
        </div>
    </div>
</div>
<div class="ab-row-fluid">
    <div class="ab-formGroup ab-full ab-left">
        <label class="ab-formLabel"><?php _e( 'Card Security Code', 'ab' ) ?></label>
        <div class="ab-formField">
            <input class="ab-formElement ab-full-name" style="width: 50px;float: left;" type="text" name="ab_card_code" />
        </div>
    </div>

</div><div class="ab-clear"></div>
<div class="ab-error ab-bold ab-card-error"></div>