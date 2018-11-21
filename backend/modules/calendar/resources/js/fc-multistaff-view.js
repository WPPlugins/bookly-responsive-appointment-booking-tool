(function($) {
    var FC = $.fullCalendar; // a reference to FullCalendar's root namespace

    var MultiStaffView = FC.views.agenda.class.extend({
        initialize: function() {
            FC.views.agenda.class.prototype.initialize.apply(this, arguments);

            this.timeGrid.rangeUpdated = function() {
                var view = this.view;
                var colDates = [];
                var date;

                date = this.start.clone();
                while (date.isBefore(this.end)) {
                    for (var i = 0; i < this.view.opt('staffMembers').length; ++ i) {
                        // For each staff create separate column.
                        colDates.push(date.clone());
                    }
                    date.add(1, 'day');
                    date = view.skipHiddenDays(date);
                }

                if (this.isRTL) {
                    colDates.reverse();
                }

                this.colDates = colDates;
                this.colCnt = colDates.length;
                this.rowCnt = Math.ceil((this.maxTime - this.minTime) / this.snapDuration);
            };

            this.timeGrid.groupSegCols = function(segs) {
                var segCols = [];
                var i;

                for (i = 0; i < this.colCnt; i++) {
                    segCols.push([]);
                }

                var staffCols = {};
                for (var i = 0; i < this.view.opt('staffMembers').length; ++ i) {
                    staffCols[this.view.opt('staffMembers')[i].id] = i;
                }

                for (i = 0; i < segs.length; i++) {
                    segCols[staffCols[segs[i].event.staffId]].push(segs[i]);
                }

                return segCols;
            };

            this.timeGrid.rangeToSegs = function(range) {
                var colCnt = this.colCnt;
                var segs = [];
                var seg;
                var col;
                var colDate;
                var colRange;

                // Take staff id into account too.
                range = {
                    start: range.start.clone().stripZone(),
                    end: range.end.clone().stripZone(),
                    staffId: range.event.staffId
                };

                for (col = 0; col < colCnt; col++) {
                    colDate = this.colDates[col];
                    colRange = {
                        start: colDate.clone().time(this.minTime),
                        end: colDate.clone().time(this.maxTime),
                        staffId: this.view.opt('staffMembers')[col].id
                    };
                    seg = intersectionToSeg(range, colRange);
                    if (seg) {
                        seg.col = col;
                        segs.push(seg);
                    }
                }

                return segs;
            };

            this.timeGrid.headHtml = function() {
                var rowCellHtml = '';
                var col;

                for (col = 0; col < this.colCnt; col++) {
                    rowCellHtml += '<th class="fc-day-header fc-widget-header fc-mon">' + this.view.opt('staffMembers')[col].name + '</th>'
                }

                rowCellHtml = this.bookendCells(rowCellHtml, 'head', 0);

                return '' +
                    '<div class="fc-row ' + this.view.widgetHeaderClass + '">' +
                        '<table>' +
                            '<thead>' +
                                '<tr>' + rowCellHtml + '</tr>' +
                            '</thead>' +
                        '</table>' +
                    '</div>';
            };
        }
    });


    FC.views.multiStaff = {
        'class': MultiStaffView,  // register our class with the view system
        defaults: {
            staffMembers: [],
            allDaySlot: true,
            allDayText: 'all-day',
            slotDuration: '00:30:00',
            minTime: '00:00:00',
            maxTime: '24:00:00',
            slotEventOverlap: true // a bad name. confused with overlap/constraint system
        }
    };

    FC.views.multiStaffDay = {
        type: 'multiStaff',
        duration: { days: 1 }
    };

    function intersectionToSeg(subjectRange, constraintRange) {
        var subjectStart = subjectRange.start;
        var subjectEnd = subjectRange.end;
        var constraintStart = constraintRange.start;
        var constraintEnd = constraintRange.end;
        var segStart, segEnd;
        var isStart, isEnd;

        // Take staff id into account too.
        if (subjectEnd > constraintStart && subjectStart < constraintEnd && subjectRange.staffId == constraintRange.staffId) {

            if (subjectStart >= constraintStart) {
                segStart = subjectStart.clone();
                isStart = true;
            } else {
                segStart = constraintStart.clone();
                isStart =  false;
            }

            if (subjectEnd <= constraintEnd) {
                segEnd = subjectEnd.clone();
                isEnd = true;
            } else {
                segEnd = constraintEnd.clone();
                isEnd = false;
            }

            return {
                start: segStart,
                end: segEnd,
                isStart: isStart,
                isEnd: isEnd
            };
        }
    }
})(jQuery);