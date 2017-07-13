<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="bookly-export-dialog" class="modal fade" tabindex=-1 role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <div class="modal-title h2"><?php _e( 'Export to CSV', 'bookly' ) ?></div>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="bookly-csv-delimiter"><?php _e( 'Delimiter', 'bookly' ) ?></label>
                    <select id="bookly-csv-delimiter" class="form-control">
                        <option value=","><?php _e( 'Comma (,)', 'bookly' ) ?></option>
                        <option value=";"><?php _e( 'Semicolon (;)', 'bookly' ) ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <div class="checkbox"><label><input checked value="0" type="checkbox" /><?php _e( 'No.', 'bookly' ) ?></label></div>
                    <div class="checkbox"><label><input checked value="1" type="checkbox" /><?php _e( 'Booking Time', 'bookly' ) ?></label></div>
                    <div class="checkbox"><label><input checked value="2" type="checkbox" /><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_employee' ) ?></label></div>
                    <div class="checkbox"><label><input checked value="3" type="checkbox" /><?php _e( 'Customer Name', 'bookly' ) ?></label></div>
                    <div class="checkbox"><label><input checked value="4" type="checkbox" /><?php _e( 'Customer Phone', 'bookly' ) ?></label></div>
                    <div class="checkbox"><label><input checked value="5" type="checkbox" /><?php _e( 'Customer Email', 'bookly' ) ?></label></div>
                    <div class="checkbox"><label><input checked value="6" type="checkbox" /><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_service' ) ?></label></div>
                    <div class="checkbox"><label><input checked value="7" type="checkbox" /><?php _e( 'Duration', 'bookly' ) ?></label></div>
                    <div class="checkbox"><label><input checked value="8" type="checkbox" /><?php _e( 'Status', 'bookly' ) ?></label></div>
                    <div class="checkbox"><label><input checked value="9" type="checkbox" /><?php _e( 'Payment', 'bookly' ) ?></label></div>
                    <?php $i = 10; foreach ( $custom_fields as $custom_field ) : ?>
                        <div class="checkbox"><label><input checked value="<?php echo $i ++ ?>" type="checkbox"/><?php echo $custom_field->label ?></label></div>
                    <?php endforeach ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-lg btn-success" id="bookly-export" data-dismiss="modal">
                    <?php _e( 'Export to CSV', 'bookly' ) ?>
                </button>
            </div>
        </div>
    </div>
</div>