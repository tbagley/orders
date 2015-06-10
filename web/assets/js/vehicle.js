/*

    Vehicle JS

    File:       /assets/js/vehicle.js
    Author:     Tom Leach
*/
var lastDevice='';
var lastMapDTS='';
var lastMapRefresh='';
var mapAutoRefresh='';
var skipRefresh='';
//var wizardL2I='';
var commandBool='';


$(document).ready(function() {

    $('.BtnToggle').click(function() {
        Vehicle.BtnToggle($(this).attr('id'));
    });

    $('ul.sidebar-btn-group li').click(function() {
        $(this).closest('div').find('button.sidebar-btn-group-box').text($(this).find('a').text());
        $(this).closest('div').find('button.sidebar-btn-group-box').attr('value',$(this).attr('id'));
        switch($(this).closest('div.table-responsive').find('div.report-master').attr('id')){
            case                                                         'stops-report-all' :
            case                                                    'stops-report-frequent' :
            case                                                      'stops-report-recent' : if($('#stops-report-all').is(':visible')){
                                                                                                Core.DataTable.pagedReport('stops-report-all');
                                                                                              }else if($('#stops-report-frequent').is(':visible')){
                                                                                                Core.DataTable.pagedReport('stops-report-frequent');
                                                                                              }else if($('#stops-report-recent').is(':visible')){
                                                                                                Core.DataTable.pagedReport('stops-report-recent');
                                                                                              }
                                                                                              break;
        }
    });

	$('body').on('click', function (e) {
//console.log('body:click:start');
	    $('.has-popover').each(function () {
	        if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {

                //Leave Starter Enable/Disable panels visible
                if($('#popover-content-starter-enable-panels').is(":visible")||$('#popover-content-starter-disable-panels').is(":visible")||$('#popover-content-reminder-enable-panels').is(":visible")||$('#popover-content-reminder-disable-panels').is(":visible")||$('#popover-content-locate-on-demand-panels').is(":visible")) {
                    return true;
                }

                //Leave Add Address on Verification popup visible
                if($('#button-verification-add-address').is(":visible")) {return true;}

	            $(this).popover('hide');
	        	if($('#popover-content-starter-enable-panel-2').is(":visible"))
	        	{
		        	$('#popover-content-starter-enable-panel-1, #popover-content-starter-enable-panel-2').collapse('toggle');
	        	}
                else if($('#popover-content-starter-disable-panel-2').is(":visible"))
                {
                    $('#popover-content-starter-disable-panel-1, #popover-content-starter-disable-panel-2').collapse('toggle');
                }
                else if($('#popover-content-locate-on-demand-panel-2').is(":visible"))
                {
                    $('#popover-content-locate-on-demand-panel-1, #popover-content-locate-on-demand-panel-2').collapse('toggle');
	        	}
	        }
	    });
//console.log('body:click:end');
	});

    Vehicle.isLoaded();

    /**
     *
     * Common Functionality for Map and List
     *
     */
   if (Core.Environment.context() === 'vehicle/print') {
      Vehicle.Map.initMap();
      var unit_id = $('#detail-panel').find('.hook-editable-keys').eq(0).data('vehiclePk');
      if (unit_id) Vehicle.Common.DetailPanel.renderForPrint(unit_id);
   }
   else if (Core.Environment.context() != 'vehicle/group')
   {

        Vehicle.Common.SecondaryPanel.init();
        Vehicle.Common.SecondaryPanel.initVehicleSearch();
        Vehicle.Common.DetailPanel.initClose();
        Vehicle.Common.DetailPanel.initInfoTab();
        Vehicle.Common.DetailPanel.initCommandsTab();
        Vehicle.Common.DetailPanel.initQuickHistoryTab();
        Vehicle.Common.DetailPanel.initVerificationTab();
        Vehicle.Common.DataTables.init();

        //Vehicle.Map.DataTables.init();
        Vehicle.Common.DetailPanel.initPopOver();
        Vehicle.Map.initMap();
    }

    Core.Editable.disable();

    /**
     *
     * Page Specific Functionality
     *
     * */
    switch (Core.Environment.context()) {

        /* MAP */
        case 'vehicle/map':
            //Vehicle.Map.initMap();
            Vehicle.Map.initQuickFilters();
            //Vehicle.Map.initManageVehiclePanel();
            //Vehicle.Map.initManageVehicleIcons();
            //Vehicle.Map.initManageQuickHistory();
            //Vehicle.Map.initCommands();
            //Vehicle.Map.DataTables.init();
            Vehicle.Map.initDraggablePanelBar();
            break;

        /* BATCH COMMANDS */
        case 'vehicle/batch':
        case 'vehicle/batchqueue':
        case 'vehicle/commandhistory':
        case 'vehicle/commandqueue':
            Vehicle.Map.initQuickFilters();
            // Vehicle.List.initModal();
            // Vehicle.List.DataTables.initGroups();
            break;

        /* LIST */
        case 'vehicle/list':
            Vehicle.Map.initQuickFilters();
            // Vehicle.List.initModal();
            // Vehicle.List.DataTables.initGroups();
            Core.DataTable.pagedReport('vehicle-list-table');
            break;

        case 'vehicle/group':
            // Vehicle.Group.DataTable.init();
            // Vehicle.Group.DataTable.search();
            // Vehicle.Group.Modal.init();
            // Vehicle.Group.Edit.init();
            // Vehicle.Group.Popover.init();
            Core.DataTable.pagedReport('vehicle-group-table');
            break;

    }

});

var Vehicle = {};

