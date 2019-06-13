<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
?>
<form id="bookly-delete-dialog" class="modal fade" tabindex=-1>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <div class="modal-title h2"><?php esc_html_e( 'Delete customers', 'bookly' ) ?></div>
            </div>
            <div class="modal-body">
                <p class="bookly-js-delete-with-events"><?php esc_html_e( 'You are going to delete customers with existing bookings. Notifications will not be sent to them.', 'bookly' ) ?></p>
                <p class="bookly-js-delete-without-events"><?php esc_html_e( 'You are going to delete customers, are you sure?', 'bookly' ) ?></p>
                <div class="bookly-js-delete-with-events bookly-margin-bottom-sm collapse">
                    <div class="checkbox">
                        <label>
                            <input class="bookly-js-delete-with-events-checkbox" type="checkbox"/><?php esc_html_e( 'Delete customers with existing bookings', 'bookly' ) ?>
                        </label>
                    </div>
                </div>
                <div>
                    <div class="checkbox">
                        <label>
                            <input class="bookly-js-delete-with-wp-user-checkbox" type="checkbox"/><?php esc_html_e( 'Delete customers\' WordPress accounts if there are any', 'bookly' ) ?>
                        </label>
                    </div>
                </div>
                <div>
                    <div class="checkbox">
                        <label>
                            <input class="bookly-js-remember-choice-checkbox" type="checkbox"/><?php esc_html_e( 'Remember my choice', 'bookly' ) ?>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <?php Buttons::renderCustom( null, 'btn-danger ladda-button bookly-js-delete btn-lg', esc_html__( 'Delete', 'bookly' ) ) ?>
                <?php Buttons::renderCustom( null, 'btn-default btn-lg', esc_html__( 'Cancel', 'bookly' ), array( 'data-dismiss' => 'modal' ) ) ?>
            </div>
        </div>
    </div>
</form>