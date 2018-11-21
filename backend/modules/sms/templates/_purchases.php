<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="form-inline bookly-margin-bottom-xlg bookly-relative">
    <div class="form-group">
        <button type="button" id="purchases_date_range" class="btn btn-block btn-default" data-date="<?php echo date( 'Y-m-d', strtotime( '-30 days' ) ) ?> - <?php echo date( 'Y-m-d' ) ?>">
            <i class="dashicons dashicons-calendar-alt"></i>
            <input type="hidden" name="form-purchases">
            <span>
                <?php echo \Bookly\Lib\Utils\DateTime::formatDate( '-30 days' ) ?> - <?php echo \Bookly\Lib\Utils\DateTime::formatDate( 'today' ) ?>
            </span>
        </button>
    </div>
</div>

<table id="bookly-purchases" class="table table-striped" width="100%">
    <thead>
    <tr>
        <th><?php _e( 'Date', 'bookly' ) ?></th>
        <th><?php _e( 'Time', 'bookly' ) ?></th>
        <th><?php _e( 'Type', 'bookly' ) ?></th>
        <th><?php _e( 'Order', 'bookly' ) ?></th>
        <th><?php _e( 'Status', 'bookly' ) ?></th>
        <th><?php _e( 'Amount', 'bookly' ) ?></th>
    </tr>
    </thead>
</table>