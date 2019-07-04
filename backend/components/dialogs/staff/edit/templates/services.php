<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Components\Dialogs;
use Bookly\Backend\Modules\Staff\Proxy;
use Bookly\Lib\Utils\Common;
/** @var Dialogs\Staff\Edit\Forms\StaffServices $form */
?>
<div>
    <?php if ( $form->getCategories() || $form->getUncategorizedServices() ) : ?>
        <form>
            <?php Proxy\Locations::renderLocationSwitcher( $staff_id, $location_id, 'custom_services' ) ?>
            <?php if ( $form->getUncategorizedServices() ) : ?>
                <div class="panel panel-default bookly-panel-unborder">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="checkbox bookly-margin-remove">
                                    <label>
                                        <input id="bookly-check-all-entities" type="checkbox">
                                        <b><?php esc_html_e( 'All services', 'bookly' ) ?></b>
                                    </label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="bookly-flexbox">
                                    <div class="bookly-flex-row">
                                        <div class="bookly-flex-cell hidden-xs hidden-sm hidden-md text-right">
                                            <div class="bookly-font-smaller bookly-color-gray">
                                                <?php esc_html_e( 'Price', 'bookly' ) ?>
                                            </div>
                                        </div>
                                        <?php Proxy\Shared::renderStaffServiceLabels() ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <ul class="bookly-category-services list-group bookly-padding-top-md">
                        <?php foreach ( $form->getUncategorizedServices() as $service ) : ?>
                            <?php $sub_service = current( $service->getSubServices() ) ?>
                            <li class="list-group-item" data-service-id="<?php echo $service->getId() ?>" data-service-type="<?php echo $service->getType() ?>" data-sub-service="<?php echo empty( $sub_service ) ? null : $sub_service->getId(); ?>">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="checkbox">
                                            <label>
                                                <input class="bookly-service-checkbox" <?php checked( array_key_exists( $service->getId(), $services_data ) ) ?>
                                                       type="checkbox" value="<?php echo $service->getId() ?>"
                                                       name="service[<?php echo $service->getId() ?>]"
                                                >
                                                <span class="bookly-toggle-label"><?php echo esc_html( $service->getTitle() ) ?></span>
                                            </label>
                                            <?php Proxy\Ratings::renderStaffServiceRating( $staff_id, $service->getId(), 'right' ) ?>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="row">
                                            <div class="bookly-flexbox">
                                                <div class="bookly-flex-row">
                                                <div class="bookly-flex-cell">
                                                    <div class="bookly-font-smaller bookly-margin-bottom-xs bookly-color-gray visible-xs visible-sm visible-md">
                                                        <?php esc_html_e( 'Price', 'bookly' ) ?>
                                                    </div>
                                                    <input class="form-control text-right" type="text" <?php disabled( ! array_key_exists( $service->getId(), $services_data ) ) ?>
                                                           name="price[<?php echo $service->getId() ?>]"
                                                           value="<?php echo array_key_exists( $service->getId(), $services_data ) ? $services_data[ $service->getId() ]['price'] : $service->getPrice() ?>"
                                                    />
                                                </div>

                                                <?php Proxy\Shared::renderStaffService( $staff_id, $service, $services_data, array() ) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php Proxy\Shared::renderStaffServiceTail( $staff_id, $service, $location_id ) ?>
                            </li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>

            <?php foreach ( $form->getCategories() as $category ) : ?>
                <div class="panel panel-default bookly-panel-unborder">
                    <div class="panel-heading bookly-services-category">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="checkbox bookly-margin-remove">
                                    <label>
                                        <input type="checkbox" class="bookly-category-checkbox bookly-category-<?php echo $category->getId() ?>"
                                               data-category-id="<?php echo $category->getId() ?>">
                                        <b><?php echo esc_html( $category->getName() ) ?></b>
                                    </label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="bookly-flexbox">
                                    <div class="bookly-flex-row">
                                        <div class="bookly-flex-cell hidden-xs hidden-sm hidden-md text-right">
                                            <div class="bookly-font-smaller bookly-color-gray"><?php esc_html_e( 'Price', 'bookly' ) ?></div>
                                        </div>
                                        <?php Proxy\Shared::renderStaffServiceLabels() ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <ul class="bookly-category-services list-group bookly-padding-top-md">
                        <?php foreach ( $category->getServices() as $service ) : ?>
                            <?php $sub_service = current( $service->getSubServices() ) ?>
                            <li class="list-group-item" data-service-id="<?php echo $service->getId() ?>" data-service-type="<?php echo $service->getType() ?>" data-sub-service="<?php echo empty( $sub_service ) ? null : $sub_service->getId(); ?>">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="checkbox">
                                            <label>
                                                <input class="bookly-service-checkbox bookly-category-<?php echo $category->getId() ?>"
                                                       data-category-id="<?php echo $category->getId() ?>" <?php checked( array_key_exists( $service->getId(), $services_data ) ) ?>
                                                       type="checkbox" value="<?php echo $service->getId() ?>"
                                                       name="service[<?php echo $service->getId() ?>]"
                                                />
                                                <span class="bookly-toggle-label"><?php echo esc_html( $service->getTitle() ) ?></span>
                                            </label>
                                            <?php Proxy\Ratings::renderStaffServiceRating( $staff_id, $service->getId(), 'right' ) ?>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="bookly-flexbox">
                                            <div class="bookly-flex-row">
                                            <div class="bookly-flex-cell">
                                                <div class="bookly-font-smaller bookly-margin-bottom-xs bookly-color-gray visible-xs visible-sm visible-md">
                                                    <?php esc_html_e( 'Price', 'bookly' ) ?>
                                                </div>
                                                <input class="form-control text-right" type="text" <?php disabled( ! array_key_exists( $service->getId(), $services_data ) ) ?>
                                                       name="price[<?php echo $service->getId() ?>]"
                                                       value="<?php echo array_key_exists( $service->getId(), $services_data ) ? $services_data[ $service->getId() ]['price'] : $service->getPrice() ?>"
                                                />
                                            </div>

                                            <?php Proxy\Shared::renderStaffService( $staff_id, $service, $services_data, array() ) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php Proxy\Shared::renderStaffServiceTail( $staff_id, $service, $location_id ) ?>
                            </li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endforeach ?>

            <input type="hidden" name="action" value="bookly_staff_services_update">
            <input type="hidden" name="staff_id" value="<?php echo $staff_id ?>">
            <?php Inputs::renderCsrf() ?>

            <div class="panel-footer">
                <span class="bookly-js-services-error text-danger"></span>
                <?php Buttons::renderCustom( 'bookly-services-save', 'btn-lg btn-success' ) ?>
                <?php Buttons::renderReset( 'bookly-services-reset' ) ?>
            </div>
        </form>
    <?php else : ?>
        <h5 class="text-center"><?php esc_html_e( 'No services found. Please add services.', 'bookly' ) ?></h5>
        <p class="bookly-margin-top-xlg text-center">
            <a class="btn btn-xlg btn-success-outline"
               href="<?php echo Common::escAdminUrl( Bookly\Backend\Modules\Services\Page::pageSlug() ) ?>" >
                <?php esc_html_e( 'Add Service', 'bookly' ) ?>
            </a>
        </p>
    <?php endif ?>
    <div style="display: none">
        <?php Dialogs\SpecialPrice\Proxy\SpecialHours::renderSpecialPricePopup( $staff_id ) ?>
    </div>
</div>