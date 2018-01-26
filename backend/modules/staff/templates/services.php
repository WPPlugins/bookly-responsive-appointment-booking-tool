<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/** @var \BooklyLite\Lib\Entities\Category[] $categories */
?>
<div>
    <?php if ( $categories || $uncategorized_services ) : ?>
        <form>
            <?php if ( ! empty ( $uncategorized_services ) ) : ?>
                <div class="panel panel-default bookly-panel-unborder">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="checkbox bookly-margin-remove">
                                    <label>
                                        <input id="bookly-check-all-entities" type="checkbox">
                                        <b><?php _e( 'All services', 'bookly' ) ?></b>
                                    </label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <div class="<?php echo \BooklyLite\Lib\Proxy\Shared::prepareStaffServiceLabelClass( 'col-lg-12' ) ?> hidden-xs hidden-sm hidden-md text-right">
                                        <div class="bookly-font-smaller bookly-color-gray">
                                            <?php _e( 'Price', 'bookly' ) ?>
                                        </div>
                                    </div>

                                    <?php \BooklyLite\Lib\Proxy\DepositPayments::renderStaffServiceLabel() ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <ul class="bookly-category-services list-group bookly-padding-top-md">
                        <?php foreach ( $uncategorized_services as $service ) : ?>
                            <li class="list-group-item" data-service-id="<?php echo $service['id'] ?>" data-service-type="<?php echo $service['type'] ?>" data-sub-service="<?php echo $service['sub_service_id'] ?>">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="checkbox">
                                            <label>
                                                <input class="bookly-service-checkbox" <?php checked( array_key_exists( $service['id'], $services_data ) ) ?>
                                                       type="checkbox" value="<?php echo $service['id'] ?>"
                                                       name="service[<?php echo $service['id'] ?>]"
                                                >
                                                <span class="bookly-toggle-label"><?php echo esc_html( $service['title'] ) ?></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="row">
                                            <div class="<?php echo \BooklyLite\Lib\Proxy\Shared::prepareStaffServiceInputClass( 'col-xs-12' ) ?>">
                                                <div class="bookly-font-smaller bookly-margin-bottom-xs bookly-color-gray visible-xs visible-sm visible-md">
                                                    <?php _e( 'Price', 'bookly' ) ?>
                                                </div>
                                                <input class="form-control text-right" type="text" <?php disabled( !array_key_exists( $service['id'], $services_data ) ) ?>
                                                       name="price[<?php echo $service['id'] ?>]"
                                                       value="<?php echo array_key_exists( $service['id'], $services_data ) ? $services_data[ $service['id'] ]['price'] : $service['price'] ?>"
                                                >
                                            </div>

                                            <?php \BooklyLite\Lib\Proxy\Shared::renderStaffService( $staff_id, $service['id'], $services_data, $service['type'] == \BooklyLite\Lib\Entities\Service::TYPE_PACKAGE ? array( 'read-only' => array( 'deposit' => true ) ) : array() ) ?>

                                            <div style="display: none">
                                                <div class="form-group bookly-js-capacity-form-group">
                                                    <div class="row">
                                                        <div class="col-xs-6">
                                                            <input class="form-control bookly-js-capacity bookly-js-capacity-min" type="number" min=1 <?php disabled( ! array_key_exists( $service['id'], $services_data ) ) ?>
                                                                   name="capacity_min[<?php echo $service['id'] ?>]"
                                                                   value="1"
                                                                   <?php if ( $service['type'] == \BooklyLite\Lib\Entities\Service::TYPE_PACKAGE ) : ?>readonly<?php endif ?>
                                                            >
                                                        </div>
                                                        <div class="col-xs-6">
                                                            <input class="form-control bookly-js-capacity bookly-js-capacity-max" type="number" min=1 <?php disabled( ! array_key_exists( $service['id'], $services_data ) ) ?>
                                                                   name="capacity_max[<?php echo $service['id'] ?>]"
                                                                   value="1"
                                                                   <?php if ( $service['type'] == \BooklyLite\Lib\Entities\Service::TYPE_PACKAGE ) : ?>readonly<?php endif ?>
                                                            >
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php if ( $service['type'] == \BooklyLite\Lib\Entities\Service::TYPE_SIMPLE ) { \BooklyLite\Lib\Proxy\Shared::renderStaffServiceTail( $staff_id, $service[ 'id' ] ); } ?>
                            </li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>

            <?php if ( ! empty ( $categories ) ) : ?>
                <?php foreach ( $categories as $category ) : ?>
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
                                    <div class="row">
                                        <div class="<?php echo \BooklyLite\Lib\Proxy\Shared::prepareStaffServiceLabelClass( 'col-lg-12' )?> hidden-xs hidden-sm hidden-md text-right">
                                            <div class="bookly-font-smaller bookly-color-gray"><?php _e( 'Price', 'bookly' ) ?></div>
                                        </div>

                                        <?php \BooklyLite\Lib\Proxy\DepositPayments::renderStaffServiceLabel() ?>
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
                                                    >
                                                    <span class="bookly-toggle-label"><?php echo esc_html( $service->getTitle() ) ?></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="row">
                                                <div class="<?php echo \BooklyLite\Lib\Proxy\Shared::prepareStaffServiceInputClass( 'col-xs-12' ) ?>">
                                                    <div class="bookly-font-smaller bookly-margin-bottom-xs bookly-color-gray visible-xs visible-sm visible-md">
                                                        <?php _e( 'Price', 'bookly' ) ?>
                                                    </div>
                                                    <input class="form-control text-right" type="text" <?php disabled( ! array_key_exists( $service->getId(), $services_data ) ) ?>
                                                           name="price[<?php echo $service->getId() ?>]"
                                                           value="<?php echo array_key_exists( $service->getId(), $services_data ) ? $services_data[ $service->getId() ]['price'] : $service->getPrice() ?>"
                                                    >
                                                </div>

                                                <?php \BooklyLite\Lib\Proxy\Shared::renderStaffService( $staff_id, $service->getId(), $services_data, $service->getType() == \BooklyLite\Lib\Entities\Service::TYPE_PACKAGE ? array( 'read-only' => array( 'deposit' => true ) ) : array() ) ?>

                                                <div style="display: none">
                                                    <div class="form-group bookly-js-capacity-form-group">
                                                        <div class="row">
                                                            <div class="col-xs-6">
                                                                <input class="form-control bookly-js-capacity bookly-js-capacity-min" type="number" min="1" <?php disabled( ! array_key_exists( $service->getId(), $services_data ) ) ?>
                                                                       name="capacity_min[<?php echo $service->getId() ?>]"
                                                                       value="1"
                                                                       <?php if ( $service->getType() == \BooklyLite\Lib\Entities\Service::TYPE_PACKAGE ) : ?>readonly<?php endif ?>
                                                                >
                                                            </div>
                                                            <div class="col-xs-6">
                                                                <input class="form-control bookly-js-capacity bookly-js-capacity-max" type="number" min="1" <?php disabled( ! array_key_exists( $service->getId(), $services_data ) ) ?>
                                                                       name="capacity_max[<?php echo $service->getId() ?>]"
                                                                       value="1"
                                                                       <?php if ( $service->getType() == \BooklyLite\Lib\Entities\Service::TYPE_PACKAGE ) : ?>readonly<?php endif ?>
                                                                >
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ( $service->getType() == \BooklyLite\Lib\Entities\Service::TYPE_SIMPLE ) { \BooklyLite\Lib\Proxy\Shared::renderStaffServiceTail( $staff_id, $service->getId() ); } ?>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                <?php endforeach ?>
            <?php endif ?>

            <input type="hidden" name="action" value="bookly_staff_services_update">
            <input type="hidden" name="staff_id" value="<?php echo $staff_id ?>">
            <?php \BooklyLite\Lib\Utils\Common::csrf() ?>

            <div class="panel-footer">
                <span class="bookly-js-services-error text-danger"></span>
                <?php \BooklyLite\Lib\Utils\Common::submitButton( 'bookly-services-save' ) ?>
                <?php \BooklyLite\Lib\Utils\Common::resetButton( 'bookly-services-reset' ) ?>
            </div>
        </form>
    <?php else : ?>
        <h5 class="text-center"><?php _e( 'No services found. Please add services.', 'bookly' ) ?></h5>
        <p class="bookly-margin-top-xlg text-center">
            <a class="btn btn-xlg btn-success-outline"
               href="<?php echo \BooklyLite\Lib\Utils\Common::escAdminUrl( \BooklyLite\Backend\Modules\Services\Controller::page_slug ) ?>" >
                <?php _e( 'Add Service', 'bookly' ) ?>
            </a>
        </p>
    <?php endif ?>
    <div style="display: none">
        <?php BooklyLite\Lib\Proxy\Shared::renderStaffServices( $staff_id ) ?>
    </div>
</div>