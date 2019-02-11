<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
?>
<div id=bookly-test-email-notifications-modal class="modal fade" tabindex=-1 role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <div class="modal-title h2"><?php esc_html_e( 'Test email notifications', 'bookly' ) ?></div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="bookly_test_to_email"><?php esc_html_e( 'To email', 'bookly' ) ?></label>
                                <input id="bookly_test_to_email" class="form-control" type="text" name="to_email" value="admin@example.com"/>
                            </div>
                        </div>
                    </div>
                    <?php self::renderTemplate( '_common_settings', array( 'tail' => '_test' ) ) ?>
                    <div>
                        <div class="btn-group bookly-margin-bottom-lg">
                            <button class="btn btn-default btn-block dropdown-toggle bookly-flexbox" data-toggle="dropdown">
                                <div class="bookly-flex-cell text-left" style="width: 100%">
                                    <?php esc_html_e( 'Notification templates', 'bookly' ) ?>
                                    (<span class="bookly-js-count">0</span>)
                                </div>
                                <div class="bookly-flex-cell">
                                    <div class="bookly-margin-left-md"><span class="caret"></span></div>
                                </div>
                            </button>
                            <ul class="dropdown-menu" style="width: 570px">
                                <li class="bookly-padding-horizontal-md">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" id="bookly-check-all-entities"/>
                                            <?php esc_html_e( 'All templates', 'bookly' ) ?>
                                        </label>
                                    </div>
                                </li>
                                <li role="separator" class="divider"></li>
                                <li id="bookly-js-test-notifications-list"></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <?php Inputs::renderCsrf() ?>
                    <?php Buttons::renderCustom( null, 'btn-lg btn-success', esc_attr__( 'Send', 'bookly' ) ) ?>
                </div>
            </form>
        </div>
    </div>
</div>