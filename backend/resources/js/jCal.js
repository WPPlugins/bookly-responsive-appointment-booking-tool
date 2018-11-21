/*
 * jCal calendar multi-day and multi-month datepicker plugin for jQuery
 *	version 0.3.7
 * Author: Jim Palmer
 * Released under MIT license.
 */
(function($) {
  $.fn.jCal = function (opt) {
    $.jCal(this, opt);
    return this;
  };

  var events = {};

  $.jCal = function (target, opt) {
    opt = $.extend({
      day:			new Date(),									// date to drive first cal
      events:       {},                                         // default events
      action:       '',                                         // action to create/update/delete a event
      staff_id:     false,                                      // calendar for selected staff
      days:			1,											// default number of days user can select
      showMonths:	1,											// how many side-by-side months to show
      monthSelect:	false,										// show selectable month and year ranges via animated comboboxen
      dCheck:		function (day) { return 'day'; },			// handler for checking if single date is valid or not - returns class to add to day cell
      callback:		function (day, days) { return true; },		// callback function for click on date
      drawBack:		function () { return true; },				// callback function for month being drawn
      selectedBG:	'rgb(0, 143, 214)',							// default bgcolor for selected date cell
      defaultBG:	'rgb(255, 255, 255)',						// default bgcolor for unselected date cell
      dayOffset:	0,											// 0=week start with sunday, 1=week starts with monday
      scrollSpeed:	150,										// default .animate() speed used
      forceWeek:	false,										// true=force selection at start of week, false=select days out from selected day
      ms:			['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      _target:		target										// target DOM element - no need to set extend this variable
    }, opt);
    opt.day = new Date(opt.day.getFullYear(), opt.day.getMonth(), 1);
    if ( !$(opt._target).data('days') ) $(opt._target).data('days', opt.days);
    var $target = $(target);
    $target.stop().empty();
    events = opt.events;
    for (var sm=0; sm < opt.showMonths; sm++)
      $target.append('<div class="jCalMo"></div>');
    opt.cID = 'c' + $('.jCalMo').length;
    $('.jCalMo', $target).each(
      function (ind) {
        drawCalControl($(this), $.extend( {}, opt, { 'ind':ind,
            'day':new Date( new Date( opt.day.getTime() ).setMonth( new Date( opt.day.getTime() ).getMonth() + ind ) ) }
        ));
        drawCal($(this), $.extend( {}, opt, { 'ind':ind,
            'day':new Date( new Date( opt.day.getTime() ).setMonth( new Date( opt.day.getTime() ).getMonth() + ind ) ) }
        ));
      });
  };

  // draw arrow controlers
  function drawCalControl ($target, opt) {
    $target.append(
      '<div class="jCal">' +
        ( (opt.ind == 0) ? '<div class="left" />' : '' ) +
        '<div class="month">' +
//        '<span class="monthYear">' + opt.day.getFullYear() + '</span>' +
        '<span class="monthName">' + opt.ml[opt.day.getMonth()] + '</span>' +
        '</div>' +
        ( (opt.ind == ( opt.showMonths - 1 )) ? '<div class="right" />' : '' ) +
        '</div>');

    // set current year
    $('.jcal_year').val(opt.day.getFullYear());

    // left arrow
    $target.find('.jCal .left').bind('click', $.extend( {}, opt ),
      function (e) {
        if ($('.jCalMask', e.data._target).length > 0) return false;
        $(e.data._target).stop();
        var mD = { w:0, h:0 };
        $('.jCalMo', e.data._target).each( function () {
          mD.w += $(this).width() + parseInt($(this).css('padding-left')) + parseInt($(this).css('padding-right'));
          var cH = $(this).height() + parseInt($(this).css('padding-top')) + parseInt($(this).css('padding-bottom'));
          mD.h = ((cH > mD.h) ? cH : mD.h);
        } );
        // save right arrow
        var right = null;
        // create new previous 12 months
        for (var i = 0; i < 12; i++ ) {
          $(e.data._target).prepend('<div class="jCalMo"></div>');
          e.data.day = new Date( $('div[id*=' + e.data.cID + 'd_]:first', e.data._target).attr('id').replace(e.data.cID + 'd_', '').replace(/_/g, '/') );
          e.data.day.setDate(1);
          e.data.day.setMonth( e.data.day.getMonth() - 1);
          drawCalControl($('.jCalMo:first', e.data._target), e.data);
          drawCal($('.jCalMo:first', e.data._target), e.data);
          // clone right arrow
          right = $('.right', e.data._target).clone(true);
        }
        // and delete previous 12 month
        for (var i = 0; i < 12; i++) {
          $('.jCalMo:last').remove();
        }
        // restore left arrow
        right.appendTo( $('.jCalMo:eq(1) .jCal', e.data._target) );
      });

    // right arrow
    $target.find('.jCal .right').bind('click', $.extend( {}, opt ),
      function (e) {
        if ($('.jCalMask', e.data._target).length > 0) return false;
        $(e.data._target).stop();
        var mD = { w:0, h:0 };
        $('.jCalMo', e.data._target).each( function () {
          mD.w += $(this).width() + parseInt($(this).css('padding-left')) + parseInt($(this).css('padding-right'));
          var cH = $(this).height() + parseInt($(this).css('padding-top')) + parseInt($(this).css('padding-bottom'));
          mD.h = ((cH > mD.h) ? cH : mD.h);
        } );
        // need save left arrow before remove first month
        var left = false;
        // create new next 12 month
        for (var i = 0; i < 12; i++) {
          $(e.data._target).append('<div class="jCalMo"></div>');
          e.data.day = new Date( $('div[id^=' + e.data.cID + 'd_]:last', e.data._target).attr('id').replace(e.data.cID + 'd_', '').replace(/_/g, '/') );
          e.data.day.setDate(1);
          e.data.day.setMonth( e.data.day.getMonth() + 1 );
          drawCalControl($('.jCalMo:last', e.data._target), e.data);
          drawCal($('.jCalMo:last', e.data._target), e.data);
          // clone left arrow
          left = $('.left', e.data._target).clone(true);
        }
        // and delete previous 12 month
        for (var i = 0; i < 12; i++) {
          $('.jCalMo:first').remove();
        }
        // restore left arrow
        left.prependTo( $('.jCalMo:eq(1) .jCal', e.data._target) );
      });
  }

  // called for each month
  function drawCal($target, opt) {

    // draw the name of week days
    for (var ds = opt.dayOffset; ds < 7; ds++) {
      $target.append('<div class="dow">' + opt.dow[ds] + '</div>');
    }
    for (var ds = 0; ds < opt.dayOffset ; ds++) {
      $target.append('<div class="dow">' + opt.dow[ds] + '</div>');
    }

    var fd              = new Date( new Date( opt.day.getTime() ).setDate(1) ); // first day of month
    var ld              = new Date( new Date( new Date( fd.getTime() ).setMonth( fd.getMonth() + 1 ) ).setDate(0) ); // last day of month
    var ldlm            = new Date( new Date( fd.getTime() ).setDate(0) );  // last day of previous month
    var fdnm            = new Date( new Date( new Date( fd.getTime() ).setMonth( fd.getMonth() + 1 ) ).setDate(1) ); // first day of next month

    // if month not start on the first day of week, render previous month days
    if (fd.getDay() != opt.dayOffset) {
      // get days from the start of week to the start of current month
      var diff = fd.getDay() < opt.dayOffset ? fd.getDay() + 7 - opt.dayOffset : Math.abs(fd.getDay() - opt.dayOffset);
      for (var d = (ldlm.getDate() - diff + 1); d <= ldlm.getDate(); d++) {
        $target.append('<div id="' + opt.cID + 'd' + d + '" class="pday">' + d + '</div>');
      }
    }

    // renders days of the current month
    for (var d = 1; d <= ld.getDate(); d++) {
      $target.append('<div id="' + opt.cID + 'd_' + (fd.getMonth() + 1) + '_' + d + '_' + fd.getFullYear() + '" class="day">' + d  + '</div>');
    }

    // if month not end at end of week
    if ( (opt.dayOffset && ld.getDay() != opt.dayOffset - 1) || (!opt.dayOffset && ld.getDay() != 6) ) {
      // get days from end of the month to end of current week
      var diff = fdnm.getDay() >= opt.dayOffset ? 6 - fdnm.getDay() + opt.dayOffset : 6 - (fdnm.getDay() - opt.dayOffset + 7);
      for (var d = 1; d <= diff + 1; d++) {
        $target.append('<div id="' + opt.cID + 'd' + d + '" class="pday">' + d + '</div>');
      }
    }

    $target.find('div[id^=' + opt.cID + 'd]:first, div[id^=' + opt.cID + 'd]:nth-child(7n+2)').before( '<br style="clear:both;" />' );
    $target.find('div[id^=' + opt.cID + 'd_]:not(.invday)').bind("mouseover mouseout click", $.extend( {}, opt ),
      function(e){
        if ($('.jCalMask', e.data._target).length > 0) return false;
        var osDate = new Date ( $(this).attr('id').replace(/c[0-9]{1,}d_([0-9]{1,2})_([0-9]{1,2})_([0-9]{4})/, '$1/$2/$3') );
        if (e.data.forceWeek) osDate.setDate( osDate.getDate() + (e.data.dayOffset - osDate.getDay()) );
        var sDate = new Date ( osDate.getTime() );
        if (e.type == 'click')
          $('div[id*=d_]', e.data._target).stop().removeClass('selectedDay').removeClass('overDay');
        for (var di = 0, ds = $(e.data._target).data('days'); di < ds; di++) {
          var currDay = $(e.data._target).find('#' + e.data.cID + 'd_' + ( sDate.getMonth() + 1 ) + '_' + sDate.getDate() + '_' + sDate.getFullYear());
          if ( currDay.length == 0 || $(currDay).hasClass('invday') ) break;
          if ( e.type == 'mouseover' )		$(currDay).addClass('overDay');
          else if ( e.type == 'mouseout' )	$(currDay).stop().removeClass('overDay');
          else if ( e.type == 'click' )		$(currDay).stop().addClass('selectedDay');
          sDate.setDate( sDate.getDate() + 1 );
        }
        if (e.type == 'click') {
          e.data.day = osDate;
          drawPopup($target, opt, this, osDate);
          if ( e.data.callback( osDate, di, this ) )
            $(e.data._target).data('day', e.data.day).data('days', di);
        }
      });

      // draw events for this month
      if (events) {
          drawEvents($target, fd.getMonth() + 1);
      }
  }

  // draw the events in calendar (called for each month)
  function drawEvents($target, month) {
      // remove old events
      $('.holidayDay', $target).removeClass('holidayDay').data('id', null);
      $('.repeatDay', $target).removeClass('repeatDay');
      // and add new
      for (var i in events) {
          if (events.hasOwnProperty(i)) {
              if (events[i].m == month) {
                  $target.find(getEventSelector(events[i]))
                      .addClass('holidayDay')
                      .addClass(events[i].hasOwnProperty('y') ? '' : 'repeatDay')
                      .data('id', i);
              }
          }
      }
  }

  // create a selector string by event
  function getEventSelector(event) {
      return 'div[id^=c12d_' + event.m + '_' + event.d + '_' + (event.hasOwnProperty('y') ? (event.y + ']') : ']');
  }

    // draw the popup on click to day
    function drawPopup($target, opt, div, day) {
        $('.popover').popover('hide');
        var $div = $(div);

        // checked or not on draw
        var ch  = $div.hasClass('holidayDay') ? 'checked="checked"' : '';
        var ch2 = $div.hasClass('repeatDay') ? 'checked="checked"' : '';
        var di  = ch ? '' : 'disabled="disabled"';
        var day = new Date(day);

        var $popup = $('<div class="text-center" style="width: 260px;">' +
            '<div class="checkbox bookly-margin-bottom-md"><label>' +
            '<img src="' + opt.loadingImg + '" style="display: none; margin: 0 10px 0 -26px">' +
            '<input type="checkbox" class="bookly-js-day-off" ' + ch + '>' +
            '<span class="bookly-toggle-label">' + opt.we_are_not_working + '</span>' +
            '</label></div><div class="checkbox"><label>' +
            '<img src="' + opt.loadingImg + '" style="display: none; margin: 0 10px 0 -26px">' +
            '<input ' + di + ' type="checkbox" class="bookly-js-repeat" ' + ch2 + '>' +
            '<span class="bookly-toggle-label">' + opt.repeat  + '</span>' +
            '</label></div></div><hr><div class="text-right"><button type="button" class="btn btn-default">' + opt.close +
            '</button></div>');

        var $day_off = $popup.find('.bookly-js-day-off');
        var $repeat  = $popup.find('.bookly-js-repeat');

        $popup.find('button').on('click', function () {
            $div.popover('hide');
        });

        // Show popover.
        $div
            .popover({
                html: true,
                placement: 'bottom',
                template: '<div class="popover bookly-jcal" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
                content: function() {
                    return $popup;
                },
                trigger: 'manual'
            })
            .popover('show');

        $('.bookly-js-day-off, .bookly-js-repeat', $popup).on('change', function () {
            var $this   = $(this);
            var day_off = $day_off.prop('checked');
            var repeat  = $repeat.prop('checked');

            if (day_off) {
                $repeat.prop('disabled', false);
            } else {
                $repeat.prop('checked', false).prop('disabled', true);
            }

            // update data on server side
            var options = {
                action  : opt.action,
                csrf_token: opt.csrf_token,
                id      : $div.data('id'),
                holiday : day_off,
                repeat  : repeat,
                day     : day.getFullYear() + '-' + (day.getMonth() + 1) + '-' + day.getDate()
            };
            if (opt.staff_id) {
                options.staff_id = opt.staff_id;
            }

            $this.prop('disabled', true).hide().prev('img').show();

            $.get(
                ajaxurl,
                options,
                function (response) {
                    $this.prop('disabled', false).show().prev('img').hide();
                    // refresh events from server
                    events = response;
                    drawEvents($target, day.getMonth() + 1);
                },
                'json'
            );
        });
    }

    $('body').on('click', function (e) {
        var $target = $(e.target);
        if (!$target.hasClass('day') && $target.closest('.popover.bookly-jcal').length == 0) {
            $('.popover.bookly-jcal').popover('hide');
        }
    });
})(jQuery);