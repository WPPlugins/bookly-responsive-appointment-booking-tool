<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="ab_import_customers_dialog" class="modal fade" tabindex=-1 role="dialog" aria-labelledby="importCustomersModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="form-horizontal" enctype="multipart/form-data" action="<?php echo AB_Utils::escAdminUrl( AB_CustomerController::page_slug ) ?>" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php _e( 'Import', 'bookly' ) ?></h4>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="form-group">
                            <label><?php _e( 'Note', 'bookly' ) ?></label>
                            <?php _e( 'You may import list of clients in CSV format. The file needs to have three columns: Name, Phone and Email.', 'bookly' ) ?>
                        </div>
                        <div class="form-group">
                            <label for="import_customers_file"><?php _e( 'Select file', 'bookly' ) ?></label>
                            <input name="import_customers_file" id="import_customers_file" type="file">
                        </div>
                        <div class="form-group">
                            <label for="import_customers_delimiter"><?php _e( 'Delimiter', 'bookly' ) ?></label>
                            <select name="import_customers_delimiter" id="import_customers_delimiter" class="form-control">
                                <option value=","><?php _e( 'Comma (,)', 'bookly' ) ?></option>
                                <option value=";"><?php _e( 'Semicolon (;)', 'bookly' ) ?></option>
                            </select>
                        </div>
                    <input type="hidden" name="import">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info ab-popup-save" name="import-customers"><?php _e( 'Import', 'bookly' ) ?></button>
                    <button class="ab-reset-form" data-dismiss="modal" aria-hidden="true"><?php _e( 'Cancel', 'bookly' ) ?></button>
                </div>
            </div>
        </form>
    </div>
</div>