jQuery.extend(Vehicle, {

    isLoaded: function() {

        $('#detail-panel').hide();
//console.log('Vehicle JS Loaded');

    },

    BtnToggle: function(eid) {

console.log('BtnToggle: function(eid):'+eid);

        $('btntoggle_recent').removeClass('BtnToggle-active');
        $('btntoggle_frequent').removeClass('BtnToggle-active');
        $('btntoggle_all').removeClass('BtnToggle-active');

        if(eid){
            $(eid).addClass('BtnToggle-active');
        }

        switch(eid){
            case 'btntoggle_recent' :   $('#tabs').tabs('select','sub-recent-stops');
                                        break;
            case 'btntoggle_frequent' : $('#tabs').tabs('select','sub-frequent-stops');
                                        break;
            case 'btntoggle_all'    :   $('#tabs').tabs('select','sub-all-events');
                                        break;
        }


    },


    // Wizard: {

    //     DeSelect: function (eid) {
    //         $('#'+eid).slideUp(300);
    //         $('#'+eid).closest('.dropdown-backdrop').remove();
    //     },

    //     Input2Link: function (eid,val) {

    //         var sss = $('#secondary-sidebar-scroll');
    //         var uid = '';

    //         if(sss){
    //             if(sss.find('li.active').attr('id')){
    //                 uid = sss.find('li.active').attr('id').split('-').pop();
    //             }
    //         }

    //         if(eid){

    //             $('#'+eid).next('div').remove();
    //             $('#'+eid).text(val);
    //             $('#'+eid).addClass('wizard-pending');
    //             $('#'+eid).attr('title','Save Request Pending...');
    //             $('#'+eid).show();

    //             if(uid){
    //                 console.log('Vehicle.Wizard.Input2Link:'+uid+':'+eid+'="'+val+'"');
    //                 Core.Ajax(eid,val,uid,'update');
    //             }

    //         }
    //         wizardL2I='';

    //     },

    //     Link2Input: function (eid,val) {

    //         switch(eid){

    //             case      'my-account-first-name' :

    //             case        'customer-first-name' :
    //             case         'customer-last-name' :
    //             case           'customer-address' :
    //             case              'customer-city' :
    //             case             'customer-state' :
    //             case           'customer-zipcode' :
    //             case      'customer-mobile-phone' :
    //             case        'customer-home-phone' :
    //             case             'customer-email' :

    //             case               'vehicle-name' :
    //             case              'vehicle-group' :
    //             case             'vehicle-status' :
    //             case    'vehicle-install-mileage' :
    //             case                'vehicle-vin' : 
    //             case               'vehicle-make' :
    //             case              'vehicle-model' :
    //             case               'vehicle-year' :
    //             case              'vehicle-color' :
    //             case      'vehicle-license-plate' :
    //             case            'vehicle-loan-id' :
    //             case       'vehicle-install-date' :
    //             case          'vehicle-installer' :
    //             case          'vehicle-installer' :
    //             case          'vehicle-installer' :
    //             case          'vehicle-installer' :
    //             case          'vehicle-installer' :
    //             case          'vehicle-installer' : break; 

    //                                       default : console.log('Vehicle.Wizard.Link2Input:'+eid+':'+val);
    //                                                 eid='';

    //         }

    //         if((eid)&&(eid!=wizardL2I)){
    //             wizardL2I=eid;
    //             $('<div class="form-group wizard-div"></div>').insertAfter('#'+eid).append('<input class="wizard-input" type="text" id="wizard-input-'+eid+'" name="wizard-input-'+eid+'" onblur="Vehicle.Wizard.Input2Link(\''+eid+'\',this.value);" value="'+val.replace('"','\"')+'" />');
    //             $('#'+eid).hide();
    //             $('#wizard-input-'+eid).focus();
    //         }

    //     },

    //     Link2Select: function (eid,val) {

    //         var sss = $('#secondary-sidebar-scroll');
    //         var uid = '';

    //         if(sss){
    //             uid = sss.find('li.active').attr('id').split('-').pop();
    //         }

    //         if(uid){
    //             console.log('Vehicle.Wizard.Input2Link:'+uid+':'+eid+'="'+val+'"');
    //             Core.Ajax(eid,'',uid,'options');
    //         }

    //     },

    //     Option2Link: function (eid,val,html) {

    //         $('#ul-'+eid).slideUp(300);
    //         $('#ul-'+eid).closest('.dropdown-backdrop').remove();
                                                            
    //         var sss = $('#secondary-sidebar-scroll');
    //         var uid = '';

    //         if(sss){
    //             uid = sss.find('li.active').attr('id').split('-').pop();
    //         }

    //         if(eid){

    //             $('#'+eid).next('div').remove();
    //             $('#'+eid).text(html);
    //             $('#'+eid).addClass('wizard-pending');
    //             $('#'+eid).attr('title','Save Request Pending...');
    //             $('#'+eid).show();

    //             if(uid){
    //                 console.log('Vehicle.Wizard.Input2Link:'+uid+':'+eid+'="'+val+'"');
    //                 Core.Ajax(eid,val,uid,'update');
    //             }

    //         }
    //         wizardL2I='';

    //     }

    // },


    Map: {

        DataTables: {

            init: function() {

                // verification table
                Vehicle.Map.DataTables.verificationDataTable = Core.DataTable.init('verification-table', 5, {
                    /*'bServerSide': true,
                    'sAjaxSource': '',*/
                    'oLanguage': {
                        'sInfo': 'Showing _START_ to _END_ of _TOTAL_ Locations'
                    }
                });
            },

            verificationDataTable: {

                fnDraw: function () {
                    // return null
                },

                fnStandingRedraw: function () {
                    // return null
                }

            }

        },

        map: undefined,

        initMap: function() {
            Vehicle.Map.map = Map.initMap('map-div');
        },

        initQuickFilters: function() {

            var $quickFilters        = $('#quick-actions').find('a'),
                $allQuickFilterIcons = $quickFilters.find('.icon16')
            ;

            $quickFilters.click(function() {
console.log('$quickFilters.click(function()');

                var $self        = $(this),
                    $secondaryPanelPagination = $('#secondary-panel-pagination'),
                    $clickedIcon = $self.find('.icon16')
                ;

                // when clicking quick filter icon, reset paging
                $secondaryPanelPagination.data('paging','');

                // if ($clickedIcon.is('.active')) {
                //     $allQuickFilterIcons.removeClass('active');

                //     $('.sub-panel-items').find('li').filter('.active').removeClass('active');
                //     $secondaryPanelPagination.data('drawMarker','');

                //     // clear out map
                //     Map.clearMarkers(Vehicle.Map.map);
                //     $('#hide-vehicle-panel').trigger('click', function() {
                //         Map.resetMap(Vehicle.Map.map);
                //     });

                //     // reset to default
                //     $('#sidebar-vehicle-status').val('All').text('All');
                //     Vehicle.Common.SecondaryPanel.fetchFilteredVehicles();
                // } else {
                //     $allQuickFilterIcons.removeClass('active');
                //     $clickedIcon.addClass('active');

                //     $secondaryPanelPagination.data('drawMarker','yes');

                //     var vehicle_filtervalue = $self.data('value');
                //     var vehicle_filterlabel = $self.data('label');

                //     $('#sidebar-vehicle-status').val(vehicle_filtervalue).text(vehicle_filterlabel);

                //     $('#hide-vehicle-panel').trigger('click', function() {
                //         //Map.resetMap(Vehicle.Map.map);
                //     });

                //     var selectedVehicleSearchTab = $('#select-vehicle-search-tab');
                //     var currenttab = selectedVehicleSearchTab.find('li').filter('.active');
                //     if (currenttab.text() == 'Search') {
                //         // switch to filter active
                //         selectedVehicleSearchTab.find('li').each(function() {

                //             var $self = $(this);

                //             if($self.text() == 'Filter') {
                //                 $self.addClass('active');
                //                 $('#vehicle-search-tab').removeClass('in active');
                //                 $('#vehicle-filter-tab').addClass('in active');
                //             } else {
                //                 $self.removeClass('active');
                //             }
                //         });
                //     }
                // }

                var v='';
                switch($self.attr('id')){
                    
                    case           'metric-installed' : v = 'installed'; 
                                                        break;

                    case           'metric-inventory' : v = 'inventory'; 
                                                        break;

                    case            'metric-landmark' : v = 'in-a-landmark'; 
                                                        break;

                    case            'metric-movement' : v = 'no-movement-in-7-days'; 
                                                        break;

                    case        'metric-nonreporting' : v = 'not-reported-in-7-days'; 
                                                        break;

                    case            'metric-reminder' : v = 'reminder-on'; 
                                                        break;

                    case        'metric-repossession' : v = 'repossession'; 
                                                        break;

                    case             'metric-starter' : v = 'starter-disabled'; 
                                                        break;
                
                }
                if($self.hasClass('quick-icon-active')){
                    v = 'all';
                }

console.log('Core:Map:initQuickFilters:');
                // triger filter search for quick filtering
                switch(Core.Environment.context()){
                    case            'vehicle/map' : // clear map markers
                                                    $('#uid-none').trigger('click');
                                                    $('#tab-filter-link').trigger('click');
                                                    if(v){
                                                        $('#sidebar-vehicle-status').val(v);
                                                        Core.DataTable.secondarySidepanelScrollGo(1);
                                                    }
                                                    break;
                    case           'vehicle/list' : // clear map markers
                                                    if(v){
                                                        $('#sidebar-vehicle-status').val(v);
                                                        $('#sidebar-vehicle-status').trigger('change');
                                                    }
                                                    break;
                }

                $('.quick-icon-active').removeClass('quick-icon-active');
                switch(v){
                    case                    'all' : break;
                    case          'in-a-landmark' : $('#metric-landmark').addClass('quick-icon-active');
                                                    break;
                    case  'no-movement-in-7-days' : $('#metric-movement').addClass('quick-icon-active');
                                                    break;                                               
                    case 'not-reported-in-7-days' : $('#metric-nonreporting').addClass('quick-icon-active');
                                                    break;
                    case            'reminder-on' : $('#metric-reminder').addClass('quick-icon-active');
                                                    break;
                    case       'starter-disabled' : $('#metric-starter').addClass('quick-icon-active');
                                                    break;
                                          default : if($('#metric-'+v).attr('id')){
                                                      $('#metric-'+v).addClass('quick-icon-active');
                                                    }
                }

            });

        },

        initDraggablePanelBar: function() {
console.log('initDraggablePanelBar');

            var $panel       = $('#detail-panel'),
                $bar         = $panel.find('.panel-heading.navbar-gradient').eq(0),
                $map         = $('#map-div'),
                $window      = $(window),
                isDragging   = false,
                startPixel   = 0 ,
                currentPixel = 0,
                mapHeight    = 0,
                newMapHeight = 0,
                //mapHeightMax = 715,//749,
                mapHeightMax = parseInt(Core.Viewport.contentHeight-35)-50,
                //mapHeightMin = 400
                mapHeightMin = mapHeightMax - parseInt($panel.css('height'))
            ;


            $('#main-content').css({
                overflow: 'hidden'
            });


            $bar.css('cursor', 'row-resize');

            $bar.on('dragstart', function(event) {
                event.preventDefault();
            });

            $bar.mousedown(function(downEvent) {

                //downEvent.stopPropagation();

                startPixel = downEvent.pageY;
                $panel.addClass('disable-selection');

                mapHeight    = parseInt($map.css('height'));

                $bar.css('cursor', 'row-resize');

                $window.mousemove(function(moveEvent) {

                    mapHeightMax = parseInt(Core.Viewport.contentHeight-35)-50;
                    mapHeightMin = mapHeightMax - parseInt($panel.css('height'));

                    currentPixel = moveEvent.pageY;
                    newMapHeight = mapHeight + (currentPixel - startPixel);



                    // max height
                    if (newMapHeight > mapHeightMax) {
                        newMapHeight = mapHeightMax;
                        $window.unbind('mousemove');
                        $bar.css('cursor', 'n-resize');

                    }

                    // min-height
                    if (newMapHeight < mapHeightMin) {
                        newMapHeight = mapHeightMin;
                        $window.unbind('mousemove');
                        $bar.css('cursor', 's-resize');

                    }

                    $('#map-div').css('height', newMapHeight);

                    isDragging = true;

                });
            });

            $('body').find('.wrap').mouseup(function() {

                var wasDragging = isDragging;
                isDragging      = false;

                var unitId = $('#detail-panel').find('.hook-editable-keys').data('vehiclePk'),
                    $selectedItem = $('#vehicle-li-'+unitId),
                    latitude = $selectedItem.data('latitude'),
                    longitude = $selectedItem.data('longitude')
                ;

                if (wasDragging) {
                    $panel.removeClass('disable-selection');
                    $window.unbind('mousemove');//$bar.css('cursor', 'default');
                    //$bar.css('cursor', 'row-resize');
                    Map.resize(Vehicle.Map.map);

                    if (latitude != null && longitude != null) {

                        if ($('#info_window_div').length > 0) {     //  re-center map taking into consideration info window
                            latitude = parseFloat(latitude) + 0.00027;//0.0035;
                        }

                        Map.centerMap(Vehicle.Map.map, latitude, longitude);
                    }
                } else {
                    $window.unbind('mousemove');
                }
            });
        },

        paginateActiveVehicles: function(activeUnits) {
            // if there were active units that need to be shown
            if (activeUnits != undefined && activeUnits.length > 0) {
                var $me = {},
                    id = 0,
                    length = activeUnits.length,
                    lastIndex = length - 1,
                    callback = undefined,
                    activeId = []
                ;

                // iterate through each unit currently on the map and mark them as active
                $.each(activeUnits, function(key, value) {
                    id = $(this).prop('id').split('-')[2];
                    $me = $('#vehicle-li-'+id);
                    if ($me.length > 0) {
                        $me.addClass('active');
                        activeId.push({unit_id: id, event_id: $me.data('eventId')});
                    } else {
                        if (lastIndex == 0) {
                            callback = function() {
                                $('#hide-vehicle-panel').trigger('click', function() {
                                    Map.resetMap(Vehicle.Map.map);
                                });
                            };
                        } else if (key == lastIndex && length != 1) {
                            callback = function() {
                                //Map.resetMap(Vehicle.Map.map);
                            };
                        }

                        //Map.removeMarker(Vehicle.Map.map, id, callback);
                    }
                    if (key == lastIndex) {
                        // if only one unit is active on the list but there was more than one unit active before, render the detail panel for the unit
                        if (activeId.length == 1 && length != 1) {
                            var singleItemId = activeId[0].unit_id,
                                eventId = activeId[0].event_id
                            ;

console.log('singleItemId=='+singleItemId);

                            $.ajax({
                                url: '/ajax/vehicle/getVehicleInfo',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    unit_id: singleItemId,
                                    event_id: eventId
                                },
                                success: function(responseData) {

console.log('responseData.permission.locate=='+responseData.permission.locate);
                                    if(responseData.permission.locate=='Y'){
                                        $('#div-command-locate').show();
                                        $('#div-command-last-event').hide();
                                    } else {
                                        $('#div-command-locate').hide();
                                        $('#div-command-last-event').show();
                                    }
console.log('responseData.permission.buzzer=='+responseData.permission.buzzer);
                                    if(responseData.permission.buzzer=='Y'){
                                        $('#div-command-reminder').show();
                                    }
console.log('responseData.permission.starter=='+responseData.permission.starter);
                                    if(responseData.permission.starter=='Y'){
                                        $('#div-command-starter').show();
                                    }

                                    if (responseData.code === 0) {
                                        var unitdata = responseData.data;
                                        Vehicle.Common.DetailPanel.render(unitdata, function() {
                                            Map.resize(Vehicle.Map.map);
                                            Map.updateMapBound(Vehicle.Map.map);
                                            if (! $.isEmptyObject(unitdata.eventdata)) {
console.log('painting map-bubble');
                                                Map.openInfoWindow(Vehicle.Map.map, 'unit', unitdata.eventdata.latitude, unitdata.eventdata.longitude, unitdata.eventdata);
                                            } else {
                                                Map.resetMap(Vehicle.Map.map);
                                            }
                                        });
                                    } else {
                                        if ($.isEmptyObject(responseData.validaton_errors) === false) {
                                            //	display validation errors
                                        }
                                    }

                                    if ($.isEmptyObject(responseData.message) === false) {
                                        //	display messages
                                    }
                                }
                            });
                        }
                    }
                });
            }
        }
    },

    List: {

        DataTables: {

            initGroups: function() {

                Vehicle.List.DataTables.vehicleListTable = Core.DataTable.init('vehicle-list-table', 20, {
                    'oLanguage': {
                        'sInfo': 'Showing _START_ to _END_ of _TOTAL_ Vehicles'
                    },
                    //"aLengthMenu": [[20, 50, 100]],
                    //"sScrollY": "400px",
                    //"bScrollCollapse": true,
                    'bServerSide': true,
                    'sAjaxSource':   '/ajax/vehicle/getFilteredVehicleList',
                    'aoColumns': [
                        { 'mDataProp': 'unitname' },
                        { 'mDataProp': 'unitgroupname' },
                        { 'mDataProp': 'eventstatus' },
                        { 'mDataProp': 'duration' },
                        { 'mDataProp': 'formatted_address' },
                        { 'mDataProp': 'lastevent' },
                        { 'mDataProp': 'display_unittime' },
                        { 'mDataProp': 'mileage' }
                    ],
            		'aoColumnDefs': [
            		    { 'sClass': 'col-vehicle no-wrap',   'aTargets': [0] },
            		    { 'sClass': 'col-group',             'aTargets': [1], "bSearchable": false },
            		    { 'sClass': 'col-attribute no-wrap', 'aTargets': [2], "bSearchable": false },
            		    { 'sClass': 'col-duration',          'aTargets': [3], "bSearchable": false },
            		    { 'sClass': 'col-address',           'aTargets': [4], "bSearchable": false },
            		    { 'sClass': 'col-event no-wrap',     'aTargets': [5], "bSearchable": false },
            		    { 'sClass': 'col-event-time',        'aTargets': [6], "bSearchable": false },
            		    { 'sClass': 'col-mileage',           'aTargets': [7], "bSearchable": false }
                    ],
                    'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
                        // set <a> tag name and add data-event-id attribute to tr
                        $('td:eq(0)', nRow).html('<a href="#">'+aData.unitname+'</a>');
                        $(nRow).data('eventId', aData.rid);

                        return nRow;
                    },
                    'fnServerParams': function (aoData) {

                        var vehicle_group_id        = $('#sidebar-vehicle-single').val().trim();
                        var vehicle_state_status    = $('#sidebar-vehicle-status').val().trim();
                        var toDay                   = new Date();
                        var currentTime             = Core.StringUtility.filterEndDateConversion(toDay, 'today');
                        var string_search           = '';

                        var searchvehiclestring = $('#text-vehicle-search').val().trim();
                        if (typeof(searchvehiclestring) != 'undefined' && searchvehiclestring != '')
                        {
                            string_search           = searchvehiclestring;
                            vehicle_group_id        = 'All';
                            vehicle_state_status    = 'All';
                        }

                        aoData.push({name: 'vehicle_group_id', value: vehicle_group_id});
                        aoData.push({name: 'current_time', value: currentTime});
                        aoData.push({name: 'string_search', value: string_search});
                        aoData.push({name: 'vehicle_state_status', value: vehicle_state_status});
                    }
                });

            }
        },

        initModal: function() {
            $(document).on('click', '.col-vehicle a', function() {
console.log('initModal:body:click:.col-vehicle a');
                var $self         = $(this),
                    $trNode       = $(this).closest('tr'),
                    unitId        = $trNode.attr('id').split('-')[2],
                    eventId       = $trNode.data('eventId'),
                    $modal        = $('#modal-vehicle-list'),
                    $vehicleLocation = $modal.find('.modal-location').eq(0)
                ;

                if (unitId != undefined && eventId != undefined) {
                    $.ajax({
                        url: '/ajax/vehicle/getVehicleInfo',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            unit_id: unitId,
                            event_id: eventId
                        },
                        success: function(responseData) {

                            if (responseData.code === 0) {
                                var unitdata = responseData.data;

                                Vehicle.Common.DetailPanel.render(unitdata);

                                Core.Dialog.launch('#'+$modal.prop('id'), unitdata.unitname, {
                                        width: '1080px'
                                    }, {
                                        hidden: function() {
                                            Vehicle.Common.DetailPanel.reset();
                                        },
                                        shown: function() {

                                            // show address in modal title
                                            var address = $self.closest('td').siblings('.col-address').html();
                                            $vehicleLocation.html('<span class="vehicle-location-label">@ '+address+'</span>');
                                            Map.resize(Vehicle.Map.map);
                                            Map.resetMap(Vehicle.Map.map);
                                            Map.clearMarkers(Vehicle.Map.map);
                                            if (! $.isEmptyObject(unitdata.eventdata)) {
                                                Map.addMarker(
                                                    Vehicle.Map.map,
                                                    {
                                                        id: unitdata.unit_id,
                                                        name: unitdata.unitname,
                                                        latitude: unitdata.eventdata.latitude,
                                                        longitude: unitdata.eventdata.longitude,
                                                        eventname: unitdata.eventdata.eventname,
                                                        click: function() {
                                                            Map.getVehicleEvent(Vehicle.Map.map, unitdata.unit_id, unitdata.eventdata.id);
                                                        }
                                                    },
                                                    true
                                                );
                                                Map.updateMarkerBound(Vehicle.Map.map);
                                                Map.updateMapBound(Vehicle.Map.map);
console.log('painting map-bubble');
                                                Map.openInfoWindow(Vehicle.Map.map, 'unit', unitdata.eventdata.latitude, unitdata.eventdata.longitude, unitdata.eventdata, unitdata.moving, unitdata.battery, unitdata.signal, unitdata.satellites, unitdata.territoryname);
                                                // Map.openInfoWindow(Vehicle.Map.map, 'unit', unitdata.eventdata.latitude, unitdata.eventdata.longitude, unitdata.eventdata);
                                            }
                                            //$vehicleLabel.next().remove(); // remove any previous address in the modal title
                                            //$vehicleLabel.after('<small><wbr />&nbsp;@ <span class="vehicle-location-label">'+address+'</span></small>');

                                        }
                                    });

                            } else {
                                if ($.isEmptyObject(responseData.validaton_errors) === false) {
                                    //	display validation errors
                                }
                            }
                            /*
                            if ($.isEmptyObject(responseData.message) === false) {
                                Core.SystemMessage.show(responseData.message, responseData.code);
                            }
                            */
                        }
                    });
                }
            });
        }
    },

    Group: {

        DataTable: {

            init: function() {
                Vehicle.Group.DataTable.vehicleGroupListTable = Core.DataTable.init('vehicle-group-list-table', 20, {
                    'oLanguage': {
                        'sInfo': 'Showing _START_ to _END_ of _TOTAL_ Vehicle Groups'
                    },
                    'bServerSide': true,
                    'sAjaxSource':   '/ajax/vehicle/getFilteredVehicleGroupList',
                    'aoColumns': [
                        { 'mDataProp': 'unitgroupname' },
                        { 'mDataProp': 'unitcount' }
                    ],
                    'aoColumnDefs': [
                        { 'sClass': 'col-name no-wrap', 'aTargets': [0] },
                        { 'sClass': 'col-count', 'aTargets': [1] }
                    ],
                    'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
                        // set <a> tag name and add data-group-id attribute to tr
                        $('td:eq(0)', nRow).html('<a href="#">'+aData.unitgroupname+'</a>');
                        $(nRow).data('groupId', aData.unitgroup_id);

                        return nRow;
                    },
                    'fnServerParams': function (aoData) {
                        var string_search = '';

                        var searchvehiclestring = $('#text-vehicle-search').val().trim();
                        if (typeof(searchvehiclestring) != 'undefined' && searchvehiclestring != '')
                        {
                            string_search           = searchvehiclestring;
                        }

                        aoData.push({name: 'string_search', value: string_search});
                    }
                });
            },

            search: function() {
                var $vehicleSearch      = $('#text-vehicle-search');
                var $vehicleSearchGo    = $('#vehicle-search-go');

                $vehicleSearch.on('keyup', function () {
                    // get current search string
                    var searchvehiclestring = $vehicleSearch.val().trim();

                    if (searchvehiclestring.length > 1 || searchvehiclestring.length == 0) {
                        Vehicle.Group.DataTable.vehicleGroupListTable.fnDraw();
                    }
                });

                $vehicleSearchGo.on('click', function () {
console.log('Group:DataTable:search:$vehicleSearchGo.on:click');
                    // get current search string
                    var searchvehiclestring = $vehicleSearch.val().trim();

                    if (searchvehiclestring != '') {
                        Vehicle.Group.DataTable.vehicleGroupListTable.fnDraw();
                    }
                });

            }
        },

        Modal: {

            init: function() {
                $(document).on('click', '.col-name a, #new-vehicle-group-modal-trigger', function() {
console.log('Group:Modal:init:click:.col-name a, #new-vehicle-group-modal-trigger');
                    var $self = $(this),
                        //$trNode = $self.closest('tr'),
                        //vehicleGroupId = $trNode.attr('id').split('-')[2],
                        $modal                      = $('#modal-vehicle-group-list'),
                        $vehicleGroupAssignment     = $('#vehicle-group-assignment'),
                        $vehicleGroupAvailableList  = $vehicleGroupAssignment.find('.drag-drop-available'),
                        $vehicleGroupAssignedList   = $vehicleGroupAssignment.find('.drag-drop-assigned'),
                        $navTabContainer            = $('#vehicle-group-tabs'),
                        $navTabs                    = $navTabContainer.find('li'),
                        $tabPanes                   = $modal.find('.tab-pane'),
                        $saveNewGroupButton         = $('#new-vehicle-group-confirm')
                    ;

                    var isNewVehicleGroup = $self.is('#new-vehicle-group-modal-trigger');

                    // Editing Vehicle Group
                    if ( ! isNewVehicleGroup) {

                        var $trNode        = $self.closest('tr'),
                            vehicleGroupId = $trNode.attr('id').split('-')[2]
                        ;

                        if (vehicleGroupId != undefined) {

console.log('click:'+vehicleGroupId);

                            /*$.ajax({
                                url: '/ajax/vehicle/getVehicleGroupInfo',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    group_id: vehicleGroupId
                                },
                                success: function(responseData) {
                                    if (responseData.code === 0) {
                                        var unitgroupdata = responseData.data.vehiclegroup_data;
                                        if (! $.isEmptyObject(unitgroupdata)) {
                                            //Core.Dialog.launch('#'+$modal.prop('id'), unitgroupdata.unitgroupname, {
                                            Core.Dialog.launch('#'+$modal.prop('id'), 'Vehicle Group', {
                                                //width: '1000px'
                                                width: '598px'
                                            },
                                            {
                                                hidden: function() {
                                                    // redraw vehicle group list table
                                                    Vehicle.Group.DataTable.vehicleGroupListTable.fnStandingRedraw();

                                                    // hide 'More Options' section
                                                    var $toggle = $('#vehicle-group-more-options-toggle').find('small');
                                                    if ($toggle.text() == 'Show Less Options') {
                                                        $toggle.trigger('click');
                                                    }

                                                    // destroy any DragDrop containers after closing modal
                                                    Core.DragDrop.destroy();

                                                },
                                                hide: function() {
                                                    // close filter popover
                                                    $('#popover-available-filter-cancel').trigger('click');
                                                    // clear available and assigned vehicle list
                                                    $($vehicleGroupAvailableList.selector+','+$vehicleGroupAssignedList.selector).html('');

                                                    // reset tabs
                                                    $navTabs.removeClass('active');
                                                    $navTabs.first().addClass('active');
                                                    $tabPanes.removeClass('active').removeClass('in');
                                                    $tabPanes.first().addClass('active').addClass('in');

                                                    $navTabs.find('a[href="#vehicle-group-assignment-tab"]').addClass('active').addClass('in');
                                                },
                                                shown: function() {

                                                    var $detailPanel   = $('#detail-panel'),
                                                        $moreOptions   = $('#vehicle-group-more-options-toggle'),
                                                        $hideIfDefault = $detailPanel.find('.hide-if-default'),
                                                        defaultGroupId = responseData.data.defaultgroup_id != undefined ? responseData.data.defaultgroup_id : 0
                                                    ;

                                                    $detailPanel.find('.hook-editable-keys').data('vehicleGroupPk', vehicleGroupId).data('vehicleDefaultGroupId', defaultGroupId);

                                                    // Don't allow editing of default group
                                                    if (unitgroupdata.unitgroupname.toLowerCase() == 'default' || defaultGroupId == 0) {
                                                        if (unitgroupdata.unitgroupname.toLowerCase() == 'default') {
                                                            $('#not-editable-group-name').show();
                                                            $('#editable-group-name').add($moreOptions)
                                                                                     .add($hideIfDefault)
                                                                                     .hide()
                                                            ;
                                                        } else {
                                                            $('#not-editable-group-name').hide();
                                                            $('#editable-group-name').show();
                                                            $moreOptions.show();
                                                            $hideIfDefault.hide();

                                                            Core.Editable.setValue($('#vehicle-group-name'), unitgroupdata.unitgroupname);
                                                        }

                                                        $detailPanel.closest('.modal-dialog').css('width', '666px');
                                                    } else {
                                                        $('#not-editable-group-name').hide();
                                                        $('#editable-group-name').add($moreOptions)
                                                                                 .add($hideIfDefault)
                                                                                 .show()
                                                        ;
                                                        Core.Editable.setValue($('#vehicle-group-name'), unitgroupdata.unitgroupname);
                                                    }

                                                    // create assigned vehicle list
                                                    if (! $.isEmptyObject(unitgroupdata.assigned_vehicles)) {
                                                        var assigned_vehicles = unitgroupdata.assigned_vehicles;
                                                        //console.log(assigned_contacts);
                                                        $.each(assigned_vehicles, function() {
                                                            $vehicleGroupAssignedList.append(Core.DragDrop._generateGroupMarkup(this.unit_id, this.unitname));
                                                        });
                                                    }

                                                    // create available vehicle list
                                                    if (! $.isEmptyObject(unitgroupdata.available_vehicles)) {
                                                        var available_vehicles = unitgroupdata.available_vehicles;
                                                        //console.log(assigned_contacts);
                                                        $.each(available_vehicles, function() {
                                                            $vehicleGroupAvailableList.append(Core.DragDrop._generateGroupMarkup(this.unit_id, this.unitname));
                                                        });
                                                    }

                                                    var $unitGroupButton = $('#move-to-group'),
                                                        $unitGroupDropdown = $unitGroupButton.siblings('ul').eq(0)
                                                    ;

                                                    // update unitgroup dropdown
                                                    if (! $.isEmptyObject(responseData.data.vehicle_groups)) {
                                                        var vehicleGroups = responseData.data.vehicle_groups,
                                                            lastIndex = vehicleGroups.length - 1,
                                                            html = ''
                                                        ;

                                                        $.each(vehicleGroups, function(key, value) {
                                                            html += '<li><a data-value="'+value.unitgroup_id+'">'+value.unitgroupname+'</a></li>'
                                                            if (key == lastIndex) {
                                                                $unitGroupDropdown.html(html);
                                                            }
                                                        });

                                                        $unitGroupButton.text('Select One').siblings('button').removeClass('disabled');
                                                    } else {
                                                        $unitGroupDropdown.html('');
                                                        $unitGroupButton.text('No Groups Available').siblings('button').addClass('disabled');
                                                    }

                                                    // keep reference to group id for further use
                                                    $modal.find('#detail-panel').data('groupId', vehicleGroupId);

                                                    Core.DragDrop.init();
                                                }
                                            });
                                        }
                                    } else {
                                        if ($.isEmptyObject(responseData.validaton_error) === false) {
                                            //	display validation errors
                                        }
                                    }

                                    if ($.isEmptyObject(responseData.message) === false) {
                                        //Core.SystemMessage.show(responseData.message, responseData.code);
                                    }
                                }
                            });*/

                            _getVehicleGroupInfo(vehicleGroupId);

                        }
                    } else {
                        // NEW Vehicle Group
                        _launchModal({
                            // hide
                            hide: function() {
                                console.log('hide callback - new');

                            },
                            // hidden
                            hidden: function() {
                                console.log('hidden callback - new');

                            },
                            // show
                            show: function() {
                                console.log('show callback - new');


                            },
                            // shown
                            shown: function() {
                                console.log('shown callback - new');
                                $modal.find('.hide-if-new').hide();
                                $('#new-group-name').show();
                            }
                    });
                    }

                    function _getVehicleGroupInfo(vehicleGroupId) {

                        /*var $editingModalButtons  = $('#footer-buttons-edit'),
                            $newModalButtons      = $('#footer-buttons-new'),
                            $toggle = $('#vehicle-group-more-options-toggle').find('small')
                        ;

                        // hide 'More Options' section
                        *//*if ($toggle.text() == 'Show Less Options') {
                            $toggle.trigger('click');
                        }*//*

                        if (isNewVehicleGroup) {
                            $editingModalButtons.hide();
                            $newModalButtons.show();
                            console.log('_getVeh....NEW');
                        } else {
                            console.log('_getVeh....EDIT');
                            $editingModalButtons.show();
                            $newModalButtons.hide();
                        }*/

                        console.log('getting group info:'+vehicleGroupId)

                        $.ajax({
                            url: '/ajax/vehicle/getVehicleGroupInfo',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                group_id: vehicleGroupId
                            },
                            success: function(responseData) {
                                if (responseData.code === 0) {
                                    var unitgroupdata = responseData.data.vehiclegroup_data;

                                    //console.log(unitgroupdata);

                                    if (! $.isEmptyObject(unitgroupdata)) {

                                        _launchModal({
                                            // hide
                                            hide: function() {
                                                console.log('hide callback - edit');
                                                // close filter popover
                                                //$('#popover-available-filter-cancel').trigger('click');
                                                // clear available and assigned vehicle list
                                                $($vehicleGroupAvailableList.selector+','+$vehicleGroupAssignedList.selector).html('');

                                                // reset tabs
                                                $navTabs.removeClass('active');
                                                $navTabs.first().addClass('active');
                                                $tabPanes.removeClass('active').removeClass('in');
                                                $tabPanes.first().addClass('active').addClass('in');

                                                $navTabs.find('a[href="#vehicle-group-assignment-tab"]').addClass('active').addClass('in');
                                            },
                                            // hidden
                                            hidden: function() {
                                                console.log('hidden callback - edit');
                                                // redraw vehicle group list table
                                                Vehicle.Group.DataTable.vehicleGroupListTable.fnStandingRedraw();

                                                /*// hide 'More Options' section
                                                var $toggle = $('#vehicle-group-more-options-toggle').find('small');
                                                if ($toggle.text() == 'Show Less Options') {
                                                    $toggle.trigger('click');
                                                }*/

                                                // destroy any DragDrop containers after closing modal
                                                //Core.DragDrop.destroy();

                                                /* */

                                                // reset modal search and dropdown options
                                                $('#vehicle-group-source').val('all').text('All');
                                                $('#vehicle-group-destination').val('0').text('Select One');
                                                $('#vehicle-group-source').siblings('.dropdown-menu').find('li').show();
                                                $('#vehicle-group-destination').siblings('.dropdown-menu').find('li').show();
                                                $('#vehicle-group-assignment .drag-drop-available').html('');
                                                $('#vehicle-group-assignment .drag-drop-assigned').html('');
                                                $('#filter-available-text').val('');
                                                $('#filter-assigned-text').val('');

                                                // destroy any DragDrop containers after closing modal
                                                Core.DragDrop.destroy();






                                            },
                                            // show
                                            show: function() {
                                                console.log('show callback - edit');
                                            },
                                            // shown
                                            shownx: function() {

                                                console.log('shown callback - edit');

                                                var $detailPanel   = $('#detail-panel'),
                                                    /*$moreOptions   = $('#vehicle-group-more-options-toggle'),*/
                                                    $hideIfDefault = $detailPanel.find('.hide-if-default'),
                                                    defaultGroupId = responseData.data.defaultgroup_id != undefined ? responseData.data.defaultgroup_id : 0,
                                                    $groupSource = $('#vehicle-group-source'),
                                                    $groupDestination = $('#vehicle-group-destination')
                                                ;

                                                $detailPanel.find('.hook-editable-keys').data('vehicleGroupPk', vehicleGroupId).data('vehicleDefaultGroupId', defaultGroupId);

                                                $modal.find('.hide-if-new').show();
                                                $('#new-group-name').hide();

                                                // Don't allow editing of default group
                                                if (unitgroupdata.unitgroupname.toLowerCase() == 'all' || defaultGroupId == 0) {
                                                    if (unitgroupdata.unitgroupname.toLowerCase() == 'all') {
                                                        $('#not-editable-group-name').show();
                                                        $('#editable-group-name')/*.add($moreOptions)*/
                                                                                 .add($hideIfDefault)
                                                                                 .hide()
                                                        ;
                                                    } else {
                                                        $('#not-editable-group-name').hide();
                                                        $('#editable-group-name').show();
                                                        /*$moreOptions.show();*/
                                                        $hideIfDefault.hide();

                                                        Core.Editable.setValue($('#vehicle-group-name'), unitgroupdata.unitgroupname);
                                                    }

                                                    $detailPanel.closest('.modal-dialog').css('width', '666px');
                                                } else {
                                                    $('#not-editable-group-name').hide();
                                                    $('#editable-group-name')/*.add($moreOptions)*/
                                                                             .add($hideIfDefault)
                                                                             .show()
                                                    ;
                                                    Core.Editable.setValue($('#vehicle-group-name'), unitgroupdata.unitgroupname);
                                                }

                                                $('#vehicle-group-more-options').hide();

                                                // create assigned vehicle list
                                                if (! $.isEmptyObject(unitgroupdata.assigned_vehicles)) {
                                                    var assigned_vehicles = unitgroupdata.assigned_vehicles;
                                                    //console.log(assigned_contacts);
                                                    $.each(assigned_vehicles, function() {
                                                        $vehicleGroupAssignedList.append(Core.DragDrop._generateGroupMarkup(this.unit_id, this.unitname));
                                                    });
                                                }

                                                // create available vehicle list
                                                if (! $.isEmptyObject(unitgroupdata.available_vehicles)) {
                                                    var available_vehicles = unitgroupdata.available_vehicles;
                                                    //console.log(assigned_contacts);
                                                    $.each(available_vehicles, function() {
                                                        $vehicleGroupAvailableList.append(Core.DragDrop._generateGroupMarkup(this.unit_id, this.unitname));
                                                    });
                                                }

                                                // update unitgroup dropdown
                                                if (! $.isEmptyObject(responseData.data.vehicle_groups)) {
                                                    var vehicleGroups = responseData.data.vehicle_groups,
                                                        lastIndex = vehicleGroups.length - 1,
                                                        html = ''
                                                    ;

                                                    $.each(vehicleGroups, function(key, value) {
                                                        html += '<li class="transfer-group-id-'+value.unitgroup_id+'"><a data-value="'+value.unitgroup_id+'">'+value.unitgroupname+'</a></li>'
                                                        if (key == lastIndex) {

                                                            $groupSource.siblings('ul').html('<li class="transfer-group-id-all"><a data-value="all">All</a></li>'+html);
                                                            $groupDestination.siblings('ul').html(html);

                                                            // hide the selected group on the
                                                            $groupDestination.siblings('ul').find('.transfer-group-id-'+vehicleGroupId).hide();
                                                        }
                                                    });

                                                    $groupSource.siblings('button').removeClass('disabled');
                                                    $groupDestination.val('0').text('Select One').siblings('button').removeClass('disabled');
                                                } else {
                                                    $groupSource.val('0').text('No Groups Available').siblings('button').addClass('disabled');
                                                    $groupDestination.val('0').text('No Groups Available').siblings('button').addClass('disabled');
                                                    $groupSource.siblings('ul').html('');
                                                    $groupDestination.siblings('ul').html('');
                                                }

                                                // keep reference to group id for further use
                                                $modal.find('#detail-panel').data('groupId', vehicleGroupId);

                                                //Core.DragDrop.init();


//                                                 $.ajax({
//                                                     url: '/ajax/device/getDeviceTransferDataByAccountId',
//                                                     type: 'POST',
//                                                     dataType: 'json',
//                                                     data: {
//                                                         vehicle_group_id: vehicleGroupId
//                                                     },
//                                                     success: function(responseData) {
//                                                         if (responseData.code === 0) {
//                                                             var unitdata = responseData.data;

//                                                             // update available list
//                                                             var $vehicleGroupAssignment = $('#vehicle-group-assignment'),
//                                                                 $vehicleAvailableList   = $vehicleGroupAssignment.find('.drag-drop-available'),
//                                                                 $vehicleAssignedList    = $vehicleGroupAssignment.find('.drag-drop-assigned')
//                                                             ;

//                                                             // clear available
//                                                             $vehicleAvailableList.html('');
//                                                             $vehicleAssignedList.html('');

//                                                             // create available vehicle list
//                                                             if (! $.isEmptyObject(unitdata)) {
//                                                                 var available_vehicles = unitdata;
//                                                                 $.each(available_vehicles, function() {
//                                                                     groupIdClass = 'transfer-group-id-'+this.unitgroup_id;
//                                                                     $vehicleAvailableList.append(Core.DragDrop._generateGroupMarkup(this.unit_id, this.unitname, this.unitgroup_id, groupIdClass));
// console.log('$vehicleAvailableList += '+this.unit_id+':'+this.unitname+':'+this.unitgroup_id+':'+groupIdClass);
//                                                                 });
//                                                             }

//                                                             Core.DragDrop.init();

//                                                             var vehicleGroupName = $('#vehicle-group-name').text() == 'Not Set' ? 'All' : $('#vehicle-group-name').text();
//                                                             $('#vehicle-group-source').val(vehicleGroupId).text(vehicleGroupName);
//                                                             switch(vehicleGroupName){
//                                                                 case          "All" :   $('#editable-group-name').hide();
//                                                                                         $('#not-editable-group-name').show();
//                                                                                         break;
//                                                                             default :   $('#not-editable-group-name').hide();
//                                                                                         $('#editable-group-name').show();
//                                                             }

//                                                             console.log($('#vehicle-group-name').text());

//                                                             var $container = $('#vehicle-group-assignment');
//                                                             var $devices   =  $container.find('.drag-drop-available').find('li');
//                                                             $devices.hide();
//                                                             $devices.filter('.transfer-group-id-'+vehicleGroupId).show();



//                                                         } else {
//                                                             if ($.isEmptyObject(responseData.validaton_errors) === false) {
//                                                                 //	display validation errors
//                                                             }
//                                                         }
//                                                     }
//                                                 });

                                                /*$('#vehicle-group-source').val(vehicleGroupId).text($('.transfer-group-id-' + vehicleGroupId).eq(0).text());
                                                var $container = $('#vehicle-group-assignment');
                                                var $devices =  $container.find('.drag-drop-available').find('li');*/

                                                //console.log($devices);




                                               // $('#vehicle-group-source').closest('.btn-group').find('li').filter('.transfer-group-id-' + vehicleGroupId).find('a').trigger('click');
                                                //$('#vehicle-group-source').closest('.btn-group').find('.btn-dropdown ~ .dropdown-menu li.transfer-group-id-' + vehicleGroupId+' a').trigger('click');
                                                //$('#vehicle-group-source').dropdown('toggle');
                                                //console.log($('#vehicle-group-source').closest('.btn-group').find('.btn-dropdown ~ .dropdown-menu li.transfer-group-id-' + vehicleGroupId+' a'));


                                            }
                                        });
                                    }
                                } else {
                                    if ($.isEmptyObject(responseData.validaton_error) === false) {
                                        //	display validation errors
                                    }
                                }

                                if ($.isEmptyObject(responseData.message) === false) {
                                    //Core.SystemMessage.show(responseData.message, responseData.code);
                                }
                                //$modal.trigger('show.bs.modal');
                                _getVehicleGroupLists(vehicleGroupId);
                                $modal.trigger('shown.bs.modal');
                            }
                        });
                    }

                    function _launchModal(callbacks) {
                        Core.Dialog.launch('#'+$modal.prop('id'), 'Vehicle Group', {
                                //width: '1000px'
                                width: '598px'
                            },
                            callbacks
                        );

                        var $editingModalButtons  = $('#footer-buttons-edit'),
                            $newModalButtons      = $('#footer-buttons-new'),
                            $toggle               = $('#vehicle-group-more-options-toggle'),
                            $toggleContainer      = $('#vehicle-group-more-options')
                        ;

                        if (isNewVehicleGroup) {
                            $editingModalButtons.hide();
                            $newModalButtons.show();
                            $toggle.hide();
                        } else {
                            $editingModalButtons.show();
                            $newModalButtons.hide();
                            $toggle.show();
                        }

                        $toggleContainer.hide();
                        $toggle.find('small').text('Show More Options');
                    }

                    $saveNewGroupButton.off('click'); // $.one() will not work in this case (i.g. the user never clicks the save button)
                    $saveNewGroupButton.on('click', function() {
console.log('$saveNewGroupButton:on(click)');
                        var $modal     = $('#modal-vehicle-group-list'),
                            $groupName = $('#vehicle-group-name-new'),
                            groupName  = $groupName.val()
                        ;

                        if (groupName != '' && groupName != undefined) {
                            $.ajax({
                                url: '/ajax/vehicle/addVehicleGroup',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    vehiclegroupname: groupName
                                },
                                success: function(responseData) {
                                    if (responseData.code === 0) {

                                        // on success, redraw the verification table
                                        Vehicle.Group.DataTable.vehicleGroupListTable.fnStandingRedraw();


                                        vehicleGroupId = responseData.data.groupid;
                                        $modal.find('#detail-panel').data('groupId', responseData.data.groupid);
                                        isNewVehicleGroup = false;
                                        $modal.find('.hide-if-new').show();
                                        $('#new-group-name').hide();
                                        $('#not-editable-group-name').hide();
                                        Core.Editable.setValue($('#vehicle-group-name'), groupName);
                                        _getVehicleGroupInfo();

                                        $('#vehicle-group-access-tab-href').trigger( "click" );

                                    } else {
                                        if (! $.isEmptyObject(responseData.validation_error)) {
                                            //	display validation errors
                                        }
                                    }

                                    if (! $.isEmptyObject(responseData.message)) {
                                        Core.SystemMessage.show(responseData.message, responseData.code);
                                    }

                                    console.log(responseData.data);
                                    /*$('#vehicle-group-list-table').one('Core.Datatable.standingRedraw', function(event) {

                                        $(this).find('.col-name').each(function() {
                                            var $self = $(this),
                                                $a    = $self.find('a')
                                            ;

                                            if ($a.text() == groupName) {
                                                $a.trigger('click');
                                                return false; // break
                                            } else {
                                                return true; // continue
                                            }
                                        });
                                    });*/
                                }
                            });
                        } else {
                            alert('Vehicle Group Name cannot be blank');
                        }
                    });

                });

                /**
                 * Assign User Access Modal
                 *
                 */
                // initialize vehicle group assignment to user modal
                //$(document).on('click', 'a.edit-user', function() {
                $(document).on('click', 'a[href="#vehicle-group-access-tab"]', function() {
console.log('on(click):a[href="#vehicle-group-access-tab"]');
                    var $self = $(this),
                        //$trNode = $self.closest('tr'),
                        //$modal = $('#modal-vehicle-group-edit-user'),
                        $modal = $('#modal-vehicle-group-list'),
                        vehicleGroupId = $modal.find('#detail-panel').data('groupId'),//$trNode.attr('id').split('-')[2],

                        //groupName = $trNode.find('td.col-name').text(),
                        $userTypeList = $('#vehicle-group-usertype-list'),
                        $userList = $('#vehicle-group-user-list')
                    ;

                    if (vehicleGroupId != undefined) {

                        $.ajax({
                            url: '/ajax/vehicle/getUsersByVehicleGroupId',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                group_id: vehicleGroupId
                            },
                            success: function(responseData) {
                                if (responseData.code === 0) {
                                    var users = responseData.data.users;
                                    var userTypes = responseData.data.usertypes;

                                    if (! $.isEmptyObject(userTypes)) {

                                        $('#vehicle-group-user-all, #vehicle-group-usertype-all').prop('checked', false);

                                        $($userTypeList.selector+','+$userList.selector).html('');



                                        $('#detail-panel').find('.hook-editable-keys').data('vehicleGroupPk', vehicleGroupId);

                                        // create user types list
                                        if (! $.isEmptyObject(userTypes)) {
                                            var html = '',
                                                lastIndex = userTypes.length - 1
                                            ;

                                            $.each(userTypes, function(key, value) {
                                                html += '<li id="usertype-li-'+this.usertype_id+'" data-usertype-id="'+this.usertype_id+'" class="list-group-item usertype-item">' +
                                                        '   <div class="clearfix">' +
                                                        '       <div class="pull-left">' +
                                                        '           <label for="user-type-'+this.usertype_id+'" class="sr-only">'+this.usertype+'</label>' +
                                                        '         '+this.usertype+
                                                        '       </div>' +
                                                        '       <div class="pull-right">' +
                                                        '           <input type="checkbox" id="user-type-'+this.usertype_id+'" class="usertype-item-checkbox" />' +
                                                        '       </div>' +
                                                        '   </div>' +
                                                        '</li>'
                                                ;

                                                if (key == lastIndex) {
                                                    $userTypeList.append(html);
                                                }
                                            });
                                        }

                                        // create user list
                                        if (! $.isEmptyObject(users)) {
                                            var html = '',
                                                lastIndex = users.length - 1
                                            ;

                                            $.each(users, function(key, value) {
                                                html += '<li id="user-li-'+this.user_id+'" data-user-id="'+this.user_id+'" data-original-state="' + ((this.unitgroup_id != undefined && this.unitgroup_id == vehicleGroupId) ? 'checked' : 'unchecked') + '" class="list-group-item user-item usertype-id-'+this.usertype_id+'">' +
                                                        '   <div class="clearfix">' +
                                                        '       <div class="user-name pull-left">' +
                                                        '           <label for="user-'+this.user_id+'" class="sr-only">'+this.fullname+'</label>' +
                                                        '         '+this.fullname+
                                                        '       </div>' +
                                                        '       <div class="pull-right">' +
                                                        '           <input type="checkbox" class="user-item-checkbox" id="user-'+this.user_id+'" '+((this.unitgroup_id != undefined && this.unitgroup_id == vehicleGroupId) ? 'checked="checked"' : '')+' />' +
                                                        //'           <input type="checkbox" class="user-item-checkbox" id="user-'+this.user_id+'"  />' +
                                                        '       </div>' +
                                                        '   </div>' +
                                                        '</li>'
                                                ;

                                                if (key == lastIndex) {
                                                    $userList.append(html);
                                                }
                                            });
                                        }
                                    }
                                } else {
                                    if ($.isEmptyObject(responseData.validaton_error) === false) {
                                        //	display validation errors
                                    }
                                }

                                if ($.isEmptyObject(responseData.message) === false) {
                                    //Core.SystemMessage.show(responseData.message, responseData.code);
                                }
                            }
                        });
                    }

                });

                // listener for updating usertype checked states
                $('#vehicle-group-usertype-list').on('click', '.usertype-item-checkbox', function() {
console.log('#vehicle-group-usertype-list:click:.usertype-item-checkbox');
                    var $self = $(this),
                        $userList = $('#vehicle-group-user-list'),
                        $userTypeList = $('#vehicle-group-usertype-list'),
                        $selectedUserTypes = $userTypeList.find('.usertype-item-checkbox').filter(':checked') // get all the usertypes that are checked
                    ;

                    if ($selectedUserTypes.length > 0) {
                        var filterClass = '',
                            lastIndex = $selectedUserTypes.length - 1
                        ;

                        $.each($selectedUserTypes, function(key, value) {
                            var $self = $(this),
                                $liNode = $self.closest('li')
                            ;

                            filterClass += '.usertype-id-'+$liNode.data('usertypeId') + ', ';

                            // once we've gotten all usertype classes to filter by, hide all the users not in this class and show the ones that are
                            if (key == lastIndex) {

                                // remove the ending comma and space from the last
                                filterClass = filterClass.substring(0, filterClass.length - 2);

                                var $userItems = $userList.find('li');

                                // hide users not in this usetype class and remove their update class
                                $userItems.filter(':not('+filterClass+')').removeClass('update').hide();

                                // show any hidden users with this usertype class
                                var $hiddenUsers = $userItems.filter(filterClass).filter(':not(:visible)');
                                if ($hiddenUsers.length > 0) {
                                    var lastIndex2 = $hiddenUsers.length - 1;
                                    $.each($hiddenUsers, function(key2, value2) {
                                        var $that = $(this),
                                            checked = $that.data('originalState') == 'checked' ? true : false
                                        ;

                                        $that.find('.user-item-checkbox').prop('checked', checked);

                                        if (key2 == lastIndex2) {
                                            $hiddenUsers.show();
                                        }
                                    });
                                }
                            }
                        });
                    } else {
                        // no usertypes selected, show all users
                        var $hiddenUsers = $userList.find('li').filter(':not(:visible)'),
                            lastIndex = $hiddenUsers.length - 1
                        ;

                        $.each($hiddenUsers, function(key, value) {
                            var $self = $(this),
                                checked = $self.data('originalState') == 'checked' ? true : false
                            ;

                            $self.find('.user-item-checkbox').prop('checked', checked);

                            if (key == lastIndex) {
                                $hiddenUsers.show();
                            }
                        });
                    }


                    // uncheck the select ALL usertype checkbox if a usertype is uncheck
                    if ($self.is(':not(:checked)') && $('#vehicle-group-usertype-all').is(':checked')) {
                        $('#vehicle-group-usertype-all').prop('checked', false);
                    }
                });

                // listener for updating user checked states
                $('#vehicle-group-user-list').on('click', '.user-item-checkbox', function() {
console.log('#vehicle-group-user-list:click:.user-item-checkbox');
                    var $self = $(this),
                        $liNode = $self.closest('li')
                    ;

                    if ($liNode.data('originalState') == 'checked') {
                        if ($self.is(':checked')) {
                            $liNode.removeClass('update');
                        } else {
                            $liNode.addClass('update');
                        }
                    } else {
                        if ($self.is(':checked')) {
                            $liNode.addClass('update');
                        } else {
                            $liNode.removeClass('update');
                        }
                    }

                    // uncheck the select ALL user checkbox if a user is uncheck
                    if ($self.is(':not(:checked)') && $('#vehicle-group-user-all').is(':checked')) {
                        $('#vehicle-group-user-all').prop('checked', false);
                    }
                });

                // save vehicle group to users
                $('#vehicle-group-user-save').on('click', function() {
console.log('#vehicle-group-user-save:click');
                    var vehicleGroupId = $('#detail-panel').find('.hook-editable-keys').data('vehicleGroupPk'),
                        $userTypeList = $('#vehicle-group-usertype-list'),
                        $userList = $('#vehicle-group-user-list'),
                        updatedUsers = $userList.find('li').filter('.update')
                    ;

                    if (vehicleGroupId != undefined && updatedUsers.length > 0) {
                        var lastIndex = updatedUsers.length - 1,
                            addToUsers = [],
                            removeFromUsers = []
                        ;

                        $.each(updatedUsers, function(key, value) {
                            var $self = $(this);

                            if ($self.find('.user-item-checkbox').is(':checked')) {
                                addToUsers.push({id: $self.data('userId'), name: $self.find('.user-name').text().trim()});
                            } else {
                                removeFromUsers.push({id: $self.data('userId'), name: $self.find('.user-name').text().trim()});
                            }

                            if (key == lastIndex) {
                                $.ajax({
                                    url: '/ajax/vehicle/updateVehicleGroupUsers',
                                    type: 'POST',
                                    dataType: 'json',
                                    data: {
                                        vehiclegroup_id: vehicleGroupId,
                                        add_users: addToUsers,
                                        remove_users: removeFromUsers
                                    },
                                    success: function(responseData) {
                                        if (responseData.code === 0) {
                                            // close modal
                                            $('#vehicle-group-user-close').trigger('click');
                                        } else {
                                            if ($.isEmptyObject(responseData.validation_error) === false) {
                                                //	display validation errors
                                            }

                                            // uncheck users that were not able to be assign to this group
                                            if (! $.isEmptyObject(responseData.data.failed_add_users)) {
                                                var failed_add_user = responseData.data.failed_add_users;
                                                $.each(failed_add_user, function() {
                                                    var $self = $(this);
                                                    $('#user-li-'+$self.id).find('.user-item-checkbox').prop('checked', false);
                                                });
                                            }

                                            // check users that were not able to be remove to this group
                                            if (! $.isEmptyObject(responseData.data.failed_remove_users)) {
                                                var failed_remove_user = responseData.data.failed_remove_users;
                                                $.each(failed_remove_user, function() {
                                                    var $self = $(this);
                                                    $('#user-li-'+$self.id).find('.user-item-checkbox').prop('checked', true);
                                                });
                                            }

                                            if ((! $.isEmptyObject(responseData.data.failed_add_users) || ! $.isEmptyObject(responseData.data.failed_remove_users)) && ! $.isEmptyObject(responseData.message) === false) {
                                                alert(responseData.message);
                                            }
                                        }

                                        if ($.isEmptyObject(responseData.message) === false) {
                                            Core.SystemMessage.show(responseData.message, responseData.code);
                                        }


                                        // update original-state for updated users
                                        if (! $.isEmptyObject(responseData.data.updated_users)) {
                                            var $self = $(this),
                                                $liNode = $('#user-li-'+$self.id),
                                                checked = ($liNode.find('.user-item-checkbox').is(':checked') ? 'checked' : 'unchecked')
                                            ;

                                            $liNode.data('originalState', checked);
                                        }

                                        // remove update classes
                                        updatedUsers.removeClass('update');
                                    }
                                });
                            }
                        });
                    } else {
                        alert('No users to assign/remove vehicle group to/from.');
                    }
                });

                // transfer vehicle(s) to new group
                $('#vehicle-group-transfer, #vehicle-group-transfer2').on('click', function() {
console.log('#vehicle-group-transfer, #vehicle-group-transfer2:click');

                    // update available list
                    var $activeItems                        = Core.DragDrop.activeItemIds(),
                        $selectedVehicleGroupSourceId       = $('#vehicle-group-source').val(),
                        $selectedVehicleDestinationSourceId = $('#vehicle-group-destination').val();
                    ;

                    if ($selectedVehicleDestinationSourceId != 0) {
                        if ($activeItems != 0) {
console.log('transfer:'+$selectedVehicleGroupSourceId+':'+$selectedVehicleDestinationSourceId+':'+$activeItems+':');

                            $.ajax({
                                url: '/ajax/vehicle/updateVehicleGroupIds',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    devices: $activeItems,
                                    groupid: $selectedVehicleDestinationSourceId
                                },
                                success: function(responseData) {
                                    console.log(responseData.code+':'+responseData.message);
                                    if (responseData.code === 0) {
                                        //Core.SystemMessage.show('Success', responseData.code);
                                        //location.reload();
                                        _getVehicleGroupLists();
                                    } else {
                                        Core.SystemMessage.show(responseData.message, responseData.code);
                                    }
                                }
                            });

                        } else {
                            Core.SystemMessage.show('Please Select Devices to Transfer', 1);
                        }
                    } else {
                        Core.SystemMessage.show('Please Select Target Group', 1);
                    }

console.log('transfer_end');
                });

                // toggle groups
                $('#vehicle-group-toggle').on('click', function() {
console.log('#vehicle-group-toggle:click');
                    $from_id = $('#vehicle-group-source').val();
                    $to_id = $('#vehicle-group-destination').val();
                    $from_text = $('#vehicle-group-source').text();
                    $to_text = $('#vehicle-group-destination').text();
                    $('#vehicle-group-source').val($to_id);
                    $('#vehicle-group-destination').val($from_id);
                    $('#vehicle-group-source').text($to_text);
                    $('#vehicle-group-destination').text($from_text);
                    _getVehicleGroupLists();
                });

                // select all usertypes
                $('#vehicle-group-usertype-all').on('click', function() {
console.log('#vehicle-group-usertype-all:click');
                    var $self = $(this),
                        $userList = $('#vehicle-group-usertype-list')
                    ;

                    if ($self.is(':checked')) {
                        $userList.find('.usertype-item-checkbox').filter(':not(:checked)').trigger('click');
                    } else {
                        $userList.find('.usertype-item-checkbox').prop('checked', false);
                    }
                });

                // select all users
                $('#vehicle-group-user-all').on('click', function() {
console.log('#vehicle-group-user-all');
                    var $self = $(this),
                        $userList = $('#vehicle-group-user-list')
                    ;

                    if ($self.is(':checked')) {
                        $userList.find('.user-item-checkbox').prop('checked', true);
                    } else {
                        $userList.find('.user-item-checkbox').prop('checked', false);
                    }
                });




                 // listener for changing vehicle bulk transfer dropdown groups
                $('#vehicle-group-source, #vehicle-group-destination').on('Core.DropdownButtonChange', function() {
                    _getVehicleGroupLists();
                });



                function _getVehicleGroupLists(vehicleGroupId) {

                    // update available list
                    var $self                               = $(this),
                        $clickedVehicleGroupButton          = $self.prop('id'),
                        $vehicleGroupAssignment             = $('#vehicle-group-assignment'),
                        $selectedVehicleGroupSourceId       = $('#vehicle-group-source').val(),
                        $selectedVehicleDestinationSourceId = $('#vehicle-group-destination').val();
                        $availableVehicleList               = $('.drag-drop-available').find('li'),
                        $assignedVehicleList                = $('.drag-drop-assigned').find('li'),
                        $sourceDropdownList                 = $('#vehicle-group-source').siblings('.dropdown-menu').find('li'),
                        $destinationDropdownList            = $('#vehicle-group-destination').siblings('.dropdown-menu').find('li'),
                        $availableVehicleFilterText         = $('#filter-available-text').val(),
                        $assignedVehicleFilterText          = $('#filter-assigned-text').val(),
                        $groupIdTransferSourceClass         = 'transfer-group-id-'+$selectedVehicleGroupSourceId,
                        $groupIdTransferDestinationClass    = 'transfer-group-id-'+$selectedVehicleDestinationSourceId
                    ;

                    $availableVehicleList.removeClass('active');

                    if(vehicleGroupId){
console.log('reset:$selectedVehicleGroupSourceId: from:'+$selectedVehicleGroupSourceId+': to:'+vehicleGroupId+':');
                        $sourceDropdownList.each(function() {
                            if ($(this).attr('class').replace('transfer-group-id-','')==$selectedVehicleGroupSourceId) {
                                $selectedVehicleGroupSourceId=vehicleGroupId;
                                $('#vehicle-group-source').val($selectedVehicleGroupSourceId)
                                $groupIdTransferSourceClass         = 'transfer-group-id-'+$selectedVehicleGroupSourceId,
                                $buffer=$(this).text();
                                $('#vehicle-group-source').text($buffer);
console.log('setting:vehicle-group-source:option:'+vehicleGroupId+':'+$selectedVehicleGroupSourceId+':'+$groupIdTransferSourceClass+':'+$buffer);
                            }
                        });
                    }

                    if ($selectedVehicleDestinationSourceId) {
                        $destinationDropdownList.each(function() {
                            if (($(this).attr('class').replace('transfer-group-id-','')!=$selectedVehicleGroupSourceId)&&($selectedVehicleDestinationSourceId==0)) {
                                $selectedVehicleDestinationSourceId=$(this).attr('class').replace('transfer-group-id-','');
                                $('#vehicle-group-destination').val($selectedVehicleDestinationSourceId);
                                $buffer=$(this).text();
                                $('#vehicle-group-destination').text($buffer);
                                $groupIdTransferDestinationClass    = 'transfer-group-id-'+$selectedVehicleDestinationSourceId
console.log('setting:vehicle-group-destination:option:'+vehicleGroupId+':'+$selectedVehicleDestinationSourceId+':'+$groupIdTransferDestinationClass+':'+$buffer);
                            }
                        });
                    }

                    // destination dropdown manipulation: show all group options but that of the selected source group and it's own selected group
                    $destinationDropdownList.filter(':not(.'+$groupIdTransferSourceClass+')').show();
                    $destinationDropdownList.filter('.'+$groupIdTransferDestinationClass).hide();
                    $destinationDropdownList.filter('.'+$groupIdTransferSourceClass).hide();

                    // source dropdown manipulation: show all group options but that of the selected destination group and it's own selected group
                    $sourceDropdownList.filter(':not(.'+$groupIdTransferDestinationClass+')').show();
                    $sourceDropdownList.filter('.'+$groupIdTransferDestinationClass).hide();
                    $sourceDropdownList.filter('.'+$groupIdTransferSourceClass).hide();

                    _getVehicleGroupListsAjax();

                    // if group on right side changed (vehicle-group-destination)
                    // display all available vehicles on left side accordingly
                    $vehicleGroupAssignment.find('.drag-drop-available').append($assignedVehicleList.detach());
                    $vehicleGroupAssignment.find('.drag-drop-assigned').html('');

                    if ($selectedVehicleGroupSourceId != 'all') {
                        // show left side vehicles according to selected group id, hide the others
                        $('.drag-drop-available').find('li').filter('.'+$groupIdTransferSourceClass).show();
                        $('.drag-drop-available').find('li').filter(':not(.'+$groupIdTransferSourceClass+')').hide();

                        if ( $selectedVehicleDestinationSourceId != 0) {
                            // get selected destination group vehicles, detach and show it on right side accordingly
                            $selectedDestinationVehicles = $('.drag-drop-available li').filter('.'+$groupIdTransferDestinationClass);
                            $vehicleGroupAssignment.find('.drag-drop-assigned').append($selectedDestinationVehicles.show().detach());
                        }
                    } else{
                        // at the ALL group dropdownd
                        $('.drag-drop-available li').filter(':not(.'+$groupIdTransferDestinationClass+')').show();

                        if ( $selectedVehicleDestinationSourceId != 0) {
                            // get selected destination group vehicles, detach and show it on right side accordingly
                            $selectedDestinationVehicles = $('.drag-drop-available li').filter('.'+$groupIdTransferDestinationClass);
                            $vehicleGroupAssignment.find('.drag-drop-assigned').append($selectedDestinationVehicles.show().detach());
                        }
                    }

                    // clear text search on group change
                    if ($assignedVehicleFilterText != '') {
                        $('#filter-assigned-text').val('');
                    }

                    if ($availableVehicleFilterText != '') {
                        // trigger text search on left side if text search currently exits for "All" groups
                        $('#filter-available-text').trigger('keyup');

                    }

                   Core.DragDrop.destroy();
                   Core.DragDrop.init();

                }



                function _getVehicleGroupListsAjax() {

                    // update available list
                    var $self                               = $(this),
                        $vehicleGroupAssignment             = $('#vehicle-group-assignment'),
                        $selectedVehicleGroupSourceId       = $('#vehicle-group-source').val(),
                        $selectedVehicleDestinationSourceId = $('#vehicle-group-destination').val();
                        $availableVehicleList               = $('.drag-drop-available').find('li'),
                        $assignedVehicleList                = $('.drag-drop-assigned').find('li'),
                        $sourceDropdownList                 = $('#vehicle-group-source').siblings('.dropdown-menu').find('li'),
                        $destinationDropdownList            = $('#vehicle-group-destination').siblings('.dropdown-menu').find('li'),
                        $availableVehicleFilterText         = $('#filter-available-text').val(),
                        $assignedVehicleFilterText          = $('#filter-assigned-text').val(),
                        $groupIdTransferSourceClass         = 'transfer-group-id-'+$selectedVehicleGroupSourceId,
                        $groupIdTransferDestinationClass    = 'transfer-group-id-'+$selectedVehicleDestinationSourceId
                    ;

console.log('bulk transfer:source:'+$selectedVehicleGroupSourceId);

                    $.ajax({
                        url: '/ajax/device/getDeviceTransferDataByAccountId',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            vehicle_group_id: $selectedVehicleGroupSourceId
                        },
                        success: function(responseData) {

                            if (responseData.code === 0) {
                                
                                var unitdata = responseData.data;

                                // update available list
                                var $vehicleGroupAssignment = $('#vehicle-group-assignment'),
                                    $vehicleAvailableList   = $vehicleGroupAssignment.find('.drag-drop-available')
                                    //$vehicleAssignedList    = $vehicleGroupAssignment.find('.drag-drop-assigned')
                                ;

                                // clear available
                                $vehicleAvailableList.html('');
                                //$vehicleAssignedList.html('');

                                // create available vehicle list
                                if (! $.isEmptyObject(unitdata)) {
                                    var available_vehicles = unitdata;
                                    $.each(available_vehicles, function() {
                                        groupIdClass = 'transfer-group-id-'+this.unitgroup_id;
                                        $vehicleAvailableList.append(Core.DragDrop._generateGroupMarkup(this.unit_id, this.unitname, this.unitgroup_id, groupIdClass));
console.log('$vehicleAvailableList += '+this.unit_id+':'+this.unitname+':'+this.unitgroup_id+':'+groupIdClass);
                                    });
                                }

                                Core.DragDrop.init();

                                var vehicleGroupName = $('#vehicle-group-source').val($selectedVehicleGroupSourceId).text();
                                $('#vehicle-group-name').text(vehicleGroupName);
                                switch(vehicleGroupName){
                                    case          "All" :   $('#editable-group-name').hide();
                                                            $('#not-editable-group-name').show();
                                                            break;
                                                default :   $('#not-editable-group-name').hide();
                                                            $('#editable-group-name').show();
                                }
                                                            
console.log('Source Group Id Updated:'+$('#vehicle-group-source').val()+':'+$('#vehicle-group-source').text());

                                var $container = $('#vehicle-group-assignment');
                                var $devices   =  $container.find('.drag-drop-available').find('li');
                                //$devices.hide();
                                $devices.filter('.transfer-group-id-'+$selectedVehicleGroupSourceId).show();

                            } else {
                                if ($.isEmptyObject(responseData.validaton_errors) === false) {
                                    //  display validation errors
                                }
                            }
                        }
                    });

                    if ($selectedVehicleDestinationSourceId != 0) {
                        if ($assignedVehicleFilterText != '') {
                            // trigger text search on right side if text search for current selected group
                            $('#filter-assigned-text').trigger('keyup');
                        } else {
                            // get selected destination group vehicles, detach and show it on right side
                            $selectedDestinationVehicles = $availableVehicleList.filter('.'+$groupIdTransferDestinationClass);
                            $vehicleGroupAssignment.find('.drag-drop-assigned').append($selectedDestinationVehicles.show().detach());
                        }
                    } else {
                        $vehicleGroupAssignment.find('.drag-drop-assigned').html(''); // clear rigth side
                    }

                    if ($selectedVehicleGroupSourceId != 'all') {
                        // show selected source group vehicles and hide the rest on left side
                        $availableVehicleList.filter(':not(.'+$groupIdTransferSourceClass+')').hide();
                    } else {
                        if ($selectedVehicleDestinationSourceId != 0) {
                            // show selected source group vehicles and hide the rest on left side
                            $availableVehicleList.filter('.'+$groupIdTransferDestinationClass).hide();
                        }
                    }

                    // clear text search on group change
                    if ($availableVehicleFilterText != '') {
                        $('#filter-available-text').val('');
                    }

