<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div>
    <?php if ( $categories || $uncategorized_services ) : ?>
        <form>
            <?php if ( ! empty ( $uncategorized_services ) ) : ?>
                <div class="panel panel-default bookly-panel-unborder">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="checkbox bookly-margin-remove">
                                    <label>
                                        <input id="bookly-check-all-entities" type="checkbox">
                                        <b><?php _e( 'All services', 'bookly' ) ?></b>
                                    </label>
                                </div>
                            </div>

                            <div class="<?php echo apply_filters( 'bookly_staff_service_label_col_size', 'col-md-3' ) ?> hidden-xs hidden-sm text-right">
                                <div class="bookly-font-smaller bookly-color-gray">
                                    <?php _e( 'Price', 'bookly' ) ?>
                                </div>
                            </div>

                            <?php do_action( 'bookly_deposit_payments_staff_service_label' ) ?>

                            <div class="<?php echo apply_filters( 'bookly_staff_service_label_col_size', 'col-md-3' ) ?> hidden-xs hidden-sm">
                                <div class="bookly-font-smaller bookly-color-gray bookly-truncate">
                                    <?php _e( 'Capacity', 'bookly' ) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <ul class="bookly-category-services list-group bookly-padding-top-md">
                        <?php foreach ( $uncategorized_services as $service ) : ?>
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="checkbox">
                                            <label>
                                                <input class="ab-service-checkbox" <?php checked( array_key_exists( $service['id'], $services_data ) ) ?>
                                                       type="checkbox" value="<?php echo $service['id'] ?>"
                                                       name="service[<?php echo $service['id'] ?>]"
                                                >
                                                <span class="bookly-toggle-label"><?php echo esc_html( $service['title'] ) ?></span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="<?php echo apply_filters( 'bookly_staff_service_input_col_size', 'col-xs-7 col-md-3' ) ?>">
                                        <div class="bookly-font-smaller bookly-margin-bottom-xs bookly-color-gray visible-xs visible-sm">
                                            <?php _e( 'Price', 'bookly' ) ?>
                                        </div>
                                        <input class="form-control text-right" type="text" <?php disabled( !array_key_exists( $service['id'], $services_data ) ) ?>
                                               name="price[<?php echo $service['id'] ?>]"
                                               value="<?php echo array_key_exists( $service['id'], $services_data ) ? $services_data[ $service['id'] ]['price'] : $service['price'] ?>"
                                        >
                                    </div>

                                    <?php do_action( 'bookly_staff_render_service', $staff_id, $service['id'], $services_data ) ?>

                                    <div class="<?php echo apply_filters( 'bookly_staff_service_input_col_size', 'col-xs-5 col-md-3' ) ?>">
                                        <div class="bookly-font-smaller bookly-margin-bottom-xs bookly-color-gray visible-xs visible-sm">
                                            <?php _e( 'Capacity', 'bookly' ) ?>
                                        </div>
                                        <input class="form-control" type="number" min=1 <?php disabled( ! array_key_exists( $service['id'], $services_data ) ) ?>
                                               name="capacity[<?php echo $service['id'] ?>]"
                                               value="<?php echo array_key_exists( $service['id'], $services_data ) ? $services_data[ $service['id'] ]['capacity'] : $service['capacity'] ?>"
                                        >
                                    </div>
                                </div>
                                <?php do_action( 'bookly_staff_render_service_tail', $staff_id, $service['id'] ) ?>
                            </li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>

            <?php if ( ! empty ( $categories ) ) : ?>
                <?php foreach ( $categories as $category ) : ?>
                    <div class="panel panel-default bookly-panel-unborder">
                        <div class="panel-heading ab-services-category">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="checkbox bookly-margin-remove">
                                        <label>
                                            <input type="checkbox" class="bookly-category-checkbox bookly-category-<?php echo $category->get( 'id' ) ?>"
                                                   data-category-id="<?php echo $category->get( 'id' ) ?>">
                                            <b><?php echo esc_html( $category->get( 'name' ) ) ?></b>
                                        </label>
                                    </div>
                                </div>

                                <div class="<?php echo apply_filters( 'bookly_staff_service_label_col_size', 'col-xs-7 col-md-3')?> hidden-xs hidden-sm text-right">
                                    <div class="bookly-font-smaller bookly-color-gray"><?php _e( 'Price', 'bookly' ) ?></div>
                                </div>

                                <?php do_action( 'bookly_deposit_payments_staff_service_label' ) ?>

                                <div class="<?php echo apply_filters( 'bookly_staff_service_label_col_size', 'col-xs-5 col-md-3')?> hidden-xs hidden-sm">
                                    <div class="bookly-font-smaller bookly-color-gray bookly-truncate"><?php _e( 'Capacity', 'bookly' ) ?></div>
                                </div>
                            </div>
                        </div>

                        <ul class="bookly-category-services list-group bookly-padding-top-md">
                            <?php foreach ( $category->getServices() as $service ) : ?>
                                <li class="list-group-item">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="checkbox">
                                                <label>
                                                    <input class="ab-service-checkbox bookly-category-<?php echo $category->get( 'id' ) ?>"
                                                           data-category-id="<?php echo $category->get( 'id' ) ?>" <?php checked( array_key_exists( $service->get( 'id' ), $services_data ) ) ?>
                                                           type="checkbox" value="<?php echo $service->get( 'id' ) ?>"
                                                           name="service[<?php echo $service->get( 'id' ) ?>]"
                                                    >
                                                    <span class="bookly-toggle-label"><?php echo esc_html( $service->get( 'title' ) ) ?></span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="<?php echo apply_filters( 'bookly_staff_service_input_col_size', 'col-xs-7 col-sm-3')?>">
                                            <div class="bookly-font-smaller bookly-margin-bottom-xs bookly-color-gray visible-xs visible-sm">
                                                <?php _e( 'Price', 'bookly' ) ?>
                                            </div>
                                            <input class="form-control text-right" type="text" <?php disabled( ! array_key_exists( $service->get( 'id' ), $services_data ) ) ?>
                                                   name="price[<?php echo $service->get( 'id' ) ?>]"
                                                   value="<?php echo array_key_exists( $service->get( 'id' ), $services_data ) ? $services_data[ $service->get( 'id' ) ]['price'] : $service->get( 'price' ) ?>"
                                            >
                                        </div>

                                        <?php do_action( 'bookly_staff_render_service', $staff_id, $service->get( 'id' ), $services_data ) ?>

                                        <div class="<?php echo apply_filters( 'bookly_staff_service_input_col_size', 'col-xs-5 col-sm-2')?>">
                                            <div class="bookly-font-smaller bookly-margin-bottom-xs bookly-color-gray visible-xs visible-sm">
                                                <?php _e( 'Capacity', 'bookly' ) ?>
                                            </div>
                                            <input class="form-control" type="number" min="1" <?php disabled( ! array_key_exists( $service->get( 'id' ), $services_data ) ) ?>
                                                   name="capacity[<?php echo $service->get( 'id' ) ?>]"
                                                   value="<?php echo array_key_exists( $service->get( 'id' ), $services_data ) ? $services_data[ $service->get( 'id' ) ]['capacity'] : $service->get( 'capacity' ) ?>"
                                            >
                                        </div>
                                    </div>
                                    <?php do_action( 'bookly_staff_render_service_tail', $staff_id, $service->get( 'id' ) ) ?>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                <?php endforeach ?>
            <?php endif ?>

            <input type="hidden" name="action" value="bookly_staff_services_update">
            <input type="hidden" name="staff_id" value="<?php echo $staff_id ?>">

            <div class="panel-footer">
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
</div>
