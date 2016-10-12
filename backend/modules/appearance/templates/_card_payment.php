<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="ab-row bookly-table">
    <div class="ab-formGroup" style="width:200px!important">
        <label>
            <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_ccard_number' ) ) ?>" class="ab_editable editable editable-click inline-block" id="ab-text-label-ccard-number" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_label_ccard_number' ) ) ?></span>
        </label>
        <div>
            <input type="text" />
        </div>
    </div>
    <div class="ab-formGroup">
        <label>
            <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_ccard_expire' ) ) ?>" class="ab_editable editable editable-click inline-block" id="ab-text-label-ccard-expire" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_label_ccard_expire' ) ) ?></span>
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
<div class="ab-row ab-clearBottom">
    <div class="ab-formGroup">
        <label>
            <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_ccard_code' ) ) ?>" class="ab_editable editable editable-click inline-block" id="ab-text-label-ccard-code" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_label_ccard_code' ) ) ?></span>
        </label>
        <div>
            <input class="ab-card-cvc" type="text" />
        </div>
    </div>
</div>