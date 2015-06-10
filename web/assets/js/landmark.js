/*

    Vehicle JS

    File:       /assets/js/vehicle.js
    Author:     Tom Leach
*/

$(document).ready(function() {

    Landmark.isLoaded();

    /**
     *
     * Common Functionality for Map and List
     *
     */
    //Landmark.Common.initMap();
    Landmark.Common.SecondaryPanel.init();
    Landmark.Common.SecondaryPanel.initLandmarkSearch();
    Landmark.Common.DetailPanel.initLandmarkInfo();
    Landmark.Common.DetailPanel.initClose();
    //Landmark.Common.initMap();
    Landmark.Common.Popover.initAddLandmark();
    Landmark.Common.Popover.initImportExportLandmark();
    Core.Editable.disable();

console.log('landmark/map');
    switch (Core.Environment.context()) {

        /* MAP */
        case 'landmark/map':
            Landmark.Common.initMap();
            Landmark.Map.initDraggablePanelBar();
            Landmark.Common.Popover.initTerritoryMode();
            Landmark.Common.DetailPanel.initMoreOptions();
            break;

        /* LIST */
        case 'landmark/list':
        //case 'landmark/verification':
            Landmark.Common.initMap();
            Landmark.List.initModal();
            Landmark.List.DataTables.init();
            // Landmark.List.initPopoverMap();
            Landmark.Common.Popover.initTerritoryMode();
            Landmark.Common.DetailPanel.initMoreOptions();
            break;
        case 'landmark/incomplete':
            Landmark.Common.initMap();
            Landmark.Incomplete.initIncompleteModal();
            Landmark.Incomplete.DataTables.init();
            Landmark.Common.DetailPanel.initMoreOptions();
            break;
        case 'landmark/group':
            Landmark.Group.DataTable.init();
            Landmark.Group.Modal.init();
            Landmark.Group.Edit.init();
            Landmark.Group.Popover.init();
            Landmark.Common.SecondaryPanel.initLandmarkGroupSearch();
        case 'landmark/verification':
            Landmark.Common.initMap();
            Landmark.Verification.initVerificationModal();
            Landmark.Verification.DataTables.init();
            Landmark.Common.DetailPanel.initMoreOptions();
            break;
    }
console.log('landmark/map');

});

var Landmark = {};

