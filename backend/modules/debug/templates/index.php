<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Components\Controls\Buttons;
?>
<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body">
        <div class="page-header text-right clearfix">
            <div class="bookly-page-title">
                Data management
            </div>
        </div>
        <?php if ( $import_status ) : ?>
            <div class="alert alert-success">
                Data successfully imported
            </div>
        <?php endif ?>

        <div class="panel-group" id="data-management">
            <div class="bookly-data-button">
            <form action="<?php echo admin_url( 'admin-ajax.php?action=bookly_export_data' ) ?>" method="POST">
                <?php Inputs::renderCsrf() ?>
                <button id="bookly-export" type="submit" class="btn btn-lg btn-success">
                    <span class="ladda-label">Export data</span>
                </button>
            </form>
            </div>
            <div class="bookly-data-button">
            <form id="bookly_import" action="<?php echo admin_url( 'admin-ajax.php?action=bookly_import_data' ) ?>" method="POST" enctype="multipart/form-data">
                <?php Inputs::renderCsrf() ?>
                <div id="bookly-import" class="btn btn-lg btn-primary btn-file">
                    <span class="ladda-label">Import data</span>
                    <input type="file" id="bookly_import_file" name="import">
                </div>
            </form>
          </div>
        </div>
        <div class="page-header text-right clearfix">
            <div class="bookly-page-title">
                Database Integrity
            </div>
        </div>
        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
            <?php foreach ( $debug as $tableName => $table ) : ?>
                <div class="panel <?php echo $table['status'] == 1 ? 'panel-success' : 'panel-danger' ?>">
                    <div class="panel-heading" role="tab" id="heading_<?php echo $tableName ?>">
                        <h4 class="panel-title">
                            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#<?php echo $tableName ?>" aria-expanded="true" aria-controls="<?php echo $tableName ?>">
                                <?php echo $tableName ?>
                            </a>
                            <?php if ( ! $table['status'] ) : ?>
                                <button class="btn btn-success btn-xs pull-right" type="button" data-action="fix-create-table">create</button>
                            <?php endif ?>
                        </h4>
                    </div>
                    <div id="<?php echo $tableName ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="<?php echo $tableName ?>">
                        <div class="panel-body">
                            <?php if ( $table['status'] ) : ?>
                                <h4>Columns</h4>
                                <table class="table table-condensed">
                                    <thead>
                                    <tr>
                                        <th>Column name</th>
                                        <th width="50">Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ( $table['fields'] as $field => $status ) : ?>
                                        <tr class="<?php echo $status ? 'default' : 'danger' ?>">
                                            <td><?php echo $field ?></td>
                                            <td><?php echo $status ? 'OK' : '<button class="btn btn-success btn-xs" type="button" data-action="fix-column">FIX…</button>' ?></td>
                                        </tr>
                                    <?php endforeach ?>
                                    </tbody>
                                </table>
                                <?php if ( $table['constraints'] ) : ?>
                                    <h4>Constraints</h4>
                                    <table class="table table-condensed">
                                        <thead>
                                        <tr>
                                            <th>Column name</th>
                                            <th>Referenced table name</th>
                                            <th>Referenced column name</th>
                                            <th width="50">Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ( $table['constraints'] as $key => $constraint ) : ?>
                                            <tr class="<?php echo $constraint['status'] ? 'default' : 'danger' ?>">
                                                <td><?php echo $constraint['column_name'] ?></td>
                                                <td><?php echo $constraint['referenced_table_name'] ?></td>
                                                <td><?php echo $constraint['referenced_column_name'] ?></td>
                                                <td><?php echo $constraint['status'] ? 'OK' : '<button class="btn btn-success btn-xs" type="button" data-action="fix-constraint">FIX…</button>' ?></td>
                                            </tr>
                                        <?php endforeach ?>
                                        </tbody>
                                    </table>
                                <?php endif ?>
                            <?php else: ?>
                                Table does not exist
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>
    <div id="bookly-js-add-constraint" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Add constraint</h4>
                </div>
                <div class="modal-body">
                    <div class="bookly-js-loading" style="height: 120px;"></div>
                    <div class="bookly-js-loading">
                    <pre>
   ALTER TABLE `<span id="bookly-js-table"></span>`
