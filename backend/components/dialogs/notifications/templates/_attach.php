<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Modules\Notifications\Proxy;
?>
<div class="bookly-js-attach-container">
    <div class="form-group bookly-js-attach bookly-js-ics">
        <input type="hidden" name="notification[attach_ics]" value="0">
        <div class="checkbox"><label for="notification_attach_ics">
                <input id="notification_attach_ics" name="notification[attach_ics]" type="checkbox" value="1"/> <?php esc_attr_e( 'Attach ICS file', 'bookly' ) ?>
            </label>
        </div>
    </div>
    <?php Proxy\Invoices::renderAttach() ?>
</div>