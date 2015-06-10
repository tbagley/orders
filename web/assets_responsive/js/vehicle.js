
$(document).ready(function() {

    $('body').on('click', function (e) {
console.log('body:click:start');
        $('.has-popover').each(function () {
            if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {

                //Leave Starter Enable/Disable panels visible
                if($('#popover-content-starter-enable-panels').is(":visible")||$('#popover-content-starter-disable-panels').is(":visible")) {return true;}

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
            }
        });
console.log('body:click:end');
    });

    $('#vehicleTabs a').click(function (e) {
console.log('vehicleTabs a');
      e.preventDefault();
      $(this).tab('show');
    });

    $('#tab-customer a').click(function (e) {
      e.preventDefault()
      $(this).tab('show')
    });

    $('#tab-device-info a').click(function (e) {
      e.preventDefault()
      $(this).tab('show')
    });

    $('#tab-vehicle a').click(function (e) {
      e.preventDefault()
      $(this).tab('show')
    });

    $('ul.list-group li.list-group-item').hover(function() {
console.log("ul.list-group li.list-group-item').hover.out");
        $( this ).find('div').hide();
    }, function() {
console.log("ul.list-group li.list-group-item').hover.in");
        $( this ).find('div').show();
    });




    /**
     *
     * Page Specific Functionality
     *
     * */
    switch (Core.Environment.context()) {

        /* MAP */
        case 'vehicle/map':

            //VehicleResponsive.Map.initMap();
            VehicleResponsive.Map.initQuickFilters();
            //VehicleResponsive.Map.initManageVehiclePanel();
            //VehicleResponsive.Map.initManageVehicleIcons();
            //VehicleResponsive.Map.initManageQuickHistory();
            //VehicleResponsive.Map.initCommands();
            //VehicleResponsive.Map.DataTables.init();
            VehicleResponsive.Map.initDraggablePanelBar();

            break;

        /* LIST */
        case 'vehicle/list':
            VehicleResponsive.Group.DataTable.init();
            VehicleResponsive.Group.DataTable.initVehicle();

            break;
        case 'vehicle/group':
            VehicleResponsive.Group.DataTable.init();
            VehicleResponsive.Group.DataTable.initGroup();


            break;

    }

    VehicleResponsive.isLoaded();

});

var VehicleResponsive = {};

