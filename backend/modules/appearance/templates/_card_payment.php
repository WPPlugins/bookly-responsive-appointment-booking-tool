<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="bookly-box bookly-table">
    <div class="ab-formGroup" style="width:200px!important">
        <label>
            <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_label_ccard_number', ) ) ?>
        </label>
        <div>
            <input type="text" />
        </div>
    </div>
    <div class="ab-formGroup">
        <label>
            <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_label_ccard_expire', ) ) ?>
        </label>
        <div>
            <select class="ab-card-exp">
                <?php for ( $i = 1; $i <= 12; ++ $i ) : ?>
                    <option value="<?php echo $i ?>"><?php printf( '%02d', $i ) ?></option>
                <?php endfor ?>
            </select>
            <select class="ab-card-exp">
                <?php for ( $i = date( 'Y' ); $i <= date( 'Y' ) + 10; ++ $i ) : ?>
                    <option value="<?php echo $i ?>"><?php echo $i ?></option>
                <?php endfor ?>
            </select>
        </div>
    </div>
</div>
<div class="bookly-box ab-clearBottom">
    <div class="ab-formGroup">
        <label>
            <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_label_ccard_code', ) ) ?>
        </label>
        <div>
            <input class="ab-card-cvc" type="text" />
        </div>
    </div>
</div>