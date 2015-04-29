<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="ab-annual-calendar-scroll" style="text-align: center;">
  <div class="input-prepend input-append">
    <span class="ab-week-picker-arrow prev add-on col-arrow">◄</span>
    <input style="width: 70px; text-align: center;background: white" class="span2 jcal_year" readonly="readonly" id="appendedPrependedInput" size="16" type="text" value="2014">
    <span class="ab-week-picker-arrow next add-on col-arrow">►</span>
  </div>
</div>
<div id="ab-annual-calendar"></div>

<script type="text/javascript">
  jQuery(function($) {
    var d = new Date();
    $('#ab-annual-calendar').jCal({
        day:            new Date(d.getFullYear(), 0, 1),
        days:           1,
        showMonths:     12,
        scrollSpeed:    350,
        events:         <?php echo $holidays ?>,
        action:         'ab_staff_holidays_update',
        staff_id:       <?php echo $id ?>,
        dayOffset:      <?php echo get_option('start_of_week', 0) ?>
    });
  });
</script>