console.log('bulk transfer:destination:'+$selectedVehicleDestinationSourceId);

                    $.ajax({
                        url: '/ajax/device/getDeviceTransferDataByAccountId',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            vehicle_group_id: $selectedVehicleDestinationSourceId
                        },
                        success: function(responseData) {

                            if (responseData.code === 0) {
                                    
                                var unitdata = responseData.data;

                                // update assigned list
                                var $vehicleGroupAssignment = $('#vehicle-group-assignment'),
                                    //$vehicleAvailableList   = $vehicleGroupAssignment.find('.drag-drop-available')
                                    $vehicleAssignedList    = $vehicleGroupAssignment.find('.drag-drop-assigned')
                                ;

                                // clear assigned
                                //$vehicleAvailableList.html('');
                                $vehicleAssignedList.html('');

                                // create assigned vehicle list
                                if (! $.isEmptyObject(unitdata)) {
                                    var assigned_vehicles = unitdata;
                                    $.each(assigned_vehicles, function() {
                                        groupIdClass = 'transfer-group-id-'+this.unitgroup_id;
                                        $vehicleAssignedList.append(Core.DragDrop._generateGroupMarkup(this.unit_id, this.unitname, this.unitgroup_id, groupIdClass));
console.log('$vehicleAssignedList += '+this.unit_id+':'+this.unitname+':'+this.unitgroup_id+':'+groupIdClass);
                                    });
                                }

                                //Core.DragDrop.init();

console.log('Assigned Group Id Updated:'+$('#vehicle-group-destination').val()+':'+$('#vehicle-group-destination').text());

                                // var $container = $('#vehicle-group-assignment');
                                // var $devices   =  $container.find('.drag-drop-available').find('li');
                                // //$devices.hide();
                                // $devices.filter('.transfer-group-id-'+$selectedVehicleDestinationSourceId).show();

                            } else {
                                if ($.isEmptyObject(responseData.validaton_errors) === false) {
                                    //  display validation errors
                                }
                            }

                        }
                    });
                }


            }
        },

        Edit: {

            init: function() {

                // NTD: call this method after generating the list of vehicle groups
                //Core.DragDrop.init();

                // Group Assignment
                /*$('#vehicle-group-assignment').on('Core.DragDrop.Dropped', function(event, extraParams) {

                    if (! $.isEmptyObject(extraParams)) {
                        var $self                       = $(this),
                            $detailPanelHook            = $('#detail-panel').find('.hook-editable-keys'),
                            updatedItems                = extraParams.updatedItems.items,
                            method                      = (extraParams.updatedItems.inAssignedGroup === true) ? 'addVehiclesToGroup' : 'removeVehiclesFromGroup',
                            vehicleGroupId              = $detailPanelHook.data('vehicleGroupPk') || 0,
                            defaultGroupId              = $detailPanelHook.data('vehicleDefaultGroupId') || 0
                        ;

                        if (method == 'removeVehiclesFromGroup') {
                            vehicleGroupId = defaultGroupId;
                        }

                        if (updatedItems != undefined && updatedItems.length != 0 && vehicleGroupId != undefined && vehicleGroupId != 0) {

                            $.ajax({
                                url: '/ajax/vehicle/'+method,
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    vehiclegroup_id: vehicleGroupId,
                                    vehicles: updatedItems
                                },
                                success: function(responseData) {
                                    if (responseData.code === 0) {

                                    } else {
                                        if (! $.isEmptyObject(responseData.validation_error)) {
                                            // display validation errors
                                        }

                                        // restore the failed unit/landmark back to its original location
                                        if (! $.isEmptyObject(responseData.data) && ! $.isEmptyObject(responseData.data.failed_groups)) {
                                            var destination = (extraParams.updatedItems.inAssignedGroup === true) ? 'assigned' : 'available',
                                                $destinationItems = $self.find('.drag-drop-'+destination+' li'),
                                                $source = $self.find('.drag-drop-'+((destination == 'available') ? 'assigned' : (destination == 'assigned') ? 'available' : destination))
                                            ;

                                            // remove the failed groups from their destination and add them back to their origin
                                            $.each(responseData.data.failed_groups, function(key, value) {
                                                $source.append($destinationItems.filter('[data-id="'+value.id+'"]').detach());
                                            });

                                            alert(responseData.message);
                                        }
                                    }

                                    if (! $.isEmptyObject(responseData.message)) {
                                        Core.SystemMessage.show(responseData.message, responseData.code);
                                    }
                                }
                            });

                        }
                    }
                });*/
                $('#vehicle-group-assignment').on('Core.DragDrop.Dropped', function(event, extraParams) {

console.log('dropped');

                    if (! $.isEmptyObject(extraParams)) {
                        var $self                               = $(this),
                            id                                  = $self.prop('id'),
                            type                                = (id == 'vehicle-group-assignment') ? 'vehicle' : '',
                            updatedItems                        = extraParams.updatedItems.items,
                            method                              = 'addVehicleToGroup',
                            assignToGroupId                     = '',
                            selectedVehicleGroupSourceId        = $('#vehicle-group-source').val(),
                            selectedVehicleDestinationSourceId  = $('#vehicle-group-destination').val(),
                            data                                = {}
                        ;

                        if (extraParams.updatedItems.inAssignedGroup === true) {
                            assignToGroupId = selectedVehicleDestinationSourceId;
                        } else {
                            assignToGroupId = selectedVehicleGroupSourceId;
                        }

                        if (updatedItems != undefined && updatedItems.length != 0 && assignToGroupId != undefined && assignToGroupId != 0 && assignToGroupId.toLowerCase() != 'all') {

                            data.unitgroup_id = assignToGroupId;

                            if (type == 'vehicle') {
                                data.vehicles = updatedItems;
                            }

                            $.ajax({
                                url: '/ajax/'+type+'/'+method,
                                type: 'POST',
                                dataType: 'json',
                                data: data,
                                success: function(responseData) {
                                    if (responseData.code === 0) {
                                        $.each(updatedItems, function(key, value) {
                                            // save new dropped destination vehicles to right side
                                            if (extraParams.updatedItems.inAssignedGroup === true) {
                                                updatedDestinationItem  = $('#vehicle-group-assignment .drag-drop-assigned').find('li').filter('[data-id="'+value.id+'"]');
                                                ItemSourceGroupId       = updatedDestinationItem.data('groupId');
                                                updatedDestinationItem.removeClass('transfer-group-id-'+ItemSourceGroupId);
                                                updatedDestinationItem.data('groupId', assignToGroupId);
                                                updatedDestinationItem.addClass('transfer-group-id-'+assignToGroupId);

                                            } else if (extraParams.updatedItems.inAssignedGroup !== true && assignToGroupId != 'All') {
                                                // save new dropped destination vehicles to left side

                                                updatedSourceItem = $('#vehicle-group-assignment .drag-drop-available').find('li').filter('[data-id="'+value.id+'"]');
                                                ItemSourceGroupId = updatedSourceItem.data('groupId');
                                                updatedSourceItem.removeClass('transfer-group-id-'+ItemSourceGroupId);
                                                updatedSourceItem.data('groupId', assignToGroupId);
                                                updatedSourceItem.addClass('transfer-group-id-'+assignToGroupId);
                                            }
                                        });
                                    } else {
                                        // restore the failed association back to its original location
                                        if (! $.isEmptyObject(responseData.data) && ! $.isEmptyObject(responseData.data.failed_groups)) {
                                            var destination         = (extraParams.updatedItems.inAssignedGroup === true) ? 'assigned' : 'available',
                                                destinationItems   = $self.find('.drag-drop-'+destination+' li'),
                                                source             = $self.find('.drag-drop-'+((destination == 'available') ? 'assigned' : (destination == 'assigned') ? 'available' : destination))
                                            ;

                                            $.each(responseData.data.failed_groups, function(key, value) {
                                                // remove the each failed vehicle from their destination and add them back to their origin
                                                source.append(destinationItems.filter('[data-id="'+value.id+'"]').detach());
                                            });

                                            alert(responseData.message);
                                        }
                                    }

                                    if (! $.isEmptyObject(responseData.message)) {
                                        Core.SystemMessage.show(responseData.message, responseData.code);
                                    }

                                    Core.DragDrop.destroy();
                                    Core.DragDrop.init();
                                }
                            });

                        } else {

                            var destination = (extraParams.updatedItems.inAssignedGroup === true) ? 'assigned' : 'available',
                                destinationItems = $self.find('.drag-drop-'+destination+' li'),
                                source = $self.find('.drag-drop-'+((destination == 'available') ? 'assigned' : (destination == 'assigned') ? 'available' : destination))
                            ;

                            $.each(updatedItems, function(key, value) {
                                //remove the each failed vehicle from their destination and add them back to their origin
                                source.append(destinationItems.filter('[data-id="'+value.id+'"]').detach().show());
                            });

                            alert('Select The Vehicle Group To Assign To.');
                        }
                    }
                });


                // More Options
                var $optionsToggle = $('#vehicle-group-more-options-toggle'),
                    $toggleLabel   = $optionsToggle.find('small')
                ;

                $optionsToggle.on('click', function() {
console.log('$optionsToggle:click');
                    if ($toggleLabel.text() == 'Show More Options') {
                        $toggleLabel.text('Show Less Options');
                    } else {
                        $toggleLabel.text('Show More Options');
                    }

                    $('#vehicle-group-more-options').slideToggle(300);

                });

                // Search Available Vehicles By Name
                var $availableVehicleSearch     = $('#filter-available-text');
                var $availableVehicleSearchGo   = $('#filter-available-go');

                $(document).on('keyup', $availableVehicleSearch.selector, function () {
                    // get current search string
                    var searchvehiclestring = $availableVehicleSearch.val().trim();

                    if (searchvehiclestring.length > 1 || searchvehiclestring.length == 0) {
                        Vehicle.Group.Edit.getFilteredAvailableVehicles(searchvehiclestring, 'available');
                    }
                });

                $(document).on('click', $availableVehicleSearchGo.selector, function () {
console.log('$(document):click:$availableVehicleSearchGo.selector');
                    // get current search string
                    var searchvehiclestring = $availableVehicleSearch.val().trim();

                    if (searchvehiclestring != '') {
                        Vehicle.Group.Edit.getFilteredAvailableVehicles(searchvehiclestring, 'available');
                    }
                });

                var $assignedVehicleSearch     = $('#filter-assigned-text');
                var $assignedVehicleSearchGo   = $('#filter-assigned-go');

                $(document).on('keyup', $assignedVehicleSearch.selector, function () {
                    // get current search string
                    var searchvehiclestring = $assignedVehicleSearch.val().trim();

                    if (searchvehiclestring.length > 1 || searchvehiclestring.length == 0) {
                        Vehicle.Group.Edit.getFilteredAvailableVehicles(searchvehiclestring, 'assigned');
                    }
                });

                $(document).on('click', $assignedVehicleSearchGo.selector, function () {
console.log('$(document):click:$assignedVehicleSearchGo.selector');
                    // get current search string
                    var searchvehiclestring = $assignedVehicleSearch.val().trim();

                    if (searchvehiclestring != '') {
                        Vehicle.Group.Edit.getFilteredAvailableVehicles(searchvehiclestring, 'assigned');
                    }
                });
            },

            getFilteredAvailableVehicles: function(searchString, groupColumn) {

                searchString = searchString || '';
                groupColumn = groupColumn || '';
                searchFromGroupId = '';

                var $vehicleGroupAssignment = $('#vehicle-group-assignment')
                if (groupColumn == 'available') {
                    $searchGroupList  = $vehicleGroupAssignment.find('.drag-drop-available');
                    searchFromGroupId = $('#detail-panel').find('.hook-editable-keys').data('vehicleDefaultGroupId');
                } else {
                    $searchGroupList  = $vehicleGroupAssignment.find('.drag-drop-assigned');
                    searchFromGroupId = $('#detail-panel').find('.hook-editable-keys').data('vehicleGroupPk');
                }

console.log('getFilteredAvailableVehicles:ajax');

                $.ajax({
                    url: '/ajax/vehicle/getFilteredAvailableVehicles',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        search_string: searchString,
                        vehiclegroup_id: searchFromGroupId
                    },
                    success: function(responseData) {
                        if (responseData.code === 0) {
                            // clear available
                            $($searchGroupList.selector).html('');

                            // create available vehicle list
                            if (! $.isEmptyObject(responseData.data.units)) {
                                var available_vehicles = responseData.data.units;
                                $.each(available_vehicles, function() {
                                    $searchGroupList.append(Core.DragDrop._generateGroupMarkup(this.unit_id, this.unitname));
                                });
                            }

                            // destroy and re-init dragdrop containers
                            Core.DragDrop.destroy();
                            Core.DragDrop.init();

                        } else {
                            if (! $.isEmptyObject(responseData.validation_error)) {
                                //	display validation errors
                            }
                        }

                        if (! $.isEmptyObject(responseData.message)) {
                            Core.SystemMessage.show(responseData.message, responseData.code);
                        }
                    }
                });
            }
        },

        Popover: {

            init: function() {
                /**
                 * Add vehicle group
                 **/
                /*$(document).on('click', '#new-vehicle-group-confirm', function() {
                    var $modal = $('#modal-vehicle-group-list'),
                        $groupName = $('#vehicle-group-name-new'),
                        groupName = $groupName.val()
                    ;

                    if (groupName != '' && groupName != undefined) {
                        $.ajax({
                            url: '/ajax/vehicle/addVehicleGroup',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                vehiclegroupname: groupName
                            },
                            success: function(responseData) {
                                if (responseData.code === 0) {
                                    // close popover
                                    $('#popover-new-vehicle-group-cancel').trigger('click');

                                    // on success, redraw the verification table
                                    Vehicle.Group.DataTable.vehicleGroupListTable.fnStandingRedraw();
                                } else {
                                    if (! $.isEmptyObject(responseData.validation_error)) {
                                        //	display validation errors
                                    }
                                }

                                if (! $.isEmptyObject(responseData.message)) {
                                    Core.SystemMessage.show(responseData.message, responseData.code);
                                }

                                $('#vehicle-group-list-table').one('Core.Datatable.standingRedraw', function(event) {

                                    $(this).find('.col-name').each(function() {
                                        var $self = $(this),
                                            $a    = $self.find('a')
                                        ;

                                        if ($a.text() == groupName) {
                                            $a.trigger('click');
                                            return false; // break
                                        } else {
                                            return true; // continue
                                        }
                                    });
                                });
                            }
                        });
                    } else {
                        alert('Vehicle Group Name cannot be blank');
                    }
                });*/

                /**
                 * Delete vehicle group
                 **/
                $(document).on('click', '#popover-vehicle-group-delete-confirm', function() {
console.log('$(document):click:#popover-vehicle-group-delete-confirm');
                    var $modal = $('#modal-vehicle-group-list'),
                        //vehicleGroupId = $('#detail-panel').find('.hook-editable-keys').data('vehicleGroupPk')
                        vehicleGroupId = $('#vehicle-group-source').val();
                        vehicleGroupLabel = $('#vehicle-group-source').text();
                    ;
console.log('popover-vehicle-group-delete-confirm:'+vehicleGroupId+':'+vehicleGroupLabel);
                    if (vehicleGroupLabel != 'Default' && vehicleGroupId != '' && vehicleGroupId != 'All' && vehicleGroupId != undefined) {
                        if (confirm('Permanently Delete this Group?')){
                            $.ajax({
                                url: '/ajax/vehicle/deleteVehicleGroup',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    vehiclegroup_id: vehicleGroupId
                                },
                                success: function(responseData) {
                                    if (responseData.code === 0) {
                                        // close popover
                                        $('#popover-user-vehicle-group-cancel').trigger('click');

                                        // close modal
                                        $modal.find('.modal-footer button').eq(0).trigger('click');

                                        // on success, redraw the verification table
                                        Vehicle.Group.DataTable.vehicleGroupListTable.fnStandingRedraw();
                                    } else {
                                        if (! $.isEmptyObject(responseData.validation_error)) {
                                            //	display validation errors
                                            alert(responseData.validation_error);
                                        }
                                    }

                                    if (! $.isEmptyObject(responseData.message)) {
                                        Core.SystemMessage.show(responseData.message, responseData.code);
                                    }
                                }
                            });
                        }
                    } else {
                        alert('Invalid vehicle group ID');
                    }
                });

                // Move assigned landmarks to another group
                $(document).on('click', '#popover-move-to-group-confirm', function() {
console.log('$(document):click:#popover-move-to-group-confirm');
                    var $selectedAssignVehicles    = $('.drag-drop-assigned').find('.drag-drop-item').filter('.active');
                    var vehicleGroupId             = $('#move-to-group').val();
                    var movevehicles               = new Array();
                    var transfervehicles           = new Array();

                    $selectedAssignVehicles.each(function(index) {
                        var vehicle_id = $(this).data('id');
                        saveVehicle = {id: vehicle_id};
                        movevehicles.push(saveVehicle);
                        transfervehicles.push(vehicle_id);
                    });

                    if (movevehicles != undefined && movevehicles.length != 0 && vehicleGroupId != undefined && vehicleGroupId != 0) {

console.log('#popover-move-to-group-confirm:ajax');

                        $.ajax({
                            url: '/ajax/vehicle/addVehiclesToGroup',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                vehiclegroup_id: vehicleGroupId,
                                vehicles: movevehicles
                            },
                            success: function(responseData) {
                                if (responseData.code === 0) {

                                    // remove the failed groups from their destination and add them back to their origin
                                    $.each(transfervehicles, function(key,value) {
                                        $('.drag-drop-assigned li').filter('[data-id="'+value+'"]').detach();
                                    });

                                    $('#popover-move-to-group-cancel').trigger('click');

                                    $('#move-to-group').val(0).text('Select One');

                                    Vehicle.Group.DataTable.vehicleGroupListTable.fnStandingRedraw();

                                }

                                if (! $.isEmptyObject(responseData.message)) {
                                    Core.SystemMessage.show(responseData.message, responseData.code);
                                }
                            }
                        });
                    } else {
                        var alertmessage = '';
                        if (movevehicles == undefined || movevehicles.length == 0) {
                            alertmessage = '\n     -assigned vehicle';
                        }
                        if (vehicleGroupId == undefined || vehicleGroupId == 0) {
                            alertmessage = alertmessage + '\n     -destination vehicle group';
                        }
                        alert('Missing:'+alertmessage);
                    }
                });

                // Canceling move to group popover
                $(document).on('click', '#popover-move-to-group-cancel', function() {
console.log('$(document):click:#popover-move-to-group-cancel');
                    $('#move-to-group').val(0).text('Select One');
                });

                /**
                 * Reset Available Vehicles filter
                 */
                $(document).on('click', '#popover-available-filter-reset', function() {
console.log('$(document):click:#popover-available-filter-reset');
                    $('#filter-available-text').val('');
                    Vehicle.Group.Edit.getFilteredAvailableVehicles('');
                });

                /**
                 * Reset Add Vehicle Group Popover
                 **/
                $('#popover-vehicle-group-new').on('hidden.bs.popover', function() {
                    $('#vehicle-group-name-new').val('')
                });

                /**
                 * Reset Filter Available Group Popover
                 **/
                $('#popover-filter-available').on('hidden.bs.popover', function() {
                    $('#filter-available-text').val('')
                });
            }
        }
    },

    Common: {

        DataTables: {

            init: function() {

                // quick history all table
                Vehicle.Common.DataTables.quickHistoryAllDataTable = Core.DataTable.init('quick-history-all-table', 5, {
                    'bServerSide': true,
                    'sAjaxSource':   '/ajax/vehicle/getVehicleQuickHistory',
                    'aoColumns': [
                        { 'mDataProp': 'mappoint' },
                        { 'mDataProp': 'display_unittime' },
                        { 'mDataProp': 'eventname' },
                        { 'mDataProp': 'formatted_address' },
                        { 'mDataProp': 'speed' },
                        { 'mDataProp': 'duration' }
                    ],
                    'aaSorting': [
                        [1,'desc']  // order by date time (display_unittime) desc
                    ],
                    'fnServerParams': function (aoData) {
console.log('Vehicle.Common.DataTables.quickHistoryAllDataTable:fnServerParams');
                        // default params
                        var unit_id         = 0,
                            eventType       = 'all',
                            dateFilter      = 'today',
                            startDate       = '',
                            endDate         = '',
                            durationFilter  = '0',      //'5-hours'
                            toDay           = new Date(),
                            $historyDateFilter     = $('#select-history-day-filter'),
                            $historyDurationFilter = $('#select-history-duration-filter')
                        ;

                        unit_id   = $('#detail-panel').find('.hook-editable-keys').data('vehiclePk');
                        //eventType = $(".event-type-button.active").attr("value");

                        if ($historyDateFilter.val() != '') {
                            dateFilter = $historyDateFilter.val();
                        }

                        if ($historyDurationFilter.val() != '') {
                            //durationFilter = $historyDurationFilter.val();
                        }

                        startDate = Core.StringUtility.filterStartDateConversion(toDay, dateFilter);
                        endDate   = Core.StringUtility.filterEndDateConversion(toDay, dateFilter);

                        // convert the start and end dates to UTC here so that they will be consistent
                        // on the server side, regardless of the user's current timezone
                        if (startDate != '') {
                            startDate = moment(startDate).utc().format();
                            startDate = startDate.replace('T', ' ').replace(/\+.*/, '');
                        }

                        if (endDate != '') {
                            endDate = moment(endDate).utc().format();
                            endDate = endDate.replace('T', ' ').replace(/\+.*/, '');
                        }

                        aoData.push({name: 'unit_id', value: unit_id});
                        aoData.push({name: 'start_date', value: startDate});
                        aoData.push({name: 'end_date', value: endDate});
                        aoData.push({name: 'event_type', value: eventType});
                        aoData.push({name: 'duration', value: durationFilter});
                    },
                    'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
console.log('Vehicle.Common.DataTables.quickHistoryAllDataTable:fnRowCallback');
                        $('td:eq(3)', nRow).html('<a class="quick-history-map-link" data-mappoint="'+aData.mappoint+'" data-datetime="'+aData.display_unittime+'" data-event="'+aData.eventname+'" data-landmarkname="'+aData.territoryname+'" data-location="'+aData.formatted_address+'" data-speed="'+aData.speed+'" data-duration="'+aData.duration+'" data-lat="'+aData.latitude+'" data-long="'+aData.longitude+'" href="#">' + ((aData.territoryname != undefined) ? ('('+aData.territoryname + ') ') : '') + aData.formatted_address+'</a>');
                        return nRow;
                    }
                });

                // quick history recent table
                Vehicle.Common.DataTables.quickHistoryRecentDataTable = Core.DataTable.init('quick-history-recent-table', 5, {
                    'bServerSide': true,
                    'sAjaxSource':   '/ajax/vehicle/getVehicleQuickHistory',
                    'aoColumns': [
                        { 'mDataProp': 'mappoint' },
                        { 'mDataProp': 'display_unittime' },
                        { 'mDataProp': 'eventname' },
                        { 'mDataProp': 'formatted_address' },
                        { 'mDataProp': 'duration' }
                    ],
                    'aaSorting': [
                        [1,'desc']  // order by date time (display_unittime) desc
                    ],
                    'fnServerParams': function (aoData) {
console.log('Vehicle.Common.DataTables.quickHistoryRecentDataTable:fnServerParams');
                        // default params
                        var unit_id         = 0,
                            eventType       = 'recent',
                            dateFilter      = 'today',
                            startDate       = '',
                            endDate         = '',
                            durationFilter  = '0',      //'5-hours'
                            toDay           = new Date(),
                            $historyDateFilter     = $('#select-history-day-filter'),
                            $historyDurationFilter = $('#select-history-duration-filter')
                        ;

                        unit_id = $('#detail-panel').find('.hook-editable-keys').data('vehiclePk');
                        //eventType = $(".event-type-button.active").attr("value");

                        if ($historyDateFilter.val() != '') {
                            dateFilter = $historyDateFilter.val();
                        }

                        if ($historyDurationFilter.val() != '') {
                            durationFilter = $historyDurationFilter.val();
                        }

                        startDate = Core.StringUtility.filterStartDateConversion(toDay, dateFilter);
                        endDate   = Core.StringUtility.filterEndDateConversion(toDay, dateFilter);

                        // convert the start and end dates to UTC here so that they will be consistent
                        // on the server side, regardless of the user's current timezone
                        if (startDate != '') {
                            startDate = moment(startDate).utc().format();
                            startDate = startDate.replace('T', ' ').replace(/\+.*/, '');
                        }

                        if (endDate != '') {
                            endDate = moment(endDate).utc().format();
                            endDate = endDate.replace('T', ' ').replace(/\+.*/, '');
                        }

                        aoData.push({name: 'unit_id', value: unit_id});
                        aoData.push({name: 'start_date', value: startDate});
                        aoData.push({name: 'end_date', value: endDate});
                        aoData.push({name: 'event_type', value: eventType});
                        aoData.push({name: 'duration', value: durationFilter});
                    },
                    'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
console.log('Vehicle.Common.DataTables.quickHistoryRecentDataTable:fnRowCallback');
                        $('td:eq(3)', nRow).html('<a class="quick-history-map-link" data-mappoint="'+aData.mappoint+'" data-datetime="'+aData.display_unittime+'" data-event="'+aData.eventname+'" data-landmarkname="'+aData.territoryname+'" data-location="'+aData.formatted_address+'" data-duration="'+aData.duration+'" data-lat="'+aData.latitude+'" data-long="'+aData.longitude+'" href="#">' + ((aData.territoryname != undefined) ? ('('+aData.territoryname + ') ') : '') + aData.formatted_address+'</a>');
                        return nRow;
                    }
                });

                // quick history frequent table
                Vehicle.Common.DataTables.quickHistoryFrequentDataTable = Core.DataTable.init('quick-history-frequent-table', 5, {
                    'bServerSide': true,
                    'sAjaxSource':   '/ajax/vehicle/getVehicleQuickHistory',
                    'aoColumns': [
                        { 'mDataProp': 'mappoint' },
                        { 'mDataProp': 'stop_counter' },
                        { 'mDataProp': 'formatted_address' },
                        { 'mDataProp': 'duration' }
                    ],
                    /* // remove since ordering by stop_counter is done first at code level
                    'aaSorting': [
                        [0,'asc'],
                        [1,'desc']
                    ],
                    */
                    'fnServerParams': function (aData) {
console.log('Vehicle.Common.DataTables.quickHistoryFrequentDataTable:fnServerParams');

                        // default params
                        var unit_id         = 0,
                            eventType       = 'frequent',
                            dateFilter      = 'today',
                            startDate       = '',
                            endDate         = '',
                            durationFilter  = '0',      //'5-hours'
                            toDay           = new Date(),
                            $historyDateFilter     = $('#select-history-day-filter'),
                            $historyDurationFilter = $('#select-history-duration-filter')
                        ;

                        unit_id   = $('#detail-panel').find('.hook-editable-keys').data('vehiclePk');
                        //eventType = $(".event-type-button.active").attr("value");

                        if ($historyDateFilter.val() != '') {
                            dateFilter = $historyDateFilter.val();
                        }

                        if ($historyDurationFilter.val() != '') {
                            durationFilter = $historyDurationFilter.val();
                        }

                        startDate = Core.StringUtility.filterStartDateConversion(toDay, dateFilter);
                        endDate   = Core.StringUtility.filterEndDateConversion(toDay, dateFilter);

                        // convert the start and end dates to UTC here so that they will be consistent
                        // on the server side, regardless of the user's current timezone
                        if (startDate != '') {
                            startDate = moment(startDate).utc().format();
                            startDate = startDate.replace('T', ' ').replace(/\+.*/, '');
                        }

                        if (endDate != '') {
                            endDate = moment(endDate).utc().format();
                            endDate = endDate.replace('T', ' ').replace(/\+.*/, '');
                        }

                        aData.push({name: 'unit_id', value: unit_id});
                        aData.push({name: 'start_date', value: startDate});
                        aData.push({name: 'end_date', value: endDate});
                        aData.push({name: 'event_type', value: eventType});
                        aData.push({name: 'duration', value: durationFilter});
                    },
                    'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
console.log('Vehicle.Common.DataTables.quickHistoryFrequentDataTable:fnRowCallback');
                        $('td:eq(2)', nRow).html('<a class="quick-history-map-link" data-mappoint="'+aData.mappoint+'" data-stopcounter="'+aData.stop_counter+'" data-landmarkname="'+aData.territoryname+'" data-location="'+aData.formatted_address+'" data-duration="'+aData.duration+'" data-lat="'+aData.latitude+'" data-long="'+aData.longitude+'" href="#">' + ((aData.territoryname != undefined) ? ('('+aData.territoryname + ') ') : '') + aData.formatted_address+'</a>');
                        return nRow;
                    }
                });

                // verification table
                Vehicle.Map.DataTables.verificationDataTable = Core.DataTable.init('verification-table', 5, {
                    'bServerSide': true,
                    'sAjaxSource': '/ajax/vehicle/getVehicleVerificationData',
                    'aoColumns': [
                        { 'mDataProp': 'territoryname',           'sClass': 'verification-name-cell' },
                        { 'mDataProp': 'formatted_address',       'sClass': 'verification-address-cell' },
                        { 'mDataProp': 'latitude',                'sClass': 'verification-coords-cell' },
                        { 'mDataProp': 'radius_in_miles',         'sClass': 'verification-radius-cell' },
                        { 'mDataProp': 'verified',                'sClass': 'verification-verified-cell' },
                        { 'mDataProp': 'formatted_verified_date', 'sClass': 'verification-date-cell' },
                        { 'mDataProp': 'table_actions',           'sClass': 'table-actions' }
                    ],
                    'aaSorting': [
                        [0,'asc']
                    ],
                    'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
console.log('Vehicle.Map.DataTables.verificationDataTable:fnRowCallback');
                       // format coordinate string
                       $('td:eq(2)', nRow).html(aData.formatted_latitude + '/' + aData.formatted_longitude);

                       // format verified status
                       var verifiedStatus = '<span class="label label-' + (aData.verified ? 'success' : 'danger') + '">' + (aData.verified ? 'Verified' : 'Not Verified') + '</span>'
                       $('td:eq(4)', nRow).html(verifiedStatus);

                       // store current reference landmark info for use when editing landmarks
                       $(nRow).data('landmarkId', aData.territory_id)
                              .data('latitude', aData.latitude)
                              .data('longitude', aData.longitude)
                              .data('streetAddress', aData.streetaddress)
                              .data('city', aData.city)
                              .data('state', aData.state)
                              .data('zip', aData.zipcode)
                              .data('country', aData.country);

                       return nRow;
                    },
                    'fnServerParams': function (aData) {
console.log('Vehicle.Map.DataTables.verificationDataTable:fnServerParams');
                        // default params
                        var unit_id         = 0,
                           filter_type      = 'all'
                        ;

                        var unitId = $('#detail-panel').find('.hook-editable-keys').data('vehiclePk');

                        if ( typeof(unitId) != 'undefined') {
                            unit_id = unitId;
                        }

                        var filterType = $('#verification-filter').val();

                        if (typeof(filterType) != 'undefined') {
                            filter_type = filterType;
                        }

                        aData.push({name: 'unit_id', value: unit_id});
                        aData.push({name: 'filter_type', value: filter_type});
                    },
                    'fnDrawCallback': function() {
console.log('Vehicle.Map.DataTables.verificationDataTable:fnDrawCallback');
                        Core.Tooltip.init();
                        Core.Popover.init();
                    }
                });
            }
        },

        SecondaryPanel: {

            init: function() {

                var $detailPanelTriggers        = $('.sub-panel-items, .any-other-triggers-may-go-here'),
                    $subPanelItems              = $('.sub-panel-items'),
                    $sidebarScroll              = $('#secondary-sidebar-scroll')
                ;

                /**
                 *
                 * Clicking on the Edit Icon in the Secondary Panel
                 *
                 * handles opening of detail panel
                 *
                 */
                $detailPanelTriggers.on('click', 'span.glyphicon', function(event) {
console.log('$detailPanelTriggers:click:span.glyphicon');

                    event.stopPropagation();

                    var $self         = $(this),
                        $selectedItem = $self.closest('li'),
                        selectedItemId = $selectedItem.attr('id').split('-')[2],
                        autoOpenDetailPanel = true,
                        $detailPanel = $('#detail-panel'),
                        $detailPanelHooks = $detailPanel.find('.hook-editable-keys').eq(0)
                    ;

                    if ( ! $selectedItem.is('.active')) {
                        // reset quick history filter date/duration states to default values if selected units is a new
                        if ($detailPanelHooks.data('vehiclePk') != selectedItemId) {
                            Vehicle.Common.DetailPanel.resetQuickHistoryDataTable();
                        }

                        $selectedItem.trigger('click', {hideLabel: true, autoCloseDetailPanel: false});
                    } else {
                        if (Core.Environment.context() == 'vehicle/map') {
                            // if the detail panel already has data for this unit, just open the detail panel again (no need for ajax)
                            if (! $detailPanel.is('.open')) {
                                if ($detailPanelHooks.length > 0) {
                                    if ($detailPanelHooks.data('vehiclePk') == selectedItemId) {
                                        Vehicle.Common.DetailPanel.open(function() {
                                            Map.resize(Vehicle.Map.map);
                                            if ($selectedItem.data('eventId') != null) {
                                                Map.showHideLabel(Vehicle.Map.map, selectedItemId, false);
                                                Map.clickMarker(Vehicle.Map.map, selectedItemId);
                                            } else {
                                                Map.resetMap(Vehicle.Map.map);
                                            }
                                        });

                                        // set auto open detail panel to false
                                        autoOpenDetailPanel = false;
                                    }
                                }
                            }
                        } else if (Core.Environment.context() == 'vehicle/list') {
                            // do something else for vehicle list
                        }
                    }

                    var $otherSelectedItems = $subPanelItems.find('li').filter('.active').not($selectedItem),
                        lastIndex = $otherSelectedItems.length - 1
                    ;

                    if ($otherSelectedItems.length > 0) {

                        $.each($otherSelectedItems, function(key, value) {
                            updateMapBound = false;

                            if (key == lastIndex) {
                                updateMapBound = true;
                            }

                            $(this).trigger('click', {autoOpenDetailPanel: autoOpenDetailPanel, updateMapBound: updateMapBound});
                        });
                    }
                });

                /**
                 *
                 * Hovering over a Vehicle in the Secondary Panel
                 *
                 * */
                $subPanelItems.on('mouseenter', 'li', function() {
                    var $self = $(this);
                    $self.find('.toggle').show();
                });

                $subPanelItems.on('mouseleave', 'li', function() {
                    var $self = $(this);
                    $self.find('.toggle').hide();
                });

                /**
                 *
                 * Clicking Select - All
                 *
                 * selects all vehicles
                 *
                 */
                $('#vehicle-toggle-all').click(function() {
console.log('#vehicle-toggle-all');

                    var $subPanelItems = $('.sub-panel-items'),
                        $nonActiveItems = $subPanelItems.find('li').filter(':not(.active)'),
                        lastIndex = $nonActiveItems.length - 1,
                        unitIds = new Array(),
                        unitId = '',
                        $detailPanel = $('#detail-panel')
                    ;

                    $('#secondary-panel-pagination').data('drawMarker', 'yes');

                    $nonActiveItems.each(function(key, value) {

                        unitId = $(this).attr('id').split('-')[2];

                        currentUnitId = unitId;

                        if (unitId != undefined && unitId != '') {
                            unitIds.push(unitId);
                        }

                    });

console.log('#vehicle-toggle-all:unitIds.length:'+unitIds.length);

                    // make ajax call to get the units' event after the last unit id has been processed
                    if (unitIds.length > 0) {

console.log('#vehicle-toggle-all:ajax');

                        $.ajax({
                            url: '/ajax/vehicle/getLastReportedEvent',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                unit_id: unitIds
                            },
                            success: function(responseData) {
console.log('#vehicle-toggle-all:responseData.code:'+responseData.code);
                                if (responseData.code === 0) {
console.log('#vehicle-toggle-all:responseData.data.length:'+responseData.data.length);
                                    if (responseData.data.length > 0) {

                                        // close detail panel and info window
                                        Map.closeInfoWindow(Vehicle.Map.map);

                                        if ($detailPanel.is('.open')) {     // if the detail panel is opened, perform map-related tasks only after closing panel

                                            var singleItemId = $detailPanel.find('.hook-editable-keys').eq(0).data('vehiclePk');
                                            $('#hide-vehicle-panel').trigger('click', function() {

                                                // show the current unit's label
                                                Map.showHideLabel(Vehicle.Map.map, singleItemId, true);

                                                // resize map after closing detail panel
                                                Map.resize(Vehicle.Map.map);
console.log('*** Map.resize **************************');

                                                Map.addMarkers(Vehicle.Map.map, 'unit', responseData.data);
                                            });
                                        } else {                            // else, simply perform map-related tasks
                                            Map.addMarkers(Vehicle.Map.map, 'unit', responseData.data);
                                        }
                                    }
                                } else {
                                    if (! $.isEmptyObject(responseData.validation_error)) {
                                        //	display message
                                    }
                                }

                                if (! $.isEmptyObject(responseData.message)) {
                                    Core.SystemMessage.show(responseData.message, responseData.code);
                                }
                            }
                        });

                    }

                });

                /**
                 *
                 * Clicking Select - None
                 *
                 * selects all vehicles
                 *
                 */
                $('#vehicle-toggle-none').click(function() {
                    var $activeItems = $('.sub-panel-items').find('li').filter('.active'),
                        $detailPanel = $('#detail-panel')
                    ;

                    $('#secondary-panel-pagination').data('drawMarker', '');

                    $activeItems.removeClass('active');
                    Map.clearMarkers(Vehicle.Map.map);
                    $('#hide-vehicle-panel').trigger('click', function() {
                        Map.resetMap(Vehicle.Map.map);
console.log('*** Map.resetMap #vehicle-toggle-none **************************');
                    });
                });

                /**
                 *
                 * Clicking on a Vehicle in Secondary Panel
                 *
                 * handles showing which vehicle is selected in the sidebar
                 *
                 * */
                $sidebarScroll.on('click', 'li', function(event, extraParams) {
console.log("$sidebarScroll.on('click', 'li', function(event, extraParams) {");

                    $('#stops-report-all').find('.dataTables-current-page').text('1');
                    $('#stops-report-frequent').find('.dataTables-current-page').text('1');
                    $('#stops-report-recent').find('.dataTables-current-page').text('1');
                    $('#verification-report-recent').find('.dataTables-current-page').text('1');

                    allNone=0;
                    mapZoomBool='';
                    
                    var $self = $(this);
                    currentUnitIdHidePanel='';
                    $self.closest('ul').find('li').each( function() {
                        $(this).removeClass('active');
                        $(this).removeClass('all-none-active');
                    });
                    $self.addClass('active');
                    setTimeout("$('#refresh-map-markers').trigger('click')",1);

                    // if($self.find('a').hasClass('li-inventory')){
                    //     $('#vehicle-status').prop('disabled',true);
                    // } else {
                    //     $('#vehicle-status').prop('disabled',false);
                    // }

                    commandBool='';
                    if ($('#popover-content-starter-enable-panels').is(":visible")){
                        $('#popover-content-starter-enable-panels').find('.popover-cancel').trigger('click');
                    } else if ($('#popover-content-starter-disable-panels').is(":visible")){
                        $('#popover-content-starter-disable-panels').find('.popover-cancel').trigger('click');
                    } else if ($('#popover-content-reminder-enable-panels').is(":visible")){
                        $('#popover-content-reminder-enable-panels').find('.popover-cancel').trigger('click');
                    } else if ($('#popover-content-reminder-disable-panels').is(":visible")){
                        $('#popover-content-reminder-disable-panels').find('.popover-cancel').trigger('click');
                    } else if ($('#popover-content-locate-on-demand-panels').is(":visible")) {
                        $('#popover-content-locate-on-demand-panels').find('.popover-cancel').trigger('click');
                    }

                    if($('#vehicle-detail-info-tab').is(':visible')){
                        $('#vehicle-detail-info-tab').trigger('click');
                    }else if($('#vehicle-detail-commands-tab').is(':visible')){
                        $('#vehicle-detail-commands-tab').trigger('click');
                    }else if($('#vehicle-detail-quick-history').is(':visible')){
                        $('#vehicle-detail-quick-history').trigger('click');
                    }else if($('#vehicle-detail-verification-tab').is(':visible')){
                        $('#vehicle-detail-verification-tab').trigger('click');
                    }
                    $('#stops-breadcrumbs').val(0);
                    $('#btn-stops-all').trigger('click');

                    $('#div-all-none-clear').css({ display: 'none'});
                    $('#div-all-none').css({ display: 'block'});                                

                });

                /**
                 *
                 * Setting Up the Vehicle Map
                 *
                 * handles showing vehicles selected on the sidebar on the map and open Detail Panel if only a single vehicle is selected
                 *
                 * */
                $('#refresh-map-markers').on('click', function() {
                    
                    var $self = '',
                        autoOpenDetailPanel = true,
                        autoCloseDetailPanel = true,
                        hideLabel = true,
                        method = 'getVehicleInfo',
                        refreshMap = false,
                        updateMapBound = true,
                        unitIds = [],
                        unitId = ''
                    ;

                    if ( ( mapAutoRefresh ) && ( $('#tab-commands').is(':visible') || $('#tab-quick-history').is(':visible') || $('#tab-verification').is(':visible') || skipRefresh ) ) {

console.log('*** Core.MapAutoRefreshToggle - TURN ON **************************');
                        Core.MapAutoRefreshToggle();

                    } else {

                        $('#secondary-sidebar-scroll').find('li').filter('.active').each(function(){
                            $self = $(this);
                            unitId = $self.attr('id').split('-')[2];
                            unitIds.push($self.attr('id').split('-')[2]);
                        });

                        // if(unitId != commandBool){
                        //     $('#vehicle-detail-info-tab').trigger('click');
                        // }

                        if ((method !== 'getLastReportedEvent') && ((lastDevice === '') || (singleItemId !== lastDevice))) {
                            $('#div-command-locate').hide();
                            $('#div-command-locate-span').text('');
                            $('#div-command-reminder').hide();
                            $('#div-command-reminder-span').text('');
                            $('#div-command-starter').hide();
                            $('#div-command-starter-span').text('');
                        }

                        currentUnitId = unitId;
                        currentEventData='';
                        currentUnitData='';
console.log('#refresh-map-markers:/ajax/vehicle/'+method);
console.log(unitIds);
                        $.ajax({
                            url: '/ajax/vehicle/'+method+'',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                unit_id: unitId,
                                unitIds: unitIds
                            },
                            success: function(responseData) {

                                if (responseData.code === 0) {

                                    // $('#print-button').attr('href','vehicle/print/'+unitId);

                                    var unitdata = {},
                                        eventdata = {}
                                    ;

                                    if (method === 'getLastReportedEvent' && ! $.isEmptyObject(responseData.data)) {
                                        unitdata = responseData.data[0];
                                        eventdata = unitdata.eventdata;
                                    } else {
                                        unitdata = responseData.data;
                                        eventdata = unitdata.eventdata;
                                    }

                                    // Update the Map  (if in vehicle/map context)
                                    if (Core.Environment.context() == 'vehicle/map') {
console.log('/// Update the Map  (if in vehicle/map context):'+Core.Environment.context());

                                        $('#vehicle-li-'+unitId).attr('data-lat',eventdata.latitude);
                                        $('#vehicle-li-'+unitId).attr('data-long',eventdata.longitude);
                                        $('#vehicle-li-'+unitId).attr('data-event-id',eventdata.id);
                                        $('#vehicle-li-'+unitId).attr('data-event',eventdata.eventname);
                                        currentEventData = eventdata;
                                        currentUnitData = unitdata;
                                        
                                        var $detailPanel = $('#detail-panel');
                                        
                                        if (! $.isEmptyObject(eventdata)) {
                                        
                                            // Map.clearMarkers(Vehicle.Map.map);
                    
                                            // var markerOptions = {
                                            //         id: unitdata.unit_id,
                                            //         name: unitdata.unitname+' ('+eventdata.eventname+')',
                                            //         latitude: eventdata.latitude,
                                            //         longitude: eventdata.longitude,
                                            //         eventname: eventdata.eventname, // used in map class to get vehicle marker color
                                            //         click: function() {
                                            //             Map.getVehicleEvent(Vehicle.Map.map, unitdata.unit_id, eventdata.id);
                                            //         }
                                            //     }
                                            // ;

                                            // Map.addMarker(Vehicle.Map.map, markerOptions, hideLabel);

                                            $self.data('event-id', eventdata.id)
                                                 .data('event', eventdata.eventname)
                                                 .data('latitude', eventdata.latitude)
                                                 .data('longitude', eventdata.longitude);
                                            
                                        }

                                        if(responseData.permission.locate=='Y'){
                                            $('#div-command-locate').show();
                                            $('#div-command-last-event').hide();
                                        } else {
                                            $('#div-command-locate').hide();
                                            $('#div-command-last-event').show();
                                        }

                                        if(responseData.permission.buzzer=='Y'){
                                            $('#div-command-reminder').show();
                                        }

                                        if(responseData.permission.starter=='Y'){
                                            $('#div-command-starter').show();
                                        }

                                        if((unitId!=currentUnitIdHidePanel)&&(unitId!=commandBool)){
console.log(')))))))))))))))))))))))))))))))))))))))))))))))))))))))))))))))))))))))) unitId:'+unitId+', currentUnitIdHidePanel:'+currentUnitIdHidePanel+', commandBool:'+commandBool);
                                            Vehicle.Common.DetailPanel.render(unitdata);
                                        }
console.log('painting map-bubble');
console.log(eventdata);
console.log(unitdata);
                                        lastLatVehicle = eventdata.latitude;
                                        lastLongVehicle = eventdata.longitude;
                                        Map.openInfoWindow(Vehicle.Map.map, 'unit', eventdata.latitude, eventdata.longitude, eventdata, unitdata.moving, unitdata.battery, unitdata.signal, unitdata.satellites, unitdata.territoryname, '', unitdata.unitstatus_id);
                                        Core.Map.Refresh('Vehicle.Map.map','',1);

console.log('*** Core.MapAutoRefreshToggle - TURN ON **************************');
                                        Core.MapAutoRefreshToggle();

                                        switch($('#vehicleTabs').find('li.active').find('a').attr('id')){
                                            case                            'vehicle-detail-quick-history': setTimeout("$('#stops-report-all').find('.dataTables-search-btn').trigger('click')",1);
                                                                                                            break;
                                            case                         'vehicle-detail-verification-tab': setTimeout("$('#verification-report-recent').find('.dataTables-search-btn').trigger('click')",1);
                                                                                                            break;
                                        }

                                    }

                                    // In vehicle/list context
                                    if (Core.Environment.context() == 'vehicle/list') {
                                        // do stuff
                                    }

                                } else {

                                    if ($.isEmptyObject(responseData.validaton_error, responseData.code) === false) {
                                        //	display validation errors
                                    }

                                }

                                if ($.isEmptyObject(responseData.message) === false) {
                                    //	display message
                                    //Core.SystemMessage.show(responseData.message, responseData.code);
                                }

                                if(!(responseData)){
                                    window.location = '/logout';
                                }

                            }

                        });

                    }

                });

            },

            initVehicleSearch: function() {

                var $vehicleSearch                  = $('#text-vehicle-search');
                var $vehicleSearchGo                = $('#vehicle-search-go');
                var $vehicleGroupFilter             = $('#sidebar-vehicle-single');
                var $vehicleGroupAttributeFilter    = $('#sidebar-vehicle-status');
                var $secondaryPanelPagination       = $('#secondary-panel-pagination');
                var $selectVehicleSearchTab         = $('#select-vehicle-search-tab');

                /**
                 *
                 * On keyup when searching vehicles using search string
                 *
                 */
                $vehicleSearch.on('keyup', function () {

                    $('#quick-actions').find('.icon16').removeClass('active');

                    // get current search string
                    var searchvehiclestring = $vehicleSearch.val().trim();

                    if (Core.Environment.context() == 'vehicle/map') {

                        $secondaryPanelPagination.data('vehicleStartIndex', 0);
                        $secondaryPanelPagination.data('paging', '');
                        $secondaryPanelPagination.data('drawMarker', '');

                        if (searchvehiclestring.length == 0 || searchvehiclestring.length > 1) {
                            Core.Ajax('secondary-sidebar-scroll',searchvehiclestring,'','devices');
                            //Vehicle.Common.SecondaryPanel.fetchSearchStringFilteredVehicles();
                        } else {
    	                    $('#secondary-sidebar-scroll').empty();
                        }
                    }

                    if (Core.Environment.context() == 'vehicle/list') {

                        if (searchvehiclestring.length > 1) {
                            Vehicle.List.DataTables.vehicleListTable.fnDraw();
                        } else if (searchvehiclestring.length == 0) {
                            Vehicle.List.DataTables.vehicleListTable.fnDraw({});
                        }
                    }

                    $('#sidebar-vehicle-single').val('All').text('All');
                    $('#sidebar-vehicle-status').val('ALL').text('All');

                });

                /**
                 *
                 * On Search Button Click when searching vehicles using search string
                 *
                 */
                $vehicleSearchGo.on('click', function () {
console.log('$vehicleSearchGo:click');
                    // get current search string
                    var searchvehiclestring = $vehicleSearch.val().trim();

                    if (Core.Environment.context() == 'vehicle/map') {
                        $('#quick-actions').find('.icon16').removeClass('active');

                        var $secondaryPanelPagination = $('#secondary-panel-pagination');
                        $secondaryPanelPagination.data('drawMarker', '');

                        if (searchvehiclestring.length >= 0) {
                            $secondaryPanelPagination.data('vehicleStartIndex', 0);
                            $secondaryPanelPagination.data('paging', '');

                            Vehicle.Common.SecondaryPanel.fetchSearchStringFilteredVehicles();
                        } else {
    	                    $('.sub-panel-items').html('');
    	                    $('#secondary-panel-pagination .showing').text('0-0');
                        }
                    }

                    if (Core.Environment.context() == 'vehicle/list') {
                        if (searchvehiclestring != '') {
                            Vehicle.List.DataTables.vehicleListTable.fnDraw();
                        } else {
                            // alert blank search string
                            //Vehicle.List.DataTables.vehicleListTable.fnClearTable();
                        }
                    }

                    $('#sidebar-vehicle-single').val('All').text('All');
                    $('#sidebar-vehicle-status').val('ALL').text('All');

                });

                /**
                 *
                 * On Change of Vehicle Group Filtering on vehicle filter search
                 *
                 */
                $vehicleGroupFilter.on('Core.DropdownButtonChange', function() {

                    $('#text-vehicle-search').val('');
                    $secondaryPanelPagination.data('paging','');
                    $secondaryPanelPagination.data('vehicleStartIndex', 0);

                    if (Core.Environment.context() == 'vehicle/map') {

                        $secondaryPanelPagination.data('drawMarker', '');

                        // reset quick filter highlight
                        $('#map-quick-filters .btn').each(function() {
                            $(this).removeClass('active');
                        });

                        // need to clear map
                        Map.clearMarkers(Vehicle.Map.map);

                        // request vehicles for listing for filter params
                        Vehicle.Common.SecondaryPanel.fetchFilteredVehicles(false, true);
                    }

                    if (Core.Environment.context() == 'vehicle/list') {
                        // clear out the search box before redrawing table
                        //$('#text-vehicle-search').val('');
                        Vehicle.List.DataTables.vehicleListTable.fnDraw();
                    }
                });

                /**
                 *
                 * On Change of Attribute Group Filtering on vehicle filter search
                 *
                 */
                $vehicleGroupAttributeFilter.on('Core.DropdownButtonChange', function() {

                    $('#text-vehicle-search').val('');
                    $secondaryPanelPagination.data('paging','');
                    $secondaryPanelPagination.data('vehicleStartIndex', 0);

                    if (Core.Environment.context() == 'vehicle/map') {

                        $secondaryPanelPagination.data('drawMarker', '');

                        // reset quick filter highlight
                        $('#quick-actions').find('.icon16').removeClass('active');

                        // need to clear map
                        //Map.clearMarkers(Vehicle.Map.map);

                        // request vehicles for listing for filter params
                        Vehicle.Common.SecondaryPanel.fetchFilteredVehicles();
                    }

                    if (Core.Environment.context() == 'vehicle/list') {
                        // clear out the search box before redrawing table
                        $('#text-vehicle-search').val('');
                        Vehicle.List.DataTables.vehicleListTable.fnDraw();
                    }
                });

                /**
                 *
                 * On Clicking vehicle map paging backward glyphicon
                 *
                 */
                $('.glyphicon-backward').click(function() {

                    $secondaryPanelPagination.data('paging','-');

                    Map.clearMarkers(Vehicle.Map.map);
                    //Map.resetMap(Vehicle.Map.map);

                    var drawMarkers = false;
                    if( $secondaryPanelPagination.data('draw-marker') == 'yes') {
                        drawMarkers = true;
                    }

                    var activeUnits = $('.sub-panel-items').find('li').filter('.active');

                    var vehicleFilterTab = $selectVehicleSearchTab.find('li').filter('.active').text();
                    if (vehicleFilterTab == 'Search') {
                        Vehicle.Common.SecondaryPanel.fetchSearchStringFilteredVehicles(drawMarkers, activeUnits);
                    } else {
                        Vehicle.Common.SecondaryPanel.fetchFilteredVehicles(drawMarkers);
                    }
                });

                /**
                 *
                 * On Clicking vehicle map paging forward glyphicon
                 *
                 */
                $('.glyphicon-forward').click(function() {

                    // set page direction value
                    $secondaryPanelPagination.data('paging','+');

                    Map.clearMarkers(Vehicle.Map.map);
                    //Map.resetMap(Vehicle.Map.map);

                    var drawMarkers = false;
                    if( $secondaryPanelPagination.data('draw-marker') == 'yes') {
                        drawMarkers = true;
                    }

                    var activeUnits = $('.sub-panel-items').find('li').filter('.active');

                    var vehicleFilterTab = $selectVehicleSearchTab.find('li').filter('.active').text();
                    if (vehicleFilterTab == 'Search') {
                        Vehicle.Common.SecondaryPanel.fetchSearchStringFilteredVehicles(drawMarkers, activeUnits);
                    } else {
                        Vehicle.Common.SecondaryPanel.fetchFilteredVehicles(drawMarkers);
                    }
                });

                /**
                 *
                 * On Chaning the vehicle amount to be displayed per page for vehicle map paging
                 *
                 */
                $('#secondary-panel-pagination a').click(function() {
                    $self = $(this);
                    var currentActive = $('#secondary-panel-pagination a.active').data('value');

                    if (currentActive != $self.data('value')) {
                        $('#secondary-panel-pagination').find('a').each(function() {
                            if($(this).data('value') == $self.data('value')) {
                                // activate selected vehicle display amount
                                $(this).addClass('active');
                            } else {
                                // deactivate non selected amount
                                $(this).removeClass('active');
                            }
                        });

                        $secondaryPanelPagination.data('paging','');
                        $secondaryPanelPagination.data('vehicleStartIndex', 0);

                        //Map.clearMarkers(Vehicle.Map.map);

                        var activeUnits = $('.sub-panel-items').find('li').filter('.active');

                        var drawMarkers = false;
                        if( $secondaryPanelPagination.data('draw-marker') == 'yes') {
                            drawMarkers = true;
                        }

                        var vehicleFilterTab = $selectVehicleSearchTab.find('li').filter('.active').text();
                        if (vehicleFilterTab == 'Search') {
                            Vehicle.Common.SecondaryPanel.fetchSearchStringFilteredVehicles(drawMarkers, activeUnits);
                        } else {
                            Vehicle.Common.SecondaryPanel.fetchFilteredVehicles(drawMarkers, false, activeUnits);
                        }
                    }
                });

                /**
                 * Export Filter Vehicle List table
                 *
                 */
                var $body = $('body');

                $body.on('click', '#popover-vehicle-list-export-csv-confirm, #popover-vehicle-list-export-pdf-confirm', function() {
console.log('$body:click:#popover-vehicle-list-export-csv-confirm, #popover-vehicle-list-export-pdf-confirm');
                    var exportFormat                = $(this).prop('id') == 'popover-vehicle-list-export-pdf-confirm' ? 'pdf' : 'csv';
                    var $secondaryPanelPagination   = $('#secondary-panel-pagination');
                    var searchvehiclestring         = $('#vehicle-list-table').find('.dataTables-search').val().trim();
                    var search_string               = searchvehiclestring;
                    var vehicle_group_id            = $('#sidebar-vehicle-group').val().trim();
                    var vehicle_state_status        = $('#sidebar-vehicle-status').val().trim();


                    if (Core.Environment.context() == 'vehicle/list') {
                        if (search_string != '') {
                            window.location = '/ajax/vehicle/exportFilteredVehicleList/' + exportFormat + '/string_search/' + search_string + '/All';
                        } else {
                            window.location = '/ajax/vehicle/exportFilteredVehicleList/' + exportFormat + '/group_filter/' + vehicle_group_id + '/' + vehicle_state_status;
                        }
                    } else {
                        if(!(search_string)){
                            search_string = '_SEARCH_';
                        }
                        window.location = '/ajax/vehicle/exportFilteredVehicleList/' + exportFormat + '/' + search_string + '/' + vehicle_group_id + '/' + vehicle_state_status;
                    }

                    switch(exportFormat){

                        case      'csv' :   setTimeout("$('#vehicle-export-csv-cancel').trigger('click')",1500);
                                            break;

                        case      'pdf' :   setTimeout("$('#vehicle-export-pdf-cancel').trigger('click')",1500);
                                            break;

                    }

                });

            },

            fetchSearchStringFilteredVehicles: function (drawMarkers, activeUnits) {
                var $secondaryPanelPagination   = $('#secondary-panel-pagination');
                var searchvehiclestring         = $('#text-vehicle-search').val().trim();

console.log('fetchSearchStringFilteredVehicles');

                // send ajax request
/*
                $.ajax({
                    url: '/ajax/vehicle/searchVehicleByName',
                    type: 'POST',
                    data: {
                        search_string:          searchvehiclestring,
                        vehicle_listing_length: $('#secondary-panel-pagination a.active').data('value'),
                        vehicle_start_index:    $secondaryPanelPagination.data('vehicleStartIndex'),
                        paging:                 $secondaryPanelPagination.data('paging')
                    },
                    dataType: 'json',
                    success: function(responseData) {
                        if (responseData.code === 0) { // 0 means SUCCESS, > 0 means FAIL
                            var html                = '';
                            var vehicles            = responseData.data.vehicles;
                            var endpage             = responseData.data.endpage;
                            var showUnitMarkerLabel = true;

                            if (vehicles.length > 0) {
                                var length = vehicles.length;
                                // create filtered vehicle listing
                                $.each(vehicles, function(key, unit) {
                                    var vehicleListActiveClass = "";
                                    if (drawMarkers === true) {
                                        if (typeof(unit.eventdata.id) != 'undefined' && unit.eventdata.id != '') {
                                            if (vehicles.length != 1) {
                                                vehicleListActiveClass = " active";
                                                showUnitMarkerLabel = false;
                                            }

                                            Map.addMarker(
                                                Vehicle.Map.map,
                                                {
                                                    id: unit.unit_id,
                                                    name: unit.unitname,
                                                    latitude: unit.eventdata.latitude,
                                                    longitude: unit.eventdata.longitude,
                                                    eventname: unit.eventdata.eventname,
                                                    click: function() {
                                                        Map.getVehicleEvent(Vehicle.Map.map, unit.unit_id, unit.eventdata.id);
                                                    }
                                                },
                                                showUnitMarkerLabel
                                            );
                                            if (key == (length - 1)) {
                                                Map.updateMarkerBound(Vehicle.Map.map);
                                                Map.updateMapBound(Vehicle.Map.map);
                                            }
                                        }
                                    }




                                    html += '<li id="vehicle-li-'+unit.unit_id+'" data-event-id="'+unit.eventdata.id+'" class="list-group-item clearfix' + vehicleListActiveClass +'">' +
                                            '   <label for="vehicle-li-'+unit.unit_id+'">'+unit.name+'</label>' +
                                            '   <div class="toggle pull-right">' +
                                            '       <span class="glyphicon glyphicon-pencil"></span>' +
                                            '   </div>' +
                                            '   ' +
                                            '</li>'
                                    ;
                                });

                                // update listing index
                                if ($secondaryPanelPagination.data('paging') == '-') {
                                    var new_start_index = parseInt($secondaryPanelPagination.data('vehicleStartIndex')) - parseInt($('#secondary-panel-pagination a.active').data('value'));
                                    if (new_start_index < 0) {
                                        new_start_index = 0;
                                    }
                                    $secondaryPanelPagination.data('vehicleStartIndex', new_start_index);
                                } else if ($secondaryPanelPagination.data('paging') == '+') {
                                    var new_start_index = parseInt($secondaryPanelPagination.data('vehicleStartIndex')) + parseInt($('#secondary-panel-pagination a.active').data('value'));
                                    $secondaryPanelPagination.data('vehicleStartIndex', new_start_index);
                                } else {
                                    $secondaryPanelPagination.data('vehicleStartIndex', 0);
                                }

                                // update showing text info
                                var start_showing = end_showing = 0;
                                start_showing = parseInt($secondaryPanelPagination.data('vehicleStartIndex')) + 1;
                                end_showing = parseInt($secondaryPanelPagination.data('vehicleStartIndex')) + vehicles.length;
                                var new_showing_text = start_showing + '-' + end_showing;
                                $('#secondary-panel-pagination .showing').text(new_showing_text);
                                $('#secondary-panel-pagination .total').text(responseData.data.total_vehicles_count);

                                if (typeof(endpage) != 'undefined' && parseInt(endpage) == 1) {
                                    $('#secondary-panel-pagination .glyphicon-forward').addClass('hidden');
                                } else {
                                    $('#secondary-panel-pagination .glyphicon-forward').removeClass('hidden');
                                }

                            } else {
                                $('#secondary-panel-pagination .showing').text('0-0');
                                $('#secondary-panel-pagination .total').text('0');
                                if (! $.isEmptyObject(responseData.message)) {
                                    Core.SystemMessage.show(responseData.message, 1);
                                }
                            }

                            $('.sub-panel-items').html(html);
                            
                            var activeUnits = $('.sub-panel-items').find('li').filter('.active');
                            if (Core.Environment.context() == 'vehicle/map') {
                                if (activeUnits != undefined && activeUnits.length > 0) {
                                    Vehicle.Map.paginateActiveVehicles(activeUnits);
                                } else {
                                    if (drawMarkers !== true && vehicles.length != 1) {
                                    	Map.clearMarkers(Vehicle.Map.map);
                                        $('#hide-vehicle-panel').trigger('click', function() {
                                            Map.resetMap(Vehicle.Map.map);
                                        });
                                    } else {
                                        Map.resetMap(Vehicle.Map.map);
                                    }
                                }
                            }
                        } else {
                            $('.sub-panel-items').html('');
                        }
                    },
                    complete: function(){
                    }
                });
*/

            },

            fetchFilteredVehicles: function (drawMarkers, updateQuickFilters, activeUnits)
            {
                var $secondaryPanelPagination = $('#secondary-panel-pagination');

console.log('fetchFilteredVehicles:ajax');

                // send ajax request
                $.ajax({
                    url: '/ajax/vehicle/getFilteredVehicles',
                    type: 'POST',
                    data: {
                        vehicle_group_id:       $('#sidebar-vehicle-single').val().trim(),
                        vehicle_state_status:   $('#sidebar-vehicle-status').val().trim(),
                        vehicle_listing_length: $('#secondary-panel-pagination a.active').data('value'),
                        vehicle_start_index:    $secondaryPanelPagination.data('vehicleStartIndex'),
                        paging:                 $secondaryPanelPagination.data('paging')
                    },
                    dataType: 'json',
                    success: function(responseData) {
                        if (responseData.code === 0) { // 0 means SUCCESS, > 0 means FAIL
                            var html                = '';
                            var vehicles            = responseData.data.vehicles;
                            var endpage             = responseData.data.endpage;
                            var quickFilters        = responseData.data.quick_filters;
                            var showUnitMarkerLabel = true;
                            if (vehicles.length > 0) {
                                var length = vehicles.length;

                                // create filtered vehicle listing
                                $.each(vehicles, function(key, unit) {
                                    var vehicleListActiveClass = "";

                                    if (drawMarkers === true) {
                                        if (typeof(unit.event.id) != 'undefined' && unit.event.id != '') {
                                            if (vehicles.length != 1) {
                                                vehicleListActiveClass = " active";
                                                showUnitMarkerLabel = false;
                                            }

                                            Map.addMarker(
                                                Vehicle.Map.map,
                                                {
                                                    id: unit.unit_id,
                                                    name: unit.unitname,
                                                    latitude: unit.event.latitude,
                                                    longitude: unit.event.longitude,
                                                    click: function() {
                                                        Map.getVehicleEvent(Vehicle.Map.map, unit.unit_id, unit.event.id);
                                                    }
                                                },
                                                true
                                            );
                                            if (key == (length - 1)) {
                                                Map.updateMarkerBound(Vehicle.Map.map);
                                                Map.updateMapBound(Vehicle.Map.map);
                                            }
                                        }
                                    }

                                    html += '<li id="vehicle-li-'+unit.unit_id+'" data-event-id="'+unit.event.id+'" class="list-group-item clearfix' + vehicleListActiveClass +'">' +
                                            '   <label for="vehicle-li-'+unit.unit_id+'">'+unit.name+'</label>' +
                                            '   <div class="toggle pull-right">' +
                                            '       <span class="glyphicon glyphicon-pencil"></span>' +
                                            '   </div>' +
                                            '   ' +
                                            '</li>'
                                    ;
                                });

                                if ($secondaryPanelPagination.data('paging') == '-') {
                                    var new_start_index = parseInt($secondaryPanelPagination.data('vehicleStartIndex')) - parseInt($('#secondary-panel-pagination a.active').data('value'));
                                    if (new_start_index < 0) {
                                        new_start_index = 0;
                                    }
                                    $secondaryPanelPagination.data('vehicleStartIndex', new_start_index);
                                } else if ($secondaryPanelPagination.data('paging') == '+') {
                                    var new_start_index = parseInt($secondaryPanelPagination.data('vehicleStartIndex')) + parseInt($('#secondary-panel-pagination a.active').data('value'));
                                    $secondaryPanelPagination.data('vehicleStartIndex', new_start_index);
                                } else {
                                    $secondaryPanelPagination.data('vehicleStartIndex', 0);
                                }

                                // update showing text info
                                var start_showing = end_showing = 0;
                                start_showing = parseInt($secondaryPanelPagination.data('vehicleStartIndex')) + 1;
                                end_showing = parseInt($secondaryPanelPagination.data('vehicleStartIndex')) + vehicles.length;
                                var new_showing_text = start_showing + '-' + end_showing;
                                $('#secondary-panel-pagination .showing').text(new_showing_text);
                                $('#secondary-panel-pagination .total').text(responseData.data.total_vehicles_count);

                                if (typeof(endpage) != 'undefined' && parseInt(endpage) == 1) {
                                    $('#secondary-panel-pagination .glyphicon-forward').addClass('hidden');
                                } else {
                                    $('#secondary-panel-pagination .glyphicon-forward').removeClass('hidden');
                                }
                            } else {
                                $('#secondary-panel-pagination .showing').text('0-0');
                                $('#secondary-panel-pagination .total').text('0');
                            }

                            $('.sub-panel-items').html(html);

                            if (Core.Environment.context() == 'vehicle/map') {
                                if (activeUnits != undefined && activeUnits.length > 0) {
                                    Vehicle.Map.paginateActiveVehicles(activeUnits);
                                } else {
                                    if (drawMarkers !== true && vehicles.length != 1) {

                                    	Map.clearMarkers(Vehicle.Map.map);
                                        $('#hide-vehicle-panel').trigger('click', function() {
                                            Map.resetMap(Vehicle.Map.map);
                                        });

                                        $('#quick-actions').find('.icon16').removeClass('active');
                                    } else {
                                        Map.resetMap(Vehicle.Map.map);
                                    }

                                    if (vehicles.length == 1) {
                                       $('.sub-panel-items').find('li').trigger('click');
                                    }
                                }

                                if (updateQuickFilters === true) {
                                    var quickFilterIcons = $('#quick-actions').find('a');
                                    //for each quickFilter, find and update counter
                                    $.each(quickFilters, function(key, value) {
                                        var val = key.toLowerCase().replace(/\_/g, '-');
                                        var newTitle = key.replace(/\_/g, ' ') + ' ('+value+')';

                                        quickFilterIcons.each(function() {
                                            var $self = $(this);
                                            if($self.data('value') == val) {
                                                // assign the quick filter title with new counter
                                                $self.data('title', newTitle);
                                            }
                                        });
                                    });

                                    Core.Tooltip.init();
                                }
                            }
                        } else {
                            $('.sub-panel-items').html('');
                        }
                    },
                    complete: function(){
                    }
                });
            }
        },

        DetailPanel : {


            /**
             *
             * Populates and prepares the Detail Panel after AJAX calls are made for vehicle specific data
             *
             * */
            render: function(unitdata, callBack) {
console.log('-------------------------------------------------- Vehicle.Common.DetailPanel.render');
                if ((unitdata != undefined) && (typeof(unitdata) == 'object') && (! $.isEmptyObject(unitdata))) {

                    if (Core.Environment.context() == 'vehicle/map') {

                        var $mapDiv				           = $('#map-div'),
                            $container                     = $('#detail-panel'),
                            $vehicleLabelContainer         = $container.find('.vehicle-label').eq(0),
                            $vehicleLocationLabelContainer = $container.find('.vehicle-location-label').eq(0),
                            $vehicleSinceEventLabelContainer = $container.find('.vehicle-since-event-label').eq(0),
                            $vehicleInLandmarkLabelContainer = $container.find('.vehicle-in-landmark-label').eq(0)
                        ;

                        callBack = ((callBack != undefined) && (typeof(callBack) == 'function')) ? callBack : undefined;

                        $vehicleLabelContainer.text(Core.StringUtility.ellipsisTruncate(unitdata.unitname, 20));

                        $vehicleLocationLabelContainer.text(((typeof(unitdata.eventdata) == 'object') && (! $.isEmptyObject(unitdata.eventdata)) && (unitdata.eventdata.formatted_address != undefined)) ? unitdata.eventdata.formatted_address : 'Location Not Available');

                        $vehicleSinceEventLabelContainer.text(((typeof(unitdata.eventdata) == 'object') && (! $.isEmptyObject(unitdata.eventdata)) && (unitdata.eventdata.eventname != undefined)) ? unitdata.eventdata.eventname+' ('+unitdata.eventdata.since_eventtime+')' : '');

                        $vehicleInLandmarkLabelContainer.text(((typeof(unitdata.eventdata) == 'object') && (! $.isEmptyObject(unitdata.eventdata)) && (unitdata.eventdata.territoryname != undefined)) ? unitdata.eventdata.territoryname : 'n/a');
                    }

                    if (Core.Environment.context() == 'vehicle/list') {
                        //$container = $('#detail-panel');
                        $container = $('#modal-vehicle-list');
                    }

                    // check if need to redraw quick history table datas
                    if ($container.find('.hook-editable-keys').eq(0).data('vehicle-pk') != unitdata.unit_id) {
                        // add class 'draw_table' to the quick history tab/nav to trigger a datatable redraw on click
                        $('#vehicle-detail-quick-history-tab').addClass('draw_table');
                    } else {
                        // remove class 'draw_table' to the quick history tab/nav: don't redraw on click if same unit clicked
                        $('#vehicle-detail-quick-history-tab').removeClass('draw_table');
                    }

                   // populate vehicle id
                   $container.find('.hook-editable-keys').eq(0).data('vehicle-pk', unitdata.unit_id).data('vehicle-odometer-id', unitdata.odometer_id);

                   // add class 'draw_table' to the verification tab/nav to trigger a datatable redraw on click
                   $('#vehicle-detail-verification-tab').addClass('draw_table');

                    Vehicle.Common.DetailPanel.basic_render(unitdata);

                    /***************
                     *
                     * Tooltips Reinit
                     *
                     ***************/
                    Core.Tooltip.init(); // reinit tooltips - ensures all widths are correct


                    /***************
                     *
                     * Map/Detail Panel Animation (if in vehicle/map context)
                     *
                     ***************/
                    //setTimeout(function(){
                        Vehicle.Common.DetailPanel.open(callBack);
                   // }, 1000);

                }
            },

            basic_render: function(unitdata, callback)
            {

                // var buffer = Array();
                // $.each( unitdata, function( key, value ) {
                //     if(value==''){
                //         buffer.push({key:'No Data'});
                //     } else {
                //         buffer.push({key:value});
                //     }
                //     console.log('basic_render:unitdata:'+key+'="'+value+'"');
                // });
                // $.each( buffer, function( key, value ) {
                //     console.log('basic_render:buffer:'+key+'="'+value+'"');
                // });
                // unitdata = buffer;

               /***************
               *
               * VEHICLE INFO
               *
               ***************/
               var $vehicleStatus         = $('#vehicle-status'),           //
                  $vehicleName           = $('#vehicle-name'),              //
                  $vehicleSerial         = $('#vehicle-serial'),            //
                  $vehicleGroup          = $('#vehicle-group'),             //
                  $vehicleVin            = $('#vehicle-vin'),               //
                  $vehicleMake           = $('#vehicle-make'),              //
                  $vehicleModel          = $('#vehicle-model'),             //
                  $vehicleYear           = $('#vehicle-year'),              //
                  $vehicleColor          = $('#vehicle-color'),             //
                  $vehicleStock          = $('#vehicle-stock'),             //
                  $vehicleLicPlate       = $('#vehicle-license-plate'),     //
                  $vehicleLoanId         = $('#vehicle-loan-id'),           //
                  $vehicleInstallDate    = $('#vehicle-install-date'),      //
                  $vehicleInstaller      = $('#vehicle-installer'),         //
                  $vehicleInstallMileage = $('#vehicle-install-mileage'),   //
                  $vehicleDrivenMiles    = $('#vehicle-driven-miles'),      //
                  $vehicleTotalMileage   = $('#vehicle-total-mileage'),      //
                  $vehicleModalDevice    = $('#vehicle-map-html-modal-device'),      //
                  $vehicleModalDetail    = $('#vehicle-map-html-modal-details'),      //
                  $vehicleModalLandmark  = $('#vehicle-map-html-modal-landmark')      //
               ;


               // editable
               $vehicleStatus.val(unitdata.unitstatus_id);
               Core.Editable.setValue($vehicleName, unitdata.unitname);
               $vehicleGroup.val(unitdata.unitgroup_id);
               Core.Editable.setValue($vehicleVin, unitdata.vin);
               Core.Editable.setValue($vehicleMake, unitdata.make);
               Core.Editable.setValue($vehicleModel, unitdata.model);
               Core.Editable.setValue($vehicleYear, unitdata.year);
               Core.Editable.setValue($vehicleColor, unitdata.color);
               Core.Editable.setValue($vehicleStock, unitdata.stock);  // cannot find 'stock' column in DB
               Core.Editable.setValue($vehicleLicPlate, unitdata.licenseplatenumber);
               Core.Editable.setValue($vehicleLoanId, unitdata.loannumber);
               Core.Editable.setValue($vehicleInstallDate, unitdata.formatted_installdate);
               Core.Editable.setValue($vehicleInstaller, unitdata.installer);
               Core.Editable.setValue($vehicleInstallMileage, unitdata.installmileage);

               // not editable
               $vehicleModalDevice.html(Core.StringUtility.formatStaticFormValue(unitdata.unitname));
$.each(unitdata.eventdata, function( k1, v1 ) {
    console.log('unitdata:'+k1+':'+v1);
});
               var d = new Date(unitdata.eventdata.servertime);
               $vehicleModalDetail.html(Core.StringUtility.formatStaticFormValue(unitdata.eventdata.stoppedormoving+' ('+unitdata.eventdata.moving+' ago)'));
               if(unitdata.eventdata.territoryname){
                 // unitdata.eventdata.territoryname = 'Landmark: ' + unitdata.eventdata.territoryname;
                 unitdata.eventdata.territoryname = unitdata.eventdata.territoryname;
                 $('#detail-landmark-dashboard').show();
               } else {
                 $('#detail-landmark-dashboard').hide();
               }
               $vehicleModalLandmark.html(Core.StringUtility.formatStaticFormValue(unitdata.eventdata.territoryname));
               $vehicleSerial.html(Core.StringUtility.formatStaticFormValue(unitdata.serialnumber));
               $vehicleDrivenMiles.html(Core.StringUtility.formatStaticFormValue(unitdata.drivenmileage));
               $vehicleTotalMileage.html(Core.StringUtility.formatStaticFormValue(unitdata.totalmileage));

               /***************
               *
               * CUSTOMER INFO
               *
               ***************/
               var $customerFirstName   = $('#customer-first-name'),
                  $customerLastName    = $('#customer-last-name'),
                  $customerAddress     = $('#customer-address'),
                  $customerState       = $('#customer-state'),
                  $customerCity        = $('#customer-city'),
                  $customerZip         = $('#customer-zipcode'),
                  $customerMobilePhone = $('#customer-mobile-phone'),
                  $customerHomePhone   = $('#customer-home-phone'),
                  $customerEmail       = $('#customer-email')
               ;

               Core.Editable.setValue($customerFirstName, unitdata.firstname);
               Core.Editable.setValue($customerLastName, unitdata.lastname);
               Core.Editable.setValue($customerAddress, unitdata.streetaddress);
               Core.Editable.setValue($customerCity, unitdata.city);
               Core.Editable.setValue($customerState, unitdata.state);
               Core.Editable.setValue($customerZip, unitdata.zipcode);
               Core.Editable.setValue($customerMobilePhone, unitdata.formatted_cell_phone);
               Core.Editable.setValue($customerHomePhone, unitdata.formatted_home_phone);
               Core.Editable.setValue($customerEmail, unitdata.email);

               /***************
               *
               * Device INFO
               *
               ***************/
               var $deviceSerial           = $('#device-serial'),
                  $deviceStatus           = $('#device-status'),
                  $devicePlan             = $('#device-plan'),
                  $devicePurchaseDate     = $('#device-purchase-date'),
                  $deviceActivationDate   = $('#device-activation-date'),
                  $deviceRenewalDate      = $('#device-renewal-date'),
                  $deviceLastRenewed      = $('#device-last-renewed'),
                  $deviceDeactivationDate = $('#device-deactivation-date')
               ;

               $deviceSerial.html(Core.StringUtility.formatStaticFormValue(unitdata.serialnumber));
               $deviceStatus.html(Core.StringUtility.formatStaticFormValue(unitdata.unitstatusname));
               $devicePlan.html(Core.StringUtility.formatStaticFormValue(unitdata.subscription));
               $devicePurchaseDate.html(Core.StringUtility.formatStaticFormValue(unitdata.formatted_purchasedate));
               $deviceRenewalDate.html(Core.StringUtility.formatStaticFormValue(unitdata.formatted_expirationdate));
               $deviceLastRenewed.html(Core.StringUtility.formatStaticFormValue(unitdata.formatted_lastrenewaldate));
               $deviceActivationDate.html(Core.StringUtility.formatStaticFormValue(unitdata.formatted_activatedate));
               $deviceDeactivationDate.html(Core.StringUtility.formatStaticFormValue(unitdata.formatted_deactivatedate));

               Core.Editable.setValue($deviceSerial, unitdata.serialnumber);
               Core.Editable.setValue($deviceStatus, unitdata.unitstatusname);
               Core.Editable.setValue($devicePlan, unitdata.subscription);
               Core.Editable.setValue($devicePurchaseDate, unitdata.formatted_purchasedate);
               Core.Editable.setValue($deviceActivationDate, unitdata.formatted_activatedate);
               Core.Editable.setValue($deviceRenewalDate, unitdata.formatted_expirationdate);
               Core.Editable.setValue($deviceLastRenewed, unitdata.formatted_lastrenewaldate);
               Core.Editable.setValue($deviceActivationDate, unitdata.formatted_activatedate);
               Core.Editable.setValue($deviceDeactivationDate, unitdata.formatted_deactivatedate);

               $('#print-button').prop('href', '/vehicle/print/'+unitdata.unit_id);

console.log('basic_render:callback"');
               callback && typeof callback === 'function' && callback()
            },

            open: function(eid,nocnt) {

                if(eid){

                    if(!(nocnt)){
                        countOpen = 5;
                    } else {
                        countOpen--;
                    }

                    setTimeout("if(countOpen>1){Vehicle.Common.DetailPanel.open('"+eid+"',1);}else if(countOpen>0){Vehicle.Common.DetailPanel.open2('"+eid+"');}",100);

                }

            },

            open2: function(eid) {

                countOpen=-1;

                /**
                 *
                 * Open up the detail panel
                 *
                 * */
console.log('=============================================== '+Core.Environment.context()+', currentUnitId='+currentUnitId+', currentUnitIdHidePanel='+currentUnitIdHidePanel);

                if ( Core.Environment.context() == 'vehicle/map') {

                    if ( (currentUnitId) && (currentUnitId == currentUnitIdHidePanel) ) {

                        Core.Map.Refresh('Vehicle.Map.map','',1);

                    } else {

                        var newHeight = 0;

                        $('#detail-panel').show();
                        $('#detail-panel').addClass('open');

                        $('#detail-panel').css({ height: 'auto' });
                        $('#detail-panel').find('.panel-bottomless').css({ height: 'auto' });
                        $('#detail-panel').find('.panel-bottomless > .block').css({ height: 'auto' });
                        $('#detail-panel').find('.panel-bottomless > .block > .block-title').css({ height: 'auto' });
                        $('#detail-panel').find('.panel-bottomless > .block > .tab-content').css({ height: 'auto' });

                        newHeight=newHeight+$('#detail-panel').find('.panel-bottomless > .block > .block-title').outerHeight();
// console.log(' !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! Vehicle.Common.DetailPanel.open:'+newHeight);

                        switch($('#vehicleTabs').find('li.active').find('a').attr('id')){
                            case                                'vehicle-detail-info-tab' : 
                            case                            'vehicle-detail-commands-tab' : $('#detail-panel').find('.panel-bottomless > .block > .tab-content > div.active > div').each(function() {
                                                                                                newHeight=newHeight+$(this).outerHeight();
                                                                                            });
                                                                                            break;
                            case                           'vehicle-detail-quick-history' : newHeight=newHeight+51;
                                                                                            $('#detail-panel').find('.panel-bottomless > .block > .tab-content > div.active > div.table-responsive > div > div.tab-content > div.active').each(function() {
                                                                                                newHeight=newHeight+$(this).outerHeight();
                                                                                            });
                                                                                            newHeight=newHeight+10;
                                                                                            break;
                            case                        'vehicle-detail-verification-tab' : $('#detail-panel').find('.panel-bottomless > .block > .tab-content > div.active > div').each(function() {
                                                                                                newHeight=newHeight+$(this).outerHeight();
                                                                                            });
                                                                                            newHeight=newHeight+10;
                                                                                            break;
                        }
// console.log('+++++++++++++++++++++++++++++++++++++++++++++++++++ Vehicle.Common.DetailPanel.open:'+newHeight);

                        newHeight=newHeight+20;
console.log(' !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! Vehicle.Common.DetailPanel.open:FINAL:'+newHeight);
                        $('#detail-panel').css({ height: newHeight+'px' });
                        newHeight=newHeight-2;
                        $('#detail-panel').find('.panel-bottomless').css({ height: newHeight+'px' });
                        newHeight=newHeight-2;
                        $('#detail-panel').find('.panel-bottomless > .block').css({ height: newHeight+'px' });
                                                                                            
                        Core.Viewport.adjustLayout();

                    }

                } else if (Core.Environment.context() == 'vehicle/list') {
                    var header = 400 ;
                    var h = 400;
                    switch(eid){
                        case        'vehicle-detail-commands-tab' : h = 195 ;
                                                                    break;
                        case                  'tab-quick-history' : h = $('#modal-edit-vehicle-list').find('.active').find('.panel-report-scroll').height();
                                                                    h = h+124;
                                                                    $('#modal-edit-vehicle-list').find('div.tab-pane active').find('div.report-master').height(h);
                                                                    h = h+85;
                                                                    break;
                        case                   'tab-verification' : h = $('#modal-edit-vehicle-list').find('.active').find('.panel-report-scroll').height();
                                                                    h = h+74;
                                                                    $('#modal-edit-vehicle-list').find('div.tab-pane active').find('div.report-master').height(h);
                                                                    h = h+85;
                                                                    break;
                                                          default : h = 245 ;
                    }
                    $('#modal-edit-vehicle-list').find('div.modal-pronounce').height(header+h);
                    h=h+8;
                    $('#modal-edit-vehicle-list').find('div.modal-content').height(header+h);
                    $('#modal-edit-vehicle-list').closest('div.modal-dialog').css({ backgroundColor: '#999999' ,display: 'block' });
console.log('<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< vehicle/list:header:'+header+':h:'+h+':eid:'+eid);
                }
            },

            reset: function() {

                /**
                 *
                 * Clear all inline-editable errors and reset the detail panel
                 * to the default tab and section (Info Tab - Vehicle Information)
                 *
                 * */

                var $container = {},
                    $containerNav = {},
                    $containerSection = {}
                ;

                if (Core.Environment.context() == 'vehicle/map') {
                    $container        = $('#detail-panel');
                    $containerNav     = $('#vehicle-detail-info-tab');
                    $containerSection = $('#vehicle-info-button');
                }

                if (Core.Environment.context() == 'vehicle/list') {
                    $container        = $('#modal-vehicle-list');
                    $containerNav     = $('#vehicle-detail-info-tab');
                    $containerSection = $('#vehicle-info-button');
                }

                // trigger click on all inline-editable cancel buttons when detail panel closes
                $container.find('button').filter('.editable-cancel').trigger('click');

                // reset tabs to defaults (Info Tab - Vehicle Information)
                $containerNav.trigger('click');
                $containerSection.trigger('click');
            },

            initClose: function() {
console.log('initClose');
                /**
                 *
                 * When the Detail Panel's Close (x) Icon is clicked
                 *
                 * */
                var $mapDiv      = $('#map-div'),
                    $detailPanel = $('#detail-panel')
                ;

                $('#hide-vehicle-panel').click(function(event, callBack) {
                    $mapDiv.animate({
                        //'height': '800px'
                        'height': parseInt(Core.Viewport.contentHeight-35)+'px'
                    }, 300, function() {
                        if (Core.Environment.context() == 'vehicle/map') {
                            Map.resize(Vehicle.Map.map);
                            if ($detailPanel.is('.open')) {
                                $detailPanel.removeClass('open');
                            }

                            Vehicle.Common.DetailPanel.reset();
                        }

                        if ((callBack != undefined) && (typeof(callBack) == 'function')) {
                            callBack();
                        }
                    });

                    $detailPanel.slideUp(300);
                });
            },

            initInfoTab: function() {

//                 $('#vehicle-detail-info-tab').on('click', function() {
//                     // clear temp markers
//                     if ($('#detail-panel').is('.open')) {
// console.log('#vehicle-detail-info-tab:click:Vehicle.Common.DetailPanel.clearQuickHistoryMarkers()');
//                         Vehicle.Common.DetailPanel.clearQuickHistoryMarkers();
//                         window.setTimeout("Vehicle.Common.DetailPanel.opened('"+$(this).attr('id')+"')",1);
//                     }
//                 });

                /**
                 *
                 * When Vehicle Name Changes
                 *
                 * listens for the Core.FormElementChanged event triggered by Core.Editable
                 *
                 * */
                var $vehicleName               = $('#vehicle-name'),
                    $detailPanel               = $('#detail-panel'),
                    $vehicleGroupSelect        = $('#vehicle-group'),
                    $vehicleGroupFilterSelect  = $('#sidebar-vehicle-single'),
                    $vehicleGroupFilterOptions = $vehicleGroupFilterSelect.find('option'),
                    $modalDialog               = $('#modal-vehicle-list'),
                    $vehicleOdometer           = $('#vehicle-install-mileage'),
                    $vinDecoder                = $('#vin-decoder-button')
                ;

                $vehicleName.on('Core.FormElementChanged', function(event, extraParams) {
                    extraParams = extraParams || {
                        value: false,
                        pk:    false
                    };

                    // require value and pk
                    if (! $.isEmptyObject(extraParams.value) && !$.isEmptyObject(extraParams.pk)) {
                        if (Core.Environment.context() == 'vehicle/map') {

                            var vehicleLabelText = Core.StringUtility.ellipsisTruncate(extraParams.value, 20);

                            // change title in detail panel
                            var $vehicleLabel = $detailPanel.find('.vehicle-label');
                            $vehicleLabel.hide()
                                         .text(vehicleLabelText)
                                         .fadeIn(300)
                            ;

                            // change label in secondary panel
                            $('label[for="vehicle-li-'+extraParams.pk.vehiclePk+'"]').text(vehicleLabelText);

                            // change label on map info window
                            if ($('#info_window_div').length > 0) {
                                $('#info_window_unit_name').html('<b>'+extraParams.value+'</b>');
                            }
                        }

                        if (Core.Environment.context() == 'vehicle/list') {
                            // update title in modal
                            var $vehicleLabel = $modalDialog.find('.modal-title').text(extraParams.value);
                            // update title in table row
                            $('#vehicle-tr-'+extraParams.pk.vehiclePk).find('td a').text(extraParams.value);
                        }

                    }
                });

                $vehicleGroupSelect.on('Core.FormElementChanged', function(event, extraParams) {
                    if (($vehicleGroupFilterOptions.filter(':selected').val() !== '') && ($vehicleGroupFilterOptions.filter(':selected').val() !== 'ALL')) {
                        //  trigger refresh on vehicle group
                        $vehicleGroupFilterSelect.trigger('change');
                    }
                });

                $vehicleOdometer.on('Core.FormElementChanged', function(event, extraParams) {
                    if (Core.Environment.context() == 'vehicle/map' || Core.Environment.context() == 'vehicle/list') {
                        if (! $.isEmptyObject(extraParams) && extraParams.response != undefined) {
                            if (extraParams.response.data != undefined && extraParams.response.data.unitodometer_id != undefined) {
                                $detailPanel.find('.hook-editable-keys').eq(0).data('vehicleOdometerId', extraParams.response.data.unitodometer_id);
                            }

                            // if the unit odometer was successfully updated
                            if (extraParams.response.code == 0) {
                                // update the total mileage in the vehicle info
                                var $installMileage     = $('#vehicle-install-mileage'),
                                    $drivenMiles        = $('#vehicle-driven-miles'),
                                    $totalMileage       = $('#vehicle-total-mileage'),
                                    ioValue             = extraParams.response.data.value,
                                    dmValue             = $drivenMiles.text()
                                ;

                                // convert install mileage and driven mileage to numbers for total mileage calculation
                                ioValue = parseInt(ioValue,10);
                                dmValue = (dmValue === 'No Data') ? 0 : parseInt(dmValue,10);

                                // update the install mileage (with any leading zeroes stripped out)
                                setTimeout(function() {
                                    Core.Editable.setValue($installMileage, (ioValue+''));
                                }, 100);

                                $totalMileage.html(Core.StringUtility.formatStaticFormValue((ioValue+dmValue)+''));
                            }
                        }
                    }
                });

                $vinDecoder.click(function() {
                    var vin = $('#vehicle-vin').text() || '',
                        $container = (Core.Environment.context() == 'vehicle/map') ? $('#detail-panel') : $('#modal-vehicle-list'),
                        unit_id = $container.find('.hook-editable-keys').eq(0).data('vehicle-pk') || 0
                    ;

                    if (vin != '' && unit_id != 0) {

console.log('$vinDecoder.click:ajax');

                        $.ajax({
                            url: '/ajax/vehicle/decodeVin',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                unit_id: unit_id,
                                vin: vin
                            },
                            success: function(responseData) {
                                if (responseData.code === 0) {
                                    if (! $.isEmptyObject(responseData.data)) {
                                        // update vheicle year, make, and model
                                        if (responseData.data.make != undefined) {
                                            Core.Editable.setValue($('#vehicle-make'), responseData.data.make);
                                        }

                                        if (responseData.data.model != undefined) {
                                            Core.Editable.setValue($('#vehicle-model'), responseData.data.model);
                                        }

                                        if (responseData.data.year != undefined) {
                                            Core.Editable.setValue($('#vehicle-year'), responseData.data.year);
                                        }
                                    }
                                } else {
                                    if ($.isEmptyObject(responseData.validation_error) === false) {

                                    }
                                }

                                if ($.isEmptyObject(responseData.message) === false) {
                                    Core.SystemMessage.show(responseData.message, responseData.code);
                                }
                            }
                        });
                    } else {
                        Core.SystemMessage.show('Please enter a VIN to be decoded', 1);
                    }
                });

            },

            getCurrentLocationCall: function() {

                var unitId = '',
                    $detailPanel = $('#detail-panel'),
                    $vehicleLocationLabelContainer = $detailPanel.find('.vehicle-location-label').eq(0)
                ;

                if (Core.Environment.context() == 'vehicle/map') {
                    var $selectedItem = $('.sub-panel-items').find('li').filter('.active');

                    if ($selectedItem.length == 1) {
                        unitId = $selectedItem.attr('id').split('-')[2];
                    }

                }

                if (Core.Environment.context() == 'vehicle/list') {
                    unitId = $detailPanel.find('.hook-editable-keys').eq(0).data('vehiclePk');
                }

                if (unitId != '') {

                    $.ajax({
                        url: '/ajax/vehicle/getLastReportedEvent',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            unit_id: unitId
                        },
                        success: function(responseData) {
console.log(responseData);
                            if (responseData.code === 0) {
                                if (responseData.data[0]) {
                                    var unitdata = responseData.data[0],
                                        eventdata = unitdata.eventdata
                                    ;

                                    if (! $.isEmptyObject(eventdata)) {
                                        if (Core.Environment.context() == 'vehicle/map' || Core.Environment.context() == 'vehicle/list') {
                                            var markerOptions = {
                                                latitude: eventdata.latitude,
                                                longitude: eventdata.longitude,
                                                click: function() {
                                                    Map.getVehicleEvent(Vehicle.Map.map, unitdata.unit_id, eventdata.id);
                                                }
                                            };

                                            Map.updateMarker(Vehicle.Map.map, unitId, markerOptions);
console.log('painting map-bubble');
                                            Map.openInfoWindow(Vehicle.Map.map, 'locate', eventdata.latitude, eventdata.longitude, eventdata, responseData.data.moving, responseData.data.battery, responseData.data.signal, responseData.data.satellites, unitdata.territoryname);

                                            $vehicleLocationLabelContainer.text(eventdata.formatted_address);

                                            if (Core.Environment.context() == 'vehicle/map') {
                                                $selectedItem.data('event-id', eventdata.id)
                                                             .data('latitude', eventdata.latitude)
                                                             .data('longitude', eventdata.longitude);
                                            }
                                        }
                                    } else {
                                        if (Core.Environment.context() == 'vehicle/map') {
                                            $selectedItem.data('event-id', null);
                                        }
                                    }
                                }

                            } else {


                                if ($.isEmptyObject(responseData.validaton_error) === false) {
                                    //  display validation errors
                                }
                            }

                            if ($.isEmptyObject(responseData.message) === false) {
                                Core.SystemMessage.show(responseData.message, responseData.code);
                            }
                        }
                    });
                }

            },

            initCommandsTab: function() {

                var _timeouts = {}
                    ,_getCurrentLocation = function() {

                        $('#info_window_div').closest('div').width('301px');
                        $('#info_window_div').width('301px');
                        $('#info_window_div').html('<div class="current-location text-coal text-10"><h3>Get Current Location</h3><p>Locating...<div class="progress progress-striped active" style="height:10px;"><div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="height:10px;width: 100%"></div></div></div>');
                        setTimeout("Vehicle.Common.DetailPanel.getCurrentLocationCall();",8000);


                    };

//                 $('#vehicle-detail-commands-tab').on('click', function() {
// console.log('#vehicle-detail-commands-tab:click');
//                     if ($('#detail-panel').is('.open')) {
//                         Vehicle.Common.DetailPanel.clearQuickHistoryMarkers();
//                         window.setTimeout("Vehicle.Common.DetailPanel.open('"+$(this).attr('id')+"')",1);
//                     }
//                 });

                /**
                 *
                 * Clicking Command Buttons: starter enable/disable reminder on/off, locate on demand
                 *
                 * */
                $('body').on('click', '#starter-enable-button, #starter-disable-button, #reminder-on-button, #reminder-off-button', function() {
console.log('body:click:#starter-enable-button, #starter-disable-button, #reminder-on-button, #reminder-off-button');
                    var id = $(this).attr('id');
                    var topTop = $(this).offset().top;
                    switch (id) {
                        case   'starter-enable-button': id='#popover-starter-enable-confirm';                                                                    
                                                        break;
                        case  'starter-disable-button': id='#popover-starter-disable-confirm';
                                                        break;
                        case      'reminder-on-button': id='#popover-reminder-enable-confirm';                                                                    
                                                        break;
                        case     'reminder-off-button': id='#popover-reminder-disable-confirm';                                                                    
                                                        break;
                    }

console.log(">>>>>>>>>>>>>>>>>>>>> id:"+id);
                    if($(id).closest('.popover').is(':visible')){
                        // var newTop = $(window).height() - $(id).closest('.popover').height() - topTop;
                        var newTop = topTop - 110;
                        $(id).closest('.popover').css({ top: newTop });
console.log(">>>>>>>>>>>>>>>>>>>>> id:"+id+":newTop:"+newTop+' = '+$(window).height()+' - '+$(id).closest('.popover').height()+' - '+topTop);
                        $(id).closest('.popover-content').addClass('command-popup-content-width');
                        $(id).closest('.popover').addClass('command-popup-width');
                        $(id).closest('.popover').find('.arrow').addClass('command-arrow');
                    }

                });

                /**
                 *
                 * Clicking Command Buttons: starter enable/disable reminder on/off, locate on demand
                 *
                 * */
                $('body').on('click', '#popover-starter-enable-close, #popover-starter-disable-close, #popover-reminder-enable-close, #popover-reminder-disable-close', function() {
console.log('body:click:#popover-starter-enable-close, #popover-starter-disable-close, #popover-reminder-enable-close, #popover-reminder-disable-close');
                    var id = $(this).attr('id');
                    switch (id) {
                        case   'popover-starter-enable-close': $('#popover-starter-enable-retry').trigger('click');                                                                    
                                                                break;
                        case  'popover-starter-disable-close': $('#popover-starter-disable-retry').trigger('click');                                                                    
                                                                break;
                        case  'popover-reminder-enable-close': $('#popover-reminder-enable-retry').trigger('click');                                                                    
                                                                break;
                        case 'popover-reminder-disable-close': $('#popover-reminder-disable-retry').trigger('click');                                                                    
                                                                break;
                    }

                    if($(this).closest('.popover').is(':visible')){
                        // var newTop = $(window).height() - $(this).closest('.popover').height() - 40;
                        // $(this).closest('.popover').css({ top: newTop });
// console.log(">>>>>>>>>>>>>>>>>>>>> newTop:"+newTop+' = '+$(window).height()+' - '+$(this).closest('.popover').height()+' - 40');
                        $(this).closest('.popover-content').addClass('command-popup-content-width');
                        $(this).closest('.popover').addClass('command-popup-width');
                    }

                });

                /**
                 *
                 * Clicking Command Buttons: starter enable/disable reminder on/off, locate on demand
                 *
                 * */
                $('body').on('click', '#popover-starter-enable-confirm, #popover-starter-disable-confirm, #popover-reminder-enable-confirm, #popover-reminder-disable-confirm, #popover-locate-on-demand-confirm, #locate-on-demand-button-confirm', function() {
console.log('body:click:#popover-starter-enable-confirm, #popover-starter-disable-confirm, #popover-reminder-enable-confirm, #popover-reminder-disable-confirm, #locate-on-demand-button-confirm');

                    var id = $(this).attr('id')
                        ,action = ''
                        ,unitId = ''
                        ,unitName = ''
                        ,responseElSelector
                        ,responseSpanId
                        ,responseSpanText
                        ,progressBarSnippet = '<div class="progress progress-striped active" style="height:10px;"><div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="height:10px;width: 100%"></div></div>'
                        ,topTop=70;

                    topTop = $(this).offset().top - 200;

                    switch (id) {
                        case 'locate-on-demand-button-confirm' :    $('#popover-locate-on-demand-retry').trigger('click');
                                                                    $('#popover-locate-on-demand-confirm').trigger('click');
                                                                    $('#popover-locate-on-demand-retry').show();
                                                                    break;
                        case 'popover-locate-on-demand-confirm':    action = 'locate_on_demand';
                                                                    responseElSelector = '#locate-on-demand-response';
                                                                    responseSpanId = '#div-command-locate-span';
                                                                    // responseSpanText = 'Status: Located';
                                                                    commandBool = '' ;
                                                                    $('#popover-locate-on-demand-retry').show();
                                                                    break;
                        case   'popover-starter-enable-confirm':    action = 'starter_enable';
                                                                    responseElSelector = '#starter-enable-response';
                                                                    responseSpanId = '#div-command-starter-span';
                                                                    // responseSpanText = 'Status: Enabled';
                                                                    commandBool = '' ;
                                                                    $('#popover-starter-enable-retry').show();
                                                                    break;
                        case  'popover-starter-disable-confirm':    action = 'starter_disable';
                                                                    responseElSelector = '#starter-disable-response';
                                                                    responseSpanId = '#div-command-starter-span';
                                                                    // responseSpanText = 'Status: Disabled';
                                                                    commandBool = '' ;
                                                                    $('#popover-starter-disable-retry').show();
                                                                    break;
                        case  'popover-reminder-enable-confirm':    action = 'reminder_on';
                                                                    responseElSelector = '#reminder-enable-response';
                                                                    responseSpanId = '#div-command-reminder-span';
                                                                    // responseSpanText = 'Status: On';
                                                                    commandBool = '' ;
                                                                    $('#popover-reminder-enable-retry').show();
                                                                    break;
                        case 'popover-reminder-disable-confirm':    action = 'reminder_off';
                                                                    responseElSelector = '#reminder-disable-response';
                                                                    responseSpanId = '#div-command-reminder-span';
                                                                    // responseSpanText = 'Status: Off';
                                                                    commandBool = '' ;
                                                                    $('#popover-reminder-disable-retry').show();
                                                                    break;
                        default:
                            break;
                    }

                    if($(this).closest('.popover').is(':visible')){
                        $(this).closest('.popover-content').addClass('command-popup-content-width');
                        $(this).closest('.popover').addClass('command-popup-width');
                        $(id).closest('.popover').find('.arrow').addClass('command-arrow');
                    }

                    if (_timeouts[action]) clearTimeout(_timeouts[action]);

                    if (Core.Environment.context() == 'vehicle/map') {
                        var $selectedItem = $('.sub-panel-items').find('li').filter('.active');

                        if ($selectedItem.length == 1) {
                            unitId = $selectedItem.attr('id').split('-')[2];
                            unitName = $selectedItem.find('a').text();
                        }
                    }

                    if (Core.Environment.context() == 'vehicle/list') {
                        unitId = currentUnitId; // global var set in Core.Js
                        // topTop = 200;
                    }

                    if (action != '' && unitId != '') {

console.log('+++++++++++++++++++++++++++ commandBoolReset to '+unitId);
                        commandBool = unitId ;

                        $(responseElSelector).html('Sending command...'+progressBarSnippet)

console.log('ajax:/ajax/vehicle/sendCommand');

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

// console.log('............................. commandBool:'+commandBool+':unitId:'+unitId+':sendCommand:'+responseData.code+':'+responseData.message);

                                    if (responseData.code === 0) {

                                        timeoutFn = function(responseData){

                                            count++

                                            if ((commandBool!=unitId)||(count>limit)){
                                                
                                                if(commandBool==''){
                                                    $(responseElSelector).closest('.panel-collapse').find('btn-default').trigger('click');
                                                } else {
                                                    $(responseElSelector).html('Timed out waiting for response.');
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

                                                            $(responseElSelector).html(responseData.message);
console.log(responseData);
                                                            // $(responseSpanId).text(responseSpanText);

                                                            switch (id) {
                                                                case 'locate-on-demand-button-confirm' :    
                                                                case 'popover-locate-on-demand-confirm':    $('#popover-locate-on-demand-retry').hide();
                                                                                                            break;
                                                                case   'popover-starter-enable-confirm':    $('#popover-starter-enable-retry').hide();
                                                                                                            break;
                                                                case  'popover-starter-disable-confirm':    $('#popover-starter-disable-retry').hide();
                                                                                                            break;
                                                                case  'popover-reminder-enable-confirm':    $('#popover-reminder-enable-retry').hide();
                                                                                                            break;
                                                                case 'popover-reminder-disable-confirm':    $('#popover-reminder-disable-retry').hide();
                                                                                                            break;
                                                            }

                                                            if( (responseData.event) ){

                                                                var event = responseData.event;
                                                                if((event.formatted_address=='')||(event.formatted_address=='undefined')){
                                                                    event.formatted_address='Unknown Address';
                                                                }

                                                                event.unitname = unitName;

                                                                switch (id) {
                                                                    case 'locate-on-demand-button-confirm' :    
                                                                    case 'popover-locate-on-demand-confirm':    event.eventname = 'Locate';
                                                                                                                break;
                                                                    case   'popover-starter-enable-confirm':    event.eventname = 'Starter Enable';
                                                                                                                break;
                                                                    case  'popover-starter-disable-confirm':    event.eventname = 'Starter Disable';
                                                                                                                break;
                                                                    case  'popover-reminder-enable-confirm':    event.eventname = 'Reminder On';

                                                                                                                break;
                                                                    case 'popover-reminder-disable-confirm':    event.eventname = 'Reminder Off';
                                                                                                                $('#popover-reminder-disable-retry').hide();
                                                                                                                alert('hw');
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
                                                                    true
                                                                );
                                                                if(event.eventname == 'Locate'){
                                                                    Map.openInfoWindow(Vehicle.Map.map,'locate', event.latitude, event.longitude, event , responseData.moving, responseData.duration)
                                                                } else {
// console.log('re-painting map-bubble');
                                                                    Map.openInfoWindow(Vehicle.Map.map, 'unit', event.latitude, event.longitude, event, responseData.moving, responseData.battery, responseData.signal, responseData.satellites, unitdata.territoryname);
                                                                    // Map.openInfoWindow(Vehicle.Map.map, 'unit', event.latitude, event.longitude, event, responseData.moving, responseData.duration)
                                                                }
                                                            } else {
                                                                $('#refresh-map-markers').trigger('click');
                                                                // _getCurrentLocation();
                                                            }
                                                        } else if(commandBool==unitId) {
// console.log('............................. commandBool:'+commandBool+':unitId:'+unitId+':sendCommand:'+responseData.code+':'+responseData.message);
                                                            $(responseElSelector).html('Waiting for Response...'+progressBarSnippet)
                                                            _timeouts[action] = setTimeout(timeoutFn, interval*1000);
                                                        }
                                                        if(responseData.metrics){
                                                            setTimeout("Core.Ajax('','','','metrics')",1);
                                                        }
                                                    },
                                                    error: function() {
                                                        if(count<limit){
                                                            count--;
                                                            _timeouts[action] = setTimeout(timeoutFn, 1);
                                                        } else if(count>=limit){
                                                            $(responseElSelector).html('Timed out waiting for response.');
                                                        } else {
                                                            $(responseElSelector).html('Ajax Call Error');
                                                        }
                                                    }
                                               });

                                            }
                                        }

                                        if (_timeouts[action]) clearTimeout(_timeouts[action]);
                                        $(responseElSelector).html(responseData.message+'. Waiting for response...'+progressBarSnippet)
                                        _timeouts[action] = setTimeout(timeoutFn, interval*1000);

                                    } else {
                                        $(responseElSelector).html(responseData.validation_error || responseData.message || 'unknown error')
                                    }

                                };
                            
                            }(id, unitId, action, responseElSelector)
                        })
                    }

                });

                $('body').on('click', '#popover-locate-on-demand-cancel, #popover-reminder-enable-retry, #popover-reminder-disable-retry, #popover-starter-enable-retry, #popover-starter-disable-retry, #popover-locate-on-demand-retry', function() {
console.log('body:click:#popover-locate-on-demand-cancel, #popover-starter-enable-retry, #popover-starter-disable-retry, #popover-locate-on-demand-retry');
                    var id = $(this).attr('id')
                        ,action
                    switch (id) {
                        case 'popover-locate-on-demand-retry':
                            action = 'locate_on_demand';
                            break;
                        case 'popover-starter-enable-retry':
                            action = 'starter_enable';
                            break;
                        case 'popover-starter-disable-retry':
                            action = 'starter_disable';
                            break;
                        case 'popover-reminder-enable-retry':
                            action = 'reminder_enable';
                            break;
                        case 'popover-reminder-disable-retry':
                            action = 'reminder_disable';
                            break;
                    }
console.log('+++++++++++++++++++++++++++ commandBoolReset');
                    commandBool='';
                    if (_timeouts[action]) clearTimeout(_timeouts[action]);
                });

                /**
                 *
                 * Click Command Get Current Location
                 *
                 * */
                $('#get-current-location-button').click(_getCurrentLocation);

            },

            initQuickHistoryTab: function() {

                var $historyFilterAllevents      = $('#history-filter-all-events'),
                    $historyFilterRecentstops    = $('#history-filter-recent-stops'),
                    $historyFilterFrequentstops  = $('#history-filter-frequent-stops'),
                    $selectHistoryDayFilter      = $('#select-history-day-filter'),
                    $selectHistoryDurationFilter = $('#select-history-duration-filter'),
                    $filterDurationContainer     = $('#filter-duration-container'),
                    $body                        = $('body')
                ;

                /**
                 * Navigation Quick History Tab - makes ajax call to get quick history for the selected vehicle
                 *
                 */
//                 $('#vehicle-detail-quick-history-tab').click(function() {
// console.log('#vehicle-detail-quick-history-tab:click');
//                     if ($(this).is('.draw_table')) {
//                         // set default day and duration filter values
//                         Vehicle.Common.DetailPanel.resetQuickHistoryDataTable();
//                         //$selectHistoryDayFilter.val("today").text("Today");
//                         //$selectHistoryDurationFilter.val("0-mins").text("All");

//                         // draw tables with default values
//                         Vehicle.Common.DataTables.quickHistoryAllDataTable.fnDraw();
//                         Vehicle.Common.DataTables.quickHistoryRecentDataTable.fnDraw();
//                         Vehicle.Common.DataTables.quickHistoryFrequentDataTable.fnDraw();
//                         $(this).removeClass('draw_table');
//                     }
//                     if ($('#detail-panel').is('.open')) {
//                         window.setTimeout("Vehicle.Common.DetailPanel.open('"+$(this).attr('id')+"')",1);
//                     }
//                 });

                /**
                 * When All Event is clicked, restore day and duration filter setting
                 */
                $historyFilterAllevents.on('click', function() {
console.log('$historyFilterAllevents:click');
                    var eventType = eventType = $('.event-type-button').filter('.current').val()
                        ,qhv = Vehicle.Common.DetailPanel.quickHistoryValues;

                    if ($(this).val() !== eventType) {                  // if this event type is not the currently active event,
                        Vehicle.Common.DetailPanel.clearQuickHistoryMarkers();
                    }

                    $selectHistoryDayFilter.val(qhv.dateStateValue).text(qhv.dateStateLabel);
                    $selectHistoryDurationFilter.val(qhv.durationStateValue).text(qhv.durationStateLabel);

                    $filterDurationContainer.fadeOut(300);
                    $('.event-type-button').removeClass('current');
                    $(this).addClass('current');
                });

                /**
                 * When Recent Stop Event is clicked, restore day and duration filter setting
                 */
                $historyFilterRecentstops.on('click', function() {
console.log('$historyFilterRecentstops:click');
                    var eventType = eventType = $('.event-type-button').filter('.current').val()
                        ,qhv = Vehicle.Common.DetailPanel.quickHistoryValues;

                    if ($(this).val() !== eventType) {                  // if this event type is not the currently active event,
                        Vehicle.Common.DetailPanel.clearQuickHistoryMarkers();
                    }

                    $selectHistoryDayFilter.val(qhv.dateStateValue).text(qhv.dateStateLabel);
                    $selectHistoryDurationFilter.val(qhv.durationStateValue).text(qhv.durationStateLabel);

                    $filterDurationContainer.fadeIn(300).removeClass('hide');
                    $('.event-type-button').removeClass('current');
                    $(this).addClass('current');
                });

                /**
                 * When Frequent Stop Event is clicked, restore day and duration filter setting
                 */
                $historyFilterFrequentstops.on('click', function() {
console.log('$historyFilterFrequentstops:click');
                    var eventType = eventType = $('.event-type-button').filter('.current').val()
                        ,qhv = Vehicle.Common.DetailPanel.quickHistoryValues;

                    if ($(this).val() !== eventType) {                  // if this event type is not the currently active event,
                        Vehicle.Common.DetailPanel.clearQuickHistoryMarkers();
                    }

                    $selectHistoryDayFilter.val(qhv.dateStateValue).text(qhv.dateStateLabel);
                    $selectHistoryDurationFilter.val(qhv.durationStateValue).text(qhv.durationStateLabel);

                    $filterDurationContainer.fadeIn(300).removeClass('hide');
                    $('.event-type-button').removeClass('current');
                    $(this).addClass('current');
                });

                /**
                 * When the Day Filter Dropdown Changes, store current date and duration then redraw table
                 */
                $selectHistoryDayFilter.on('Core.DropdownButtonChange', function() {

                    // clear temp markers and trigger a click on the currently selected unit
                    Vehicle.Common.DetailPanel.clearQuickHistoryMarkers();

                    var $self = $(".event-type-button").filter(".active");
                    var eventType = $self.val();

                    Vehicle.Common.DetailPanel.quickHistoryValues.dateStateValue = $selectHistoryDayFilter.val();
                    Vehicle.Common.DetailPanel.quickHistoryValues.dateStateLabel = $selectHistoryDayFilter.text();

                    Vehicle.Common.DataTables.quickHistoryAllDataTable.fnDraw();
                    Vehicle.Common.DataTables.quickHistoryRecentDataTable.fnDraw();
                    Vehicle.Common.DataTables.quickHistoryFrequentDataTable.fnDraw();
                });

                /**
                 * When the Duration Filter Dropdown Changes, store current date and duration then redraw table
                 */
                $selectHistoryDurationFilter.on('Core.DropdownButtonChange', function() {

                    // clear temp markers and trigger a click on the currently selected unit
                    Vehicle.Common.DetailPanel.clearQuickHistoryMarkers();

                    var $self = $(".event-type-button").filter(".active");
                    var eventType = $self.val()
                        ,qhv = Vehicle.Common.DetailPanel.quickHistoryValues;

                    if (eventType == 'all') {
                        qhv.durationStateValue = "0-mins";
                        qhv.durationStateLabel = "All";
                    } else {
                        qhv.durationStateValue = $selectHistoryDurationFilter.val();
                        qhv.durationStateLabel = $selectHistoryDurationFilter.text();
                    }
                    Vehicle.Common.DataTables.quickHistoryAllDataTable.fnDraw();
                    Vehicle.Common.DataTables.quickHistoryRecentDataTable.fnDraw();
                    Vehicle.Common.DataTables.quickHistoryFrequentDataTable.fnDraw();
                });

                /* Email/Export Quick History to CSV */
                $body.on('click', '#popover-vehicle-history-export-csv-confirm, #popover-vehicle-history-export-pdf-confirm, #popover-history-email-confirm, #show-on-map', function() {
console.log('body:click:#popover-vehicle-history-export-csv-confirm, #popover-vehicle-history-export-pdf-confirm, #popover-history-email-confirm, #show-on-map');
                    var unitId = $('#detail-panel').find('.hook-editable-keys').eq(0).data('vehiclePk') || '',
                        eventType = $(".event-type-button.active").val() || '',
                        days = $selectHistoryDayFilter.val() || 'today',
                        time = $selectHistoryDurationFilter.val() || '0',
                        today = new Date(),
                        id = $(this).attr('id'),
                        validation = new Array()
                    ;

                    unitId = currentUnitId ;

                    if (unitId == undefined || unitId == '') {
                        validation.push('- Invalid unit id');
                    }

                    if (eventType == '') {
                        validation.push('- Please select a Quick History event type');
                    }

                    if (days == '') {
                        validation.push('- Please select a day duration');
                    }

                    if (validation.length == 0) {
                        startDate = Core.StringUtility.filterStartDateConversion(today, days);
                        endDate   = Core.StringUtility.filterEndDateConversion(today, days);
                        if (startDate != '' && endDate != '') {
                            if (id == 'popover-vehicle-history-export-csv-confirm' || id == 'popover-vehicle-history-export-pdf-confirm') {
                                exportFormat = id == 'popover-vehicle-history-export-pdf-confirm' ? 'pdf' : 'csv';
                                startDate = startDate.replace(/ /g,"_");
                                endDate   = endDate.replace(/ /g,"_");

                                window.location = '/ajax/vehicle/exportVehicleQuickHistory/'+exportFormat+'/'+unitId+'/'+eventType+'/'+startDate+'/'+endDate+'/'+time;
                            } else  if (id == 'popover-history-email-confirm') {
                                var emails = $('#quick-history-export-email').val();

                                if (emails != '') {

console.log('click:#popover-vehicle-history-export-csv-confirm, #popover-vehicle-history-export-pdf-confirm, #popover-history-email-confirm, #show-on-map:ajax');

                                    $.ajax({
                                        url: '/ajax/vehicle/sendEmailVehicleQuickHistory',
                                        type: 'POST',
                                        dataType: 'json',
                                        data: {
                                            unit_id: unitId,
                                            event_type: eventType,
                                            start_date: startDate,
                                            end_date: endDate,
                                            duration: time,
                                            emails: emails
                                        },
                                        success: function(responseData) {
                                            if (responseData.code === 0) {
                                                // success message will be displayed with the code below
                                            } else {
                                                if (! $.isEmptyObject(responseData.validation_error)) {
                                                    //	display validation errors
                                                }
                                            }

                                            if (! $.isEmptyObject(responseData.message)) {
                                                Core.SystemMessage.show(responseData.message, responseData.code);
                                            }

                                            if (! $.isEmptyObject(responseData.email_message) && responseData.email_message != '') {
                                                // hide email info div and display email message (for success or failed/invalid emails message)
                                                $('#popover-history-email-info-div').fadeOut('300', function() {
                                                    $('#popover-history-email-message-div').html(responseData.email_message).fadeIn('300');
                                                });
                                            }
                                        }
                                    });
                                } else {
                                    alert('Please enter at least one email address');
                                }
                            } else if (id == 'show-on-map') {

                                startDate = moment(startDate).utc().format();
								startDate = startDate.replace('T', ' ').replace(/\+.*/, '');
                                endDate = moment(endDate).utc().format();
								endDate = endDate.replace('T', ' ').replace(/\+.*/, '');

                                Map.clearMarkers(Vehicle.Map.map, function() {
                                    Map.updateMapBound(Vehicle.Map.map);
                                }, true);

console.log('show-on-map:ajax');

                                $.ajax({
                                    url: '/ajax/vehicle/getVehicleQuickHistoryForMap',
                                    type: 'POST',
                                    dataType: 'json',
                                    data: {
                                        unit_id: unitId,
                                        event_type: eventType,
                                        start_date: startDate,
                                        end_date: endDate,
                                        duration: time,
                                        sSortDir_0: 'desc',
                                        iSortCol_0: 1,
                                        mDataProp_1: 'stop_counter',
                                        bSortable_1:	true
                                    },
                                    success: function(responseData) {
                                        if (responseData.code === 0) {
                                            var unitdata = responseData.data,
                                                length = unitdata.length,
                                                lastIndex = length - 1,
                                                markerOptions = {},
                                                unitname = $('#detail-panel').find('.vehicle-label').text();
                                            ;

                                            if (length > 0) {
                                                Map.closeInfoWindow(Vehicle.Map.map, function() {
                                                    $.each(unitdata, function(key, value) {
                                                        value.unitname = unitname;
                                                        value.event_type = eventType;
                                                        value.max = length;

                                                        markerOptions = {
                                                            id: value.mappoint,
                                                            type: 'temp',
                                                            name: value.mappoint,
                                                            latitude: value.latitude,
                                                            longitude: value.longitude,
                                                            eventname: value.eventname, // used in map class to get vehicle marker color
                                                            click: function() {
                                                                Map.openInfoWindow(Vehicle.Map.map, 'quick_history', value.latitude, value.longitude, value);
                                                            }
                                                        };

                                                        Map.addMarker(Vehicle.Map.map, markerOptions, true);

                                                        if (key == lastIndex) {
                                                            // setting the second parameter in this function to true will update the map to view the temp markers only
                                                            Map.updateMapBound(Vehicle.Map.map, true);
                                                        }
                                                    });
                                                });
                                            } else {
                                                if ($('#info_window_div').length == 0) {
                                                    Map.clickMarker(Vehicle.Map.map, unitId);
                                                }
                                            }
                                        } else {
                                            if (! $.isEmptyObject(responseData.validation_error)) {
                                                //	display validation errors
                                            }
                                        }

                                        if (! $.isEmptyObject(responseData.message)) {
                                            Core.SystemMessage.show(responseData.message, responseData.code);
                                        }
                                    }
                                });
                            }
                        } else {
                            alert('Invalid start and/or end time');
                        }
                    } else {
                        alert(validation.join('\n'));
                    }
                });

                $('#quick-history-recent-table')
                .add($('#quick-history-frequent-table'))
                .add($('#quick-history-all-table'))
                .on('click', '.quick-history-map-link', function() {
console.log('#quick-history-recent-table, #quick-history-frequent-table, #quick-history-all-table:click:.quick-history-map-link');
                    var $self = $(this);

                    // clear temp markers
                    Map.clearMarkers(Vehicle.Map.map, function() {
                	Map.updateMapBoundZoom(Vehicle.Map.map);
console.log('zoom2');
                    }, true);

                    var unitId = $('#detail-panel').find('.hook-editable-keys').eq(0).data('vehiclePk') || '',
                        unitname        = $('#detail-panel').find('.vehicle-label').text(),
                        eventType       = $('.event-type-button').filter('.current').val(),
                        value           = {},
                        markerOptions   = {}
                    ;

                    value.mappoint          = $self.data('mappoint');
                    value.eventname         = $self.data('event');
                    value.display_unittime  = $self.data('datetime');
                    value.territoryname     = $self.data('landmarkname');
                    value.formatted_address = $self.data('location');
                    value.speed             = $self.data('speed');
                    value.duration          = $self.data('duration');
                    value.stop_counter      = $self.data('stopcounter');
                    value.latitude          = $self.data('lat');
                    value.longitude         = $self.data('long');
                    value.unitname          = unitname;
                    value.event_type        = eventType;

                    // hide unitId marker
                    Map.hideMarker(Vehicle.Map.map, unitId);

                    // close marker info window
                    Map.closeInfoWindow(Vehicle.Map.map, function() {
                        // set up marker info
                        markerOptions = {
                        	id:         value.mappoint,
                        	type:       'temp',
                        	name:       value.mappoint,
                        	latitude:   value.latitude,
                        	longitude:  value.longitude,
                        	eventname:  value.eventname, // used in map class to get vehicle marker color
                        	click: function() {
                        		Map.openInfoWindow(Vehicle.Map.map, 'quick_history', value.latitude, value.longitude, value);
                        	}
                        };

                        // add temp marker to map
                        Map.addMarker(Vehicle.Map.map, markerOptions, true);

                        // setting the second parameter in this function to true will update the map to view the temp markers only
                        Map.updateMapBoundZoom(Vehicle.Map.map, true);
                    });

                });

            },

            quickHistoryValues: {
                dateStateValue:      undefined
                ,dateStateLabel:     undefined
                ,durationStateValue: undefined
                ,durationStateLabel: undefined
            },

            // when switching to view other units, reset the quick history tabs to default
        	resetQuickHistoryDataTable: function() {

                var qhv = Vehicle.Common.DetailPanel.quickHistoryValues;

                qhv.dateStateValue     = "today"
                qhv.dateStateLabel     = "Today"
                qhv.durationStateValue = "0-mins"
                qhv.durationStateLabel = "All"

        	    //Vehicle.Common.DetailPanel.resetQuickHistoryDataTable();
            	// reset active class
            	$('.event-type-button').removeClass('active')
            	                       .removeClass('current');
                $('#filter-duration-container').addClass('hide');

                // day/duration defaults
                $('#select-history-day-filter').val(qhv.dateStateValue).text(qhv.dateStateLabel);
                $('#select-history-duration-filter').val(qhv.durationStateValue).text(qhv.durationStateLabel);

                // all events defaults
                $('#history-filter-all-events').addClass('active')
                                               .addClass('current')
                                               .trigger('click');
        	},

        	clearQuickHistoryMarkers: function() {
                if (Map.hasTempMarkers(Vehicle.Map.map)) {
                    Map.clearMarkers(Vehicle.Map.map, function() {  // clear temp markers and trigger a click on the currently selected unit
                        var unitId = $('#detail-panel').find('.hook-editable-keys').eq(0).data('vehiclePk') || '';
                        if (unitId != '') {
                            // show marker first before clicking
                            Map.showMarker(Vehicle.Map.map, unitId);
                            setTimeout(function(){
                                Map.clickMarker(Vehicle.Map.map, unitId);
                            }, 400);
                        }

                         Map.updateMapBound(Vehicle.Map.map);
                    }, true);
                }
        	},

            initVerificationTab: function() {

                var $body = $('body');

                /**
                 * Navigation Verification Tab - makes ajax call to get reference landmarks for the selected vehicle
                 *
                 */

                // $('#vehicle-detail-verification-tab').click(function() {

                //     Vehicle.Common.DetailPanel.clearQuickHistoryMarkers();

                //     if ($(this).is('.draw_table')) {
                //         // Vehicle.Map.DataTables.verificationDataTable.fnDraw();
                //         //$(this).removeClass('draw_table');
                //     }

                //     window.setTimeout("Vehicle.Common.DetailPanel.open('"+$(this).attr('id')+"')",1);

                // });

                /**
                 * Filter for Verification table
                 *
                 */
                $('#verification-filter').on('Core.DropdownButtonChange', function() {
                    //  temporarily use client-side processing for now - will change to server-side after the meeting on 10/15
                    // Vehicle.Map.DataTables.verificationDataTable.fnDraw();

                });

                /**
                 * Export Verification table
                 *
                 */
                $body.on('click', '#popover-verification-export-csv-confirm, #popover-verification-export-pdf-confirm', function() {
console.log('body:click:#popover-verification-export-csv-confirm, #popover-verification-export-pdf-confirm');
                    var exportFormat = $(this).prop('id') == 'popover-verification-export-pdf-confirm' ? 'pdf' : 'csv';
                    var $container = $(),
                        unitId = ''
                    ;

                    if (Core.Environment.context() == 'vehicle/map') {
                        $container = $('#detail-panel');
                    } else if (Core.Environment.context() == 'vehicle/list') {
                        $container = $('#modal-vehicle-list');
                    }

                    // unitId = $container.find('.hook-editable-keys').eq(0).data('vehiclePk');
                    unitId = currentUnitId ;

                    if (unitId != '') {
                        window.location = '/ajax/vehicle/exportReferenceLandmarks/' + exportFormat + '/' + unitId;
                    }
                });

                /**
                 * Add Address Popover - Locate on Map button
                 *
                 */
                $body.on('click', '#verification-address-locate, #verification-address-locate-edit', function() {
                    $('#verification-add-confirm').prop('disabled',true);
console.log('body:click:#verification-address-locate, #verification-address-locate-edit');
                    var id = $(this).attr('id'),
                        $address = $(),
                        $latlng = $(),
                        $title = $(),
                        $radius = $(),
                        addressValue = '',
                        edit = (id == 'verification-address-locate-edit') ? '-edit' : '',
                        $context = (id == 'verification-address-locate-edit') ? $('.popover').filter('.in') : $body,
                        $addButton = (id == 'verification-address-locate-edit') ? $('#popover-verification-edit-address-confirm', $context) : $('#popover-verification-add-address-persist')
                    ;

                    $address = $('#verification-address-address' + edit, $context);
                    $latlng = $('#verification-address-latlng' + edit, $context);
                    $title = $('#verification-address-name' + edit, $context);
                    $radius = $('#verification-address-radius' + edit, $context); // radius is in miles

                    addressValue = $address.val();

                    if (addressValue != undefined && addressValue != '') {

                        Map.geocode(Vehicle.Map.map, addressValue, function(data) {

                            if (data.success == 1) {

                                if(1==0){
                                // if(Core.Environment.context()=='vehicle/map'){

                                    var markerOptions = {} ;
                                    Map.clearMarkers(Vehicle.Map.map);
                                    Map.resetMap(Vehicle.Map.map);
                                    Map.resize(Vehicle.Map.map);
                                    markerOptions = {
                                        id: 999,
                                        type: 'temp',
                                        name: $address.val(),
                                        latitude: data.latitude,
                                        longitude: data.longitude,
                                        eventname: 'stop', // used in map class to get vehicle marker color
                                    };
                                    Map.addMarker(Vehicle.Map.map, markerOptions, true);
                                    // Map.updateMapBound(Vehicle.Map.map);
                                    Map.updateMapBound(Vehicle.Map.map, true);
                                    // Map.updateMapBoundZoom(Vehicle.Map.map, true);

                                } else {

                                    if (! Map.doesTempLandmarkExist(Vehicle.Map.map)) {
                                        var eventCallbacks = {
                                            drag: function(event) {
                                                Map.updateTempLandmark(Vehicle.Map.map, 'circle', event.latitude, event.longitude, $radius.val(), $title.val());
                                                $address.val('Waiting...');
                                                $latlng.html('');
                                            },
                                            dragend: function(event) {
                                                Map.centerMap(Vehicle.Map.map, event.latitude, event.longitude);
                                                Map.reverseGeocode(Vehicle.Map.map, event.latitude, event.longitude, function(result) {
                                                    if (result.success == 1) {
                                                        $address.val(result.formatted_address);
                                                        verificationAddLat = result.latitude ;
                                                        verificationAddLng = result.longitude;
                                                        $latlng.html(verificationAddLat+' / '+verificationAddLng);
                                                        $addButton.data('latitude', result.latitude)
                                                                  .data('longitude', result.longitude)
                                                                  .data('street-address', result.address_components.address)
                                                                  .data('city', result.address_components.city)
                                                                  .data('state', result.address_components.state)
                                                                  .data('zip', result.address_components.zip)
                                                                  .data('country', result.address_components.country);
                                                    } else {
                                                        alert(result.error);
                                                    }
                                                });
                                                $addButton.data('latitude', event.latitude).data('longitude', event.longitude);
                                            }
                                        };

                                        Map.createTempLandmark(Vehicle.Map.map, 'circle', data.latitude, data.longitude, $radius.val(), $title.val(), true, eventCallbacks);
                                    } else {
                                        Map.updateTempLandmark(Vehicle.Map.map, 'circle', data.latitude, data.longitude, $radius.val(), $title.val());
                                        Map.centerMap(Vehicle.Map.map, data.latitude, data.longitude);
                                    }

                                    $addButton.data('latitude', data.latitude)
                                              .data('longitude', data.longitude)
                                              .data('street-address', data.address_components.address)
                                              .data('city', data.address_components.city)
                                              .data('state', data.address_components.state)
                                              .data('zip', data.address_components.zip)
                                              .data('country', data.address_components.country);

                                }

                                $('#verification-address-latlng').html(data.latitude+' / '+data.longitude)
                                verificationAddLat = data.latitude;
                                verificationAddLng = data.longitude;
                                verificationAddAddress = data.address_components.address;
                                verificationAddCity = data.address_components.city;
                                verificationAddState = data.address_components.state;
                                verificationAddZip = data.address_components.zip;
                                verificationAddCountry = data.address_components.country;

                                $('#verification-add-confirm').prop('disabled',false);

                            } else {

                                alert(data.error);

                            }

                        });

                    } else {
                        // alert('Please enter an address to be geocoded');
                    }

                });

                /**
                 * Add Address Popover - Use Map Click button
                 *
                 */
                $body.on('click', '#verification-address-click, #verification-address-click-edit', function() {
console.log('body:click:#verification-address-click, #verification-address-click-edit');
                    var id = $(this).attr('id'),
                        $address = $(),
                        $title = $(),
                        $radius = $(),
                        edit = (id == 'verification-address-click-edit') ? '-edit' : '',
                        $context = (id == 'verification-address-click-edit') ? $('.popover').filter('.in') : $body,
                        $addButton = (id == 'verification-address-locate-edit') ? $('#popover-verification-edit-address-confirm', $context) : $('#popover-verification-add-address-persist')
                    ;

                    $address = $('#verification-address-address' + edit, $context);
                    $title = $('#verification-address-name' + edit, $context);
                    $radius = $('#verification-address-radius' + edit, $context); // radius is in miles

                    Map.addMapClickListener(Vehicle.Map.map, function(event) {
                        Map.reverseGeocode(Vehicle.Map.map, event.latitude, event.longitude, function(result) {
                            if (result.success == 1) {
                                if (! Map.doesTempLandmarkExist(Vehicle.Map.map)) {
                                    var eventCallbacks = {
                                        drag: function(data) {
                                            Map.updateTempLandmark(Vehicle.Map.map, 'circle', data.latitude, data.longitude, $radius.val(), $title.val());
                                            $address.val('Waiting...');
                                        },
                                        dragend: function(data) {
                                            Map.centerMap(Vehicle.Map.map, data.latitude, data.longitude);
                                            Map.reverseGeocode(Vehicle.Map.map, data.latitude, data.longitude, function(data1) {
                                                if (data1.success == 1) {
                                                    $address.val(data1.formatted_address);
console.log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> '+data1.latitude + ' / ' + data.longitude);
                                                    $addButton.data('latitude', data1.latitude)
                                                              .data('longitude', data1.longitude)
                                                              .data('street-address', data1.address_components.address)
                                                              .data('city', data1.address_components.city)
                                                              .data('state', data1.address_components.state)
                                                              .data('zip', data1.address_components.zip)
                                                              .data('country', data1.address_components.country);
                                                } else {
                                                    alert(data1.error);
                                                }
                                            });
                                            $addButton.data('latitude', data.latitude).data('longitude', data.longitude);

                                            if (Map.api() == 'mapbox') {    // fix for a possible mapbox/leaflet api bug where a 'click' event is triggered after a 'dragend'
                                                setTimeout(function() {
                                                    Vehicle.Map.map._preventClick = false;
                                                }, 500);
                                            }
                                        }
                                    };
                                    Map.createTempLandmark(Vehicle.Map.map, 'circle', result.latitude, result.longitude, $radius.val(), $title.val(), true, eventCallbacks);
                                } else {
                                    Map.updateTempLandmark(Vehicle.Map.map, 'circle', result.latitude, result.longitude, $radius.val(), $title.val());
                                    Map.centerMap(Vehicle.Map.map, result.latitude, result.longitude);
                                }

                                $address.val(result.formatted_address);

                                $addButton.data('latitude', result.latitude)
                                          .data('longitude', result.longitude)
                                          .data('street-address', result.address_components.address)
                                          .data('city', result.address_components.city)
                                          .data('state', result.address_components.state)
                                          .data('zip', result.address_components.zip)
                                          .data('country', result.address_components.country);
                            } else {
                                alert(result.error);
                            }
                        });
                    });
                });

                /**
                 * Add Address Popover - Radius Dropdown
                 *
                 */
                $body.on('Core.DropdownButtonChange', '#verification-address-radius, #verification-address-radius-edit', function() {
                    if (Map.doesTempLandmarkExist(Vehicle.Map.map)) {
                        Map.updateTempPolygon(Vehicle.Map.map, {type: 'circle', radius: $(this).val()});
                    }
                });

                /**
                 * Add Address Popover - Landmark/Address Name
                 *
                 */
                $body.on('keyup', '#verification-address-name, #verification-address-name-edit', function() {
                    Map.updateTempMarker(Vehicle.Map.map, {title: $(this).val()});
                });

                /**
                 * Add Address Popover - Add button
                 *
                 */
                $body.on('click', '#popover-verification-add-address-persist', function(event, extraParams) {
console.log('body:click:#popover-verification-add-address-persist');
                    var latitude = $(this).data('latitude'),
                        longitude = $(this).data('longitude'),
                        streetAddress = $(this).data('streetAddress'),
                        city = $(this).data('city'),
                        state = $(this).data('state'),
                        zip = $(this).data('zip'),
                        country = $(this).data('country'),
                        title = $('#verification-address-name').val(),
                        radius = $('#verification-address-radius').val(),
                        $container = {},
                        unitId = '',
                        validation = {}
                    ;

                    if (Core.Environment.context() == 'vehicle/map') {
                        $container = $('#detail-panel');
                    } else if (Core.Environment.context() == 'vehicle/list') {
                        $container = $('#modal-vehicle-list');
                    }

                    unitId = $container.find('.hook-editable-keys').eq(0).data('vehiclePk');

                    if (unitId == '') {
                        validation['unit_id'] = 'Invalid unit id';
                    }

                    if (title == '') {
                        validation['verification-address-name'] = 'Address Name cannot be blank';
                    }

                    if (latitude == null || longitude == null) {
                        validation['coordinates'] = 'Invalid latitude and/or longitude';
                    }

                    if (radius == '' || radius == null) {
                        validation['verification-address-radius'] = 'Invalid radius';
                    }


                    if ($.isEmptyObject(validation)) {

console.log('click:#popover-verification-add-address-persist:ajax');

                        $.ajax({
                            url: '/ajax/vehicle/addReferenceLandmarkToVehicle',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                unit_id: unitId,
                                latitude: latitude,
                                longitude: longitude,
                                title: title,
                                radius: radius,
                                street_address: streetAddress,
                                city: city,
                                state: state,
                                zip: zip,
                                country: country
                            },
                            success: function(responseData) {
                                if (responseData.code === 0) {

                                    Vehicle.Map.DataTables.verificationDataTable.fnDraw();

                                    if ($.isEmptyObject(extraParams)) { // if no other parameter was passed in with the click event, just reset the input fields
                                        Vehicle.Common.DetailPanel.resetVerificationAddAddressPopup();
                                    } else {                            // else, check the paramters for an alternative success callback (passed from Add + Close button to close popover)
                                        if ((extraParams.success != undefined) && (typeof(extraParams.success) == 'function')) {
                                            extraParams.success();
                                        }
                                    }
                                } else {
                                    if (! $.isEmptyObject(responseData.validation_error)) {
                                        //	display validation errors
                                    }
                                }

                                if (! $.isEmptyObject(responseData.message)) {
                                    Core.SystemMessage.show(responseData.message, responseData.code);
                                }
                            }
                        });
                    } else {
                        // show validation errors
                    }
                });

                /**
                 * Add Address Popover - Add + Close button
                 *
                 */
                $body.on('click', '#popover-verification-add-address-confirm', function() {
console.log('body:click:#popover-verification-add-address-confirm');
                    // trigger a click on the Add button, follow by a click on the Cancel button on success
                    $('#popover-verification-add-address-persist').trigger('click', {
                        success: function() {
                            $('#popover-verification-add-address-cancel').trigger('click');
                        }
                    });
                });



                /**
                 * Add Address Popover - Update Unit Id to add Uploaded Reference Landmarks To
                 *
                 */
                $body.on('click', '#button-verification-import', function() {
console.log('body:click:#button-verification-import');
                    var unitId = $('#detail-panel').find('.hook-editable-keys').eq(0).data('vehiclePk');

                    if (unitId != undefined && unitId != '') {
                        $('#reference-landmark-upload-unit-id').val(unitId);
                    }
                });

                /**
                 * Add Address Popover - Import Reference Address button
                 *
                 */
                $body.on('Core.Upload', '#popover-verification-import-confirm', function() {

                    var iframeId = $(this).data('iframeId'),
                        responseData = Core.Upload.getResponse(iframeId)
                    ;

                    if (! $.isEmptyObject(responseData)) {

                        if (responseData.code === 0) {
                            Vehicle.Map.DataTables.verificationDataTable.fnDraw();
                        }

                        if (! $.isEmptyObject(responseData.upload_message) && responseData.upload_message != '') {
                            $('#verification-import-csv-info-div').hide();
                            $('#verification-import-csv-success-div').html(responseData.upload_message).show();
                            $('#button-verification-import').trigger('Core.PopoverContentChange');
                        }

                        if (! $.isEmptyObject(responseData.message)) {
                            Core.SystemMessage.show(responseData.message, responseData.code);
                        }

                    }

                    $('#verification-import-csv-form').clearForm();
                });

                /**
                 * Table Action - TR Hover & TR Highlight when editing
                 *
                 * */
                var $verificationTable = $('#verification-table');
                if ($verificationTable.data('editable') == 1) { // check to see if user has permission to edit verification addresses
                    $verificationTable.on('mouseenter', 'tr', function() {
                        var $self = $(this);
                        $self.find('.glyphicon').removeClass('hidden');
                    });
                    $verificationTable.on('mouseleave', 'tr', function() {
                        var $self = $(this);
                        if (! $self.is('.editing-tr-data')) {
                            $self.find('.glyphicon').addClass('hidden');
                        }
                    });

                    $verificationTable.on('click', '.has-popover', function() {
console.log('$verificationTable:click:.has-popover');
                        var $self = $(this),
                            $tr = $self.closest('tr')
                        ;
                        $tr.addClass('editing-tr-data');
                    });
                }

                /**
                 * Populate Edit Address Popover
                 * */

                $verificationTable.on('shown.bs.popover', '.has-popover', function(event) {

                    var $self     = $(this),
                        $tr       = $self.closest('tr'),
                        name      = $tr.find('.verification-name-cell').text(),
                        address   = $tr.find('.verification-address-cell').text(),
                        radius    = $tr.find('.verification-radius-cell').text(),
                        $popover  = $('.popover').filter('.in'),
                        feetInMile = 5280,
                        landmarkId = $tr.data('landmarkId'),
                        $updateButton = $('#popover-verification-edit-address-confirm', $popover)
                        lat       = $tr.data('latitude'),
                        long      = $tr.data('longitude'),
                        streetAddress = $tr.data('streetAddress'),
                        city      = $tr.data('city'),
                        state     = $tr.data('state'),
                        zip       = $tr.data('zip'),
                        country   = $tr.data('country')
                    ;

                    $('#verification-address-name-edit', $popover).val(name);
                    $('#verification-address-address-edit', $popover).val(address);
                    $('#verification-address-radius-edit', $popover).siblings('.dropdown-menu').find('a[data-value='+(feetInMile*radius)+']').trigger('click');
                    $popover.data('landmarkId', landmarkId);
                    $updateButton.data('latitude', lat)
                                 .data('longitude', long)
                                 .data('streetAddress', streetAddress)
                                 .data('city', city)
                                 .data('state', state)
                                 .data('zip', zip)
                                 .data('country', country);
                });

                /**
                 * Delete reference landmark/address
                 * */
                $body.on('click', '#popover-verification-address-delete-confirm', function() {
console.log('$body:click:#popover-verification-address-delete-confirm');
                    var $popover = $('.popover').filter('.in'),
                        landmarkId = $popover.data('landmarkId')
                    ;

                    if (landmarkId != '' && landmarkId != undefined) {

console.log('click:#popover-verification-address-delete-confirm:ajax');

                        $.ajax({
                            url: '/ajax/vehicle/deleteReferenceLandmark',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                landmark_id: landmarkId
                            },
                            success: function(responseData) {
                                if (responseData.code === 0) {
                                    // on success, redraw the verification table
                                    Vehicle.Map.DataTables.verificationDataTable.fnStandingRedraw();
                                } else {
                                    if (! $.isEmptyObject(responseData.validation_error)) {
                                        //	display validation errors
                                    }
                                }

                                if (! $.isEmptyObject(responseData.message)) {
                                    Core.SystemMessage.show(responseData.message, responseData.code);
                                }
                            },
                            complete: function() {
                                // close popover
                                $('#popover-verification-address-delete-cancel', $popover).trigger('click');
                            }
                        });
                    } else {
                        alert('Invalid Landmark ID');
                    }
                });

                /**
                 * Edit reference landmark/address
                 *
                 */
                 $body.on('click', '#popover-verification-edit-address-confirm', function() {
console.log('$body:click:#popover-verification-address-delete-confirm');
                    var $self = $(this),
                        $popover = $('.popover').filter('.in'),
                        landmarkId = $popover.data('landmarkId'),
                        radius = $('#verification-address-radius-edit', $popover).val(),
                        title = $('#verification-address-name-edit', $popover).val(),
                        latitude = $self.data('latitude'),
                        longitude = $self.data('longitude'),
                        streetAddress = $self.data('streetAddress'),
                        city = $self.data('city'),
                        state = $self.data('state'),
                        zip = $self.data('zip'),
                        country = $self.data('country'),
                        validation = new Array()
                    ;

                    if (landmarkId == '' || landmarkId == undefined) {
                        //validation['landmark-id'] = 'Invalid Landmark ID';
                        validation.push('- Invalid Landmark ID');
                    }

                    if (title == '') {
                        //validation['verification-address-name-edit'] = 'Address Name cannot be blank';
                        validation.push('- Address Name cannot be blank');
                    }

                    if (latitude == null || longitude == null) {
                        //validation['coordinates'] = 'Invalid latitude and/or longitude';
                        validation.push('- Invalid latitude and/or longitude');
                    }

                    if (radius == '' || radius == null) {
                        //validation['verification-address-radius-edit'] = 'Invalid radius';
                        validation.push('- Invalid Radius');
                    }

                    if ($.isEmptyObject(validation)) {
                        $.ajax({
                            url: '/ajax/vehicle/updateReferenceLandmark',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                landmark_id: landmarkId,
                                latitude: latitude,
                                longitude: longitude,
                                title: title,
                                radius: radius,
                                street_address: streetAddress,
                                city: city,
                                state: state,
                                zip: zip,
                                country: country
                            },
                            success: function(responseData) {
                                if (responseData.code === 0) {
                                    // on success, redraw the verification table
                                    Vehicle.Map.DataTables.verificationDataTable.fnStandingRedraw();
                                } else {
                                    if (! $.isEmptyObject(responseData.validation_error)) {
                                        //	display validation errors
                                    }
                                }

                                if (! $.isEmptyObject(responseData.message)) {
                                    Core.SystemMessage.show(responseData.message, responseData.code);
                                }
                            },
                            complete: function() {
                                // close popover
                                $('#popover-verification-edit-address-cancel', $popover).trigger('click');
                            }
                        });
                    } else {
                        // NTD: write a common function for displaying validation errors
                        alert(validation.join('\n'));
                    }
                 });
            },

            resetVerificationAddAddressPopup: function(removeClickListener) {
                // clear temp marker and polygon from map
                if (Map.doesTempLandmarkExist(Vehicle.Map.map)) {
                    Map.removeTempLandmark(Vehicle.Map.map);
                }

                if (removeClickListener != undefined && removeClickListener == true) {
                    // remove map click listener
                    Map.removeMapClickListener(Vehicle.Map.map);
                }

                // reset all input fields
                $('#verification-address-radius').val("330").text("1/16 Mile");
                $('#verification-address-name').val('');
                $('#verification-address-address').val('');

                // remove current lat/lng of temp landmark from Add button
                $('#popover-verification-add-address-persist').data('latitude', null)
                                                              .data('longitude', null)
                                                              .data('street-address', null)
                                                              .data('city', null)
                                                              .data('state', null)
                                                              .data('zip', null)
                                                              .data('country', null);
            },

            initPopOver: function() {
                /**
                 * Verification - Listener for when the Add Address Popover is closed
                 *
                 */
                $('#button-verification-add-address').on('hidden.bs.popover', function() {
                    Vehicle.Common.DetailPanel.resetVerificationAddAddressPopup(true); // reset input fields on success and remove click listener
                });

                /**
                 * Verification - Listener for when the Edit Address Popover is closed
                 *
                 */
                $('body').on('hidden.bs.popover', '#verification-table td.table-actions span.glyphicon-pencil', function() {
                    // clear temp marker and polygon from map
                    if (Map.doesTempLandmarkExist(Vehicle.Map.map)) {
                        Map.removeTempLandmark(Vehicle.Map.map);
                    }

                    // remove map click listener
                    Map.removeMapClickListener(Vehicle.Map.map);
                });

                /**
                 * Verification - Listener for when the Import Address Popover is closed
                 *
                 */
                $('#button-verification-import').on('hidden.bs.popover', function() {
                    $('#verification-import-csv-success-div').html('').hide();
                    $('#verification-import-csv-info-div').show();
                });

                /**
                 * Quick History - Listener for when the Email CSV Popover is closed
                 *
                 */
                 $('#button-history-email').on('hidden.bs.popover', function() {
                     $('#quick-history-export-email').val('');
                     $('#popover-history-email-message-div').html('').hide();
                     $('#popover-history-email-info-div').show();
                 });
            },

            renderForPrint: function(unit_id) {
               $.ajax({
                  url: '/ajax/vehicle/getVehicleInfo',
                  type: 'POST',
                  dataType: 'json',
                  data: {
                      unit_id: unit_id
                  },
                  success: function(responseData) {

                      if (responseData.code === 0) {
                           var unitdata = responseData.data;
                           Vehicle.Common.DetailPanel.basic_render(unitdata, function() {
                              if (! $.isEmptyObject(unitdata.eventdata)) {
                                 $('#print-event-info .vehicle-location-label').eq(0).text(((typeof(unitdata.eventdata) == 'object') && (! $.isEmptyObject(unitdata.eventdata)) && (unitdata.eventdata.formatted_address != undefined)) ? unitdata.eventdata.formatted_address : 'Location Not Available');
                                 $('#print-event-info .vehicle-in-landmark-label').eq(0).text(((typeof(unitdata.eventdata) == 'object') && (! $.isEmptyObject(unitdata.eventdata)) && (unitdata.eventdata.territoryname != undefined)) ? unitdata.eventdata.territoryname : 'n/a');
                                 Map.addMarker(
                                    Vehicle.Map.map,
                                    {
                                         id: unitdata.unit_id,
                                         name: unitdata.unitname,
                                         latitude: unitdata.eventdata.latitude,
                                         longitude: unitdata.eventdata.longitude,
                                         eventname: unitdata.eventdata.eventname,
                                         click: function() {
                                             Map.getVehicleEvent(Vehicle.Map.map, unitdata.unit_id, unitdata.eventdata.id);
                                         }
                                    },
                                    true
                                 );
                                 Map.updateMarkerBound(Vehicle.Map.map);
                                 Map.updateMapBound(Vehicle.Map.map);
                              } else {
                                   Map.resetMap(Vehicle.Map.map);
                                   $('#print-event-info').hide();
                              }
                           });
                      } else {
                           if ($.isEmptyObject(responseData.validaton_errors) === false) {
                              //	display validation errors
                           }
                      }

                      if ($.isEmptyObject(responseData.message) === false) {
                           //	display messages
                      }
                  }
               });
            }
        }

    }
});
