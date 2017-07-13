<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $time_interval = get_option( 'bookly_gen_time_slot_length' );
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
                                <div class="bookly-flex-cell bookly-vertical-middle" style="width: 1%">
                                    <span class="bookly-service-color bookly-margin-right-sm bookly-js-service-color"
                                          style="background-color: <?php echo esc_attr( $service['color'] ) ?>">&nbsp;</span>
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
                                    <?php echo( $service['type'] == \BooklyLite\Lib\Entities\Service::TYPE_SIMPLE ? \BooklyLite\Lib\Utils\DateTime::secondsToInterval( $service['duration'] ) : sprintf( _n( '%d service', '%d services', count( json_decode( $service['sub_services'], true ) ), 'bookly' ), count( json_decode( $service['sub_services'], true ) ) ) ) ?>
                                </span>
                                </div>
                                <div class="bookly-flex-cell bookly-vertical-middle hidden-xs" style="width: 30%">
                                <span class="bookly-js-service-price">
                                    <?php echo \BooklyLite\Lib\Utils\Common::formatPrice( $service['price'] ) ?>
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
                            <?php do_action( 'bookly_render_service_form_contents', $service ) ?>
                            <div class="row">
                                <div class="col-md-9 col-sm-6">
                                    <div class="form-group">
                                        <label for="title_<?php echo $service_id ?>"><?php _e( 'Title', 'bookly' ) ?></label>
                                        <input name="title" value="<?php echo esc_attr( $service['title'] ) ?>" id="title_<?php echo $service_id ?>" class="form-control" type="text">
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label><?php _e( 'Color', 'bookly' ) ?></label>
                                        <div class="bookly-color-picker-wrapper">
                                            <input name="color" value="<?php echo esc_attr( $service['color'] ) ?>" class="bookly-js-color-picker" data-last-color="<?php echo esc_attr( $service['color'] ) ?>" type="hidden">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="visibility_<?php echo $service_id ?>"><?php _e( 'Visibility', 'bookly' ) ?></label>
                                        <p class="help-block"><?php _e( 'To make service invisible to your customers set the visibility to "Private".', 'bookly' ) ?></p>
                                        <select name="visibility" class="form-control" id="visibility_<?php echo $service_id ?>">
                                            <option value="public" <?php selected( $service['visibility'], 'public' ) ?>><?php _e( 'Public', 'bookly' ) ?></option>
                                            <option value="private" <?php selected( $service['visibility'], 'private' ) ?>><?php _e( 'Private', 'bookly' ) ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="price_<?php echo $service_id ?>"><?php _e( 'Price', 'bookly' ) ?></label>
                                        <input id="price_<?php echo $service_id ?>" class="form-control ab-question" type="number" min="0" step="1" name="price" value="<?php echo esc_attr( $service['price'] ) ?>">
                                    </div>
                                </div>
                                <div class="col-sm-4 ab--for-simple">
                                    <div class="form-group">
                                        <label for="capacity_<?php echo $service_id ?>"><?php _e( 'Capacity', 'bookly' ) ?></label>
                                        <p class="help-block"><?php _e( 'The maximum number of customers allowed to book the service for the certain time period.', 'bookly' ) ?></p>
                                        <input id="capacity_<?php echo $service_id ?>" class="form-control ab-question" type="number" min="1" step="1" name="capacity" value="<?php echo esc_attr( $service['capacity'] ) ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="ab--for-simple">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="duration_<?php echo $service_id ?>">
                                                <?php _e( 'Duration', 'bookly' ) ?>
                                            </label>
                                            <select id="duration_<?php echo $service_id ?>" class="form-control" name="duration">
                                                <?php for ( $j = $time_interval; $j <= 720; $j += $time_interval ) : ?><?php if ( $service['duration'] / 60 > $j - $time_interval && $service['duration'] / 60 < $j ) : ?><option value="<?php echo esc_attr( $service['duration'] ) ?>" selected><?php echo \BooklyLite\Lib\Utils\DateTime::secondsToInterval( $service['duration'] ) ?></option><?php endif ?><option value="<?php echo $j * 60 ?>" <?php selected( $service['duration'], $j * 60 ) ?>><?php echo \BooklyLite\Lib\Utils\DateTime::secondsToInterval( $j * 60 ) ?></option><?php endfor ?>
                                                <option value="86400" <?php selected( $service['duration'], DAY_IN_SECONDS ) ?>><?php _e( 'All day', 'bookly' ) ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-8">
                                        <div class="form-group">
                                            <label for="padding_left_<?php echo $service_id ?>">
                                                <?php _e( 'Padding time (before and after)', 'bookly' ) ?>
                                            </label>
                                            <p class="help-block"><?php _e( 'Set padding time before and/or after an appointment. For example, if you require 15 minutes to prepare for the next appointment then you should set "padding before" to 15 min. If there is an appointment from 8:00 to 9:00 then the next available time slot will be 9:15 rather than 9:00.', 'bookly' ) ?></p>
                                            <div class="row">
                                                <div class="col-xs-6">
                                                    <select id="padding_left_<?php echo $service_id ?>" class="form-control" name="padding_left">
                                                        <option value="0"><?php _e( 'OFF', 'bookly' ) ?></option>
                                                        <?php for ( $j = $time_interval; $j <= 720; $j += $time_interval ) : ?><?php if ( $service['padding_left'] > 0 && $service['padding_left'] / 60 > $j - $time_interval && $service['padding_left'] / 60 < $j ) : ?><option value="<?php echo esc_attr( $service['padding_left'] ) ?>" selected><?php echo \BooklyLite\Lib\Utils\DateTime::secondsToInterval( $service['padding_left'] ) ?></option><?php endif ?><option value="<?php echo $j * 60 ?>" <?php selected( $service['padding_left'], $j * 60 ) ?>><?php echo \BooklyLite\Lib\Utils\DateTime::secondsToInterval( $j * 60 ) ?></option><?php endfor ?>
                                                    </select>
                                                </div>
                                                <div class="col-xs-6">
                                                    <select id="padding_right_<?php echo $service_id ?>" class="form-control" name="padding_right">
                                                        <option value="0"><?php _e( 'OFF', 'bookly' ) ?></option>
                                                        <?php for ( $j = $time_interval; $j <= 720; $j += $time_interval ) : ?><?php if ( $service['padding_right'] > 0 && $service['padding_right'] / 60 > $j - $time_interval && $service['padding_right'] / 60 < $j ) : ?><option value="<?php echo esc_attr( $service['padding_right'] ) ?>" selected><?php echo \BooklyLite\Lib\Utils\DateTime::secondsToInterval( $service['padding_right'] ) ?></option><?php endif ?><option value="<?php echo $j * 60 ?>" <?php selected( $service['padding_right'], $j * 60 ) ?>><?php echo \BooklyLite\Lib\Utils\DateTime::secondsToInterval( $j * 60 ) ?></option><?php endfor ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="category_<?php echo $service_id ?>"><?php _e( 'Category', 'bookly' ) ?></label>
                                        <select id="category_<?php echo $service_id ?>" class="form-control" name="category_id"><option value="0"><?php _e( 'Uncategorized', 'bookly' ) ?></option>
                                            <?php foreach ( $category_collection as $category ) : ?>
                                                <option value="<?php echo $category['id'] ?>" <?php selected( $category['id'], $service['category_id'] ) ?>><?php echo esc_html( $category['name'] ) ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6 ab--for-simple">
                                    <div class="form-group">
                                        <label><?php _e( 'Providers', 'bookly' ) ?></label><br>
                                        <div class="btn-group">
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
                                                            <input type="checkbox" id="service_<?php echo $service_id ?>_all_bookly-js-check-entity" class="bookly-check-all-entities" <?php checked( $all_staff_selected ) ?>">
                                                            <?php _e( 'All staff', 'bookly' ) ?>
                                                        </label>
                                                    </a>
                                                </li>
                                                <?php foreach ( $staff_collection as $i => $staff ) : ?>
                                                    <li>
                                                        <a class="checkbox" href="javascript:void(0)">
                                                            <label>
                                                                <input type="checkbox" name="staff_ids[]" class="bookly-js-check-entity" value="<?php echo $staff['id'] ?>" <?php checked( in_array( $staff['id'], $assigned_staff_ids ) ) ?> data-staff_name="<?php echo esc_attr( $staff['full_name'] ) ?>">
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

                            <div class="form-group">
                                <label for="info_<?php echo $service_id ?>">
                                    <?php _e( 'Info', 'bookly' ) ?>
                                </label>
                                <p class="help-block">
                                    <?php printf( __( 'This text can be inserted into notifications with %s code.', 'bookly' ), '{service_info}' ) ?>
                                </p>
                                <textarea class="form-control" id="info_<?php echo $service_id ?>" name="info" rows="3" type="text"><?php echo esc_textarea( $service['info'] ) ?></textarea>
                            </div>

                            <?php do_action( 'bookly_compound_render_sub_services', $service, $service_collection, $service['sub_services'] ) ?>

                            <?php if ( has_action( 'bookly_service_schedule_render_service_settings' ) ) : ?>
                                <div class="ab--for-simple bookly-margin-bottom-xs">
                                    <a class="h4" href="#bookly_service_schedule_container_<?php echo $service_id ?>" data-toggle="collapse" role="button">
                                        <?php _e( 'Schedule', 'bookly' ) ?>
                                    </a>
                                    <div id="bookly_service_schedule_container_<?php echo $service_id ?>" class="bookly-margin-top-lg collapse in">
                                        <?php do_action( 'bookly_service_schedule_render_service_settings', $service ) ?>
                                    </div>
                                </div>
                            <?php endif ?>

                            <?php if ( has_filter( 'bookly_service_extras_find_by_service_id' ) ) : ?>
                                <div class="ab--for-simple bookly-margin-bottom-xs">
                                    <a class="h4" href="#bookly_service_extras_container_<?php echo $service_id ?>" data-toggle="collapse" role="button">
                                        <?php echo get_option( 'bookly_l10n_step_extras' ) ?>
                                    </a>
                                    <div id="bookly_service_extras_container_<?php echo $service_id ?>" class="bookly-margin-top-lg collapse in">
                                        <ul class="list-group extras-container" data-service="<?php echo $service_id ?>">
                                            <div class="form-group text-right">
                                                <button type="button" class="btn btn-success extra-new" data-spinner-size="40" data-style="zoom-in">
                                                    <span class="ladda-label"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'New Item', 'bookly' ) ?></span>
                                                </button>
                                            </div>
                                            <?php foreach ( apply_filters( 'bookly_service_extras_find_by_service_id', array(), $service_id ) as $extra ) : ?>
                                                <li class="list-group-item extra" data-extra-id="<?php echo $extra->get( 'id' ) ?>">
                                                    <div class="row">
                                                        <div class="col-lg-3">
                                                            <div class="bookly-flexbox">
                                                                <div class="bookly-flex-cell bookly-vertical-top">
                                                                    <i class="bookly-js-handle bookly-icon bookly-icon-draghandle bookly-margin-right-sm bookly-cursor-move" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                                                                </div>
                                                                <div class="bookly-flex-cell" style="width: 100%">
                                                                    <div class="form-group">
                                                                        <input name="extras[<?php echo $extra->get( 'id' ) ?>][id]"
                                                                               value="<?php echo $extra->get( 'id' ) ?>" type="hidden">
                                                                        <input name="extras[<?php echo $extra->get( 'id' ) ?>][attachment_id]"
                                                                               value="<?php echo $extra->get( 'attachment_id' ) ?>" type="hidden">

                                                                        <?php $img = wp_get_attachment_image_src( $extra->get( 'attachment_id' ), 'thumbnail' ) ?>

                                                                        <div class="extra-attachment-image bookly-thumb bookly-thumb-lg bookly-margin-right-lg"
                                                                             <?php echo $img ? 'style="background-image: url(' . $img[0] . '); background-size: cover;"' : ''  ?>
                                                                        >
                                                                            <a class="bookly-js-remove-attachment dashicons dashicons-trash text-danger bookly-thumb-delete" href="javascript:void(0)" title="<?php _e( 'Delete', 'bookly' ) ?>"
                                                                               <?php if ( !$img ) : ?>style="display: none;"<?php endif ?>>
                                                                            </a>
                                                                            <div class="bookly-thumb-edit extra-attachment" <?php if ( $img ) : ?>style="display: none;"<?php endif ?> >
                                                                                <div class="bookly-pretty">
                                                                                    <label class="bookly-pretty-indicator bookly-thumb-edit-btn"><?php _e( 'Image', 'bookly' ) ?></label>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-lg-9">
                                                            <div class="form-group">
                                                                <label for="title_extras_<?php echo $extra->get( 'id' ) ?>">
                                                                    <?php _e( 'Title', 'bookly' ) ?>
                                                                </label>
                                                                <input name="extras[<?php echo $extra->get( 'id' ) ?>][title]" class="form-control" type="text" id="title_extras_<?php echo $extra->get( 'id' ) ?>" value="<?php echo $extra->get( 'title' ) ?>">
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-sm-4">
                                                                    <div class="form-group">
                                                                        <label for="price_extras_<?php echo $extra->get( 'id' ) ?>">
                                                                            <?php _e( 'Price', 'bookly' ) ?>
                                                                        </label>
                                                                        <input name="extras[<?php echo $extra->get( 'id' ) ?>][price]" class="form-control" type="number" step="1" id="price_extras_<?php echo $extra->get( 'id' ) ?>" min="0.00" value="<?php echo $extra->get( 'price' ) ?>">
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-4">
                                                                    <div class="form-group">
                                                                        <label for="duration_extras_<?php echo $extra->get( 'id' ) ?>">
                                                                            <?php _e( 'Duration', 'bookly' ) ?>
                                                                        </label>
                                                                        <select name="extras[<?php echo $extra->get( 'id' ) ?>][duration]" id="duration_extras_<?php echo $extra->get( 'id' ) ?>" class="form-control">
                                                                            <option value="0"><?php _e( 'OFF', 'bookly' ) ?></option>
                                                                            <?php for ( $j = $time_interval; $j <= 720; $j += $time_interval ) : ?><?php if ( $extra->get( 'duration' ) > 0 && $extra->get( 'duration' ) / 60 > $j - $time_interval && $extra->get( 'duration' ) / 60 < $j ) : ?><option value="<?php echo esc_attr( $extra->get( 'duration' ) ) ?>" selected><?php echo BooklyLite\Lib\Utils\DateTime::secondsToInterval( $extra->get( 'duration' ) ) ?></option><?php endif ?><option value="<?php echo $j * 60 ?>" <?php selected( $extra->get( 'duration' ), $j * 60 ) ?>><?php echo BooklyLite\Lib\Utils\DateTime::secondsToInterval( $j * 60 ) ?></option><?php endfor ?>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-4">
                                                                    <div class="form-group">
                                                                        <label for="max_quantity_extras_<?php echo $extra->get( 'id' ) ?>">
                                                                            <?php _e( 'Max quantity', 'bookly' ) ?>
                                                                        </label>
                                                                        <input name="extras[<?php echo $extra->get( 'id' ) ?>][max_quantity]" class="form-control" type="number" step="1" id="max_quantity_extras_<?php echo $extra->get( 'id' ) ?>" min="1" value="<?php echo $extra->get( 'max_quantity' ) ?>">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="form-group text-right">
                                                                <?php \BooklyLite\Lib\Utils\Common::deleteButton( null, 'extra-delete' ) ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php endforeach ?>
                                        </ul>
                                    </div>
                                </div>
                            <?php endif ?>

                            <div class="panel-footer">
                                <input type="hidden" name="action" value="bookly_update_service">
                                <input type="hidden" name="id" value="<?php echo esc_html( $service_id ) ?>">
                                <input type="hidden" name="update_staff" value="0">
                                <?php \BooklyLite\Lib\Utils\Common::submitButton( null, 'ajax-service-send' ) ?>
                                <?php \BooklyLite\Lib\Utils\Common::resetButton( null, 'js-reset' ) ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>
<?php endif ?>