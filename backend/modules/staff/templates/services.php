<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
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
    <?php if ( count( $collection ) ) : ?>
        <form>
            <ul>
                <li>
                    <input id="ab-all-services" type="checkbox" />
                    <span><?php _e( 'All services', 'ab' ) ?></span>
                    <?php if ( !empty ( $uncategorized_services ) ) : ?>
                        <div class="ab-title-service">
                            <div><?php _e( 'Price' ) ?></div>
                            <div><?php _e( 'Capacity' ) ?></div>
                        </div>
                    <?php endif ?>
                </li>
                <?php if ( !empty ( $uncategorized_services ) ) : ?>
                    <li>
                        <ul>
                            <li class="ab-category-services">
                                <ul>
                                    <?php foreach ( $uncategorized_services as $service ) : ?>
                                        <li>
                                            <div class="left ab-list-title">
                                                <input class="ab-service-checkbox" <?php if ( array_key_exists( $service->get( 'id' ), $selected ) ) echo 'checked=checked' ?> type="checkbox" value="<?php echo $service->get( 'id' ) ?>" name="service[<?php echo $service->get( 'id' ) ?>]"/>
                                                <?php echo esc_html($service->get('title')) ?>
                                            </div>
                                            <div class="right">
                                                <input class="ab-price" type="text" <?php if ( !array_key_exists( $service->get( 'id' ), $selected ) ) echo 'disabled=disabled' ?> name="price[<?php echo $service->get( 'id' ) ?>]" value="<?php echo array_key_exists( $service->get( 'id' ), $selected ) ? $selected[ $service->get('id') ]['price'] : $service->get( 'price' )?>">
                                                <input class="ab-price" type="number" min=1 <?php if ( !array_key_exists( $service->get( 'id' ), $selected )) echo 'disabled=disabled' ?> name="capacity[<?php echo $service->get( 'id' ) ?>]" value="1" />
                                            </div>
                                            <div style="border-bottom: 1px dotted black; overflow: hidden; padding-top: 15px;"></div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
                <li>
                    <ul>
                        <?php foreach ( $collection as $category ) : ?>
                            <li class="ab-services-category">
                                <input class="ab-category-checkbox ab-category-<?php echo $category->get( 'id' ) ?>" data-category-id="<?php echo $category->get( 'id' ) ?>" type="checkbox" value="" />
                                <span><?php echo esc_html($category->get('name')) ?></span>
                                <div class="ab-title-service">
                                    <div><?php _e( 'Price' ) ?></div>
                                    <div><?php _e( 'Capacity' ) ?></div>
                                </div>
                            </li>
                            <li class="ab-category-services">
                                <ul>
                                    <?php foreach ( $category->getServices() as $service ) : ?>
                                        <li>
                                            <div class="left ab-list-title">
                                               <input class="ab-service-checkbox ab-category-<?php echo $category->get( 'id' ) ?>" data-category-id="<?php echo $category->get( 'id' ) ?>" <?php if ( array_key_exists( $service->get( 'id' ), $selected )) echo 'checked=checked' ?> type="checkbox" value="<?php echo $service->get( 'id' ) ?>" name="service[<?php echo $service->get( 'id' ) ?>]"/>
                                               <?php echo esc_html($service->get('title')) ?>
                                            </div>
                                            <div class="right">
                                                <input class="ab-price" type="text" <?php if ( !array_key_exists( $service->get( 'id' ), $selected )) echo 'disabled=disabled' ?> name="price[<?php echo $service->get( 'id' ) ?>]" value="<?php echo array_key_exists( $service->get( 'id' ), $selected ) ? $selected[$service->get('id')]['price'] : $service->get('price')?>">
                                                <input class="ab-price" type="number" min=1 <?php if ( !array_key_exists( $service->get( 'id' ), $selected )) echo 'disabled=disabled' ?> name="capacity[<?php echo $service->get( 'id' ) ?>]" value="<?php echo array_key_exists( $service->get( 'id' ), $selected ) ? $selected[$service->get('id')]['capacity'] : $service->get('capacity')?>">
                                            </div>
                                            <div style="border-bottom: 1px dotted black; overflow: hidden; padding-top: 15px;"></div>
                                        </li>
                                    <?php endforeach ?>
                                </ul>
                            </li>
                        <?php endforeach ?>
                    </ul>
                </li>
            </ul>
            <input type="hidden" name="action" value="ab_staff_services_update"/>
            <input type="hidden" name="staff_id" value="<?php echo $staff_id ?>"/>
            <span class="spinner"></span>
            <a class="btn btn-info ab-update-button" href="javascript:void(0)" id="ab-staff-services-update"><?php _e('Update', 'ab') ?></a>
            <button class="ab-reset-form" type="reset"><?php _e( 'Reset', 'ab' ) ?></button>
        </form>
    <?php endif; ?>
</div>

<div class="modal fade" id="lite_notice" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php _e('Notice', 'ab') ?></h4>
            </div>
            <div class="modal-body">
                <?php _e('This function is disabled in the lite version of Bookly. If you find the plugin useful for your business please consider buying a licence for the full version. It costs just $38 and for this money you will get many useful functions, lifetime free update and excellent support! More information can be found here', 'ab'); ?>: <a href="http://bookly.ladela.com" target="_blank">http://bookly.ladela.com</a>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close', 'ab') ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->