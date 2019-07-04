<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Modules\Staff\Proxy;
/** @var Bookly\Lib\Entities\Staff $staff */
?>
<?php if ( $staff->getId() ) : ?>
    <ul class="nav nav-tabs nav-justified bookly-nav-justified">
        <li class="active">
            <a id="bookly-details-tab" href="#details" data-toggle="tab">
                <i class="fa fa-cog fa-fw"></i>
                <span class="bookly-nav-tabs-title"><?php esc_html_e( 'Details', 'bookly' ) ?></span>
            </a>
        </li>
        <li>
            <a id="bookly-services-tab" href="#services" data-toggle="tab">
                <i class="fas fa-th fa-fw"></i>
                <span class="bookly-nav-tabs-title"><?php esc_html_e( 'Services', 'bookly' ) ?></span>
            </a>
        </li>
        <li>
            <a id="bookly-schedule-tab" href="#schedule" data-toggle="tab">
                <i class="fas fa-clock fa-fw"></i>
                <span class="bookly-nav-tabs-title"><?php esc_html_e( 'Schedule', 'bookly' ) ?></span>
            </a>
        </li>
        <?php Proxy\Shared::renderStaffTab( $staff ) ?>
        <li>
            <a id="bookly-holidays-tab" href="#daysoff" data-toggle="tab">
                <i class="fas fa-calendar fa-fw"></i>
                <span class="bookly-nav-tabs-title"><?php esc_html_e( 'Days off', 'bookly' ) ?></span>
            </a>
        </li>
    </ul>
<?php endif ?>

<div class="tab-content">
    <div style="display: none;" class="bookly-loading"></div>

    <div class="tab-pane active" id="details">
        <div id="bookly-details-container"></div>
    </div>
    <div class="tab-pane" id="services">
        <div id="bookly-services-container" style="display: none"></div>
    </div>
    <div class="tab-pane" id="schedule">
        <div id="bookly-schedule-container" style="display: none"></div>
    </div>
    <div class="tab-pane" id="special_days">
        <div id="bookly-special-days-container" style="display: none"></div>
    </div>
    <div class="tab-pane" id="daysoff">
        <div id="bookly-holidays-container" style="display: none"></div>
    </div>
</div>
