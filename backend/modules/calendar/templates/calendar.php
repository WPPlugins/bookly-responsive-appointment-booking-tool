<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    // wp start day
    $week_start_day = get_option('start_of_week', 1);
?>
<div ng-app=appointmentForm class="wrap">
    <div id="ab_calendar_header">
        <h2><?php _e('Calendar', 'ab') ?></h2>
        <div class="ab-nav-calendar">
            <div class="btn-group right-margin left">
                <button class="btn btn-info ab-calendar-switch-view ab-calendar-day"><?php _e('Day','ab') ?></button>
                <button class="btn btn-info ab-calendar-switch-view ab-calendar-week ab-button-active"><?php _e('Week','ab') ?></button>
            </div>
            <button class="btn btn-info ab-calendar-today right-margin left"><?php _e('Today','ab') ?></button>
            <div id="week-calendar-picker" class="ab-week-picker-wrapper left right-margin" data-first_day="<?php echo $week_start_day ?>">
                <div class="input-prepend input-append">
                    <span class="ab-week-picker-arrow prev add-on col-arrow">&#9668;</span>
                    <input class="span2 ab-date-calendar" readonly="readonly" id="appendedPrependedInput" size="16" type="text" value="" />
                    <span class="ab-week-picker-arrow next add-on col-arrow">&#9658;</span>
                </div>
                <div class="ab-week-picker"></div>
            </div>
            <div id="day-calendar-picker" class="ab-week-picker-wrapper left right-margin" style="display: none;" data-first_day="<?php echo $week_start_day ?>">
                <div class="pagination left">
                    <ul>
                        <li><a href="#" class="ab-week-picker-arrow-prev">&#9668;</a></li>
                        <li><a style="padding: 0" href="#"></a></li>
                    </ul>
                </div>
                <div class="input-append left" style="margin-right:-1px">
                    <input style="width:131px;margin-left:-2px;border-radius:0" class="span2" id="appendedInput" size="16" type="text" value="" /><span style="border-radius:0" class="add-on col-arrow">▼</span>
                </div>
                <div class="pagination left">
                    <ul>
                        <?php for ( $i = 1; $i <= 7; ++ $i ) : ?>
                            <li>
                                <a href="#" class="ab-day-of-month" <?php if ( 1 == $i ) : ?> style="border-radius:0"<?php endif; ?>></a>
                            </li>
                        <?php endfor; ?>
                        <li><a href="#" class="ab-week-picker-arrow-next">&#9658;</a></li>
                    </ul>
                </div>
            </div>
            <!--div class="btn-group right right-margin">
                <a class="btn btn-info" href="#"><i class="icon-user icon-white"></i><?php _e(' All services','ab') ?></a>
                <a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li>
                        <a href="javascript:void(0)">
                            <input style="margin-right: 5px;" type="checkbox" id="" class="all-staff left">
                            <label for=""><?php _e('All staff','ab') ?></label>
                        </a>
                    </li>
                </ul>
            </div-->
            <div class="btn-group pull-right">
                <a class="btn btn-info ab-staff-filter-button" href="javascript:void(0)">
                    <i class="icon-user icon-white"></i>
                    <span id="ab-staff-button">
                        <?php
                            $staff_numb = count($collection);
                            if ($staff_numb == 0) {
                                _e(' No staff selected','ab');
                            } else if ($staff_numb == 1) {
                                echo $collection[0]->full_name;
                            } else {
                                echo $staff_numb . ' '. __('staff members','ab');
                            }
                        ?>
                    </span>
                </a>
                <a class="btn btn-info dropdown-toggle ab-staff-filter-button" href="javascript:void(0)"><span class="caret"></span></a>
                <ul class="dropdown-menu pull-right">
                    <li>
                        <a href="javascript:void(0)">
                            <input style="margin-right: 5px;" type="checkbox" checked="checked" id="ab-filter-all-staff" class="left">
                            <label for="ab-filter-all-staff"><?php _e('All staff','ab') ?></label>
                        </a>
                        <?php foreach ($collection as $staff) : ?>
                            <a style="padding-left: 35px;" href="javascript:void(0)">
                                <input style="margin-right: 5px;" type="checkbox" checked="checked" id="ab-filter-staff-<?php echo $staff->id ?>" value="<?php echo $staff->id ?>" class="ab-staff-option left">
                                <label style="padding-right: 15px;" for="ab-filter-staff-<?php echo $staff->id ?>"><?php echo $staff->full_name ?></label>
                            </a>
                        <?php endforeach ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <?php if ( $collection ) : ?>
        <?php
            $user_names = array();
            $user_ids   = array();
        ?>
        <div id="week_calendar_wrapper">
            <div class="tabbable" style="margin-top: 20px;">
                <ul class="nav nav-tabs" style="margin-bottom:0;border-bottom: 6px solid #1f6a8c">
                    <?php foreach ($collection as $i => $staff) : ?>
                        <li class="ab-staff-tab-<?php echo $staff->id ?> ab-calendar-tab<?php echo 0 == $i ? ' active' : '' ?>" data-staff-id="<?php echo $staff->id ?>">
                            <a href="#" data-toggle="tab"><?php echo $staff->full_name ?></a>
                        </li>
                    <?php
                        $user_names[] = $staff->full_name;
                        $user_ids[]   = $staff->id;
                    ?>
                    <?php endforeach ?>
                </ul>
            </div>
            <div class="ab-calendar-element-container">
                <div class="ab-calendar-element"></div>
            </div>
        </div>
        <div id="day_calendar_wrapper" style="display: none">
            <div class="ab-calendar-element-container">
                <div class="ab-calendar-element"></div>
            </div>
        </div>
        <?php include 'appointment_form.php' ?>
        </div>
        <span id="staff_ids" style="display: none"><?php echo json_encode($user_ids) ?></span>
        <span id="ab_calendar_data_holder" style="display: none">
            <span class="ab-calendar-first-day"><?php echo $week_start_day ?></span>
            <span class="ab-calendar-time-format"><?php echo get_option( 'time_format' ) ?></span>
            <span class="ab-calendar-users"><?php echo implode( '|', $user_names ) ?></span>
        </span>
    <?php endif; ?>
</div>