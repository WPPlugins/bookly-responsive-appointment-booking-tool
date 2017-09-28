<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<script type="text/ng-template" id="bookly-customer-dialog.tpl">
<div id="bookly-customer-dialog" class="modal fade" tabindex=-1 role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <div class="modal-title h2"><?php _e( 'New Customer', 'bookly' ) ?></div>
            </div>
            <div ng-show=loading class="modal-body">
                <div class="bookly-loading"></div>
            </div>
            <div class="modal-body" ng-hide="loading">
                <div class="form-group">
                    <label for="wp_user"><?php _e( 'User', 'bookly' ) ?></label>
                    <select ng-model="form.wp_user_id" class="form-control" id="wp_user">
                        <option value=""></option>
                        <?php foreach ( get_users( array( 'fields' => array( 'ID', 'display_name' ), 'orderby' => 'display_name' ) ) as $wp_user ) : ?>
                            <option value="<?php echo $wp_user->ID ?>">
                                <?php echo $wp_user->display_name ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="username"><?php _e( 'Name', 'bookly' ) ?></label>
                    <input class="form-control" type="text" ng-model="form.name" id="username">
                    <span style="font-size: 11px;color: red" ng-show="errors.name.required"><?php _e( 'Required', 'bookly' ) ?></span>
                </div>

                <div class="form-group">
                    <label for="phone"><?php _e( 'Phone', 'bookly' ) ?></label>
                    <input class="form-control" type="text" ng-model=form.phone id="phone">
                </div>

                <div class="form-group">
                    <label for="email"><?php _e( 'Email', 'bookly' ) ?></label>
                    <input class="form-control" type="text" ng-model=form.email id="email">
                </div>

                <div class="form-group">
                    <label for="notes"><?php _e( 'Notes', 'bookly' ) ?></label>
                    <textarea class="form-control" ng-model=form.notes id="notes"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <div ng-hide=loading>
                    <?php \BooklyLite\Lib\Utils\Common::customButton( null, 'btn-success btn-lg', __( 'Create customer', 'bookly' ), array( 'ng-click' => 'processForm()' ) ) ?>
                    <?php \BooklyLite\Lib\Utils\Common::customButton( null, 'btn-default btn-lg', __( 'Cancel', 'bookly' ), array( 'data-dismiss' => 'modal' ) ) ?>
                </div>
            </div>
        </div>
    </div>
</div>
</script>