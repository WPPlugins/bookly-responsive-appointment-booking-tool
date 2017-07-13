<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="bookly-box bookly-table">
    <div class="ab-formGroup" style="width:200px!important">
        <label><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_ccard_number' ) ?></label>
        <div>
            <input type="text" name="ab_card_number" autocomplete="off" />
        </div>
    </div>
    <div class="ab-formGroup">
        <label><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_ccard_expire' ) ?></label>
        <div>
            <select class="ab-card-exp" name="ab_card_exp_month">
                <?php for ( $i = 1; $i <= 12; ++ $i ) : ?>
                    <option value="<?php echo $i ?>"><?php printf( '%02d', $i ) ?></option>
                <?php endfor ?>
            </select>
            <select class="ab-card-exp" name="ab_card_exp_year">
                <?php for ( $i = date( 'Y' ); $i <= date( 'Y' ) + 10; ++ $i ) : ?>
                    <option value="<?php echo $i ?>"><?php echo $i ?></option>
                <?php endfor ?>
            </select>
        </div>
    </div>
</div>
<div class="bookly-box ab-clearBottom">
    <div class="ab-formGroup">
        <label><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_ccard_code' ) ?></label>
        <div>
            <input type="text" class="ab-card-cvc" name="ab_card_cvc" autocomplete="off" />
        </div>
    </div>
</div>
<div class="ab-label-error ab-card-error"></div>