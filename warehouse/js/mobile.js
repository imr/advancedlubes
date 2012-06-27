/**
 * mobile.js - Base mobile application logic.
 *
 * Copyright 2010-2011 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @author   Michael J. Rubinsky <mrubinsk@horde.org>
 * @author   Jan Schneider <jan@horde.org>
 * @category Horde
 * @license  http://www.horde.org/licenses/gpl GPL
 * @package  Warehouse
 */
 var WarehouseMobile = {

    /**
     * List of calendars we are displaying
     */
    calendars:  [],

    /**
     * List of calendars that are currently loaded for the current view
     */
    loadedCalendars: [],

    /**
     * Event cache
     */
    ecache: {},
    cacheStart: null,
    cacheEnd: null,

    deferHash: {},
    viewRan: false,

    /**
     * The currently displayed view
     */
    view: 'day',

    /**
     * The currently selected date
     */
    date: null,

    /**
     * Temporary fix for pages not firing pagebeforecreate events properly
     */
    haveOverview: false,

    /**
     * Load all events between start and end time.
     *
     * @param Date firstDay
     * @param Date lastDay
     * @param string view    The view we are loading for (month, day)
     */
    loadEvents: function(firstDay, lastDay, view)
    {
        var dates = [firstDay, lastDay], loading = false;

        // Clear out the loaded cal cache
        WarehouseMobile.loadedCalendars = [];
        WarehouseMobile.clearView(view);
        WarehouseMobile.viewRan = false;
        $.each(WarehouseMobile.calendars, function(key, cal) {
            var startDay = dates[0].clone(), endDay = dates[1].clone(),
            cals = WarehouseMobile.ecache[cal[0]], c;
            if (typeof cals != 'undefined' &&
                typeof cals[cal[1]] != 'undefined') {

                cals = cals[cal[1]];
                c = cals[startDay.dateString()];
                while (typeof c != 'undefined' && startDay.isBefore(endDay)) {
                    if (view == 'day') {
                        WarehouseMobile.insertEvents([startDay, startDay], view, cal.join('|'));
                    }
                    startDay.addDays(1);
                    c = cals[startDay.dateString()];
                }

                c = cals[endDay.dateString()];
                while (typeof c != 'undefined' && !startDay.isAfter(endDay)) {
                    if (view == 'day') {
                        WarehouseMobile.insertEvents([endDay, endDay], view, cal.join('|'));
                    }
                    endDay.addDays(-1);
                    c = cals[endDay.dateString()];
                }
                if (startDay.compareTo(endDay) > 0) {
                    WarehouseMobile.loadedCalendars.push(cal.join('|'));
                    return;
                }
            }

            var start = startDay.dateString(), end = endDay.dateString();
            loading = true;
            HordeMobile.doAction('listEvents',
                                 {
                                   'start': start,
                                   'end': end,
                                   'cal': cal.join('|'),
                                   'view': view,
                                   'sig': start + end + (Math.random() + '').slice(2)
                                 },
                                 WarehouseMobile.loadEventsCallback
            );
        });

        if (!loading && view == 'overview') {
            WarehouseMobile.insertEvents([firstDay, lastDay], view);
        }
    },

    /**
     * Sort a collection of events as returned from the ecache
     */
    sortEvents: function(events)
    {
        var e = [];

        // Need a native array to sort.
        $.each(events, function(id, event) {
            e.push(event);
        });
        return  e.sort(function(a, b) {
           sortA = a.sort;
           sortB = b.sort;
           return (sortA < sortB) ? -1 : (sortA > sortB) ? 1 : 0;
         });
    },

    /**
     * Callback for the loadEvents AJAX request. For now, assume we are in
     * day view, wait for all calendar responses to be received and then build
     * the event elements in the listview.
     *
     * @param object data  The ajax response.
     */
    loadEventsCallback: function(data)
    {
        var start = WarehouseMobile.parseDate(data.sig.substr(0, 8)),
            end = WarehouseMobile.parseDate(data.sig.substr(8, 8)),
            dates = [start, end], view = data.view;

        WarehouseMobile.storeCache(data.events, data.cal, dates, true);
        WarehouseMobile.loadedCalendars.push(data.cal);
        WarehouseMobile.insertEvents(dates, view, data.cal);
    },

    /**
     * Inserts events into current view.
     * For Day view, builds a new listview and attaches to the DOM.
     * For Month view, hightlights dates with events.
     */
    insertEvents: function(dates, view, cal)
    {
        var key = dates[0].dateString() + dates[1].dateString() + view + cal,
        d = [dates[0].clone(), dates[1].clone()], date, events, list, key, day;

        switch (view) {
        case 'day':
        case 'overview':
            // Make sure all calendars are loaded before rendering the view.
            // @TODO: Implement LIFO queue as in warehouse.js
            if (WarehouseMobile.loadedCalendars.length != WarehouseMobile.calendars.length) {
                if (WarehouseMobile.deferHash[key]) {
                    return;
                } else {
                    WarehouseMobile.deferHash[key] = window.setTimeout(function() { WarehouseMobile.insertEvents(d, view, cal); }, 0);
                    return;
                }
            }
            if (WarehouseMobile.deferHash[key]) {
                window.clearTimeout(WarehouseMobile.deferHash[key]);
                WarehouseMobile.deferHash[key] = false;
            }
        }
        WarehouseMobile.running = true;
        switch (view) {
            case 'day':
                if (!WarehouseMobile.viewRan) {
                    WarehouseMobile.viewRan = true;
                    date = d[0].dateString();
                    events = WarehouseMobile.getCacheForDate(date);
                    events = WarehouseMobile.sortEvents(events);
                    list = $('<ul>').attr({'data-role': 'listview'});
                    $.each(events, function(index, event) {
                        list.append(WarehouseMobile.buildDayEvent(event));
                    });
                    if (!list.children().length) {
                        list.append($('<li>').text(Warehouse.text.noevents));
                    }
                    $('#dayview [data-role=content]').append(list).trigger('create');
                }
                break;

            case 'month':
                day = d[0].clone();
                while (!day.isAfter(d[1])) {
                    date = day.dateString();
                    events = WarehouseMobile.getCacheForDate(date);
                    $.each(events, function(key, event) {
                        $('#warehouseMonth' + date).addClass('warehouseContainsEvents');
                    });
                    day.next().day();
                }
                // Select current date.
                $('#warehouseMonth'+ WarehouseMobile.date.dateString()).addClass('warehouseSelected');
                WarehouseMobile.selectMonthDay(WarehouseMobile.date.dateString());
                break;

            case 'overview':
                day = d[0].clone(), haveEvent = false;
                list = $('<ul>').attr({'data-role': 'listview'});
                while (!day.isAfter(d[1])) {
                    list.append($('<li>').attr({ 'data-role': 'list-divider' }).text(day.toString('ddd') + ' ' + day.toString('d')));
                    events = WarehouseMobile.sortEvents(WarehouseMobile.getCacheForDate(day.dateString())) ;
                    $.each(events, function(index, event) {
                        list.append(WarehouseMobile.buildDayEvent(event));
                        haveEvent = true;
                    });
                    if (!haveEvent) {
                        list.append($('<li>').text(Warehouse.text.noevents));
                    }
                    haveEvent = false;
                    day.next().day();
                }
                $('#overview [data-role=content]').append(list).trigger('create');
                break;
        }
        WarehouseMobile.running = false;
    },

    /**
     * Build the dom element for an event to insert into the day view.
     *
     * @param string cal    The calendar name returned from the ajax request.
     * @param object event  The event object returned from the ajax request.
     * @param string id     The event identifier
     */
    buildDayEvent: function(event)
    {
        var id;
        if ($.isEmptyObject(event)) {
          return;
        }

        var cal = event.calendar, type = cal.split('|')[0], c = cal.split('|')[1],
        d = $('<div>').attr({'style': 'color:' + Warehouse.conf.calendars[type][c].bg}),
        item = $('<li>'), a;

        // Time
        var timeWrapper = $('<div>').addClass('warehouseTimeWrapper');
        if (event.al) {
            timeWrapper.append(Warehouse.text.allday).html();
        } else {
            var startTime = Date.parse(event.s).toString(Warehouse.conf.time_format);
            var endTime = '- ' + Date.parse(event.e).toString(Warehouse.conf.time_format);
            timeWrapper
              .append($('<div>').addClass('warehouseStartTime').append(startTime))
              .append($('<div>').addClass('warehouseEndTime').append(endTime));
        }

        e = $('<h2>').text(event.t);
        l = $('<p>').addClass('warehouseDayLocation').text(event.l);
        d.append(timeWrapper).append(e).append(l);

        // Add the link to view the event detail.
        a = $('<a>').attr({'href': '#eventview'}).click(function(ev) {
            $('#eventview [data-role=content] ul').detach();
            WarehouseMobile.loadEvent(cal, event.id, Date.parse(event.e));
        }).append(d);

        return item.append(a);
    },

    /**
     * Retrieve a single event from the server and show it.
     *
     * @param string cal  The calendar identifier.
     * @param string id   The event identifier.
     * @param Date   d    The date the event occurs.
     */
    loadEvent: function(cal, id, d)
    {
        HordeMobile.doAction('getEvent',
                             {'cal': cal, 'id': id, 'date': d.toString('yyyyMMdd')},
                             WarehouseMobile.loadEventCallback);
    },

    /**
     * Callback for loadEvent call.  Assume we are in Event view for now, build
     * the event view structure and attach to DOM.
     *
     * @param object data  The ajax response.
     */
    loadEventCallback: function(data)
    {
         if (!data.event) {
             // @TODO: Error handling.
             return;
         }

         var event = data.event;
         var ul = WarehouseMobile.buildEventView(event);
         $('#eventview [data-role=content]').append(ul).trigger('create');
    },

    /**
     * Build event view DOM structure and return the top event element.
     *
     * @param object e  The event structure returned from the ajax call.
     */
    buildEventView: function(e)
    {
         var list = $('<ul>')
            .addClass('warehouseEventDetail')
            .attr({'data-role': 'listview', 'data-inset': true});

         var loc = false;

         // Title and calendar
         var title = $('<div>').addClass('warehouseEventDetailTitle').append($('<h2>').text(e.t));
         var calendar = $('<p>').addClass('warehouseEventDetailCalendar').text(Warehouse.conf.calendars[e.ty][e.c]['name']);
         list.append($('<li>').append(title).append(calendar));

         // Time
         var item = $('<div>');
         if (e.r) {
             var recurText = Warehouse.text.recur.desc[e.r.t][(e.r.i > 1) ? 1 : 0];
             var date = Date.parse(e.s);
             switch (e.r.t) {
             case 1:
                 // Daily
                 recurText = Warehouse.text.recur[e.r.t];
                 break;
             case 2:
                 // Weekly
                 recurText = recurText.replace('#{weekday}', Warehouse.text.weekday[e.r.d]);
                 recurText = recurText.replace('#{interval}', e.r.i);
                 break;
             case 3:
                 // Monthly_Date
                 recurText = recurText.replace('#{date}', date.toString('dS'));
                 // Fall-thru
             case 4:
             case 5:
                 // Monthly_Day
                 recurText = recurText.replace('#{interval}', e.r.i);
                 break;
             case 6:
             case 7:
             default:
                 recurText = 'todo';
             }
             item.append($('<div>').addClass('warehouseEventDetailRecurring').append(recurText));
             item.append($('<div>').addClass('warehouseEventDetailRecurring').text(Warehouse.text.recur[e.r.t]));
         } else if (e.al) {
             item.append($('<div>').addClass('warehouseEventDetailAllDay').text(Warehouse.text.allday));
         } else {
             item.append($('<div>')
                .append($('<div>').addClass('warehouseEventDetailDate').text(Date.parse(e.s).toString('D'))
                .append($('<div>').addClass('warehouseEventDetailTime').text(Date.parse(e.s).toString(Warehouse.conf.time_format) + ' - ' + Date.parse(e.e).toString(Warehouse.conf.time_format))))
             );
         }
         list.append($('<li>').append(item));

         // Location
         if (e.gl) {
             loc = $('<div>').addClass('warehouseEventDetailLocation')
                .append($('<a>').attr({'data-style': 'b', 'href': 'http://maps.google.com?q=' + encodeURIComponent(e.gl.lat + ',' + e.gl.lon)}).text(e.l));
         } else if (e.l) {
             loc = $('<div>').addClass('warehouseEventDetailLocation')
                .append($('<a>').attr({'href': 'http://maps.google.com?q=' + encodeURIComponent(e.l)}).text(e.l));
         }
         if (loc) {
             list.append($('<li>').append(loc));
         }

         // Description
         if (e.d) {
           list.append($('<li>').append($('<div>').addClass('warehouseEventDetailDesc').text(e.d)));
         }

         // url
         if (e.u) {
           list.append($('<li>').append($('<a>').attr({'rel': 'external', 'href': e.u}).text(e.u)));
         }

         return list;
    },

    clearView: function(view)
    {
        switch (view) {
        case 'month':
            $('.warehouseDayDetail ul').detach();
            break;
        case 'day':
            $('#dayview [data-role=content] ul').detach();
            break;
        case 'overview':
            $('#overview [data-role=content] ul').detach();
        }
    },

    /**
     * Advance the day view by one day
     */
    showNextDay: function()
    {
        WarehouseMobile.moveToDay(WarehouseMobile.date.clone().addDays(1));
    },

    /**
     * Move the day view back by one day
     */
    showPrevDay: function()
    {
        WarehouseMobile.moveToDay(WarehouseMobile.date.clone().addDays(-1));
    },

    /**
     * Move the day view to a specific day
     *
     * @param Date date  The date to set the day view to.
     */
    moveToDay: function(date)
    {
        $('.warehouseDayDate').text(date.toString('ddd') + ' ' + date.toString('d'));
        WarehouseMobile.date = date.clone();
        WarehouseMobile.loadEvents(WarehouseMobile.date, WarehouseMobile.date, 'day');
    },

    /**
     * Advance the month view ahead one month.
     */
    showPrevMonth: function()
    {
        WarehouseMobile.moveToMonth(WarehouseMobile.date.clone().addMonths(-1));
    },

    /**
     * Move the month view back one month
     */
    showNextMonth: function()
    {
        WarehouseMobile.moveToMonth(WarehouseMobile.date.clone().addMonths(1));
    },

    /**
     * Move the month view to the month containing the specified date.
     *
     * @params Date date  The date to move to.
     */
    moveToMonth: function(date)
    {
        var dates = WarehouseMobile.viewDates(date, 'month');
        WarehouseMobile.date = date;
        WarehouseMobile.loadEvents(dates[0], dates[1], 'month');
        WarehouseMobile.buildCal(date);
        WarehouseMobile.insertEvents(dates, 'month');
    },

    /**
     * Selects a day in the month view, and displays any events it may contain.
     * Also sets the dayview to the same date, so navigating back to it is
     * smooth.
     *
     * @param string date  A date string in the form of yyyyMMdd.
     */
    selectMonthDay: function(date)
    {
        var ul = $('<ul>').attr({ 'data-role': 'listview'}),
        d = WarehouseMobile.parseDate(date), today = new Date(), text;
        $('.warehouseDayDetail ul').detach();
        if (today.dateString() == d.dateString()) {
          text = Warehouse.text.today;
        } else if (today.clone().addDays(-1).dateString() == d.dateString()) {
          text = Warehouse.text.yesterday;
        } else if (today.clone().addDays(1).dateString() == d.dateString()) {
          text = Warehouse.text.tomorrow;
        } else {
          text = d.toString('ddd') + ' ' + d.toString('d')
        }
        $('.warehouseDayDetail h4').text(text);
        $('.warehouseSelected').removeClass('warehouseSelected');
        $('#warehouseMonth' + date).addClass('warehouseSelected');
        if ($('#warehouseMonth' + date).hasClass('warehouseContainsEvents')) {
            var events = WarehouseMobile.getCacheForDate(date);
            events = WarehouseMobile.sortEvents(events);
            $.each(events, function(k, e) {
                ul.append(WarehouseMobile.buildDayEvent(e));
            });
        }
        $('.warehouseDayDetail').append(ul).trigger('create');
        WarehouseMobile.moveToDay(d);
    },

    /**
     * Calculates first and last days being displayed.
     *
     * @var Date date    The date of the view.
     * @var string view  A view name.
     *
     * @return array  Array with first and last day of the view.
     */
    viewDates: function(date, view)
    {
        var start = date.clone(), end = date.clone();

        switch (view) {
        case 'month':
            start.setDate(1);
            start.moveToBeginOfWeek(Warehouse.conf.week_start);
            end.moveToLastDayOfMonth();
            end.moveToEndOfWeek(Warehouse.conf.week_start);
            break;
        case 'summary':
            end.add(6).days();
            break;
        }

        return [start, end];
    },

    /**
     * Creates the month view calendar.
     *
     * @param Date date        The date to show in the calendar.
     */
    buildCal: function(date)
    {
        var tbody = $('.warehouseMinical table tbody');
        var dates = WarehouseMobile.viewDates(date, 'month'), day = dates[0].clone(),
        today = Date.today(), dateString, td, tr, i;

        // Remove old calendar rows.
        tbody.children().remove();

        // Update title
        $('.warehouseMinicalDate').html(date.toString('MMMM yyyy'));

        for (i = 0; i < 42; i++) {
            dateString = day.dateString();

            // Create calendar row .
            if (day.getDay() == Warehouse.conf.week_start) {
                tr = $('<tr>');
                tbody.append(tr);
            }

            // Insert day cell.
            td = $('<td>').attr({ 'id': 'warehouseMonth' + dateString, 'class': 'warehouseMonthDay' }).data('date', dateString);
            if (day.getMonth() != date.getMonth()) {
                td.addClass('warehouseMinicalEmpty');
            }

            // Highlight today.
            if (day.dateString() == today.dateString()) {
                td.addClass('warehouseToday');
            }
            td.html(day.getDate());
            tr.append(td);
            day.next().day();
        }
    },

    /**
     * Parses a date attribute string into a Date object.
     *
     * For other strings use Date.parse().
     *
     * @param string date  A yyyyMMdd date string.
     *
     * @return Date  A date object.
     */
    parseDate: function(date)
    {
        var d = new Date(date.substr(0, 4), date.substr(4, 2) - 1, date.substr(6, 2));
        if (date.length == 12) {
            d.setHours(date.substr(8, 2));
            d.setMinutes(date.substr(10, 2));
        }
        return d;
    },

    storeCache: function(events, calendar, dates, createCache)
    {
        events = events || {};

        //calendar[0] == type, calendar[1] == calendar name
        calendar = calendar.split('|');
        if (!WarehouseMobile.ecache[calendar[0]]) {
            if (!createCache) {
                return;
            }
            WarehouseMobile.ecache[calendar[0]] = {};
        }
        if (!WarehouseMobile.ecache[calendar[0]][calendar[1]]) {
            if (!createCache) {
                return;
            }
            WarehouseMobile.ecache[calendar[0]][calendar[1]] = {};
        }
        var calHash = WarehouseMobile.ecache[calendar[0]][calendar[1]];

        // Create empty cache entries for all dates.
        if (!!dates) {
            var day = dates[0].clone(), date;
            while (!day.isAfter(dates[1])) {
                date = day.dateString();
                if (!calHash[date]) {
                    if (!createCache) {
                        return;
                    }
                    if (!WarehouseMobile.cacheStart || WarehouseMobile.cacheStart.isAfter(day)) {
                        WarehouseMobile.cacheStart = day.clone();
                    }
                    if (!WarehouseMobile.cacheEnd || WarehouseMobile.cacheEnd.isBefore(day)) {
                        WarehouseMobile.cacheEnd = day.clone();
                    }
                    calHash[date] = {};
                }
                day.add(1).day();
            }
        }

        var cal = calendar.join('|');
        $.each(events, function(key, date) {
            // We might not have a cache for this date if the event lasts
            // longer than the current view
            if (typeof calHash[key] == 'undefined') {
                return;
            }

            // Store useful information in event objects.
            $.each(date, function(k, event) {
                event.calendar = cal;
                event.start = Date.parse(event.s);
                event.end = Date.parse(event.e);
                event.sort = event.start.toString('HHmmss')
                    + (240000 - parseInt(event.end.toString('HHmmss'), 10)).toPaddedString(6);
                event.id = k;
            });

            // Store events in cache.
            $.extend(calHash[key], date);
        });
    },

    /**
     * Return all events for a single day from all displayed calendars merged
     * into a single hash.
     *
     * @param string date  A yyyymmdd date string.
     *
     * @return Hash  An event hash which event ids as keys and event objects as
     *               values.
     */
    getCacheForDate: function(date, calendar)
    {
        if (calendar) {
            var cals = calendar.split('|');
            return WarehouseMobile.ecache[cals[0]][cals[1]][date];
        }

        var events = {};
        $.each(WarehouseMobile.ecache, function(key, type) {
            $.each(type, function(id, cal) {
                if (!Warehouse.conf.calendars[key][id].show) {
                    return;
                }
                if (typeof cal[date] != 'undefined') {
                    $.extend(events, cal[date]);
                }
           });
        });

        return events;
    },

    /**
     * Returns the currently displayed view, based on the visible page.
     *
     */
    currentPageView: function()
    {
        switch($.mobile.activePage) {
        case 'dayview':
            return 'day';
        case 'monthview':
            return 'month';
        }
    },

    /**
     * Handle swipe events for the current view.
     */
    handleSwipe: function(map)
    {
        switch (WarehouseMobile.view) {
        case 'day':
            if (map.type == 'swipeleft') {
                WarehouseMobile.showNextDay();
            } else {
                WarehouseMobile.showPrevDay();
            }
            break;

        case 'month':
            if (map.type == 'swipeleft') {
                WarehouseMobile.showNextMonth();
            } else {
                WarehouseMobile.showPrevMonth();
            }
        }
    },

    /**
     * Catch-all event handler for the click event.
     *
     * @param object e  An event object.
     */
    clickHandler: function(e)
    {
        var elt = $(e.target);
        while (elt && elt != window.document && elt.parent().length) {
            if (elt.hasClass('warehousePrevDay')) {
                WarehouseMobile.showPrevDay();
                return;
            }
            if (elt.hasClass('warehouseNextDay')) {
                WarehouseMobile.showNextDay();
                return;
            }
            if (elt.hasClass('warehouseMinicalNext')) {
                WarehouseMobile.showNextMonth();
                return;
            }
            if (elt.hasClass('warehouseMinicalPrev')) {
                WarehouseMobile.showPrevMonth();
                return;
            }
            if (elt.hasClass('warehouseMonthDay')) {
                WarehouseMobile.selectMonthDay(elt.data('date'));
                return;
            }
            elt = elt.parent();
        }
    },

    onDocumentReady: function()
    {
        // Set up HordeMobile.
        HordeMobile.urls.ajax = Warehouse.conf.URI_AJAX;

        // Build list of calendars we want.
        $.each(Warehouse.conf.calendars, function(key, value) {
            $.each(value, function(cal, info) {
                if (info.show) {
                    WarehouseMobile.calendars.push([key, cal]);
                }
            });
        });

        // Bind click and swipe events
        $(document).bind('vclick', WarehouseMobile.clickHandler);
        $('body').bind('swipeleft', WarehouseMobile.handleSwipe);
        $('body').bind('swiperight', WarehouseMobile.handleSwipe);
    }
};
$(WarehouseMobile.onDocumentReady);
