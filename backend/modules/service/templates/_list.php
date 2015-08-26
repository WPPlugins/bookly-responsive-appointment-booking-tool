<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $time_interval  = get_option( 'ab_settings_time_slot_length' );
?>
<?php AB_Utils::notice( __( 'Settings saved.', 'bookly' ), 'notice-success', false ) ?>
<?php if ( ! empty( $service_collection ) ) : ?>
    <div class="panel-group" id="services_list" role="tablist" aria-multiselectable="true">
        <?php foreach ( $service_collection as $i => $service ) : ?>
            <?php $service_id   = $service['id'];
            $assigned_staff_ids = $service['staff_ids'] ? explode( ',', $service['staff_ids'] ) : array();
            $all_staff_selected = count( $assigned_staff_ids ) == count( $staff_collection );
            ?>
            <div class="panel panel-default service" data-service_id="<?php echo $service_id ?>">
                <div class="panel-heading" role="tab" id="s_<?php echo esc_html( $service_id ) ?>">
                    <h4 class="panel-title">
                        <span class="ab-handle ab-move" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"><i class="ab-inner-handle glyphicon glyphicon-align-justify"></i></span>
                        <span class="badge" style="background-color: <?php echo esc_attr( $service['color'] ) ?>">&nbsp;</span>
                        <a role="button" class="collapsed" data-toggle="collapse" data-parent="#services_list" href="#service_<?php echo esc_html( $service_id ) ?>" aria-expanded="false" aria-controls="service_<?php echo esc_html( $service_id ) ?>">
                            <?php echo esc_html( $service['title'] ) ?>
                        </a>
                        <div class="pull-right">
                            <div class="ab-right-nav ab-inline-block">
                                <small>
                                    <?php echo AB_DateTimeUtils::secondsToInterval( $service['duration'] ) ?>
                                    <div class="ab-inline-block" style="width: 75px;text-align: right">
                                        <?php echo AB_Utils::formatPrice( $service['price'] ) ?>
                                    </div>
                                </small>
                            </div>

                            <input style="margin: 0 0 0 5px" type="checkbox" class="service-checker"/>
                        </div>
                    </h4>
                </div>
                <div id="service_<?php echo esc_html( $service_id ) ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="s_<?php echo esc_html( $service_id ) ?>" style="height: 0px;">
                    <div class="panel-body">
                        <form method="post">
                            <div class="form-group">
                                <label for="title_<?php echo $service_id ?>"><?php _e( 'Title', 'bookly' ) ?></label>
                                <div class="row">
                                    <div class="col-sm-11 col-xs-10">
                                        <input id="title_<?php echo $service_id ?>" class="form-control" type="text" name="title" value="<?php echo esc_attr( $service['title'] ) ?>" />
                                    </div>
                                    <div class="col-sm-1 col-xs-2">
                                        <div class="service-color-wrapper">
                                            <input type="hidden" class="service-color" name="color" value="<?php echo esc_attr( $service['color'] ) ?>" data-last_color='' />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-5 col-xs-10 ab-col-responsive">
                                        <label for="duration_<?php echo $service_id ?>"><?php _e( 'Duration', 'bookly' ) ?></label>
                                        <select id="duration_<?php echo $service_id ?>" class="form-control" name="duration">
                                            <?php for ( $j = $time_interval; $j <= 720; $j += $time_interval ) : ?>
                                                <?php if ( $service['duration'] / 60 > $j - $time_interval && $service['duration'] / 60 < $j ): ?>
                                                    <option value="<?php echo esc_attr( $service['duration'] ) ?>" selected>
                                                        <?php echo AB_DateTimeUtils::secondsToInterval( $service['duration'] ) ?>
                                                    </option>
                                                <?php endif ?>
                                                <option value="<?php echo $j * 60 ?>" <?php selected( $service['duration'], $j * 60 ) ?>>
                                                    <?php echo AB_DateTimeUtils::secondsToInterval( $j * 60 ) ?>
                                                </option>
                                            <?php endfor ?>
                                            <option value="86400" <?php selected( $service['duration'], DAY_IN_SECONDS ) ?>>
                                                <?php _e( 'All day', 'bookly' ) ?>
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-sm-6 col-xs-10 ab-padding-before-after">
                                        <label for="padding_left_<?php echo $service_id ?>"><?php _e( 'Padding time (before and after)', 'bookly' ) ?></label>
                                        <div style="clear: both;"></div>
                                        <select id="padding_left_<?php echo $service_id ?>" class="form-control ab-auto-w pull-left" name="padding_left">
                                            <option value="0"><?php _e( 'OFF', 'bookly' ) ?></option>
                                            <?php for ( $j = $time_interval; $j <= 720; $j += $time_interval ) : ?>
                                                <?php if ( $service['duration'] / 60 > $j - $time_interval && $service['duration'] / 60 < $j ): ?>
                                                    <option value="<?php echo esc_attr( $service['duration'] ) ?>" selected>
                                                        <?php echo AB_DateTimeUtils::secondsToInterval( $service['duration'] ) ?>
                                                    </option>
                                                <?php endif ?>
                                                <option value="<?php echo $j * 60 ?>" <?php selected( $service['padding_left'], $j * 60 ) ?>>
                                                    <?php echo AB_DateTimeUtils::secondsToInterval( $j * 60 ) ?>
                                                </option>
                                            <?php endfor ?>
                                        </select>
                                        <select id="padding_right_<?php echo $service_id ?>" class="form-control ab-auto-w pull-right" name="padding_right">
                                            <option value="0"><?php _e( 'OFF', 'bookly' ) ?></option>
                                            <?php for ( $j = $time_interval; $j <= 720; $j += $time_interval ) : ?>
                                                <?php if ( $service['duration'] / 60 > $j - $time_interval && $service['duration'] / 60 < $j ): ?>
                                                    <option value="<?php echo esc_attr( $service['duration'] ) ?>" selected>
                                                        <?php echo AB_DateTimeUtils::secondsToInterval( $service['duration'] ) ?>
                                                    </option>
                                                <?php endif ?>
                                                <option value="<?php echo $j * 60 ?>" <?php selected( $service['padding_right'], $j * 60 ) ?>>
                                                    <?php echo AB_DateTimeUtils::secondsToInterval( $j * 60 ) ?>
                                                </option>
                                            <?php endfor ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-1 col-xs-2">
                                        <?php AB_Utils::popover( __( 'Set padding time before and/or after an appointment. For example, if you require 15 minutes to prepare for the next appointment then you should set "padding before" to 15 min. If there is an appointment from 8:00 to 9:00 then the next available time slot will be 9:15 rather than 9:00.', 'bookly' ) ) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-xs-5">
                                        <label for="price_<?php echo $service_id ?>"><?php _e( 'Price', 'bookly' ) ?></label>
                                        <input id="price_<?php echo $service_id ?>" class="form-control ab-question" type="number" min="0.00" step="any" name="price" value="<?php echo esc_attr( $service['price'] ) ?>"/>
                                    </div>
                                    <div class="col-sm-3 col-xs-5">
                                        <label for="capacity_<?php echo $service_id ?>"><?php _e( 'Capacity', 'bookly' ) ?></label>
                                        <input id="capacity_<?php echo $service_id ?>" class="form-control ab-question" type="number" min="1" step="1" name="capacity" value="<?php echo esc_attr( $service['capacity'] ) ?>"/>
                                    </div>
                                    <div class="col-xs-1">
                                        <?php AB_Utils::popover( __( 'The maximum number of customers allowed to book the service for the certain time period.', 'bookly' ) ) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-xs-5">
                                        <label for="category_<?php echo $service_id ?>"><?php _e( 'Category', 'bookly' ) ?></label>
                                        <select id="category_<?php echo $service_id ?>" class="form-control" name="category_id">
                                            <option value="0"></option>
                                            <?php foreach ( $category_collection as $category ) : ?>
                                                <option value="<?php echo $category['id'] ?>" <?php selected( $category['id'], $service['category_id'] ) ?>>
                                                    <?php echo esc_html( $category['name'] ) ?>
                                                </option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                    <div class="col-xs-7">
                                        <label><?php _e( 'Providers', 'bookly' ) ?></label>
                                        <div style="clear: both"></div>
                                        <div class="btn-group">
                                            <button class="btn btn-info" data-toggle="dropdown"><i class="glyphicon glyphicon-user"></i> <span class=staff-count><?php echo $service['total_staff'] ?></span>
                                            </button>
                                            <button class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu staff-list">
                                                <li>
                                                    <a href="javascript:void(0)">
                                                        <input type="checkbox" id="service_<?php echo $service_id ?>_all_staff" class="all-staff" <?php checked( $all_staff_selected ) ?>"/>
                                                        <label class="ab-inline" for="service_<?php echo $service_id ?>_all_staff"><?php _e( 'All staff', 'bookly' ) ?></label>
                                                    </a>
                                                </li>
                                                <?php foreach ( $staff_collection as $i => $staff ) : ?>
                                                    <li>
                                                        <a href="javascript:void(0)" style="padding-left: 30px">
                                                            <input type="checkbox" name="staff_ids[]" class="staff" value="<?php echo $staff['id'] ?>" <?php checked( in_array( $staff['id'], $assigned_staff_ids ) ) ?> data-staff_name="<?php echo esc_attr( $staff['full_name'] ) ?>" />
                                                            <label class="ab-inline" for="service_<?php echo $service_id . '_staff_' . $i ?>">
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
                                <input type="hidden" name="action" value="ab_update_service_value">
                                <input type="hidden" name="id" value="<?php echo esc_html( $service_id ) ?>">
                                <input type="hidden" name="update_staff" value="0">
                                <?php AB_Utils::submitButton( null, 'ajax-service-send' ) ?>
                                <?php AB_Utils::resetButton( null, 'js-reset' ) ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>
<?php endif ?>