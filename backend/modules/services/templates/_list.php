<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Modules\Services\Proxy;
use Bookly\Lib\Utils\Common;
use Bookly\Lib\Utils\DateTime;
use Bookly\Lib\Utils\Price;
use Bookly\Lib\Entities\Service;

?>
<?php if ( ! empty( $service_collection ) ) : ?>
    <div class="panel-group" id="services_list" role="tablist" aria-multiselectable="true">
        <?php foreach ( $service_collection as $service ) : ?>
            <?php $service_id   = $service['id'];
            $assigned_staff_ids = $service['staff_ids'] ? explode( ',', $service['staff_ids'] ) : array();
            $all_staff_selected = count( $assigned_staff_ids ) == count( $staff_collection );
            ?>
            <div class="panel panel-default bookly-js-collapse" data-service-id="<?php echo $service_id ?>">
                <div class="panel-heading" role="tab" id="s_<?php echo $service_id ?>">
                    <div class="row">
                        <div class="col-sm-8 col-xs-10">
                            <div class="bookly-flexbox">
                                <div class="bookly-flex-cell bookly-vertical-middle" style="width: 1%">
                                    <i class="bookly-js-handle bookly-icon bookly-icon-draghandle bookly-margin-right-sm bookly-cursor-move"
                                       title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                                </div>
                                <div class="bookly-flex-cell bookly-vertical-middle bookly-js-service-color" style="width: 55px; padding-left: 25px;">
                                    <span class="bookly-service-color bookly-margin-right-sm bookly-js-service bookly-js-service-simple bookly-js-service-compound bookly-js-service-package"
                                          style="background-color: <?php echo esc_attr( $service['colors'][0] == '-1' ? 'grey' : $service['colors'][0] ) ?>">&nbsp;</span>
                                    <span class="bookly-service-color bookly-margin-right-sm bookly-js-service bookly-js-service-compound bookly-js-service-package"
                                          style="background-color: <?php echo esc_attr( $service['colors'][1] == '-1' ? 'grey' : $service['colors'][1] ) ?>; <?php if ( $service['type'] == Service::TYPE_SIMPLE ) : ?>display: none;<?php endif ?>">&nbsp;</span>
                                    <span class="bookly-service-color bookly-margin-right-sm bookly-js-service bookly-js-service-package"
                                          style="background-color: <?php echo esc_attr( $service['colors'][2] == '-1' ? 'grey' : $service['colors'][2] ) ?>; <?php if ( $service['type'] != Service::TYPE_PACKAGE ) : ?>display: none;<?php endif ?>">&nbsp;</span>
                                </div>
                                <div class="bookly-flex-cell bookly-vertical-middle">
                                    <a role="button" class="panel-title collapsed bookly-js-service-title" data-toggle="collapse"
                                       data-parent="#services_list" href="#service_<?php echo $service_id ?>"
                                       aria-expanded="false" aria-controls="service_<?php echo $service_id ?>">
                                        <?php echo esc_html( $service['title'] ) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-2">
                            <div class="bookly-flexbox">
                                <div class="bookly-flex-cell bookly-vertical-middle hidden-xs" style="width: 60%">
                                <span class="bookly-js-service-duration">
                                    <?php
                                        switch ( $service['type'] ) {
                                            case Service::TYPE_SIMPLE:
                                            case Service::TYPE_PACKAGE:
                                                echo DateTime::secondsToInterval( $service['duration'] ); break;
                                            case Service::TYPE_COMPOUND:
                                                echo sprintf( _n( '%d service', '%d services', $service['sub_services_count'], 'bookly' ), $service['sub_services_count'] ); break;
                                        }
                                    ?>
                                </span>
                                </div>
                                <div class="bookly-flex-cell bookly-vertical-middle hidden-xs" style="width: 30%">
                                <span class="bookly-js-service-price">
                                    <?php echo Price::format( $service['price'] ) ?>
                                </span>
                                </div>
                                <div class="bookly-flex-cell bookly-vertical-middle text-right" style="width: 10%">
                                    <div class="checkbox bookly-margin-remove">
                                        <label><input type="checkbox" class="service-checker" value="<?php echo $service_id ?>"/></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="service_<?php echo $service_id ?>" class="panel-collapse collapse" role="tabpanel" style="height: 0">
                    <div class="panel-body">
                        <form method="post">
                            <div class="form-inline bookly-margin-bottom-lg bookly-js-service-type collapse">
                                <div class="form-group">
                                    <div class="radio">
                                        <label class="bookly-margin-right-md">
                                            <input type="radio" name="type" value="simple" data-panel-class="panel-default" <?php echo checked( $service['type'] == Service::TYPE_SIMPLE ) ?>><?php _e( 'Simple', 'bookly' ) ?>
                                        </label>
                                    </div>
                                </div>
                                <?php Proxy\Shared::renderServiceFormHead( $service ) ?>
                            </div>
                            <div class="row">
                                <div class="col-md-9 col-sm-6 bookly-js-service bookly-js-service-simple bookly-js-service-compound bookly-js-service-package">
                                    <div class="form-group">
                                        <label for="title_<?php echo $service_id ?>"><?php _e( 'Title', 'bookly' ) ?></label>
                                        <input name="title" value="<?php echo esc_attr( $service['title'] ) ?>" id="title_<?php echo $service_id ?>" class="form-control" type="text">
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 bookly-js-service bookly-js-service-simple">
                                    <div class="form-group">
                                        <label><?php _e( 'Color', 'bookly' ) ?></label>
                                        <div class="bookly-color-picker-wrapper">
                                            <input name="color" value="<?php echo esc_attr( $service['color'] ) ?>" class="bookly-js-color-picker" data-last-color="<?php echo esc_attr( $service['color'] ) ?>" type="text" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php Proxy\Packages::renderSubForm( $service, $service_collection ) ?>
                            <div class="row">
                                <input type="hidden" name="visibility" value="public">
                                <?php Proxy\Pro::renderVisibility( $service ) ?>
                                <div class="col-sm-4 bookly-js-service bookly-js-service-simple bookly-js-service-compound bookly-js-service-package">
                                    <div class="form-group">
                                        <label for="price_<?php echo $service_id ?>" class="bookly-js-price-label"><?php _e( 'Price', 'bookly' ) ?></label>
                                        <?php Proxy\CustomDuration::renderServicePriceLabel( $service_id ) ?>
                                        <input id="price_<?php echo $service_id ?>" class="form-control bookly-question" type="number" min="0" step="1" name="price" value="<?php echo esc_attr( $service['price'] ) ?>" />
                                    </div>
                                </div>
                                <input type="hidden" name="capacity_min" value="1">
                                <input type="hidden" name="capacity_max" value="1">
                                <?php Proxy\GroupBooking::renderCapacity( $service ) ?>
                            </div>

                            <?php Proxy\Taxes::renderSubForm( $service ) ?>

                            <?php Proxy\CustomerGroups::renderSubForm( $service ) ?>

                            <div class="bookly-js-service bookly-js-service-simple">
                                <div class="row">
                                    <div class="col-sm-4 bookly-js-service bookly-js-service-simple">
                                        <div class="form-group">
                                            <label for="duration_<?php echo $service_id ?>">
                                                <?php _e( 'Duration', 'bookly' ) ?>
                                            </label>
                                            <?php Proxy\CustomDuration::renderServiceDurationHelp() ?>
                                            <?php
                                            $options = Common::getDurationSelectOptions( $service['duration'] );
                                            $options = Proxy\CustomDuration::prepareServiceDurationOptions( $options, $service );
                                            ?>
                                            <select id="duration_<?php echo $service_id ?>" class="bookly-js-service-unit-duration form-control" name="duration">
                                                <?php foreach ( $options as $option ): ?><option value="<?php echo $option['value']; ?>" <?php echo $option['selected']; ?>><?php echo $option['label']; ?></option><?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <input type="hidden" name="padding_left"  value="0">
                                    <input type="hidden" name="padding_right" value="0">
                                    <?php Proxy\Pro::renderPadding( $service ) ?>
                                </div>
                                <?php Proxy\CustomDuration::renderServiceDurationFields( $service ); ?>
                            </div>
                            <div class="bookly-js-service bookly-js-service-simple">
                                <div class="row">
                                    <div class="col-sm-8 bookly-js-service bookly-js-service-simple">
                                        <div class="form-group">
                                            <label for="start_time_info_<?php echo $service_id ?>"><?php _e( 'Start and end times of the appointment', 'bookly' ) ?></label>
                                            <p class="help-block"><?php _e( 'Allows to set the start and end times for an appointment for services with the duration of 1 day or longer. This time will be displayed in notifications to customers, backend calendar and codes for booking form.', 'bookly' ) ?></p>
                                            <div class="row">
                                                <div class="col-xs-6">
                                                    <input id="start_time_info_<?php echo $service_id ?>" class="form-control" type="text" name="start_time_info" value="<?php echo esc_attr( $service['start_time_info'] ) ?>" />
                                                </div>
                                                <div class="col-xs-6">
                                                    <input class="form-control" type="text" name="end_time_info" value="<?php echo esc_attr( $service['end_time_info'] ) ?>" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bookly-js-service bookly-js-service-simple">
                                <div class="row">
                                    <input type="hidden" name="staff_preference" value="<?php echo Service::PREFERRED_LEAST_EXPENSIVE ?>" data-default="[]">
                                    <?php Proxy\Pro::renderStaffPreference( $service ) ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 bookly-js-service bookly-js-service-simple bookly-js-service-compound">
                                    <div class="form-group">
                                        <label for="category_<?php echo $service_id ?>"><?php _e( 'Category', 'bookly' ) ?></label>
                                        <select id="category_<?php echo $service_id ?>" class="form-control" name="category_id"><option value="0"><?php _e( 'Uncategorized', 'bookly' ) ?></option>
                                            <?php foreach ( $category_collection as $category ) : ?>
                                                <option value="<?php echo $category['id'] ?>" <?php selected( $category['id'], $service['category_id'] ) ?>><?php echo esc_html( $category['name'] ) ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6 bookly-js-service bookly-js-service-simple bookly-js-service-package">
                                    <div class="form-group">
                                        <label><?php _e( 'Providers', 'bookly' ) ?></label><br>
                                        <div class="btn-group bookly-js-entity-selector-container">
                                            <button class="btn btn-default btn-block dropdown-toggle bookly-flexbox" data-toggle="dropdown">
                                                <div class="bookly-flex-cell">
                                                    <i class="dashicons dashicons-admin-users bookly-margin-right-md"></i>
                                                </div>
                                                <div class="bookly-flex-cell text-left" style="width: 100%">
                                                    <span class=bookly-entity-counter><?php echo $service['total_staff'] ?></span>
                                                </div>
                                                <div class="bookly-flex-cell"><div class="bookly-margin-left-md"><span class="caret"></span></div></div>
                                            </button>
                                            <ul class="dropdown-menu bookly-entity-selector">
                                                <li>
                                                    <a class="checkbox" href="javascript:void(0)">
                                                        <label>
                                                            <input type="checkbox" class="bookly-check-all-entities" <?php checked( $all_staff_selected ) ?> data-title="<?php esc_attr_e( 'All staff', 'bookly' ) ?>" data-nothing="<?php esc_attr_e( 'No staff selected', 'bookly' ) ?>">
                                                            <?php _e( 'All staff', 'bookly' ) ?>
                                                        </label>
                                                    </a>
                                                </li>
                                                <?php foreach ( $staff_collection as $i => $staff ) : ?>
                                                    <li>
                                                        <a class="checkbox" href="javascript:void(0)">
                                                            <label>
                                                                <input type="checkbox" name="staff_ids[]" class="bookly-js-check-entity" value="<?php echo $staff['id'] ?>" <?php checked( in_array( $staff['id'], $assigned_staff_ids ) ) ?> data-title="<?php echo esc_attr( $staff['full_name'] ) ?>">
                                                                <?php echo esc_html( $staff['full_name'] ) ?>
                                                            </label>
                                                        </a>
                                                    </li>
                                                <?php endforeach ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="appointments_limit" value="">
                            <input type="hidden" name="limit_period" value="off">
                            <?php Proxy\Pro::renderLimitAppointmentsPerCustomer( $service ) ?>
                            <div class="form-group bookly-js-service bookly-js-service-simple bookly-js-service-compound bookly-js-service-package">
                                <label for="info_<?php echo $service_id ?>">
                                    <?php _e( 'Info', 'bookly' ) ?>
                                </label>
                                <p class="help-block">
                                    <?php printf( __( 'This text can be inserted into notifications with %s code.', 'bookly' ), '{service_info}' ) ?>
                                </p>
                                <textarea class="form-control" id="info_<?php echo $service_id ?>" name="info" rows="3" type="text"><?php echo esc_textarea( $service['info'] ) ?></textarea>
                            </div>

                            <?php Proxy\CompoundServices::renderSubForm( $service, $service_collection ) ?>

                            <?php Proxy\Shared::renderServiceForm( $service ) ?>

                            <div class="panel-footer">
                                <input type="hidden" name="action" value="bookly_update_service">
                                <input type="hidden" name="id" value="<?php echo esc_html( $service_id ) ?>">
                                <input type="hidden" name="update_staff" value="0">
                                <span class="bookly-js-services-error text-danger"></span>
                                <?php Inputs::renderCsrf() ?>
                                <?php Buttons::renderSubmit( null, 'ajax-service-send' ) ?>
                                <?php Buttons::renderReset( null, 'js-reset' ) ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>
<?php endif ?>
<div style="display: none">
    <?php Proxy\Shared::renderAfterServiceList( $service_collection ) ?>
</div>