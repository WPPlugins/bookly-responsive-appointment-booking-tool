<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components;
use Bookly\Backend\Components\Dashboard;
use Bookly\Backend\Modules\Dashboard\Proxy;
use Bookly\Lib\Utils\Common;
use Bookly\Lib\Utils\DateTime;
?>
<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body">
        <div class="page-header text-right">
            <div class="bookly-page-title">
                <?php esc_html_e( 'Dashboard', 'bookly' ) ?>
            </div>
            <?php if ( Common::isCurrentUserSupervisor() ) : ?>
                <?php Components\Support\Buttons::render( $self::pageSlug() ) ?>
            <?php endif ?>
        </div>
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="bookly-margin-bottom-lg bookly-relative">
                    <button type="button" class="btn btn-block btn-default" id="bookly-filter-date" data-date="<?php echo date( 'Y-m-d', strtotime( '-7 days' ) ) ?> - <?php echo date( 'Y-m-d' ) ?>">
                        <i class="dashicons dashicons-calendar-alt"></i>
                        <span>
                            <?php echo DateTime::formatDate( '-7 days' ) ?> - <?php echo DateTime::formatDate( 'today' ) ?>
                        </span>
                    </button>
                </div>
            </div>
            <div class="col-md-9 col-sm-6">
                <div class="h5">
                    <?php esc_html_e( 'See the number of appointments and total revenue for the selected period', 'bookly' ) ?>
                </div>
            </div>
        </div>
        <div class="panel panel-default bookly-main">
            <div class="panel-body">
                <?php Dashboard\Appointments\Widget::renderChart() ?>
                <?php Proxy\Pro::renderAnalytics() ?>
            </div>
        </div>
    </div>
</div>