ADD CONSTRAINT
   FOREIGN KEY (`<span id="bookly-js-column"></span>`)
    REFERENCES `<span id="bookly-js-ref_table"></span>` (`<span id="bookly-js-ref_column"></span>`)
     ON DELETE <select id="bookly-js-DELETE_RULE">
            <option></option>
            <option value="RESTRICT">RESTRICT</option>
            <option value="CASCADE">CASCADE</option>
            <option value="SET NULL">SET NULL</option>
            <option value="NO ACTIONS">NO ACTIONS</option>
            </select>
     ON UPDATE <select id="bookly-js-UPDATE_RULE">
            <option></option>
            <option value="RESTRICT">RESTRICT</option>
            <option value="CASCADE">CASCADE</option>
            <option value="SET NULL">SET NULL</option>
            <option value="NO ACTIONS">NO ACTIONS</option>
            </select></pre>
                </div>
                </div>
                <div class="modal-footer">
                    <div class="pull-left">
                        <div class="btn-group bookly-js-fix-consistency">
                            <button type="button" class="btn btn-lg btn-danger bookly-js-auto ladda-button" data-spinner-size="40" data-style="zoom-in" data-action="fix-consistency"><span class="ladda-label">Consistency…</span></button>
                            <button type="button" class="btn btn-lg btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="bookly-js-update" href="#" data-action="fix-consistency">UPDATE `<span class="bookly-js-ref_table"></span>` SET `<span class="bookly-js-ref_column"></span>` = NULL WHERE `<span class="bookly-js-ref_column"></span>` NOT IN (…)</a></li>
                                <li><a class="bookly-js-delete" href="#" data-action="fix-consistency">DELETE FROM `<span class="bookly-js-ref_table"></span>` WHERE `<span class="bookly-js-ref_column"></span>` NOT IN (…)</a></li>
                            </ul>
                        </div>
                    </div>
                    <?php Buttons::renderCustom( null, 'bookly-js-delete btn-lg btn-danger pull-left', 'Delete rows…', array( 'style' => 'display:none' ) ) ?>
                    <?php Buttons::renderCustom( null, 'bookly-js-save btn-lg btn-success', 'Add constraint' ) ?>
                    <?php Buttons::renderCustom( null, 'btn-lg btn-default', 'Close', array( 'data-dismiss' => 'modal' ) ) ?>
                </div>
            </div>
        </div>
    </div>
    <div id="bookly-js-add-field" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Add column</h4>
                </div>
                <div class="modal-body">
                    <div class="bookly-js-loading" style="height: 120px;"></div>
                    <div class="bookly-js-loading"><pre></pre></div>
                </div>
                <div class="modal-footer">
                    <?php Buttons::renderCustom( null, 'bookly-js-save btn-lg btn-success', 'Add column' ) ?>
                    <?php Buttons::renderCustom( null, 'btn-lg btn-default', 'Close', array( 'data-dismiss' => 'modal' ) ) ?>
                </div>
            </div>
        </div>
    </div>
    <div id="bookly-js-create-table" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Create table</h4>
                </div>
                <div class="modal-body">
                    <div class="bookly-js-loading" style="height: 120px;"></div>
                    <div class="bookly-js-loading"><pre></pre></div>
                </div>
                <div class="modal-footer">
                    <?php Buttons::renderCustom( null, 'bookly-js-save btn-lg btn-success', 'Create table' ) ?>
                    <?php Buttons::renderCustom( null, 'btn-lg btn-default', 'Close', array( 'data-dismiss' => 'modal' ) ) ?>
                </div>
            </div>
        </div>
    </div>
</div>