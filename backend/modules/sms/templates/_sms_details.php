<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Utils\DateTime;
?>
<div class="form-inline bookly-margin-bottom-xlg bookly-relative">
    <div class="form-group">
        <button type="button" id="sms_date_range" class="btn btn-block btn-default" data-date="<?php echo date( 'Y-m-d', strtotime( '-30 days' ) ) ?> - <?php echo date( 'Y-m-d' ) ?>">
            <i class="dashicons dashicons-calendar-alt"></i>
            <input type="hidden" name="form-purchases">
            <span>
                <?php echo DateTime::formatDate( '-30 days' ) ?> - <?php echo DateTime::formatDate( 'today' ) ?>
            </span>
        </button>
    </div>
</div>

<table id="bookly-sms" class="table table-striped" width="100%">
    <thead>
    <tr>
        <th><?php esc_html_e( 'Date', 'bookly' ) ?></th>
        <th><?php esc_html_e( 'Time', 'bookly' ) ?></th>
        <th><?php esc_html_e( 'Text', 'bookly' ) ?></th>
        <th><?php esc_html_e( 'Phone', 'bookly' ) ?></th>
        <th><?php esc_html_e( 'Sender ID', 'bookly' ) ?></th>
        <th><?php esc_html_e( 'Cost', 'bookly' ) ?></th>
        <th><?php esc_html_e( 'Status', 'bookly' ) ?></th>
        <th><?php esc_html_e( 'Info', 'bookly' ) ?></th>
    </tr>
    </thead>
</table>
