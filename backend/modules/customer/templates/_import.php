<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="ab_import_customers_dialog" class="modal hide fade" tabindex=-1 role="dialog" aria-labelledby="importCustomersModalLabel" aria-hidden="true">
    <div class="dialog-content">
        <form class="form-horizontal" enctype="multipart/form-data" action="?page=ab-system-customers" method="POST">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="importCustomersModalLabel"><?php _e( 'Import', 'ab' ) ?></h3>
            </div>
            <div class="modal-body">
                <div class="control-group">
                    <label class="control-label"><?php _e( 'Note' , 'ab' ) ?></label>
                    <div class="controls">
                        <?php _e( 'You may import list of clients in CSV format. The file needs to have three columns: Name, Phone and Email.' , 'ab' ) ?>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label"><?php _e( 'Select file' , 'ab' ) ?></label>
                    <div class="controls">
                        <input name="import_customers_file" type="file">
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label"><?php _e( 'Delimiter' , 'ab' ) ?></label>
                    <div class="controls">
                        <select name="import_customers_delimiter">
                            <option value=","><?php _e( 'Comma (,)', 'ab' ) ?></option>
                            <option value=";"><?php _e( 'Semicolon (;)', 'ab' ) ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="ab-modal-button">
                    <input type="hidden" name="import">
                    <input type="submit" class="btn btn-info ab-popup-save ab-update-button" value="<?php _e( 'Import' , 'ab' ) ?>" />
                    <button class="ab-reset-form" data-dismiss="modal" aria-hidden="true"><?php _e( 'Cancel' , 'ab' ) ?></button>
                </div>
            </div>
        </form>
    </div>
</div>