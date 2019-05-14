<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Modules\Services\Proxy;
use Bookly\Lib;

?>
<div id="bookly-edit-service-modal" class="modal fade" tabindex=-1 role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                    <div class="modal-title h2"><?php esc_html_e( 'Edit service', 'bookly' ) ?></div>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs nav-justified bookly-nav-justified bookly-js-service-tabs">
                        <li class="active">
                            <a id="bookly-services-general-tab" href="#bookly-services-general" data-toggle="tab">
                                <i class="fa fa-cog fa-fw"></i>
                                <span class="bookly-nav-tabs-title"><?php _e( 'General', 'bookly' ) ?></span>
                            </a>
                        </li>
                        <li class="bookly-js-service bookly-js-service-simple">
                            <a id="bookly-services-time-tab" href="#bookly-services-time" data-toggle="tab">
                                <i class="fa fa-clock fa-fw"></i>
                                <span class="bookly-nav-tabs-title"><?php _e( 'Time', 'bookly' ) ?></span>
                            </a>
                        </li>
                        <?php if ( Lib\Config::proActive() ) : ?>
                            <li class="bookly-js-service bookly-js-service-simple bookly-js-service-collaborative bookly-js-service-compound">
                                <a id="bookly-services-advanced-tab" href="#bookly-services-advanced" data-toggle="tab">
                                    <i class="fa fa-cogs fa-fw"></i>
                                    <span class="bookly-nav-tabs-title"><?php _e( 'Advanced', 'bookly' ) ?></span>
                                </a>
                            </li>
                        <?php endif ?>
                        <?php Proxy\ServiceExtras::renderTab() ?>
                        <?php Proxy\ServiceSchedule::renderTab() ?>
                        <?php if ( Lib\Config::serviceScheduleActive() ) : ?>
                            <?php Proxy\ServiceSpecialDays::renderTab() ?>
                        <?php endif ?>
                    </ul>
                    <div class="bookly-js-service-containers tab-content">
                        <div style="display: none;" class="bookly-loading"></div>

                        <div class="tab-pane active" id="bookly-services-general">
                            <div id="bookly-services-general-container"></div>
                        </div>
                        <div class="tab-pane" id="bookly-services-advanced">
                            <div id="bookly-services-advanced-container"></div>
                        </div>
                        <div class="tab-pane" id="bookly-services-time">
                            <div id="bookly-services-time-container"></div>
                        </div>
                        <div class="tab-pane" id="bookly-services-extras">
                            <div id="bookly-services-extras-container"></div>
                        </div>
                        <div class="tab-pane" id="bookly-services-schedule">
                            <div id="bookly-services-schedule-container"></div>
                        </div>
                        <div class="tab-pane" id="bookly-services-special-days">
                            <div id="bookly-services-special-days-container"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <span class="bookly-js-service-error text-danger pull-left text-left"></span>
                    <input type="hidden" name="action" value="bookly_update_service"/>
                    <input type="hidden" name="id"/>
                    <input type="hidden" name="type"/>
                    <input type="hidden" name="update_staff" value="0"/>
                    <?php Inputs::renderCsrf() ?>
                    <?php Buttons::renderSubmit() ?>
                    <?php Buttons::renderCustom( null, 'btn-lg btn-default', esc_html__( 'Close', 'bookly' ), array( 'data-dismiss' => 'modal' ) ) ?>
                </div>
            </form>
        </div>
    </div>
    <div class="collapse" id="bookly-service-additional-html"></div>
</div>