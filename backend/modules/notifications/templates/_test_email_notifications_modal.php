<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div ng-controller=testEmailNotificationsDialogCtrl>
    <div id=ab_test_email_notifications_dialog class="modal fade" tabindex=-1 role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div ng-show=loading class="bookly-loading"></div>

                <div ng-hide=loading>
                    <form ng-submit="testEmailNotifications()">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <div class="modal-title h2"><?php _e( 'Test Email Notifications', 'bookly' ) ?></div>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="bookly_test_to_email"><?php _e( 'To email', 'bookly' ) ?></label>
                                <input id="bookly_test_to_email" class="form-control" type="text" ng-model="toEmail"/>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="bookly_test_sender_name"><?php _e( 'Sender name', 'bookly' ) ?></label>
                                        <input id="bookly_test_sender_name" class="form-control" type="text" ng-model="dataSource.sender_name" />
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="bookly_test_sender_email"><?php _e( 'Sender email', 'bookly' ) ?></label>
                                        <input id="bookly_test_sender_email" class="form-control" type="text" ng-model="dataSource.sender_email" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="bookly_test_reply_to_customers"><?php _e( 'Reply directly to customers', 'bookly' ) ?></label>
                                        <select id="bookly_test_reply_to_customers" class="form-control" ng-model="dataSource.reply_to_customers">
                                            <option value="0"><?php _e( 'Disabled', 'bookly' ) ?></option>
                                            <option value="1"><?php _e( 'Enabled', 'bookly' ) ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="bookly_test_send_as"><?php _e( 'Send emails as', 'bookly' ) ?></label>
                                        <select id="bookly_test_send_as" class="form-control" ng-model="dataSource.send_as">
                                            <option value="html"><?php _e( 'HTML', 'bookly' ) ?></option>
                                            <option value="text"><?php _e( 'Text', 'bookly' ) ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="btn-group bookly-margin-bottom-lg">
                                    <button class="btn btn-default btn-block dropdown-toggle bookly-flexbox" data-toggle="dropdown">
                                        <div class="bookly-flex-cell text-left" style="width: 100%">
                                            <?php _e( 'Notification templates', 'bookly' ) ?> ({{selectedNotificationsCount()}})
                                        </div>
                                        <div class="bookly-flex-cell"><div class="bookly-margin-left-md"><span class="caret"></span></div></div>
                                    </button>
                                    <ul class="dropdown-menu" style="width: 570px">
                                        <li class="bookly-padding-horizontal-md">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" id="bookly-check-all-entities" ng-model="allNotifications" ng-change="toggleAllNotifications()" />
                                                    <?php _e( 'All templates', 'bookly' ) ?>
                                                </label>
                                            </div>
                                        </li>
                                        <li role="separator" class="divider"></li>

                                        <li class="bookly-padding-horizontal-md" ng-repeat="notification in dataSource.notifications">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" ng-model="notification.active"
                                                           ng-true-value="'1'" ng-false-value="'0'" ng-change="notificationChecked()" />
                                                    {{notification.name}}
                                                </label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <?php \BooklyLite\Lib\Utils\Common::submitButton( '', '', __( 'Send', 'bookly' ) ) ?>
                        </div>
                    </form>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div class="modal fade" id="ab--modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="<?php esc_attr_e( 'Close', 'bookly' ) ?>"><span aria-hidden="true">&times;</span></button>
                    <div class="modal-title h2"></div>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <?php _e( 'Close', 'bookly' ) ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>