<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php _e( 'Services', 'bookly' ) ?></h3>
    </div>
    <div class="panel-body">
        <div class="ab-wrapper-container">
            <div class="row">
                <div class="ab-left-bar col-md-3 col-sm-3 col-xs-12 col-lg-3">
                    <div id="ab-categories-list">
                        <div class="ab-category-item ab-active ab-main-category-item" data-id=""><?php _e( 'All Services', 'bookly' ) ?></div>
                        <ul id="ab-category-item-list" class="ab-category-item-list">
                            <?php foreach ( $category_collection as $category ): ?>
                                <li class="ab-category-item" data-id="<?php echo $category['id'] ?>">
                                      <span class="ab-handle" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>">
                                        <i class="ab-inner-handle glyphicon glyphicon-align-justify"></i>
                                      </span>
                                      <span class="left displayed-value"><?php echo esc_html( $category['name'] ) ?></span>
                                      <a href="#" class="left ab-hidden ab-edit"></a>
                                      <input class="form-control value ab-value" type="text" name="name" value="<?php echo esc_attr( $category['name'] ) ?>" style="display: none" />
                                      <a href="#" class="left ab-hidden ab-delete"></a>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                    <input type="hidden" id="color" />
                    <div id="new_category_popup" class="ab-popup-wrapper">
                        <input class="btn btn-info ab-popup-trigger" type="submit" value="<?php _e( 'New Category', 'bookly' ) ?>" />
                        <div class="ab-popup" style="display: none; margin-top: 10px;">
                            <div class="ab-arrow"></div>
                            <div class="ab-content">
                                <form method="post" id="new-category-form">
                                    <table class="form-horizontal">
                                        <tr>
                                            <td>
                                                <input class="form-control ab-clear-text" style="width: 170px" type="text" name="name" />
                                                <input type="hidden" name="action" value="ab_category_form" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <?php AB_Utils::submitButton() ?>
                                                <a class="ab-popup-close" href="#"><?php _e( 'Cancel', 'bookly' ) ?></a>
                                            </td>
                                        </tr>
                                    </table>
                                    <a class="ab-popup-close ab-popup-close-icon" href="#"></a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ab-right-content col-md-9 col-sm-9 col-xs-12 col-lg-9" id="ab_services_wrapper">
                    <h2 class="ab-category-title"><?php _e( 'All Services', 'bookly' ) ?></h2>
                    <div class="no-result"<?php if ( ! empty ( $service_collection ) ) : ?> style="display: none"<?php endif ?>><?php _e( 'No services found. Please add services.', 'bookly' ) ?></div>
                    <div class="list-wrapper">
                        <div id="ab-services-list">
                            <?php include '_list.php' ?>
                        </div>
                        <div class="list-actions">
                            <a class="add-service btn btn-info" href="#"><?php _e( 'Add Service', 'bookly' ) ?></a>
                            <a class="delete btn btn-info" href="#"><?php _e( 'Delete', 'bookly' ) ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <div id="ab-staff-update" class="modal fade">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"><?php _e( 'Update service setting', 'bookly' ) ?></h4>
                        </div>
                        <div class="modal-body" style="white-space: normal">
                            <span class="help-block"><?php _e( 'You are about to change a service setting which is also configured separately for each staff member. Do you want to update it in staff settings too?', 'bookly' ) ?></span>
                            <label>
                                <input style="margin: 0" id="ab-remember-my-choice" type="checkbox" /> <?php _e( 'Remember my choice', 'bookly' ) ?>
                            </label>
                        </div>
                        <div class="modal-footer">
                            <button type="reset" class="btn btn-default ab-no" data-dismiss="modal" aria-hidden="true"><?php _e( 'No, update just here in services', 'bookly' ) ?></button>
                            <button type="submit" class="btn btn-primary ab-yes"><?php _e( 'Yes', 'bookly' ) ?></button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
        </div>
    </div>
</div>
<div class="modal fade" id="lite_notice" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php _e('Notice', 'bookly') ?></h4>
            </div>
            <div class="modal-body">
                <?php _e('This function is disabled in the lite version of Bookly. If you find the plugin useful for your business please consider buying a licence for the full version. It costs just $46 and for this money you will get many useful functions, lifetime free update and excellent support! More information can be found here', 'bookly'); ?>: <a href="http://booking-wp-plugin.com" target="_blank">http://booking-wp-plugin.com</a>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close', 'bookly') ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->