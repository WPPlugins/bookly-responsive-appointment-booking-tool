<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    /**
     * @var AB_Category[] $collection
     * @var AB_StaffServicesForm $form
     * @var int $staff_id
     */
    $collection = $form->getCollection();
    $selected = $form->getSelected();
    $uncategorized_services = $form->getUncategorizedServices();

    $currentDateTime  = new DateTime( 'now' );
    $durationDateTime = new DateTime( 'now' );
    $current_time     = time();
?>
<div id="ab-staff-services">
    <?php if ( $collection || $uncategorized_services ) : ?>
        <form>
            <ul>
                <li style="position: relative">
                    <input id="ab-all-services" type="checkbox" />
                    <span><?php _e( 'All services', 'bookly' ) ?></span>
                    <?php if ( ! empty ( $uncategorized_services ) ) : ?>
                        <div class="ab-title-service">
                            <div><?php _e( 'Price', 'bookly' ) ?></div>
                            <div><?php _e( 'Capacity', 'bookly' ) ?></div>
                        </div>
                    <?php endif ?>
                </li>
                <?php if ( ! empty ( $uncategorized_services ) ) : ?>
                    <li>
                        <ul>
                            <li class="ab-category-services">
                                <ul>
                                    <?php foreach ( $uncategorized_services as $service ) : ?>
                                        <li>
                                            <div class="left ab-list-title">
                                                <input class="ab-service-checkbox" <?php checked( array_key_exists( $service['id'], $selected ) ) ?> type="checkbox" value="<?php echo $service['id'] ?>" name="service[<?php echo $service['id'] ?>]"/>
                                                <?php echo esc_html( $service['title'] ) ?>
                                            </div>
                                            <div class="right">
                                                <input class="form-control ab-inline-block ab-price" type="text" <?php disabled( !array_key_exists( $service['id'], $selected ) ) ?> name="price[<?php echo $service['id'] ?>]" value="<?php echo array_key_exists( $service['id'], $selected ) ? $selected[ $service['id'] ]['price'] : $service['price'] ?>">
                                                <input class="form-control ab-inline-block ab-price" type="number" min=1 <?php disabled( !array_key_exists( $service['id'], $selected ) ) ?> name="capacity[<?php echo $service['id'] ?>]" value="<?php echo array_key_exists( $service['id'], $selected ) ? $selected[ $service['id'] ]['capacity'] : $service['capacity'] ?>">
                                            </div>
                                            <div style="border-bottom: 1px dotted black; overflow: hidden; padding-top: 15px;"></div>
                                        </li>
                                    <?php endforeach ?>
                                </ul>
                            </li>
                        </ul>
                    </li>
                <?php endif ?>
                <?php if ( !empty ( $collection ) ) : ?>
                    <li>
                        <ul>
                            <?php foreach ( $collection as $category ) : ?>
                                <li class="ab-services-category">
                                    <input class="ab-category-checkbox ab-category-<?php echo $category->get( 'id' ) ?>" data-category-id="<?php echo $category->get( 'id' ) ?>" type="checkbox" value="" />
                                    <span><?php echo esc_html( $category->get( 'name' ) ) ?></span>
                                    <div class="ab-title-service">
                                        <div><?php _e( 'Price', 'bookly' ) ?></div>
                                        <div><?php _e( 'Capacity', 'bookly' ) ?></div>
                                    </div>
                                </li>
                                <li class="ab-category-services">
                                    <ul>
                                        <?php foreach ( $category->getServices() as $service ) : ?>
                                            <li>
                                                <div class="left ab-list-title">
                                                   <input class="ab-service-checkbox ab-category-<?php echo $category->get( 'id' ) ?>" data-category-id="<?php echo $category->get( 'id' ) ?>" <?php checked( array_key_exists( $service->get( 'id' ), $selected ) )  ?> type="checkbox" value="<?php echo $service->get( 'id' ) ?>" name="service[<?php echo $service->get( 'id' ) ?>]"/>
                                                   <?php echo esc_html( $service->get( 'title' ) ) ?>
                                                </div>
                                                <div class="right">
                                                    <input class="form-control ab-inline-block ab-price" type="text" <?php disabled( !array_key_exists( $service->get( 'id' ), $selected ) ) ?> name="price[<?php echo $service->get( 'id' ) ?>]" value="<?php echo array_key_exists( $service->get( 'id' ), $selected ) ? $selected[$service->get( 'id' )]['price'] : $service->get('price')?>">
                                                    <input class="form-control ab-inline-block ab-price" type="number" min=1 <?php disabled( !array_key_exists( $service->get( 'id' ), $selected ) ) ?> name="capacity[<?php echo $service->get( 'id' ) ?>]" value="<?php echo array_key_exists( $service->get( 'id' ), $selected ) ? $selected[$service->get( 'id' )]['capacity'] : $service->get('capacity')?>">
                                                </div>
                                                <div style="border-bottom: 1px dotted black; overflow: hidden; padding-top: 15px;"></div>
                                            </li>
                                        <?php endforeach ?>
                                    </ul>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    </li>
                <?php endif ?>
            </ul>
            <input type="hidden" name="action" value="ab_staff_services_update"/>
            <input type="hidden" name="staff_id" value="<?php echo $staff_id ?>"/>
            <?php AB_Utils::submitButton( 'ajax-send-service' ) ?>
            <?php AB_Utils::resetButton() ?>
        </form>
    <?php else : ?>
        <?php _e( 'No services found. Please add services.', 'bookly' ) ?>
        <a class="btn btn-info" href="<?php echo AB_Utils::escAdminUrl( AB_ServiceController::page_slug ) ?>" ><?php _e( 'Add Service', 'bookly' ) ?></a>
    <?php endif ?>
</div>
