<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
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
                <button id="bookly-export" type="submit" class="btn btn-lg btn-success">
                    <span class="ladda-label">Export data</span>
                </button>
            </form>
            </div>
            <div class="bookly-data-button">
            <form id="bookly_import" action="<?php echo admin_url( 'admin-ajax.php?action=bookly_import_data' ) ?>" method="POST" enctype="multipart/form-data">
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
                                            <td><?php echo $status ? 'OK' : 'ERROR' ?></td>
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
                                                <td><?php echo $constraint['status'] ? 'OK' : 'ERROR' ?></td>
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
</div>