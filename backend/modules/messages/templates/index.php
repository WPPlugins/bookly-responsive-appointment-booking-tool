<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components;
?>
<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body">
        <div class="page-header text-right clearfix">
            <div class="bookly-page-title">
                <?php _e( 'Messages', 'bookly' ) ?>
            </div>
            <?php Components\Support\Buttons::render( $self::pageSlug() ) ?>
        </div>
        <div class="panel panel-default bookly-main">
            <div class="panel-body">
                <table id="bookly-messages-list" class="table table-striped" width="100%">
                    <thead>
                    <tr>
                        <th><?php _e( 'Date', 'bookly' ) ?></th>
                        <th><?php _e( 'Subject', 'bookly' ) ?></th>
                        <th><?php _e( 'Message', 'bookly' ) ?></th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>