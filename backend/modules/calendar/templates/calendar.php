<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Utils\Common;
use Bookly\Backend\Components;
use Bookly\Backend\Modules as Backend;
use Bookly\Backend\Modules\Calendar\Proxy;
/** @var Bookly\Lib\Entities\Staff[] $staff_members */
?>
<style>
    .fc-slats tr { height: <?php echo max( 21, (int) ( 0.43 * get_option( 'bookly_gen_time_slot_length' ) ) ) ?>px; }
    .fc-time-grid-event.fc-short .fc-time::after { content: '' !important; }
</style>
<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body">
        <div class="page-header text-right clearfix">
            <div class="bookly-page-title">
                <?php _e( 'Calendar', 'bookly' ) ?>
            </div>
            <?php if ( Common::isCurrentUserAdmin() ) : ?>
                <?php Components\Support\Buttons::render( $self::pageSlug() ) ?>
            <?php endif ?>
        </div>
        <div class="panel panel-default bookly-main bookly-fc-inner">
            <div class="panel-body">
                <?php if ( $staff_members ) : ?>
                <ul class="bookly-nav bookly-nav-tabs">
                    <?php if ( Common::isCurrentUserAdmin() ) : ?>
                        <li class="bookly-nav-item bookly-js-calendar-tab" data-staff_id="0">
                            <?php _e( 'All', 'bookly' ) ?>
                        </li>
                    <?php endif ?>
                    <?php foreach ( $staff_members as $staff ) : ?>
                        <li class="bookly-nav-item bookly-js-calendar-tab" data-staff_id="<?php echo $staff->getId() ?>" style="display: none">
                            <?php echo esc_html( $staff->getFullName() ) ?>
                        </li>
                    <?php endforeach ?>
                    <?php if ( Common::isCurrentUserAdmin() ) : ?>
                        <div class="btn-group pull-right bookly-margin-top-xs">
                            <button class="btn btn-default dropdown-toggle bookly-js-staff-filter bookly-flexbox" data-toggle="dropdown">
                                <div class="bookly-flex-cell"><i class="dashicons dashicons-admin-users bookly-margin-right-md"></i></div>
                                <div class="bookly-flex-cell text-left"><span id="bookly-staff-button"></span></div>
                                <div class="bookly-flex-cell"><div class="bookly-margin-left-md"><span class="caret"></span></div></div>
                            </button>
                            <ul class="dropdown-menu bookly-entity-selector">
                                <li>
                                    <a class="checkbox" href="javascript:void(0)">
                                        <label><input type="checkbox" id="bookly-check-all-entities"><?php _e( 'All staff', 'bookly' ) ?></label>
                                    </a>
                                </li>
                                <?php foreach ( $staff_members as $staff ) : ?>
                                    <li>
                                        <a class="checkbox" href="javascript:void(0)">
                                            <label>
                                                <input type="checkbox" id="bookly-filter-staff-<?php echo $staff->getId() ?>" value="<?php echo $staff->getId() ?>" data-staff_name="<?php echo esc_attr( $staff->getFullName() ) ?>" class="bookly-js-check-entity">
                                                <?php echo esc_html( $staff->getFullName() ) ?>
                                            </label>
                                        </a>
                                    </li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                    <?php endif ?>
                    <?php Proxy\Locations::renderCalendarLocationFilter() ?>
                    <?php Proxy\AdvancedGoogleCalendar::renderSyncButton( $staff_members ) ?>
                </ul>
                <?php endif ?>
                <div class="bookly-margin-top-xlg">
                    <?php if ( $staff_members ) : ?>
                        <div class="fc-loading-inner" style="display: none">
                            <div class="fc-loading"></div>
                        </div>
                        <div id="bookly-fc-wrapper" class="bookly-calendar">
                            <div class="bookly-js-calendar-element"></div>
                        </div>
                        <?php Components\Dialogs\Appointment\Edit\Dialog::render() ?>
                        <?php Proxy\Shared::renderAddOnsComponents() ?>
                    <?php elseif( Bookly\Lib\Config::proActive() ) : ?>
                        <?php Components\Notices\Proxy\Pro::renderWelcome() ?>
                    <?php else : ?>
                        <div class="well">
                            <div class="h1"><?php esc_html_e( 'Welcome to Bookly and thank you for your choice!', 'bookly' ) ?></div>
                            <h3><?php esc_html_e( 'Bookly will simplify the booking process for your customers. This plugin creates another touchpoint to convert your visitors into customers. With Bookly your clients can see your availability, pick the services you provide, book them online and much more.', 'bookly' ) ?></h3>
                            <p><?php esc_html_e( 'To start using Bookly, you need to set up the services you provide and specify the staff members who will provide those services.', 'bookly' ) ?></p>
                            <ol>
                                <li><?php esc_html_e( 'Add a staff member (you can add only one service provider with a free version of Bookly).', 'bookly' ) ?></li>
                                <li><?php esc_html_e( 'Add services you provide (up to five with a free version of Bookly) and assign them to a staff member.', 'bookly' ) ?></li>
                                <li><?php esc_html_e( 'Go to Posts/Pages and click on the “Add Bookly booking form” button in the page editor to publish the booking form on your website.', 'bookly' ) ?></li>
                            </ol>
                            <p><?php printf( __( 'Bookly can boost your sales and scale together with your business. Get more features and remove the limits by upgrading to the paid version with the <a href="%s" target="_blank">Bookly Pro add-on</a>, which allows you to use a vast number of additional features and settings for booking services, install other add-ons for Bookly, and includes six months of customer support.', 'bookly' ), Common::prepareUrlReferrers( 'https://codecanyon.net/item/bookly/7226091?ref=ladela', 'welcome' ) ) ?></p>
                            <hr>
                            <a class="btn btn-success" href="<?php echo Common::escAdminUrl( Backend\Staff\Ajax::pageSlug() ) ?>">
                                <?php esc_html_e( 'Add Staff Members', 'bookly' ) ?>
                            </a>
                            <a class="btn btn-success" href="<?php echo Common::escAdminUrl( Backend\Services\Ajax::pageSlug() ) ?>">
                                <?php esc_html_e( 'Add Services', 'bookly' ) ?>
                            </a>
                            <a class="btn btn-success" href="<?php echo Common::prepareUrlReferrers( 'https://codecanyon.net/item/bookly/7226091?ref=ladela', 'welcome' ) ?>" target="_blank">
                                <?php esc_html_e( 'Try Bookly Pro add-on', 'bookly' ) ?>
                            </a>
                        </div>
                    <?php endif ?>
                </div>
            </div>
        </div>

        <?php Components\Dialogs\Appointment\Delete\Dialog::render() ?>
    </div>
</div>