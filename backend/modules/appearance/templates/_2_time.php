<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="ab-booking-form" class="ab-booking-form" style="overflow: hidden">
<!-- Progress Tracker-->
<?php $step = 2; include '_progress_tracker.php'; ?>

<div style="margin-bottom: 15px!important;" class="ab-row-fluid">
  <span data-inputclass="input-xxlarge" data-notes="<?php _e( '<b>[[SERVICE_NAME]]</b> - name of service, <b>[[STAFF_NAME]]</b> - name of staff,', 'ab' ); ?><br><?php _e( '<b>[[CATEGORY_NAME]]</b> - name of category.', 'ab' ); ?>" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_info_second_step' ) ) ?>" data-link-class="ab-text-info-second" class="ab-text-info-second-preview ab-row-fluid ab_editable" id="ab-text-info-second" data-type="textarea" data-pk="1"><?php echo esc_html( get_option( 'ab_appearance_text_info_second_step' ) ) ?></span>
</div>
<!-- timeslots -->
<div class="ab-columnizer-wrap" style="height: 400px;">
    <div class="ab-columnizer">
        <div class="ab-time-screen">
            <div class="ab-column">
                <button class="ab-available-day ab-first-child" value="">Wed, Jul 31</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>1:00 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>1:15 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>1:30 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>1:45 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>2:00 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>2:15 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>2:30 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>2:45 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>3:00 pm</button>
            </div>
            <div class="ab-column">
                <button class="ab-available-hour ab-first-child"><i class="ab-hour-icon"><span></span></i>3:15 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>3:30 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>3:45 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>4:00 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>4:15 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>4:30 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>4:45 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>5:00 pm</button>
                <button class="ab-available-day ab-first-child" value="">Thu, Aug 01</button>
                <button class="ab-available-hour ab-last-child"><i class="ab-hour-icon"><span></span></i>10:00 am</button>
            </div>
            <div class="ab-column">
                <button class="ab-available-hour ab-first-child"><i class="ab-hour-icon"><span></span></i>10:15 am</button>
                <button class="ab-available-hour"><i class="ab-hour-icon"><span></span></i>10:30 am</button>
                <button class="ab-available-hour"><i class="ab-hour-icon"><span></span></i>10:45 am</button>
                <button class="ab-available-hour"><i class="ab-hour-icon"><span></span></i>11:00 am</button>
                <button class="ab-available-hour"><i class="ab-hour-icon"><span></span></i>11:15 am</button>
                <button class="ab-available-hour"><i class="ab-hour-icon"><span></span></i>11:30 am</button>
                <button class="ab-available-hour"><i class="ab-hour-icon"><span></span></i>11:45 am</button>
                <button class="ab-available-hour"><i class="ab-hour-icon"><span></span></i>12:00 pm</button>
                <button class="ab-available-hour"><i class="ab-hour-icon"><span></span></i>12:15 pm</button>
                <button class="ab-available-hour ab-last-child""><i class="ab-hour-icon"><span></span></i>12:30 pm</button>
            </div>
            <div class="ab-column">
                <button class="ab-available-hour ab-first-child"><i class="ab-hour-icon"><span></span></i>12:45 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>1:00 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>1:15 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>3:30 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>3:45 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>4:00 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>4:15 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>4:30 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>4:45 pm</button>
                <button class="ab-available-hour ab-last-child"><i class="ab-hour-icon"><span></span></i>5:00 pm</button>
            </div>
            <div class="ab-column">
                <button class="ab-available-day ab-first-child" value="">Fri, Aug 02</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>1:00 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>1:15 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>1:30 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>1:45 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>2:00 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>2:15 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>2:30 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>2:45 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>3:00 pm</button>
            </div>
            <div class="ab-column">
                <button class="ab-available-hour ab-first-child"><i class="ab-hour-icon"><span></span></i>3:15 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>3:30 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>3:45 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>4:00 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>4:15 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>4:30 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>4:45 pm</button>
                <button class=ab-available-hour><i class="ab-hour-icon"><span></span></i>5:00 pm</button>
                <button class="ab-available-day ab-first-child" value="">Sat, Aug 03</button>
                <button class="ab-available-hour ab-last-child"><i class="ab-hour-icon"><span></span></i>10:00 am</button>
            </div>
            <div class="ab-column">
                <button class="ab-available-hour ab-first-child"><i class="ab-hour-icon"><span></span></i>10:15 am</button>
                <button class="ab-available-hour"><i class="ab-hour-icon"><span></span></i>10:30 am</button>
                <button class="ab-available-hour"><i class="ab-hour-icon"><span></span></i>10:45 am</button>
                <button class="ab-available-hour"><i class="ab-hour-icon"><span></span></i>11:00 am</button>
                <button class="ab-available-hour"><i class="ab-hour-icon"><span></span></i>11:15 am</button>
                <button class="ab-available-hour"><i class="ab-hour-icon"><span></span></i>11:30 am</button>
                <button class="ab-available-hour"><i class="ab-hour-icon"><span></span></i>11:45 am</button>
                <button class="ab-available-hour"><i class="ab-hour-icon"><span></span></i>12:00 pm</button>
                <button class="ab-available-hour"><i class="ab-hour-icon"><span></span></i>12:15 pm</button>
                <button class="ab-available-hour ab-last-child""><i class="ab-hour-icon"><span></span></i>12:30 pm</button>
            </div>
        </div>
    </div>
</div>
<div class="ab-time-buttons ab-row-fluid ab-nav-steps last-row ab-clear">
    <button class="ab-time-next ab-btn ab-right ladda-button orange zoom-in">
        <span class="ab_label">&gt;</span>
        <span class="spinner"></span>
    </button>
    <button class="ab-time-prev ab-btn ab-right ladda-button orange zoom-in">
        <span class="ab_label">&lt;</span>
        <span class="spinner"></span>
    </button>
    <button class="ab-left ab-to-first-step ab-btn ladda-button orange zoom-in">
        <span><?php _e( 'Back', 'ab' ) ?></span>
    </button>
</div>
</div>
