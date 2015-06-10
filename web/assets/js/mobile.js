/*

    Mobile JS

    File:       /assets/js/Mobile.js
    Author:     Todd Bagley
*/

var Core = {};
var Mobile = {};

var _timeouts = {};

var ajaxSkip=0;
var commandBool='';
var counter=0;
var currentCommand='';
var currentUnitId='';
var currentUnitName='';
var lastLat='';
var lastLong='';
var mapZoomBool=0;

$(document).ready(function() {

    Mobile.isLoaded();
    Mobile.Map.initMap();

    setTimeout("if($('#div-welcome').hasClass('active')){$('#tools').trigger('click');}",1);

});

jQuery.extend(Core, {
    Environment: {
        context: function() {
            return 'vehicle/map' ;
        }
    },
    log: function(x) {
        console.log(x);
    }
});


jQuery.extend(Mobile, {

    command: function() {

        var action = ''
            ,id = ''
            ,unitId = currentUnitId
            ,unitName = currentUnitName
            ,responseElSelector = $('#div-console').find('.output')
            ,progressBarSnippet = '<div class="progress progress-striped active" style="height:10px;"><div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="height:10px;width: 100%"></div></div>'
            ,topTop=70;

        switch (currentCommand) {
            case           'locate':    action = 'locate';
                                        commandBool='';
                                        break;
            case   'starter-enable':    action = 'starter_enable';
                                        commandBool='';
                                        break;
            case  'starter-disable':    action = 'starter_disable';
                                        commandBool='';
                                        break;
            case      'reminder-on':    action = 'reminder_on';
                                        commandBool='';
                                        break;
            case     'reminder-off':    action = 'reminder_off';
                                        commandBool='';
                                        break;
                            default:
                                        break;
        }

        if (_timeouts[action]) clearTimeout(_timeouts[action]);

        if (action != '' && unitId != '') {

            commandBool = unitId ;

            $(responseElSelector).html('Sending command...'+progressBarSnippet)

            $.ajax({
                url: '/ajax/vehicle/sendCommand',
                type: 'POST',
                dataType: 'json',
                data: {
                    unit_id: unitId,
                    command_type: action
                },
                success: function(id, unitId, action, responseElSelector) { 

                    console.log('sendCommand:'+unitId+':'+action);

                    return function(responseData) {

                        var interval = 5 ;
                        var count = 0 ;
                        var limit = 12 ;
                        var timeoutFn = 0 ;
                        var success = 0;

                        if (responseData.code === 0) {

                            timeoutFn = function(responseData){

                                count++

                                if ((commandBool!=unitId)||(count>limit)){
                                    
                                    if(commandBool==''){
                                        $(responseElSelector).closest('.panel-collapse').find('btn-default').trigger('click');
                                    } else {
                                        $(responseElSelector).html('<span class="center">Timed out waiting for response.</span>');
                                    }
                                    return

                                } else {

                                    $.ajax({
                                        url: '/ajax/vehicle/getCommandStatus',
                                        type: 'POST',
                                        dataType: 'json',
                                        data: {
                                            unit_id: unitId,
                                            command_type: action
                                        },
                                        success: function(responseData) {
                                            success++;
                                            // console.log('getCommandStatus:success:'+responseData.code+':'+responseData.in_id+':'+responseData.message+'('+responseData.console+')');
                                            if($(this).closest('.popover').is(':visible')){
                                                var newTop = $(window).height() - $(this).closest('.popover').height() - topTop;
                                                $(this).closest('.popover').css({ top: newTop });
                                                // console.log(">>>>>>>>>>>>>>>>>>>>> newTop:"+newTop+' = '+$(window).height()+' - '+$(this).closest('.popover').height()+' - '+topTop);
                                                $(this).closest('.popover-content').addClass('command-popup-content-width');
                                                $(this).closest('.popover').addClass('command-popup-width');
                                            }

                                            if (responseData.code == '0') {
                                                setTimeout("$('#buttons').trigger('click')",3500);
                                                $(responseElSelector).html('<a href="javascript:void(0);" id="buttons" class="center navigation">'+responseData.message+'</a>');
                                                if( (responseData.event) ){
                                                    var event = responseData.event;
                                                    if((event.formatted_address=='')||(event.formatted_address=='undefined')){
                                                        event.formatted_address='Unknown Address';
                                                    }
                                                    event.unitname = unitName;
                                                    switch (id) {
                                                        case          'locate' :    event.eventname = 'Locate';
                                                                                    break;
                                                        case   'starter-enable':    event.eventname = 'Starter Enable';
                                                                                    break;
                                                        case  'starter-disable':    event.eventname = 'Starter Disable';
                                                                                    break;
                                                        case  'reminder-enable':    event.eventname = 'Reminder On';
                                                                                    break;
                                                        case 'reminder-disable':    event.eventname = 'Reminder Off';
                                                                                    break;
                                                    }
                                                    Map.closeInfoWindow(Vehicle.Map.map);
                                                    Map.clearMarkers(Vehicle.Map.map);
                                                    Map.addMarker(
                                                        Vehicle.Map.map,
                                                        {
                                                            id: 999,
                                                            name: unitName,
                                                            latitude: event.latitude,
                                                            longitude: event.longitude,
                                                            markerColor: 'ff0000',
                                                            eventname: event.eventname
                                                        },
                                                        false
                                                    );
                                                    if(event.eventname == 'Locate'){
                                                        // Map.openInfoWindow(Vehicle.Map.map,'locate', event.latitude, event.longitude, event , responseData.moving, responseData.duration)
                                                    } else {
                                                        // Map.openInfoWindow(Vehicle.Map.map, 'unit', event.latitude, event.longitude, event, responseData.moving, responseData.battery, responseData.signal, responseData.satellites, unitdata.territoryname);
                                                    }
                                                } else {
                                                    // $('#refresh-map-markers').trigger('click');
                                                }
                                            } else if(commandBool==unitId) {
                                                $(responseElSelector).html('Waiting for Response...'+progressBarSnippet)
                                                _timeouts[action] = setTimeout(timeoutFn, interval*1000);
                                            }
                                        },
                                        error: function() {
                                            if(count<limit){
                                                count--;
                                                _timeouts[action] = setTimeout(timeoutFn, 1);
                                            } else if(count>=limit){
                                                $(responseElSelector).html('<span class="center">Timed out waiting for response.</span>');
                                            } else {
                                                $(responseElSelector).html('<span class="center text-red">Ajax Call Error</span>');
                                            }
                                        }
                                   });

                                }
                            }

                            if (_timeouts[action]) clearTimeout(_timeouts[action]);
                            $(responseElSelector).html(responseData.message+'. Waiting for response...'+progressBarSnippet)
                            _timeouts[action] = setTimeout(timeoutFn, interval*1000);

                        } else {
                            $(responseElSelector).html(responseData.validation_error || responseData.message || '<span class="center">unknown error</span>')
                        }

                    };
                
                }(id, unitId, action, responseElSelector)
            })
        }

    },

    isLoaded: function() {

        Mobile.resizeLogin();

        $(document).on('click', '.btn', function() {
            switch($(this).attr('id')){
                case            'execute' : if((currentCommand)&&(currentUnitId)){
                                                Mobile.command();
                                            }
                                            $('#console').trigger('click');
                                            break;
                case       'reminder-off' : $('#div-confirm').find('.output').html("This command will turn off the vehicle's reminder.");
                                            $('#execute').text('OFF');
                                            $('#confirm').trigger('click');
                                            currentCommand=$(this).attr('id');
                                            break;
                case        'reminder-on' : $('#div-confirm').find('.output').html("This command will turn on the vehicle's reminder.");
                                            $('#execute').text('ON');
                                            $('#confirm').trigger('click');
                                            currentCommand=$(this).attr('id');
                                            break;
                case    'starter-disable' : $('#div-confirm').find('.output').html("This command will disable the vehicle's starter.");
                                            $('#execute').text('DISABLE');
                                            $('#confirm').trigger('click');
                                            currentCommand=$(this).attr('id');
                                            break;
                case     'starter-enable' : $('#div-confirm').find('.output').html("This command will enable the vehicle's starter.");
                                            $('#execute').text('ENABLE');
                                            $('#confirm').trigger('click');
                                            currentCommand=$(this).attr('id');
                                            break;
            }
        });

        $(document).on('change', '.dataTables-length, .dataTables-search', function() {
            Mobile.pagedReport($(this).closest('div.report-master').attr('id'));
        });

        $(document).on('keyup', '.dataTables-search', function() {
            Mobile.pagedReport($(this).closest('div.report-master').attr('id'));
        });

        $(document).on('click', '.dataTables-clear-btn', function() {
            $(this).closest('.report-master').find('.dataTables-search').val('');
            $(this).closest('.report-master').find('.dataTables-search-btn').trigger('click');
        });

        $(document).on('click', '.dataTables-search-btn', function() {
            Mobile.pagedReport($(this).closest('div.report-master').attr('id'));
        });

        $(document).on('click', '.dataTables-begin', function() {
            Mobile.pagedReport($(this).closest('div.report-master').attr('id'),'begin');
        });

        $(document).on('click', '.dataTables-previous', function() {
            Mobile.pagedReport($(this).closest('div.report-master').attr('id'),'down');
        });

        $(document).on('click', '.dataTables-next', function() {
            Mobile.pagedReport($(this).closest('div.report-master').attr('id'),'up');
        });

        $(document).on('click', '.dataTables-end', function() {
            Mobile.pagedReport($(this).closest('div.report-master').attr('id'),'end');
        });

        $(document).on('click', '.group-name', function() {
            $('#vehicles').trigger('click');
            $('#vehicle-list-table').find('.dataTables-search').val($(this).text());
            $('#vehicle-list-table').find('.dataTables-search-btn').trigger('click');
        });

        $(document).on('click', '.log-in', function() {
            $('#log-in-error-msg').html('');
            var user = $('#login-username').val();
            var pswd = $('#login-password').val();
            $.ajax({
                url: '/ajax/core/ajax',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'login',
                    _username: user,
                    _password: pswd
                },
                success: function(response) {
                    if(response.code){
                        if(!(response.message)){
                            response.message = 'Ajax Error';
                        }
                        $('#log-in-error-msg').html(response.message+'<br>&nbsp;');
                    } else {
                        $('#tools').trigger('click');
                    }
                }
            });
        });

        $(document).on('click', '.navigation', function() {

            var eid = $(this).attr('id');
            var lvl = 1;

            console.log('eid:'+eid);

            while ( (!($('#div-'+eid).hasClass('div-'+lvl))) && (lvl<10) ){
                lvl++;
            }

            if(lvl<10){
    
                console.log('lvl:'+lvl);
    
                $.each($('.div-'+lvl+'.active'), function(){
                    $(this).removeClass('active');
                });
    
                $('#div-'+eid).addClass('active');

                switch(lvl){

                    case 1 :    if(eid=='tools'){
                                    $('#vehicles').trigger('click');
                                    $('#vehicle-list-table').find('.dataTables-search-btn').trigger('click');
                                    setTimeout('Mobile.initTools()',1);
                                    setTimeout("$('#vehicle-group-table').find('.dataTables-search-btn').trigger('click')",3500);
                                } else {
                                    Mobile.resizeLogin();
                                }
                                break;

                    case 2 :    if(eid=='map'){
                                    $('#buttons').trigger('click');
                                    Mobile.Map.sizeMap();
                                } else if(eid=='groups'){
                                    // $('#vehicle-group-table').find('.dataTables-search-btn').trigger('click');
                                    // setTimeout('Mobile.initTools()',1);
                                } else if(eid=='vehicles'){
                                    // $('#vehicle-list-table').find('.dataTables-search-btn').trigger('click');
                                    // setTimeout('Mobile.initTools()',1);
                                }
                                $.each($('.navigation.active'), function(){
                                    $(this).removeClass('active');
                                });
                                $('#'+eid).addClass('active');
                                break;

                }

            }

        });
        
        $(document).on('click', '.report-address-id', function() {
            var uid = $(this).closest('td').data('unit-id');
            $('#report-address-'+uid).find('a').trigger('click');
        });
        
        $(document).on('click', '.report-address', function() {

            currentUnitId = $(this).closest('tr').find('td.address_map_link').data('unit_id');
            var battery = $(this).closest('tr').find('td.address_map_link').data('battery');
            var duration = $(this).closest('tr').find('td.address_map_link').data('duration');
            var eventname = $(this).closest('tr').find('td.address_map_link').data('event');
            var eventid = $(this).closest('tr').find('td.address_map_link').data('eventid');
            var label = $(this).closest('tr').find('td.address_map_link').data('label');
            var latitude = $(this).closest('tr').find('td.address_map_link').data('latitude');
            var longitude = $(this).closest('tr').find('td.address_map_link').data('longitude');
            var satellites = $(this).closest('tr').find('td.address_map_link').data('satellites');
            var signal = $(this).closest('tr').find('td.address_map_link').data('signal');
            var speed = $(this).closest('tr').find('td.address_map_link').data('speed');
            var status = $(this).closest('tr').find('td.address_map_link').data('status');
            var stopMove = $(this).closest('tr').find('td.address_map_link').data('stop-move');
            var state = $(this).closest('tr').find('td.address_map_link').data('state');
            var territoryname = $(this).closest('tr').find('td.address_map_link').data('territoryname');
            var unitname = $(this).closest('tr').find('td.address_map_link').data('unitname');
            var unittime = $(this).closest('tr').find('td.address_map_link').data('unittime');
            currentUnitName = unitname;

            var moving = {};
            moving.duration = duration;
            moving.speed = speed;
            moving.state = state;
            moving.status = status;

            var event = {};
            event.eventname = eventname;
            event.eventid = eventid;
            event.infomarker_address = label;
            event.latitude = latitude;
            event.longitude = longitude;
            event.formatted_address = label;
            event.speed = speed;
            event.territoryname = territoryname;
            event.unitname = unitname;
            event.display_unittime = unittime;

            var unitdata = {};
            unitdata.moving = moving;
            unitdata.battery = battery;
            unitdata.satellites = satellites;
            unitdata.signal = signal;
            unitdata.territoryname = territoryname;

            Mobile.Map.paintMap(latitude,longitude,label,eventname,unitdata);

            $('#label_unitname').html(unitname);
            $('#label_address').html(label);
            $('#label_event').html(stopMove+'&nbsp;('+duration+')&nbsp;&nbsp;&nbsp;<span class="text-black">'+eventname+'</span>');
            $('#label_time').html('<span class="text-grey">'+unittime+'</span>');

        });

        console.log('Mobile JS Loaded');
    
    },

    initTools: function() {
        var h = Math.floor($(window).height());
        var w = Math.floor($(window).width());
        h = h - $('#tools-container').find('.block').offset().top - 2 ; 
        $('body').css({ width: w+'px' });
        $('#tools-container').css({ width: w+'px' });
        $('#tools-container').find('.block').css({ height: h+'px', width: w+'px' });
    },

    resize: function() {
        if($('#div-tools').hasClass('active')){
            Mobile.initTools();
            if($('#div-map').is(':visible')){
                Mobile.Map.sizeMap();
            }
        } else {
            Mobile.resizeLogin();            
        }
    },

    resizeLogin: function() {
        // $('#tools').text($(window).width());
        var loginHeight = $(window).height();
        $('#div-login').css({ height: loginHeight+'px' , width: '100%' });
        $('#div-forgotpassword').css({ height: loginHeight+'px' , width: '100%' });
        $('#div-forgotusername').css({ height: loginHeight+'px' , width: '100%' });
    },

    Map: {

        map: undefined,

        initMap: function() {
            Mobile.Map.map = Map.initMap('map-div');
        },

        paintMap: function(latitude,longitude,label,event,unitdata) {
            Map.resetMap(Mobile.Map.map);
            if((latitude)&&(longitude)){
                var markerOptions = {};
                Map.clearMarkers(Mobile.Map.map);
                newLat = latitude;
                newLong = longitude;
                markerOptions = {
                        id: 1,
                        name: label,
                        latitude: latitude,
                        longitude: longitude,
                        eventname: event, // used in map class to get vehicle marker color
                    }
                ;
                Map.addMarker(Mobile.Map.map, markerOptions, false);
                // Map.openInfoWindow(Mobile.Map.map, 'unit', latitude, longitude, event, unitdata.moving, unitdata.battery, unitdata.signal, unitdata.satellites, unitdata.territoryname, 1);
            }
            Map.resize(Mobile.Map.map);
            Map.updateMapBound(Mobile.Map.map);
            if((latitude)&&(longitude)){
                lastLat=latitude;
                lastLong=longitude;
                Map.centerMap(Mobile.Map.map,latitude,longitude,16);
            } else if(!(mapZoomBool)) {
                mapZoomBool=1;
                Map.zoomMap(Mobile.Map.map,5);
            }
        },

        sizeMap: function() {
            counter++;
            var h = $('#div-map').closest('.block').height();
            var w = $('#div-map').width();
            h = h - 130;
            $('#div-map').css({ height: h+'px' });
            $('#map-div').css({ height: '100%' , width: '100%' });
        }

    },

    pagedReport: function(pid,pag,noskip) {

        if((!(ajaxSkip))||(noskip)){

            ajaxSkip=1;
            window.setTimeout("ajaxSkip='';",3000);

            $('#'+pid).find('thead').empty();
            $('#'+pid).find('tbody').empty();
            $('#'+pid).find('thead').append('<tr><th class="text-grey"><i>requesting data...</i></td></tr>');
            $('#'+pid).find('tbody').append('<tr><td style="height:1000px;">&nbsp;</td></tr>');

            var length = $('#'+pid).find('select.dataTables-length').val();
            var search = $('#'+pid).find('input.dataTables-search').val();
            var pageCount = $('#'+pid).find('span.dataTables-page-count').html();
            var pageTotal = $('#'+pid).find('span.dataTables-page-total').html();

            // var sidebarAlertAlert           = $('#sidebar-alert-alert').val();
            // var sidebarAlertType            = $('#sidebar-alert-type').val();
            // var sidebarContactMode          = $('#sidebar-contact-mode').val();
            // var sidebarContactGroup         = $('#sidebar-contact-group').val();
            // var sidebarContactMethod        = $('#sidebar-contact-method').val();
            // var sidebarContactSingle        = $('#sidebar-contact-single').val();
            // var sidebarDateRange            = $('#sidebar-date-range').val();
            // var sidebarLandmarkCategories   = $('#sidebar-landmark-categories').val();
            // var sidebarLandmarkGroup        = $('#sidebar-landmark-group').val();
            // var sidebarReason               = $('#sidebar-reason').val();
            // var sidebarReportType           = $('#sidebar-report-type').val();
            // var sidebarTerritoryType        = $('#sidebar-territory-type').val();
            // var sidebarTriggeredLast        = $('#sidebar-triggered-last').val();
            // var sidebarVehicleSingle        = $('#sidebar-vehicle-single').val();
            // var sidebarVehicleGroup         = $('#sidebar-vehicle-group').val();
            // var sidebarVehicleStatus        = $('#sidebar-vehicle-status').val();
            // var sidebarVerification         = $('#sidebar-verification').val();

            // var unit_id = '';
            // var activeLi = $('.sub-panel-items').find('li').filter('.active').attr('id');
            // if (activeLi) {
            //     unit_id=activeLi.split('-')[2];
            // } else if(currentUnitId) {
            //     unit_id = currentUnitId;
            // }

            // var duration = $('#stops-duration').val();
            // var date_range = $('#stops-date-range').val();

            switch(pag){
                case 'begin' : pageCount=0;
                               break;
                case  'down' : pageCount--;
                               break;
                case   'end' : pageCount=pageTotal;
                               break;
                case    'up' : pageCount++;
                               break;
            }

            if(pageCount<1){
                pageCount=1;
            }else if(pageCount>pageTotal){
                pageCount=pageTotal;
            }

            $.ajax({
                url: '/ajax/report/getFilteredScheduleReports',
                type: 'POST',
                dataType: 'json',
                data: {
                    // breadcrumbs: breadcrumbs,
                    // duration: duration,
                    // daterange: date_range,
                    length: length,
                    pag: pag,
                    pid: pid,
                    search: search,
                    pageCount: pageCount,
                    pageTotal: pageTotal,
                    // sidebarAlertAlert: sidebarAlertAlert,
                    // sidebarAlertType: sidebarAlertType,
                    // sidebarContactGroup: sidebarContactGroup,
                    // sidebarContactMethod: sidebarContactMethod,
                    // sidebarContactMode: sidebarContactMode,
                    // sidebarContactSingle: sidebarContactSingle,
                    // sidebarDateRange: sidebarDateRange,
                    // sidebarLandmarkCategories: sidebarLandmarkCategories,
                    // sidebarLandmarkGroup: sidebarLandmarkGroup,
                    // sidebarReason: sidebarReason,
                    // sidebarReportType: sidebarReportType,
                    // sidebarTerritoryType: sidebarTerritoryType,
                    // sidebarTriggeredLast: sidebarTriggeredLast,
                    // sidebarVehicleGroup: sidebarVehicleGroup,
                    // sidebarVehicleSingle: sidebarVehicleSingle,
                    // sidebarVehicleStatus: sidebarVehicleStatus,
                    // sidebarVerification: sidebarVerification,
                    // unit_id: unit_id,
                    mobile: 1
                },
                success: function(responseData) {
                breadcrumbs='';
                ajaxSkip='';
                    if(responseData.pid){
                        $('#'+responseData.pid).find('thead').empty();
                        $('#'+responseData.pid).find('tbody').empty();
                        if(responseData.code === 0){
                            $('#'+responseData.pid).find('thead').append(responseData.thead);
                            $('#'+responseData.pid).find('tbody').append(responseData.tbody);
                            $('#'+responseData.pid).find('span.dataTables-records-count').text(responseData.records);
                            $('#'+responseData.pid).find('span.dataTables-page-count').text(responseData.pageCount);
                            $('#'+responseData.pid).find('span.dataTables-page-total').text(responseData.pageTotal);
                            $('#'+responseData.pid).find('span.dataTables-current-page').text(responseData.pageCount);
                            $('#'+responseData.pid).find('span.dataTables-last-report').html(responseData.lastReport);
                        } else {
                            $('#'+responseData.pid).find('thead').append('<tr><th>Error</td></tr>');
                            $('#'+responseData.pid).find('tbody').append('<tr><td class="error">'+responseData.message+'</td></tr>');
                            $('#'+responseData.pid).find('span.dataTables-records-count').text('0');
                            $('#'+responseData.pid).find('span.dataTables-page-count').text('0');
                            $('#'+responseData.pid).find('span.dataTables-page-total').text('0');
                            $('#'+responseData.pid).find('span.dataTables-current-page').text('0');
                            $('#'+responseData.pid).find('span.dataTables-last-report').text('');
                        }
                        var h = $(window).height();
                        h = h - $('#'+responseData.pid).offset().top - 60;
                        $('#'+responseData.pid).find('.panel-report-scroll').css({ height: h+'px' });
                        // $('#vehicles').html('h:'+h);
                    } else {
                        window.location = '/logout';
                    }

                }

            });

        }

    }

});