jQuery.extend(VehicleResponsive, {

    isLoaded: function() {

        console.log('Responsive Vehicle JS Loaded');
    },

    Group: {

        DataTable: {

            init: function() {

                /* Initialize Bootstrap Datatables Integration */
                webApp.datatables();


            },

            initVehicle: function() {

console.log('VehicleResponsive:initVehicle');

                /* Initialize Datatables */
                $('#vehicle-list-example-datatable').dataTable({
                    //"aoColumnDefs": [{"bSortable": false, "aTargets": [4]}],
                    "iDisplayLength": 10,
                    "aLengthMenu": [[15, 30, 50, -1], [15, 30, 50, "All"]]
                });

                /* Add classes to select and input */
                $('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
                $('.dataTables_length select').addClass('form-control');

console.log('VehicleResponsive:initVehicle');

            },

            initGroup: function() {

console.log('VehicleResponsive:initGroup');

                /* Initialize Datatables */
                $('#example-datatable').dataTable({
                    //"aoColumnDefs": [{"bSortable": false, "aTargets": [4]}],
                    "iDisplayLength": 10,
                    "aLengthMenu": [[15, 30, 50, -1], [15, 30, 50, "All"]]
                });


                /* Add classes to select and input */
                $('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
                $('.dataTables_length select').addClass('form-control');

console.log('VehicleResponsive:initGroup');

            },

            search: function() {


            }
        },

        Modal: {

            init: function() {
                
            }
        },

        Edit: {

            init: function() {
            
            }
        }

    },

    Map: {

        DataTables: {

            init: function() {

                // verification table
                VehicleResponsive.Map.DataTables.$verificationDataTable = Core.DataTable.init('verification-table', 5, {
                    /*'bServerSide': true,
                    'sAjaxSource': '',*/
                    'oLanguage': {
                        'sInfo': 'Showing _START_ to _END_ of _TOTAL_ Locations'
                    }
                });
            },

            $verificationDataTable: null

        },

        map: undefined,

        initMap: function() {
            VehicleResponsive.Map.map = Map.initMap('map-div');
        },

        initQuickFilters: function() {

            var $quickFilters        = $('#quick-actions').find('a'),
                $allQuickFilterIcons = $quickFilters.find('.icon16')
            ;

            $quickFilters.click(function() {
                var $self        = $(this),
                    $secondaryPanelPagination = $('#secondary-panel-pagination'),
                    $clickedIcon = $self.find('.icon16')
                ;

                // when clicking quick filter icon, reset paging
                $secondaryPanelPagination.data('paging','');

                if ($clickedIcon.is('.active')) {
                    $allQuickFilterIcons.removeClass('active');

                    $('.sub-panel-items').find('li').filter('.active').removeClass('active');
                    $secondaryPanelPagination.data('drawMarker','');

                    // clear out map
                    Map.clearMarkers(VehicleResponsive.Map.map);
                    $('#hide-vehicle-panel').trigger('click', function() {
                        Map.resetMap(VehicleResponsive.Map.map);
                    });

                    // reset to default
                    $('#sidebar-vehicle-status').val('All').text('All');
                    VehicleResponsive.Common.SecondaryPanel.fetchFilteredVehicles();
                } else {
                    $allQuickFilterIcons.removeClass('active');
                    $clickedIcon.addClass('active');

                    $secondaryPanelPagination.data('drawMarker','yes');

                    var vehicle_filtervalue = $self.data('value');
                    var vehicle_filterlabel = $self.data('label');

                    $('#sidebar-vehicle-status').val(vehicle_filtervalue).text(vehicle_filterlabel);

                    $('#hide-vehicle-panel').trigger('click', function() {
                        //Map.resetMap(VehicleResponsive.Map.map);
                    });

                    var selectedVehicleSearchTab = $('#select-vehicle-search-tab');
                    var currenttab = selectedVehicleSearchTab.find('li').filter('.active');
                    if (currenttab.text() == 'Search') {
                        // switch to filter active
                        selectedVehicleSearchTab.find('li').each(function() {

                            var $self = $(this);

                            if($self.text() == 'Filter') {
                                $self.addClass('active');
                                $('#vehicle-search-tab').removeClass('in active');
                                $('#vehicle-filter-tab').addClass('in active');
                            } else {
                                $self.removeClass('active');
                            }
                        });
                    }                        // triger filter search for quick filtering

                    if (Core.Environment.context() == 'vehicle/map') {
                        // clear map markers
                        Map.clearMarkers(VehicleResponsive.Map.map);

                        // clear search string field
                        $('#text-vehicle-search').val('');

                        // request vehicles for listing for filter params
                        VehicleResponsive.Common.SecondaryPanel.fetchFilteredVehicles(true);
                    }

                }
            });

        },

        initDraggablePanelBar: function() {

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
                    Map.resize(VehicleResponsive.Map.map);

                    if (latitude != null && longitude != null) {

                        if ($('#info_window_div').length > 0) {     //  re-center map taking into consideration info window
                            latitude = parseFloat(latitude) + 0.00027;//0.0035;
                        }

                        Map.centerMap(VehicleResponsive.Map.map, latitude, longitude);
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
                                    Map.resetMap(VehicleResponsive.Map.map);
                                });
                            };
                        } else if (key == lastIndex && length != 1) {
                            callback = function() {
                                //Map.resetMap(VehicleResponsive.Map.map);
                            };
                        }

                        //Map.removeMarker(VehicleResponsive.Map.map, id, callback);
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
                                        VehicleResponsive.Common.DetailPanel.render(unitdata, function() {
                                            Map.resize(VehicleResponsive.Map.map);
                                            Map.updateMapBound(VehicleResponsive.Map.map);
                                            if (! $.isEmptyObject(unitdata.eventdata)) {
                                                Map.openInfoWindow(VehicleResponsive.Map.map, 'unit', unitdata.eventdata.latitude, unitdata.eventdata.longitude, unitdata.eventdata);
                                            } else {
                                                Map.resetMap(VehicleResponsive.Map.map);
                                            }
                                        });
                                    } else {
                                        if ($.isEmptyObject(responseData.validaton_errors) === false) {
                                            //  display validation errors
                                        }
                                    }

                                    if ($.isEmptyObject(responseData.message) === false) {
                                        //  display messages
                                    }
                                }
                            });
                        }
                    }
                });
            }
        }
    }

});