jQuery.extend(Landmark, {

    AddMap: {

        init: function () {

console.log("................................................................................... Landmark.AddMap.init : 1");

            //var val = $method.val(),
            var $self       = $('#landmark-add-shape'),
                id          = $self.prop('id'),
                map         = Landmark.Common.getCurrentMap()
            ;
            
            var $title          = $('#landmark-add-name'),
                // $addButton      = (id == 'landmark-new-method') ? $('#popover-landmark-new-persist') : $('#landmark-save'),
                // $addSaveButtons = (id == 'landmark-new-method') ? $('#popover-landmark-new-persist, #popover-landmark-new-confirm') : $('#landmark-save, #landmark-restore'),
                $latitude       = $('#landmark-add-latitude'),
                $longitude      = $('#landmark-add-longitude'),
                $radius         = $('#landmark-add-radius'),
                $type           = $('#landmark-add-shape')
            ;

            if ($type.val() != 'polygon') {
console.log("................................................................................... Landmark.AddMap.init : 2a");

                Map.removeMapClickListener(map);
                Landmark.Common.enableEditLandmarkInfo();
                Map.removeTempLandmark(map);
                $('#landmark-add-radius-row').show();                                

                var eventCallbacks = {
                    click: function(data) {
console.log('eventCallbacks:click');
                    }
                };

                Map.addMapClickListener(map, function(event) {
                    Map.clearMarkers(map);
                    Map.createTempLandmark(map, $type.val(), $latitude.val(), $longitude.val(), $radius.val(), $title.val(), true, eventCallbacks);                                    
                });
                    
            } else {
console.log("................................................................................... Landmark.AddMap.init : 2b");

                $('#landmark-add-radius-row').hide();                                

console.log('eventCallbacks:click');
                Map.addMapClickListener(map, function(event) {
console.log('eventCallbacks:click');

                    var eventCallbacks = {
                        click: function(data) {
console.log('eventCallbacks:click');
                        },
                        drag: function(data) {
console.log('eventCallbacks:drag');
                            var title = $title.val(),
                                radius = $radius.val()
                            ;
                            Map.updateTempLandmark(map, $type.val(), data.latitude, data.longitude, radius, title);
                            $locate.val('Waiting...');                                      
                        },
                        dragend: function(data) {
console.log('eventCallbacks:dragend');
                            var a = { latitude: data.latitude, longitude: data.longitude };
                            Core.EditMap.LatLngEdit(a);
                            // $addButton.data('latitude', data.latitude).data('longitude', data.longitude);
                            if (Map.api() == 'mapbox') {    // fix for a possible mapbox/leaflet api bug where a 'click' event is triggered after a 'dragend'
                                setTimeout(function() {
                                    map._preventClick = false;
                                }, 500);                                                    
                            }  
                        }    
                    };

                    if ($type.val() == 'circle' || $type.val() == 'square') {

                        Map.reverseGeocode(map, event.latitude, event.longitude, function(result) {

                            if (result.success == 1) {
                                
                                var title = $title.val(),
                                    radius = $radius.val()
                                ;

                                if (! Map.doesTempLandmarkExist(map)) {
                                    Map.clearMarkers(map);
                                    Map.createTempLandmark(map, $type.val(), result.latitude, result.longitude, radius, title, true, eventCallbacks);                                    
console.log('Map.doesTempLandmarkExist(map):no');
                                } else {
console.log('Map.doesTempLandmarkExist(map):yes');
                                    Map.updateTempLandmark(map, $type.val(), result.latitude, result.longitude, radius, title);
                                    Map.centerMap(map, result.latitude, result.longitude);
                                }

                                // $locate.val(result.formatted_address);    
console.log('result');
console.log(result);

                            } else {
                                alert(result.error);
                            }        
                        });

                    }  else if ($type.val() == 'rectangle' || $type.val() == 'polygon') {

                        var title   = $title.val(),
                            radius  = $radius.val()
                        ;
                        
                        // events for the points
                        var events = {
                            click: function() {

                                if ((($type.val() == 'rectangle') && (Map.getTempMarkerArray(map).length == 2)) || (($type.val() == 'polygon') && (Map.getTempMarkerArray(map).length > 2))) {
                                    // $addSaveButtons.prop('disabled', false);                                   
                                } else {
                                    //
                                }
                            }
                        };
                        
                        if (! Map.doesTempLandmarkExist(map)) {
console.log('create draggable landmark');
                            // Map.createTempLandmark(map, $type.val(), event.latitude, event.longitude, radius, title, true, {}, events);                                    
                            Map.updateTempLandmark(map, $type.val(), event.latitude, event.longitude, radius, title, events, true);
                        } else {
console.log('update draggable landmark');
                            Map.updateTempLandmark(map, $type.val(), event.latitude, event.longitude, radius, title, events, true);
                        }
console.log('draggable landmark created/updated');
                        
                        if ((($type.val() == 'rectangle') && (Map.getTempMarkerArray(map).length == 2)) || (($type.val() == 'polygon') && (Map.getTempMarkerArray(map).length > 2))) {
                            // $addSaveButtons.prop('disabled', false);
                        } else {
                            //
                        }
                    }     
                });
console.log(' >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Map.addMapClickListener(map, function(event)');
            }
console.log("................................................................................... Landmark.AddMap.init : EOF");
        }

    },

    isLoaded: function() {

        Core.log('Landmark JS Loaded');
    },
    
    Map: {

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
            })

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

                var landmarkId = $('#detail-panel').find('.hook-editable-keys').data('landmarkPk'),
                    $selectedItem = $('#landmark-li-'+landmarkId),
                    latitude = $selectedItem.data('latitude'),
                    longitude = $selectedItem.data('longitude')
                ;

                if (wasDragging) {
                    $panel.removeClass('disable-selection');
                    $window.unbind('mousemove');//$bar.css('cursor', 'default');
                    //$bar.css('cursor', 'row-resize');
                    Map.resize(Landmark.Common.map);
                    
                    if (latitude != null && longitude != null) {
                    
                        if ($('#info_window_div').length > 0) {     //  re-center map taking into consideration info window 
                            latitude = parseFloat(latitude) + 0.00027;//0.0035;
                        }
                     
                        Map.centerMap(Landmark.Common.map, latitude, longitude);                    
                    }
                } else {
                    $window.unbind('mousemove');
                }
            });
        }

    },
    
    List: {

        map: undefined,
        
        initPopoverMap: function() {
            var options = {
                zoom: 4
            };
            
            Landmark.List.map = Map.initMap('popover-map-div', options);
        },

        DataTables: {

            init: function() {

                Landmark.List.DataTables.landmarkListTable = Core.DataTable.init('landmark-list-table', 20, {
                    'oLanguage': {
                        'sInfo': 'Showing _START_ to _END_ of _TOTAL_ Landmarks'
                    },
                    //"aLengthMenu": [[20, 50, 100]],
                    //"sScrollY": "400px",
                    //"bScrollCollapse": true,
                    'bServerSide': true,
                    'sAjaxSource':   '/ajax/landmark/getFilteredLandmarksList',
                    'aoColumns': [
                        { 'mDataProp': 'territoryname' },
                        { 'mDataProp': 'territorygroupname' },
                        { 'mDataProp': 'radius_in_miles' },
                        { 'mDataProp': 'formatted_address' }
                    ],
                    'aoColumnDefs': [
                        { 'sClass': 'col-landmark no-wrap', 'aTargets': [0] },
                        { 'sClass': 'col-group',            'aTargets': [1] },
                        { 'sClass': 'col-radius',           'aTargets': [2]},
                        { 'sClass': 'col-location',         'aTargets': [3]}
                    ],
                    'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
                        // set <a> tag name and add data-landmark-id attribute to tr
                        $('td:eq(0)', nRow).html('<a href="#">'+aData.territoryname+'</a>');
                        $(nRow).data('landmarkId', aData.territory_id)
                               .data('latitude', aData.latitude)
                               .data('longitude', aData.longitude);
                               
                        if (aData.formatted_address == '') {
                            $('td:eq(4)', nRow).text(aData.latitude + ' ' + aData.longitude);
                        }

                        return nRow;
                    },
                    'fnServerParams': function (aoData) {

                        var landmark_group_id       = $('#select-landmark-group-filter').val().trim();
                        var landmark_category_id    = $('#select-landmark-category-filter').val().trim();
                        //var landmark_type           = $('#filter-territory-all, #filter-territory-landmark, #filter-territory-reference').filter(':checked').val();
                        var landmark_type           = 'landmark';
                        var search_string           = '';
                        var filter_type             = 'group_filter';

                        var searchLandmarkString = $('#text-landmark-search').val().trim();
                        if (typeof(searchLandmarkString) != 'undefined' && searchLandmarkString != '')
                        {
                            search_string           = searchLandmarkString;
                            landmark_group_id       = 'All';
                            landmark_state_status   = 'All';
                            filter_type             = 'string_search';
                            
                        }

                        aoData.push({name: 'territorygroup_id', value: landmark_group_id});
                        aoData.push({name: 'territorycategory_id', value: landmark_category_id})
                        aoData.push({name: 'search_string', value: search_string});
                        aoData.push({name: 'territorytype', value: landmark_type});
                        aoData.push({name: 'filter_type', value: filter_type});
                    }
                });
            }
        },

        initModal: function() {
console.log('initModal');

            $(document).on('click', '.col-landmark a', function() {

                var $self               = $(this),
                    $trNode             = $self.closest('tr'),
                    landmarkId          = $trNode.attr('id').split('-')[2],
                    $modal              = $('#modal-landmark-list'),
                    $landmarkLocation   = $modal.find('.modal-location').eq(0)
                ;
                
                if (landmarkId != undefined) {
                    $.ajax({
                        url: '/ajax/landmark/getLandmarkByIds',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            territory_id: landmarkId
                        },
                        success: function(responseData) {
                            if (responseData.code === 0) {
                                var landmarkData = responseData.data;
    
                                Landmark.Common.DetailPanel.render(landmarkData);

                                Core.Dialog.launch('#'+$modal.prop('id'), landmarkData.territoryname, {
                                        width: '1080px'
                                    },
                                    {
                                        hidden: function() {
                                            Landmark.Common.DetailPanel.reset();
                                            if ($('#detail-panel').data('updateRow') != undefined && $('#detail-panel').data('updateRow') == true) {
                                                Landmark.List.DataTables.landmarkListTable.fnStandingRedraw();    
                                            }
                                        },
                                        show: function() {
                                            var location = $self.closest('td').siblings('.col-location').html();
                                            $landmarkLocation.html('<span class="landmark-location-label">@ '+location+'</span>');
                                            $('.popover').find('.close').trigger('click');
                                            Landmark.Common.DetailPanel.render();
                                       },
                                       shown: function() {
                                           Core.Viewport.adjustLayout();
console.log('initModal:shown');
                                        // show address in modal title
                                        var location = $self.closest('td').siblings('.col-location').html();
                                        $landmarkLocation.html('<span class="landmark-location-label">@ '+location+'</span>');
                                        Map.resize(Landmark.Common.map);
                                        Map.resetMap(Landmark.Common.map);
                                        Map.clearMarkers(Landmark.Common.map);
                                        if (landmarkData.latitude != '' && landmarkData.latitude != 0 && landmarkData.longitude != '' && landmarkData.longitude != 0) {
                                            var markerOptions = {
                                                    id: landmarkData.territory_id,
                                                    name: landmarkData.territoryname,
                                                    latitude: landmarkData.latitude,
                                                    longitude: landmarkData.longitude,
                                                    click: function() {
                                                        Map.getLandmarkInfo(Landmark.Common.map, landmarkData.territory_id);
                                                    }
                                                },
                                                polygonOptions = {
                                                    type: landmarkData.shape,
                                                    radius: landmarkData.radius,
                                                    points: landmarkData.coordinates
                                                }
                                            ;
                                            Map.addMarkerWithPolygon(Landmark.Common.map, markerOptions, true, polygonOptions);
                                            Map.updateMarkerBound(Landmark.Common.map);
                                            Map.updateMapBound(Landmark.Common.map);                                            
                                            Map.openInfoWindow(Landmark.Common.map, 'landmark', landmarkData.latitude, landmarkData.longitude, landmarkData);
                                        }
console.log('initModal:shown');

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


            var $method   = $('#landmark-method'),
                $location = $('#landmark-location')
            ;
            $(document).on('Core.DropdownButtonChange', $method.selector, function() {

                var val = $method.val(),
                    placeholder = 'Address or Lat/Long'
                ;

                if (val == 'address') {
                    placeholder = 'Address';
                } else if (val == 'lat-long') {
                    placeholder = 'Lat/Long'
                }

                $location.prop('placeholder', placeholder);

            });
        }
    },

    Verification: {

        initVerificationModal: function() {
console.log('initVerificationModal');

            var $body                   = $('body');
            var $verificationSearch     = $('#text-verification-search');
            var $verificationSearchGo   = $('#verification-search-go');

            /**
             * On keyup when searching verification address using search string 
             *
             */
            $verificationSearch.on('keyup', function () {
                // get current search string
                var searchVerificationString = $verificationSearch.val().trim();
                if (searchVerificationString.length > 1 || searchVerificationString.length == 0) {
                    Landmark.Verification.DataTables.verificationListTable.fnStandingRedraw();
                }
                
                $('#verification-filter').val('ALL').text('All');
                $('#verification-vehicle-filter').val('ALL').text('All');

            });

            /**
             * On Search Button Click when searching verification address using search string 
             *
             */
            $verificationSearchGo.on('click', function () {
                Landmark.Verification.DataTables.verificationListTable.fnStandingRedraw();
                $('#verification-filter').val('ALL').text('All');
                $('#verification-vehicle-filter').val('ALL').text('All');
            });

            /**
             * Filter for Verification table
             *
             */ 
            $('#verification-filter').on('Core.DropdownButtonChange', function() {
                $verificationSearch.val('');
                Landmark.Verification.DataTables.verificationListTable.fnStandingRedraw();
            });

            /**
             * Filter for Verification table
             *
             */ 
            $('#verification-vehicle-filter').on('Core.DropdownButtonChange', function() {
                $verificationSearch.val('');
                Landmark.Verification.DataTables.verificationListTable.fnStandingRedraw();
            });
            
            /**
             * Export Verification table
             *
             */
            $body.on('click', '#popover-verification-list-export-confirm', function() {


            });                 
        
            $(document).on('click', '.col-landmark a', function() {

                var $self               = $(this),
                    $trNode             = $self.closest('tr'),
                    landmarkId          = $trNode.attr('id').split('-')[2],
                    data                = $trNode.data(),
                    $modal              = $('#modal-verification-list'),
                    $landmarkLocation   = $modal.find('.modal-location').eq(0)
                ;

                if (landmarkId != undefined) {
                    
                    $.ajax({
                        url: '/ajax/landmark/getVerifacationAddressByIds',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            territory_id: landmarkId
                        },
                        success: function(responseData) {
                            if (responseData.code === 0) {
                                var landmarkData = responseData.data;
                                var validation_error = responseData.validation_error;
                                
                                if (! $.isEmptyObject(validation_error)) {
                                    landmarkData.validation_error = validation_error;
                                }

                                Landmark.Common.DetailPanel.render(landmarkData);

                                Core.Dialog.launch('#'+$modal.prop('id'), landmarkData.territoryname, {
                                        width: '1080px'
                                    },
                                    {
                                        hidden: function() {
                                            Landmark.Common.DetailPanel.reset();
                                            if ($('#detail-panel').data('updateRow') != undefined && $('#detail-panel').data('updateRow') == true) {
                                                Landmark.Verification.DataTables.verificationListTable.fnStandingRedraw();    
                                            }
                                        },
                                        show: function() {
                                            var location = $self.closest('td').siblings('.col-location').html();
                                            $landmarkLocation.html('<span class="landmark-location-label">@ '+location+'</span>');
                                            $('.popover').find('.close').trigger('click');
                                            Landmark.Common.DetailPanel.render();
                                       },
                                       shown: function() {
console.log('initVerificationModal:shown');
                                           // show address in modal title
                                           var location = $self.closest('td').siblings('.col-location').html();
                                           $landmarkLocation.html('<span class="landmark-location-label">@ '+location+'</span>');
                                           Map.resize(Landmark.Common.map);
                                           Map.resetMap(Landmark.Common.map);
                                           Map.clearMarkers(Landmark.Common.map);
                                           if (landmarkData.latitude != '' && landmarkData.latitude != 0) {
                                               var markerOptions = {
                                                    id: landmarkData.territory_id,
                                                    name: landmarkData.territoryname,
                                                    latitude: landmarkData.latitude,
                                                    longitude: landmarkData.longitude,
                                                    click: function() {
                                                        Map.getVerificationLandmarkInfo(Landmark.Common.map, landmarkData.territory_id);
                                                    }
                                                },
                                                polygonOptions = {
                                                    type: landmarkData.shape,
                                                    radius: landmarkData.radius
                                                }
                                            ;
                                            Map.addMarkerWithPolygon(Landmark.Common.map, markerOptions, true, polygonOptions);
                                            Map.updateMarkerBound(Landmark.Common.map);
                                            Map.updateMapBound(Landmark.Common.map);
                                            Map.openInfoWindow(Landmark.Common.map, 'landmark', landmarkData.latitude, landmarkData.longitude, landmarkData);
                                            $(window).trigger('resize');
                                        }
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
        },

        DataTables: {
            init: function() {

                Landmark.Verification.DataTables.verificationListTable = Core.DataTable.init('verification-list-table', 20, {
                    'oLanguage': {
                        'sInfo': 'Showing _START_ to _END_ of _TOTAL_ Addresses'
                    },
                    //"aLengthMenu": [[20, 50, 100]],
                    //"sScrollY": "400px",
                    //"bScrollCollapse": true,
                    'bServerSide': true,
                    'sAjaxSource':   '/ajax/landmark/getFilteredVerificationList',
                    'aoColumns': [
                        { 'mDataProp': 'territoryname' },
                        { 'mDataProp': 'unitname' },
                        { 'mDataProp': 'formatted_address' },
                        { 'mDataProp': 'radius_in_miles' },
                        { 'mDataProp': 'verified' },
                        { 'mDataProp': 'formatted_verified_date' }
                    ],
                    'aoColumnDefs': [
                        { 'sClass': 'col-landmark no-wrap', 'aTargets': [0] },
                        { 'sClass': 'col-name',             'aTargets': [1] },
                        { 'sClass': 'col-location',         'aTargets': [2] },
                        { 'sClass': 'col-radius',           'aTargets': [3] },
                        { 'sClass': 'col-verified',         'aTargets': [4] },
                        { 'sClass': 'col-time',             'aTargets': [5] }
                    ],
                    'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
                        // set <a> tag name and add data-landmark-id attribute to tr
                        $('td:eq(0)', nRow).html('<a href="#">'+aData.territoryname+'</a>');
                        
                       // format verified status
                       var verifiedStatus = '<span class="label label-' + (aData.verified ? 'success' : 'danger') + '">' + (aData.verified ? 'Verified' : 'Not Verified') + '</span>'
                       $('td:eq(4)', nRow).html(verifiedStatus);

                        $(nRow).data('landmarkId', aData.territory_id)
                               .data('latitude', aData.latitude)
                               .data('longitude', aData.longitude);
                               
                        if (aData.formatted_address == '') {
                            $('td:eq(2)', nRow).text(aData.latitude + ' ' + aData.longitude);
                        }

                        return nRow;
                    },
                    'fnServerParams': function (aoData) {

                        var verified_status = $('#verification-filter').val();
                        var vehicle_filter  = $('#verification-vehicle-filter').val();
                        var landmark_type   = 'reference';
                        var search_string   = '';
                        var filter_type     = 'group_filter';

                        if (typeof(verified_status) != 'undefined') {
                            verified_status = verified_status;    
                        } else {
                            verified_status = 'All';
                        }

                        if (typeof(vehicle_filter) != 'undefined') {
                            vehicle_filter = vehicle_filter;    
                        } else {
                            vehicle_filter = 'All';
                        }

                        var searchVerificationString = $('#text-verification-search').val().trim();
                        if (typeof(searchVerificationString) != 'undefined' && searchVerificationString != '')
                        {
                            search_string   = searchVerificationString;
                            filter_type     = 'string_search';
                        }

                        aoData.push({name: 'verified', value: verified_status});
                        aoData.push({name: 'vehicle_id', value: vehicle_filter});
                        aoData.push({name: 'search_string', value: search_string});
                        aoData.push({name: 'territorytype', value: landmark_type});
                        aoData.push({name: 'filter_type', value: filter_type});
                    }
                });


            }
        }

 
    },

    Incomplete: {

        Reason: { // represents what needs to be fixed by the user
            '1': 'coords',
            '2': 'address',
            '3': 'name',
            '4': 'data',
            '5': 'geo',
            '6': 'rgeo'
        },


        initIncompleteModal: function() {
console.log('initIncompleteModal');

            $(document).on('click', '.col-incomplete-name a', function() {

                var $self               = $(this),
                    $trNode             = $self.closest('tr'),
                    landmarkId          = $trNode.attr('id').split('-')[2],
                    data                = $trNode.data(),
                    reason              = Landmark.Incomplete.Reason[data.incompleteReason],
                    category            = $trNode.find('.col-incomplete-category').text(),
                    $modal              = $('#modal-incomplete-location'),
                    $landmarkLocation   = $modal.find('.modal-location').eq(0)
                ;

                if (landmarkId != undefined) {
                    
                    $.ajax({
                        url: '/ajax/landmark/getIncompleteLandmarkByIds',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            territoryupload_id: landmarkId
                        },
                        success: function(responseData) {
                            if (responseData.code === 0) {
                                var landmarkData = responseData.data;
                                var validation_error = responseData.validation_error;
                                
                                if (! $.isEmptyObject(validation_error)) {
                                    landmarkData.validation_error = validation_error;
                                }

                                Landmark.Common.DetailPanel.render(landmarkData);

                                Core.Dialog.launch('#'+$modal.prop('id'), landmarkData.territoryname, {
                                        width: '1080px'
                                    },
                                    {
                                        hidden: function() {
                                            Landmark.Common.DetailPanel.reset();
                                            if ($('#detail-panel').data('updateRow') != undefined && $('#detail-panel').data('updateRow') == true) {
                                                Landmark.Incomplete.incompleteLocationTable.fnStandingRedraw();    
                                            }
                                        },
                                        show: function() {
                                            var location = $self.closest('td').siblings('.col-incomplete-address').html();
                                            $landmarkLocation.html('<span class="landmark-location-label">@ '+location+'</span>');
                                            $('.popover').find('.close').trigger('click');
                                            Landmark.Common.DetailPanel.render();
                                       },
                                       shown: function() {
console.log('initIncompleteModal:shown');
                                           $(window).trigger('resize');
                                           // show address in modal title
                                           var location = $self.closest('td').siblings('.col-incomplete-address').html();
                                           $landmarkLocation.html('<span class="landmark-location-label">@ '+location+'</span>');
                                           Map.resize(Landmark.Common.map);
                                           Map.resetMap(Landmark.Common.map);
                                           Map.clearMarkers(Landmark.Common.map);
                                           if (landmarkData.latitude != '' && landmarkData.latitude != 0) {
                                               var markerOptions = {
                                                    id: landmarkData.territory_id,
                                                    name: landmarkData.territoryname,
                                                    latitude: landmarkData.latitude,
                                                    longitude: landmarkData.longitude,
                                                    click: function() {
                                                        Map.getIncompleteLandmarkInfo(Landmark.Common.map, landmarkData.territory_id);
                                                    }
                                                },
                                                polygonOptions = {
                                                    type: landmarkData.shape,
                                                    radius: landmarkData.radius
                                                }
                                            ;
                                            Map.addMarkerWithPolygon(Landmark.Common.map, markerOptions, true, polygonOptions);
                                            Map.updateMarkerBound(Landmark.Common.map);
                                            Map.updateMapBound(Landmark.Common.map);
                                            Map.openInfoWindow(Landmark.Common.map, 'landmark', landmarkData.latitude, landmarkData.longitude, landmarkData);

                                        }
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
        },

        DataTables: {
            init: function() {

                Landmark.Incomplete.incompleteLocationTable = Core.DataTable.init('incomplete-list-table', 20, {
                    'oLanguage': {
                        'sInfo': 'Showing _START_ to _END_ of _TOTAL_ Incomplete Locations'
                    },
                    'bServerSide': true,
                    'sAjaxSource':   '/ajax/landmark/getIncompleteLandmarksList',
                    'aoColumns': [
                        { 'mDataProp': 'territoryname' },
                        { 'mDataProp': 'territorygroupname' },
                        { 'mDataProp': 'territorytype' },
                        { 'mDataProp': 'radius_in_miles' },
                        { 'mDataProp': 'formatted_address' },
                        { 'mDataProp': 'latitude' },
                        { 'mDataProp': 'longitude' },
                        { 'mDataProp': 'reason' }
                    ],
                    'aaSorting': [
                        [0,'asc'],
                        [1,'asc']
                    ],
                    'aoColumnDefs': [
                        { 'sClass': 'col-incomplete-name no-wrap',    'aTargets': [0] },
                        { 'sClass': 'col-incomplete-group no-wrap',    'aTargets': [1] },
                        { 'sClass': 'col-incomplete-category no-wrap',  'aTargets': [2] },
                        { 'sClass': 'col-incomplete-radius no-wrap', 'aTargets': [3] },
                        { 'sClass': 'col-incomplete-address no-wrap',  'aTargets': [4] },
                        { 'sClass': 'col-incomplete-lat no-wrap',  'aTargets': [5] },
                        { 'sClass': 'col-incomplete-long no-wrap',  'aTargets': [6] },
                        { 'sClass': 'col-incomplete-reason no-wrap',  'aTargets': [7] }
                    ],
                    'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
                        // set <a> tag name and add data-landmark-id attribute to tr
                        if (aData.territoryname != '') {
                            $('td:eq(0)', nRow).html('<a href="#">'+aData.territoryname+'</a>');
                        } else {
                            $('td:eq(0)', nRow).html('<a href="#">-</a>');
                        }
                        
                        $(nRow).data('landmarkId', aData.territory_id)
                               .data('latitude', aData.latitude)
                               .data('longitude', aData.longitude)
                               .data('incompleteReason', aData.reason);

                        return nRow;
                    },
                    'fnServerParams': function (aoData) {

                        var territorygroup_id       = $('#select-landmark-group-filter').val().trim();
                        var territorytype           = $('#select-landmark-attribute-filter').val().trim();
                        var territory_reason         = $('#select-landmark-reason-filter').val().trim();
                        var search_string           = '';
                        var filter_type             = 'group_filter';
                        var searchLandmarkString    = $('#text-landmark-search').val().trim();

                        if (typeof(searchLandmarkString) != 'undefined' && searchLandmarkString != '')
                        {
                            search_string           = searchLandmarkString;
                            territorygroup_id        = 'All';
                            territory_state_status   = 'All';
                            filter_type             = 'string_search';
                        }

                        aoData.push({name: 'territorygroup_id', value: territorygroup_id});
                        aoData.push({name: 'search_string', value: search_string});
                        aoData.push({name: 'territorytype', value: territorytype});
                        aoData.push({name: 'filter_type', value: filter_type});
                        aoData.push({name: 'territory_reason', value: territory_reason});
                    }
                });


            }
        }

    },

    Group: {

        DataTable: {

            init: function() {
                Landmark.Group.DataTable.landmarkGroupListTable = Core.DataTable.init('landmark-group-list-table', 20, {
                    'oLanguage': {
                        'sInfo': 'Showing _START_ to _END_ of _TOTAL_ Landmark Groups'
                    },
                    'bServerSide': true,
                    'sAjaxSource':   '/ajax/landmark/getLandmarkGroupList',
                    'aoColumns': [
                        { 'mDataProp': 'territorygroupname' },
                        { 'mDataProp': 'territory_count' }
                    ],
                    'aoColumnDefs': [
                        { 'sClass': 'col-name no-wrap', 'aTargets': [0] },
                        { 'sClass': 'col-count',            'aTargets': [1] }
                    ],
                    'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
                        // set <a> tag name and add data-landmark-id attribute to tr
                        $('td:eq(0)', nRow).html('<a href="#">'+aData.territorygroupname+'</a>');
                       
                        //$(nRow).data('landmarkId', aData.territory_id);

                        return nRow;
                    },
                    'fnServerParams': function (aoData) {
                        var filter_type                 = 'string_search';
                        var searchLandmarkGroupString   = $('#text-landmark-group-search').val().trim();
                        var search_string = '';

                        if (typeof(searchLandmarkGroupString) != 'undefined' && searchLandmarkGroupString != '')
                        {
                            search_string           = searchLandmarkGroupString;
                            filter_type             = 'string_search';
                        }

                        aoData.push({name: 'string_search', value: search_string});
                        aoData.push({name: 'filter_type', value: filter_type});
                    }
                });
            }

        },

        Modal: {

            init: function() {
                $(document).on('click', '.col-name a', function() {

                    var $self = $(this),
                        $trNode = $self.closest('tr'),
                        landmarkGroupId = $trNode.attr('id').split('-')[2],
                        $modal = $('#modal-landmark-group-list'),
                        $landmarkGroupAssignment     = $('#landmark-group-assignment'),
                        $landmarkGroupAvailableList  = $landmarkGroupAssignment.find('.drag-drop-available'),
                        $landmarkGroupAssignedList   = $landmarkGroupAssignment.find('.drag-drop-assigned')
                    ;

                    if (landmarkGroupId != undefined) {
                        $.ajax({
                            url: '/ajax/landmark/getLandmarkGroupInfo',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                group_id: landmarkGroupId
                            },
                            success: function(responseData) {
                                if (responseData.code === 0) {
                                    var landmarkgroupdata = responseData.data.landmarkgroup_data;
                                    if (! $.isEmptyObject(landmarkgroupdata)) {
                                        Core.Dialog.launch('#'+$modal.prop('id'), landmarkgroupdata.territorygroupname, {
                                            width: '1000px'
                                        }, 
                                        {
                                            hidden: function() {
                                                // redraw vehicle group list table
                                                Landmark.Group.DataTable.landmarkGroupListTable.fnStandingRedraw();
                                                
                                                // hide 'More Options' section
                                                var $toggle = $('#landmark-group-more-options-toggle').find('small');
                                                if ($toggle.text() == 'Show Less Options') {
                                                    $toggle.trigger('click');    
                                                }

                                                // destroy any DragDrop containers after closing modal
                                                Core.DragDrop.destroy();                                                
                                            },
                                            hide: function() {
                                                // close filter popover
                                                $('#popover-available-filter-cancel').trigger('click');
                                                // clear available and assigned landmark list
                                                $($landmarkGroupAvailableList.selector+','+$landmarkGroupAssignedList.selector).html('');                                                
                                            },
                                            shown: function() {

                                                var $detailPanel   = $('#detail-panel'),
                                                    $moreOptions   = $('#landmark-group-more-options-toggle'),
                                                    $hideIfDefault = $detailPanel.find('.hide-if-default'),
                                                    defaultGroupId = responseData.data.defaultgroup_id != undefined ? responseData.data.defaultgroup_id : 0
                                                ;
                                            
                                                $detailPanel.find('.hook-editable-keys').data('landmarkGroupPk', landmarkGroupId).data('landmarkDefaultGroupId', defaultGroupId);

                                                // Don't allow editing of default group or group that do not have any available territories
                                                if (landmarkgroupdata.territorygroupname.toLowerCase() == 'default' || defaultGroupId == 0) {
                                                    if (landmarkgroupdata.territorygroupname.toLowerCase() == 'default') {
                                                        $('#not-editable-group-name').show();
                                                        
                                                        $('#editable-group-name').add($moreOptions)
                                                                                 .add($hideIfDefault)
                                                                                 .hide();
                                                    } else {
                                                        $('#not-editable-group-name').hide();
                                                        $('#editable-group-name').show();
                                                        $moreOptions.show();
                                                        $hideIfDefault.hide();
                                                        Core.Editable.setValue($('#landmark-group-name'), landmarkgroupdata.territorygroupname);
                                                    }
                                                    
                                                    $detailPanel.closest('.modal-dialog').css('width', '666px');
                                                } else {
                                                    $('#not-editable-group-name').hide();
                                                    $('#editable-group-name').add($moreOptions)
                                                                             .add($hideIfDefault)
                                                                             .show()
                                                    ;
                                                    Core.Editable.setValue($('#landmark-group-name'), landmarkgroupdata.territorygroupname);
                                                }

                                                // create assigned landmark list
                                                if (! $.isEmptyObject(landmarkgroupdata.assigned_territories)) {
                                                    var assigned_landmarks = landmarkgroupdata.assigned_territories;

                                                    $.each(assigned_landmarks, function() {
                                                        $landmarkGroupAssignedList.append(Core.DragDrop._generateGroupMarkup(this.territory_id, this.territoryname));
                                                    });
                                                }
                                                
                                                // create available landmark list
                                                if (! $.isEmptyObject(landmarkgroupdata.available_territories)) {
                                                    var available_landmarks = landmarkgroupdata.available_territories;

                                                    $.each(available_landmarks, function() {
                                                        $landmarkGroupAvailableList.append(Core.DragDrop._generateGroupMarkup(this.territory_id, this.territoryname));
                                                    });
                                                }

                                                var $landmarkGroupButton = $('#move-to-group'),
                                                    $landmarkGroupDropdown = $landmarkGroupButton.siblings('ul').eq(0)
                                                ;
                                                                                                
                                                // populate territorygroup dropdown
                                                if (! $.isEmptyObject(responseData.data.territory_groups)) {
                                                    var territoryGroups = responseData.data.territory_groups,
                                                        lastIndex = territoryGroups.length - 1,
                                                        html = ''
                                                    ;
                                                    
                                                    $.each(territoryGroups, function(key, value) {
                                                        html += '<li><a data-value="'+value.territorygroup_id+'">'+value.territorygroupname+'</a></li>'
                                                        if (key == lastIndex) {
                                                            $landmarkGroupDropdown.html(html);
                                                        }
                                                    });
                                                    
                                                    $landmarkGroupButton.text('Select One').siblings('button').removeClass('disabled');
                                                } else {
                                                    $landmarkGroupDropdown.html('');
                                                    $landmarkGroupButton.text('No Groups Available').siblings('button').addClass('disabled');
                                                }                                                
    
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
                                    Core.SystemMessage.show(responseData.message, responseData.code);
                                }
                            }
                        });
                    }


                });
            }
        },

        Edit: {

            init: function() {

                // Group Assignment
                $('#landmark-group-assignment').on('Core.DragDrop.Dropped', function(event, extraParams) {

                    if (! $.isEmptyObject(extraParams)) {
                        var $self                       = $(this),
                            $detailPanelHook            = $('#detail-panel').find('.hook-editable-keys'),
                            updatedItems                = extraParams.updatedItems.items,
                            method                      = (extraParams.updatedItems.inAssignedGroup === true) ? 'addLandmarksToGroup' : 'removeLandmarksFromGroup',
                            landmarkGroupId             = $detailPanelHook.data('landmarkGroupPk') || 0,
                            defaultGroupId              = $detailPanelHook.data('landmarkDefaultGroupId') || 0
                        ;
                        
                        if (method == 'removeLandmarksFromGroup') {
                            landmarkGroupId = defaultGroupId;
                        }

                        if (updatedItems != undefined && updatedItems.length != 0 && landmarkGroupId != undefined && landmarkGroupId != 0) {

                            $.ajax({
                                url: '/ajax/landmark/'+method,
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    landmarkgroup_id: landmarkGroupId,
                                    landmarks: updatedItems
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
                });

                // More Options
                var $optionsToggle = $('#landmark-group-more-options-toggle'),
                    $toggleLabel   = $optionsToggle.find('small')
                ;

                $optionsToggle.on('click', function() {

                    if ($toggleLabel.text() == 'Show More Options') {
                        $toggleLabel.text('Show Less Options');
                    } else {
                        $toggleLabel.text('Show More Options');
                    }

                    $('#landmark-group-more-options').slideToggle(300);

                });
                
                // Search Available Landmarks By Name on left hand side
                var $availableLandmarkSearch     = $('#filter-available-text');
                var $availableLandmarkSearchGo   = $('#filter-available-go');

                $(document).on('keyup', $availableLandmarkSearch.selector, function () {
                    // get current search string
                    var searchlandmarkstring = $availableLandmarkSearch.val().trim();

                    if (searchlandmarkstring.length > 1 || searchlandmarkstring.length == 0) {
                        Landmark.Group.Edit.getFilteredAvailableLandmarks(searchlandmarkstring, 'available');
                    }
                });
                
                $(document).on('click', $availableLandmarkSearchGo.selector, function () {
                    // get current search string
                    var searchlandmarkstring = $availableLandmarkSearch.val().trim();

                    if (searchlandmarkstring != '') {
                        Landmark.Group.Edit.getFilteredAvailableLandmarks(searchlandmarkstring, 'available');
                    }
                });

                // Search Available Landmarks By Name on right hand side
                var $assignedLandmarkSearch     = $('#filter-assigned-text');
                var $assignedLandmarkSearchGo   = $('#filter-assigned-go');

                $(document).on('keyup', $assignedLandmarkSearch.selector, function () {
                    // get current search string
                    var searchlandmarkstring = $assignedLandmarkSearch.val().trim();

                    if (searchlandmarkstring.length > 1 || searchlandmarkstring.length == 0) {
                        Landmark.Group.Edit.getFilteredAvailableLandmarks(searchlandmarkstring, 'assigned');
                    }
                });
                
                $(document).on('click', $assignedLandmarkSearchGo.selector, function () {
                    // get current search string
                    var searchlandmarkstring = $assignedLandmarkSearch.val().trim();

                    if (searchlandmarkstring != '') {
                        Landmark.Group.Edit.getFilteredAvailableLandmarks(searchlandmarkstring, 'assigned');
                    }
                });
            },
            
            getFilteredAvailableLandmarks: function(searchString, groupColumn) {
                
                searchString = searchString || '';
                groupColumn = groupColumn || '';
                searchFromGroupId = 0;

                var $landmarkGroupAssignment = $('#landmark-group-assignment')
                if (groupColumn == 'available') {
                    $searchGroupList  = $landmarkGroupAssignment.find('.drag-drop-available');
                    searchFromGroupId = $('#detail-panel').find('.hook-editable-keys').data('landmarkDefaultGroupId');
                } else {
                    $searchGroupList  = $landmarkGroupAssignment.find('.drag-drop-assigned');
                    searchFromGroupId = $('#detail-panel').find('.hook-editable-keys').data('landmarkGroupPk');
                }

                $.ajax({
                    url: '/ajax/landmark/getFilteredAvailableLandmarks',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        search_string: searchString,
                        landmarkgroup_id: searchFromGroupId
                    },
                    success: function(responseData) {
                        if (responseData.code === 0) {
                            // clear available
                            $($searchGroupList.selector).html('');
                                            
                            // create available vehicle list
                            if (! $.isEmptyObject(responseData.data.landmarks)) {
                                var available_landmarks = responseData.data.landmarks;
                                $.each(available_landmarks, function() {
                                    $searchGroupList.append(Core.DragDrop._generateGroupMarkup(this.territory_id, this.territoryname));
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
                 * Add landmark group
                 **/                
                $(document).on('click', '#popover-new-landmark-group-confirm', function() {
                    var $modal = $('#modal-landmark-group-list'),
                        $groupName = $('#landmark-group-name-new'),
                        groupName = $groupName.val()
                    ;
            
                    if (groupName != '' && groupName != undefined) {
                        $.ajax({
                            url: '/ajax/landmark/addLandmarkGroup',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                landmarkgroupname: groupName
                            },
                            success: function(responseData) {
                                if (responseData.code === 0) {
                                    // close popover
                                    $('#popover-new-landmark-group-cancel').trigger('click');
                                    
                                    // on success, redraw the verification table
                                    Landmark.Group.DataTable.landmarkGroupListTable.fnStandingRedraw();    
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
                        alert('Landmark Group Name cannot be blank');
                    }
                });
                
                /**
                 * Delete landmark group
                 **/                
                $(document).on('click', '#popover-landmark-group-delete-confirm', function() {
                    var $modal = $('#modal-landmark-group-list'),
                        landmarkGroupId = $('#detail-panel').find('.hook-editable-keys').data('landmarkGroupPk')
                    ;
            
                    if (landmarkGroupId != '' && landmarkGroupId != undefined) {
                        $.ajax({
                            url: '/ajax/landmark/deleteLandmarkGroup',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                landmarkgroup_id: landmarkGroupId
                            },
                            success: function(responseData) {
                                if (responseData.code === 0) {
                                    // close popover
                                    $('#popover-user-landmark-group-cancel').trigger('click');
                                
                                    // close modal
                                    $modal.find('.modal-footer button').eq(0).trigger('click');
                                    
                                    // on success, redraw the verification table
                                    Landmark.Group.DataTable.landmarkGroupListTable.fnStandingRedraw();    
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
                        alert('Invalid landmark group ID');
                    }
                });

                // Move assigned landmarks to another group
                $(document).on('click', '#popover-move-to-group-confirm', function() {

                    var $selectedAssignLandmarks    = $('.drag-drop-assigned').find('.drag-drop-item').filter('.active');
                    var landmarkGroupId             = $('#move-to-group').val();
                    var movelandmarks               = new Array();
                    var transferlandmarks           = new Array();

                    $selectedAssignLandmarks.each(function(index) {
                        var landmark_id = $(this).data('id');
                        saveLandmark = {id: landmark_id};
                        movelandmarks.push(saveLandmark);
                        transferlandmarks.push(landmark_id);
                    });

                    if (movelandmarks != undefined && movelandmarks.length != 0 && landmarkGroupId != undefined && landmarkGroupId != 0) {

                        $.ajax({
                            url: '/ajax/landmark/addLandmarksToGroup',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                landmarkgroup_id: landmarkGroupId,
                                landmarks: movelandmarks
                            },
                            success: function(responseData) {
                                if (responseData.code === 0) {

                                    // remove the failed groups from their destination and add them back to their origin
                                    $.each(transferlandmarks, function(key,value) {
                                        $('.drag-drop-assigned li').filter('[data-id="'+value+'"]').detach();
                                    });
                                    
                                    $('#popover-move-to-group-cancel').trigger('click');
                                    
                                    $('#move-to-group').val(0).text('Select One');
                                    
                                    Landmark.Group.DataTable.landmarkGroupListTable.fnStandingRedraw();

                                }
                                
                                if (! $.isEmptyObject(responseData.message)) {
                                    Core.SystemMessage.show(responseData.message, responseData.code);
                                }
                            }
                        });
                    } else {
                        var alertmessage = '';
                        if (movelandmarks == undefined || movelandmarks.length == 0) {
                            alertmessage = '\n     -assigned landmark';
                        }
                        if (landmarkGroupId == undefined || landmarkGroupId == 0) {
                            alertmessage = alertmessage + '\n     -destination landmark group';
                        }
                        alert('Missing:'+alertmessage);
                    }
                });
                
                // Canceling move to group popover
                $(document).on('click', '#popover-move-to-group-cancel', function() {

                    $('#move-to-group').val(0).text('Select One');
                });
                
                /**
                 * Reset Available Landmarks filter
                 */
                $(document).on('click', '#popover-available-filter-reset', function() {
                    $('#filter-available-text').val('');
                    Landmark.Group.Edit.getFilteredAvailableLandmarks('');
                });
    
                /**
                 * Reset Add Landmark Group Popover
                 **/
                $('#popover-landmark-group-new').on('hidden.bs.popover', function() {
                    $('#landmark-group-name-new').val('')        
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

    initAddLandmarkMapModal: function() {

        var $mapModal = $('#modal-landmark-map')
        ;

        $mapModal.on('Landmark.MapModalShow', function() {

            Core.Dialog.launch('#'+$mapModal.prop('id'), '', {
                width:    '500px',
                height:   '500px',
                backdrop: true,
                keyboard: false
            }, {

            });

        });

        $mapModal.on('Landmark.MapModalHide', function() {

            $mapModal.modal('hide');

        });
    },
        
   
    
    Common: {

        addmap: undefined,
        map: undefined,
        
        initMap: function() {
console.log('initMap');

            Landmark.Common.map = Map.initMap('map-div', {zoom: 5});

            switch(Core.Environment.context()){
                
                case         'landmark/group' : 
                case  'landmark/verification' : break;

                                      default : Landmark.Common.addmap = Map.initMap('addmap-div', {zoom: 5});

            }

            // Landmark.Common.addmap.on('click', function(e) {
            //     // Zoom exactly to each double-clicked point
            //     Core.AddMap.LatLng(e.latlng);
            // });        

        },

        SecondaryPanel: {

            init: function() {
console.log('SecondaryPanel:init');

                var $detailPanelTriggers    = $('.sub-panel-items, .any-other-triggers-may-go-here'),
                    $subPanelItems          = $('.sub-panel-items')
                ;

                /**
                 *
                 * Clicking on the Edit Icon in the Secondary Panel
                 *
                 * handles opening of detail panel
                 *
                 */
                $detailPanelTriggers.on('click', 'span.glyphicon', function(event) {

                    event.stopPropagation();

                    var $self               = $(this),
                        $selectedItem       = $self.closest('li'),
                        selectedItemId      = $selectedItem.attr('id').split('-')[2],
                        autoOpenDetailPanel = true
                    ;

                    if ( ! $selectedItem.is('.active')) {
                        $selectedItem.trigger('click', {hideLabel: true, autoCloseDetailPanel: false});
                    } else {
                        if (Core.Environment.context() == 'landmark/map') {
                            var $detailPanel        = $('#detail-panel'),
                                $detailPanelHooks   = $detailPanel.find('.hook-editable-keys').eq(0)
                            ;
                            
                            // if the detail panel already has data for this unit, just open the detail panel again (no need for ajax)
                            if (! $detailPanel.is('.open')) {
                                if ($detailPanelHooks.length > 0) {
                                    if ($detailPanelHooks.data('landmarkPk') == selectedItemId) {
console.log('SecondaryPanel:init:Landmark.Common.DetailPanel.open');
                                        Landmark.Common.DetailPanel.open(function() {
                                            Map.resize(Landmark.Common.map);
                                            Map.showHideLabel(Landmark.Common.map, selectedItemId, false);
                                            Map.clickMarker(Landmark.Common.map, selectedItemId);
                                        });
                                        
                                        // set auto open detail panel to false
                                        autoOpenDetailPanel = false;
                                    } 
                                }
                            }
                        } else if (Core.Environment.context() == 'landmark/list') {
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
                * Hovering over a Landmark in the Secondary Panel
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
                $('#landmark-toggle-all').click(function() {

                    var $subPanelItems  = $('.sub-panel-items'),
                        $nonActiveItems = $subPanelItems.find('li').filter(':not(.active)'),
                        lastIndex       = $nonActiveItems.length - 1,
                        landmarkIds     = new Array(),
                        landmarkId      = '',
                        $detailPanel    = $('#detail-panel')
                    ;

                    $('#secondary-panel-pagination').data('drawMarker', 'yes');

                    $nonActiveItems.each(function(key, value) {

                        landmarkId = $(this).attr('id').split('-')[2];
                        
                        if (landmarkId != undefined && landmarkId != '') {
                            landmarkIds.push(landmarkId);
                        }

                        // make ajax call to get the units' event after the last unit id has been processed 
                        if ((key == lastIndex) && (landmarkIds.length > 0)) {   
                            $.ajax({
                                url: '/ajax/landmark/getLandmarkByIds',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    territory_id: landmarkIds
                                },
                                success: function(responseData) {
console.log('SecondaryPanel:init:landmark-toggle-all');
                                    if (responseData.code === 0) {
                                        if (responseData.data.length > 0) {

                                            // close detail panel and info window
                                            Map.closeInfoWindow(Landmark.Common.map);
                                            
                                            if ($detailPanel.is('.open')) {     // if the detail panel is opened, perform map-related tasks only after closing panel
                                                var singleItemId = $detailPanel.find('.hook-editable-keys').eq(0).data('landmarkPk');
                                                $('#hide-landmark-panel').trigger('click', function() {
                                                    // show the current unit's label
                                                    Map.showHideLabel(Landmark.Common.map, singleItemId, true);
                                                    // resize map after closing detail panel
                                                    Map.resize(Landmark.Common.map);
                                                    Map.addMarkers(Landmark.Common.map, 'landmark', responseData.data);
                                                });                                                
                                            } else {                            // else, simply perform map-related tasks
                                                Map.addMarkers(Landmark.Common.map, 'landmark', responseData.data);
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
                });

                /**
                 *
                 * Clicking Select - None
                 *
                 * selects all landmarks
                 *
                 */
                $('#landmark-toggle-none').click(function() {
console.log('SecondaryPanel:init:landmark-toggle-none');

                    var $activeItems = $('.sub-panel-items').find('li').filter('.active'),
                        $detailPanel = $('#detail-panel')
                    ;
                    
                    $('#secondary-panel-pagination').data('drawMarker', '');
                    
                    $activeItems.removeClass('active');

                    Map.clearMarkers(Landmark.Common.map);
                    
                    $('#hide-landmark-panel').trigger('click', function() {
                        Map.resetMap(Landmark.Common.map);
                    });
                    
                    
                    // remove any temp landmarks (NTD: fix the issue where it also removes temp landmark created from 'Add Landmark')
console.log("................................................................................. Map.doesTempLandmarkExist(map)");
                    if (Map.doesTempLandmarkExist(Landmark.Common.map)) {
                        Map.removeTempLandmark(Landmark.Common.map);
                    }
                    
                    Map.removeMapClickListener(Landmark.Common.map);
                });
               

                /**
                 *
                 * Clicking on a Landmark in Secondary Panel
                 *
                 * handles showing which vehicle is selected in the sidebar
                 *
                 * */
                $subPanelItems.on('click', 'li', function(event, extraParams) {
                    var $self = $(this);
                    currentLandmarkIdHidePanel='';
                    $self.closest('ul').find('li').each( function() {
                        $(this).removeClass('active');
                    });
                    $self.addClass('active');
                    $('#landmark-method2').val('manual-entry');
                    $('.leaflet-popup-pane').show();
                    var map     = Landmark.Common.getCurrentMap();
                    if (map) {
                        Map.removeTempLandmark(map);
                    }
                    setTimeout("$('#refresh-map-markers').trigger('click')",1);
                });

              /**
                 *
                 * Clicking on a Landmark in Secondary Panel
                 *
                 * handles showing landmark on the map and open Detail Panel if only a single landmark is selected
                 *
                 * */
                $('#refresh-map-markers').on('click', function() {

                    var $self                   = $(this),
                        autoOpenDetailPanel     = true,
                        updateMapBound          = true,
                        hideLabel               = true,
                        autoCloseDetailPanel    = true,
                        $detailPanel            = $('#detail-panel'),
                        landmarkIds = [],
                        landmarkId = ''
                    ;

                    $('#secondary-sidebar-scroll').find('li').filter('.active').each(function(){
                        $self = $(this);
                        landmarkId = $self.attr('id').split('-')[2];
                        landmarkIds.push($self.attr('id').split('-')[2]);
                    });

                    currentLandmarkId = landmarkId;

//                     if (extraParams != undefined && ! $.isEmptyObject(extraParams)) {
                    
//                         //  autoOpenDetailPanel - indicates if we want to auto open detail panel anytime there is only one vehicle selected (defaults to true)
//                         if (extraParams.autoOpenDetailPanel != undefined) {
//                             autoOpenDetailPanel = extraParams.autoOpenDetailPanel;
//                         }
                        
//                         //  updateMapBound - indicates if map bound needs to be updated (use for when selecting ALL markers)
//                         if (extraParams.updateMapBound != undefined) {
//                             updateMapBound = extraParams.updateMapBound;
//                         }
                        
//                         //  hideLabel - indicate if marker label will be hidden when creating marker on map
//                         if (extraParams.hideLabel != undefined) {
//                             hideLabel = extraParams.hideLabel;
//                         }
                        
//                         // autoCloseDetailPanel - indicates if detail panel should auto close when there is more than one vehicle selected (defaults to true)
//                         if (extraParams.autoCloseDetailPanel != undefined) {
//                             autoCloseDetailPanel = extraParams.autoCloseDetailPanel;
//                         }
//                     }                    

//                     switch(uid_toggle){

//                         case        'all' : autoOpenDetailPanel = false;
//                                             autoCloseDetailPanel = true;
//                                             break;

//                         case    'uid-all' : uid_toggle='all';
//                                             $self.closest('ul').find('li').each( function() {
//                                                 $(this).addClass('active');
//                                             });
//                                             autoOpenDetailPanel = false;
//                                             autoCloseDetailPanel = true;
//                                             break;

//                         case   'uid-none' : $self.closest('ul').find('li').each( function() {
//                                                 $self.removeClass('active');
//                                             });
//                                             landmarkId='skip';
//                                             break;

//                                   default : 
// console.log('uid_toggle:'+uid_toggle+':landmarkId:'+landmarkId);
//                                             //show vehicle as being selected
//                                             $self.toggleClass('active');
//                                             if(!($subPanelItems.find('li').not($self).filter('.active'))){
//                                                 unitId='skip';
//                                             } else if(!($self.filter('.active'))){
// console.log('Map.removeMarker(Landmark.Common.map, landmarkId)');
//                                                 Map.removeMarker(Landmark.Common.map, landmarkId);
//                                             }

//                     }
                    
// console.log('landmarkId:'+landmarkId);
//                     if (landmarkId == 'skip') {

//                         Core.Map.Refresh('Landmark.Common.map','1',1);

//                     } else if (landmarkId != undefined) {
//                         if ($self.is('.active')) {   //  if item was just selected
//                             var selectedItems = $subPanelItems.find('li').not($self).filter('.active'),     //  get all list items that are currently active (excluding self)
//                                 singleItemId = ''
//                             ;

//                             if (selectedItems.length >= 1 || ((selectedItems.length == 0) && (! autoOpenDetailPanel))) {  // only get event info if more than one vehicle is currently selected (includes select ALL markers)
                                
//                                 if (selectedItems.length == 1) {   //  only one item is current
//                                     singleItemId = selectedItems.attr('id').split('-')[2];
//                                     $('#secondary-panel-pagination').data('drawMarker', '');

//                                     // remove temp landmark and map click listener
//                                     Map.removeMapClickListener(Landmark.Common.map);
//                                     if (Map.doesTempLandmarkExist(Landmark.Common.map)) {
//                                         Map.removeTempLandmark(Landmark.Common.map);
//                                     }
                                    
//                                     // show previous marker if hidden due to draw
//                                     Map.showMarker(Landmark.Common.map, singleItemId);
//                                 }
                                
//                                 if (selectedItems.length == 2) {
//                                     Map.showHideAllLabels(Landmark.Common.map, true);
//                                 }
                                
//                                 if (hideLabel == undefined) {
//                                     hideLabel = false;
//                                 }
//                             } else if (autoOpenDetailPanel) {                                                           // get vehicle info and open detail panel for single vehicle
                                
//                                 if (hideLabel == undefined) {
//                                     hideLabel = true;
//                                 }
//                             }

                            $.ajax({
                                url: '/ajax/landmark/getLandmarkByIds',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    territory_id: landmarkId,
                                   territory_ids: landmarkIds
                                },
                                success: function(responseData) {
console.log('SecondaryPanel:init:$ajax:/ajax/landmark/getLandmarkByIds:'+landmarkId+':'+landmarkIds);
                                    if (responseData.code === 0) {
                                        var landmarkData = responseData.data;

                                        // Update the Map  (if in vehicle/map context)
                                        if (Core.Environment.context() == 'landmark/map') {
                                            if (! $.isEmptyObject(landmarkData)) {
                                                var markerOptions = {
                                                        id: landmarkData.territory_id,
                                                        name: landmarkData.territoryname,
                                                        latitude: landmarkData.latitude,
                                                        longitude: landmarkData.longitude,
                                                        click: function() {
                                                            Map.getLandmarkInfo(Landmark.Common.map, landmarkData.territory_id);
                                                        }
                                                    },
                                                    polygonOptions = {
                                                        type: landmarkData.shape,
                                                        radius: landmarkData.radius,
                                                        points: landmarkData.coordinates
                                                    }
                                                ;
console.log("Map.addMarkerWithPolygon(Landmark.Common.map, markerOptions, hideLabel, polygonOptions)");
// console.log(markerOptions);
// console.log(polygonOptions);
                                                Map.clearMarkers(Landmark.Common.map);

                                                if((landmarkData.latitude!=0)&&(landmarkData.longitude!=0)){

                                                    Map.addMarkerWithPolygon(Landmark.Common.map, markerOptions, hideLabel, polygonOptions);

                                                    $self.data('latitude', landmarkData.latitude)
                                                         .data('longitude', landmarkData.longitude);

                                                    lastPolygon = landmarkData.coordinates ;

                                                    $('#landmark-li-'+landmarkId).attr('data-shape',landmarkData.shape);
                                                    $('#landmark-li-'+landmarkId).attr('data-radius',landmarkData.radius);
                                                    $('#landmark-li-'+landmarkId).attr('data-points',landmarkData.coordinates);
                                                    $('#landmark-li-'+landmarkId).attr('data-lat',landmarkData.latitude);
                                                    $('#landmark-li-'+landmarkId).attr('data-long',landmarkData.longitude);
                                                    $('#landmark-li-'+landmarkId).attr('data-event-id',landmarkData.territory_id);
                                                    $('#landmark-li-'+landmarkId).attr('data-event',landmarkData.territoryname);

                                                }
                                        
                                            }
                                                                                            
                                            // // if (selectedItems.length > 0) {     //	create marker only (more than one vehicle is current selected - excluded self) 
                                                
                                            //     Map.closeInfoWindow(Landmark.Common.map);
                                                
                                            //     if (singleItemId != '') {
                                            //         Map.showHideLabel(Landmark.Common.map, singleItemId, true);
                                            //     }
                                                
                                            //     if ($detailPanel.is('.open')) {
                                            //         if (! autoCloseDetailPanel) {   // if autoCloseDetailPanel is false, do not close the detail panel (prevent unnecessary animation)
                                            //             Landmark.Common.DetailPanel.reset();                                                           
                                            //             if (updateMapBound) {
                                            //                 Map.updateMapBound(Landmark.Common.map);
                                            //             }                                                           
                                            //         } else {                        // else close the detail panel and resize map
                                            //             $('#hide-landmark-panel').trigger('click', function() {
                                            //                 Map.resize(Landmark.Common.map);
                                            //                 if (updateMapBound) {
                                            //                     Map.updateMapBound(Landmark.Common.map);
                                            //                 }
                                            //             });
                                            //         }
                                            //     } else {
                                            //         if (updateMapBound) {
                                            //             Map.updateMapBound(Landmark.Common.map);                                                    
                                            //         }                                                        
                                            //     }
                                            // } else {	//	create marker, open info window, and open detail vehicle tab (single landmark was selected)


                                            if(landmarkId!=currentLandmarkIdHidePanel){
console.log(')))))))))))))))))))))))))))))))))))))))))))))))))))))))))))))))))))))))) landmarkId:'+landmarkId+'!=currentLandmarkIdHidePanel:'+currentLandmarkIdHidePanel);
                                                // Vehicle.Common.DetailPanel.render(unitdata);
                                                Landmark.Common.DetailPanel.render(landmarkData);
                                            }
                                            Map.openInfoWindow(Landmark.Common.map, 'landmark', landmarkData.latitude, landmarkData.longitude, landmarkData);                                                        
                                            
                                            // Core.Viewport.adjustLayout();

                                            Core.Map.Refresh('Landmark.Common.map','',1);

                                                // // Landmark.Common.DetailPanel.render(landmarkData, function() {
                                                //     Map.resize(Landmark.Common.map);
                                                //     Map.updateMarkerBound(Landmark.Common.map);
                                                //     Map.updateMapBound(Landmark.Common.map);
                                                //     if (! $.isEmptyObject(landmarkData)) {
                                                //         Map.openInfoWindow(Landmark.Common.map, 'landmark', landmarkData.latitude, landmarkData.longitude, landmarkData);                                                        
                                                //     } else {
                                                //         Map.resetMap(Landmark.Common.map);
                                                //     }
                                                // });                                                    
                                            // }
                                        }

                                        // In vehicle/list context
                                        if (Core.Environment.context() == 'landmark/list') {
                                          // do stuff
                                        }                                                
                                    } else {
                                        if ($.isEmptyObject(responseData.validation_error, responseData.code) === false) {
                                            //	display validation errors
                                        }
                                    }
                                    
                                    if ($.isEmptyObject(responseData.message) === false) {
                                        //	display message
                                        Core.SystemMessage.show(responseData.message, responseData.code);
                                    }
                                }
                            });
                        // } else if (Core.Environment.context() == 'landmark/map') {       // else if item was just unchecked 
                        //     /**
                        //      *
                        //      * Deselect Vehicle (if in vehicle/map context)
                        //      *
                        //      * */

                        //     Map.removeMarker(Landmark.Common.map, landmarkId);

                        //     Core.Map.Refresh('Landmark.Common.map','',1);

                        //     // Map.updateMarkerBound(Landmark.Common.map);
                            
                        //     // if (updateMapBound) {
                        //     //     Map.updateMapBound(Landmark.Common.map);
                        //     // }

                        //     // remove temp landmark and map click listener
                        //     Map.removeMapClickListener(Landmark.Common.map);
                        //     if (Map.doesTempLandmarkExist(Landmark.Common.map)) {
                        //         Map.removeTempLandmark(Landmark.Common.map);
                        //     }
                            
                        //     $self.data('latitude', null)
                        //          .data('longitude', null);

                        //     var selectedItems = $subPanelItems.find('li').filter('.active');

                        //     if (selectedItems.length == 0) {
                        //         Map.closeInfoWindow(Landmark.Common.map);
                        //         $('#hide-landmark-panel').trigger('click', function() {
                        //             // Map.resetMap(Landmark.Common.map);
                        //         });
                        //         // Map.resetMap(Landmark.Common.map);
                        //         // Map.resize(Landmark.Common.map);

                        //         Core.Map.Refresh('Landmark.Common.map','1',1);

                        //     } else if (selectedItems.length == 1 && autoOpenDetailPanel) {
                        //         var singleItemId = selectedItems.attr('id').split('-')[2],
                        //             $detailPanelHooks = $detailPanel.find('.hook-editable-keys').eq(0)
                        //         ;
                                
                        //         $('#secondary-panel-pagination').data('drawMarker', '');
                                
                        //         Map.showHideLabel(Landmark.Common.map, singleItemId, false);

                                
                        //         if ($detailPanelHooks.data('landmarkPk') == singleItemId) {  // if detail panel already contains data for this unit, just open detail panel again
                        //             Landmark.Common.DetailPanel.open(function() {

                        //                 Core.Map.Refresh('Landmark.Common.map','',1);

                        //                 // Map.resize(Landmark.Common.map);
                        //                 Map.clickMarker(Landmark.Common.map, singleItemId);
                                                                                
                        //             });
                        //         } else {                                                    // else make AJAX call to get vehicle info
                        //             $.ajax({
                        //                 url: '/ajax/landmark/getLandmarkByIds',
                        //                 type: 'POST',
                        //                 dataType: 'json',
                        //                 data: {
                        //                     territory_id: singleItemId
                        //                 },
                        //                 success: function(responseData) {
                        //                     if (responseData.code === 0) {
                        //                         var landmarkData = responseData.data;

                        //                         // Landmark.Common.DetailPanel.render(landmarkData, function() {
                        //                             Map.resize(Landmark.Common.map);
                        //                             if (! $.isEmptyObject(landmarkData)) {
                        //                                 Map.openInfoWindow(Landmark.Common.map, 'landmark', landmarkData.latitude, landmarkData.longitude, landmarkData);
                        //                             } else {
                        //                                 Map.resetMap(Landmark.Common.map);
                        //                             }
                        //                         // });                                                   
                        //                     } else {
                        //                         if ($.isEmptyObject(responseData.validation_errors) === false) {
                        //                             //	display validation errors
                        //                         }
                        //                     }
                                            
                        //                     if ($.isEmptyObject(responseData.message) === false) {
                        //                         //	display messages
                        //                     }                                            
                        //                 }
                        //             });
                        //         }
                        //     }
                    //     }
                    // }

                });
               
            },
            
            initLandmarkSearch: function() {

                var $landmarkSearch                 = $('#text-landmark-search');
                var $landmarkSearchGo               = $('#landmark-search-go');
                var $landmarkGroupFilter            = $('#select-landmark-group-filter');
                var $landmarkGroupAttributeFilter   = $('#select-landmark-attribute-filter');
                var $landmarkGroupReasonFilter      = $('#select-landmark-reason-filter');
                var $secondaryPanelPagination       = $('#secondary-panel-pagination');
                var $selectLandmarkSearchTab        = $('#select-landmark-search-tab');
                var $landmarkGroupCategoryFilter    = $('#select-landmark-category-filter');
                
                /**
                 *
                 * On keyup when searching landmarks using search string 
                 *
                 */
                $landmarkSearch.on('keyup', function () {
                    
                    // get current search string
                    var searchLandmarkString = $landmarkSearch.val().trim();

                    if (Core.Environment.context() == 'landmark/map') {
                    
                        $secondaryPanelPagination.data('landmarkStartIndex', 0);
                        $secondaryPanelPagination.data('paging', '');
                        
                        if (searchLandmarkString.length == 0 || searchLandmarkString.length > 1) {
                            Landmark.Common.SecondaryPanel.fetchFilteredLandmarks('string_search');
                        } else {
    	                    $('.sub-panel-items').html('');
    	                    $('#secondary-panel-pagination .showing').text('0-0');
                        }
                    }
                    
                    if (Core.Environment.context() == 'landmark/list') {

                        if (searchLandmarkString.length > 1) {
                            Landmark.List.DataTables.landmarkListTable.fnDraw();
                        } else if (searchLandmarkString.length == 0) {
                            Landmark.List.DataTables.landmarkListTable.fnDraw({});
                        }
                    }

                    if (Core.Environment.context() == 'landmark/incomplete') {

                        if (searchLandmarkString.length > 1) {
                            Landmark.Incomplete.incompleteLocationTable.fnDraw();
                        } else if (searchLandmarkString.length == 0) {
                            Landmark.Incomplete.incompleteLocationTable.fnDraw({});
                        }
                        $('#select-landmark-reason-filter').val('ALL').text('All');
                    }

                    $('#select-landmark-group-filter').val('All').text('All');
                    $('#select-landmark-attribute-filter').val('ALL').text('All');
                });

                /**
                 *
                 * On Search Button Click when searching landmarks using search string 
                 *
                 */
                $landmarkSearchGo.on('click', function () {
                    // get current search string
                    var searchLandmarkString = $landmarkSearch.val().trim();

                    if (Core.Environment.context() == 'landmark/map') {

                        var $secondaryPanelPagination = $('#secondary-panel-pagination');
                        if (searchLandmarkString.length >= 0) {

                            $secondaryPanelPagination.data('landmarkStartIndex', 0);
                            $secondaryPanelPagination.data('paging', '');
                        
                            Landmark.Common.SecondaryPanel.fetchFilteredLandmarks('string_search');

                        } else {
    	                    $('.sub-panel-items').html('');
    	                    $('#secondary-panel-pagination .showing').text('0-0');
                        }
                    }
                    
                    if (Core.Environment.context() == 'landmark/list') {
                        if (searchLandmarkString != '') {
                            Landmark.List.DataTables.landmarkListTable.fnDraw();
                        }
                    }

                    if (Core.Environment.context() == 'landmark/incomplete') {
                        if (searchLandmarkString != '') {
                            Landmark.Incomplete.incompleteLocationTable.fnDraw();
                        }
                        $('#select-landmark-reason-filter').val('ALL').text('All');
                    }

                    $('#select-landmark-group-filter').val('All').text('All');
                    $('#select-landmark-attribute-filter').val('ALL').text('All');
                });
                
                /**
                 *
                 * On Change of Landmark Group Filtering on landmark filter search
                 *
                 */
                $landmarkGroupFilter.on('Core.DropdownButtonChange', function() {

                    $('#text-landmark-search').val('');
                    $secondaryPanelPagination.data('paging','');
                    $secondaryPanelPagination.data('landmarkStartIndex', 0);
                    
                    if (Core.Environment.context() == 'landmark/map') {
                        // reset quick filter highlight
                        $('#map-quick-filters .btn').each(function() {
                            $(this).removeClass('active');
                        });
                        
                        // need to clear map
                       // Map.clearMarkers(Landmark.Common.map);
                    
                        // request landmarks for listing for filter params
                        Landmark.Common.SecondaryPanel.fetchFilteredLandmarks('group_filter', false);
                    }

                    if (Core.Environment.context() == 'landmark/list') {
                        // clear out the search box before redrawing table
                        //$('#text-landmark-search').val('');
                        Landmark.List.DataTables.landmarkListTable.fnDraw();
                    }

                    if (Core.Environment.context() == 'landmark/incomplete') {
                        // clear out the search box before redrawing table
                        //$('#text-landmark-search').val('');
                        Landmark.Incomplete.incompleteLocationTable.fnDraw();
                    }
                });

                /**
                 *
                 * On Change of Attribute Group Filtering on landmark filter search
                 *
                 */
                $landmarkGroupAttributeFilter.on('Core.DropdownButtonChange', function() {
console.log("$landmarkGroupAttributeFilter.on('Core.DropdownButtonChange'");
                    $('#text-landmark-search').val('');
                    $secondaryPanelPagination.data('paging','');
                    $secondaryPanelPagination.data('landmarkStartIndex', 0);

                    if (Core.Environment.context() == 'landmark/map') {
                        
                        // need to clear map
                        Map.clearMarkers(Landmark.Common.map);
                        
                        // request landmarks for listing for filter params
                        Landmark.Common.SecondaryPanel.fetchFilteredLandmarks('group_filter');
                    }

                    if (Core.Environment.context() == 'landmark/list') {
                        // clear out the search box before redrawing table
                        $('#text-landmark-search').val('');
                        Landmark.List.DataTables.landmarkListTable.fnDraw();
                    }

                    if (Core.Environment.context() == 'landmark/incomplete') {
                        // clear out the search box before redrawing table
                        $('#text-landmark-search').val('');
                        Landmark.Incomplete.incompleteLocationTable.fnDraw();
                    }
                });

                /**
                 *
                 * On Change of Category Group Filtering on landmark filter search
                 *
                 */
                $landmarkGroupCategoryFilter.on('Core.DropdownButtonChange', function() {
console.log("$landmarkGroupAttributeFilter.on('Core.DropdownButtonChange'");
                    $('#text-landmark-search').val('');
                    $secondaryPanelPagination.data('paging','');
                    $secondaryPanelPagination.data('landmarkStartIndex', 0);

                    if (Core.Environment.context() == 'landmark/map') {
                        
                        // need to clear map
                        Map.clearMarkers(Landmark.Common.map);
                        
                        // request landmarks for listing for filter params
                        Landmark.Common.SecondaryPanel.fetchFilteredLandmarks('group_filter');
                    }

                    if (Core.Environment.context() == 'landmark/list') {
                        // clear out the search box before redrawing table
                        $('#text-landmark-search').val('');
                        Landmark.List.DataTables.landmarkListTable.fnDraw();
                    }

                    if (Core.Environment.context() == 'landmark/incomplete') {
                        // clear out the search box before redrawing table
                        $('#text-landmark-search').val('');
                        Landmark.Incomplete.incompleteLocationTable.fnDraw();
                    }
                });

                /**
                 *
                 * On Change of Reason Filtering on landmark filter search
                 *
                 */
                $landmarkGroupReasonFilter.on('Core.DropdownButtonChange', function() {

                    $('#text-landmark-search').val('');
                    $secondaryPanelPagination.data('paging','');
                    $secondaryPanelPagination.data('landmarkStartIndex', 0);

                    if (Core.Environment.context() == 'landmark/incomplete') {
                        // clear out the search box before redrawing table
                        $('#text-landmark-search').val('');
                        Landmark.Incomplete.incompleteLocationTable.fnDraw();
                    }
                });

                /**
                 *
                 * On Clicking landmark map paging backward glyphicon 
                 *
                 */
                $('.glyphicon-backward').click(function() {
console.log("$('.glyphicon-backward').click(function() {");

                    $secondaryPanelPagination.data('paging','-');
                    
                    Map.clearMarkers(Landmark.Common.map);

                    var drawMarkers = false;
                    if( $secondaryPanelPagination.data('draw-marker') == 'yes') {
                        drawMarkers = true;
                    }
                    
                    var landmarkFilterTab = $selectLandmarkSearchTab.find('li').filter('.active').text();
                    if (landmarkFilterTab == 'Search') {
                        Landmark.Common.SecondaryPanel.fetchFilteredLandmarks('string_search', drawMarkers);
                    } else {
                        Landmark.Common.SecondaryPanel.fetchFilteredLandmarks('group_filter', drawMarkers);
                    }
                });
                
                /**
                 *
                 * On Clicking landmark map paging forward glyphicon 
                 *
                 */
                $('.glyphicon-forward').click(function() {
console.log("$('.glyphicon-forward').click(function() {");

                    // set page direction value 
                    $secondaryPanelPagination.data('paging','+');
                    
                    Map.clearMarkers(Landmark.Common.map);

                    var drawMarkers = false;
                    if( $secondaryPanelPagination.data('draw-marker') == 'yes') {
                        drawMarkers = true;
                    }
                    
                    var landmarkFilterTab = $selectLandmarkSearchTab.find('li').filter('.active').text();
                    if (landmarkFilterTab == 'Search') {
                        Landmark.Common.SecondaryPanel.fetchFilteredLandmarks('string_search', drawMarkers);
                    } else {
                        Landmark.Common.SecondaryPanel.fetchFilteredLandmarks('group_filter', drawMarkers);
                    }
                });

                /**
                 *
                 * On Chaning the landmark amount to be displayed per page for landmark map paging 
                 *
                 */
                $('#secondary-panel-pagination a').click(function() {
console.log("$('#secondary-panel-pagination a').click(function() {");
                    $self = $(this);
                    var currentActive = $('#secondary-panel-pagination a.active').data('value');

                    if (currentActive != $self.data('value')) {
                        $('#secondary-panel-pagination').find('a').each(function() {
                            if($(this).data('value') == $self.data('value')) {
                                // activate selected landmark display amount
                                $(this).addClass('active');
                            } else {
                                // deactivate non selected amount
                                $(this).removeClass('active');
                            }
                        });

                        $secondaryPanelPagination.data('paging','');
                        $secondaryPanelPagination.data('landmarkStartIndex', 0);

                        Map.clearMarkers(Landmark.Common.map);

                        var drawMarkers = false;
                        if( $secondaryPanelPagination.data('draw-marker') == 'yes') {
                            drawMarkers = true;
                        }

                        var landmarkFilterTab = $selectLandmarkSearchTab.find('li').filter('.active').text();
                        if (landmarkFilterTab == 'Search') {
                            Landmark.Common.SecondaryPanel.fetchFilteredLandmarks('string_search', drawMarkers);
                        } else {
                            Landmark.Common.SecondaryPanel.fetchFilteredLandmarks('group_filter', drawMarkers);
                        }
                    }
                });

            },
            
            fetchFilteredLandmarks: function (filterType, drawMarkers, territory_id)
            {
                var $secondaryPanelPagination = $('#secondary-panel-pagination');
                
                territorytype = 'landmark';

                // send ajax request
                $.ajax({
                    url: '/ajax/landmark/getFilteredLandmarks',
                    type: 'POST',
                    data: {
                        filter_type:                filterType,
                        search_string:              $('#text-landmark-search').val().trim(),
                        territorygroup_id:          $('#select-landmark-group-filter').val().trim(),
                        territorytype:              'landmark',
                        landmark_listing_length:    $('#secondary-panel-pagination a.active').data('value'),
                        landmark_start_index:       $secondaryPanelPagination.data('landmarkStartIndex'),
                        paging:                     $secondaryPanelPagination.data('paging'),
                        territorycategory_id:       $('#select-landmark-category-filter').val(),
                        territory_id:               (territory_id == undefined) ? 0 : territory_id
                    },
                    dataType: 'json',
                    success: function(responseData) {
console.log("fetchFilteredLandmarks: function (filterType, drawMarkers, territory_id):success");
                        if (responseData.code === 0) { // 0 means SUCCESS, > 0 means FAIL
                            var html                = '';
                            var landmarks           = responseData.data.landmarks;
                            var endpage             = responseData.data.endpage;
                            var quickFilters        = responseData.data.quick_filters;
                            var showUnitMarkerLabel = true;
                            var startIndex          = (responseData.data.landmark_start_index != undefined ? responseData.data.landmark_start_index : $secondaryPanelPagination.data('landmarkStartIndex'));
                            
                            if (landmarks.length > 0) {
                                var length = landmarks.length;

                                // create filtered landmark listing
                                $.each(landmarks, function(key, landmark) {
                                    var landmarkListActiveClass = "";

                                    if (drawMarkers === true && territory_id == undefined) {
                                        if (landmarks.length != 1) {
                                            landmarkListActiveClass = " active";
                                            showUnitMarkerLabel = false;
                                        }
  
                                        Map.addMarkerWithPolygon(
                                            Landmark.Common.map, 
                                            {
                                                id: landmark.territory_id,
                                                name: landmark.territoryname,
                                                latitude: landmark.latitude,
                                                longitude: landmark.longitude,
                                                click: function() {
                                                    Map.getLandmarkInfo(Landmark.Common.map, landmark.territory_id);
                                                }
                                            },
                                            showUnitMarkerLabel,
                                            {
                                                type: landmark.shape,
                                                radius: landmark.radius,
                                                points: landmark.coordinates
                                            }
                                        );
                                        
                                        if (key == (length - 1)) {
                                            setTimeout(function(){
                                                Map.updateMapBound(Landmark.Common.map);
                                            },300);
                                        }
                                    }

                                    html += '<li id="landmark-li-'+landmark.territory_id+'" class="list-group-item clearfix' + landmarkListActiveClass +'">' +
                                            '   <label for="landmark-li-'+landmark.territory_id+'">'+landmark.territoryname+'</label>' +
                                            '   <div class="toggle pull-right">' +
                                            '       <span class="glyphicon glyphicon-pencil"></span>' +
                                            '   </div>' +
                                            '   ' +
                                            '</li>'
                                    ;
                                });
                                
                                if (responseData.data.landmark_start_index != undefined) {  // if a specific landmark/page was requested, set the start index to that page
                                    $secondaryPanelPagination.data('landmarkStartIndex', startIndex);    
                                } else {                                                    // else use the forward/backward icons to determine the starting index
                                    if ($secondaryPanelPagination.data('paging') == '-') {
                                        var new_start_index = parseInt(startIndex) - parseInt($('#secondary-panel-pagination a.active').data('value'));
                                        if (new_start_index < 0) {
                                            new_start_index = 0;
                                        }
                                        $secondaryPanelPagination.data('landmarkStartIndex', parseInt(new_start_index));
                                    } else if ($secondaryPanelPagination.data('paging') == '+') {
                                        var new_start_index = parseInt(startIndex) + parseInt($('#secondary-panel-pagination a.active').data('value'));
                                        $secondaryPanelPagination.data('landmarkStartIndex', parseInt(new_start_index));
                                    } else {
                                        $secondaryPanelPagination.data('landmarkStartIndex', 0);
                                    }
                                }
                                
                                // update showing text info
                                var start_showing = end_showing = 0;
                                start_showing = parseInt($secondaryPanelPagination.data('landmarkStartIndex')) + 1;
                                end_showing = parseInt($secondaryPanelPagination.data('landmarkStartIndex')) + landmarks.length;
                                var new_showing_text = start_showing + '-' + end_showing;
                                $('#secondary-panel-pagination .showing').text(new_showing_text);
                                $('#secondary-panel-pagination .total').text(responseData.data.total_landmarks_count);
       
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

                            if (Core.Environment.context() == 'landmark/map') {
                                if (drawMarkers !== true && landmarks.length != 1) {
                                	Map.clearMarkers(Landmark.Common.map);
                                    $('#hide-landmark-panel').trigger('click', function() {
                                        Map.resetMap(Landmark.Common.map);
                                    });
                                    
                                } else {
                                    Map.resetMap(Landmark.Common.map);
                                }

                                if (landmarks.length == 1) {
                                   $('.sub-panel-items').find('li').trigger('click');
                                } else if ((territory_id != undefined) && (drawMarkers == true)) { // click on the newly added landmark if the 'Add + Close' button was clicked
                                    $('#landmark-li-'+territory_id).find('.glyphicon-pencil').trigger('click');
                                }

                            }
                        } else {
                            $('.sub-panel-items').html('');
                        }
                    },
                    complete: function(){
                    }
                });
            },

            initLandmarkGroupSearch: function() {

                var $landmarkGroupSearch    = $('#text-landmark-group-search');
                var $landmarkGroupSearchGo  = $('#landmark-group-search-go');
                
                /**
                 *
                 * On keyup when searching landmarks using search string 
                 *
                 */
                $landmarkGroupSearch.on('keyup', function () {
                    // get current search string
                    var searchLandmarkGroupString = $landmarkGroupSearch.val().trim();
                    if (searchLandmarkGroupString.length == 0 || searchLandmarkGroupString.length > 1) {
                        Landmark.Group.DataTable.landmarkGroupListTable.fnStandingRedraw();
                    }
                });

                /**
                 *
                 * On Search Button Click when searching landmarks using search string 
                 *
                 */
                $landmarkGroupSearchGo.on('click', function () {
                    // get current search string
                    var searchLandmarkGroupString = $landmarkGroupSearch.val().trim();
                    if (searchLandmarkGroupString.length >= 0) {
                        Landmark.Group.DataTable.landmarkGroupListTable.fnStandingRedraw();
                    }
                });
            }
        },

        DetailPanel: {

            render: function(landmarkdata, callback, updateRow) {
console.log('-------------------------------------------------- Landmark.Common.DetailPanel.render');

                if ((landmarkdata != undefined) && (typeof(landmarkdata) == 'object') && (! $.isEmptyObject(landmarkdata))) {
                    var $detailPanel = $('#detail-panel'),
                        context     = Core.Environment.context(),
                        updateRow   = updateRow || false,
                        callback    = ((callback != undefined) && (typeof(callback) == 'function')) ? callback : undefined
                    ;
                    
                    // populate landmark id
                    $detailPanel.find('.hook-editable-keys').eq(0).data('landmark-pk', landmarkdata.territory_id).data('landmark-shape', landmarkdata.shape).data('prev-shape', landmarkdata.shape).data('landmark-radius',landmarkdata.radius);

                    var $container = $(),
                        $landmarkLabelContainer = $()
                    ;
                    
                    if (context == 'landmark/map') { 
                        
                        $container              = $detailPanel;
                        $landmarkLabelContainer = $detailPanel.find('#landmark-label');                            
                        $landmarkLocationLabel  = $detailPanel.find('#landmark-location-label');                            
                        $landmarkLocationLabel.text(landmarkdata.formatted_address);

                    } else if (context == 'landmark/list') {
                        
                        $container              = $('#modal-edit-landmark');
                        $landmarkLabelContainer = $container.find('.modal-title').eq(0);
                        $detailPanel.data('updateRow', updateRow);                           
                    
                    } else if (context == 'landmark/verification') {

                        $container              = $('#modal-verification-list');
                        $landmarkLabelContainer = $container.find('.modal-title').eq(0);
                        $detailPanel.data('updateRow', updateRow);

                    } else if (context == 'landmark/incomplete') {
                        
                        $container              = $('#modal-incomplete-location');
                        $landmarkLabelContainer = $container.find('.modal-title').eq(0);
                        $detailPanel.data('updateRow', updateRow);
                        
                        $detailPanel.find('.hook-editable-keys').eq(0).data('landmark-page', 'incomplete');
                        
                        // show validation error if any
                        if (typeof(landmarkdata) != 'undefined' && ! $.isEmptyObject(landmarkdata.validation_error)) {
                            $.each(landmarkdata.validation_error, function (key, msg) {
                                Core.Editable.setError('#'+key, msg);
                            });
                        }
                    }

                    // update landmark name and address
                    $container.find('.landmark-location-label').eq(0).text(((context !== 'landmark/map') ? '@ ' : '') + ((landmarkdata.formatted_address != undefined) ? ((landmarkdata.formatted_address == '' && landmarkdata.latitude != undefined && landmarkdata.longitude != undefined) ? (landmarkdata.latitude + ' ' + landmarkdata.longitude) : landmarkdata.formatted_address) : 'Location Not Available'));
                    $landmarkLabelContainer.text(landmarkdata.territoryname);
                    
                   /***************
                     *
                     * LANDMARK INFO
                     *
                     ***************/
                    var $landmarkName           = $('#landmark-name'),
                        $landmarkGroup          = $('#landmark-group'),
                        $landmarkUnit           = $('#landmark-unit'),
                        $landmarkRadius         = $('#landmark-radius'),
                        $landmarkLatitude       = $('#landmark-latitude'),
                        $landmarkLongitude      = $('#landmark-longitude'),
                        $landmarkStreetAddress  = $('#landmark-street-address'),
                        $landmarkCity           = $('#landmark-city'),
                        $landmarkState          = $('#landmark-state'),
                        $landmarkZipcode        = $('#landmark-zipcode'),
                        $landmarkCountry        = $('#landmark-country'),
                        $landmarkType           = $('#landmark-type'),
                        $landmarkShape          = $('#landmark-shape'),
                        $landmarkCategory       = $('#landmark-category'),
                        $landmarkPolygonOut     = $('#poloygon-output')
                    ;

                    // editable
                    Core.Editable.setValue($landmarkName, landmarkdata.territoryname);
                    if (typeof(landmarkdata.unit) != 'undefined') {
                        Core.Editable.setValue($landmarkUnit, landmarkdata.unit.unit_id);
                    }
                    //Core.Editable.setValue($landmarkUnit, landmarkdata.unit.unit_id);
                    
                    $landmarkCategory.val(landmarkdata.territorycategory_id);
                    Core.Editable.setValue($landmarkCity, landmarkdata.city);
                    $landmarkCountry.val(landmarkdata.country);
                    $landmarkGroup.val(landmarkdata.territorygroup_id);
                    $landmarkLatitude.text(landmarkdata.latitude.substring(0,7));
                    $landmarkLongitude.text(landmarkdata.longitude.substring(0,8));
                    $landmarkRadius.val(landmarkdata.radius);
                    $landmarkShape.val(landmarkdata.shape);
                    Core.Editable.setValue($landmarkStreetAddress, landmarkdata.streetaddress);
                    Core.Editable.setValue($landmarkType, landmarkdata.territorytype);
                    $landmarkState.val(landmarkdata.state);
                    Core.Editable.setValue($landmarkZipcode, landmarkdata.zipcode);
                    $landmarkPolygonOut.val(landmarkdata.boundingbox);

                    Landmark.Common.DetailPanel.open(callback);

                    // trigger restore to clear any previous location
                    $('#landmark-restore').trigger('click');
                    
                    // hide group row and disable shape changing for reference landmarks
                    // if (landmarkdata.territorytype == 'reference') {
                    //     $landmarkGroup.closest('.row').hide();
                    //     $landmarkShape.addClass('disabled');
                    //     $landmarkShape.siblings('button').addClass('disabled');
                    //     $landmarkCategory.closest('.row').hide();
                    //     $landmarkUnit.closest('.row').show();
                    //     $landmarkUnit.editable('disable').data('disabled', true);
                    // } else {
                    //     $landmarkGroup.closest('.row').show();

                    //     if ($landmarkName.data('isEditable') == 1) {
                    //         $landmarkShape.removeClass('disabled');
                    //         $landmarkShape.siblings('button').removeClass('disabled');
                    //     }
                        
                    //     $landmarkCategory.closest('.row').show();
                    //     $landmarkUnit.closest('.row').hide();
                    // }
                    
                    // if incomplete landmarks, disable shape dropdown (circle default)
                    // if (Core.Environment.context() == 'landmark/incomplete') {
                    //     $landmarkShape.addClass('disabled');
                    //     $landmarkShape.siblings('button').addClass('disabled');
                    // }                   
                    
                    // disable landmark type changing for all landmark type
                    // $landmarkType.editable('disable').data('disabled', true);//off('mouseenter');
                }
            },

            open: function(callback) {

                // var $mapDiv      = $('#map-div'),
                //     $detailPanel = $('#detail-panel')
                // ;

                // $mapDiv.animate({
                //     // 'height': '400px'
                //     'height': (Core.Viewport.contentHeight-parseInt($detailPanel.css('height')))+'px'
                // }, 300, function() {
                //     if ((callback != undefined) && (typeof callback == 'function')) {
                //         callback();
                //     }
                // });

                // $detailPanel.slideDown(300).addClass('open');

                /**
                 *
                 * Open up the detail panel
                 *
                 * */
console.log('=============================================== '+Core.Environment.context()+', currentLandmarkId='+currentLandmarkId+', currentLandmarkIdHidePanel='+currentLandmarkIdHidePanel);

                if ( Core.Environment.context() == 'landmark/map') {

                    if ( (currentLandmarkId) && (currentLandmarkId == currentLandmarkIdHidePanel) ) {

                        Core.Map.Refresh('Landmark.Common.map','',1);

                    } else {

                        $('#detail-panel').show();
                        $('#detail-panel').addClass('open');

                        $('#detail-panel').height('190px');
                        $('#detail-panel').find('.panel-bottomless').height('176px');
                        $('#detail-panel').find('.panel-bottomless').find('.block').height('174px');
                        $('#detail-panel').find('.panel-bottomless').find('.block').find('.tab-content').height('116px');

                        Core.Viewport.adjustLayout();

                    }

                    switch($('#landmark-shape').val()){
                        case    'circle' :  
                        case    'square' :  $('#landmark-method2').val('manual-entry');
                                            $('#landmark-method2').trigger('change');
                                            break;
                        case   'polygon' :  $('#landmark-method2').val('manual-entry');
                                            // $('#landmark-method2').val('map-click');
                                            $('#landmark-method2').trigger('change');
                                            break;
                    }

                } else if (Core.Environment.context() == 'landmark/list') {

                    switch($('#landmark-shape').val()){
                        case    'circle' :  
                        case    'square' :  $('#landmark-method2').val('manual-entry');
                                            $('#landmark-method2').trigger('change');
                                            break;
                        case   'polygon' :  $('#landmark-method2').val('map-click');
                                            $('#landmark-method2').trigger('change');
                                            break;
                    }

                    // var header = 400 ;
                    // var h = 400;
                    // switch(eid){
                    //     // case        'vehicle-detail-commands-tab' : h = 195 ;
                    //     //                                             break;
                    //     // case                  'tab-quick-history' : 
                    //     // case                   'tab-verification' : h = $('#modal-edit-vehicle-list').find('.active').find('.panel-report-scroll').height();
                    //     //                                             h = h+64;
                    //     //                                             $('#modal-edit-vehicle-list').find('div.tab-pane active').find('div.report-master').height(h);
                    //     //                                             h = h+75;
                    //     //                                             break;
                    //                                       default : h = 225 ;
                    // }
                    // $('#modal-edit-landmark-list').find('div.modal-pronounce').height(header+h);
                    // h=h+8;
                    // $('#modal-edit-landmark-list').find('div.modal-content').height(header+h);
                    // $('#modal-edit-landmark-list').closest('div.modal-dialog').css({ backgroundColor: '#999999' ,display: 'block' });
// console.log('<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< vehicle/list:header:'+header+':h:'+h+':eid:'+eid);
                }

            },

            reset: function() {

            },
            
            initClose: function() {
console.log("Landmark.DetailPanel.initClose");

                /**
                 *
                 * When the Detail Panel's Close (x) Icon is clicked
                 *
                 * */
                var $mapDiv      = $('#map-div'),
                    $detailPanel = $('#detail-panel')
                ;

                $('#hide-landmark-panel').click(function(event, callBack) {
                    $mapDiv.animate({
                        //'height': '800px'
                        'height': parseInt(Core.Viewport.contentHeight-35)+'px'
                    }, 300, function() {
                        if (Core.Environment.context() == 'landmark/map') {
                            Map.resize(Landmark.Common.map);
                            if ($detailPanel.is('.open')) {
                                $detailPanel.removeClass('open');
                            }
                            
                            Landmark.Common.DetailPanel.reset();                              
                        }
                        
                        if ((callBack != undefined) && (typeof(callBack) == 'function')) {
                            callBack();
                        }
                    });

                    $detailPanel.slideUp(300);
                });
            },
            
            initLandmarkInfo: function() {
                /**
                 *
                 * When Landmark Info Changes
                 *
                 * listens for the Core.FormElementChanged event triggered by Core.Editable
                 *
                 * */
                var $landmarkName               = $('#landmark-name'),
                    $landmarkRadius             = $('#landmark-radius'),
                    $landmarkGroup              = $('#landmark-group'),
                    $modalDialog                = $('#modal-landmark-list'),
                    $modalDialogVerification    = $('#modal-verification-list'),
                    $modalDialogIncomplete      = $('#modal-incomplete-location'),
                    $landmarkShape              = $('#landmark-shape'),
                    $detailPanel                = $('#detail-panel'),
                    $modalErrorMessages         = $('#error-messages')
                ;

                $modalErrorMessages.on('Core.ErrorsResolved', function(event) {

                    if (Core.Environment.context() == 'landmark/incomplete') {

                        // close popover
                        $('.incomplete-landmark-close').trigger('click');
    
    					// redraw incomplete landmark list
    					Landmark.Incomplete.incompleteLocationTable.fnStandingRedraw();
					}
                });

                //  When Name Changed
                $landmarkName.on('Core.FormElementChanged', function(event, extraParams) {
                    extraParams = extraParams || {
                        value: false,
                        pk:    false
                    };

                    // require value and pk
                    if (! $.isEmptyObject(extraParams.value) && !$.isEmptyObject(extraParams.pk)) {
                        if (Core.Environment.context() == 'landmark/map') {
                            // change title in detail panel
                            var $landmarkLabel = $detailPanel.find('.landmark-label');
                            $landmarkLabel.hide()
                                          .text(extraParams.value)
                                          .fadeIn(300)
                            ;
    
                            $('label[for="landmark-li-'+extraParams.pk.landmarkPk+'"]').text(extraParams.value);    // change label in secondary panel
                        }
                        
                        if (Core.Environment.context() == 'landmark/list') {
                            $modalDialog.find('.modal-title').text(extraParams.value);      // update title in modal
                            $('#landmark-tr-'+extraParams.pk.landmarkPk).find('td a').text(extraParams.value);      // update title in table row
                        }

                        if (Core.Environment.context() == 'landmark/verification') {
                            $modalDialogVerification.find('.modal-title').text(extraParams.value);      // update title in modal
                            $('#landmark-tr-'+extraParams.pk.landmarkPk).find('td a').text(extraParams.value);      // update title in table row
                        }

                        if (Core.Environment.context() == 'landmark/incomplete') {

                            $modalDialogIncomplete.find('.modal-title').text(extraParams.value);        // update title in modal
                            $('#incomplete-tr-'+extraParams.pk.landmarkPk).find('td a').text(extraParams.value);    // update title in table row

                            // change label on map info window
                            if ($('#info_window_div').length > 0) {
                                $('#info_window_landmark_name').html('<b>'+extraParams.value+'</b>');
                            }
                        }

                        // change label on map info window
                        if ($('#info_window_div').length > 0) {
                            $('#info_window_landmark_name').html('<b>'+extraParams.value+'</b>');
                        }
                    }
                });
                
                // When Group Changed
                $landmarkGroup.on('Core.FormElementChanged', function(event, extraParams) {
                    extraParams = extraParams || {
                        value: false,
                        pk:    false
                    };

                    // require value and pk
                    if (! $.isEmptyObject(extraParams.value) && !$.isEmptyObject(extraParams.pk)) {
                        
                        // change label on map info window
                        if ($('#info_window_div').length > 0) {
                            $('#info_window_landmark_group').html($(this).siblings('.editable-container').eq(0).find('.form-control option[value="'+extraParams.value+'"]').text());
                        }
                    }
                    
                    $detailPanel.data('updateRow', true);
                });
                
                // When Radius Changed
                $landmarkRadius.on('Core.FormElementChanged', function(event, extraParams) {
                    extraParams = extraParams || {
                        value: false,
                        pk:    false
                    };

                    // require value and pk
                    if (! $.isEmptyObject(extraParams.value) && !$.isEmptyObject(extraParams.pk)) {
                        var elem        = '',
                            landmarkId  = '',
                            $container  = {}
                        ;

                        $detailPanel.find('.hook-editable-keys').eq(0).data('landmarkRadius', extraParams.value); 

                        // change label on map info window
                        if ($('#info_window_div').length > 0) {
                            //var radiusInMiles   = parseFloat(($(this).siblings('.editable-container').eq(0).find('.form-control option[value="'+extraParams.value+'"]').val() * 0.00018939393).toFixed(3)),
                                //measurementUnit = (radiusInMiles > 1) ? ' Miles' : ' Mile'; 

                            //$('#info_window_landmark_radius').html(radiusInMiles + measurementUnit);
                            var newRadiusFraction = $(this).siblings('.editable-container').eq(0).find('.form-control option[value="'+extraParams.value+'"]').text();
                            $('#info_window_landmark_radius').html(newRadiusFraction);
                        }
                        
                        $container = (Core.Environment.context() == 'landmark/map') ? $detailPanel : $modalDialog;
                        elem = (Core.Environment.context() == 'landmark/map') ? 'li' : 'tr';
                        var type        = $landmarkShape.val(),
                            landmarkId  = $container.find('.hook-editable-keys').eq(0).data('landmarkPk'),
                            latitude    = $('#landmark-' + elem + '-' + landmarkId).data('latitude'),
                            longitude   = $('#landmark-' + elem + '-' + landmarkId).data('longitude')
                        ;

                        if (Core.Environment.context() == 'landmark/verification') {
                            landmarkId  = $modalDialogVerification.find('.hook-editable-keys').eq(0).data('landmarkPk');
                            latitude    = $('#landmark-' + elem + '-' + landmarkId).data('latitude');
                            longitude   = $('#landmark-' + elem + '-' + landmarkId).data('longitude');
                        }

                        if (Core.Environment.context() == 'landmark/incomplete') {
                            landmarkId  = $modalDialogIncomplete.find('.hook-editable-keys').eq(0).data('landmarkPk');
                            latitude    = $('#incomplete-' + elem + '-' + landmarkId).data('latitude');
                            longitude   = $('#incomplete-' + elem + '-' + landmarkId).data('longitude');
                        }

console.log("Landmark.DetailPanel.initLandmarkInfo");
                        Map.updateMarkerWithPolygon(Landmark.Common.map, landmarkId, {latitude: latitude, longitude: longitude}, {type: type, radius: extraParams.value});
                    }
                    
                    $detailPanel.data('updateRow', true);
                });
                
                // When Shape Changed
                $landmarkShape.on('Core.DropdownButtonChange', function(event, extraParams) {
                    var $self           = $(this),
                        shape           = $self.val(),
                        landmarkId      = $detailPanel.find('.hook-editable-keys').eq(0).data('landmarkPk'),
                        landmarkShape   = $detailPanel.find('.hook-editable-keys').eq(0).data('landmarkShape'),
                        prevShape       = $detailPanel.find('.hook-editable-keys').eq(0).data('prevShape'),
                        $container      = (Core.Environment.context() == 'landmark/map') ? $detailPanel : $modalDialog;
                    ;

                    $('#landmark-radius-row').show();

                    if (landmarkShape != 'polygon' && (shape == 'circle' || shape == 'square')) {

                        if (prevShape == 'polygon') {
                            // remove map click listener and temp marker
                            Map.removeMapClickListener(Landmark.Common.map);
                            Map.removeTempLandmark(Landmark.Common.map);
                            Map.showMarker(Landmark.Common.map, landmarkId); 
                            Map.clickMarker(Landmark.Common.map, landmarkId);
                        }
                        
                        // show landmark shape/radius
                        elem = (Core.Environment.context() == 'landmark/map') ? 'li' : 'tr';
                        var landmarkId      = $container.find('.hook-editable-keys').eq(0).data('landmarkPk'),
                            landmarkRadius  = $container.find('.hook-editable-keys').eq(0).data('landmarkRadius'),
                            latitude        = $('#landmark-' + elem + '-' + landmarkId).data('latitude'),
                            longitude       = $('#landmark-' + elem + '-' + landmarkId).data('longitude')
                        ;

                        if (Core.Environment.context() == 'landmark/verification') {
                            landmarkId  = $modalDialogVerification.find('.hook-editable-keys').eq(0).data('landmarkPk');
                            landmarkRadius  = $modalDialogVerification.find('.hook-editable-keys').eq(0).data('landmarkRadius');
                            latitude    = $('#landmark-' + elem + '-' + landmarkId).data('latitude');
                            longitude   = $('#landmark-' + elem + '-' + landmarkId).data('longitude');
                        }

                        if (Core.Environment.context() == 'landmark/incomplete') {
                            landmarkId  = $modalDialogIncomplete.find('.hook-editable-keys').eq(0).data('landmarkPk');
                            landmarkRadius  = $modalDialogIncomplete.find('.hook-editable-keys').eq(0).data('landmarkRadius');
                            latitude    = $('#incomplete-' + elem + '-' + landmarkId).data('latitude');
                            longitude   = $('#incomplete-' + elem + '-' + landmarkId).data('longitude');
                        }

                        // update the shape on the map marker
                        Map.updateMarkerWithPolygon(Landmark.Common.map, landmarkId, {latitude: latitude, longitude: longitude}, {type: shape, radius: landmarkRadius});
                        
                        // disable the landmark save and restore button because saving of circle and shape is being dynamically saved below
                        $('#landmark-save, #landmark-restore').prop('disabled', true);
                        
                        if (shape != prevShape) {

                            if (typeof(landmarkRadius) == 'undefined') {
                                $('#temp-landmark-radius').trigger('click');
                            }

                            // save circle and square shape
                            data = {};
                            data.id             = "landmark-shape";
                            data.value          = shape;
                            data.primary_keys   = {
                                landmarkPk: landmarkId,
                                landmarkPage: $('#detail-panel').find('.hook-editable-keys').eq(0).data('landmarkPage'),
                                landmarkRadius: landmarkRadius
                            };
    
                            $.ajax({
                                url: '/ajax/landmark/updateLandmarkInfo',
                                type: 'POST',
                                dataType: 'json',
                                data: data,
                                success: function(responseData) {
                                    if (responseData.code === 0) {
                                        $detailPanel.find('.hook-editable-keys').eq(0).data('landmarkShape', shape);
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
                        
console.log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> disableEditLandmarkInfo');
                        // close info window
                        Map.closeInfoWindow(Landmark.Common.map);
console.log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> disableEditLandmarkInfo');
                        // hide marker
                        Map.hideMarker(Landmark.Common.map, landmarkId);                        
console.log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> disableEditLandmarkInfo');
                        // trigger map click so user can start clicking on the map to create polygon
                        $('#landmark-method').val('map-click').text('Use Map Click').trigger('Core.DropdownButtonChange');
console.log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> disableEditLandmarkInfo');
                        // disable info not needed/allowed for polygon shape
                        Landmark.Common.disableEditLandmarkInfo();
console.log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> disableEditLandmarkInfo');
                        // hide the radius dropdown, no radius for polygons                        
                        $('#landmark-radius-row').hide();
                    }

                    // update the temp shape data info
                    $detailPanel.find('.hook-editable-keys').eq(0).data('prevShape', shape);                    

                    // remove any temp landmarks
console.log("................................................................................. Map.doesTempLandmarkExist(map)");
                    if (Map.doesTempLandmarkExist(Landmark.Common.map)) {
                        Map.removeTempLandmark(Landmark.Common.map);    
                    }
                    
                    // allow shape dropdown option if user wants to change shape again
                    $('#landmark-shape').removeClass('disabled');
                    $('#landmark-shape').siblings().filter(':button').removeClass('disabled');

                });
                
                /**
                 * Delete landmark
                 */
                var $body = $('body');
                $body.on('click', '#popover-landmark-delete-confirm', function() {
                    var landmarkId = $detailPanel.find('.hook-editable-keys').eq(0).data('landmark-pk'),
                        data = {}
                    ;

                    if (landmarkId != '' && landmarkId != undefined) {
                        $url = '/ajax/landmark/deleteLandmark';
                        data.territory_id = landmarkId;
                        data.reference = 0;
                        if (Core.Environment.context() == 'landmark/incomplete') {
                            $url = '/ajax/landmark/deleteLandmarkUpload';
                        }
                        
                        if (Core.Environment.context() == 'landmark/verification') {
                            data.reference = 1;
                        }

                        $.ajax({
                            url: $url,
                            type: 'POST',
                            dataType: 'json',
                            data: data,
                            success: function(responseData) {
                                if (responseData.code === 0) {
                                    // close popover
                                    $('#popover-landmark-delete-cancel').trigger('click');
    
                                    // clear marker
                                    Map.clearMarkers(Landmark.Common.map);
    
                                    if (Core.Environment.context() == 'landmark/map') {
                                        $('#hide-landmark-panel').trigger('click', function() {
                                            Map.resetMap(Landmark.Common.map);
                                        });
    
                                        $('#secondary-panel-pagination').data('landmarkStartIndex', 0);
                                        $('#secondary-panel-pagination').data('paging', '');
                                        Landmark.Common.SecondaryPanel.fetchFilteredLandmarks('string_search');
                                    }
    
                                    if (Core.Environment.context() == 'landmark/list') {
                                            // close popover
                                            $('#modal-landmark-list .modal-footer button').trigger('click');
                                            
                                            // redraw landmark list
                                            Landmark.List.DataTables.landmarkListTable.fnStandingRedraw();
                                    } 

                                    if (Core.Environment.context() == 'landmark/verification') {
                                            // close popover
                                            $('#modal-verification-list .modal-footer button').trigger('click');
                                            
                                            // redraw incomplete landmark list
                                            Landmark.Verification.DataTables.verificationListTable.fnStandingRedraw();
                                    }

                                    if (Core.Environment.context() == 'landmark/incomplete') {
                                            // close popover
                                            $('.incomplete-landmark-close').trigger('click');
                                            
                                            // redraw incomplete landmark list
                                            Landmark.Incomplete.incompleteLocationTable.fnStandingRedraw();
                                    }
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

                            }
                        });
                    } else {
                        alert('Invalid Landmark ID');
                    }
                });
                
                /**
                 * Editing Radius of Temp Landmark
                 */
                 $body.on('Core.DropdownButtonChange', '#temp-landmark-radius', function() {
                     var $self = $(this),
                         value = $self.val(),
                         shape = $landmarkShape.val()
                     ;

console.log("................................................................................. Map.doesTempLandmarkExist(Landmark.Common.map)");
                     if (Map.doesTempLandmarkExist(Landmark.Common.map)) {
                         Map.updateTempPolygon(Landmark.Common.map, {type: shape, radius: value});
                     }
                 });
                
                /**
                 * Restore Origin Landmark
                 */
                $('#landmark-restore').on('click', function(event, extraParams) {

                    // restore original landmark
                    var landmarkId = $detailPanel.find('.hook-editable-keys').eq(0).data('landmarkPk');
                    var landmarkShape = $detailPanel.find('.hook-editable-keys').eq(0).data('landmarkShape');
                    var openInfoWindow = (extraParams != undefined) ? (extraParams.openInfoWindow != undefined ? extraParams.openInfoWindow : true) : true,
                        updateMapBound = (extraParams != undefined) ? (extraParams.updateMapBound != undefined ? extraParams.updateMapBound : true) : true
                    ;
                    
                    if (landmarkId != undefined && landmarkId != '') {
                        Map.showMarker(Landmark.Common.map, landmarkId, function() {
                            if (openInfoWindow) {
                                setTimeout(function() {
                                    Map.clickMarker(Landmark.Common.map, landmarkId);
                                }, 400);
                            }
                        });
                    } else {
                        Map.resetMap(Landmark.Common.map);
                    }
                    
                    // clear location address
                    $('#landmark-location').val('').text('');
                    
                    // reset method dropdown
                    $('#landmark-method').val('manual-entry').text('Manual Entry');    
                
                    // remove map click listener and temp marker
                    Map.removeMapClickListener(Landmark.Common.map);
                    Map.removeTempLandmark(Landmark.Common.map);
                    
                    if (updateMapBound) {
                        Map.updateMapBound(Landmark.Common.map);
                    }
                    
                    // disable 'Save' & 'Restore' buttons
                    $('#landmark-save, #landmark-restore').prop('disabled', true);
                    
                    // enabled selected fields
                    Landmark.Common.enableEditLandmarkInfo();
                    
                    // change shape dropdown back to origin
                    if (landmarkShape != undefined && landmarkShape != '') {
                        $landmarkShape.val(landmarkShape).text(landmarkShape.charAt(0).toUpperCase() + landmarkShape.slice(1));
                        if (landmarkShape == 'circle' || landmarkShape == 'square') {
                            $('#landmark-radius-row').show();
                        } else {
                            $('#landmark-radius-row').hide();
                        }
                        $('#temp-landmark-radius-row').hide();
                    }

                    // disable shape dropdown if reference or incomplete landmark edit page (always circle shape)
                    if (Core.Environment.context() == 'landmark/verification' || Core.Environment.context() == 'landmark/incomplete') {
                        $('#landmark-shape').addClass('disabled');
                        $('#landmark-shape').siblings().filter(':button').addClass('disabled');
                    }
                });
            
                /**
                 * Save Edited Landmark Shape and Location
                 */
                $('#landmark-save').on('click', function() {

                    var $self       = $(this),
                        shape       = $landmarkShape.val(),
                        landmarkId  = $('#detail-panel').find('.hook-editable-keys').eq(0).data('landmarkPk'),
                        page        = $('#detail-panel').find('.hook-editable-keys').eq(0).data('landmarkPage'),
                        radius      = $('#temp-landmark-radius').val(),
                        title       = $('#landmark-name').text(),
                        latlngs     = [],
                        data        = {},
                        validation  = [],
                        point       = Map.getTempMarkerPosition(Landmark.Common.map);
                    ;

                    // save shape
                    data.shape          = shape;
                    data.primary_keys   = {
                        landmarkPk: landmarkId,
                        landmarkPage: page
                    };
                    
                    // save the center of the temp marker
                    if (! $.isEmptyObject(point)) {
                        data.latitude   = point.latitude;
                        data.longitude  = point.longitude;                                
                    } else {
                        validation.push('Failed to retrieve landmark position');
                    }
                    
                    if (shape == 'rectangle' || shape == 'polygon' || shape == 'square') { // for rectangles, squares, and polygons
                        // get the coordinates of the temp polygon as an array of objects, each with two properties called 'latitude' and 'longitude'
                        // (i.e. [{latitude: 12.345, longitude: 45.56}, {latitude: 34.45, longitude: 56.788}, ...]) 
                        var coordinates = Map.getTempPolygonPoints(Landmark.Common.map);
                        
                        if ((shape == 'rectangle' || shape == 'square')) {
                            if (coordinates.length == 4) { // rectangle or square needs 5 points to connect
                                latlngs = coordinates;
                            } else {
                                validation.push('Rectangle landmarks require 2 points');
                            }
                        } else if (shape == 'polygon') { // polygon needs at least 4 points to connect (i.e triangle)
                            if (coordinates.length >= 3) {
                                latlngs = coordinates;
                            } else {                                            
                                validation.push('Polygon landmarks require 3 points');
                            }
                        }
                    }                   
                    
                    if (shape == 'circle' || shape == 'square') { // for circles and squares   
                        data.street_address = $self.data('streetAddress');
                        data.city           = $self.data('city');
                        data.state          = $self.data('state');
                        data.zip            = $self.data('zip');
                        data.country        = $self.data('country');
                        data.id             = 'landmark-radius';
                        data.value          = radius;
                        
                        if (shape == 'circle') {
                            latlngs = [{latitude: data.latitude, longitude: data.longitude}];
                        }
                    }

                    data.coordinates = latlngs;
                    
                    // mark incomplete territory as processed, meaning that it has been rgeo/geo
                    if (Core.Environment.context() == 'landmark/incomplete') {
                        data.process = 1;
                    }
                    
                    if ($.isEmptyObject(validation)) {
                        $.ajax({
                            url: '/ajax/landmark/updateLandmarkInfo',
                            type: 'POST',
                            dataType: 'json',
                            data: data,
                            success: function(responseData) {
                                if (responseData.code === 0) {
                                    var landmarkData = responseData.data.landmark_data,
                                        context = Core.Environment.context(),
                                        isIncomplete = (context == 'landmark/incomplete') ? true : false
                                    ; 
                                    
                                    var markerOptions = {
                                            id: landmarkId,
                                            name: title,
                                            latitude: data.latitude,
                                            longitude: data.longitude,
                                            click: function() {
                                                Map.getLandmarkInfo(Landmark.Common.map, landmarkId, isIncomplete);
                                            }
                                        },
                                        polygonOptions = {
                                            type: shape
                                        }
                                    ;
                                    
                                    if (shape == 'circle') {
                                        polygonOptions.radius = radius;
                                    } else if (shape == 'square') {
                                        polygonOptions.radius = radius;
                                        polygonOptions.points = data.coordinates;
                                    } else {
                                        polygonOptions.points = data.coordinates;
                                    }

                                    Map.addMarkerWithPolygon(Landmark.Common.map, markerOptions, true, polygonOptions);

                                    var elem = (context == 'landmark/map') ? 'li' : 'tr';

                                    $('#landmark-'+elem+'-'+landmarkId).data('latitude', data.latitude)
                                                                       .data('longitude', data.longitude);

                                    // render detail panel with updated info                                
                                    Landmark.Common.DetailPanel.render(landmarkData, function() {
                                        if (! $.isEmptyObject(landmarkData)) {
                                            Map.openInfoWindow(Landmark.Common.map, 'landmark', data.latitude, data.longitude, landmarkData);                                                        
                                        } else {
                                            Map.resetMap(Landmark.Common.map);
                                        }
                                    }, true);                                    
                                      
                                    Map.removeMapClickListener(Landmark.Common.map);
                                    Map.removeTempLandmark(Landmark.Common.map);
                              
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
                        alert(validation.join('\n'));
                    }
    
                });
            },

            initMoreOptions: function() {

                $('#landmark-more-options-toggle').on('click', function() {

                    var $self          = $(this),
                        $container     = $('#landmark-more-options'),
                        $textContainer = $self.find('small')
                    ;
                    $container.slideToggle(300);
                    if ($textContainer.text() == 'Show More Options') {
                        $textContainer.text('Show Less Options');
                    } else {
                        $textContainer.text('Show More Options');
                    }
                });
            }
        },

        Popover: {

            landmarkMethodCheck: function() {
                if( ($('#landmark-method2').attr('id')) && ($('#landmark-shape').attr('id')) ){
                    if( ($('#landmark-method2').val()!='manual-entry') && ($('#landmark-shape').val()!='polygon') ){
                        $('#landmark-method2').val('manual-entry');
                        $('#landmark-method2').trigger('change');
                    }
                }
            },

            initAddLandmark: function() {

                var $method     = $('#landmark-new-method'),
                    $location   = $('#landmark-location-new'),
                    $process    = $('#landmark-new-geo'),
                    $addButtons = $('#popover-landmark-new-persist, #popover-landmark-new-confirm'),
                    $body       = $('body'),
                    $methods    = $('#landmark-method, ' + $method.selector + ''),
                    $processes  = $('#landmark-geo, ' + $process.selector + '')
                ;

                $(document).on('change', '#landmark-method2', function() {

                    switch($(this).val()){

                        case 'manual-entry' : $('#landmark-method').val($(this).val());
                                              if(!($('#landmark-state').attr('disabled'))){                                                
                                                  $('#landmark-state').prop('disabled', false);
                                              }
                                              if(!(bool_Core_DropdownButtonChange)){
                                                bool_Core_DropdownButtonChange=1;
                                                setTimeout("$('#landmark-method').trigger('Core.DropdownButtonChange')",1);
console.log(' ================================================================================================================ #landmark-method2:'+$(this).val());
console.log(' ================================================================================================================ #landmark-method2:'+$(this).val());
console.log(' ================================================================================================================ #landmark-method2:'+$(this).val());
                                              }
                                              break;
                            
                        case    'map-click' : $('#landmark-method').val($(this).val());
                                              $('#landmark-state').prop('disabled', true);
                                              if(!(bool_Core_DropdownButtonChange)){
                                                bool_Core_DropdownButtonChange=1;
                                                setTimeout("$('#landmark-method').trigger('Core.DropdownButtonChange')",1);
console.log(' XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX #landmark-method2:'+$(this).val());
console.log(' XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX #landmark-method2:'+$(this).val());
console.log(' XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX #landmark-method2:'+$(this).val());
                                              }
                                              break;

                    }

                });

                $(document).on('Core.DropdownButtonChange', $methods.selector, function() {

                    setTimeout("bool_Core_DropdownButtonChange='';",500);
console.log('................................................................................... Core.DropdownButtonChange:'+$methods.selector);

                    //var val = $method.val(),
                    var $self       = $(this),
                        id          = $self.prop('id'),
                        val         = $('#landmark-method').val(),
                        placeholder = 'Address or Lat/Long',
                        elem        = '',
                        map         = Landmark.Common.getCurrentMap()
                    ;
                    
                    if (id == 'landmark-new-method') {
                        elem = '-new';
                    } 

                    var $title          = $('#landmark'+elem+'-name'),
                        $radius         = $('#landmark'+elem+'-radius'),
                        $type           = $('#landmark'+elem+'-shape'),
                        $meth           = $('#landmark'+elem+'-method'),
                        $locate         = $('#landmark-location'+elem);
                        methodValue     = $meth.val(),
                        addressValue    = $locate.val(),
                        $addButton      = (id == 'landmark-new-method') ? $('#popover-landmark-new-persist') : $('#landmark-save'),
                        $addSaveButtons = (id == 'landmark-new-method') ? $('#popover-landmark-new-persist, #popover-landmark-new-confirm') : $('#landmark-save, #landmark-restore')
                    ;

                    if (val == 'manual-entry') {

console.log('Core.DropdownButtonChange:'+id+':enableEditLandmarkInfo');
                        Map.removeMapClickListener(Landmark.Common.map);
                        Landmark.Common.enableEditLandmarkInfo();
                        Map.removeTempLandmark(map);
                        $('#secondary-sidebar-scroll').find('li.active').trigger('click');
                        if(!($('.leaflet-popup').is(':visible'))){
                            $('.leaflet-popup').show();
                        }
                        $('#landmark-radius-row').show();                                
                            
                    } else if (val == 'map-click') {

                        $('#landmark-radius-row').hide();                                
                        
console.log('eventCallbacks');
                        var eventCallbacks = {
                            drag: function(data) {
console.log('eventCallbacks:drag');
                                if($('.leaflet-popup').is(':visible')){
                                    $('.leaflet-popup').hide();
                                }
                                var title = $title.val(),
                                    radius = $radius.val()
                                ;
                                if ($self.prop('id') == 'landmark-method') {
                                    title = $title.text();
                                    radius = Landmark.Common.getRadiusFromText($radius.text());
                                }
                                Map.updateTempLandmark(map, $type.val(), data.latitude, data.longitude, radius, title);
                                $locate.val('Waiting...');                                      
                            },
                            dragend: function(data) {
console.log('eventCallbacks:dragend');
                                var a = { latitude: data.latitude, longitude: data.longitude };
                                Core.EditMap.LatLngEdit(a);
                                $addButton.data('latitude', data.latitude).data('longitude', data.longitude);
                                if (Map.api() == 'mapbox') {    // fix for a possible mapbox/leaflet api bug where a 'click' event is triggered after a 'dragend'
                                    setTimeout(function() {
                                        map._preventClick = false;
                                    }, 500);                                                    
                                }  
                            }    
                        };

                        if (id == 'landmark-method') {
                            if ($('#popover-landmark-form').is(':visible')) { //  close 'Add Landmark Popover' if it's opened  
                                $('#popover-landmark-new-cancel').trigger('click');
                            }
console.log('Core.DropdownButtonChange:'+id+':disableEditLandmarkInfo');
                            Landmark.Common.disableEditLandmarkInfo();
                            
                            // hide inline editing landmark radius dropdown
                            $('#landmark-radius-row').hide();
                            
                            // show temp landmark radius dropdown if circle or square
                            if ($type.val() == 'circle' || $type.val() == 'square') {
                                $('#temp-landmark-radius-row').show();
                                $radius = $('#temp-landmark-radius');
                            }

                            if($('#secondary-sidebar-scroll').find('.active').attr('data-event-id')){
                                var lbl = $('#secondary-sidebar-scroll').find('.active').attr('data-event');
                                var lat = $('#secondary-sidebar-scroll').find('.active').attr('data-lat');
                                var lng = $('#secondary-sidebar-scroll').find('.active').attr('data-long');
                                var rad = $('#secondary-sidebar-scroll').find('.active').attr('data-radius');
                                var shp = $('#secondary-sidebar-scroll').find('.active').attr('data-shape');
                                var pts = $('#secondary-sidebar-scroll').find('.active').attr('data-points');
                                Map.clearMarkers(Landmark.Common.map);
                                if (shp == 'circle' || shp == 'square') {
console.log('Map.createTempLandmark(map, shp, lat, lng, rad, lbl, true, eventCallbacks);');
                                    Map.createTempLandmark(map, shp, lat, lng, rad, lbl, true, eventCallbacks);
                                } else if (shp == 'polygon') {
console.log(' >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> lastPolygon');
console.log(lastPolygon);
console.log($addButton);
$.each(lastPolygon, function(key, val) {
    // console.log(val);
    console.log(val.latitude+' / '+val.longitude);
    if(key<1){
console.log("................................................................................. Map.doesTempLandmarkExist(map)");
        // if (Map.doesTempLandmarkExist(map)) {
            Map.removeTempLandmark(map);
        // }
        Map.createTempPolygon(map, shp, val.latitude, val.longitude, rad, '#ff0000', eventCallbacks);
    } else {
        Map.updateTempLandmark(map, shp, val.latitude, val.longitude, rad, lbl);
    }
});
console.log(' >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> lastPolygon');
                                }
                            }

                        }

console.log(' >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Map.addMapClickListener(map, function(event)');
                        Map.addMapClickListener(map, function(event) {
console.log(' >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Map.addMapClickListener(map, function(event)');
                            if ($type.val() == 'circle' || $type.val() == 'square') {

                                Map.reverseGeocode(map, event.latitude, event.longitude, function(result) {

                                    if (result.success == 1) {
                                        
                                        var title = $title.val(),
                                            radius = $radius.val()
                                        ;

                                        if ($self.prop('id') == 'landmark-method') {
                                            title   = $title.text();
                                            radius  = Landmark.Common.getRadiusFromText($radius.text());
                                            
                                            var landmarkId = $('#detail-panel').find('.hook-editable-keys').eq(0).data('landmarkPk');
                                            if (landmarkId != '') {
                                                Map.hideMarker(map, landmarkId);
                                                Map.closeInfoWindow(map);
                                            }
                                            
console.log('disableEditLandmarkInfo');
                                            Landmark.Common.disableEditLandmarkInfo();
                                        }
    
console.log("................................................................................. Map.doesTempLandmarkExist(map)");
                                        if (! Map.doesTempLandmarkExist(map)) {
                                            Map.clearMarkers(Landmark.Common.map);
                                            Map.createTempLandmark(map, $type.val(), result.latitude, result.longitude, radius, title, true, eventCallbacks);                                    
console.log('Map.doesTempLandmarkExist(map):no');
                                        } else {
console.log('Map.doesTempLandmarkExist(map):yes');
                                            Map.updateTempLandmark(map, $type.val(), result.latitude, result.longitude, radius, title);
                                            Map.centerMap(map, result.latitude, result.longitude);
                                        }
        
                                        $locate.val(result.formatted_address);    
        
console.log('result');
console.log(result);
if($('#landmark-latitude').attr('id')){
    $('#landmark-latitude').html(result.latitude);
}
if($('#landmark-longitude').attr('id')){
    $('#landmark-longitude').html(result.longitude);
}
if($('#secondary-sidebar-scroll').find('.active').attr('data-event-id')){
    if(result.address_components.address){ result.address_components.address = result.address_components.address.replace(':',''); }
    if(result.address_components.city){    result.address_components.city = result.address_components.city.replace(':',''); }
    if(result.address_components.state){   result.address_components.state = result.address_components.state.replace(':',''); }
    if(result.address_components.zip){     result.address_components.zip = result.address_components.zip.replace(':',''); }
    if(result.address_components.country){ result.address_components.country = result.address_components.country.replace(':',''); }
    Core.Ajax('landmark-click',result.latitude+':'+result.longitude+':'+result.address_components.address+':'+result.address_components.city+':'+result.address_components.state+':'+result.address_components.zip+':'+result.address_components.country,$('#secondary-sidebar-scroll').find('.active').attr('data-event-id'),'clicklandmark');
}


                                        // $addButton.data('latitude', result.latitude)
                                        //           .data('longitude', result.longitude)
                                        //           .data('street-address', result.address_components.address)
                                        //           .data('city', result.address_components.city)
                                        //           .data('state', result.address_components.state)
                                        //           .data('zip', result.address_components.zip)
                                        //           .data('country', result.address_components.country);

                                        // $addSaveButtons.prop('disabled', false);
                                    } else {
                                        alert(result.error);
                                    }        
                                });
                            }  else if ($type.val() == 'rectangle' || $type.val() == 'polygon') {

                                var title   = $title.val(),
                                    radius  = $radius.val()
                                ;
                                
                                if ($self.prop('id') == 'landmark-method') {
                                    title   = $title.text();
                                    radius  = Landmark.Common.getRadiusFromText($radius.text());
                                    var landmarkId = $('#detail-panel').find('.hook-editable-keys').eq(0).data('landmarkPk');
                                    
                                    if (landmarkId != '') {
                                        Map.hideMarker(map, landmarkId);
                                        Map.closeInfoWindow(map);
                                    }
                                    
console.log('disableEditLandmarkInfo');
                                    Landmark.Common.disableEditLandmarkInfo();
                                }
                                
                                // events for the points
                                var events = {
                                    click: function() {

                                        if ((($type.val() == 'rectangle') && (Map.getTempMarkerArray(map).length == 2)) || (($type.val() == 'polygon') && (Map.getTempMarkerArray(map).length > 2))) {
                                            if ($self.prop('id') == 'landmark-method') {
                                                $addButton.prop('disabled', false);
                                            } else {
                                                $addSaveButtons.prop('disabled', false);                                   
                                            }
                                        } else {
                                            if ($self.prop('id') == 'landmark-method') {
                                                $('#landmark-restore').prop('disabled', false);
                                                $addButton.prop('disabled', true);
                                            }
                                        }
                                    }
                                };
                                
console.log("................................................................................. Map.doesTempLandmarkExist(map)");
                                if (! Map.doesTempLandmarkExist(map)) {
console.log('create draggable landmark');
                                    // Map.createTempLandmark(map, $type.val(), event.latitude, event.longitude, radius, title, true, {}, events);                                    
                                    Map.updateTempLandmark(map, $type.val(), event.latitude, event.longitude, radius, title, events, true);
                                } else {
console.log('update draggable landmark');
                                    Map.updateTempLandmark(map, $type.val(), event.latitude, event.longitude, radius, title, events, true);
console.log('update draggable landmark');
                                }
                                
                                if ((($type.val() == 'rectangle') && (Map.getTempMarkerArray(map).length == 2)) || (($type.val() == 'polygon') && (Map.getTempMarkerArray(map).length > 2))) {
                                    if ($self.prop('id') == 'landmark-method') {
                                        $addButton.prop('disabled', false);
                                    } else {
                                        $addSaveButtons.prop('disabled', false);
                                    }                                   
                                } else {
                                    if ($self.prop('id') == 'landmark-method') {
                                        $('#landmark-restore').prop('disabled', false);
                                        $addButton.prop('disabled', true);
                                    } 
                                }

                                Core.Wizard.Polygon(map);

                            }     
                        });
console.log(' >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Map.addMapClickListener(map, function(event)');
                    }
console.log('................................................................................... Core.DropdownButtonChange:'+$methods.selector);
                });

                /**
                 * Process Location
                 *
                 * Enable Add Buttons once location has been processed
                 *
                 * */
//                $(document).on('click', $process.selector, function() {
                $(document).on('click', $processes.selector, function() {
/*
                    var $title          = $('#landmark-new-name'),
                        $radius         = $('#landmark-new-radius'),
                        $type           = $('#landmark-new-shape'),
                        $addButton      = $('#popover-landmark-new-persist'),
                        addressValue    = $location.val(),
                        methodValue     = $method.val(),
                        map             = {}
                    ;

                    map = Landmark.Common.getCurrentMap();
*/

                    var $self   = $(this),
                        id      = $self.prop('id'),
                        elem    = '',
                        map     = {}
                    ;
                    
                    map = Landmark.Common.getCurrentMap();
                    
                    if (id == 'landmark-new-geo') {
                        elem = '-new';    
                    } else if (id == 'landmark-geo') {
                        // close 'Add Landmark' popover if it's opened
                        if ($('#popover-landmark-form').is(':visible')) {
                            $('#popover-landmark-new-cancel').trigger('click');
                        }
                    }
                    
                    var $title          = $('#landmark'+elem+'-name'),
                        $radius         = $('#landmark'+elem+'-radius'),
                        $type           = $('#landmark'+elem+'-shape'),
                        $meth           = $('#landmark'+elem+'-method'),
                        $locate         = $('#landmark-location'+elem);
                        methodValue     = $meth.val(),
                        addressValue    = $locate.val(),
                        $addButton      = (id == 'landmark-new-geo') ? $('#popover-landmark-new-persist') : $('#landmark-save'),
                        $addSaveButtons = (id == 'landmark-new-geo') ? $('#popover-landmark-new-persist, #popover-landmark-new-confirm') : $('#landmark-save, #landmark-restore')
                    ;
                    
                    if (addressValue != undefined && addressValue != '') {
   
                        // find out if an address or lat/lng pair was entered
                        var separatorList   = [',', ' ', '/'],
                            separator       = -1,
                            index           = 0,
                            latlng          = [],
                            lat             = '',
                            lng             = '',
                            type            = 'address'
                        ;
                        
                        // get the separator character
                        for (index=0; index<separatorList.length; index++) {
                            separator = addressValue.indexOf(separatorList[index]);
                            if (separator != -1) {
                                break;
                            }
                        }
                        
                        if (separator != -1) { // if valid separator character was found, break up string and find out the type
                            
                            latlng = addressValue.split(separatorList[index]);
                            if (latlng.length == 2) {
                                lat = parseFloat(latlng[0]);
                                lng = parseFloat(latlng[1]);
                                
                                if (lat != '' && lng != '' && (lat >= -90 && lat <= 90) && (lng >= -180 && lng <= 180)) {
                                    type = 'latlng';       
                                }
                            }
                        }
                        
                        if (type == 'latlng') {
                            Map.reverseGeocode(map, lat, lng, function(result) {
                                if (result.success == 1) {
                                
                                    Landmark.Common.updateMap(map, $self, $title, $radius, $type, $addButton, $addSaveButtons, $locate, result);
                                   
                                } else {
                                    alert(result.error);
                                }        
                            });
                        } else if (type == 'address') {
                            Map.geocode(map, addressValue, function(data) {
                                if (data.success == 1) {

                                    Landmark.Common.updateMap(map, $self, $title, $radius, $type, $addButton, $addSaveButtons, $locate, data);

                                } else {
                                    alert(data.error);
                                }    
                            });    
                        } 
                    } else {
                        alert('Please enter an address/coordinate to be geocoded');
                    }
                });

                /**
                 *
                 * Add buttons clicked
                 *
                 * */
                $(document).on('click', $addButtons.selector, function() {

                    var $self       = $(this),
                        $popoverDiv = $self.closest('.popover'),
                        $addButton  = $('#popover-landmark-new-persist'),
                        map         = Landmark.Common.getCurrentMap(),
                        point       = Map.getTempMarkerPosition(map),
                        latlngs     = []   
                    ;


                    var latitude        = point.latitude,
                        longitude       = point.longitude,
                        streetAddress   = $addButton.data('streetAddress'),
                        city            = $addButton.data('city'),
                        state           = $addButton.data('state'),
                        zip             = $addButton.data('zip'),
                        country         = $addButton.data('country'),
                        title           = $('#landmark-new-name').val(),
                        radius          = $('#landmark-new-radius').val(),
                        validation      = [],
                        method          = $method.val(),
                        shape           = $('#landmark-new-shape').val(),
                        type            = $('#landmark-new-type').val(),
                        group           = $('#landmark-new-group').val()
                    ;
                    
                    
                    if (shape == 'rectangle' || shape == 'polygon' || shape == 'square') { // prep coordinates for rectangle, polygon, and square
                    
                        // get the coordinates of the temp polygon as an array of objects, each with two properties called 'latitude' and 'longitude'
                        // (i.e. [{latitude: 12.345, longitude: 45.56}, {latitude: 34.45, longitude: 56.788}, ...]) 
                        var coordinates = Map.getTempPolygonPoints(map);

                        if ((shape == 'rectangle' || shape == 'square')) {
                            if (coordinates.length == 4) { // rectangle or square needs 4 points to connect
                                latlngs = coordinates;
                            } else {
                                validation.push('Square landmarks require 4 points');
                            }
                        } else if (shape == 'polygon') { // polygon needs at least 3 points to connect (i.e triangle)
                            if (coordinates.length >= 3) {
                                latlngs = coordinates;
                            } else {                                            
                                validation.push('Polygon landmarks require 3 points');
                            }
                        }
                    } else {                                                                // prep coordinates for circle
                        latlngs = [{latitude: latitude, longitude: longitude}];
                    }                  

                    if (title == '') {
                        validation.push('- Landmark name cannot be blank');
                    }
                    
                    if (latitude == null || longitude == null) {
                        validation.push('- Invalid latitude and/or longitude'); 
                    }
                    
                    if (shape == 'circle' && radius == '' || radius == null) {
                        validation.push('- Invalid radius');
                    }
                    
                    if (type == '') {
                        validation.push('- Invalid landmark category');    
                    }                   

                    if (shape == '') {
                        validation.push('- Invalid landmark shape');    
                    }
                    
                    if (group == '') {
                        validation.push('- Invalid landmark group');
                    }                    
                    
                    if ($.isEmptyObject(validation)) {
                        $.ajax({
                            url: '/ajax/landmark/saveLandmark',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                shape: shape,
                                type: type,
                                group: group,
                                latitude: latitude,
                                longitude: longitude,
                                title: title,
                                radius: radius,
                                street_address: streetAddress,
                                city: city,
                                state: state,
                                zip: zip,
                                country: country,
                                coordinates: latlngs
                            },
                            success: function(responseData) {
                                if (responseData.code === 0) {
                                    var drawMarkers = false;
                                    if ($self.is('#popover-landmark-new-confirm')) { // Add+Close
                                        $popoverDiv.find('.close').trigger('click');
                                        drawMarkers = true;
                
                                    } else if ($self.is('#popover-landmark-new-persist')) { // Add
                                        Landmark.Common.Popover.resetAddLandmarkPopoverForm();
                                        $addButtons.prop('disabled', true);        
                                    }
                                    
                                    if (Core.Environment.context() == 'landmark/map') {

                                        // set landmark filters to 'All' and paginate to the page that has the new landmark (select landmark if 'Add + Close' was clicked) 
                                        $('#select-landmark-group-filter').val('All').text('All');
                                        $('#select-landmark-category-filter').val('All').text('All');
                                        $('#filter-territory-all').prop('checked', true);
                                        $('#territory-view-mode-status').text('(All)');
                                        
                                        Landmark.Common.SecondaryPanel.fetchFilteredLandmarks('group_filter', drawMarkers, responseData.data.territory_id);
                                    } else if (Core.Environment.context() == 'landmark/list') {
                                        Landmark.List.DataTables.landmarkListTable.fnStandingRedraw();             
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
                        alert(validation.join('\n'));
                    }
                });

                /**
                 *
                 * Add buttons hover - show tooltip only if buttons are disabled
                 *
                 * */
                /*var addButtonsContainer = $addButtons.closest('.has-tooltip');
                addButtonsContainer.hover(
                    // mouseenter
                    function() {
                        if ($addButtons.eq(0).is(':disabled')) {
                            addButtonsContainer.tooltip('show');
                        }
                    },
                    // mouseleave
                    function() {
                        addButtonsContainer.tooltip('hide');
                    }
                );*/

                $(document).on('mouseenter', $addButtons.selector, function() {
                    var $self      = $(this),
                        $container = $self.closest('.has-tooltip')
                    ;

                    if ($self.is(':disabled')) {
                        Core.Tooltip.init();
                        $container.tooltip('show')
                    }

                })

                $(document).on('mouseleave', $addButtons.selector, function() {
                    var $self      = $(this),
                        $container = $self.closest('.has-tooltip')
                    ;

                    $container.tooltip('hide')
                })
                
                /**
                 * Activate Map Click Listener for when Add Landmark is clicked
                 *
                 */
                $('#popover-landmark-new').on('shown.bs.popover', function() {
                    var map = Landmark.Common.getCurrentMap();
                    
                    if (Core.Environment.context() == 'landmark/map') {
                        // clear markers and reset map for drawing
                        $('#landmark-toggle-none').trigger('click');
                    }
                    
                    if (Core.Environment.context() == 'landmark/list') {
                        Map.resetMap(map);
                        Map.resize(map);
                    }
                });

                /**
                 * Reset Add Landmark form to default values after closing popover
                 *
                 */                
                $('#popover-landmark-new').on('hidden.bs.popover', function() {   
                    if ($('#landmark-method').val() == 'map-click') {
                        setTimeout(function() {
                            $('#landmark-method').trigger('Core.DropdownButtonChange');
                        }, 500);
                    }
                    
                    Landmark.Common.Popover.resetAddLandmarkPopoverForm(true);    
                });

                /**
                 * Update New landmark title
                 *
                 */                
                $body.on('keyup', '#landmark-new-name', function() {

                    var map = Landmark.Common.getCurrentMap();

                    Map.updateTempMarker(map, {title: $(this).val()});     
                });
                
                 /**
                 * Update New landmark shape/radius
                 *
                 */                
                $body.on('Core.DropdownButtonChange', '#landmark-new-shape, #landmark-new-radius', function() {
                    var map     = Landmark.Common.getCurrentMap();
                    var $self   = $(this),
                        id      = $self.prop('id'),
                        value   = $self.val()
                    ;
                    
                    if (id == 'landmark-new-shape' && (value == 'polygon' || value == 'rectangle')) {   // if shape changed
                        // clear existing temp landmark
                        Map.removeTempLandmark(map);
                        $('#landmark-new-method').val('map-click').text('Use Map Click').trigger('Core.DropdownButtonChange');
                    } else {                            // else if radius changed
console.log("................................................................................. Map.doesTempLandmarkExist(map)");
                        if (Map.doesTempLandmarkExist(map)) {
                            var options = {};
                            
                            options.type = $('#landmark-new-shape').val();
                            
                            if (options.type == 'circle' || options.type == 'square') {
                                options.radius = $('#landmark-new-radius').val();
                            }
                            
                            Map.updateTempPolygon(map, options);
                        }                        
                    }     
                });                                               

            },
            
            resetAddLandmarkPopoverForm: function(removeClickListener) {
                
                var map = Landmark.Common.getCurrentMap();
            
                // clear temp marker and polygon from map
console.log("................................................................................. Map.doesTempLandmarkExist(map)");
                if (Map.doesTempLandmarkExist(map)) {
                    Map.removeTempLandmark(map);
                }    

                if (removeClickListener != undefined && removeClickListener == true) {
                    // remove map click listener
                    Map.removeMapClickListener(map);
                }

                // reset all input fields
                $('#landmark-new-radius').val("330").text("1/16 Mile");
                $('#landmark-new-name').val('');
                $('#landmark-location-new').val('').text('');
                $('#landmark-new-group').siblings('.dropdown-menu').find('a').filter(':first').trigger('click');
                $('#landmark-new-shape').siblings('.dropdown-menu').find('a').filter(':first').trigger('click');
                
                // remove current lat/lng of temp landmark from Add button
                $('#popover-landmark-new-persist').data('latitude', null)
                                                  .data('longitude', null)
                                                  .data('street-address', null)
                                                  .data('city', null)
                                                  .data('state', null)
                                                  .data('zip', null)
                                                  .data('country', null);
                                                  
                $('#popover-landmark-new-persist, #popover-landmark-new-confirm').prop('disabled', true);
                
                $('#landmark-new-method').val('manual-entry').text('Manual Entry');
            },
            
            initImportExportLandmark: function () {
                
                var $body = $('body');

                /**
                 * Add Address Popover - Import Reference Address button
                 *
                 */
                $body.on('click', '#landmark-import-csv-file', function() {
                    $('#popover-landmark-import-confirm').removeClass('disabled');
                    $('#landmark-import-csv-info-div').show();
                    $('#landmark-import-csv-success-div').hide();
                }); 

                $body.on('click', '.download-landmark-template', function() {
                    window.open("/assets/media/downloads/import_address_template.csv");
                }); 
                
                $body.on('click', '#popover-landmark-import-confirm', function() {
                    $('#popover-landmark-import-confirm').addClass('disabled');
                    $('#landmark-import-csv-info-div').hide();
                    $('#landmark-import-csv-success-div').html('uploading...').show();                                
                }); 
                
                $body.on('Core.Upload', '#popover-landmark-import-confirm', function() {

                    var iframeId = $(this).data('iframeId'),
                        responseData = Core.Upload.getResponse(iframeId)
                    ;

                    if (! $.isEmptyObject(responseData)) {

                        if (responseData.code === 0) {

                            switch(Core.Environment.context()){

                                case    'landmark/incomplete' :
                                case          'landmark/list' : // Landmark.List.DataTables.landmarkListTable.fnStandingRedraw();
                                                                $('.report-master').find('.dataTables-search-btn').trigger('click');
                                                                break;

                            }

                        }
   
                        if (! $.isEmptyObject(responseData.upload_message) && responseData.upload_message != '') {
                            $('#landmark-import-csv-success-div').html(responseData.upload_message).show();
                            // $('#button-landmark-import').trigger('Core.PopoverContentChange');
                        } else {
                            $('#landmark-import-csv-success-div').html('Upload Complete').show();
                        }
                        
                        if (! $.isEmptyObject(responseData.message)) {
                            Core.SystemMessage.show(responseData.message, responseData.code);                                    
                        }
                    }
                    
                    $('#landmark-import-csv-file').val('');

                }); 
                
                /**
                 * Export Filter Landmark List table
                 *
                 */
                $body.on('click', '#popover-landmark-list-export-csv-confirm, #popover-landmark-list-export-pdf-confirm', function() {
                    var exportFormat                = $(this).prop('id') == 'popover-landmark-list-export-pdf-confirm' ? 'pdf' : 'csv';
                    var $secondaryPanelPagination   = $('#secondary-panel-pagination');
                    var searchLandmarkString        = $('#text-landmark-search').val().trim();
                    var search_string               = searchLandmarkString;
                    var landmarkgroup_id            = $('#select-landmark-group-filter').val().trim();
                    //var landmark_type               = $('#filter-territory-all, #filter-territory-landmark, #filter-territory-reference').filter(':checked').val();

                    landmark_type = 'landmark';

                    if (search_string != '') {
                        window.location = '/ajax/landmark/exportFilteredLandmarkList/' + exportFormat + '/string_search/' + search_string + '/All';
                    } else {
                        window.location = '/ajax/landmark/exportFilteredLandmarkList/' + exportFormat + '/group_filter/' + landmarkgroup_id + '/' + landmark_type;
                    }
                });
                
                 $body.on('click', '#popover-landmark-export-csv-confirm, #popover-landmark-export-pdf-confirm', function() {
                    var exportFormat = $(this).prop('id') == 'popover-landmark-export-pdf-confirm' ? 'pdf' : 'csv';
                    var landmarkId = $('#detail-panel').find('.hook-editable-keys').eq(0).data('landmark-pk');
                    if (landmarkId != '' && landmarkId != undefined) {
                        window.location = '/ajax/landmark/exportLandmark/' + exportFormat + '/' + landmarkId;
                    }
                });
            },

            initTerritoryMode: function() {

                var $radioGroup                 = $('input[name="territory-filter"]'),
                    $status                     = $('#territory-view-mode-status'),
                    $secondaryPanelPagination   = $('#secondary-panel-pagination')
                ;

                // reset radio group on page load
                $radioGroup.eq(0).prop('checked', true);

                $(document).on('change', $radioGroup.selector, function() {

                    var $checked = $radioGroup.filter(':checked');
                    $status.text('('+$checked.next('span').text()+')');

                    // filter landmark list
                    $('#text-landmark-search').val('');
                    $secondaryPanelPagination.data('paging','');
                    $secondaryPanelPagination.data('landmarkStartIndex', 0);

                    if (Core.Environment.context() == 'landmark/map') {
                        
                        // need to clear map
                        Map.clearMarkers(Landmark.Common.map);
                        
                        // request landmarks for listing for filter params
                        Landmark.Common.SecondaryPanel.fetchFilteredLandmarks('group_filter');
                    }

                    if (Core.Environment.context() == 'landmark/list') {
                        // clear out the search box before redrawing table
                        $('#text-landmark-search').val('');
                        Landmark.List.DataTables.landmarkListTable.fnDraw();
                    }

                    if (Core.Environment.context() == 'landmark/incomplete') {
                        // clear out the search box before redrawing table
                        $('#text-landmark-search').val('');
                        Landmark.Incomplete.incompleteLocationTable.fnDraw();
                    }
                    
                });

            }

        },
        
        getCurrentMap: function() {
            return ((Core.Environment.context() == 'landmark/map' || ((Core.Environment.context() == 'landmark/list') && ($('.modal-dialog').is(':visible'))) || Core.Environment.context() == 'landmark/incomplete' || Core.Environment.context() == 'landmark/verification') ? Landmark.Common.map : Landmark.List.map);
        },
        
        getRadiusFromText: function(radiusText) {
            var radiusString = radiusText,
                radius = ''
            ;
            
            if (radiusString != undefined && radiusString != '') {
                var temp = radiusString.split(' ')[0];
                if (temp != undefined && temp != '') {
                    var nums = temp.split('/');
                    if (nums.length == 2) {
                        radius = parseFloat(nums[0]/nums[1]).toFixed(3);
                    } else {
                        radius = nums[0];
                    }
                    radius = radius * 5280;  // convert miles to feet
                }
            } 

            return radius;
        },
        
        disableEditLandmarkInfo: function() {
            
            // temporarily disaable all editable fields
            // $('#landmark-name, #landmark-group, #landmark-type, #landmark-radius, #landmark-category').editable('disable').data('disabled', true);
            
            // disable changing the landmark shape dropdown
            $('#landmark-shape').addClass('disabled');
            $('#landmark-shape').siblings().filter(':button').addClass('disabled');
            
            $('#landmark-unit').siblings().filter(':button').addClass('disabled');
            
            // enable the restore button
            $('#landmark-restore').prop('disabled', false);       
        },
        
        enableEditLandmarkInfo: function() {
            if ($('#landmark-name').data('isEditable') == 1) {
                // enable all editable fields and shape dropdown
                $('#landmark-name, #landmark-group, #landmark-type, #landmark-radius, #landmark-category').editable('enable').data('disabled', false);
                $('#landmark-shape').removeClass('disabled');
                $('#landmark-shape').siblings().filter(':button').removeClass('disabled');    
            }
        },
        
        updateMap: function(map, $self, $title, $radius, $type, $addButton, $addSaveButtons, $locate, result) {
            // remove existing temp landmark
            Map.removeTempLandmark(map);
            
            var title = $title.val(),
                radius = $radius.val()
            ;
            
            if ($self.prop('id') == 'landmark-geo') {
                title = $title.text();
                radius = Landmark.Common.getRadiusFromText($radius.text());

                // hide original marker                
                var landmarkId = $('#detail-panel').find('.hook-editable-keys').eq(0).data('landmarkPk');
                if (landmarkId != '') {
                    Map.hideMarker(map, landmarkId);
                    Map.closeInfoWindow(map);
                }
                
                // disable editing landmark info
console.log('disableEditLandmarkInfo');
                Landmark.Common.disableEditLandmarkInfo();
                
                if ($('#popover-landmark-form').is(':visible')) { //  close 'Add Landmark Popover' if it's opened  
                    $('#popover-landmark-new-cancel').trigger('click');
                }
                
                // hide inline editing landmark radius dropdown
                $('#landmark-radius-row').hide();
                
                // show temp landmark radius dropdown if circle or square
                if ($type.val() == 'circle' || $type.val() == 'square') {
                    $('#temp-landmark-radius-row').show();
                }
            }
            
console.log("................................................................................. Map.doesTempLandmarkExist(map)");
            if (! Map.doesTempLandmarkExist(map)) {
                var eventCallbacks = {
                    drag: function(data) {
            
                        var title = $title.val(),
                            radius = $radius.val()
                        ;
                        
                        if ($self.prop('id') == 'landmark-geo') {
                            title = $title.text();
                            radius = Landmark.Common.getRadiusFromText($radius.text());
                        }
                    
                        Map.updateTempLandmark(map, $type.val(), data.latitude, data.longitude, radius, title);
                        $locate.val('Waiting...');                                      
                    },
                    dragend: function(data) {
                        Map.centerMap(map, data.latitude, data.longitude);
                        Map.reverseGeocode(map, data.latitude, data.longitude, function(data1) {
                            if (data1.success == 1) {
                                $locate.val(data1.formatted_address);

                                $addButton.data('latitude', data1.latitude)
                                          .data('longitude', data1.longitude)
                                          .data('street-address', data1.address_components.address)
                                          .data('city', data1.address_components.city)
                                          .data('state', data1.address_components.state)
                                          .data('zip', data1.address_components.zip)
                                          .data('country', data1.address_components.country);                                                    
                            } else {
                                $locate.val('').text('');
                                alert(data1.error);
                            }    
                        });                                            
                        $addButton.data('latitude', data.latitude).data('longitude', data.longitude);
            
                        if (Map.api() == 'mapbox') {    // fix for a possible mapbox/leaflet api bug where a 'click' event is triggered after a 'dragend'
                            setTimeout(function() {
                                map._preventClick = false;
                            }, 500);
                        }  

                    }    
                };
            
                Map.createTempLandmark(map, $type.val(), result.latitude, result.longitude, radius, title, true, eventCallbacks);                                    
            }
            
            $locate.val(result.formatted_address);    
            
            $addButton.data('latitude', result.latitude)
                      .data('longitude', result.longitude)
                      .data('street-address', result.address_components.address)
                      .data('city', result.address_components.city)
                      .data('state', result.address_components.state)
                      .data('zip', result.address_components.zip)
                      .data('country', result.address_components.country);
                      
            
            if ($self.prop('id') == 'landmark-geo') {
                if ($type.val() == 'circle' || $type.val() == 'square') {
                    $addSaveButtons.prop('disabled', false);    
                } else {
                    $('#landmark-restore').prop('disabled', false);
                    $('#landmark-method').val('map-click').text('Use Map Click').trigger('Core.DropdownButtonChange');
                }  
            } else {
                $addSaveButtons.prop('disabled', false);
            }
            
        }
    }

});