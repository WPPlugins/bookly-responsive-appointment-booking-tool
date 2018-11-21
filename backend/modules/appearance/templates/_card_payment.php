<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Appearance\Editable;
?>
<div class="bookly-box bookly-table">
    <div class="bookly-form-group" style="width:200px!important">
        <label>
            <?php Editable::renderString( array( 'bookly_l10n_label_ccard_number', ) ) ?>
        </label>
        <div>
            <input type="text" />
        </div>
    </div>
    <div class="bookly-form-group">
        <label>
            <?php Editable::renderString( array( 'bookly_l10n_label_ccard_expire', ) ) ?>
        </label>
        <div>
            <select class="bookly-card-exp">
                <?php for ( $i = 1; $i <= 12; ++ $i ) : ?>
                    <option value="<?php echo $i ?>"><?php printf( '%02d', $i ) ?></option>
                <?php endfor ?>
            </select>
            <select class="bookly-card-exp">
                <?php for ( $i = date( 'Y' ); $i <= date( 'Y' ) + 10; ++ $i ) : ?>
                    <option value="<?php echo $i ?>"><?php echo $i ?></option>
                <?php endfor ?>
            </select>
        </div>
    </div>
</div>
<div class="bookly-box bookly-clear-bottom">
    <div class="bookly-form-group">
        <label>
            <?php Editable::renderString( array( 'bookly_l10n_label_ccard_code', ) ) ?>
        </label>
        <div>
            <input class="bookly-card-cvc" type="text" />
        </div>
    </div>
</div>