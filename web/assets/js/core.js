/*

    Core JSON

    File:       /assets/js/core.js
    Author:     Tom Leach
*/
var addMapBool=0;
var addMapCount=0;
var addMapCoordinates=null;
var adjustLayoutCount=0;
var allClear=0;
var allNone=0;
var allNoneNewList=0;
var ajaxSkip='';
var bool_Core_DropdownButtonChange='';
var bool_loadTransferDevices='';
var breadcrumbs='';
var command_in_process='';
var clearAllMarkers = '';
var countOpen=0;
var countScheduleReport=0;
var createUpdateCount=0;
var currentLink2Input='';
var currentLink2Select='';
var currentMousePosX='-1';
var currentMousePosY='-1';
var currentUnitId='';
var currentEventData='';
var currentUnitData='';
var currentUnitIdHidePanel='';
var currentLandmarkId='';
var currentLandmarkIdHidePanel='';
var currentRefreshEid='';
var currentRepoUrl='';
var currentScheduledReportId='';
var datePickerId='';
var dblCheck='';
var deSelectCnt='';
var dontDeSelect='';
var editMapCoordinates=null;
var editMapCount=0;
var formFillBool='';
var greenCount=30;
var justTheseDevices=[];
var lastDropdown='';
var lastLatLandmark=0;
var lastLongLandmark=0;
var lastLatVehicle=0;
var lastLongVehicle=0;
var lastPolygon=[];
var logoutCounter=15;
var mapRepaint=9;
var mapAutoRefreshMode=0;
var mapZoomBool='';
var newZoomLevel=1;
var oncelerAjax='';
var pointsUpdate=[];
var popupUnitGroup=0;
var refreshOnChange='';
var repoKey='';
var secondClick='';
var singleUnitId='';
var skipRefresh='';
var tabSkip = '';
var transferManifest='';
var triggerDelay=0;
var uid_toggle='';
var updateDisabled=0;
var verificationAddAddress = '';
var verificationAddCity = '';
var verificationAddState = '';
var verificationAddZip = '';
var verificationAddCountry = '';
var verificationAddLat = '';
var verificationAddLng = '';

var wizardL2I='';

$(document).ready(function() {

    var context = Core.Environment.context().split('/');
    if(context[0]=='repo'){
        Core.isLoaded();
        Core.initMapModal();
        $('#repoKey-refresh').trigger('click');
    } else {
                                        
        Core.isLoaded();
        Core.Header.initMenu();
        Core.Help.init();
        Core.Editable.init();
        Core.Tooltip.init();
        Core.Popover.init();
        Core.DatePicker.init();
        Core.Viewport.initResize();
        Core.Viewport.initPageTransitions(300, 300);
        Core.ButtonDropdown.init();
        Core.ButtonGroupToggle.init();
        Core.ButtonFileInput.init();
        Core.DragDrop.init();
        Core.MasterDetailList.init();
        Core.Upload.init();
        Core.Wedge.init();
        Core.Cookie.init();
        Core.MyAccount.Modal.init();
        Core.Session.init();
        Core.Wizard.LogoutCounter();

        switch(Core.Environment.context()){

            case          'alert/contact' :
            case             'alert/list' :
            case                 'login/' :
            case        'forgotpassword/' :
            case        'forgotusername/' : break;

                                  default : Core.initMapModal();
                                            
        }

        //Core.initKillFormEnterKey();
        Core.Viewport.adjustLayout();

        switch(Core.Environment.context()){

            case             'admin/repo' : Core.DataTable.pagedReport('repo-list-table');
                                            break;

            case           'landmark/map' :
            case            'vehicle/map' : if($('#sidebar-left-toggle').is(':visible')){
    console.log("$('#sidebar-left-toggle').trigger('click')");
                                                setTimeout("$('#sidebar-left-toggle').trigger('click')",1000);
                                                if($('#sidebartoggle').hasClass('got-it')){
                                                    setTimeout("$('#link-modal-sidebar-left').trigger('click');Core.FixModal.FixModal('link-modal-sidebar-left');",1000);
                                                }
                                            }
                                            break;

            case            'report/list' : if($('#sidebar-left-toggle').is(':visible')){
                                                setTimeout("$('#sidebar-left-toggle').trigger('click')",2000);
                                            }
                                            break;

            case         'system/library' : Core.DataTable.pagedReport('library-list-table');
                                            break;

            case           'system/sales' : Core.DataTable.pagedReport('sales-report-table');
                                            break;

            case          'vehicle/batch' :
            case 'vehicle/commandhistory' : Core.DataTable.pagedReport('batch-command-table');
                                            break;

            case     'vehicle/batchqueue' :
            case   'vehicle/commandqueue' : Core.DataTable.pagedReport('batch-queue-table');
                                            break;

        }

    }

});

var Core = {};

jQuery.extend(Core, {

    map: undefined,

    AddMap: {

        Address: function (startOver) {
            if(!(addMapBool)){
                addMapBool=1;
                Map.resetMap(Landmark.Common.addmap);
                Map.resize(Landmark.Common.addmap);
                Map.updateMapBound(Landmark.Common.addmap);
                Map.updateMapBoundZoom(Landmark.Common.addmap);
                // Map.mapZoom(Landmark.Common.addmap,'4');
            }
            if(startOver){
                $('#landmark-error').html('');
                addMapCount=startOver;
            }
            switch(Core.Environment.context()){

                case    'landmark/incomplete' : setTimeout('if(addMapCount>1){addMapCount--;Core.AddMap.Address();}else{if(addMapCount>0){Core.AddMap.AddressFix();}addMapCount=0;}',100);
                                                break;

                                      default : setTimeout('if(addMapCount>1){addMapCount--;Core.AddMap.Address();}else{if(addMapCount>0){Core.AddMap.AddressGet();}addMapCount=0;}',100);
                                                
            }
        },

        AddressFix: function () {
            $('#landmark-fix-latitude').text('');                            
            $('#landmark-fix-longitude').text('');                            
            var address = '';
            var addressBool = '';
            if($('#landmark-fix-street-address').val()){
                address+=$('#landmark-fix-street-address').val();
                addressBool=1;
            }
            if($('#landmark-fix-city').val()){
                if(addressBool){
                    address+=', ';
                }
                address+=$('#landmark-fix-city').val();
                addressBool=1;
            }    
            if($('#landmark-fix-state').val()){
                if(addressBool){
                    address+=', ';
                }
                address+=$('#landmark-fix-state').val();
                addressBool=1;
            }    
            if($('#landmark-fix-zip').val()){
                if(addressBool){
                    address+=', ';
                }
                address+=$('#landmark-fix-zipcode').val();
                addressBool=1;
            }    
            if($('#landmark-fix-country').val()){
                if(addressBool){
                    address+=', ';
                }
                address+=$('#landmark-fix-country').val();
                addressBool=1;
            }    
console.log('Core.AddMap.AddressGet:'+address);
            Map.geocode(Landmark.Common.addmap, address, function(results, status){
                if(results){
                    console.log('Core.AddMap.AddressGet:results...');
                    console.log(results);
                    $('#landmark-fix-latitude').text(results.latitude);                            
                    $('#landmark-fix-longitude').text(results.longitude);                            
                    Map.clearMarkers(Landmark.Common.addmap);
                    addMapCoordinates = null;

                    var shape = $('#landmark-fix-shape').val();
                    var radius = $('#landmark-fix-radius').val();
console.log('shape:'+shape);
                    if (shape == 'rectangle' || shape == 'polygon' || shape == 'square') { // for rectangles, squares, and polygons
                        // get the coordinates of the temp polygon as an array of objects, each with two properties called 'latitude' and 'longitude'
                        // (i.e. [{latitude: 12.345, longitude: 45.56}, {latitude: 34.45, longitude: 56.788}, ...]) 
                        var coordinates = Map.getTempPolygonPoints(Landmark.Common.addmap);
                        
                        if ((shape == 'rectangle' || shape == 'square')) {
                            if (coordinates.length == 4) { // rectangle or square needs 5 points to connect
                                addMapCoordinates = coordinates;
                            } else {
                                console.log('Rectangle landmarks require 2 points');
                            }
                        } else if (shape == 'polygon') { // polygon needs at least 4 points to connect (i.e triangle)
                            if (coordinates.length >= 3) {
                                addMapCoordinates = coordinates;
                            } else {                                            
                                console.log('Polygon landmarks require 3 points');
                            }
                        }
                    }

                    var polygonOptions=Array();
                    polygonOptions.type = shape;
                    if (shape == 'circle') {
                        polygonOptions.radius = radius;
                    } else if (shape == 'square') {
                        polygonOptions.radius = radius;
                        polygonOptions.points = addMapCoordinates;
                    } else {
                        polygonOptions.points = addMapCoordinates;
                    }

console.log('polygonOptions...');
console.log(polygonOptions);

                    Map.addMarkerWithPolygon(
                        Landmark.Common.addmap,
                        {
                            id: 999,
                            name: address + ' (' + results.latitude + ' / ' + results.longitude + ')',
                            latitude: results.latitude,
                            longitude: results.longitude
                        },
                        false,
                        polygonOptions
                    );

                    Map.resetMap(Landmark.Common.addmap);
                    Map.resize(Landmark.Common.addmap);
                    Map.updateMapBound(Landmark.Common.addmap);
console.log(address)
                    if(address=='USA'){
                        Map.mapZoom(Landmark.Common.addmap,'4');
                    } else {
                        Map.updateMapBoundZoom(Landmark.Common.addmap);
                    }
                } else {
                    Core.log('Core.AddMap.AddressGet:No Results');
                }
            });        

        },

        AddressReverse: function (startOver) {
            if(startOver){
                addMapCount=startOver;
            }
            setTimeout('if(addMapCount>1){addMapCount--;Core.AddMap.AddressReverse();}else{if(addMapCount>0){Core.AddMap.AddressReverseGet();}addMapCount=0;}',100);
        },

        AddressReverseGet: function () {
            var latlng = { latitude: $('#landmark-add-latitude').val(), longitude: $('#landmark-add-longitude').val() };
            Core.AddMap.LatLng(latlng);        
        },

        AddressGet: function () {
            $('#landmark-add-latitude').val('');                            
            $('#landmark-add-longitude').val('');                            
            var address = '';
            var addressBool = '';
            if($('#landmark-add-street_address').val()){
                address+=$('#landmark-add-street_address').val();
                addressBool=1;
            }
            if($('#landmark-add-city').val()){
                if(addressBool){
                    address+=', ';
                }
                address+=$('#landmark-add-city').val();
                addressBool=1;
            }    
            if($('#landmark-add-state').val()){
                if(addressBool){
                    address+=', ';
                }
                address+=$('#landmark-add-state').val();
                addressBool=1;
            }    
            if($('#landmark-add-zip').val()){
                if(addressBool){
                    address+=', ';
                }
                address+=$('#landmark-add-zip').val();
                addressBool=1;
            }    
            if($('#landmark-add-country').val()){
                if(addressBool){
                    address+=', ';
                }
                address+=$('#landmark-add-country').val();
                addressBool=1;
            }    
console.log('Core.AddMap.AddressGet:'+address);
            Map.geocode(Landmark.Common.addmap, address, function(results, status){
                if(results){
                    $('#landmark-add-latitude').val(results.latitude);                            
                    $('#landmark-add-longitude').val(results.longitude);                            
                    Map.clearMarkers(Landmark.Common.addmap);
                    addMapCoordinates = null;

                    var shape = $('#landmark-add-shape').val();
                    var radius = $('#landmark-add-radius').val();

                    if (shape == 'rectangle' || shape == 'polygon' || shape == 'square') { // for rectangles, squares, and polygons
                        var lat = $('#landmark-add-latitude').val();
                        var lng = $('#landmark-add-longitude').val();
                        // get the coordinates of the temp polygon as an array of objects, each with two properties called 'latitude' and 'longitude'
                        // (i.e. [{latitude: 12.345, longitude: 45.56}, {latitude: 34.45, longitude: 56.788}, ...]) 
                        var coordinates = Map.getTempPolygonPoints(Landmark.Common.addmap);
                        
                        if ((shape == 'rectangle' || shape == 'square')) {
                            if (coordinates.length == 4) { // rectangle or square needs 5 points to connect
                                addMapCoordinates = coordinates;
                            } else {
                                console.log('Rectangle landmarks require 2 points');
                            }
                        } else if (shape == 'polygon') { // polygon needs at least 4 points to connect (i.e triangle)
                            if (coordinates.length >= 3) {
                                addMapCoordinates = coordinates;
                            } else {
                                if(((lat>=0)||(lat<0))&&((lng>=0)||(lng<0))){
                                    var points = [];
                                    lat = Math.floor(lat*100000)/100000 + 0.001 ;
                                    lng = Math.floor(lng*100000)/100000 - 0.001 ;
                                    points.push({ latitude: lat, longitude: lng });
                                    lat = lat - 0.002 ;
                                    // lng = lng - 0.002 ;
                                    points.push({ latitude: lat, longitude: lng });
                                    // lat = lat + 0.002 ;
                                    lng = lng + 0.002 ;
                                    points.push({ latitude: lat, longitude: lng });
                                    lat = lat + 0.002 ;
                                    // lng = lng + 0.002 ;
                                    points.push({ latitude: lat, longitude: lng });
                                    addMapCoordinates = points;
                                } else {
                                    console.log('Polygon landmarks require 3 points');
                                }
                            }
                        }
                    }

                    var polygonOptions=Array();
                    polygonOptions.title = $('#landmark-add-name').val();
                    polygonOptions.type = shape;
                    if (shape == 'circle') {
                        polygonOptions.radius = radius;
                    } else if (shape == 'square') {
                        polygonOptions.radius = radius;
                        polygonOptions.points = addMapCoordinates;
                    } else {
                        polygonOptions.points = addMapCoordinates;
                    }

                    var map = Landmark.Common.addmap;
                    if (Map.doesTempLandmarkExist(map)) {
                        Map.removeTempLandmark(map);
                    }

                    Core.AddMap.Landmark(map,results.latitude,results.longitude,polygonOptions.type,polygonOptions.radius,polygonOptions.points,polygonOptions.title,address);

                } else {
                    Core.log('Core.AddMap.AddressGet:No Results');
                }
            });        

        },

        Landmark: function (map,latitude,longitude,shape,radius,points,title,address) {

console.log("......... Landmark: function (map="+map+",latitude="+latitude+",longitude="+longitude+",shape="+shape+",radius="+radius+",points="+points+",title="+title+",address="+address+")");

            var $title = $('#landmark-add-name').val(),
                $radius = $('#landmark-add-radius').val()
            ;

console.log('switch('+shape+')');

            switch(shape){

                case          'circle' :
                case          'square' :    var eventCallbacks = {
                                                click: function(data) {
                                                console.log('eventCallbacks:click');
                                                },
                                                drag: function(data) {
                                                    console.log('eventCallbacks:drag');
                                                    var title = $('#landmark-add-name').val(),
                                                        type = $('#landmark-add-shape').val(),
                                                        radius = $('#landmark-add-radius').val()
                                                    ;
                                                    Map.updateTempLandmark(map, type, data.latitude, data.longitude, radius, title);
                                                },
                                                dragend: function(data) {
                                                    console.log('eventCallbacks:dragend');
                                                    var a = { latitude: data.latitude, longitude: data.longitude },
                                                        title = $('#landmark-add-name').val(),
                                                        type = $('#landmark-add-shape').val(),
                                                        radius = $('#landmark-add-radius').val()
                                                    ;
                                                    $('#landmark-add-latitude').val(data.latitude);
                                                    $('#landmark-add-longitude').val(data.longitude);
                                                    Core.AddMap.AddressReverse(1);
                                                    if (Map.api() == 'mapbox') {    // fix for a possible mapbox/leaflet api bug where a 'click' event is triggered after a 'dragend'
                                                        setTimeout(function() {
                                                            map._preventClick = false;
                                                        }, 500);                                                    
                                                    }  
                                                }
                                            };    
                                            if (Map.doesTempLandmarkExist(map)) {
                                                Map.removeTempLandmark(map);
                                            }
                                            Map.createTempLandmark(map, shape, latitude, longitude, radius, title, false, eventCallbacks);                                    
                                            // Map.addMarkerWithPolygon(
                                            //     map,
                                            //     {
                                            //         id: 999,
                                            //         name: address + ' (' + results.latitude + ' / ' + results.longitude + ')',
                                            //         latitude: results.latitude,
                                            //         longitude: results.longitude
                                            //     },
                                            //     false,
                                            //     polygonOptions
                                            // );

                                            break;

                case         'polygon' :    // for rectangles, squares, and polygons
                                            var eventCallbacks = {
                                                click: function() {
                                                    console.log('click:'+$(this).attr('id')+':'+$(this).attr('class'));
                                                    $addButton.prop('disabled', false);
                                                    $addSaveButtons.prop('disabled', false);                                   
                                                },
                                                drag: function(data) {
                                                    console.log('drag');
                                                    Map.updateTempLandmark(map, shape, data.latitude, data.longitude, radius, title);
                                                    $locate.val('Waiting...');                                      
                                                },
                                                dragend: function(data) {
                                                    console.log('dragend');
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

console.log('================================== points');
console.log('================================== points');
console.log('================================== points');
console.log(points);
                                            $.each(points, function(key, val) {
                                                console.log('points: '+key+':'+val.latitude+' / '+val.longitude);
                                                if(key<1){
console.log("................................................................................. Map.doesTempLandmarkExist(map)");
                                                    if (Map.doesTempLandmarkExist(map)) {
                                                        Map.removeTempLandmark(map);
                                                    }
                                                    Map.createTempPolygon(map, shape, val.latitude, val.longitude, radius, '#ff0000', eventCallbacks);
                                                } else if (key<10) {
                                                    Map.updateTempLandmark(map, shape, val.latitude, val.longitude, radius, $('#landmark-add-name').val());
                                                }
                                            });

                                            Map.addMapClickListener(map, function(event) {
                                            
                                                // events for the points
                                                var events = {
                                                    click: function() {
                                                        if (Map.getTempMarkerArray(map).length > 2) {
                                                            $addSaveButtons.prop('disabled', false);                                   
                                                        } else {
                                                            // $('#landmark-restore').prop('disabled', false);
                                                            // $addButton.prop('disabled', true);
                                                        }
                                                    }
                                                };
                                                if (! Map.doesTempLandmarkExist(map)) {
                                                    // Map.createTempLandmark(map, $type.val(), event.latitude, event.longitude, radius, title, true, {}, events);                                    
                                                    Map.updateTempLandmark(map, shape, event.latitude, event.longitude, radius, title, events, true);
                                                } else {
                                                    Map.updateTempLandmark(map, shape, event.latitude, event.longitude, radius, title, events, true);
                                                }
                                                
                                                if (Map.getTempMarkerArray(map).length > 2) {
                                                    $addSaveButtons.prop('disabled', false);
                                                } else {
                                                    // $('#landmark-restore').prop('disabled', false);
                                                    // $addButton.prop('disabled', true);
                                                }

                                            });
                                            break;

            }

            Map.resetMap(map);
            Map.resize(map);
            Map.updateMapBound(map,true);
            Map.centerMap(map,$('#landmark-add-latitude').val(),$('#landmark-add-longitude').val(),17);

            console.log(address)
            if(address=='USA'){
              Map.mapZoom(map,'4');
            } else {
              Map.updateMapBoundZoom(map);
            }

console.log("................................................................................. Core.AddMap.Address EOF");

        },

        LandmarkGet: function (eid,rid,tbl) {
            if((!(tbl))||(tbl=='undefined')){
                tbl='list';
            }
console.log('landmarkGet:eid="'+eid+'", rid="'+rid+'", tbl="'+tbl+'"');
            if(eid){
                var rec = eid.split('-').pop();
                var streetaddress = $('#landmark-'+tbl+'-table-crossbones-territory-streetaddress-'+rec).text();
                var city = $('#landmark-'+tbl+'-table-crossbones-territory-city-'+rec).text();
                var state = $('#landmark-'+tbl+'-table-crossbones-territory-state-'+rec).text();
                var zipcode = $('#landmark-'+tbl+'-table-crossbones-territory-zipcode-'+rec).text();
                var country = $('#landmark-'+tbl+'-table-crossbones-territory-country-'+rec).text();
                var address = streetaddress;
                if(streetaddress=='⊕'){
                    streetaddress='';
                }
                if(city=='⊕'){
                    city='';
                }
                if(state=='⊕'){
                    state='';
                }
                if(zipcode=='⊕'){
                    zipcode='';
                }
                if(country=='⊕'){
                    country='';
                }
                if(address){address += ', '; }
                address += city;
                if(address){address += ', '; }
                address += state;
                if(address){address += ', '; }
                address += zipcode;
                if(address){address += ', '; }
                address += country;
console.log('landmarkGet:address:'+address);
                Map.geocode(Landmark.Common.addmap, address, function(results, status){
                    if(results){
console.log('Core.AddMap.LandmarkGet:results...');
console.log(results);
                        Core.Ajax(rec,results.latitude+':'+results.longitude+':'+tbl,rid,'dbupdate','crossbones-territory-latlong');
                    }
                });                            
            }
        },

        LatLng: function (latLng) {
console.log(latLng);
            Map.reverseGeocode(Landmark.Common.addmap, latLng.latitude, latLng.longitude, function(results, status){
                if((results)&&(results.address_components)){
                    console.log('Core.AddMap.LatLng:results...');
                    console.log(results);
                    // $('#landmark-add-latitude').val(results.latitude);
                    // $('#landmark-add-longitude').val(results.longitude);
                    if((results.address_components.address)&&(results.address_components.address!='undefined')){
                        $('#landmark-add-street_address').val(results.address_components.address);
                    }
                    if((results.address_components.city)&&(results.address_components.city!='undefined')){
                        $('#landmark-add-city').val(results.address_components.city);
                    }
                    if((results.address_components.state)&&(results.address_components.state!='undefined')){
                        $('#landmark-add-state').val(results.address_components.state);
                    }
                    if((results.address_components.zip)&&(results.address_components.zip!='undefined')){
                        $('#landmark-add-zipcode').val(results.address_components.zip);
                    }
                    if((results.address_components.country)&&(results.address_components.country!='undefined')){
                        $('#landmark-add-country').val(results.address_components.country);
                    }
                    Map.clearMarkers(Landmark.Common.addmap);
                    addMapCoordinates = null;

                    var shape = $('#landmark-add-shape').val();
                    var radius = $('#landmark-add-radius').val();
console.log('shape:'+shape);
                    if (shape == 'rectangle' || shape == 'polygon' || shape == 'square') { // for rectangles, squares, and polygons
                        // get the coordinates of the temp polygon as an array of objects, each with two properties called 'latitude' and 'longitude'
                        // (i.e. [{latitude: 12.345, longitude: 45.56}, {latitude: 34.45, longitude: 56.788}, ...]) 
                        var coordinates = Map.getTempPolygonPoints(Landmark.Common.map);
                        
                        if ((shape == 'rectangle' || shape == 'square')) {
                            if (coordinates.length == 4) { // rectangle or square needs 5 points to connect
                                addMapCoordinates = coordinates;
                            } else {
                                console.log('Rectangle landmarks require 2 points');
                            }
                        } else if (shape == 'polygon') { // polygon needs at least 4 points to connect (i.e triangle)
                            if (coordinates.length >= 3) {
                                addMapCoordinates = coordinates;
                            } else {                                            
                                console.log('Polygon landmarks require 3 points');
                            }
                        }
                    }

                    var polygonOptions=Array();
                    polygonOptions.type = shape;
                    if (shape == 'circle') {
                        polygonOptions.radius = radius;
                    } else if (shape == 'square') {
                        polygonOptions.radius = radius;
                        polygonOptions.points = addMapCoordinates;
                    } else {
                        polygonOptions.points = addMapCoordinates;
                    }

                    Core.AddMap.Landmark(Landmark.Common.addmap,$('#landmark-add-latitude').val(),$('#landmark-add-longitude').val(),polygonOptions.type,polygonOptions.radius,polygonOptions.points,polygonOptions.title,results.formatted_address);

console.log('polygonOptions...');
console.log(polygonOptions);

//                     Map.addMarkerWithPolygon(
//                         Landmark.Common.addmap,
//                         {
//                             id: 999,
//                             name: results.address + ' (' + results.latitude + ' / ' + results.longitude + ')',
//                             latitude: results.latitude,
//                             longitude: results.longitude
//                         },
//                         false,
//                         polygonOptions
//                     );

//                     Map.resetMap(Landmark.Common.addmap);
//                     Map.resize(Landmark.Common.addmap);
//                     Map.updateMapBound(Landmark.Common.addmap);
//                     Map.updateMapBoundZoom(Landmark.Common.addmap);
                } else {
                    Core.log('Core.AddMap.LatLng:No Results');
                }
            });        

        },

        LatLngEdit: function (latLng) {
        console.log(latLng);
            Map.reverseGeocode(Landmark.Common.addmap, latLng.latitude, latLng.longitude, function(results, status){
                if(results){
                    console.log('Core.AddMap.LatLng:results...');
                    console.log(results);
                    $('#landmark-add-latitude').html(latLng.latitude);                            
                    $('#landmark-add-longitude').html(latLng.longitude);                            
                    Map.clearMarkers(Landmark.Common.addmap);
                    addMapCoordinates = null;

                    var shape = $('#landmark-add-shape').val();
                    var radius = $('#landmark-add-radius').val();
console.log('shape:'+shape);
                    if (shape == 'rectangle' || shape == 'polygon' || shape == 'square') { // for rectangles, squares, and polygons
                        // get the coordinates of the temp polygon as an array of objects, each with two properties called 'latitude' and 'longitude'
                        // (i.e. [{latitude: 12.345, longitude: 45.56}, {latitude: 34.45, longitude: 56.788}, ...]) 
                        var coordinates = Map.getTempPolygonPoints(Landmark.Common.addmap);
                        
                        if ((shape == 'rectangle' || shape == 'square')) {
                            if (coordinates.length == 4) { // rectangle or square needs 5 points to connect
                                addMapCoordinates = coordinates;
                            } else {
                                console.log('Rectangle landmarks require 2 points');
                            }
                        } else if (shape == 'polygon') { // polygon needs at least 4 points to connect (i.e triangle)
                            if (coordinates.length >= 3) {
                                addMapCoordinates = coordinates;
                            } else {                                            
                                console.log('Polygon landmarks require 3 points');
                            }
                        }
                    }

                    var polygonOptions=Array();
                    polygonOptions.type = shape;
                    if (shape == 'circle') {
                        polygonOptions.radius = radius;
                    } else if (shape == 'square') {
                        polygonOptions.radius = radius;
                        polygonOptions.points = addMapCoordinates;
                    } else {
                        polygonOptions.points = addMapCoordinates;
                    }

console.log('polygonOptions...');
console.log(polygonOptions);

                    if(results.address_components){
console.log('results.address_components...');
console.log(results.address_components);
                        if((results.address_components.address)&&(results.address_components.address!='undefined')){
                            $('#landmark-add-street_address').val(results.address_components.address);
                        }
                        if((results.address_components.city)&&(results.address_components.city!='undefined')){
                            $('#landmark-add-city').val(results.address_components.city);
                        }
                        if((results.address_components.state)&&(results.address_components.state!='undefined')){
                            $('#landmark-add-state').val(results.address_components.state);
                        }
                        if((results.address_components.zip)&&(results.address_components.zip!='undefined')){
                            $('#landmark-add-zipcode').val(results.address_components.zip);
                        }
                        if((results.address_components.country)&&(results.address_components.country!='undefined')){
                            $('#landmark-add-country').val(results.address_components.country);
                        }
                    }

                } else {
                    Core.log('Core.AddMap.LatLngEdit:No Results');
                }
            });        

        }

    },

    ClearForm: function (fid,u1,u2,u3) {
        bool_loadTransferDevices='';
        var notDefault='';                                        
console.log('Core.ClearForm:'+fid);
        switch(fid){

            case    'accept-transfer' : var devicesForTransfer='';
                                        transferManifest=[];
                                        $('#devices-importing').find('.device-for-import').each(function(){
                                            if($(this).is(':checked')){
                                                if(devicesForTransfer){
                                                    devicesForTransfer = devicesForTransfer + '<br>' ;
                                                }
                                                devicesForTransfer = devicesForTransfer + $(this).val();
                                                transferManifest.push($(this).attr('data-unit'));
                                            }
                                        });
                                        if(devicesForTransfer){
                                            $('#transfer-manifest-accept').html(devicesForTransfer);
                                            $('#modal-transfer-accept').prop('aria-hidden','false');
                                        } else {
                                            alert("Please Select at Least One Device for Transfer");
                                            setTimeout("$('#modal-transfer-accept-close').trigger('click')",1);
                                        }
                                        Core.FixModal.FixModal(fid);
                                        break;

            case          'alert-add' : $('#alert-add-contact').closest('div .row').show();
                                        $('#alert-add-contactgroup').closest('div .row').hide();
                                        $('#alert-add-contactmode').val('1');
                                        $('#alert-add-contactmethod').val('all');
                                        $('#alert-add-days').val('3');
                                        $('#alert-add-duration').closest('div .row').hide();
                                        $('#alert-add-duration').val('1');
                                        $('#alert-add-hours').val('0');
                                        $('#alert-add-landmark').closest('div .row').show();
                                        $('#alert-add-landmarkgroup').closest('div .row').hide();
                                        $('#alert-add-landmarkmode').val('3');
                                        $('#alert-add-landmarktrigger').closest('div .row').show();
                                        $('#alert-add-landmarktrigger').val('Entering');
                                        $('#alert-add-name').val('');
                                        $('#alert-add-overspeed').closest('div .row').hide();
                                        $('#alert-add-overspeed').val('25');
                                        $('#alert-add-range').val('0');
                                        $('#alert-add-range').attr('data-uid',u2);
                                        $('#alert-add-type').val('3');
                                        $('#alert-add-vehicle').closest('div .row').show();
                                        $('#alert-add-vehiclegroup').closest('div .row').hide();
                                        $('#alert-add-vehiclemode').val('1');
                                        $('#alert-add-type').trigger('change');
                                        Core.FixModal.FixModal(fid);
                                        break;

            case         'alert-edit' : formFillBool=1;
                                        $('#alert-edit-contact').closest('div .row').show();
                                        $('#alert-edit-contact').attr('data-uid',u2);
                                        $('#alert-edit-contactgroup').closest('div .row').hide();
                                        $('#alert-edit-contactgroup').attr('data-uid',u2);
                                        $('#alert-edit-contactmode').val('3');
                                        $('#alert-edit-contactmode').attr('data-uid',u2);
                                        $('#alert-edit-contactmethod').val('all');
                                        $('#alert-edit-contactmethod').attr('data-uid',u2);
                                        $('#alert-edit-days').val('3');
                                        $('#alert-edit-days').attr('data-uid',u2);
                                        $('#alert-edit-duration').closest('div .row').hide();
                                        $('#alert-edit-duration').val(-1);
                                        $('#alert-edit-duration').attr('data-uid',u2);
                                        $('#alert-edit-endhour').val('0');
                                        $('#alert-edit-endhour').attr('data-uid',u2);
                                        $('#alert-edit-hours').val('0');
                                        $('#alert-edit-hours').attr('data-uid',u2);
                                        $('#alert-edit-landmark').closest('div .row').show();
                                        $('#alert-edit-landmark').attr('data-uid',u2);
                                        $('#alert-edit-landmarkgroup').closest('div .row').hide();
                                        $('#alert-edit-landmarkgroup').attr('data-uid',u2);
                                        $('#alert-edit-landmarkmode').val('3');
                                        $('#alert-edit-landmarkmode').attr('data-uid',u2);
                                        $('#alert-edit-landmarktrigger').closest('div .row').show();
                                        $('#alert-edit-landmarktrigger').val(-1);
                                        $('#alert-edit-landmarktrigger').attr('data-uid',u2);
                                        $('#alert-edit-name').val(u1);
                                        $('#alert-edit-name').attr('data-uid',u2);
                                        $('#alert-edit-overspeed').closest('div .row').hide();
                                        $('#alert-edit-overspeed').val('25');
                                        $('#alert-edit-overspeed').attr('data-uid',u2);
                                        $('#alert-edit-range').val('0');
                                        $('#alert-edit-range').attr('data-uid',u2);
                                        $('#alert-edit-starthour').val('0');
                                        $('#alert-edit-starthour').attr('data-uid',u2);
                                        $('#alert-edit-type').val('3');
                                        $('#alert-edit-type').attr('data-uid',u2);
                                        $('#alert-edit-vehicle').closest('div .row').show();
                                        $('#alert-edit-vehicle').attr('data-uid',u2);
                                        $('#alert-edit-vehiclegroup').closest('div .row').hide();
                                        $('#alert-edit-vehiclegroup').attr('data-uid',u2);
                                        $('#alert-edit-vehiclemode').val('1');
                                        $('#alert-edit-vehiclemode').attr('data-uid',u2);
                                        $('#alert-edit-type').trigger('change');
                                        if(u2>0){
                                            Core.Ajax('load','',u2,fid);
                                        }
                                        Core.FixModal.FixModal(fid);
                                        break;

            case     'batch-commands' : //$('#batch-command').val(0);
                                        $('#batch-devices').text('');
                                        $('#batch-devices-available').find('li.active').each(function() {
                                            $(this).removeClass('active');
                                        });
                                        Core.FixModal.FixModal(fid);
                                        bool_loadTransferDevices=1;
                                        break;

            case       'batch-upload' : //$('#batch-command').val(0);
                                        Core.FixModal.FixModal(fid);
                                        break;

            case        'contact-add' : $('#contact-add-first-name').val('');
                                        $('#contact-add-last-name').val('');
                                        $('#contact-add-email').val('');
                                        $('#contact-add-cellnumber').val('');
                                        break;

            case   'contactgroup-add' : $('#contactgroup-add-name').val('');
                                        break;

            case       'edit-contact' : $('#edit-contact-title').text(u1);
                                        $('#edit-contact-first-name').val('');
                                        $('#edit-contact-last-name').val('');
                                        $('#edit-contact-email').val('');
                                        $('#edit-contact-cellnumber').val('');
                                        $('#edit-contact-carrier').val('');
                                        $('#edit-contact-carrier').attr('data-uid','');
                                        $('#edit-contact-cellnumber').attr('data-uid','');
                                        $('#edit-contact-email').attr('data-uid','');
                                        $('#edit-contact-first-name').attr('data-uid','');
                                        $('#edit-contact-last-name').attr('data-uid','');
                                        Core.FixModal.FixModal(fid);
                                        if(u2>0){
                                            Core.Ajax('load','',u2,fid);
                                        }
                                        break;

            case 'edit-contact-group' : $('#edit-contact-group-title').text(u1);
                                        $('#edit-contact-group-name').val('');
                                        $('#edit-contact-group-contacts-available').empty();
                                        $('#edit-contact-group-contacts-assigned').empty();
                                        Core.FixModal.FixModal(fid);
                                        if(u2>0){
                                            Core.Ajax('load','',u2,fid);
                                        }
                                        break;

            case        'device-edit' : $('#btn-info-vehicle').trigger('click');
                                        $('#modal-edit-device').find('.modal-title').text(u1);
                                        $('#vehicle-name').text(u1);
                                        $('#vehicle-serial').text('');
                                        $('#vehicle-group').val('');
                                        $('#vehicle-status').val('');
                                        $('#vehicle-vin').text('');
                                        $('#vehicle-make').text('');
                                        $('#vehicle-model').text('');
                                        $('#vehicle-year').text('');
                                        $('#vehicle-color').text('');
                                        $('#vehicle-stock').text('');
                                        $('#vehicle-license-plate').text('');
                                        $('#vehicle-loan-id').text('');
                                        $('#vehicle-install-date').text('');
                                        $('#vehicle-installer').text('');
                                        $('#vehicle-install-mileage').text('');
                                        $('#vehicle-driven-miles').text('');
                                        $('#vehicle-total-mileage').text('');
                                        $('#customer-first-name').text('');
                                        $('#customer-last-name').text('');
                                        $('#customer-address').text('');
                                        $('#customer-city').text('');
                                        $('#customer-state').text('');
                                        $('#customer-zipcode').text('');
                                        $('#customer-mobile-phone').text('');
                                        $('#customer-home-phone').text('');
                                        $('#customer-email').text('');
                                        $('#device-serial').text('');
                                        $('#device-status').text('');
                                        $('#device-plan').text('');
                                        $('#device-purchase-date').text('');
                                        $('#device-activation-date').text('');
                                        $('#device-renewal-date').text('');
                                        $('#device-last-renewed').text('');
                                        $('#device-deactivation-date').text('');
                                        if(u2>0){
                                            currentUnitId=u2;
                                            Core.Ajax('load','',u2,fid);
                                        }
                                        break;

            case    'device-transfer' : $('#user-edit-devices-assigned').empty();
                                        $('#user-edit-devices-available').empty();
                                        $("#transfer-group-from").find('option').each(function () {
                                            if($(this).text()=='Default'){
                                                $(this).attr('selected', 'selected');
                                            // } else {
                                            //     $(this).attr('selected', '');
                                            }
                                        }); 
                                        notDefault='';
                                        $("#transfer-group-to").find('option').each(function () {
                                            $(this).attr('selected', '');
                                        });
                                        $('#search-transfer-from').trigger('click');
                                        // $('#search-transfer-to').trigger('click');
                                        // Core.Ajax('load',$("#transfer-group-from").val(),$("#transfer-group-to").val(),fid);
                                        Core.FixModal.FixModal(fid);
                                        setTimeout("Core.fixFooter('modal-device-transfer');Core.fixListGroup('modal-device-transfer')",400);
                                        break;

        case    'edit-landmark-group' : $('#edit-landmark-group-title-edit').val(u1);
                                        if(u1=='Default'){
                                            $('#edit-landmark-group-title-edit').prop('disabled', true);
                                        } else {
                                            $('#edit-landmark-group-title-edit').prop('disabled', false);
                                        }
                                        $('#edit-landmark-group-title-edit').attr('data-uid',u2);
                                        $('#edit-landmark-groups-available').empty();
                                        $('#edit-landmark-groups-assigned').empty();
                                        Core.Ajax('load',u2,u2,fid);
                                        Core.FixModal.FixModal(fid);
                                        break;

            case 'edit-vehicle-group' : $('#edit-vehicle-group-title').text(u1);
                                        $('#edit-vehicle-group-title-edit').val(u1);
                                        if(u1=='Default'){
                                            $('#edit-vehicle-group-title-edit').prop('disabled', true);
                                        } else {
                                            $('#edit-vehicle-group-title-edit').prop('disabled', false);
                                        }
                                        $('#edit-vehicle-group-title-edit').data('id',u2);
                                        $('#edit-vehicle-group-devices-available').empty();
                                        $('#edit-vehicle-group-devices-assigned').empty();
                                        $('#edit-vehicle-group-user-types').find('input').prop('checked',false);
                                        $('#edit-vehicle-group-user-types').find('li').removeClass('active');
                                        $('#edit-vehicle-group-users').find('input').prop('checked',false);
                                        $('#edit-vehicle-group-users').find('li').removeClass('active');
                                        $("#transfer-vehicle-group-devices-group-from option").each(function () {
                                            if($(this).val()==u2){
                                                $(this).attr('selected', 'selected');
                                            }
                                        }); 
                                        notDefault='';
                                        $("#transfer-vehicle-group-devices-group-to option").each(function () {
                                            if(((!(notDefault))||($(this).html()=='Default'))&&($(this).val()!=u2)){
                                                notDefault=1;
                                                $(this).attr('selected', 'selected');
                                            }
                                        });
                                        Core.Ajax('load',$("#transfer-vehicle-group-devices-group-from").val(),$("#transfer-vehicle-group-devices-group-to").val(),fid);
                                        Core.FixModal.FixModal(fid);
                                        break;

            case       'landmark-add' : $('#landmark-error').html('');
                                        $('#landmark-add-category').val('0');
                                        // $('#landmark-add-city').val('');
                                        // $('#landmark-add-country').val('USA');
                                        $("#landmark-add-group option:contains('Default')").each(function () {
                                            if($(this).html()=='Default'){
                                                $(this).attr('selected', 'selected');
                                            }
                                        }); 
                                        $('#landmark-add-name').val('');
                                        $('#landmark-add-radius').val('330');
                                        $('#landmark-add-shape').val('circle');
                                        if(!($('#landmark-add-street_address').val())){
                                            $('#landmark-add-state').val('');
                                        }
                                        // $('#landmark-add-street_address').val('');
                                        // $('#landmark-add-zip').val('');
                                        $('#landmark-add-type').val(1);
                                        $('#div-landmark-add-type-other').hide();
                                        Core.AddMap.Address(1);
                                        Core.FixModal.FixModal(fid);
                                        break;

            case    'landmark-import' : $('#landmark-add-category').val('0');
                                        Core.FixModal.FixModal('modal-import-csv');
                                        break;

            case  'landmarkgroup-add' : $('#landmarkgroup-add-name').val('');
                                        break;

            case  'mark-for-transfer' : if($('#transferee-routing-number').val()){
                                            $('#transfer-transferee-detail').html($('#transferee-routing-number').val());
                                            var devicesForTransfer='';
                                            transferManifest=[];
                                            $('#devices-exporting').find('.device-for-export').each(function(){
                                                if($(this).is(':checked')){
                                                    if(devicesForTransfer){
                                                        devicesForTransfer = devicesForTransfer + '<br>' ;
                                                    }
                                                    devicesForTransfer = devicesForTransfer + $(this).val();
                                                    transferManifest.push($(this).attr('data-unit'));
                                                }
                                            });
                                            if(devicesForTransfer){
                                                $('#transfer-devices-detail').html(devicesForTransfer);
                                                $('#modal-transfer-authorize-release').prop('aria-hidden','false');
                                                Core.Ajax('load',$('#transferee-routing-number').val(),'',fid);
                                                // alert(transferManifest.join());
                                            } else {
                                                alert("Please Select at Least One Device for Transfer");
                                                setTimeout("$('#modal-transfer-authorize-release-close').trigger('click')",1);
                                            }
                                        } else {
                                            alert("Tranferee's Routing Number is Missing");
                                            setTimeout("$('#modal-transfer-authorize-release-close').trigger('click')",1);
                                        }
                                        break;

            case    'reject-transfer' : var devicesForTransfer='';
                                        transferManifest=[];
                                        $('#devices-importing').find('.device-for-import').each(function(){
                                            if($(this).is(':checked')){
                                                if(devicesForTransfer){
                                                    devicesForTransfer = devicesForTransfer + '<br>' ;
                                                }
                                                devicesForTransfer = devicesForTransfer + $(this).val();
                                                transferManifest.push($(this).attr('data-unit'));
                                            }
                                        });
                                        if(devicesForTransfer){
                                            $('#transfer-manifest-reject').html(devicesForTransfer);
                                            $('#modal-transfer-reject').prop('aria-hidden','false');
                                        } else {
                                            alert("Please Select at Least One Device for Transfer");
                                            setTimeout("$('#modal-transfer-reject-close').trigger('click')",1);
                                        }
                                        break;

            case   'scheduled-report' : formFillBool=1;
                                        // $('#div-scheduled-report-edit-alerttype').hide();
                                        // $('#div-scheduled-report-edit-contacts').hide();
                                        // $('#div-scheduled-report-edit-contactgroups').hide();
                                        // $('#div-scheduled-report-edit-day').hide();
                                        // $('#div-scheduled-report-edit-duration').hide();
                                        // $('#div-scheduled-report-edit-landmarks').hide();
                                        // $('#div-scheduled-report-edit-landmarkgroups').hide();
                                        // $('#div-scheduled-report-edit-landmarkmode').hide();
                                        // $('#div-scheduled-report-edit-mile').hide();
                                        // $('#div-scheduled-report-edit-minute').hide();
                                        // $('#div-scheduled-report-edit-mph').hide();
                                        // $('#div-scheduled-report-edit-not-reported').hide();
                                        // $('#div-scheduled-report-edit-range').hide();
                                        // $('#div-scheduled-report-edit-scheduleday').hide();
                                        // $('#div-scheduled-report-edit-vehicles').hide();
                                        // $('#div-scheduled-report-edit-vehiclegroups').hide();
                                        // $('#div-scheduled-report-edit-verification').hide();
                                        $('#scheduled-report-edit-alerttype').val('9');
                                        $('#scheduled-report-edit-contact').val('');
                                        $('#scheduled-report-edit-contactgroup').val('');
                                        $('#scheduled-report-edit-contactmode').val('all');
                                        $('#scheduled-report-edit-day').val('3');
                                        $('#scheduled-report-edit-duration').val('30');
                                        $('#scheduled-report-edit-format').val('');
                                        $('#scheduled-report-edit-landmark').val('');
                                        $('#scheduled-report-edit-landmarkgroup').val('');
                                        $('#scheduled-report-edit-landmarkmode').val('all');
                                        $('#scheduled-report-edit-mile').val('0');
                                        $('#scheduled-report-edit-minute').val('30');
                                        $('#scheduled-report-edit-monthly').val('1');
                                        $('#scheduled-report-edit-mph').val('75');
                                        $('#scheduled-report-edit-name').val(u1);
                                        $('#scheduled-report-edit-name').attr('data-uid',u2);
                                        $('#scheduled-report-edit-not-reported').val('7');
                                        $('#scheduled-report-edit-range').val('');
                                        $('#scheduled-report-edit-range-start').val('');
                                        $('#scheduled-report-edit-range-end').val('');
                                        $('#scheduled-report-edit-recurrence').val('Daily');
                                        $('#scheduled-report-edit-reporttype').val('');
                                        $('#scheduled-report-edit-scheduleday').val('Everyday');
                                        $('#scheduled-report-edit-time').val('');
                                        $('#scheduled-report-edit-title').html(u3);
                                        $('#scheduled-report-edit-vehicle').val('');
                                        $('#scheduled-report-edit-vehiclegroup').val('');
                                        $('#scheduled-report-edit-vehiclemode').val('all');
                                        $('#scheduled-report-edit-verification').val('All');
                                        $('#scheduled-report-edit-reporttype').trigger('change');
                                        formFillBool='';
                                        if(u2>0){
                                            currentScheduledReportId=u2;
                                            Core.Ajax('load','',u2,fid);
                                        }
                                        Core.FixModal.FixModal('modal-edit-'+fid);
                                        break;

            case           'user-add' : $('#user-add-first-name').val('');
                                        $('#user-add-last-name').val('');
                                        $('#user-add-username').val('');
                                        $('#user-add-password').val('');
                                        $('#user-add-confirm').val('');
                                        $('#user-add-email').val('');
                                        $('#user-add-mobile-number').val('');
                                        // $('#user-add-user-type').select('');
                                        // $('#user-add-carrier').select('');
                                        Core.FixModal.FixModal(fid);
                                        break;

            case          'user-edit' : $('#user-edit-title').text(u1);
                                        $('#user-edit-firstname').val('');
                                        $('#user-edit-lastname').val('');
                                        $('#user-edit-usertype').val('');
                                        $('#user-edit-email').val('');
                                        $('#user-edit-cellcarrier').val('');
                                        $('#user-edit-cellnumber').val('');
                                        $('#user-edit-landmarkgroups-assigned').empty();
                                        $('#user-edit-landmarkgroups-available').empty();
                                        $('#user-edit-vehiclegroups-assigned').empty();
                                        $('#user-edit-vehiclegroups-available').empty();
                                        if(u2>0){
                                            Core.Ajax('load','',u2,fid);
                                        }
                                        Core.FixModal.FixModal(fid);
                                        break;

            case      'user-type-add' : $('#user-type-add-name').val('');
                                        break;

            case     'user-type-edit' : $('#user-type-edit-name').val(u1);
                                        $('#user-type-edit-name').attr('data-uid',u2);
                                        $('#input-Admin-permission-1').prop('checked',false);
                                        $('#input-Vehicles-permission-2').prop('checked',false);
                                        $('#input-Landmarks-permission-3').prop('checked',false);
                                        $('#input-Alerts-permission-4').prop('checked',false);
                                        $('#input-Vehicles-permission-5').prop('checked',false);
                                        $('#input-Landmarks-permission-6').prop('checked',false);
                                        $('#input-Admin-permission-7').prop('checked',false);
                                        $('#input-Reports-permission-8').prop('checked',false);
                                        $('#input-Vehicles-permission-9').prop('checked',false);
                                        $('#input-Landmarks-permission-10').prop('checked',false);
                                        $('#input-Reports-permission-11').prop('checked',false);
                                        $('#input-Alerts-permission-12').prop('checked',false);
                                        $('#input-Vehicles-permission-13').prop('checked',false);
                                        $('#input-Vehicles-permission-14').prop('checked',false);
                                        $('#input-Vehicles-permission-15').prop('checked',false);
                                        $('#input-Vehicles-permission-16').prop('checked',false);
                                        $('#input-Vehicles-permission-17').prop('checked',false);
                                        $('#input-Vehicles-permission-18').prop('checked',false);
                                        $('#input-Vehicles-permission-19').prop('checked',false);
                                        $('#input-Vehicles-permission-20').prop('checked',false);
                                        $('#input-Vehicles-permission-21').prop('checked',false);
                                        $('#input-Admin-permission-22').prop('checked',false);
                                        $('#input-Admin-permission-23').prop('checked',false);
                                        $('#input-Admin-permission-24').prop('checked',false);
                                        $('#input-Admin-permission-1').closest('li').removeClass('active');
                                        $('#input-Vehicles-permission-2').closest('li').removeClass('active');
                                        $('#input-Landmarks-permission-3').closest('li').removeClass('active');
                                        $('#input-Alerts-permission-4').closest('li').removeClass('active');
                                        $('#input-Vehicles-permission-5').closest('li').removeClass('active');
                                        $('#input-Landmarks-permission-6').closest('li').removeClass('active');
                                        $('#input-Admin-permission-7').closest('li').removeClass('active');
                                        $('#input-Reports-permission-8').closest('li').removeClass('active');
                                        $('#input-Vehicles-permission-9').closest('li').removeClass('active');
                                        $('#input-Landmarks-permission-10').closest('li').removeClass('active');
                                        $('#input-Reports-permission-11').closest('li').removeClass('active');
                                        $('#input-Alerts-permission-12').closest('li').removeClass('active');
                                        $('#input-Vehicles-permission-13').closest('li').removeClass('active');
                                        $('#input-Vehicles-permission-14').closest('li').removeClass('active');
                                        $('#input-Vehicles-permission-15').closest('li').removeClass('active');
                                        $('#input-Vehicles-permission-16').closest('li').removeClass('active');
                                        $('#input-Vehicles-permission-17').closest('li').removeClass('active');
                                        $('#input-Vehicles-permission-18').closest('li').removeClass('active');
                                        $('#input-Vehicles-permission-19').closest('li').removeClass('active');
                                        $('#input-Vehicles-permission-20').closest('li').removeClass('active');
                                        $('#input-Vehicles-permission-21').closest('li').removeClass('active');
                                        $('#input-Admin-permission-22').closest('li').removeClass('active');
                                        $('#input-Admin-permission-23').closest('li').removeClass('active');
                                        $('#input-Admin-permission-24').closest('li').removeClass('active');
                                        if(u3>0){
                                            updateDisabled=1;
                                            $('#user-type-edit-name').attr('disabled',true);
                                            $('#input-Admin-permission-1').attr('disabled',true);
                                            $('#input-Vehicles-permission-2').attr('disabled',true);
                                            $('#input-Landmarks-permission-3').attr('disabled',true);
                                            $('#input-Alerts-permission-4').attr('disabled',true);
                                            $('#input-Vehicles-permission-5').attr('disabled',true);
                                            $('#input-Landmarks-permission-6').attr('disabled',true);
                                            $('#input-Admin-permission-7').attr('disabled',true);
                                            $('#input-Reports-permission-8').attr('disabled',true);
                                            $('#input-Vehicles-permission-9').attr('disabled',true);
                                            $('#input-Landmarks-permission-10').attr('disabled',true);
                                            $('#input-Reports-permission-11').attr('disabled',true);
                                            $('#input-Alerts-permission-12').attr('disabled',true);
                                            $('#input-Vehicles-permission-13').attr('disabled',true);
                                            $('#input-Vehicles-permission-14').attr('disabled',true);
                                            $('#input-Vehicles-permission-15').attr('disabled',true);
                                            $('#input-Vehicles-permission-16').attr('disabled',true);
                                            $('#input-Vehicles-permission-17').attr('disabled',true);
                                            $('#input-Vehicles-permission-18').attr('disabled',true);
                                            $('#input-Vehicles-permission-19').attr('disabled',true);
                                            $('#input-Vehicles-permission-20').attr('disabled',true);
                                            $('#input-Vehicles-permission-21').attr('disabled',true);
                                            $('#input-Admin-permission-22').attr('disabled',true);
                                            $('#input-Admin-permission-23').attr('disabled',true);
                                            $('#input-Admin-permission-24').attr('disabled',true);
                                        } else {
                                            updateDisabled=0;
                                            $('#user-type-edit-name').removeAttr('disabled');
                                            $('#input-Admin-permission-1').removeAttr('disabled');
                                            $('#input-Vehicles-permission-2').removeAttr('disabled');
                                            $('#input-Landmarks-permission-3').removeAttr('disabled');
                                            $('#input-Alerts-permission-4').removeAttr('disabled');
                                            $('#input-Vehicles-permission-5').removeAttr('disabled');
                                            $('#input-Landmarks-permission-6').removeAttr('disabled');
                                            $('#input-Admin-permission-7').removeAttr('disabled');
                                            $('#input-Reports-permission-8').removeAttr('disabled');
                                            $('#input-Vehicles-permission-9').removeAttr('disabled');
                                            $('#input-Landmarks-permission-10').removeAttr('disabled');
                                            $('#input-Reports-permission-11').removeAttr('disabled');
                                            $('#input-Alerts-permission-12').removeAttr('disabled');
                                            $('#input-Vehicles-permission-13').removeAttr('disabled');
                                            $('#input-Vehicles-permission-14').removeAttr('disabled');
                                            $('#input-Vehicles-permission-15').removeAttr('disabled');
                                            $('#input-Vehicles-permission-16').removeAttr('disabled');
                                            $('#input-Vehicles-permission-17').removeAttr('disabled');
                                            $('#input-Vehicles-permission-18').removeAttr('disabled');
                                            $('#input-Vehicles-permission-19').removeAttr('disabled');
                                            $('#input-Vehicles-permission-20').removeAttr('disabled');
                                            $('#input-Vehicles-permission-21').removeAttr('disabled');
                                            $('#input-Admin-permission-22').removeAttr('disabled');
                                            $('#input-Admin-permission-23').removeAttr('disabled');
                                            $('#input-Admin-permission-24').removeAttr('disabled');
                                        }
                                        $('#Vehicles').trigger('click');
                                        if(u2>0){
                                            Core.Ajax('load','',u2,fid);
                                            var h = $(window).height() * .80;
                                            h = Math.floor(h) - 170;
                                            $('#modal-edit-usertype').find('.modal-body').height(h+'px');
                                            $('#modal-edit-usertype').find('.modal-footer').height('50px');
                                            h = h - 60;
                                            $('#modal-edit-usertype').find('.permission-list-wrapper').css({ height: h+'px' });
                                            h = h - 30;
                                            $('#modal-edit-usertype').find('.permission-list-div').css({ height: h+'px' });
                                            h = h - 4;
                                            $('#modal-edit-usertype').find('.permission-list-div').find('ul').css({ height: h+'px' });
                                            $('#Vehicles-detail-list').css({ height: h+'px' });
                                        }
                                        break;

        }

    },

    Toggle: function (eid) {
        if($('#'+eid).is(':visible')){
            $('#'+eid).hide();
        } else {
            $('#'+eid).show();
        }
    },

    CenterModal: function (eid) {
        var wh = $(window).height();
        var ww = $(window).width();
        var $mid = $('#'+eid).find('.modal-dialog');
        var mh = wh-0;
        var mw = ww-0;
        var $cid = $mid.find('.modal-content');
        var ch = $cid.height();
        var cw = $cid.width();
        if((ch==null)||(ch=='')||(ch=='undefined')){
            ch=wh-Math.floor(wh*80/100);
        }
        if((cw==null)||(cw=='')||(cw=='undefined')){
            cw=ww-Math.floor(ww*80/100);
        }
        ch=ch-2;
        cw=cw-2;
        var xx = wh-$cid.height();
        var xt = Math.floor(xx/2);
console.log('xx:'+xx+':xt:'+xt+':wh:'+wh+':cid:'+$cid.height());
        xx = ww-cw;
        var xl = Math.floor(xx/2);
        if(xt>50){xt=50;}
        if(xt<1){xt=1;}
        if(xl<1){xl=1;}
        $cid.css({ position: 'fixed' , top: xt+'px', left: xl+'px', margin: 0, width: cw });
        $mid.css({ height: mh, width: mw, margin: 0 });
        $('#'+eid).css({ height: wh , width: ww, overflow: 'hidden' });
        $('<div class="modal-dialog-backdrop" style="height: '+mh+'px; width: '+mw+'px;"></div>').insertBefore($('#'+eid));
    },

    Config: {
        ajaxTimeout:  15000, // milliseconds
        ajaxType:     'POST', // POST|GET
        ajaxDataType: 'json'
    },

    DivControl: function (did,val) {

console.log('DivControl:'+did+':'+val+':'+formFillBool+':');

        switch(did){

            case                  'alert-add-contactmode' : switch(val){
                                                                 case  1  :
                                                                 case '1' : $('#div-alert-add-contact').show();
                                                                            $('#div-alert-add-contactgroup').hide();
                                                                            break;
                                                                 case  2  :
                                                                 case '2' : $('#div-alert-add-contact').hide();
                                                                            $('#div-alert-add-contactgroup').show();
                                                                            break;
                                                                 case  3  :
                                                                 case '3' : $('#div-alert-add-contact').hide();
                                                                            $('#div-alert-add-contactgroup').hide();
                                                                            break;
                                                            }
                                                            break;

            case                        'alert-add-hours' : if(!(formFillBool)){
                                                                $('#alert-add-starthour').val(6);
                                                                $('#alert-add-endhour').val(18);
                                                            }
                                                            switch(val){
                                                                 case  1  :
                                                                 case '1' : $('#div-alert-add-range').show();
                                                                            break;
                                                                  default : $('#div-alert-add-range').hide();
                                                                            break;
                                                            }
                                                            break;

            case                 'alert-add-landmarkmode' : switch(val){
                                                                 case  1  :
                                                                 case '1' : $('#div-alert-add-landmark').show();
                                                                            $('#div-alert-add-landmarkgroup').hide();
                                                                            break;
                                                                 case  2  :
                                                                 case '2' : $('#div-alert-add-landmark').hide();
                                                                            $('#div-alert-add-landmarkgroup').show();
                                                                            break;
                                                                 case  3  :
                                                                 case '3' : $('#div-alert-add-landmark').hide();
                                                                            $('#div-alert-add-landmarkgroup').hide();
                                                                            break;
                                                            }
                                                            break;

            case                         'alert-add-type' : switch(val){
                                                                 case  1  :
                                                                 case '1' : $('#div-alert-add-duration').hide();
                                                                            $('#div-alert-add-landmark').hide();
                                                                            $('#div-alert-add-landmarkmode').hide();
                                                                            $('#div-alert-add-landmarktrigger').hide();
                                                                            $('#div-alert-add-overspeed').hide();
                                                                            $('#div-alert-add-range').hide();
                                                                            $('#div-alert-add-vehicle').hide();
                                                                            $('#div-alert-add-vehiclegroup').hide();
                                                                            $('#div-alert-add-vehiclemode').hide();
                                                                            $('#alert-add-contactmode').trigger('change');
                                                                            $('#alert-add-vehiclemode').trigger('change');
                                                                            break;

                                                                 case  2  :
                                                                 case '2' : $('#div-alert-add-duration').show();
                                                                            $('#div-alert-add-landmark').hide();
                                                                            $('#div-alert-add-landmarkmode').hide();
                                                                            $('#div-alert-add-landmarktrigger').hide();
                                                                            $('#div-alert-add-overspeed').hide();
                                                                            $('#div-alert-add-range').hide();
                                                                            $('#div-alert-add-vehicle').hide();
                                                                            $('#div-alert-add-vehiclegroup').hide();
                                                                            $('#div-alert-add-vehiclemode').show();
                                                                            $('#alert-add-contactmode').trigger('change');
                                                                            $('#alert-add-vehiclemode').trigger('change');
                                                                            break;

                                                                 case  3  :
                                                                 case '3' : $('#div-alert-add-duration').hide();
                                                                            $('#div-alert-add-landmark').hide();
                                                                            $('#div-alert-add-landmarkmode').show();
                                                                            $('#div-alert-add-landmarktrigger').show();
                                                                            $('#div-alert-add-overspeed').hide();
                                                                            $('#div-alert-add-range').hide();
                                                                            $('#div-alert-add-vehicle').hide();
                                                                            $('#div-alert-add-vehiclegroup').hide();
                                                                            $('#div-alert-add-vehiclemode').show();
                                                                            $('#alert-add-contactmode').trigger('change');
                                                                            $('#alert-add-landmarkmode').trigger('change');
                                                                            $('#alert-add-vehiclemode').trigger('change');
                                                                            break;
                                                                 case  4  :
                                                                 case '4' : $('#div-alert-add-duration').hide();
                                                                            $('#div-alert-add-landmark').hide();
                                                                            $('#div-alert-add-landmarkmode').hide();
                                                                            $('#div-alert-add-landmarktrigger').hide();
                                                                            $('#div-alert-add-overspeed').hide();
                                                                            $('#div-alert-add-range').hide();
                                                                            $('#div-alert-add-vehicle').hide();
                                                                            $('#div-alert-add-vehiclegroup').hide();
                                                                            $('#div-alert-add-vehiclemode').show();
                                                                            $('#alert-add-contactmode').trigger('change');
                                                                            $('#alert-add-vehiclemode').trigger('change');
                                                                            break;
                                                                 case  5  :
                                                                 case '5' : $('#div-alert-add-duration').hide();
                                                                            $('#div-alert-add-landmark').hide();
                                                                            $('#div-alert-add-landmarkmode').hide();
                                                                            $('#div-alert-add-landmarktrigger').hide();
                                                                            $('#div-alert-add-overspeed').hide();
                                                                            $('#div-alert-add-range').hide();
                                                                            $('#div-alert-add-vehicle').hide();
                                                                            $('#div-alert-add-vehiclegroup').hide();
                                                                            $('#div-alert-add-vehiclemode').show();
                                                                            $('#alert-add-contactmode').trigger('change');
                                                                            $('#alert-add-vehiclemode').trigger('change');
                                                                            break;
                                                                 case  6  :
                                                                 case '6' : $('#div-alert-add-duration').show();
                                                                            $('#div-alert-add-landmark').hide();
                                                                            $('#div-alert-add-landmarkmode').hide();
                                                                            $('#div-alert-add-landmarktrigger').hide();
                                                                            $('#div-alert-add-overspeed').hide();
                                                                            $('#div-alert-add-range').hide();
                                                                            $('#div-alert-add-vehicle').hide();
                                                                            $('#div-alert-add-vehiclegroup').hide();
                                                                            $('#div-alert-add-vehiclemode').show();
                                                                            $('#alert-add-contactmode').trigger('change');
                                                                            $('#alert-add-vehiclemode').trigger('change');
                                                                            break;
                                                                 case  7  :
                                                                 case '7' : $('#div-alert-add-duration').hide();
                                                                            $('#div-alert-add-landmark').hide();
                                                                            $('#div-alert-add-landmarkmode').hide();
                                                                            $('#div-alert-add-landmarktrigger').hide();
                                                                            $('#div-alert-add-overspeed').show();
                                                                            $('#div-alert-add-range').hide();
                                                                            $('#div-alert-add-vehicle').hide();
                                                                            $('#div-alert-add-vehiclegroup').hide();
                                                                            $('#div-alert-add-vehiclemode').show();
                                                                            $('#alert-add-contactmode').trigger('change');
                                                                            $('#alert-add-vehiclemode').trigger('change');
                                                                            break;
                                                                 case  8  :
                                                                 case '8' : $('#div-alert-add-duration').hide();
                                                                            $('#div-alert-add-landmark').hide();
                                                                            $('#div-alert-add-landmarkmode').hide();
                                                                            $('#div-alert-add-landmarktrigger').hide();
                                                                            $('#div-alert-add-overspeed').hide();
                                                                            $('#div-alert-add-range').hide();
                                                                            $('#div-alert-add-vehicle').hide();
                                                                            $('#div-alert-add-vehiclegroup').hide();
                                                                            $('#div-alert-add-vehiclemode').show();
                                                                            $('#alert-add-contactmode').trigger('change');
                                                                            $('#alert-add-vehiclemode').trigger('change');
                                                                            break;
                                                                 case  9  :
                                                                 case '9' : $('#div-alert-add-duration').hide();
                                                                            $('#div-alert-add-landmark').hide();
                                                                            $('#div-alert-add-landmarkmode').hide();
                                                                            $('#div-alert-add-landmarktrigger').hide();
                                                                            $('#div-alert-add-overspeed').hide();
                                                                            $('#div-alert-add-range').hide();
                                                                            $('#div-alert-add-vehicle').hide();
                                                                            $('#div-alert-add-vehiclegroup').hide();
                                                                            $('#div-alert-add-vehiclemode').show();
                                                                            $('#alert-add-contactmode').trigger('change');
                                                                            $('#alert-add-vehiclemode').trigger('change');
                                                                            break;
                                                            }
                                                            break;

            case                  'alert-add-vehiclemode' : switch(val){
                                                                 case  1  :
                                                                 case '1' : $('#div-alert-add-vehicle').show();
                                                                            $('#div-alert-add-vehiclegroup').hide();
                                                                            break;
                                                                 case  2  :
                                                                 case '2' : $('#div-alert-add-vehicle').hide();
                                                                            $('#div-alert-add-vehiclegroup').show();
                                                                            break;
                                                                 case  3  :
                                                                 case '3' : $('#div-alert-add-vehicle').hide();
                                                                            $('#div-alert-add-vehiclegroup').hide();
                                                                            break;
                                                            }
                                                            break;

            case                 'alert-edit-contactmode' : if(!(formFillBool)){
                                                                // $('#alert-edit-contact').val(-1);
                                                                // $('#alert-edit-contactgroup').val(-1);
                                                            }
                                                            switch(val){
                                                                 case  1  :
                                                                 case '1' : $('#div-alert-edit-contact').show();
                                                                            $('#div-alert-edit-contactgroup').hide();
                                                                            $('#div-alert-edit-contactmethod').show();
                                                                            if(!(formFillBool)){
                                                                                $('#alert-edit-contact').val(-1);
                                                                                // $('#alert-edit-contactgroup').val(-1);
                                                                            } else {
                                                                                $('#alert-edit-contact').trigger('change');
                                                                                // $('#alert-edit-contactgroup').trigger('change');
                                                                            }
                                                                            break;
                                                                 case  2  :
                                                                 case '2' : $('#div-alert-edit-contact').hide();
                                                                            $('#div-alert-edit-contactgroup').show();
                                                                            $('#div-alert-edit-contactmethod').show();
                                                                            if(!(formFillBool)){
                                                                                // $('#alert-edit-contact').val(-1);
                                                                                $('#alert-edit-contactgroup').val(-1);
                                                                            } else {
                                                                                // $('#alert-edit-contact').trigger('change');
                                                                                $('#alert-edit-contactgroup').trigger('change');
                                                                            }
                                                                            break;
                                                                 case  3  :
                                                                 case '3' : $('#div-alert-edit-contact').hide();
                                                                            $('#div-alert-edit-contactgroup').hide();
                                                                            $('#div-alert-edit-contactmethod').hide();
                                                                            if(!(formFillBool)){
                                                                                $('#alert-edit-contact').val(-1);
                                                                                $('#alert-edit-contactgroup').val(-1);
                                                                            } else {
                                                                                $('#alert-edit-contact').trigger('change');
                                                                                // $('#alert-edit-contactgroup').trigger('change');
                                                                            }
                                                                            break;
                                                            }
                                                            break;

            case                       'alert-edit-hours' : if(!(formFillBool)){
                                                                $('#alert-edit-starthour').val(6);
                                                                $('#alert-edit-endhour').val(18);
                                                            }
                                                            switch(val){
                                                                 case  1  :
                                                                 case '1' : $('#div-alert-edit-range').show();
                                                                            break;
                                                                  default : $('#div-alert-edit-range').hide();
                                                                            break;
                                                            }
                                                            break;

            case                'alert-edit-landmarkmode' : if(!(formFillBool)){
                                                                // $('#alert-edit-landmark').val(-1);
                                                                // $('#alert-edit-landmarkgroup').val(-1);
                                                            }
                                                            switch(val){
                                                                 case  1  :
                                                                 case '1' : $('#div-alert-edit-landmark').show();
                                                                            $('#div-alert-edit-landmarkgroup').hide();
                                                                            break;
                                                                 case  2  :
                                                                 case '2' : $('#div-alert-edit-landmark').hide();
                                                                            $('#div-alert-edit-landmarkgroup').show();
                                                                            break;
                                                                 case  3  :
                                                                 case '3' : $('#div-alert-edit-landmark').hide();
                                                                            $('#div-alert-edit-landmarkgroup').hide();
                                                                            break;
                                                            }
                                                            break;

            case                        'alert-edit-type' : switch(val){
                                                                 case  1  :
                                                                 case '1' : $('#div-alert-edit-duration').hide();
                                                                            $('#div-alert-edit-landmark').hide();
                                                                            $('#div-alert-edit-landmarkgroup').hide();
                                                                            $('#div-alert-edit-landmarkmode').hide();
                                                                            $('#div-alert-edit-landmarktrigger').hide();
                                                                            $('#div-alert-edit-overspeed').hide();
                                                                            $('#div-alert-edit-vehicle').hide();
                                                                            $('#div-alert-edit-vehiclegroup').hide();
                                                                            $('#div-alert-edit-vehiclemode').hide();
                                                                            $('#alert-edit-contactmode').trigger('change');
                                                                            $('#alert-edit-vehiclemode').trigger('change');
                                                                            break;

                                                                 case  2  :
                                                                 case '2' : $('#div-alert-edit-duration').show();
                                                                            $('#div-alert-edit-landmark').hide();
                                                                            $('#div-alert-edit-landmarkgroup').hide();
                                                                            $('#div-alert-edit-landmarkmode').hide();
                                                                            $('#div-alert-edit-landmarktrigger').hide();
                                                                            $('#div-alert-edit-overspeed').hide();
                                                                            $('#div-alert-edit-vehicle').hide();
                                                                            $('#div-alert-edit-vehiclegroup').hide();
                                                                            $('#div-alert-edit-vehiclemode').show();
                                                                            $('#alert-edit-contactmode').trigger('change');
                                                                            $('#alert-edit-vehiclemode').trigger('change');
                                                                            break;

                                                                 case  3  :
                                                                 case '3' : $('#div-alert-edit-duration').hide();
                                                                            $('#div-alert-edit-landmark').hide();
                                                                            $('#div-alert-edit-landmarkgroup').hide();
                                                                            $('#div-alert-edit-landmarkmode').show();
                                                                            $('#div-alert-edit-landmarktrigger').show();
                                                                            $('#div-alert-edit-overspeed').hide();
                                                                            $('#div-alert-edit-vehicle').hide();
                                                                            $('#div-alert-edit-vehiclegroup').hide();
                                                                            $('#div-alert-edit-vehiclemode').show();
                                                                            $('#alert-edit-contactmode').trigger('change');
                                                                            $('#alert-edit-landmarkmode').trigger('change');
                                                                            $('#alert-edit-vehiclemode').trigger('change');
                                                                            break;
                                                                 case  4  :
                                                                 case '4' : $('#div-alert-edit-duration').hide();
                                                                            $('#div-alert-edit-landmark').hide();
                                                                            $('#div-alert-edit-landmarkgroup').hide();
                                                                            $('#div-alert-edit-landmarkmode').hide();
                                                                            $('#div-alert-edit-landmarktrigger').hide();
                                                                            $('#div-alert-edit-overspeed').hide();
                                                                            $('#div-alert-edit-vehicle').hide();
                                                                            $('#div-alert-edit-vehiclegroup').hide();
                                                                            $('#div-alert-edit-vehiclemode').show();
                                                                            $('#alert-edit-contactmode').trigger('change');
                                                                            $('#alert-edit-vehiclemode').trigger('change');
                                                                            break;
                                                                 case  5  :
                                                                 case '5' : $('#div-alert-edit-duration').hide();
                                                                            $('#div-alert-edit-landmark').hide();
                                                                            $('#div-alert-edit-landmarkgroup').hide();
                                                                            $('#div-alert-edit-landmarkmode').hide();
                                                                            $('#div-alert-edit-landmarktrigger').hide();
                                                                            $('#div-alert-edit-overspeed').hide();
                                                                            $('#div-alert-edit-vehicle').hide();
                                                                            $('#div-alert-edit-vehiclegroup').hide();
                                                                            $('#div-alert-edit-vehiclemode').show();
                                                                            $('#alert-edit-contactmode').trigger('change');
                                                                            $('#alert-edit-vehiclemode').trigger('change');
                                                                            break;
                                                                 case  6  :
                                                                 case '6' : $('#div-alert-edit-duration').show();
                                                                            $('#div-alert-edit-landmark').hide();
                                                                            $('#div-alert-edit-landmarkgroup').hide();
                                                                            $('#div-alert-edit-landmarkmode').hide();
                                                                            $('#div-alert-edit-landmarktrigger').hide();
                                                                            $('#div-alert-edit-overspeed').hide();
                                                                            $('#div-alert-edit-vehicle').hide();
                                                                            $('#div-alert-edit-vehiclegroup').hide();
                                                                            $('#div-alert-edit-vehiclemode').show();
                                                                            $('#alert-edit-contactmode').trigger('change');
                                                                            $('#alert-edit-vehiclemode').trigger('change');
                                                                            break;
                                                                 case  7  :
                                                                 case '7' : $('#div-alert-edit-duration').hide();
                                                                            $('#div-alert-edit-landmark').hide();
                                                                            $('#div-alert-edit-landmarkgroup').hide();
                                                                            $('#div-alert-edit-landmarkmode').hide();
                                                                            $('#div-alert-edit-landmarktrigger').hide();
                                                                            $('#div-alert-edit-overspeed').show();
                                                                            $('#div-alert-edit-vehicle').hide();
                                                                            $('#div-alert-edit-vehiclegroup').hide();
                                                                            $('#div-alert-edit-vehiclemode').show();
                                                                            $('#alert-edit-contactmode').trigger('change');
                                                                            $('#alert-edit-vehiclemode').trigger('change');
                                                                            break;
                                                                 case  8  :
                                                                 case '8' : $('#div-alert-edit-duration').hide();
                                                                            $('#div-alert-edit-landmark').hide();
                                                                            $('#div-alert-edit-landmarkgroup').hide();
                                                                            $('#div-alert-edit-landmarkmode').hide();
                                                                            $('#div-alert-edit-landmarktrigger').hide();
                                                                            $('#div-alert-edit-overspeed').hide();
                                                                            $('#div-alert-edit-vehicle').hide();
                                                                            $('#div-alert-edit-vehiclegroup').hide();
                                                                            $('#div-alert-edit-vehiclemode').show();
                                                                            $('#alert-edit-contactmode').trigger('change');
                                                                            $('#alert-edit-vehiclemode').trigger('change');
                                                                            break;
                                                                 case  9  :
                                                                 case '9' : $('#div-alert-edit-duration').hide();
                                                                            $('#div-alert-edit-landmark').hide();
                                                                            $('#div-alert-edit-landmarkgroup').hide();
                                                                            $('#div-alert-edit-landmarkmode').hide();
                                                                            $('#div-alert-edit-landmarktrigger').hide();
                                                                            $('#div-alert-edit-overspeed').hide();
                                                                            $('#div-alert-edit-vehicle').hide();
                                                                            $('#div-alert-edit-vehiclegroup').hide();
                                                                            $('#div-alert-edit-vehiclemode').show();
                                                                            $('#alert-edit-contactmode').trigger('change');
                                                                            $('#alert-edit-vehiclemode').trigger('change');
                                                                            break;
                                                            }
                                                            break;

            case                 'alert-edit-vehiclemode' : if(!(formFillBool)){
                                                                // $('#alert-edit-vehicle').val(-1);
                                                                // $('#alert-edit-vehiclegroup').val(-1);
                                                            }
                                                            switch(val){
                                                                 case  1  :
                                                                 case '1' : $('#div-alert-edit-vehicle').show();
                                                                            $('#div-alert-edit-vehiclegroup').hide();
                                                                            break;
                                                                 case  2  :
                                                                 case '2' : $('#div-alert-edit-vehicle').hide();
                                                                            $('#div-alert-edit-vehiclegroup').show();
                                                                            break;
                                                                 case  3  :
                                                                 case '3' : $('#div-alert-edit-vehicle').hide();
                                                                            $('#div-alert-edit-vehiclegroup').hide();
                                                                            break;
                                                            }
                                                            break;

            case      'scheduled-report-edit-contactmode' : if(!(formFillBool)){
                                                                // $('#scheduled-report-edit-contacts').val(-1);
                                                                // $('#scheduled-report-edit-contactgroups').val(-1);
                                                            }
                                                            switch(val){
                                                                 case 'single' :
                                                                 case  1  :
                                                                 case '1' : $('#div-scheduled-report-edit-contacts').show();
                                                                            $('#div-scheduled-report-edit-contactgroups').hide();
                                                                            break;
                                                                 case 'group' :
                                                                 case  2  :
                                                                 case '2' : $('#div-scheduled-report-edit-contacts').hide();
                                                                            $('#div-scheduled-report-edit-contactgroups').show();
                                                                            break;
                                                                 case 'all' :
                                                                 case  3  :
                                                                 case '3' : $('#div-scheduled-report-edit-contacts').hide();
                                                                            $('#div-scheduled-report-edit-contactgroups').hide();
                                                                            break;
                                                            }
                                                            break;

            case            'scheduled-report-edit-range' : if(!(formFillBool)){
                                                                var d = new Date();
                                                                var month = d.getMonth()+1;
                                                                var day = d.getDate();
                                                                var ymd = (month<10 ? '0' : '') + month + '/' +
                                                                    (day<10 ? '0' : '') + day  + '/' +
                                                                    d.getFullYear();
                                                                $('#scheduled-report-edit-range-start').val(ymd);
                                                                $('#scheduled-report-edit-range-end').val(ymd);
                                                            }
                                                            switch(val){
                                                                 case 'Custom Range' :
                                                                 case  1  :
                                                                 case '1' : $('#div-scheduled-report-edit-range').show();
                                                                            break;
                                                                  default : $('#div-scheduled-report-edit-range').hide();
                                                            }
                                                            break;

            case     'scheduled-report-edit-landmarkmode' : if(!(formFillBool)){
                                                                // $('#scheduled-report-edit-landmarks').val(-1);
                                                                // $('#scheduled-report-edit-landmarkgroups').val(-1);
                                                            }
                                                            switch(val){
                                                                 case 'single' :
                                                                 case  1  :
                                                                 case '1' : $('#div-scheduled-report-edit-landmarks').show();
                                                                            $('#div-scheduled-report-edit-landmarkgroups').hide();
                                                                            break;
                                                                 case 'group' :
                                                                 case  2  :
                                                                 case '2' : $('#div-scheduled-report-edit-landmarks').hide();
                                                                            $('#div-scheduled-report-edit-landmarkgroups').show();
                                                                            break;
                                                                 case 'all' :
                                                                 case  3  :
                                                                 case '3' : $('#div-scheduled-report-edit-landmarks').hide();
                                                                            $('#div-scheduled-report-edit-landmarkgroups').hide();
                                                                            break;
                                                            }
                                                            break;

            case       'scheduled-report-edit-recurrence' : if(!(formFillBool)){
                                                                // $('#scheduled-report-edit-monthly').val(-1);
                                                                // $('#scheduled-report-edit-scheduleday').val(-1);
                                                                // $('#scheduled-report-edit-time').val(-1);
                                                            }
                                                            switch(val){
                                                                 case 'Daily' :
                                                                 case  1  :
                                                                 case '1' : $('#div-scheduled-report-edit-monthly').hide();
                                                                            $('#div-scheduled-report-edit-scheduleday').hide();
                                                                            $('#div-scheduled-report-edit-time').show();
                                                                            break;
                                                                 case 'Weekly' :
                                                                 case  2  :
                                                                 case '2' : $('#div-scheduled-report-edit-monthly').hide();
                                                                            $('#div-scheduled-report-edit-scheduleday').show();
                                                                            $('#div-scheduled-report-edit-time').show();
                                                                            break;
                                                                 case 'Monthly' :
                                                                 case  3  :
                                                                 case '3' : $('#div-scheduled-report-edit-monthly').show();
                                                                            $('#div-scheduled-report-edit-scheduleday').hide();
                                                                            $('#div-scheduled-report-edit-time').show();
                                                                            break;
                                                            }
                                                            break;

            case       'scheduled-report-edit-reporttype' : switch(val){
                                                                case   1  : // Alert
                                                                case  '1' : $('#div-scheduled-report-edit-alerttype').show();
                                                                            $('#div-scheduled-report-edit-contacts').hide();
                                                                            $('#div-scheduled-report-edit-contactgroups').hide();
                                                                            $('#div-scheduled-report-edit-date').show();
                                                                            $('#div-scheduled-report-edit-day').hide();
                                                                            $('#div-scheduled-report-edit-duration').hide();
                                                                            $('#div-scheduled-report-edit-landmarks').hide();
                                                                            $('#div-scheduled-report-edit-landmarkgroups').hide();
                                                                            $('#div-scheduled-report-edit-landmarkmode').hide();
                                                                            $('#div-scheduled-report-edit-mile').hide();
                                                                            $('#div-scheduled-report-edit-minute').hide();
                                                                            $('#div-scheduled-report-edit-monthly').hide();
                                                                            $('#div-scheduled-report-edit-mph').hide();
                                                                            $('#div-scheduled-report-edit-not-reported').hide();
                                                                            $('#div-scheduled-report-edit-range').hide();
                                                                            $('#div-scheduled-report-edit-scheduleday').hide();
                                                                            $('#div-scheduled-report-edit-time').hide();
                                                                            $('#div-scheduled-report-edit-vehicles').hide();
                                                                            $('#div-scheduled-report-edit-vehiclegroups').hide();
                                                                            $('#div-scheduled-report-edit-vehiclemode').show();
                                                                            $('#div-scheduled-report-edit-verification').hide();
                                                                            $('#scheduled-report-edit-contactmode').trigger('change');
                                                                            $('#scheduled-report-edit-date').trigger('change');
                                                                            $('#scheduled-report-edit-vehiclemode').trigger('change');
                                                                            $('#scheduled-report-edit-recurrence').trigger('change');
                                                                            break;
                                                                case   2  : // ???
                                                                case  '2' : $('#div-scheduled-report-edit-alerttype').hide();
                                                                            $('#div-scheduled-report-edit-contacts').hide();
                                                                            $('#div-scheduled-report-edit-contactgroups').hide();
                                                                            $('#div-scheduled-report-edit-date').hide();
                                                                            $('#div-scheduled-report-edit-day').hide();
                                                                            $('#div-scheduled-report-edit-duration').hide();
                                                                            $('#div-scheduled-report-edit-landmarks').hide();
                                                                            $('#div-scheduled-report-edit-landmarkgroups').hide();
                                                                            $('#div-scheduled-report-edit-landmarkmode').hide();
                                                                            $('#div-scheduled-report-edit-mile').hide();
                                                                            $('#div-scheduled-report-edit-minute').hide();
                                                                            $('#div-scheduled-report-edit-monthly').hide();
                                                                            $('#div-scheduled-report-edit-mph').hide();
                                                                            $('#div-scheduled-report-edit-not-reported').hide();
                                                                            $('#div-scheduled-report-edit-range').hide();
                                                                            $('#div-scheduled-report-edit-scheduleday').hide();
                                                                            $('#div-scheduled-report-edit-time').hide();
                                                                            $('#div-scheduled-report-edit-vehicles').hide();
                                                                            $('#div-scheduled-report-edit-vehiclegroups').hide();
                                                                            $('#div-scheduled-report-edit-vehiclemode').hide();
                                                                            $('#div-scheduled-report-edit-verification').hide();
                                                                            $('#scheduled-report-edit-contactmode').trigger('change');
                                                                            $('#scheduled-report-edit-recurrence').trigger('change');
                                                                            // $('#scheduled-report-edit-vehiclemode').trigger('change');
                                                                            break;
                                                                case   3  : // Detailed Event
                                                                case  '3' : $('#div-scheduled-report-edit-alerttype').hide();
                                                                            $('#div-scheduled-report-edit-contacts').hide();
                                                                            $('#div-scheduled-report-edit-contactgroups').hide();
                                                                            $('#div-scheduled-report-edit-date').hide();
                                                                            $('#div-scheduled-report-edit-day').hide();
                                                                            $('#div-scheduled-report-edit-duration').hide();
                                                                            $('#div-scheduled-report-edit-landmarks').hide();
                                                                            $('#div-scheduled-report-edit-landmarkgroups').hide();
                                                                            $('#div-scheduled-report-edit-landmarkmode').hide();
                                                                            $('#div-scheduled-report-edit-mile').hide();
                                                                            $('#div-scheduled-report-edit-minute').hide();
                                                                            $('#div-scheduled-report-edit-monthly').hide();
                                                                            $('#div-scheduled-report-edit-mph').hide();
                                                                            $('#div-scheduled-report-edit-not-reported').hide();
                                                                            $('#div-scheduled-report-edit-range').hide();
                                                                            $('#div-scheduled-report-edit-scheduleday').hide();
                                                                            $('#div-scheduled-report-edit-time').hide();
                                                                            $('#div-scheduled-report-edit-vehicles').show();
                                                                            $('#div-scheduled-report-edit-vehiclegroups').hide();
                                                                            $('#div-scheduled-report-edit-vehiclemode').hide();
                                                                            $('#div-scheduled-report-edit-verification').hide();
                                                                            $('#scheduled-report-edit-contactmode').trigger('change');
                                                                            $('#scheduled-report-edit-recurrence').trigger('change');
                                                                            break;
                                                                case   4  : // Frequent Stops
                                                                case  '4' : $('#div-scheduled-report-edit-alerttype').hide();
                                                                            $('#div-scheduled-report-edit-contacts').hide();
                                                                            $('#div-scheduled-report-edit-contactgroups').hide();
                                                                            $('#div-scheduled-report-edit-date').show();
                                                                            $('#div-scheduled-report-edit-day').hide();
                                                                            $('#div-scheduled-report-edit-duration').show();
                                                                            $('#div-scheduled-report-edit-landmarks').hide();
                                                                            $('#div-scheduled-report-edit-landmarkgroups').hide();
                                                                            $('#div-scheduled-report-edit-landmarkmode').hide();
                                                                            $('#div-scheduled-report-edit-mile').hide();
                                                                            $('#div-scheduled-report-edit-minute').hide();
                                                                            $('#div-scheduled-report-edit-monthly').hide();
                                                                            $('#div-scheduled-report-edit-mph').hide();
                                                                            $('#div-scheduled-report-edit-not-reported').hide();
                                                                            $('#div-scheduled-report-edit-range').hide();
                                                                            $('#div-scheduled-report-edit-scheduleday').hide();
                                                                            $('#div-scheduled-report-edit-time').hide();
                                                                            $('#div-scheduled-report-edit-vehicles').show();
                                                                            $('#div-scheduled-report-edit-vehiclegroups').hide();
                                                                            $('#div-scheduled-report-edit-vehiclemode').hide();
                                                                            $('#div-scheduled-report-edit-verification').hide();
                                                                            $('#scheduled-report-edit-contactmode').trigger('change');
                                                                            $('#scheduled-report-edit-date').trigger('change');
                                                                            $('#scheduled-report-edit-recurrence').trigger('change');
                                                                            break;
                                                                case   5  : // Landmark
                                                                case  '5' : $('#div-scheduled-report-edit-alerttype').hide();
                                                                            $('#div-scheduled-report-edit-contacts').hide();
                                                                            $('#div-scheduled-report-edit-contactgroups').hide();
                                                                            $('#div-scheduled-report-edit-date').hide();
                                                                            $('#div-scheduled-report-edit-day').hide();
                                                                            $('#div-scheduled-report-edit-duration').hide();
                                                                            $('#div-scheduled-report-edit-landmarks').hide();
                                                                            $('#div-scheduled-report-edit-landmarkgroups').hide();
                                                                            $('#div-scheduled-report-edit-landmarkmode').show();
                                                                            $('#div-scheduled-report-edit-mile').hide();
                                                                            $('#div-scheduled-report-edit-minute').hide();
                                                                            $('#div-scheduled-report-edit-monthly').hide();
                                                                            $('#div-scheduled-report-edit-mph').hide();
                                                                            $('#div-scheduled-report-edit-not-reported').hide();
                                                                            $('#div-scheduled-report-edit-range').hide();
                                                                            $('#div-scheduled-report-edit-scheduleday').hide();
                                                                            $('#div-scheduled-report-edit-time').hide();
                                                                            $('#div-scheduled-report-edit-vehicles').hide();
                                                                            $('#div-scheduled-report-edit-vehiclegroups').hide();
                                                                            $('#div-scheduled-report-edit-vehiclemode').show();
                                                                            $('#div-scheduled-report-edit-verification').hide();
                                                                            $('#scheduled-report-edit-contactmode').trigger('change');
                                                                            $('#scheduled-report-edit-landmarkmode').trigger('change');
                                                                            $('#scheduled-report-edit-vehiclemode').trigger('change');
                                                                            $('#scheduled-report-edit-recurrence').trigger('change');
                                                                            break;
                                                                case   6  : // Mileage Summary
                                                                case  '6' : $('#div-scheduled-report-edit-alerttype').hide();
                                                                            $('#div-scheduled-report-edit-contacts').hide();
                                                                            $('#div-scheduled-report-edit-contactgroups').hide();
                                                                            $('#div-scheduled-report-edit-date').hide();
                                                                            $('#div-scheduled-report-edit-day').hide();
                                                                            $('#div-scheduled-report-edit-duration').hide();
                                                                            $('#div-scheduled-report-edit-landmarks').hide();
                                                                            $('#div-scheduled-report-edit-landmarkgroups').hide();
                                                                            $('#div-scheduled-report-edit-landmarkmode').hide();
                                                                            $('#div-scheduled-report-edit-mile').show();
                                                                            $('#div-scheduled-report-edit-minute').hide();
                                                                            $('#div-scheduled-report-edit-monthly').hide();
                                                                            $('#div-scheduled-report-edit-mph').hide();
                                                                            $('#div-scheduled-report-edit-not-reported').hide();
                                                                            $('#div-scheduled-report-edit-range').hide();
                                                                            $('#div-scheduled-report-edit-scheduleday').hide();
                                                                            $('#div-scheduled-report-edit-time').hide();
                                                                            $('#div-scheduled-report-edit-vehicles').hide();
                                                                            $('#div-scheduled-report-edit-vehiclegroups').hide();
                                                                            $('#div-scheduled-report-edit-vehiclemode').show();
                                                                            $('#div-scheduled-report-edit-verification').hide();
                                                                            $('#scheduled-report-edit-contactmode').trigger('change');
                                                                            $('#scheduled-report-edit-recurrence').trigger('change');
                                                                            $('#scheduled-report-edit-vehiclemode').trigger('change');
                                                                            break;
                                                                case   7  : // Non-Reporting
                                                                case  '7' : $('#div-scheduled-report-edit-alerttype').hide();
                                                                            $('#div-scheduled-report-edit-contacts').hide();
                                                                            $('#div-scheduled-report-edit-contactgroups').hide();
                                                                            $('#div-scheduled-report-edit-date').hide();
                                                                            $('#div-scheduled-report-edit-day').hide();
                                                                            $('#div-scheduled-report-edit-duration').hide();
                                                                            $('#div-scheduled-report-edit-landmarks').hide();
                                                                            $('#div-scheduled-report-edit-landmarkgroups').hide();
                                                                            $('#div-scheduled-report-edit-landmarkmode').hide();
                                                                            $('#div-scheduled-report-edit-mile').hide();
                                                                            $('#div-scheduled-report-edit-minute').hide();
                                                                            $('#div-scheduled-report-edit-monthly').hide();
                                                                            $('#div-scheduled-report-edit-mph').hide();
                                                                            $('#div-scheduled-report-edit-not-reported').show();
                                                                            $('#div-scheduled-report-edit-range').hide();
                                                                            $('#div-scheduled-report-edit-scheduleday').hide();
                                                                            $('#div-scheduled-report-edit-time').hide();
                                                                            $('#div-scheduled-report-edit-vehicles').hide();
                                                                            $('#div-scheduled-report-edit-vehiclegroups').hide();
                                                                            $('#div-scheduled-report-edit-vehiclemode').show();
                                                                            $('#div-scheduled-report-edit-verification').hide();
                                                                            $('#scheduled-report-edit-contactmode').trigger('change');
                                                                            $('#scheduled-report-edit-recurrence').trigger('change');
                                                                            $('#scheduled-report-edit-vehiclemode').trigger('change');
                                                                            break;
                                                                case   8  : // Speed Summary
                                                                case  '8' : $('#div-scheduled-report-edit-alerttype').hide();
                                                                            $('#div-scheduled-report-edit-contacts').hide();
                                                                            $('#div-scheduled-report-edit-contactgroups').hide();
                                                                            $('#div-scheduled-report-edit-date').hide();
                                                                            $('#div-scheduled-report-edit-day').hide();
                                                                            $('#div-scheduled-report-edit-duration').hide();
                                                                            $('#div-scheduled-report-edit-landmarks').hide();
                                                                            $('#div-scheduled-report-edit-landmarkgroups').hide();
                                                                            $('#div-scheduled-report-edit-landmarkmode').hide();
                                                                            $('#div-scheduled-report-edit-mile').hide();
                                                                            $('#div-scheduled-report-edit-minute').hide();
                                                                            $('#div-scheduled-report-edit-monthly').hide();
                                                                            $('#div-scheduled-report-edit-mph').show();
                                                                            $('#div-scheduled-report-edit-not-reported').hide();
                                                                            $('#div-scheduled-report-edit-range').hide();
                                                                            $('#div-scheduled-report-edit-scheduleday').hide();
                                                                            $('#div-scheduled-report-edit-time').hide();
                                                                            $('#div-scheduled-report-edit-vehicles').hide();
                                                                            $('#div-scheduled-report-edit-vehiclegroups').hide();
                                                                            $('#div-scheduled-report-edit-vehiclemode').show();
                                                                            $('#div-scheduled-report-edit-verification').hide();
                                                                            $('#scheduled-report-edit-contactmode').trigger('change');
                                                                            $('#scheduled-report-edit-recurrence').trigger('change');
                                                                            $('#scheduled-report-edit-vehiclemode').trigger('change');
                                                                            break;
                                                                case   9  : // Starter Disable Summary
                                                                case  '9' : $('#div-scheduled-report-edit-alerttype').hide();
                                                                            $('#div-scheduled-report-edit-contacts').hide();
                                                                            $('#div-scheduled-report-edit-contactgroups').hide();
                                                                            $('#div-scheduled-report-edit-date').hide();
                                                                            $('#div-scheduled-report-edit-day').hide();
                                                                            $('#div-scheduled-report-edit-duration').hide();
                                                                            $('#div-scheduled-report-edit-landmarks').hide();
                                                                            $('#div-scheduled-report-edit-landmarkgroups').hide();
                                                                            $('#div-scheduled-report-edit-landmarkmode').hide();
                                                                            $('#div-scheduled-report-edit-mile').hide();
                                                                            $('#div-scheduled-report-edit-minute').hide();
                                                                            $('#div-scheduled-report-edit-monthly').hide();
                                                                            $('#div-scheduled-report-edit-mph').hide();
                                                                            $('#div-scheduled-report-edit-not-reported').hide();
                                                                            $('#div-scheduled-report-edit-range').hide();
                                                                            $('#div-scheduled-report-edit-scheduleday').hide();
                                                                            $('#div-scheduled-report-edit-time').hide();
                                                                            $('#div-scheduled-report-edit-vehicles').hide();
                                                                            $('#div-scheduled-report-edit-vehiclegroups').hide();
                                                                            $('#div-scheduled-report-edit-vehiclemode').show();
                                                                            $('#div-scheduled-report-edit-verification').hide();
                                                                            $('#scheduled-report-edit-contactmode').trigger('change');
                                                                            $('#scheduled-report-edit-recurrence').trigger('change');
                                                                            $('#scheduled-report-edit-vehiclemode').trigger('change');
                                                                            break;
                                                                case  10  : // Stationary
                                                                case '10' : $('#div-scheduled-report-edit-alerttype').hide();
                                                                            $('#div-scheduled-report-edit-contacts').hide();
                                                                            $('#div-scheduled-report-edit-contactgroups').hide();
                                                                            $('#div-scheduled-report-edit-date').hide();
                                                                            $('#div-scheduled-report-edit-day').show();
                                                                            $('#div-scheduled-report-edit-duration').hide();
                                                                            $('#div-scheduled-report-edit-landmarks').hide();
                                                                            $('#div-scheduled-report-edit-landmarkgroups').hide();
                                                                            $('#div-scheduled-report-edit-landmarkmode').hide();
                                                                            $('#div-scheduled-report-edit-mile').hide();
                                                                            $('#div-scheduled-report-edit-minute').hide();
                                                                            $('#div-scheduled-report-edit-monthly').hide();
                                                                            $('#div-scheduled-report-edit-mph').hide();
                                                                            $('#div-scheduled-report-edit-not-reported').hide();
                                                                            $('#div-scheduled-report-edit-range').hide();
                                                                            $('#div-scheduled-report-edit-scheduleday').hide();
                                                                            $('#div-scheduled-report-edit-time').hide();
                                                                            $('#div-scheduled-report-edit-vehicles').hide();
                                                                            $('#div-scheduled-report-edit-vehiclegroups').hide();
                                                                            $('#div-scheduled-report-edit-vehiclemode').show();
                                                                            $('#div-scheduled-report-edit-verification').hide();
                                                                            $('#scheduled-report-edit-contactmode').trigger('change');
                                                                            $('#scheduled-report-edit-recurrence').trigger('change');
                                                                            $('#scheduled-report-edit-vehiclemode').trigger('change');
                                                                            break;
                                                                case  11  : // Stop
                                                                case '11' : $('#div-scheduled-report-edit-alerttype').hide();
                                                                            $('#div-scheduled-report-edit-contacts').hide();
                                                                            $('#div-scheduled-report-edit-contactgroups').hide();
                                                                            $('#div-scheduled-report-edit-date').hide();
                                                                            $('#div-scheduled-report-edit-day').hide();
                                                                            $('#div-scheduled-report-edit-duration').hide();
                                                                            $('#div-scheduled-report-edit-landmarks').hide();
                                                                            $('#div-scheduled-report-edit-landmarkgroups').hide();
                                                                            $('#div-scheduled-report-edit-landmarkmode').hide();
                                                                            $('#div-scheduled-report-edit-mile').hide();
                                                                            $('#div-scheduled-report-edit-minute').show();
                                                                            $('#div-scheduled-report-edit-monthly').hide();
                                                                            $('#div-scheduled-report-edit-mph').hide();
                                                                            $('#div-scheduled-report-edit-not-reported').hide();
                                                                            $('#div-scheduled-report-edit-range').hide();
                                                                            $('#div-scheduled-report-edit-scheduleday').hide();
                                                                            $('#div-scheduled-report-edit-time').hide();
                                                                            $('#div-scheduled-report-edit-vehicles').show();
                                                                            $('#div-scheduled-report-edit-vehiclegroups').hide();
                                                                            $('#div-scheduled-report-edit-vehiclemode').hide();
                                                                            $('#div-scheduled-report-edit-verification').hide();
                                                                            $('#scheduled-report-edit-contactmode').trigger('change');
                                                                            $('#scheduled-report-edit-recurrence').trigger('change');
                                                                            break;
                                                                case  12  : // User Command
                                                                case '12' : $('#div-scheduled-report-edit-alerttype').hide();
                                                                            $('#div-scheduled-report-edit-contacts').show();
                                                                            $('#div-scheduled-report-edit-contactgroups').hide();
                                                                            $('#div-scheduled-report-edit-date').hide();
                                                                            $('#div-scheduled-report-edit-day').hide();
                                                                            $('#div-scheduled-report-edit-duration').hide();
                                                                            $('#div-scheduled-report-edit-landmarks').hide();
                                                                            $('#div-scheduled-report-edit-landmarkgroups').hide();
                                                                            $('#div-scheduled-report-edit-landmarkmode').hide();
                                                                            $('#div-scheduled-report-edit-mile').hide();
                                                                            $('#div-scheduled-report-edit-minute').hide();
                                                                            $('#div-scheduled-report-edit-monthly').hide();
                                                                            $('#div-scheduled-report-edit-mph').hide();
                                                                            $('#div-scheduled-report-edit-not-reported').hide();
                                                                            $('#div-scheduled-report-edit-range').hide();
                                                                            $('#div-scheduled-report-edit-scheduleday').hide();
                                                                            $('#div-scheduled-report-edit-time').hide();
                                                                            $('#div-scheduled-report-edit-vehicles').hide();
                                                                            $('#div-scheduled-report-edit-vehiclegroups').hide();
                                                                            $('#div-scheduled-report-edit-vehiclemode').hide();
                                                                            $('#div-scheduled-report-edit-verification').hide();
                                                                            $('#scheduled-report-edit-contactmode').trigger('change');
                                                                            $('#scheduled-report-edit-recurrence').trigger('change');
                                                                            break;
                                                                case  13  : // Vehicle Information
                                                                case '13' : $('#div-scheduled-report-edit-alerttype').hide();
                                                                            $('#div-scheduled-report-edit-contacts').hide();
                                                                            $('#div-scheduled-report-edit-contactgroups').hide();
                                                                            $('#div-scheduled-report-edit-date').hide();
                                                                            $('#div-scheduled-report-edit-day').hide();
                                                                            $('#div-scheduled-report-edit-duration').hide();
                                                                            $('#div-scheduled-report-edit-landmarks').hide();
                                                                            $('#div-scheduled-report-edit-landmarkgroups').hide();
                                                                            $('#div-scheduled-report-edit-landmarkmode').hide();
                                                                            $('#div-scheduled-report-edit-mile').hide();
                                                                            $('#div-scheduled-report-edit-minute').hide();
                                                                            $('#div-scheduled-report-edit-mph').hide();
                                                                            $('#div-scheduled-report-edit-monthly').hide();
                                                                            $('#div-scheduled-report-edit-not-reported').hide();
                                                                            $('#div-scheduled-report-edit-range').hide();
                                                                            $('#div-scheduled-report-edit-scheduleday').hide();
                                                                            $('#div-scheduled-report-edit-time').hide();
                                                                            $('#div-scheduled-report-edit-vehicles').hide();
                                                                            $('#div-scheduled-report-edit-vehiclegroups').hide();
                                                                            $('#div-scheduled-report-edit-vehiclemode').show();
                                                                            $('#div-scheduled-report-edit-verification').hide();
                                                                            $('#scheduled-report-edit-contactmode').trigger('change');
                                                                            $('#scheduled-report-edit-recurrence').trigger('change');
                                                                            $('#scheduled-report-edit-vehiclemode').trigger('change');
                                                                            break;
                                                                case  14  : // Address Verification
                                                                case '14' : $('#div-scheduled-report-edit-alerttype').hide();
                                                                            $('#div-scheduled-report-edit-contacts').hide();
                                                                            $('#div-scheduled-report-edit-contactgroups').hide();
                                                                            $('#div-scheduled-report-edit-date').hide();
                                                                            $('#div-scheduled-report-edit-day').hide();
                                                                            $('#div-scheduled-report-edit-duration').hide();
                                                                            $('#div-scheduled-report-edit-landmarks').hide();
                                                                            $('#div-scheduled-report-edit-landmarkgroups').hide();
                                                                            $('#div-scheduled-report-edit-landmarkmode').hide();
                                                                            $('#div-scheduled-report-edit-mile').hide();
                                                                            $('#div-scheduled-report-edit-minute').hide();
                                                                            $('#div-scheduled-report-edit-monthly').hide();
                                                                            $('#div-scheduled-report-edit-mph').hide();
                                                                            $('#div-scheduled-report-edit-not-reported').hide();
                                                                            $('#div-scheduled-report-edit-range').hide();
                                                                            $('#div-scheduled-report-edit-scheduleday').hide();
                                                                            $('#div-scheduled-report-edit-time').hide();
                                                                            $('#div-scheduled-report-edit-vehicles').hide();
                                                                            $('#div-scheduled-report-edit-vehiclegroups').hide();
                                                                            $('#div-scheduled-report-edit-vehiclemode').show();
                                                                            $('#div-scheduled-report-edit-verification').show();
                                                                            $('#scheduled-report-edit-contactmode').trigger('change');
                                                                            $('#scheduled-report-edit-recurrence').trigger('change');
                                                                            $('#scheduled-report-edit-vehiclemode').trigger('change');
                                                                            break;
                                                                case  15  : // Last Ten Stops
                                                                case '15' : $('#div-scheduled-report-edit-alerttype').hide();
                                                                            $('#div-scheduled-report-edit-contacts').hide();
                                                                            $('#div-scheduled-report-edit-contactgroups').hide();
                                                                            $('#div-scheduled-report-edit-date').hide();
                                                                            $('#div-scheduled-report-edit-day').hide();
                                                                            $('#div-scheduled-report-edit-duration').hide();
                                                                            $('#div-scheduled-report-edit-landmarks').hide();
                                                                            $('#div-scheduled-report-edit-landmarkgroups').hide();
                                                                            $('#div-scheduled-report-edit-landmarkmode').hide();
                                                                            $('#div-scheduled-report-edit-mile').hide();
                                                                            $('#div-scheduled-report-edit-minute').hide();
                                                                            $('#div-scheduled-report-edit-monthly').hide();
                                                                            $('#div-scheduled-report-edit-mph').hide();
                                                                            $('#div-scheduled-report-edit-not-reported').hide();
                                                                            $('#div-scheduled-report-edit-range').hide();
                                                                            $('#div-scheduled-report-edit-scheduleday').hide();
                                                                            $('#div-scheduled-report-edit-time').hide();
                                                                            $('#div-scheduled-report-edit-vehicles').show();
                                                                            $('#div-scheduled-report-edit-vehiclegroups').hide();
                                                                            $('#div-scheduled-report-edit-vehiclemode').hide();
                                                                            $('#div-scheduled-report-edit-verification').hide();
                                                                            $('#scheduled-report-edit-contactmode').trigger('change');
                                                                            $('#scheduled-report-edit-recurrence').trigger('change');
                                                                            break;
                                                                  default : $('#div-scheduled-report-edit-alerttype').hide();
                                                                            $('#div-scheduled-report-edit-contacts').hide();
                                                                            $('#div-scheduled-report-edit-contactgroups').hide();
                                                                            $('#div-scheduled-report-edit-date').hide();
                                                                            $('#div-scheduled-report-edit-day').hide();
                                                                            $('#div-scheduled-report-edit-duration').hide();
                                                                            $('#div-scheduled-report-edit-landmarks').hide();
                                                                            $('#div-scheduled-report-edit-landmarkgroups').hide();
                                                                            $('#div-scheduled-report-edit-landmarkmode').hide();
                                                                            $('#div-scheduled-report-edit-mile').hide();
                                                                            $('#div-scheduled-report-edit-minute').hide();
                                                                            $('#div-scheduled-report-edit-monthly').hide();
                                                                            $('#div-scheduled-report-edit-mph').hide();
                                                                            $('#div-scheduled-report-edit-not-reported').hide();
                                                                            $('#div-scheduled-report-edit-range').hide();
                                                                            $('#div-scheduled-report-edit-scheduleday').hide();
                                                                            $('#div-scheduled-report-edit-time').hide();
                                                                            $('#div-scheduled-report-edit-vehicles').hide();
                                                                            $('#div-scheduled-report-edit-vehiclegroups').hide();
                                                                            $('#div-scheduled-report-edit-verification').hide();
                                                                            $('#scheduled-report-edit-contactmode').trigger('change');
                                                                            $('#scheduled-report-edit-recurrence').trigger('change');
                                                            }
                                                            break;

            case      'scheduled-report-edit-vehiclemode' : if(!(formFillBool)){
                                                                // $('#scheduled-report-edit-vehicles').val(-1);
                                                                // $('#scheduled-report-edit-vehiclegroups').val(-1);
                                                            }
                                                            switch(val){
                                                                 case 'single' :
                                                                 case  1  :
                                                                 case '1' : $('#div-scheduled-report-edit-vehicles').show();
                                                                            $('#div-scheduled-report-edit-vehiclegroups').hide();
                                                                            break;
                                                                 case 'group' :
                                                                 case  2  :
                                                                 case '2' : $('#div-scheduled-report-edit-vehicles').hide();
                                                                            $('#div-scheduled-report-edit-vehiclegroups').show();
                                                                            break;
                                                                 case 'all' :
                                                                 case  3  :
                                                                 case '3' : $('#div-scheduled-report-edit-vehicles').hide();
                                                                            $('#div-scheduled-report-edit-vehiclegroups').hide();
                                                                            break;
                                                            }
                                                            break;

        }

    },

    EditMap: {

        Address: function (startOver) {
            if(startOver){
                editMapCount=startOver;
            }
            setTimeout('if(editMapCount>1){editMapCount--;Core.EditMap.Address();}else{if(editMapCount>0){Core.EditMap.AddressGet();}editMapCount=0;}',100);
        },

        AddressGet: function () {
            $('#landmark-latitude').html('');                            
            $('#landmark-longitude').html('');                            
            var address = '';
            var addressBool = '';
            if(($('#landmark-street-address').text())&&($('#landmark-street-address').text()!='No Data')){
                address+=$('#landmark-street-address').text();
                addressBool=1;
            }
            if(($('#landmark-city').text())&&($('#landmark-city').text()!='No Data')){
                if(addressBool){
                    address+=', ';
                }
                address+=$('#landmark-city').text();
                addressBool=1;
            }    
            if($('#landmark-state').val()){
                if(addressBool){
                    address+=', ';
                }
                address+=$('#landmark-state').val();
                addressBool=1;
            }    
            if(($('#landmark-zipcode').text())&&($('#landmark-zipcode').text()!='No Data')){
                if(addressBool){
                    address+=', ';
                }
                address+=$('#landmark-zipcode').text();
                addressBool=1;
            }    
            if($('#landmark-country').val()){
                if(addressBool){
                    address+=', ';
                }
                address+=$('#landmark-country').val();
                addressBool=1;
            }    
console.log('Core.EditMap.AddressGet:'+address);
            Map.geocode(Landmark.Common.map, address, function(results, status){
                if(results){
                    console.log('Core.EditMap.AddressGet:results...');
                    console.log(results);
                    $('#landmark-latitude').html(results.latitude);                            
                    $('#landmark-longitude').html(results.longitude);                            
                    Map.clearMarkers(Landmark.Common.map);
                    editMapCoordinates = null;

                    var shape = $('#landmark-shape').val();
                    var radius = $('#landmark-radius').val();
console.log('shape:'+shape);
                    if (shape == 'rectangle' || shape == 'polygon' || shape == 'square') { // for rectangles, squares, and polygons
                        // get the coordinates of the temp polygon as an array of objects, each with two properties called 'latitude' and 'longitude'
                        // (i.e. [{latitude: 12.345, longitude: 45.56}, {latitude: 34.45, longitude: 56.788}, ...]) 
                        var coordinates = Map.getTempPolygonPoints(Landmark.Common.map);
                        
                        if ((shape == 'rectangle' || shape == 'square')) {
                            if (coordinates.length == 4) { // rectangle or square needs 5 points to connect
                                editMapCoordinates = coordinates;
                            } else {
                                console.log('Rectangle landmarks require 2 points');
                            }
                        } else if (shape == 'polygon') { // polygon needs at least 4 points to connect (i.e triangle)
                            if (coordinates.length >= 3) {
                                editMapCoordinates = coordinates;
                            } else {                                            
                                console.log('Polygon landmarks require 3 points');
                            }
                        }
                    }

                    var polygonOptions=Array();
                    polygonOptions.type = shape;
                    if (shape == 'circle') {
                        polygonOptions.radius = radius;
                    } else if (shape == 'square') {
                        polygonOptions.radius = radius;
                        polygonOptions.points = editMapCoordinates;
                    } else {
                        polygonOptions.points = editMapCoordinates;
                    }

console.log('polygonOptions...');
console.log(polygonOptions);

//                     Map.addMarkerWithPolygon(
//                         Landmark.Common.map,
//                         {
//                             id: 999,
//                             name: address + ' (' + results.latitude + ' / ' + results.longitude + ')',
//                             latitude: results.latitude,
//                             longitude: results.longitude
//                         },
//                         false,
//                         polygonOptions
//                     );

//                     Map.resetMap(Landmark.Common.map);
//                     Map.resize(Landmark.Common.map);
//                     Map.updateMapBound(Landmark.Common.map);
console.log(address)
//                     if(address=='USA'){
//                         Map.mapZoom(Landmark.Common.map,'4');
//                     } else {
//                         Map.updateMapBoundZoom(Landmark.Common.map);
//                     }
                    
                    // Core.Ajax('landmark-latlong',results.latitude+':'+results.longitude,currentLandmarkId,'update');
                    var laStreet = $('#landmark-street-address').text();
                    var laCity = $('#landmark-city').text();
                    var laState = $('#landmark-state').val();
                    var laZipcode = $('#landmark-zipcode').text();
                    var laCountry = $('#landmark-country').val();
                    if((laStreet == 'undefined')||(laStreet == 'No Data')){ laStreet='';}
                    if((laCity == 'undefined')||(laCity == 'No Data')){ laCity='';}
                    if((laZipcode == 'undefined')||(laZipcode == 'No Data')){ laZipcode='';}
                    var laArray = [ results.latitude , results.longitude , laStreet , laCity , laState , laZipcode , laCountry ] ;

                    Core.Ajax('updatelandmark',laArray,currentLandmarkId,'updatelandmark');

                } else {
                    Core.log('Core.EditMap.AddressGet:No Results');
                }
            });        

        },

        LandmarkGet: function (eid,rid,tbl) {
            if((!(tbl))||(tbl=='undefined')){
                tbl='list';
            }
console.log('landmarkGet:eid="'+eid+'", rid="'+rid+'", tbl="'+tbl+'"');
            if(eid){
                var rec = eid.split('-').pop();
                var streetaddress = $('#landmark-'+tbl+'-table-crossbones-territory-streetaddress-'+rec).text();
                var city = $('#landmark-'+tbl+'-table-crossbones-territory-city-'+rec).text();
                var state = $('#landmark-'+tbl+'-table-crossbones-territory-state-'+rec).text();
                var zipcode = $('#landmark-'+tbl+'-table-crossbones-territory-zipcode-'+rec).text();
                var country = $('#landmark-'+tbl+'-table-crossbones-territory-country-'+rec).text();
                var address = streetaddress;
                if(streetaddress=='⊕'){
                    streetaddress='';
                }
                if(city=='⊕'){
                    city='';
                }
                if(state=='⊕'){
                    state='';
                }
                if(zipcode=='⊕'){
                    zipcode='';
                }
                if(country=='⊕'){
                    country='';
                }
                if(address){address += ', '; }
                address += city;
                if(address){address += ', '; }
                address += state;
                if(address){address += ', '; }
                address += zipcode;
                if(address){address += ', '; }
                address += country;
console.log('landmarkGet:address:'+address);
                Map.geocode(Landmark.Common.addmap, address, function(results, status){
                    if(results){
console.log('Core.EditMap.LandmarkGet:results...');
console.log(results);
                        Core.Ajax(rec,results.latitude+':'+results.longitude+':'+tbl,rid,'dbupdate','crossbones-territory-latlong');
                    }
                });                            
            }
        },

        LatLng: function (latLng) {
console.log(latLng);
            Map.reverseGeocode(Landmark.Common.addmap, latLng.latitude, latLng.longitude, function(results, status){
                if(results){
                    console.log('Core.EditMap.LatLng:results...');
                    console.log(results);
                    $('#landmark-add-latitude').text(results.latitude);                            
                    $('#landmark-add-longitude').text(results.longitude);                            
                    Map.clearMarkers(Landmark.Common.addmap);
                    editMapCoordinates = null;

                    var shape = $('#landmark-add-shape').val();
                    var radius = $('#landmark-add-radius').val();
console.log('shape:'+shape);
                    if (shape == 'rectangle' || shape == 'polygon' || shape == 'square') { // for rectangles, squares, and polygons
                        // get the coordinates of the temp polygon as an array of objects, each with two properties called 'latitude' and 'longitude'
                        // (i.e. [{latitude: 12.345, longitude: 45.56}, {latitude: 34.45, longitude: 56.788}, ...]) 
                        var coordinates = Map.getTempPolygonPoints(Landmark.Common.map);
                        
                        if ((shape == 'rectangle' || shape == 'square')) {
                            if (coordinates.length == 4) { // rectangle or square needs 5 points to connect
                                editMapCoordinates = coordinates;
                            } else {
                                console.log('Rectangle landmarks require 2 points');
                            }
                        } else if (shape == 'polygon') { // polygon needs at least 4 points to connect (i.e triangle)
                            if (coordinates.length >= 3) {
                                editMapCoordinates = coordinates;
                            } else {                                            
                                console.log('Polygon landmarks require 3 points');
                            }
                        }
                    }

                    var polygonOptions=Array();
                    polygonOptions.type = shape;
                    if (shape == 'circle') {
                        polygonOptions.radius = radius;
                    } else if (shape == 'square') {
                        polygonOptions.radius = radius;
                        polygonOptions.points = editMapCoordinates;
                    } else {
                        polygonOptions.points = editMapCoordinates;
                    }

console.log('polygonOptions...');
console.log(polygonOptions);

                    Map.addMarkerWithPolygon(
                        Landmark.Common.addmap,
                        {
                            id: 999,
                            name: address + ' (' + results.latitude + ' / ' + results.longitude + ')',
                            latitude: results.latitude,
                            longitude: results.longitude
                        },
                        false,
                        polygonOptions
                    );

                    Map.resetMap(Landmark.Common.addmap);
                    Map.resize(Landmark.Common.addmap);
                    Map.updateMapBound(Landmark.Common.addmap);
                    Map.updateMapBoundZoom(Landmark.Common.addmap);
                } else {
                    Core.log('Core.EditMap.LatLng:No Results');
                }
            });        

        },

        LatLngEdit: function (latLng) {
console.log(latLng);
            Map.reverseGeocode(Landmark.Common.addmap, latLng.latitude, latLng.longitude, function(results, status){
                if(results){
                    console.log('Core.AddMap.LatLng:results...');
                    console.log(results);
                    $('#landmark-latitude').html(latLng.latitude);                            
                    $('#landmark-longitude').html(latLng.longitude);                            
                    Map.clearMarkers(Landmark.Common.addmap);
                    addMapCoordinates = null;

                    var shape = $('#landmark-shape').val();
                    var radius = $('#landmark-radius').val();
console.log('shape:'+shape);
                    if (shape == 'rectangle' || shape == 'polygon' || shape == 'square') { // for rectangles, squares, and polygons
                        // get the coordinates of the temp polygon as an array of objects, each with two properties called 'latitude' and 'longitude'
                        // (i.e. [{latitude: 12.345, longitude: 45.56}, {latitude: 34.45, longitude: 56.788}, ...]) 
                        var coordinates = Map.getTempPolygonPoints(Landmark.Common.addmap);
                        
                        if ((shape == 'rectangle' || shape == 'square')) {
                            if (coordinates.length == 4) { // rectangle or square needs 5 points to connect
                                addMapCoordinates = coordinates;
                            } else {
                                console.log('Rectangle landmarks require 2 points');
                            }
                        } else if (shape == 'polygon') { // polygon needs at least 4 points to connect (i.e triangle)
                            if (coordinates.length >= 3) {
                                addMapCoordinates = coordinates;
                            } else {                                            
                                console.log('Polygon landmarks require 3 points');
                            }
                        }
                    }

                    var polygonOptions=Array();
                    polygonOptions.type = shape;
                    if (shape == 'circle') {
                        polygonOptions.radius = radius;
                    } else if (shape == 'square') {
                        polygonOptions.radius = radius;
                        polygonOptions.points = addMapCoordinates;
                    } else {
                        polygonOptions.points = addMapCoordinates;
                    }

console.log('polygonOptions...');
console.log(polygonOptions);

                    if(results.address_components){
                        $('#landmark-street-address').html(results.address_components.address);
                        $('#landmark-city').html(results.address_components.city);
                        $('#landmark-state').val(results.address_components.state);
                        $('#landmark-zipcode').html(results.address_components.zip);
                        $('#landmark-country').html(results.address_components.country);

                        Map.addMarkerWithPolygon(
                            Landmark.Common.addmap,
                            {
                                id: 999,
                                name: results.address_components.address + ' (' + results.latitude + ' / ' + results.longitude + ')',
                                latitude: results.latitude,
                                longitude: results.longitude
                            },
                            false,
                            polygonOptions
                        );  

                        if($('#secondary-sidebar-scroll').find('li.active').attr('data-event-id')>0){
                            if(results.address_components.address){ results.address_components.address = results.address_components.address.replace(':',''); }
                            if(results.address_components.city){    results.address_components.city = results.address_components.city.replace(':',''); }
                            if(results.address_components.state){   results.address_components.state = results.address_components.state.replace(':',''); }
                            if(results.address_components.zip){     results.address_components.zip = results.address_components.zip.replace(':',''); }
                            if(results.address_components.country){ results.address_components.country = results.address_components.country.replace(':',''); }
                            var lcArray = [ results.latitude , results.longitude , results.address_components.address , results.address_components.city , results.address_components.state , results.address_components.zip , results.address_components.country ] ;
console.log('$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$');
                            Core.Ajax('landmark-click',lcArray,$('#secondary-sidebar-scroll').find('.active').attr('data-event-id'),'clicklandmark');
                            // Core.Ajax('landmark-click',results.latitude+':'+results.longitude+':'+results.address_components.address+':'+results.address_components.city+':'+results.address_components.state+':'+results.address_components.zip+':'+results.address_components.country,$('#secondary-sidebar-scroll').find('.active').attr('data-event-id'),'clicklandmark');
                            // Core.Ajax('landmark-latlngedit',results.address_components,$('#secondary-sidebar-scroll').find('li.active').attr('data-event-id'),'landmark-latlngedit');
                        }
                    }

                    Map.resetMap(Landmark.Common.addmap);
                    Map.resize(Landmark.Common.addmap);
                    Map.updateMapBound(Landmark.Common.addmap);
                    Map.updateMapBoundZoom(Landmark.Common.addmap);
                } else {
                    Core.log('Core.AddMap.LatLngEdit:No Results');
                }
            });        

        }

    },

    Environment: {

        current: function() {
            return $('body').data('environment');
        },

        context: function() {
            return $('body').data('context');
        },

        development: '759b74ce43947f5f4c91aeddc3e5bad3'

    },

    FixModal: {

        FixModal: function (eid) {
            setTimeout("Core.FixModal.FixModalGo('"+eid+"')",500);
        },

        FixModalGo: function (eid) {
console.log('FixModalGo: function ('+eid+')');

            switch(eid) {

                case     'accept-transfer' :    Core.fixFooter('modal-transfer-accept');
                                                break;

                case           'alert-add' :
                case           'add-alert' :    Core.fixFooter('modal-add-alert');
                                                break;

                case          'alert-edit' :
                case          'edit-alert' :    Core.fixFooter('modal-edit-alert');
                                                break;

                case      'batch-commands' :    Core.fixFooter('modal-batch-commands');
                                                Core.fixListGroup('modal-batch-commands');
                                                $('#batch-devices').css( 'height' , $('#batch-devices-available').height()+'px' );
                                                break;

                case        'batch-upload' :    Core.fixFooter('modal-batch-upload');
                                                Core.fixListGroup('modal-batch-upload');
                                                break;

                case        'edit-contact' :    Core.fixFooter('modal-edit-contact');
                                                break;

                case  'edit-contact-group' :    Core.fixFooter('modal-edit-contact-group');
                                                Core.fixListGroup('modal-edit-contact-group');
                                                break;

                case 'edit-landmark-group' :    Core.fixFooter('modal-edit-landmark-group');
                                                var mapHeight = $('#modal-edit-landmark-group').find('.modal-pronounce').height() ;
                                                mapHeight = mapHeight - $('#modal-edit-landmark-group').find('.modal-header').height() - 100 ;
                                                $('#modal-edit-landmark-group').find('.modal-body').css({ height: mapHeight + 'px' });
                                                mapHeight = mapHeight - 86 ;
                                                $('#edit-landmark-groups-available').closest('.transfer-list-div').css({ height: mapHeight + 'px' });
                                                $('#edit-landmark-groups-assigned').closest('.transfer-list-div').css({ height: mapHeight + 'px' });
                                                mapHeight = mapHeight - 4 ;
                                                $('#edit-landmark-groups-available').css({ height: mapHeight + 'px', 'overflow-x': 'hidden' });
                                                $('#edit-landmark-groups-assigned').css({ height: mapHeight + 'px', 'overflow-x': 'hidden' });
                                                break;

                case  'edit-vehicle-group' :    // Core.fixFooter('modal-edit-vehicle-group');
                                                var mapHeight = $('#modal-edit-vehicle-group').find('.modal-pronounce').height() ;
                                                mapHeight = mapHeight - $('#modal-edit-vehicle-group').find('.modal-header').height() - 56 ;
                                                $('#modal-edit-vehicle-group').find('.modal-body').css({ height: mapHeight + 'px' });
                                                mapHeight = mapHeight - 80 ;
                                                $('#edit-vehicle-group-user-types').closest('.selections-list-div').css({ height: mapHeight + 'px' });
                                                $('#edit-vehicle-group-users').closest('.selections-list-div').css({ height: mapHeight + 'px' });
                                                mapHeight = mapHeight - 4 ;
                                                $('#edit-vehicle-group-user-types').css({ height: mapHeight + 'px', 'overflow-x': 'hidden' });
                                                $('#edit-vehicle-group-users').css({ height: mapHeight + 'px', 'overflow-x': 'hidden' });
                                                mapHeight = mapHeight - 36 ;
                                                $('#edit-vehicle-group-devices-available').closest('.transfer-list-div').css({ height: mapHeight + 'px' });
                                                $('#edit-vehicle-group-devices-assigned').closest('.transfer-list-div').css({ height: mapHeight + 'px' });
                                                mapHeight = mapHeight - 4 ;
                                                $('#edit-vehicle-group-devices-available').css({ height: mapHeight + 'px', 'overflow-x': 'hidden' });
                                                $('#edit-vehicle-group-devices-assigned').css({ height: mapHeight + 'px', 'overflow-x': 'hidden' });
                                                break;

                case        'landmark-add' :    Core.fixFooter('modal-add-landmark');
                                                var mapHeight = $('#modal-add-landmark').find('.modal-pronounce').height() ;
                                                mapHeight = mapHeight - $('#modal-add-landmark').find('.modal-header').height() - 60 ;
                                                $('#addmap-div').closest('.modal-body').css({ height: mapHeight + 'px' });
                                                $('#addmap-div').closest('.col-md-6').css({ height: mapHeight + 'px' });
                                                $('#addmap-div').css({ height: mapHeight + 'px', width: '95%' });
                                                break;

                case    'modal-import-csv' :    Core.fixFooter('modal-import-csv');
                                                break;

                case        'landmark-fix' :    Core.fixFooter('modal-fix-landmark');
                                                var mapHeight = $('#modal-fix-landmark').find('.modal-pronounce').height() ;
                                                mapHeight = mapHeight - $('#modal-fix-landmark').find('.modal-header').height() - 60 ;
                                                $('#addmap-div').closest('.modal-body').css({ height: mapHeight + 'px' });
                                                $('#addmap-div').closest('.col-md-6').css({ height: mapHeight + 'px' });
                                                $('#addmap-div').css({ height: mapHeight + 'px', width: '95%' });
                                                break;

                case 'modal-map-container' :    var mapHeight = $('#modal-map-container').find('.modal-pronounce').height() ;
                                                mapHeight = mapHeight - $('#modal-map-container').find('.modal-header').height() - $('#modal-map-container').find('.modal-footer').height() -60;
                                                $('#modal-map-hook').closest('.modal-body').css({ height: mapHeight + 'px' });
                                                mapHeight = mapHeight - 30;
                                                $('#modal-map-hook').css({ height: mapHeight + 'px', width: '100%' });
                                                break;

                case            'user-add' :    Core.fixFooter('modal-add-user');
                                                Core.fixListGroup('modal-add-user');
                                                break;

                case           'user-edit' :    Core.fixFooter('modal-edit-user');
                                                Core.fixListGroup('modal-edit-user');
                                                break;

                                   default :    console.log('FixModal : '+eid);
                                                Core.fixFooter(eid);

            }

        }

    },

    fixListGroup: function (eid) {
console.log('fixListGroup: function ('+eid+')');
        if($('#'+eid).is(':visible')){
            var hHeight=$('#'+eid).find('.modal-header').outerHeight(true);
            var fHeight=$('#'+eid).find('.modal-footer').outerHeight(true);
            var bHeight=$('#'+eid).find('.modal-body').outerHeight(true);
            var bTop=Math.floor($('#'+eid).find('.modal-body').offset().top);
            var eTop=0;
            var eHeight=0;
            $('#'+eid).find('.list-group').each(function() {
                $(this).closest('div').css({ height: 'auto' }) ;
                eTop=Math.floor($(this).closest('div').offset().top);
                eHeight = bTop + bHeight - eTop - 8;
                if((eTop>0)&&(eHeight>0)){
                    $(this).closest('div').css({ height: eHeight+'px' });
                    console.log('id:'+$(this).attr('id')+', hHeight:'+hHeight+', bTop:'+bTop+', bHeight:'+bHeight+', eTop:'+eTop+', eHeight:'+eHeight);
                }
            });
        }
    },

    fixFooter: function (eid) {
console.log('fixFooter: function ('+eid+')');
        if($('#'+eid).is(':visible')){
            $('#'+eid).find('.modal-body').css({ height: 'auto' }) ;
            var cTop = Math.floor($('#'+eid).find('.modal-content').offset().top) ;
            var fTop = Math.floor($('#'+eid).find('.modal-footer').offset().top) ;
            var bTop = Math.floor($('#'+eid).find('.modal-body').offset().top) ;
            var cHeight = $('#'+eid).find('.modal-content').height() ;
            var hHeight = $('#'+eid).find('.modal-header').outerHeight(true) ;
            var fHeight = $('#'+eid).find('.modal-footer').outerHeight(true) ;
            var bHeight = Math.floor(fTop - bTop);
            $('#'+eid).find('.modal-body').css({ height: bHeight+'px' }) ;
            var nHeight = hHeight + bHeight + fHeight + 16;
            if (nHeight<cHeight) {
                bHeight = bHeight + cHeight - nHeight - 8;
                $('#'+eid).find('.modal-body').css({ height: bHeight+'px' }) ;
            } else if (nHeight>cHeight) {
                cHeight = cHeight + nHeight - cHeight ;
                $('#'+eid).find('.modal-content').css({ height: cHeight+'px' }) ;
            }
            console.log('nHeight:'+nHeight+', cHeight:'+cHeight);
        }
    },

    MapAutoRefreshToggle: function (offSwitch) {

        if(offSwitch){

            mapAutoRefreshMode=0;

        } else {

            mapAutoRefreshMode++;

            switch(mapAutoRefreshMode){

                case           1  : 
                case          '1' : Core.MapAutoRefreshCounter(60);
                                    break;

            }

        }

        // $('span.navbar-username').text('MapAutoRefreshToggle: '+mapAutoRefreshMode);

    },

    MapAutoRefreshCounter: function (countDown) {

            countDown--;

            // $('span.navbar-account').text('MapAutoRefreshCounter: '+countDown);

            switch(countDown){

                case           1  :
                case          '1' : if(!(mapAutoRefresh)){
                                        mapAutoRefresh=1;
                                        mapAutoRefreshMode=0;
                                        $('#refresh-map-markers').trigger('click');
                                        mapAutoRefresh=0;
                                    }
                                    break;

                          default : window.setTimeout("Core.MapAutoRefreshCounter('"+countDown+"')",1000);

            }

    },

    SaveList: function (lid) {
console.log('Core.SaveList:'+lid);

        if(typeof lid == "string") {

            var lids = [];
            var uid='';
            var skip='';

            switch(lid){

                case          'edit-landmark-groups-assigned' : uid = $('#edit-landmark-group-title-edit').attr('data-uid');
                                                                break;

                case         'edit-landmark-groups-available' : uid = '';
                                                                break;

                case    'edit-vehicle-group-devices-assigned' : uid = $('#transfer-vehicle-group-devices-group-to').val();
                                                                if(!(uid)){
                                                                    alert('Transfer Group Missing');
                                                                    skip=1;
                                                                }
                                                                break;

                case   'edit-vehicle-group-devices-available' : uid = $('#transfer-vehicle-group-devices-group-from').val();
                                                                uid = $('#edit-vehicle-group-title-edit').data('id');
                                                                if(!(uid)){
                                                                    alert('Return Group Missing');
                                                                    skip=1;
                                                                }
                                                                break;

                case             'user-edit-devices-assigned' : uid = $('#transfer-group-to').val();
                                                                if(!(uid)){
                                                                    alert('Transfer Group Missing');
                                                                    skip=1;
                                                                }
                                                                break;

                case            'user-edit-devices-available' : uid = $('#transfer-group-from').val();
                                                                if(!(uid)){
                                                                    alert('Return Group Missing');
                                                                    skip=1;
                                                                }
                                                                break;

                                                      default : uid = $('#'+lid).attr('data-uid');

            }
        
            if(!(skip)){
                $('#'+lid).find( "li" ).each(function( index ) {
                    lids.push($(this).attr('id'));
                });
                Core.Ajax($('#'+lid).attr('id'),lids,uid,'savelist');
            }

        }
    },

    SortList: function (lid,reverse) {
        
        if(typeof lid == "string") {
        
            lid = document.getElementById(lid);
            var lis = lid.getElementsByTagName("LI");
            var vals = [];
            var lids = [];
            for(var i = 0, l = lis.length; i < l; i++) {
                vals.push(lis[i].innerHTML);
                lids[lis[i].innerHTML]=lis[i].id;
            }
            vals.sort();
            if(reverse) {
                vals.reverse();
            }
            for(var i = 0, l = lis.length; i < l; i++) {
                lis[i].id = lids[vals[i]];
                lis[i].innerHTML = vals[i];    
            }

        }

    },

    Wizard: {

        Polygon: function(me) {

console.log('Core.Wizard.Polygon:'+me);

            pointsUpdate = [];
            var buffer='';
            var lat='';
            var lng='';

            for (var i=0; i < me._tempMarkerArray.length; i++) {

                buffer = me._tempMarkerArray[i].getLatLng();
console.log(buffer);
                // lat=Math.round(buffer.lat * 10000) / 10000;
                // lng=Math.round(buffer.lng * 10000) / 10000;
                pointsUpdate.push(buffer.lat+' '+buffer.lng); 

                if(i==0){
                    var a = { latitude: buffer.lat, longitude: buffer.lng };
                }

            }

            if($('#modal-add-landmark').is(':visible')){

                var map = Landmark.Common.addmap;

                // if (Map.doesTempLandmarkExist(map)) {
                //     Map.removeTempLandmark(map);
                // }

                Core.AddMap.LatLngEdit(a);

            } else if(currentLandmarkId){

                Core.EditMap.LatLngEdit(a);

                Core.Ajax('landmark-polygon',pointsUpdate,currentLandmarkId,'landmark-polygon');

            }

        },

        LogoutCounter: function(x) {
            if((x)&&(logoutCounter<5)){
                logoutCounter=5;
            } else {
                if(logoutCounter<1){
                    // alert('LogoutCounter');
                    // window.location.reload(1);
                } else {
                    logoutCounter--;
                    setTimeout("Core.Wizard.LogoutCounter()", 180000);
                }
            }
        },

        Delete: function (eid,dbid,v1,v2) {
console.log('################################# Core.Wizard.Delete:eid:'+eid+':dbid:'+dbid+':v1:'+v1+':v2:'+v2);
            refreshOnChange=eid;
            Core.Ajax(eid,v1,v2,'dbdelete',dbid);
        },

        DeleteRecord: function (eid,uid,type) {
console.log('################################# Core.Wizard.Delete:eid:'+eid+':'+uid);
            refreshOnChange=eid;
            if(confirm('Are You Sure?')){
                Core.Ajax(eid,uid,uid,type);
            }
        },

        DeSelect: function (eid,cnt,x) {
            deSelectCnt--;
            if(cnt){
                deSelectCnt=5;
            }
            if((deSelectCnt>0)&&((cnt)||(x))){
                setTimeout("Core.Wizard.DeSelect('"+eid+"','',1)",1000);
            } else if (eid!=dontDeSelect){
    console.log('Core.Wizard.DeSelect:'+eid);
                currentLink2Input='';
                currentLink2Select='';

                $('#'+eid).slideUp(300);
                $('#'+eid).closest('.dropdown-backdrop').remove();

                skipRefresh='';
            }
        },

        Input2Link: function (chk,val,eid,dbid,key) {

console.log('Input2Link: function (chk='+chk+',val='+val+',eid='+eid+',dbid='+dbid+',key='+key+');');

console.log('........................................................................................................... Core.Wizard.Input2Link:tabSkip'+tabSkip);


            if(tabSkip){

console.log('........................................................................................................... Core.Wizard.Input2Link:tabSkip'+tabSkip);

            } else {

console.log('Core.Wizard.Input2Link:'+chk+':'+val+':'+eid+':'+dbid+':'+key);

                val = val.trim();

                var html=val;

                if(!(html)){
                    // html='<span class="wizard-nodata" title="please click to enter data...">&oplus;</span>';
                }

                if (!(dbid)) {

                    var sss = $('#secondary-sidebar-scroll');
                    var uid = '';

                    if(sss){
                        if(sss.find('li.active').attr('id')){
                            uid = sss.find('li.active').attr('id').split('-').pop();
                        }
                    }

                    switch(eid){
                        case               'my-account-email' :
                        case          'my-account-first-name' :
                        case           'my-account-last-name' :
                        case           'my-account-user-name' : uid=eid;
                                                                break;
                        case               'customer-address' :
                        case                  'customer-city' :
                        case                 'customer-email' :
                        case            'customer-first-name' :
                        case          'customer-home-phone' :
                        case             'customer-last-name' :
                        case          'customer-mobile-phone' :
                        case                 'customer-state' :
                        case               'customer-zipcode' :
                        case                    'device-plan' :
                        case         'device-activation-date' :
                        case       'device-deactivation-date' :
                        case            'device-last-renewed' :
                        case           'device-purchase-date' :
                        case            'device-renewal-date' :
                        case                  'device-serial' :
                        case                  'device-status' :
                        case                  'vehicle-color' :
                        case                  'vehicle-group' :
                        case              'vehicle-installer' :
                        case           'vehicle-install-date' :
                        case        'vehicle-install-mileage' :
                        case          'vehicle-license-plate' :
                        case                'vehicle-loan-id' :
                        case                   'vehicle-make' :
                        case                  'vehicle-model' :
                        case                   'vehicle-name' :
                        case                 'vehicle-serial' :
                        case                 'vehicle-status' :
                        case                  'vehicle-stock' :
                        case                    'vehicle-vin' :
                        case                   'vehicle-year' : if(!(uid)){
                                                                  uid=currentUnitId;
                                                                }
                                                                break;

                        case                  'landmark-city' :
                        case        'landmark-street-address' :
                        case               'landmark-zipcode' : $('#'+eid).val(val)
                                                                $('#'+eid).next('div').remove();
                                                                $('#'+eid).html(html);
                                                                $('#'+eid).show();
                                                                eid = '';
                                                                Core.EditMap.Address(3);
                                                                break;

                        case              'landmark-latitude' :
                        case             'landmark-longitude' :
                        case                  'landmark-name' : if(!(uid)){
                                                                  uid=currentLandmarkId;
                                                                }
                                                                break;
                                                                
                    }

                    if(eid){

    console.log('Core.Wizard.Input2Link:eid:'+eid);

                        $('#'+eid).next('div').remove();
                        $('#'+eid).html(html);
                        $('#'+eid).addClass('wizard-pending');
                        $('#'+eid).attr('title','Save Request Pending...');
                        $('#'+eid).show();

                        if(uid){
    console.log('Core.Wizard.Input2Link:uid:'+uid);
                            console.log('Core.Wizard.Input2Link:'+uid+':'+eid+'="'+val+'"');

                            if(eid=='vehicle-name'){
                                if($('#info_window_unit_name').is(':visible')){
                                    $('#info_window_unit_name').html('<i class="gi gi-car"></i>&nbsp;&nbsp;<b>'+val+'</b>');
                                }
                            }

                            if(val!=chk){
                                Core.Ajax(eid,val,uid,'update');
                            } else {
    console.log('++++++++++++++++++++++++++++++++ Core.Wizard.Input2Link:uid:'+uid);
                                $('#'+eid).removeClass('wizard-pending');
                                $('#'+eid).attr('title','');
                                Core.Editable.setValue($('#'+eid),chk);
                            }
                        }

                    }

                } else {

                    $('#'+eid).next('div').remove();
                    $('#'+eid).html(html);
                    $('#'+eid).addClass('wizard-pending');
                    $('#'+eid).attr('title','Save Request Pending...');
                    $('#'+eid).show();

                    if(val!=chk){

                        switch(dbid){
                        
                            case                     'crossbones-alert-alertname' :
                            case                 'crossbones-contact-cellcarrier' :
                            case                  'crossbones-contact-cellnumber' :
                            case                       'crossbones-contact-email' :
                            case                   'crossbones-contact-firstname' :
                            case                    'crossbones-contact-lastname' :
                            case                    'crossbones-contact-username' :
                            case       'crossbones-contactgroup-contactgroupname' :
                            case                 'crossbones-unitattribute-color' :
                            case    'crossbones-unitattribute-licenseplatenumber' :
                            case             'crossbones-territory-territoryname' :
                            case   'crossbones-territorygroup-territorygroupname' :
                            case            'crossbones-unitattribute-loannumber' :
                            case                  'crossbones-unitattribute-make' :
                            case                 'crossbones-unitattribute-model' :
                            case                   'crossbones-unitattribute-vin' :
                            case                  'crossbones-unitattribute-year' :
                            case                       'crossbones-unit-unitname' :
                            case             'crossbones-unitgroup-unitgroupname' :
                            case                          'crossbones-user-email' :
                            case                      'crossbones-user-firstname' :
                            case                       'crossbones-user-lastname' :
                            case                       'crossbones-user-username' :
                            case               'crossbones-usertype-usertypename' : Core.Ajax(eid,val,key,'recupdate',dbid);
                                                                                    break;
                                                                          
                                                                          default : Core.Ajax(eid,val,key,'dbupdate',dbid);
                        
                        }

                    } else {
                        
                        $('#'+eid).removeClass('wizard-pending');
                        $('#'+eid).attr('title','');
                                                                
                    }

                }

                wizardL2I='';

            }

        },

        SaveChange: function (eid) {
            var $ele = $('#'+eid);
            var key = $ele.attr('data-uid');
            var val = $ele.val();
console.log('Input2Save:'+eid+':'+key+':'+val);
            $ele.addClass('wizard-pending');
            Core.Ajax(eid,val,key,'savechange');
        },

        Jump: function (eid) {
console.log('Core.Wizard.Jump:'+eid);
            switch(eid){
                case            'device-status' :   $('#btn-info-vehicle').trigger('click');
                                                    // $('#vehicle-status').trigger('click');
                                                    break;
            }
            return false;
        },

        Link2Input: function (eid,val,dbid,key,roc) {

            var hallPass='';

            switch(eid){

                case        'landmark-street-address' :
                case                  'landmark-city' :
                case                 'landmark-state' :
                case               'landmark-zipcode' :
                case              'landmark-latitude' :
                case             'landmark-longitude' : if($('#landmark-method2').val() == 'manual-entry'){
                                                            hallPass=1;
                                                        }
                                                        break;

                                              default : hallPass=1;

            }

console.log('Core.Wizard.Link2Input:eid="'+eid+'", currentLink2Select="'+currentLink2Select+'", hallPass="'+hallPass+'"');

            if(!(hallPass)){
                currentLink2Input=eid;
                setTimeout("currentLink2Input=''",1000);
console.log('Core.Wizard.Link2Input:eid="'+eid+'", currentLink2Select="'+currentLink2Select+'", hallPass="'+hallPass+'"", currentLink2Input="'+currentLink2Input+'"');
            } else if((eid)&&(eid!=currentLink2Input)){
console.log('Core.Wizard.Link2Input:'+eid+'!='+currentLink2Input);
                currentLink2Input=eid;
                setTimeout("currentLink2Input=''",1000);

                skipRefresh=1;

                if(roc){
                    refreshOnChange=eid;                
                }

                switch(val){ 
                    case '<span class="wizard-nodata" title="please click to enter data...">&oplus;</span>' :
                    case                                                                          '&oplus;' :
                    case                                                                                '⊕' : val='';
                                                                                                              break;
                }

                switch(eid){

                    case               'customer-address' : if(val=='Address'){val='';}
                                                            break;
                    case                  'customer-city' : if(val=='City'){val='';}
                                                            break;
                    case                 'customer-email' : if(val=='Email'){val='';}
                                                            break;
                    case            'customer-first-name' : if(val=='First Name'){val='';}
                                                            break;
                    case            'customer-home-phone' : if(val=='Home Phone'){val='';}
                                                            break;
                    case             'customer-last-name' : if(val=='Last Name'){val='';}
                                                            break;
                    case          'customer-mobile-phone' : if(val=='Mobile Phone'){val='';}
                                                            break;
                    case                 'customer-state' : if(val=='State'){val='';}
                                                            break;
                    case               'customer-zipcode' : if(val=='Zip'){val='';}
                                                            break;
                    case                    'device-plan' : if(val=='Plan'){val='';}
                                                            break;
                    case         'device-activation-date' : if(val=='Activation Date'){val='';}
                                                            break;
                    case       'device-deactivation-date' : if(val=='Deactivation Date'){val='';}
                                                            break;
                    case            'device-last-renewed' : if(val=='Last Renewed On'){val='';}
                                                            break;
                    case           'device-purchase-date' : if(val=='Purchase Date'){val='';}
                                                            break;
                    case            'device-renewal-date' : if(val=='Renewal Date'){val='';}
                                                            break;
                    case                  'device-serial' : if(val=='Serial'){val='';}
                                                            break;
                    case                  'device-status' : if(val=='Status'){val='';}
                                                            break;
                    case                  'vehicle-color' : if(val=='Color'){val='';}
                                                            break;
                    case              'vehicle-installer' : if(val=='Installer'){val='';}
                                                            break;
                    case           'vehicle-install-date' : if(val=='Install Date'){val='';}
                                                            break;
                    case        'vehicle-install-mileage' : if(val=='Install Mileage'){val='';}
                                                            break;
                    case          'vehicle-license-plate' : if(val=='Lic Plate'){val='';}
                                                            break;
                    case                'vehicle-loan-id' : if(val=='Loan ID'){val='';}
                                                            break;
                    case                   'vehicle-make' : if(val=='Make'){val='';}
                                                            break;
                    case                  'vehicle-model' : if(val=='Model'){val='';}
                                                            break;
                    case                   'vehicle-name' : if(val=='Vehicle Name'){val='';}
                                                            break;
                    case                 'vehicle-serial' : if(val=='Serial'){val='';}
                                                            break;
                    case                  'vehicle-stock' : if(val=='Stock'){val='';}
                                                            break;
                    case                    'vehicle-vin' : if(val=='Vin'){val='';}
                                                            break;
                    case                   'vehicle-year' : if(val=='Year'){val='';}
                                                            break;
                                                  default : if(val=='No Data'){val='';}
                                                            // value = '[No Data]' ;

                }


    console.log('Core.Wizard.Link2Input:'+eid+':'+val+':'+dbid+':'+key);

                if ( (!(dbid)) || (dbid=='undefined') ) {

                    switch(eid){

                                                            //
                                                            // FORM-DRIVEN KEYSTROKE-EDITABLE DATA FIELDS (i.e. "Not Report-generated data")
                                                            //
                                                            // *** EXCEPTIONS TO THE RULE ***
                                                            //
                        case           'my-account-email' :
                        case      'my-account-first-name' :
                        case       'my-account-last-name' :
                        // case        'my-account-username' :
                        case        'customer-first-name' :
                        case         'customer-last-name' :
                        case           'customer-address' :
                        case              'customer-city' :
                        case             'customer-state' :
                        case           'customer-zipcode' :
                        case      'customer-mobile-phone' :
                        case        'customer-home-phone' :
                        case             'customer-email' :
                    case                    'device-plan' :
                    case         'device-activation-date' :
                    case       'device-deactivation-date' :
                    case            'device-last-renewed' :
                    case           'device-purchase-date' :
                    case            'device-renewal-date' :
                    case                  'device-serial' :
                    case                  'device-status' :
                        case              'landmark-city' :
                        case          'landmark-latitude' :
                        case         'landmark-longitude' :
                        case              'landmark-name' :
                        case    'landmark-street-address' :
                        case           'landmark-zipcode' :
                        case              'vehicle-color' :
                        case              'vehicle-group' :
                        case       'vehicle-install-date' :
                        case          'vehicle-installer' :
                        case            'vehicle-loan-id' :
                        case               'vehicle-make' :
                        case    'vehicle-install-mileage' :
                        case              'vehicle-model' :
                        case               'vehicle-name' :
                        case      'vehicle-license-plate' :
                        case             'vehicle-status' :
                        case              'vehicle-stock' :
                        case                'vehicle-vin' : 
                        case               'vehicle-year' : break; 

                                                            //
                                                            // REPORT-DRIVEN KEYSTROKE-EDITABLE DATA FIELDS (i.e. "Not 'create a new user/device/etc' form-driven data")
                                                            //
                                                  default : console.log('Core.Wizard.Link2Input:'+eid+':'+val);
                                                            eid='';

                    }

console.log('Core.Wizard.Link2Input:'+eid+'!='+wizardL2I);
                    if((eid)&&(eid!=wizardL2I)){
                        wizardL2I=eid;
                        $('#'+eid).hide();
                        $('<div class="form-group wizard-div wizard-width"></div>').insertAfter('#'+eid).append('<input class="wizard-input wizard-width" type="text" id="wizard-input-'+eid+'" name="wizard-input-'+eid+'" onblur="Core.Wizard.Input2Link(\''+val.replace("'","")+'\',this.value,\''+eid+'\');" value="'+val.replace("'","")+'" />');
                        tabSkip=1; $('#wizard-input-'+eid).focus().val("").val(val); setTimeout("tabSkip=''",1);
                    }

                } else {

console.log('Core.Wizard.Link2Input:'+eid+'!='+wizardL2I);
                    if((eid)&&(eid!=wizardL2I)){
                        wizardL2I=eid;
                        $('#'+eid).hide();
                        $('<div class="form-group wizard-div wizard-width"></div>').insertAfter('#'+eid).append('<input class="wizard-input wizard-width" type="text" id="wizard-input-'+eid+'" name="wizard-input-'+eid+'" onblur="Core.Wizard.Input2Link(\''+val.replace("'","")+'\',this.value,\''+eid+'\',\''+dbid+'\',\''+key+'\');" value="'+val.replace("'","")+'" />');
                        tabSkip=1; $('#wizard-input-'+eid).focus().val("").val(val); setTimeout("tabSkip=''",1);
                    }

                }

            }
console.log('Core.Wizard.Link2Input:eid="'+eid+'", currentLink2Select="'+currentLink2Select+'", hallPass="'+hallPass+'", DONE');

        },

        Link2Select: function (eid,roc) {
console.log('Core.Wizard.Link2Select:eid="'+eid+'", currentLink2Select="'+currentLink2Select+'"');

            if(eid!=currentLink2Select){

                currentLink2Select = eid;
                setTimeout("currentLink2Select=''",1000);

                skipRefresh=1;
                
                if(roc){
                    refreshOnChange=eid;                
                }

                var sss = $('#secondary-sidebar-scroll');
                var uid = '';

                if(sss){
                    if(sss.find('li.active').attr('id')){
                        uid = sss.find('li.active').attr('id').split('-').pop();                    
                    }
                }

                switch (eid) {
                    
                    case     'landmark-add-category' :  Core.Ajax(eid,'','','options-landmarkcategory');
                                                        uid='';
                                                        break;
                    
                case 'crossbones-territory-country' :
                    case      'landmark-add-country' :  Core.Ajax(eid,'','','options-country');
                                                        uid='';
                                                        break;
                    
                    case        'landmark-add-group' :  Core.Ajax(eid,'','','options-landmarkgroup');
                                                        uid='';
                                                        break;
                    
                    case       'landmark-add-method' :  Core.Ajax(eid,'','','options-landmarkmethod');
                                                        uid='';
                                                        break;
                    
                  case 'crossbones-territory-radius' :
                    case       'landmark-add-radius' :  Core.Ajax(eid,'','','options-landmarkradius');
                                                        uid='';
                                                        break;
                    
                   case 'crossbones-territory-shape' :
                    case        'landmark-add-shape' :  Core.Ajax(eid,'','','options-landmarkshape');
                                                        uid='';
                                                        break;
                    
                   case 'crossbones-territory-state' :
                    case        'landmark-add-state' :  Core.Ajax(eid,'','','options-state');
                                                        uid='';
                                                        break;
                    
                   case 'crossbones-contact-carrier' :
                    case          'user-add-carrier' :
                    case       'user-update-carrier' :  Core.Ajax(eid,'','','options-carrier');
                                                        uid='';
                                                        break;

                    case        'user-add-user-type' :
                    case     'user-update-user-type' :  Core.Ajax(eid,'','','options-usertype');
                                                        uid='';
                                                        break;

                                             default :  console.log('Core.Wizard.Link2Select:hasClass:'+uid+':'+eid);
                                                        //
                                                        // DROPDOWN+SELECT EDITABLE DATA
                                                        //
                                                        var select='';
                                                        if($('#'+eid).hasClass('alertcontactcontact')){
                                                            select = 'alertcontactcontact';
                                                        }else if($('#'+eid).hasClass('alertcontactgroup')){
                                                            select = 'alertcontactgroup';
                                                        }else if($('#'+eid).hasClass('alertcontactmode')){
                                                            select = 'alertcontactmode';
                                                        }else if($('#'+eid).hasClass('alertunitunit')){
                                                            select = 'alertunitunit';
                                                        }else if($('#'+eid).hasClass('alertunitgroup')){
                                                            select = 'alertunitgroup';
                                                        }else if($('#'+eid).hasClass('alertunitmode')){
                                                            select = 'alertunitmode';
                                                        }else if($('#'+eid).hasClass('alerttype')){
                                                            select = 'alerttype';
                                                        }else if($('#'+eid).hasClass('alertunitgroup')){
                                                            select = 'alertunitgroup';
                                                        }else if($('#'+eid).hasClass('carrier')){
                                                            select = 'carrier';
                                                        }else if($('#'+eid).hasClass('cellcarrier')){
                                                            select = 'cellcarrier';
                                                        }else if($('#'+eid).hasClass('contact')){
                                                            select = 'contact';
                                                        }else if($('#'+eid).hasClass('contactgroup')){
                                                            select = 'contactgroup';
                                                        }else if($('#'+eid).hasClass('contactmethod')){
                                                            select = 'contactmethod';
                                                        }else if($('#'+eid).hasClass('contactmode')){
                                                            select = 'contactmode';
                                                        }else if($('#'+eid).hasClass('contactstatus')){
                                                            select = 'contactstatus';
                                                        }else if($('#'+eid).hasClass('country')){
                                                            select = 'country';
                                                        }else if($('#'+eid).hasClass('days')){
                                                            select = 'days';
                                                        }else if($('#'+eid).hasClass('duration')){
                                                            select = 'duration';
                                                        }else if($('#'+eid).hasClass('hours')){
                                                            select = 'hours';
                                                        }else if($('#'+eid).hasClass('gateway')){
                                                            select = 'gateway';
                                                        }else if($('#'+eid).hasClass('landmark')){
                                                            select = 'landmark';
                                                        }else if($('#'+eid).hasClass('landmarkmode')){
                                                            select = 'landmarkmode';
                                                        }else if($('#'+eid).hasClass('landmarktrigger')){
                                                            select = 'landmarktrigger';
                                                        }else if($('#'+eid).hasClass('overspeed')){
                                                            select = 'overspeed';
                                                        }else if($('#'+eid).hasClass('permissioncategory')){
                                                            select = 'permissioncategory';
                                                        }else if($('#'+eid).hasClass('radius')){
                                                            select = 'radius';
                                                        }else if($('#'+eid).hasClass('shape')){
                                                            select = 'shape';
                                                        }else if($('#'+eid).hasClass('state')){
                                                            select = 'state';
                                                        }else if($('#'+eid).hasClass('territorycategory')){
                                                            select = 'territorycategory';
                                                        }else if($('#'+eid).hasClass('territorygroup')){
                                                            select = 'territorygroup';
                                                        }else if($('#'+eid).hasClass('unit_id')){
                                                            select = 'unit_id';
                                                        }else if($('#'+eid).hasClass('unitgroupname')){
                                                            select = 'unitgroup';
                                                        }else if($('#'+eid).hasClass('unitgroup')){
                                                            select = 'unitgroup';
                                                        }else if($('#'+eid).hasClass('unitstatus')){
                                                            select = 'unitstatus';
                                                        }else if($('#'+eid).hasClass('usertype')){
                                                            select = 'usertype';
                                                        }else if($('#'+eid).hasClass('vehiclemode')){
                                                            select = 'vehiclemode';
                                                        }else if($('#'+eid).hasClass('vehicle')){
                                                            select = 'vehicle';
                                                        }
                                                        if(select){
                                                            Core.Ajax(eid,'',eid.split('-').pop(),'options-'+select);
                                                            uid='';
                                                        }
                                                        
                }

                if(uid){
                    console.log('Core.Wizard.Link2Select:'+uid+':'+eid);
                    Core.Ajax(eid,'',uid,'options');
                }

            }

        },

        Option2Link: function (eid,val,html) {
console.log('Core.Wizard.Option2Link:eid="'+eid+'", val="'+val+'", html="'+html+'"');

            Core.Wizard.DeSelect('ul-'+eid);
                                                            
            var sss = $('#secondary-sidebar-scroll');
            var uid = '';

            if(sss){
                if(sss.find('li.active').attr('id')){
                    uid = sss.find('li.active').attr('id').split('-').pop();
                }
            }

            if(eid){

                $('#'+eid).next('div').remove();
                $('#'+eid).text(html);
                $('#'+eid).addClass('wizard-pending');
                $('#'+eid).attr('value',val);
                $('#'+eid).attr('title','Save Request Pending...');
                $('#'+eid).show();

                switch(eid){

                    case       'landmark-add-country' :
                    case        'landmark-add-radius' :
                    case         'landmark-add-shape' :
                    case         'landmark-add-state' : $('#'+eid).removeClass('wizard-pending');
                                                        $('#'+eid).attr('title','');
                                                        $('#'+eid).val(val);
                                                        uid='';
                                                        Core.AddMap.Address(1);
                                                        break;

                    case          'alert-add-contact' : $('#'+eid).removeClass('wizard-pending');
                                                        $('#'+eid).attr('title','');
                                                        // $('#'+eid).val(val);
                                                        uid='';
                                                        break;

                    case    'alert-add-contactmethod' :
                    case             'alert-add-days' :
                    case         'alert-add-duration' :
                    case            'alert-add-hours' :
                    case         'alert-add-landmark' :
                    case  'alert-add-landmarktrigger' :
                    case        'alert-add-overspeed' :
                    case          'alert-add-vehicle' :
                    case          'contact-add-group' :
                    case        'contact-add-carrier' :
                    case        'landmark-add-method' :
                    case      'landmark-add-category' :
                    case         'landmark-add-group' :
                    case           'user-add-carrier' :
                    case         'user-add-user-type' :
                    case        'user-update-carrier' :
                    case      'user-update-user-type' : $('#'+eid).removeClass('wizard-pending');
                                                        $('#'+eid).attr('title','');
                                                        $('#'+eid).val(val);
                                                        uid='';
                                                        break;

                    case             'alert-add-type' :
                    case      'alert-add-contactmode' :
                    case     'alert-add-landmarkmode' :
                    case      'alert-add-vehiclemode' : $('#'+eid).removeClass('wizard-pending');
                                                        $('#'+eid).attr('title','');
                                                        $('#'+eid).val(val);
                                                        uid='';
                                                        switch($('#alert-add-type').val()){
                                                            case                       2  : 
                                                            case                      '2' :
                                                            case                       6  : 
                                                            case                      '6' : $('#alert-add-duration').closest('div .row').show();
                                                                                            $('#alert-add-overspeed').closest('div .row').hide();
                                                                                            $('#alert-add-landmarkmode').closest('div .row').hide();
                                                                                            $('#alert-add-landmarktrigger').closest('div .row').hide();
                                                                                            break;
                                                            case                       3  : 
                                                            case                      '3' : $('#alert-add-duration').closest('div .row').hide();
                                                                                            $('#alert-add-overspeed').closest('div .row').hide();
                                                                                            $('#alert-add-landmarkmode').closest('div .row').show();
                                                                                            $('#alert-add-landmarktrigger').closest('div .row').show();
                                                                                            break;
                                                            case                       7  : 
                                                            case                      '7' : $('#alert-add-duration').closest('div .row').hide();
                                                                                            $('#alert-add-overspeed').closest('div .row').show();
                                                                                            $('#alert-add-landmarkmode').closest('div .row').hide();
                                                                                            $('#alert-add-landmarktrigger').closest('div .row').hide();
                                                                                            break;
                                                                                  default : $('#alert-add-duration').closest('div .row').hide();
                                                                                            $('#alert-add-overspeed').closest('div .row').hide();
                                                                                            $('#alert-add-landmarkmode').closest('div .row').hide();
                                                                                            $('#alert-add-landmarktrigger').closest('div .row').hide();
                                                                                            break;
                                                        }
                                                        if($('#alert-add-contactmode').val()=='2'){
                                                            $('#alert-add-contact').closest('div .row').hide();
                                                            $('#alert-add-contactgroup').closest('div .row').show();
                                                            $('#alert-add-contactmethod').closest('div .row').hide();
                                                        } else if($('#alert-add-contactmode').val()=='1'){
                                                            $('#alert-add-contactgroup').closest('div .row').hide();
                                                            $('#alert-add-contact').closest('div .row').show();
                                                            $('#alert-add-contactmethod').closest('div .row').show();
                                                        } else {
                                                            $('#alert-add-contact').closest('div .row').hide();
                                                            $('#alert-add-contactgroup').closest('div .row').hide();
                                                            $('#alert-add-contactmethod').closest('div .row').hide();
                                                        }
                                                        if($('#alert-add-landmarkmode').val()=='2'){
                                                            $('#alert-add-landmark').closest('div .row').hide();
                                                            $('#alert-add-landmarkgroup').closest('div .row').show();
                                                        } else if($('#alert-add-landmarkmode').val()=='1'){
                                                            $('#alert-add-landmarkgroup').closest('div .row').hide();
                                                            $('#alert-add-landmark').closest('div .row').show();
                                                        } else {
                                                            $('#alert-add-landmark').closest('div .row').hide();
                                                            $('#alert-add-landmarkgroup').closest('div .row').hide();
                                                        }
                                                        if($('#alert-add-vehiclemode').val()=='2'){
                                                            $('#alert-add-vehicle').closest('div .row').hide();
                                                            $('#alert-add-vehiclegroup').closest('div .row').show();
                                                        } else if($('#alert-add-vehiclemode').val()=='1'){
                                                            $('#alert-add-vehiclegroup').closest('div .row').hide();
                                                            $('#alert-add-vehicle').closest('div .row').show();
                                                        } else {
                                                            $('#alert-add-vehicle').closest('div .row').hide();
                                                            $('#alert-add-vehiclegroup').closest('div .row').hide();
                                                        }
                                                        break;

                                             default :  console.log('Core.Wizard.Option2Link:hasClass:????????????????????????'+eid+'="'+val+'"');
                                                        //
                                                        // DROPDOWN+SELECT EDITABLE DATA
                                                        //
                                                        var select='';
                                                        if(eid=='vehicle-status'){
console.log('>>>>>>>>>>>>>>>>>>> Core.Ajax('+eid+','+val+','+currentUnitId+',update-unitstatus');
                                                            Core.Ajax(eid,val,uid,'update-vehiclestatus');
                                                            uid='';
                                                        }else if($('#'+eid).hasClass('crossbones-territory-country')){
                                                            select = 'crossbones-territory-country';
                                                        }else if($('#'+eid).hasClass('crossbones-territory-group')){
                                                            select = 'crossbones-territory-group';
                                                        }else if($('#'+eid).hasClass('crossbones-territory-radius')){
                                                            select = 'crossbones-territory-radius';
                                                        }else if($('#'+eid).hasClass('crossbones-territory-shape')){
                                                            select = 'crossbones-territory-shape';
                                                        }else if($('#'+eid).hasClass('crossbones-territory-state')){
                                                            select = 'crossbones-territory-state';
                                                        }else if($('#'+eid).hasClass('crossbones-territory-territorycategory')){
                                                            select = 'crossbones-territory-territorycategory';
                                                        }else if($('#'+eid).hasClass('alertcontactcontact')){
                                                            select = 'alertcontactcontact';
                                                        }else if($('#'+eid).hasClass('alertcontactgroup')){
                                                            select = 'alertcontactgroup';
                                                        }else if($('#'+eid).hasClass('alertcontactmode')){
                                                            select = 'alertcontactmode';
                                                        }else if($('#'+eid).hasClass('alertunitunit')){
                                                            select = 'alertunitunit';
                                                        }else if($('#'+eid).hasClass('alertunitgroup')){
                                                            select = 'alertunitgroup';
                                                        }else if($('#'+eid).hasClass('alertunitmode')){
                                                            select = 'alertunitmode';
                                                        }else if($('#'+eid).hasClass('alerttype')){
                                                            select = 'alerttype';
                                                        }else if($('#'+eid).hasClass('alertunitgroup')){
                                                            select = 'alertunitgroup';
                                                        }else if($('#'+eid).hasClass('cellcarrier')){
                                                            select = 'cellcarrier';
                                                        }else if($('#'+eid).hasClass('contactgroup')){
                                                            select = 'contactgroup';
                                                        }else if($('#'+eid).hasClass('contactstatus')){
                                                            select = 'contactstatus';
                                                        }else if($('#'+eid).hasClass('country')){
                                                            select = 'country';
                                                        }else if($('#'+eid).hasClass('gateway')){
                                                            select = 'gateway';
                                                        }else if($('#'+eid).hasClass('permissioncategory')){
                                                            select = 'permissioncategory';
                                                        }else if($('#'+eid).hasClass('radius')){
                                                            select = 'radius';
                                                        }else if($('#'+eid).hasClass('shape')){
                                                            select = 'shape';
                                                        }else if($('#'+eid).hasClass('state')){
                                                            select = 'state';
                                                        }else if($('#'+eid).hasClass('territorycategory')){
                                                            select = 'territorycategory';
                                                        }else if($('#'+eid).hasClass('territorygroup')){
                                                            select = 'territorygroup';
                                                        }else if($('#'+eid).hasClass('unitgroup')){
                                                            select = 'unitgroup';
                                                        }else if($('#'+eid).hasClass('unit_id')){
                                                            select = 'unit_id';
                                                        }else if($('#'+eid).hasClass('unitgroupname')){
                                                            select = 'unitgroup';
                                                        }else if($('#'+eid).hasClass('unitstatus')){
                                                            select = 'unitstatus';
                                                        }else if($('#'+eid).hasClass('usertype')){
                                                            select = 'usertype';
                                                        }
                                                        if(select){
                                                            Core.Ajax(eid,val,eid.split('-').pop(),'update-'+select);
                                                        }

                }

                if(uid){
                    console.log('Core.Wizard.Option2Link:'+uid+':'+eid+'="'+val+'"');
                    Core.Ajax(eid,val,uid,'update');
                }

            }
            wizardL2I='';

        }

    },

    TriggerDelay: function (ele,skip) {
        if(skip){
            if(triggerDelay>1){
                triggerDelay--;
                setTimeout("Core.TriggerDelay('"+ele+"','skip')",100);
            } else {
                if(triggerDelay>0){
                    triggerDelay=-1;
                    setTimeout("$('#"+ele+"').trigger('click')",1);
                }
            }
        } else {
console.log('TriggerDelay:'+ele);
            triggerDelay = 18;
            setTimeout("Core.TriggerDelay('"+ele+"','skip')",100);
        }
    },

    Metrics: function () {
        switch(Core.Environment.context()){

            case            'vehicle/map' :
            case           'vehicle/list' : Core.Ajax('','','','metrics');
                                            break;

        }
    },

    Ajax: function (ele,val,uid,act,dbid) {

        var clusterCount=0;
        var markerOptions='';
        
        Core.Wizard.LogoutCounter(1);

        if(!(oncelerAjax)){
            oncelerAjax=1;
            setTimeout("oncelerAjax=''",10000);
console.log('Core:Ajax:ele='+ele+', uid='+uid+', val="'+val+'" (act='+act+')'+' dbid='+dbid);

            if(!(uid)){
                uid = currentUnitId;
            }

            if(act=='metrics'){
                $('#div-all-none').hide();
            }

            $.ajax({
                url: '/ajax/core/ajax',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: act,
                    dbid: dbid,
                    element: ele,
                    unit_id: uid,
                    value: val
                },
                success: function(responseData) {
                    oncelerAjax='';
    console.log('Core.Ajax:responseData:code="'+responseData.code+'":action="'+responseData.action+'":mode="'+responseData.mode+'":element="'+responseData.element+'":value="'+responseData.value+'":message="'+responseData.message+'"');

                    if (responseData.code === 86) {
                        if(!(repoKey)){
                            window.location.href = "/login";
                        }
                    } else if (responseData.code === 0) {

                        if((responseData.action)&&(responseData.mode)&&(responseData.element)) {

                            if(responseData.element){
                                switch (responseData.mode) {

                                   case            'addClass' : $('#'+responseData.element).addClass(responseData.value);
                                                                break;

                                   case          'allDevices' : console.log('------------------------------------>>>>>>>>>>>>>> '+responseData.mode+':'+responseData.value);
                                                                newZoomLevel=Map.mapZoomGet(Vehicle.Map.map)+4;
                                                                if(responseData.zoom){
                                                                    newZoomLevel=responseData.zoom;
                                                                }
                                                                var clusterLat='';
                                                                var clusterLng='';
                                                                Map.clearMarkers(Vehicle.Map.map);
                                                                $.each(responseData.value, function( key, cluster ) {
                                                                    if(cluster.count!=1){
                                                                        markerOptions = {
                                                                            id: key,
                                                                            name: cluster.count + '&nbsp;',
                                                                            latitude: cluster.latitude,
                                                                            longitude: cluster.longitude,
                                                                            eventname: 'cluster', // used in map class to get vehicle marker color
                                                                            click: function() {
                                                                                if(secondClick==key){
                                                                                    justTheseDevices=cluster.justTheseDevices;
                                                                                    $('#drill').trigger('click');
                                                                                } else {
                                                                                    secondClick=key;
                                                                                    Map.openInfoWindow(Vehicle.Map.map,'maphint',cluster.latitude,cluster.longitude,cluster);
                                                                                }
                                                                            }
                                                                        };
                                                                    } else {
                                                                        markerOptions = {
                                                                            id: key,
                                                                            name: cluster.unitname + ' ',
                                                                            latitude: cluster.latitude,
                                                                            longitude: cluster.longitude,
                                                                            eventname: 'unit', // used in map class to get vehicle marker color
                                                                            click: function() {
                                                                                $('#vehicle-li-'+cluster.unit_id).trigger('click');
                                                                            }
                                                                        };
                                                                    }
                                                                    console.log('cluster:'+key+':'+cluster.latitude+':'+cluster.longitude+':'+cluster.count);
                                                                    clusterCount++;
                                                                    clusterLat = cluster.latitude;
                                                                    clusterLng = cluster.longitude;
                                                                    Map.addMarker(Vehicle.Map.map, markerOptions, false);
                                                                });
                                                                switch(responseData.action){
                                                                    case             'all' :
                                                                    case           'drill' : Map.updateMapBound(Vehicle.Map.map);
                                                                                             Map.resize(Vehicle.Map.map);
                                                                                             break;
                                                                }
                                                                break;

                                   case      'batch-commands' : console.log('Core.Ajax:responseData.mode:checkbox');
                                                                $('#batch-devices').text('');
                                                                $('#batch-command-table').find('.dataTables-search-btn').trigger('click');
                                                                break;

                                   case        'batch-upload' : console.log('Core.Ajax:responseData.mode:checkbox');
                                                                $('#batch-queue-table').find('.dataTables-search-btn').trigger('click');
                                                                break;

                                   case            'checkbox' : console.log('Core.Ajax:responseData.mode:checkbox');
                                                                if(responseData.value){
                                                                    $('#'+responseData.element).prop('checked',true);
                                                                } else {
                                                                    $('#'+responseData.element).prop('checked',false);
                                                                }
                                                                break;

                                   case       'clicklandmark' : $('#landmark-street-address').text(responseData.value.streetaddress);
                                                                $('#landmark-city').text(responseData.value.city);
                                                                $('#landmark-state').val(responseData.value.state);
                                                                $('#landmark-zipcode').text(responseData.value.zipcode);
                                                                $('#landmark-latitude').text(responseData.value.latitude);
                                                                $('#landmark-longitude').text(responseData.value.longitude);
                                                                if($('#landmark-li-'+responseData.action).attr('id')){
                                                                    // $('#landmark-li-'+responseData.action).trigger('click');
                                                                }
                                                                break;

                                   case             'deleted' : console.log('Core:Ajax:'+responseData.element+':deleted:'+responseData.value);
                                                                console.log('refreshOnChange:'+refreshOnChange);
                                                                if(responseData.element=='landmark-delete-button'){
                                                                    lastLatVehicle='';
                                                                    lastLongVehicle='';
                                                                    Map.clearMarkers(Landmark.Common.map);
                                                                    Map.resetMap(Landmark.Common.map);
                                                                    Map.resize(Landmark.Common.map);
                                                                    $('#detail-panel').hide();
                                                                    Core.DataTable.secondarySidepanelScroll();
                                                                    Core.Viewport.adjustLayout();
                                                                } else if(refreshOnChange){
                                                                    $('#'+refreshOnChange).closest('.report-master').find('.dataTables-search-btn').trigger('click');
                                                                    refreshOnChange='';
                                                                    Core.Metrics();
                                                                }
                                                                responseData.alert = 'Removed' ;
                                                                break;

                                   case         'device-edit' : console.log('Core:Ajax:'+responseData.element+':device-edit:'+responseData.value);
                                                                if(responseData.value){
                                                                    $.each(responseData.value, function( key, val ) {
                                                                        switch(key){
                                                                            case           'unitname' : $('#modal-edit-device').find('.modal-title').text(val);
                                                                                                        $('#vehicle-name').text(val);
                                                                                                        break;
                                                                            case       'serialnumber' : $('#vehicle-serial').text(val);
                                                                                                        $('#device-serial').text(val);
                                                                                                        break;
                                                                            case       'unitgroup_id' : $('#vehicle-group').val(val);
                                                                                                        break;
                                                                            case      'unitstatus_id' : $('#vehicle-status').val(val);
                                                                                                        break;
                                                                            case                'vin' : $('#vehicle-vin').text(val);
                                                                                                        break;
                                                                            case               'make' : $('#vehicle-make').text(val);
                                                                                                        break;
                                                                            case              'model' : $('#vehicle-model').text(val);
                                                                                                        break;
                                                                            case               'year' : $('#vehicle-year').text(val);
                                                                                                        break;
                                                                            case              'color' : $('#vehicle-color').text(val);
                                                                                                        break;
                                                                            case        'stocknumber' : $('#vehicle-stock').text(val);
                                                                                                        break;
                                                                            case 'licenseplatenumber' : $('#vehicle-license-plate').text(val);
                                                                                                        break;
                                                                            case         'loannumber' : $('#vehicle-loan-id').text(val);
                                                                                                        break;
                                                                            case        'installdate' : $('#vehicle-install-date').text(val);
                                                                                                        break;
                                                                            case          'installer' : $('#vehicle-installer').text(val);
                                                                                                        break;
                                                                            case    'initialodometer' : $('#vehicle-install-mileage').text(val);
                                                                                                        break;
                                                                            case    'currentodometer' : $('#vehicle-driven-miles').text(val);
                                                                                                        break;
                                                                            case      'totalodometer' : $('#vehicle-total-mileage').text(val);
                                                                                                        break;
                                                                            case          'firstname' : $('#customer-first-name').text(val);
                                                                                                        break;
                                                                            case           'lastname' : $('#customer-last-name').text(val);
                                                                                                        break;
                                                                            case      'streetaddress' : $('#customer-address').text(val);
                                                                                                        break;
                                                                            case               'city' : $('#customer-city').text(val);
                                                                                                        break;
                                                                            case              'state' : $('#customer-state').text(val);
                                                                                                        break;
                                                                            case            'zipcode' : $('#customer-zipcode').text(val);
                                                                                                        break;
                                                                            case          'cellphone' : $('#customer-mobile-phone').text(val);
                                                                                                        break;
                                                                            case          'homephone' : $('#customer-home-phone').text(val);
                                                                                                        break;
                                                                            case              'email' : $('#customer-email').text(val);
                                                                                                        break;
                                                                            case     'unitstatusname' : $('#device-status').text(val);
                                                                                                        break;
                                                                            case               'plan' : $('#device-plan').text(val);
                                                                                                        break;
                                                                            case       'purchasedate' : $('#device-purchase-date').text(val);
                                                                                                        break;
                                                                            case       'activatedate' : $('#device-activation-date').text(val);
                                                                                                        break;
                                                                            case        'renewaldate' : $('#device-renewal-date').text(val);
                                                                                                        break;
                                                                            case    'lastrenewaldate' : $('#device-last-renewed').text(val);
                                                                                                        break;
                                                                            case     'deactivatedate' : $('#device-deactivation-date').text(val);
                                                                                                        break;
                                                                        }
                                                                    });
                                                                }
                                                                break;                                        

                                 case 'load-transfer-devices' : console.log(responseData.mode);
                                                                console.log(responseData.value+':'+responseData.element);
                                                                if(responseData.element=='search-batch-devices'){
                                                                    console.log(responseData.devices);
                                                                    if(responseData.devices){
                                                                        if($('#'+responseData.element).closest('.form-group').find('ul.list-group').attr('id')){
                                                                            if($('#'+responseData.element).closest('.form-group').find('ul.list-group').attr('id').split('-').pop()=='available'){
                                                                                $('#'+responseData.element).closest('.form-group').find('ul.list-group').empty();
                                                                                $.each(responseData.devices, function( k , v ) {
                                                                                    console.log(v);
                                                                                    console.log('Core:Ajax:formfill:#'+$('#'+responseData.element).closest('.form-group').find('ul.list-group').attr('id')+':'+v.k+'='+v.v);
                                                                                    $('#'+responseData.element).closest('.form-group').find('ul.list-group').append('<li id="'+v.k+'"><a href="javascript:void(0);">'+v.v+'<span class="pull-right text-grey">'+v.k+'</span></a></li>');
                                                                                });
                                                                                Core.SortList($('#'+responseData.element).closest('.form-group').find('ul.list-group').attr('id'));
                                                                            }
                                                                        }
                                                                    }
                                                                } else {
                                                                    console.log(responseData.from);
                                                                    if(responseData.from){
                                                                        if($('#'+responseData.element).closest('.form-group').find('ul.list-group').attr('id')){
                                                                            if($('#'+responseData.element).closest('.form-group').find('ul.list-group').attr('id').split('-').pop()=='available'){
                                                                                $('#'+responseData.element).closest('.form-group').find('ul.list-group').empty();
                                                                                $.each(responseData.from, function( k , v ) {
                                                                                    console.log(v);
                                                                                    console.log('Core:Ajax:formfill:#'+$('#'+responseData.element).closest('.form-group').find('ul.list-group').attr('id')+':'+v.k+'='+v.v);
                                                                                    $('#'+responseData.element).closest('.form-group').find('ul.list-group').append('<li id="'+v.k+'"><a href="javascript:void(0);">'+v.v+'</a></li>');
                                                                                });
                                                                                Core.SortList($('#'+responseData.element).closest('.form-group').find('ul.list-group').attr('id'));
                                                                            }
                                                                        }
                                                                        // if($('#user-edit-devices-available').attr('id')){
                                                                        //     $('#user-edit-devices-available').empty();
                                                                        //     $.each(responseData.from, function( k , v ) {
                                                                        //         console.log(v);
                                                                        //         console.log('Core:Ajax:formfill:#user-edit-devices-available:'+v.k+'='+v.v);
                                                                        //         $('#user-edit-devices-available').append('<li id="'+v.k+'"><a href="javascript:void(0);">'+v.v+'</a></li>');
                                                                        //     });
                                                                        //     Core.SortList('user-edit-devices-available');
                                                                        // }
                                                                        var notDefault='';
                                                                        $("#transfer-group-to").find('option').each(function () {
                                                                            if($(this).text()=='Default'){
                                                                                //
                                                                            } else if(!(notDefault)){
                                                                                notDefault=1;
                                                                                $(this).attr('selected','selected');
                                                                            }
                                                                        });
                                                                        if(notDefault){
                                                                            $('#search-transfer-to').trigger('click');
                                                                        }
                                                                    }
                                                                    console.log(responseData.to);
                                                                    if(responseData.to){
                                                                        if($('#'+responseData.element).closest('.form-group').find('ul.list-group').attr('id')){
                                                                            if($('#'+responseData.element).closest('.form-group').find('ul.list-group').attr('id').split('-').pop()=='assigned'){
                                                                                $('#'+responseData.element).closest('.form-group').find('ul.list-group').empty();
                                                                                $.each(responseData.to, function( k , v ) {
                                                                                    console.log(v);
                                                                                    console.log('Core:Ajax:formfill:#'+$('#'+responseData.element).closest('.form-group').find('ul.list-group').attr('id')+':'+v.k+'='+v.v);
                                                                                    $('#'+responseData.element).closest('.form-group').find('ul.list-group').append('<li id="'+v.k+'"><a href="javascript:void(0);">'+v.v+'</a></li>');
                                                                                });
                                                                                Core.SortList($('#'+responseData.element).closest('.form-group').find('ul.list-group').attr('id'));
                                                                            }
                                                                        }
                                                                        // if($('#user-edit-devices-assigned').attr('id')){
                                                                        //     $('#user-edit-devices-assigned').empty();
                                                                        //     $.each(responseData.to, function( k , v ) {
                                                                        //         console.log(v);
                                                                        //         console.log('Core:Ajax:formfill:#user-edit-devices-assigned:'+v.k+'='+v.v);
                                                                        //         $('#user-edit-devices-assigned').append('<li id="'+v.k+'"><a href="javascript:void(0);">'+v.v+'</a></li>');
                                                                        //     });
                                                                        //     Core.SortList('user-edit-devices-assigned');
                                                                        // }
                                                                    }
                                                                }
                                                                bool_loadTransferDevices=1;
                                                                break;                                        

                                   case            'formfill' : console.log('Core:Ajax:formfill:'+responseData.mode+':'+responseData.action);
                                                                console.log(responseData.value);
                                                                if((Array.isArray(responseData.value))||(responseData.action=='user-edit')){
                                                                    $.each(responseData.value, function( ele, val ) {
                                                                        console.log('Core:Ajax:formfill:'+responseData.action+':'+ele+':visible?');
                                                                        if(ele.split('_')[1]=='id'){
                                                                            ele = ele.split('_')[0];
                                                                        }
                                                                        if($('#'+responseData.action+'-'+ele).attr('id')){
                                                                            console.log('Core:Ajax:formfill:'+responseData.action+':'+ele+':'+val);
                                                                            $('#'+responseData.action+'-'+ele).val(val);
                                                                            $('#'+responseData.action+'-'+ele).attr('value',val);
                                                                            $('#'+responseData.action+'-'+ele).attr('data-uid',responseData.uid);
                                                                        }
                                                                    });
                                                                }
                                                                if(responseData.territories){
                                                                    console.log('responseData.territories');
                                                                    console.log(responseData.territories.available);
                                                                    if(responseData.territories.available){
                                                                        if($('#edit-landmark-groups-available').attr('id')){
                                                                            $('#edit-landmark-groups-available').empty();
                                                                            $.each(responseData.territories.available, function( k , v ) {
                                                                                if((v.territorygroupname=='null')||(v.territorygroupname==null)){
                                                                                    v.territorygroupname='';
                                                                                }
                                                                                $('#edit-landmark-groups-available').append('<li id="'+v.territory_id+'"><a href="javascript:void(0);">'+v.territoryname+' <div class="text-grey pull-right">'+v.territorygroupname+'</div></a></li>');
                                                                            });
                                                                            Core.SortList('edit-landmark-groups-available');
                                                                        }
                                                                    }
                                                                    console.log(responseData.territories.assigned);
                                                                    if(responseData.territories.assigned){
                                                                        if($('#edit-landmark-groups-assigned').attr('id')){
                                                                            $('#edit-landmark-groups-assigned').empty();
                                                                            $.each(responseData.territories.assigned, function( k , v ) {
                                                                                $('#edit-landmark-groups-assigned').append('<li id="'+v.territory_id+'"><a href="javascript:void(0);">'+v.territoryname+'</a></li>');
                                                                            });
                                                                            Core.SortList('edit-landmark-groups-assigned');
                                                                        }
                                                                    }
                                                                }
                                                                if(responseData.vehiclegroupdevices1){
                                                                    console.log('responseData.vehiclegroupdevices1');
                                                                    console.log(responseData.vehiclegroupdevices1.assigned);
                                                                    if(responseData.vehiclegroupdevices1.assigned){
                                                                        if($('#edit-vehicle-group-devices-available').attr('id')){
                                                                            $('#edit-vehicle-group-devices-available').empty();
                                                                            $.each(responseData.vehiclegroupdevices1.assigned, function( k , v ) {
                                                                                $('#edit-vehicle-group-devices-available').append('<li id="'+v.unit_id+'"><a href="javascript:void(0);">'+v.unitname+'</a></li>');
                                                                            });
                                                                            Core.SortList('edit-vehicle-group-devices-available');
                                                                        }
                                                                    }
                                                                }
                                                                if(responseData.vehiclegroupdevices2){
                                                                    console.log('responseData.vehiclegroupdevices2');
                                                                    console.log(responseData.vehiclegroupdevices2.assigned);
                                                                    if(responseData.vehiclegroupdevices2.assigned){
                                                                        if($('#edit-vehicle-group-devices-assigned').attr('id')){
                                                                            $('#edit-vehicle-group-devices-assigned').empty();
                                                                            $.each(responseData.vehiclegroupdevices2.assigned, function( k , v ) {
                                                                                $('#edit-vehicle-group-devices-assigned').append('<li id="'+v.unit_id+'"><a href="javascript:void(0);">'+v.unitname+'</a></li>');
                                                                            });
                                                                            Core.SortList('edit-vehicle-group-devices-assigned');
                                                                        }
                                                                    }
                                                                }
                                                                if(responseData.vehiclegroupusers){
                                                                    console.log('responseData.vehiclegroupusers');
                                                                    console.log(responseData.vehiclegroupusers);
                                                                    if(responseData.vehiclegroupusers){
                                                                        if($('#edit-vehicle-group-users').attr('id')){
                                                                            $('#edit-vehicle-group-users').find('li').removeClass('active');
                                                                            $('#edit-vehicle-group-users').find('li input').prop('checked',false);
                                                                            $.each(responseData.vehiclegroupusers, function( k , v ) {
                                                                                $('#edit-vehicle-group-users').find('li.selections-vehicle-group-users-'+v.user_id).addClass('active');
                                                                                $('#edit-vehicle-group-users').find('li.selections-vehicle-group-users-'+v.user_id+' input').prop('checked',true);
                                                                            });
                                                                            // Core.SortList('edit-vehicle-group-users');
                                                                        }
                                                                    }
                                                                }
                                                                if(responseData.vehiclegroupusertypes){
                                                                    console.log('responseData.vehiclegroupusertypes');
                                                                    console.log(responseData.vehiclegroupusertypes);
                                                                    if(responseData.vehiclegroupusertypes){
                                                                        if($('#edit-vehicle-group-user-types').attr('id')){
                                                                            $('#edit-vehicle-group-user-types').find('li').removeClass('active');
                                                                            $('#edit-vehicle-group-user-types').find('li input').prop('checked',false);
                                                                            $.each(responseData.vehiclegroupusertypes, function( k , v ) {
                                                                                $('#edit-vehicle-group-user-types').find('li.selections-vehicle-group-usertypes-'+v.usertype_id).addClass('active');
                                                                                $('#edit-vehicle-group-user-types').find('li.selections-vehicle-group-usertypes-'+v.usertype_id+' input').prop('checked',true);
                                                                            });
                                                                            // Core.SortList('edit-vehicle-group-user-types');
                                                                        }
                                                                    }
                                                                }
                                                                if(responseData.vehiclegroups){
                                                                    console.log('responseData.vehiclegroups');
                                                                    console.log(responseData.vehiclegroups);
                                                                    if(responseData.vehiclegroups){
                                                                        if($('#'+responseData.action+'-vehiclegroups-available').attr('id')){
                                                                            $('#'+responseData.action+'-vehiclegroups-available').empty();
                                                                            $.each(responseData.vehiclegroups.available, function( k , v ) {
                                                                                console.log('Core:Ajax:formfill:#'+responseData.action+'-vehiclegroups-available:'+v.unitgroup_id+'='+v.unitgroupname);
                                                                                $('#'+responseData.action+'-vehiclegroups-available').append('<li id="'+v.unitgroup_id+'"><a href="javascript:void(0);">'+v.unitgroupname+'</a></li>');
                                                                            });
                                                                            Core.SortList(responseData.action+'-vehiclegroups-available');
                                                                            $('#'+responseData.action+'-vehiclegroups-assigned').attr('data-uid',responseData.uid);
                                                                            $('#'+responseData.action+'-vehiclegroups-assigned').empty();
                                                                            $.each(responseData.vehiclegroups.assigned, function( k , v ) {
                                                                                console.log('Core:Ajax:formfill:#'+responseData.action+'-vehiclegroups-assigned:'+v.unitgroup_id+'='+v.unitgroupname);
                                                                                $('#'+v.unitgroup_id).detach().appendTo($('#'+responseData.action+'-vehiclegroups-assigned'));
                                                                            });
                                                                            Core.SortList(responseData.action+'-vehiclegroups-assigned');
                                                                        }
                                                                    }
                                                                }
                                                                if(responseData.landmarkgroups){
                                                                    console.log('responseData.landmarkgroups');
                                                                    console.log(responseData.landmarkgroups);
                                                                    if(responseData.landmarkgroups){
                                                                        if($('#'+responseData.action+'-landmarkgroups-available').attr('id')){
                                                                            $('#'+responseData.action+'-landmarkgroups-available').empty();
                                                                            $.each(responseData.landmarkgroups.available, function( k , v ) {
                                                                                if(!(v.territorygroupname)){
                                                                                    v.territorygroupname = 'Landmark Group #' + v.territorygroup_id;
                                                                                }
                                                                                console.log('Core:Ajax:formfill:#'+responseData.action+'-landmarkgroups-available:'+v.territorygroup_id+'='+v.territorygroupname);
                                                                                $('#'+responseData.action+'-landmarkgroups-available').append('<li id="'+v.territorygroup_id+'"><a href="javascript:void(0);">'+v.territorygroupname+'</a></li>');
                                                                            });
                                                                            Core.SortList(responseData.action+'-landmarkgroups-available');
                                                                            $('#'+responseData.action+'-landmarkgroups-assigned').attr('data-uid',responseData.uid);
                                                                            $('#'+responseData.action+'-landmarkgroups-assigned').empty();
                                                                            $.each(responseData.landmarkgroups.assigned, function( k , v ) {
                                                                                console.log('Core:Ajax:formfill:#'+responseData.action+'-landmarkgroups-assigned:'+v.territorygroup_id+'='+v.territorygroupname);
                                                                                $('#'+v.territorygroup_id).detach().appendTo($('#'+responseData.action+'-landmarkgroups-assigned'));
                                                                            });
                                                                            Core.SortList(responseData.action+'-landmarkgroups-assigned');
                                                                        }
                                                                    }
                                                                }
                                                                if(responseData.permissions){
                                                                    console.log('responseData.permissions');
                                                                    console.log(responseData.permissions);
                                                                    if(responseData.permissions){
                                                                        $.each(responseData.permissions, function( k , v ) {
                                                                            switch(v.permissioncategory_id){
                                                                                case '1': v.permissioncategory_id='Admin'; break;
                                                                                case '2': v.permissioncategory_id='Vehicles'; break;
                                                                                case '3': v.permissioncategory_id='Landmarks'; break;
                                                                                case '4': v.permissioncategory_id='Alerts'; break;
                                                                                case '5': v.permissioncategory_id='Reports'; break;
                                                                            }
                                                                            console.log('Core:Ajax:formfill:input-'+v.permissioncategory_id+'-permission-'+v.permission_id+':check');
                                                                            if($('#input-'+v.permissioncategory_id+'-permission-'+v.permission_id).attr('id')){
                                                                                $('#input-'+v.permissioncategory_id+'-permission-'+v.permission_id).prop('checked',true);
                                                                                $('#'+v.permissioncategory_id+'-permission-'+v.permission_id).addClass('active');
                                                                            }
                                                                        });
                                                                    }
                                                                }
                                                                bool_loadTransferDevices=1;
                                                                break;                                        

                                   case      'formfill-alert' : console.log('Core:Ajax:formfill-alert:'+responseData.mode+':'+responseData.action);
                                                                console.log(responseData.value);
                                                                if(Array.isArray(responseData.value)){
                                                                    formFillBool=1;
                                                                    $.each(responseData.value, function( k1, v1 ) {
                                                                        $.each(v1, function( k2, v2 ) {
                                                                            console.log('Core:Ajax:formfill-alert: k2="'+k2+'", v2="'+v2+'"');
                                                                            switch(k2){
                                                                                case               'alerttrigger' : $('#alert-edit-landmarktrigger').val(v2);
                                                                                                                    $('#alert-edit-duration').val(v2);
                                                                                                                    $('#alert-edit-overspeed').val(v2);
                                                                                                                    break;
                                                                                case               'alerttype_id' : $('#alert-edit-type').val(v2);
                                                                                                                    break;
                                                                                case                 'contact_id' : $('#alert-edit-contact').val(v2);
                                                                                                                    if(v2>0){
                                                                                                                        $('#alert-edit-contactmode').val(1);
                                                                                                                    }
                                                                                                                    break;
                                                                                case            'contactgroup_id' : $('#alert-edit-contactgroup').val(v2);
                                                                                                                    if(v2>0){
                                                                                                                        $('#alert-edit-contactmode').val(2);
                                                                                                                    }
                                                                                                                    break;
                                                                                case                        'day' :
                                                                                case                       'days' : switch(v2){
                                                                                                                        case    'weekday' : v2=1; 
                                                                                                                                            break;
                                                                                                                        case    'weekend' : v2=2; 
                                                                                                                                            break;
                                                                                                                        case        'all' : v2=3; 
                                                                                                                                            break;
                                                                                                                    }
                                                                                                                    $('#alert-edit-days').val(v2);
                                                                                                                    break;
                                                                                case                    'endhour' : $('#alert-edit-endhour').val(v2);
                                                                                                                    break;
                                                                                case                      'hours' :
                                                                                case                       'time' : switch(v2){
                                                                                                                        case        'all' : v2=0; 
                                                                                                                                            break;
                                                                                                                        case      'range' : v2=1; 
                                                                                                                                            break;
                                                                                                                    }
                                                                                                                    $('#alert-edit-hours').val(v2);
                                                                                                                    break;
                                                                                case                     'method' : $('#alert-edit-contactmethod').val(v2);
                                                                                                                    break;
                                                                                case                  'starthour' : $('#alert-edit-starthour').val(v2);
                                                                                                                    break;
                                                                                case               'territory_id' : $('#alert-edit-landmark').val(v2);
console.log("$('#alert-edit-landmark').val(v2):"+v2);
                                                                                                                    if(v2>0){
                                                                                                                        $('#alert-edit-landmarkmode').val(1);
                                                                                                                    }
                                                                                                                    break;
                                                                                case          'territorygroup_id' : $('#alert-edit-landmarkgroup').val(v2);
                                                                                                                    if(v2>0){
                                                                                                                        $('#alert-edit-landmarkmode').val(2);
                                                                                                                    }
                                                                                                                    break;
                                                                                case                    'unit_id' : $('#alert-edit-vehicle').val(v2);
                                                                                                                    if(v2>0){
                                                                                                                        // $('#alert-edit-vehiclemode').val(1);
                                                                                                                    }
                                                                                                                    break;
                                                                                case               'unitgroup_id' : $('#alert-edit-vehiclegroup').val(v2);
                                                                                                                    if(v2>0){
                                                                                                                        // $('#alert-edit-vehiclemode').val(2);
                                                                                                                    }
                                                                                                                    break;
                                                                                case             'unitgroup_mode' : $('#alert-edit-vehiclemode').val(v2);
                                                                                                                    break;
                                                                            }
                                                                        });
                                                                    });
                                                                    $('#alert-edit-contactmode').trigger('change');
                                                                    $('#alert-edit-landmarkmode').trigger('change');
                                                                    $('#alert-edit-vehiclemode').trigger('change');
                                                                    $('#alert-edit-days').trigger('change');
                                                                    $('#alert-edit-hours').trigger('change');
                                                                    $('#alert-edit-type').trigger('change');
                                                                }
                                                                formFillBool='';
                                                                break;

                                   case    'formfill-contact' : console.log('Core:Ajax:formfill-contact:'+responseData.mode+':'+responseData.action);
                                                                console.log(responseData.value);
                                                                if(Array.isArray(responseData.value)){
                                                                    formFillBool=1;
                                                                    $.each(responseData.value, function( k1, v1 ) {
                                                                        $.each(v1, function( k2, v2 ) {
                                                                            console.log('Core:Ajax:formfill-contact: k2="'+k2+'", v2="'+v2+'"');
                                                                            switch(k2){
                                                                                case             'cellcarrier_id' : $('#edit-contact-carrier').attr('data-uid',responseData.uid);
                                                                                                                    $('#edit-contact-carrier').val(v2);
                                                                                                                    break;
                                                                                case                 'cellnumber' : $('#edit-contact-cellnumber').attr('data-uid',responseData.uid);
                                                                                                                    $('#edit-contact-cellnumber').val(v2);
                                                                                                                    break;
                                                                                case                      'email' : $('#edit-contact-email').attr('data-uid',responseData.uid);
                                                                                                                    $('#edit-contact-email').val(v2);
                                                                                                                    break;
                                                                                case                  'firstname' : $('#edit-contact-first-name').attr('data-uid',responseData.uid);
                                                                                                                    $('#edit-contact-first-name').val(v2);
                                                                                                                    break;
                                                                                case                   'lastname' : $('#edit-contact-last-name').attr('data-uid',responseData.uid);
                                                                                                                    $('#edit-contact-last-name').val(v2);
                                                                                                                    break;
                                                                            }
                                                                        });
                                                                    });
                                                                    // $('#alert-edit-contactmode').trigger('change');
                                                                }
                                                                formFillBool='';
                                                                break;

                                case 'formfill-contact-group' : console.log('Core:Ajax:formfill-contact-group:'+responseData.mode+':'+responseData.action);
                                                                if(responseData.contacts){
                                                                    console.log(responseData.contacts);
                                                                    if(responseData.contacts){
                                                                        if($('#edit-contact-group-contacts-available').attr('id')){
                                                                            $('#edit-contact-group-contacts-available').empty();
                                                                            $.each(responseData.contacts.available, function( k , v ) {
                                                                                console.log('Core:Ajax:formfill:#edit-contact-group-contacts-available:'+v.contact_id+'='+v.contactname);
                                                                                $('#edit-contact-group-contacts-available').append('<li id="'+v.contact_id+'"><a href="javascript:void(0);">'+v.contactname+'</a></li>');
                                                                            });
                                                                            Core.SortList('edit-contact-group-contacts-available');
                                                                            $('#edit-contact-group-contacts-assigned').attr('data-uid',responseData.uid);
                                                                            $('#edit-contact-group-contacts-assigned').empty();
                                                                            $.each(responseData.contacts.assigned, function( k , v ) {
                                                                                console.log('Core:Ajax:formfill:#edit-contact-group-contacts-assigned:'+v.contact_id+'='+v.contactname);
                                                                                $('#'+v.contact_id).detach().appendTo($('#edit-contact-group-contacts-assigned'));
                                                                            });
                                                                            Core.SortList('edit-contact-group-contacts-assigned');
                                                                        }
                                                                    }
                                                                }
                                                                formFillBool='';
                                                                break;

                            case    'formfill-schedulereport' : console.log('Core:Ajax:formfill-schedulereport:'+responseData.mode+':'+responseData.action);
                                                                console.log(responseData.value);
                                                                if(Array.isArray(responseData.value)){
                                                                    formFillBool=1;
                                                                    $.each(responseData.value, function( k1, v1 ) {
                                                                        $.each(v1, function( k2, v2 ) {
                                                                            console.log('Core:Ajax:formfill-schedulereport: k2="'+k2+'", v2="'+v2+'"');
                                                                            switch(k2){
                                                                                case               'alerttype_id' : $('#scheduled-report-edit-alerttype').attr('data-uid',responseData.uid);
                                                                                                                    $('#scheduled-report-edit-alerttype').val(v2);
                                                                                                                    break;
                                                                                case                 'contact_id' : $('#scheduled-report-edit-contact').attr('data-uid',responseData.uid);
                                                                                                                    $('#scheduled-report-edit-contact').val(v2);
                                                                                                                    if(v2>0){
                                                                                                                        $('#scheduled-report-edit-contactmode').val('single');
                                                                                                                    }
                                                                                                                    break;
                                                                                case            'contactgroup_id' : $('#scheduled-report-edit-contactgroup').attr('data-uid',responseData.uid);
                                                                                                                    $('#scheduled-report-edit-contactgroup').val(v2);
                                                                                                                    if(v2>0){
                                                                                                                        $('#scheduled-report-edit-contactmode').val('group');
                                                                                                                    }
                                                                                                                    break;
                                                                                case                        'day' : $('#scheduled-report-edit-day').attr('data-uid',responseData.uid);
                                                                                                                    $('#scheduled-report-edit-day').val(v2);
                                                                                                                    break;
                                                                                case                     'format' : $('#scheduled-report-edit-format').attr('data-uid',responseData.uid);
                                                                                                                    $('#scheduled-report-edit-format').val(v2);
                                                                                                                    break;
                                                                                case               'territory_id' : $('#scheduled-report-edit-landmark').attr('data-uid',responseData.uid);
                                                                                                                    $('#scheduled-report-edit-landmark').val(v2);
                                                                                                                    if(v2>0){
                                                                                                                        $('#scheduled-report-edit-landmarkmode').val('single');
                                                                                                                    }
                                                                                                                    break;
                                                                                case          'territorygroup_id' : $('#scheduled-report-edit-landmarkgroup').attr('data-uid',responseData.uid);
                                                                                                                    $('#scheduled-report-edit-landmarkgroup').val(v2);
                                                                                                                    if(v2>0){
                                                                                                                        $('#scheduled-report-edit-landmarkmode').val('group');
                                                                                                                    }
                                                                                                                    break;
                                                                                case                       'mile' : $('#scheduled-report-edit-mile').attr('data-uid',responseData.uid);
                                                                                                                    $('#scheduled-report-edit-mile').val(v2);
                                                                                                                    break;
                                                                                case                     'minute' : $('#scheduled-report-edit-minute').attr('data-uid',responseData.uid);
                                                                                                                    $('#scheduled-report-edit-minute').val(v2);
                                                                                                                    break;
                                                                                case                   'monthday' : $('#scheduled-report-edit-monthday').attr('data-uid',responseData.uid);
                                                                                                                    $('#scheduled-report-edit-monthday').val(v2);
                                                                                                                    break;
                                                                                case                        'mph' : $('#scheduled-report-edit-mph').attr('data-uid',responseData.uid);
                                                                                                                    $('#scheduled-report-edit-mph').val(v2);
                                                                                                                    break;
                                                                                case              'reporttype_id' : $('#scheduled-report-edit-reporttype').attr('data-uid',responseData.uid);
                                                                                                                    $('#scheduled-report-edit-reporttype').val(v2);
                                                                                                                    break;
                                                                                case                   'schedule' : $('#scheduled-report-edit-recurrence').attr('data-uid',responseData.uid);
                                                                                                                    $('#scheduled-report-edit-recurrence').val(v2);
                                                                                                                    break;
                                                                                case                'scheduleday' : $('#scheduled-report-edit-scheduleday').attr('data-uid',responseData.uid);
                                                                                                                    $('#scheduled-report-edit-scheduleday').val(v2);
                                                                                                                    break;
                                                                                case                   'sendhour' : $('#scheduled-report-edit-time').attr('data-uid',responseData.uid);
                                                                                                                    $('#scheduled-report-edit-time').val(v2);
                                                                                                                    break;
                                                                                case                    'unit_id' : $('#scheduled-report-edit-vehicle').attr('data-uid',responseData.uid);
                                                                                                                    $('#scheduled-report-edit-vehicle').val(v2);
                                                                                                                    if(v2>0){
                                                                                                                        $('#scheduled-report-edit-vehiclemode').val('single');
                                                                                                                    }
                                                                                                                    break;
                                                                                case               'unitgroup_id' : $('#scheduled-report-edit-vehiclegroup').attr('data-uid',responseData.uid);
                                                                                                                    $('#scheduled-report-edit-vehiclegroup').val(v2);
                                                                                                                    if(v2>0){
                                                                                                                        $('#scheduled-report-edit-vehiclemode').val('group');
                                                                                                                    }
                                                                                                                    break;
                                                                                case               'verification' : $('#scheduled-report-edit-verification').attr('data-uid',responseData.uid);
                                                                                                                    $('#scheduled-report-edit-verification').val(v2);
                                                                                                                    break;
                                                                            }
                                                                        });
                                                                    });
                                                                    $('#scheduled-report-edit-contactmode').trigger('change');
                                                                    $('#scheduled-report-edit-landmarkmode').trigger('change');
                                                                    $('#scheduled-report-edit-vehiclemode').trigger('change');
                                                                    $('#scheduled-report-edit-recurrence').trigger('change');
                                                                    $('#scheduled-report-edit-reporttype').trigger('change');
                                                                }
                                                                formFillBool='';
                                                                break;                                        

                                   case                'html' : $('#'+responseData.element).html(responseData.value);
                                                                break;

                                   case                'init' : if(responseData.element){
                                                                    switch(responseData.element){
                                                                       case 'alert-add-contact' : break;
                                                                                        default : $.each(responseData.value, function( k, v ) {
                                                                                                    console.log('Core.Ajax:init:k='+k+':v.v='+v.v+':v.k='+v.k);
                                                                                                    $('#'+responseData.element).text(v.k);
                                                                                                    $('#'+responseData.element).val(v.v);
                                                                                                  });
                                                                    }
                                                                }
                                                                break;

                                   case             'latlong' : console.log('>>>>>>>>>>>>>>>>>>>>>>>>>>> latlong:'+responseData.LandmarkTbl);
                                                                if(responseData.LandmarkTbl){
                                                                    $('#landmark-'+responseData.LandmarkTbl+'-table').find('.dataTables-search-btn').trigger('click');
                                                                }
                                                                break;

                                   case 'landmark-latlngedit' : console.log(responseData.value);
                                                                $('#secondary-sidebar-scroll').find('li.active').trigger('click');
                                                                break;

                                   case   'mark-for-transfer' : console.log('>>>>>>>>>>>>>>>>>>>>>>>>>>> mark-for-transfer');
                                                                console.log(responseData.transferee);
                                                                $('#transfer-transferee-detail').html(responseData.transferee.accountname+'<br>'+responseData.transferee.address+'<br>'+responseData.transferee.phonenumber);
                                                                break;

                                   case             'metrics' : console.log('metrics:starter='+responseData.value[0].starterstatus+':reminder='+responseData.value[0].reminderstatus);
                                                                console.log(responseData.value);
                                                                if(!(responseData.value[0].installed)){responseData.value[0].installed='0';}
                                                                if(!(responseData.value[0].inventory)){responseData.value[0].inventory='0';}
                                                                if(!(responseData.value[0].repossession)){responseData.value[0].repossession='0';}
                                                                if(!(responseData.value[0].landmark)){responseData.value[0].landmark='0';}
                                                                if(!(responseData.value[0].movement)){responseData.value[0].movement='0';}
                                                                if(!(responseData.value[0].nonreporting)){responseData.value[0].nonreporting='0';}
                                                                if(!(responseData.value[0].reminderstatus)){responseData.value[0].reminderstatus='0';}
                                                                if(!(responseData.value[0].starterstatus)){responseData.value[0].starterstatus='0';}
                                                                $('#metric-installed').find('span').text(responseData.value[0].installed);
                                                                $('#metric-inventory').find('span').text(responseData.value[0].inventory);
                                                                $('#metric-repossession').find('span').text(responseData.value[0].repossession);
                                                                $('#metric-landmark').find('span').text(responseData.value[0].landmark);
                                                                $('#metric-movement').find('span').text(responseData.value[0].movement);
                                                                $('#metric-nonreporting').find('span').text(responseData.value[0].nonreporting);
                                                                $('#metric-reminder').find('span').text(responseData.value[0].reminderstatus);
                                                                $('#metric-starter').find('span').text(responseData.value[0].starterstatus);
                                                                Core.Viewport.adjustLayout();
                                                                allClear=1;
                                                                $('#div-all-none').show();
                                                                break;

                                   case             'options' : if((lastDropdown) && (lastDropdown!=responseData.element)){
                                                                    Core.Wizard.DeSelect('ul-'+lastDropdown);
                                                                }
                                                                lastDropdown=responseData.element;
                                                                $('#ul-'+responseData.element).empty();
                                                                $.each(responseData.value, function( k1, v1 ) {
                                                                    $.each(v1, function( v2, k2 ) {
                                                                        console.log('Core:Ajax:option:'+responseData.mode+':'+responseData.element+':'+k1+':'+k2+':'+v2);
                                                                        $('#ul-'+responseData.element).append('<li><a href="javascript:void(0);" onClick="Core.Wizard.Option2Link(\''+k1.replace("'","")+'\',\''+k2.replace("'","")+'\',\''+v2.replace("'","")+'\',\''+responseData.mode+'\');">'+v2+'</a></li>');
                                                                    });
                                                                });
                                                                // var mousePosX=$('#'+responseData.element).offset().left-5;
                                                                // var mousePosY=$('#'+responseData.element).offset().top-5;
                                                                // if(mousePosY>0){
                                                                //     console.log('Core.Ajax:option:mousePosX:'+mousePosX+':mousePosY:'+mousePosY);
                                                                //     $('#ul-'+responseData.element).css({ position: 'fixed', left: mousePosX , top: mousePosY });
                                                                // }
                                                                $('<div class="dropdown-backdrop"></div>').insertBefore('#ul-'+responseData.element);
                                                                $('#ul-'+responseData.element).slideDown(300);
                                                                Core.Underwater(responseData.element);
                                                                setTimeout("Core.Underwater('"+responseData.element+"')",333);
                                                                break;

                                   case                'repo' : window.location = '/admin/repo';
                                                                break;

                                   case             'repoKey' : console.log('Core:Ajax:'+responseData.element+':repoKey:'+responseData.value);
                                                                console.log(responseData.value);
                                                                // alert('responseData.value.repoKey:'+responseData.value.repoKey);
                                                                if((responseData.value.repoKey)&&(responseData.value.repoKey!='undefined')){
                                                                    if((responseData.value.territoryname)&&(responseData.value.territoryname!='undefined')){
                                                                        $('#vehicle-address').html('<b>'+responseData.value.territoryname+'</b><br>'+responseData.value.formatted_address.replace(/,/g,'<br>')+'<br>&nbsp;');
                                                                    } else {
                                                                        $('#vehicle-address').html(responseData.value.formatted_address.replace(/,/g,'<br>')+'<br>&nbsp;');
                                                                    }
                                                                    $('#vehicle-color').html(responseData.value.color);
                                                                    $('#vehicle-event').html(responseData.value.eventname);
                                                                    $('#vehicle-expiration').html(responseData.value.expiration);
                                                                    $('#vehicle-make').html(responseData.value.make);
                                                                    $('#vehicle-model').html(responseData.value.model);
                                                                    $('#vehicle-lat-lng').html(responseData.value.latitude+' / '+responseData.value.longitude);
                                                                    $('#vehicle-license-plate').html(responseData.value.licenseplatenumber);
                                                                    $('#vehicle-servertime').html(responseData.value.servertimediff+'<br><span class="text-10 text-grey">'+responseData.value.servertime+'</span>');
                                                                    $('#vehicle-status').html(responseData.value.status);
                                                                    $('#vehicle-vin').html(responseData.value.vin);
                                                                    $('#vehicle-unittime').html(responseData.value.unittimediff+'<br><span class="text-10 text-grey">'+responseData.value.unittime+'</span>');
                                                                    $('#vehicle-year').html(responseData.value.year);
                                                                    markerOptions = {
                                                                        id: 999,
                                                                        name: repoKey, //+' '+breadcrumb.address,
                                                                        latitude: responseData.value.latitude,
                                                                        longitude: responseData.value.longitude,
                                                                        eventname: responseData.value.eventname, // used in map class to get vehicle marker color
                                                                        click: function() {
                                                                            // alert(breadcrumb.address);
                                                                            // Map.openInfoWindow(Core.map, 'repo', responseData.value.latitude, responseData.value.longitude, breadcrumb);
                                                                            Map.openInfoWindow(Core.map, 'repo', responseData.value.latitude, responseData.value.longitude, event, '', '', '', '', responseData.value.territoryname,'','','','','',responseData.value.color,responseData.value.licenseplatenumber,responseData.value.make,responseData.value.model,responseData.value.vin,responseData.value.year);
                                                                        }
                                                                    };
                                                                    Map.resize(Core.map);
                                                                    Map.resetMap(Core.map);
                                                                    Map.clearMarkers(Core.map);
                                                                    Map.addMarker(Core.map, markerOptions, true);
                                                                    Map.updateMarkerBound(Core.map);
                                                                    Map.updateMapBound(Core.map);
                                                                    // Map.updateMapBoundZoom(Core.map, true);
                                                                    var event = {
                                                                            duration: responseData.value.unittimediff,
                                                                            eventname: responseData.value.eventname,
                                                                            infomarker_address: responseData.value.formatted_address,
                                                                            latitude: responseData.value.latitude,
                                                                            longitude: responseData.value.longitude,
                                                                            status: responseData.value.status,
                                                                            unitname: responseData.value.unitname
                                                                            // unitname: repoKey
                                                                        }
                                                                    ;
                                                                    Map.openInfoWindow(Core.map, 'repo', responseData.value.latitude, responseData.value.longitude, event, '', '', '', '', responseData.value.territoryname,'','','','','',responseData.value.color,responseData.value.licenseplatenumber,responseData.value.make,responseData.value.model,responseData.value.vin,responseData.value.year);
                                                                    setTimeout("$('#repolink-none').trigger('click')",1);
                                                                    setTimeout("$('#repolink-map').trigger('click')",100);
                                                                } else {
                                                                    $('#repolink-expired').trigger('click');
                                                                    $('#nav-links').hide();
                                                                }
                                                                break;

                                   case         'removeClass' : $('#'+responseData.element).removeClass(responseData.value);
                                                                break;

                                   case            'savelist' : switch(responseData.element){
                                                                    case        'edit-vehicle-group-devices-assigned' :
                                                                    case       'edit-vehicle-group-devices-available' : $('#vehicle-group-table').find('.dataTables-search-btn').trigger('click');
                                                                                                                        break;
                                                                    case                 'user-edit-devices-assigned' :
                                                                    case                'user-edit-devices-available' : if(responseData.alert=='Updated'){
                                                                                                                            $('#device-list-table').find('.dataTables-search-btn').trigger('click');
                                                                                                                        } else {
                                                                                                                            $('#modal-device-transfer-close').trigger('click');
                                                                                                                        }
                                                                                                                        break;
                                                                }
                                                                break;

                                   case 'transfer-authorized' : console.log('Core:Ajax:'+responseData.element+':transfer-authorized:'+responseData.value);
                                                                $('#modal-transfer-authorize-release-close').trigger('click');
                                                                $('#devices-exporting').find('.dataTables-search-btn').trigger('click');
                                                                break;

                                   case   'transfer-canceled' : console.log('Core:Ajax:'+responseData.element+':transfer-canceled:'+responseData.value);
                                                                $('#devices-exporting').find('.dataTables-search-btn').trigger('click');
                                                                break;

                                   case     'transfer-accept' : console.log('Core:Ajax:'+responseData.element+':'+responseData.mode+':'+responseData.value);
                                                                console.log('$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$');
                                                                $('#modal-transfer-accept-close').trigger('click');
                                                                console.log('$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$');
                                                                $('#devices-importing').find('.dataTables-search-btn').trigger('click');
                                                                console.log('$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$');
                                                                break;

                                   case     'transfer-reject' : console.log('Core:Ajax:'+responseData.element+':'+responseData.mode+':'+responseData.value);
                                                                $('#modal-transfer-reject-close').trigger('click');
                                                                $('#devices-importing').find('.dataTables-search-btn').trigger('click');
                                                                break;

                                   case          'savechange' : console.log('Core:Ajax:'+responseData.element+':savechange:'+responseData.value);
                                                                $('#'+responseData.element).val(responseData.value);
                                                                $('#'+responseData.element).removeClass('wizard-pending');
                                                                break;

                                   case     'updatedlandmark' : console.log('Core:Ajax:'+responseData.element+':updatedlandmark:'+responseData.value.lat+':'+responseData.value.lng+':'+responseData.value.street+':'+responseData.value.city+':'+responseData.value.state+':'+responseData.value.zip);
                                                                // $('#'+responseData.element).removeClass('wizard-pending');
                                                                // $('#'+responseData.element).attr('title','');
                                                                // $('#landmark-street-address').text(responseData.value.street);
                                                                // $('#landmark-city').text(responseData.value.city);
                                                                // $('#landmark-state').text(responseData.value.state);
                                                                // $('#landmark-zipcode').text(responseData.value.zip);
                                                                // Core.EditMap.Address(3);
                                                                $('#refresh-map-markers').trigger('click');
                                                                break;

                                   case             'updated' : console.log('Core:Ajax:'+responseData.element+':updated:'+responseData.value);
                                                                $('#'+responseData.element).removeClass('wizard-pending');
                                                                $('#'+responseData.element).attr('title','');
                                                                if(responseData.confirm){
                                                                    console.log('Core:Ajax:'+responseData.element+':confirm:'+responseData.confirm);
                                                                    $('#'+responseData.element).text(responseData.confirm);
                                                                }
                                                                console.log('refreshOnChange:'+refreshOnChange);
                                                                if(refreshOnChange){
                                                                    $('#'+refreshOnChange).closest('.report-master').find('.dataTables-search-btn').trigger('click');
                                                                    refreshOnChange='';
                                                                    Core.Metrics();
                                                                }
                                                                switch(responseData.element){
                                                                    case      'edit-vehicle-group-title-edit' : $('#edit-vehicle-group-title').text(responseData.value);
                                                                                                                $('#vehicle-group-table').find('.dataTables-search-btn').trigger('click');
                                                                                                                break ;
                                                                    case                      'landmark-name' : switch(Core.Environment.context()){
                                                                                                                
                                                                                                                    case           'landmark/map' : $('#landmark-li-'+responseData.action+' a').text($('#'+responseData.element).text());
                                                                                                                                                    $('#landmark-li-'+responseData.action+' a').trigger('click');
                                                                                                                                                    break;

                                                                                                                    case  'landmark/verification' :
                                                                                                                    case          'landmark/list' : console.log('$(#landmark-list-table-'+responseData.action);
                                                                                                                                                    $('#landmark-list-table-'+responseData.action).trigger('click');
                                                                                                                                                    break;

                                                                                                                }
                                                                                                                break ;
                                                                    case                  'landmark-category' :
                                                                    case                     'landmark-group' :
                                                                    case                   'landmark-latlong' :
                                                                    case                    'landmark-radius' :
                                                                    case                     'landmark-shape' : switch(Core.Environment.context()){
                                                                                                                
                                                                                                                    case           'landmark/map' : $('#landmark-li-'+responseData.action+' a').trigger('click');
                                                                                                                                                    break;
                                                                                                                    case  'landmark/verification' :
                                                                                                                    case          'landmark/list' : console.log('$(#landmark-list-table-'+responseData.action);
                                                                                                                                                    $('#landmark-list-table-'+responseData.action).trigger('click');
                                                                                                                                                    break;
                                                                                                                                                    
                                                                                                                }
                                                                                                                break ;
                                                                    case                      'landmark-city' :
                                                                    case                     'landmark-state' :
                                                                    case            'landmark-street-address' :
                                                                    case                   'landmark-zipcode' :
                                                                    case                   'landmark-country' : Core.EditMap.Address(3);
                                                                                                                break ;
                                                                    case                  'landmark-latitude' :
                                                                    case                 'landmark-longitude' : if(($('#landmark-latitude').html())&&($('#landmark-longitude').html())){
                                                                                                                    var a = { latitude: $('#landmark-latitude').html(), longitude: $('#landmark-longitude').html() };
                                                                                                                    Core.EditMap.LatLngEdit(a);
                                                                                                                }
                                                                                                                break ;                                                                                                                
                                                                    case                       'vehicle-name' : switch(Core.Environment.context()){
                                                                                                                    
                                                                                                                    case     'admin/list' : $('#device-list-table').find('.dataTables-search-btn').trigger('click');
                                                                                                                                            break;
                                                                                                                
                                                                                                                    case    'vehicle/map' : if($('#vehicle-name').text()){
                                                                                                                                                $('#vehicle-li-'+responseData.action+' a').text($('#vehicle-name').text());
                                                                                                                                            } else {
                                                                                                                                                $('#vehicle-li-'+responseData.action+' a').text($('#vehicle-serial').text());
                                                                                                                                            }
                                                                                                                                            $('.map-bubble-title').find('b').text($('#vehicle-li-'+responseData.action+' a').text());
                                                                                                                                            $('#vehicle-map-html-modal-device').text($('#vehicle-li-'+responseData.action+' a').text());
                                                                                                                                            break;

                                                                                                                }
                                                                                                                break ;
                                                                }
                                                                switch(responseData.element){
                                                                    case               'customer-address' :
                                                                    case                  'customer-city' :
                                                                    case                 'customer-email' :
                                                                    case            'customer-first-name' :
                                                                    case          'customer-home-phone' :
                                                                    case             'customer-last-name' :
                                                                    case          'customer-mobile-phone' :
                                                                    case                 'customer-state' :
                                                                    case               'customer-zipcode' :
                                                                    case                    'device-plan' :
                                                                    case         'device-activation-date' :
                                                                    case       'device-deactivation-date' :
                                                                    case            'device-last-renewed' :
                                                                    case           'device-purchase-date' :
                                                                    case            'device-renewal-date' :
                                                                    case                  'device-serial' :
                                                                    case                  'device-status' :
                                                                    case                  'vehicle-color' :
                                                                    case                  'vehicle-group' :
                                                                    case              'vehicle-installer' :
                                                                    case           'vehicle-install-date' :
                                                                    case          'vehicle-license-plate' :
                                                                    case                'vehicle-loan-id' :
                                                                    case                   'vehicle-make' :
                                                                    case                  'vehicle-model' :
                                                                    case                   'vehicle-name' :
                                                                    case                 'vehicle-serial' :
                                                                    case                 'vehicle-status' :
                                                                    case                  'vehicle-stock' :
                                                                    case                    'vehicle-vin' :
                                                                    case                   'vehicle-year' : if((responseData.value=='')||(responseData.value=='undefined')){
                                                                                                                responseData.value='';
                                                                                                            }
                                                                                                            Core.Editable.setValue($('#'+responseData.element),responseData.value);
                                                                                                            if((responseData.element=='vehicle-vin')&&(responseData.value)){
                                                                                                                Vin.decode(responseData.value);
                                                                                                            }
                                                                                                            break;
                                                                    case        'vehicle-install-mileage' : if((responseData.value=='')||(responseData.value=='undefined')){
                                                                                                                responseData.value='';
                                                                                                            }
                                                                                                            Core.Editable.setValue($('#'+responseData.element),responseData.value);
                                                                                                            var driven = parseInt($('#vehicle-driven-miles').html());
                                                                                                            if(driven>0){
                                                                                                                var total = driven+parseInt(responseData.value);
                                                                                                                $('#vehicle-total-mileage').html(total);
                                                                                                            } else {
                                                                                                                $('#vehicle-total-mileage').html(responseData.value);
                                                                                                            }
                                                                                                            break;
                                                                }
                                                                if(responseData.LandmarkGet){
                                                                    Core.AddMap.LandmarkGet(responseData.LandmarkGet,responseData.LandmarkRid,responseData.LandmarkTbl);
                                                                }
                                                                if(!(responseData.alert)){
                                                                    responseData.alert = 'Updated';
                                                                }
                                                                break;

                                   case       'updaterefresh' : console.log('Core:Ajax:'+responseData.element+':updaterefresh:'+responseData.value);
                                                                $('.dataTables-search-btn').trigger('click');
                                                                break;

                                   case        'updateSelect' : console.log('Core:Ajax:'+responseData.element+':updateSelect:'+responseData.value);
                                                                switch (responseData.element) {
                                                                    case  'vehicle-status' : setTimeout("$('#vehicle-li-"+currentUnitId+"').trigger('click')",3000);
                                                                                             Core.DataTable.secondarySidepanelScroll();
                                                                                             break;
                                                                }
                                                                break;

                                   case        'verification' : console.log('>>>>>>>>>>>>>>>>>>>>>>>>>>> verification:'+responseData.element);
                                                                switch(responseData.element){
                                                                    case               'verification-add' :
                                                                    case       'verification-address-new' : $('#verification-report-recent').find('.dataTables-search-btn').trigger('click');
                                                                                                            break;
                                                                    case     'verification-report-recent' : $('#tab-verification').find('.dataTables-search-btn').trigger('click');
                                                                                                            break;
                                                                                                  default : $('.report-master').find('.dataTables-search-btn').trigger('click');
                                                                }
                                                                break;

                                                      default : console.log('Core:Ajax:success:action:'+responseData.action);
                                                                console.log('Core:Ajax:success:mode:'+responseData.mode);
                                                                console.log('Core:Ajax:success:element:'+responseData.element);
                                                                console.log('Core:Ajax:success:value:'+responseData.value);

                                }

                            }

                        }

                    } else if ( responseData.code === 2 ) {
                        // alert('Sorry... your user session seems to have timed-out. Please log back in.');
                        // window.location.href = "/login";
                    } else {

                        //  console validation errors
                        if ($.isEmptyObject(responseData.validaton_errors) === false) {
                            console.log('Core:Ajax:success:errors:'+responseData.validaton_errors);
                        }

                    }

                    //  console/display response code + message
                    console.log('Core:Ajax:success:'+responseData.code+':'+responseData.mode+':'+responseData.message);
                    if (responseData.alert) {
                        // alert(responseData.alert);
                        console.log('Core.Ajax:responseData.alert:'+responseData.alert);
                        Core.SystemMessage.show(responseData.alert, responseData.code);
                    }

                }
            });
        }
    },

    ScheduleReportEdit: function (x) {

        if(x){
            countScheduleReport=5;
        } else {
            countScheduleReport--;
        }

        if (countScheduleReport==1){
            countScheduleReport--;
            Core.ScheduleReportSave();
        } else if (countScheduleReport>0){
            setTimeout('Core.ScheduleReportEdit()',100);
        }

    },

    ScheduleReportSave: function () {
console.log('Core.ScheduleReportSave');

        $.ajax({
            url: '/ajax/core/ajax',
            type: 'POST',
            dataType: 'json',
            data: {
                action:         'schedule-report',
                alerttype:      $('#scheduled-report-edit-alerttype').val(),
                contact:        $('#scheduled-report-edit-contact').val(),
                contactgroup:   $('#scheduled-report-edit-contactgroup').val(),
                contactmode:    $('#scheduled-report-edit-contactmode').val(),
                day:            $('#scheduled-report-edit-day').val(),
                duration:       $('#scheduled-report-edit-duration').val(),
                format:         $('#scheduled-report-edit-format').val(),
                mile:           $('#scheduled-report-edit-mile').val(),
                minute:         $('#scheduled-report-edit-minute').val(),
                monthly:        $('#scheduled-report-edit-monthly').val(),
                mph:            $('#scheduled-report-edit-mph').val(),
                name:           $('#scheduled-report-edit-name').val(),
                not_reported:   $('#scheduled-report-edit-not-reported').val(),
                range:          $('#scheduled-report-edit-range').val(),
                rangestart:     $('#scheduled-report-edit-rangestart').val(),
                rangeend:       $('#scheduled-report-edit-rangeend').val(),
                reporttype:     $('#scheduled-report-edit-reporttype').val(),
                schedule:       $('#scheduled-report-edit-recurrence').val(),
                scheduleday:    $('#scheduled-report-edit-scheduleday').val(),
                sendhour:       $('#scheduled-report-edit-time').val(),
                territory:      $('#scheduled-report-edit-landmark').val(),
                territorygroup: $('#scheduled-report-edit-landmarkgroup').val(),
                territorymode:  $('#scheduled-report-edit-landmarkmode').val(),
                title:          $('#scheduled-report-edit-title').val(),
                uid:            $('#scheduled-report-edit-name').attr('data-uid'),
                unit:           $('#scheduled-report-edit-vehicle').val(),
                unitgroup:      $('#scheduled-report-edit-vehiclegroup').val(),
                unitmode:       $('#scheduled-report-edit-vehiclemode').val(),
                verification:   $('#scheduled-report-edit-verification').val()
            },
            success: function(responseData) {
                console.log('Core:ScheduleReportSave:success:message:'+responseData.message);
                console.log(responseData.value);
                if(responseData.alert){
                    Core.SystemMessage.show(responseData.alert, responseData.code);
                }
                $('#report-scheduled-table').find('.dataTables-search-btn').trigger('click');
            }
        });

    },

    Underwater: function(eid) {

        console.log('Core.underwater:'+eid+':');
        var winHeight=Math.floor($(window).height());
        var offsetLeft=Math.floor($('#'+eid).closest('div.wizard-select').offset().left);
        var offsetTop=Math.floor($('#'+eid).closest('div.wizard-select').offset().top)+Math.floor($('#'+eid).closest('div.wizard-select').height())+5;
        switch(eid){
            case                 'user-add-user-type' : offsetLeft = offsetLeft - Math.floor($('#'+eid).closest('div.modal-dialog').offset().left);
                                                        offsetTop = offsetTop - Math.floor($('#'+eid).closest('div.modal-dialog').offset().top);
                                                        break;
        }
        var ulWidth=Math.floor($('#'+eid).closest('div.wizard-select').width());
        $('#ul-'+eid).css({ position: 'fixed', top: offsetTop, left: offsetLeft, width: ulWidth });
        var ulHeight=Math.floor($('#ul-'+eid).height());
        var underwater=offsetTop + ulHeight - winHeight; 
        $('#ul-'+eid).width(ulWidth);
        $('#ul-'+eid).closest('td').find('.dropdown-backdrop').width(ulWidth);
        console.log('Core.underwater:'+eid+':offsetLeft:'+offsetLeft+':underwater:'+underwater+':offsetTop:'+offsetTop+':ulHeight:'+ulHeight+':'+':ulWidth:'+ulWidth+':');
        if(underwater>0){
            underwater='above';
            offsetTop=Math.floor($('#'+eid).closest('div.wizard-select').offset().top)-ulHeight-5;
            $('#ul-'+eid).css({ top: offsetTop });
        }
        console.log('Core.underwater:'+eid+':offsetLeft:'+offsetLeft+':underwater:'+underwater+':offsetTop:'+offsetTop+':ulHeight:'+ulHeight+':'+':ulWidth:'+ulWidth+':');
                                                            
    },

    Session: {

        sessionTimeout: 0,

        checkUserMinutesRemaining: 1, // minutes remaining before timeout that we check if the user is available

        availabilityChecked: false,

        lastRequest: 0,

        intervalReference: -1,

        init: function() {

            //Core.Session.sessionTimeout = $('body').data('sessionTimeout') - 1; // -1 because setInterval doesn't run the first minute
            Core.Session.setSessionTimeout($('body').data('sessionTimeout'));

            Core.Session.updateLastRequest();

            var interval = setInterval(function() {

                Core.Session.sessionTimeout = Core.Session.sessionTimeout - 1;

                console.log('################# TIMEOUT ######################## : '+Core.Session.sessionTimeout);

                // if (Core.Session.getMinutesSinceLastRequest() >= (Core.Session.sessionTimeout - Core.Session.checkUserMinutesRemaining) && Core.Session.availabilityChecked == false) {

                //     Core.Session.availabilityChecked = true;
                //     Core.Session._launchCheckUserModal();

                // } else if (Core.Session.getMinutesSinceLastRequest() >= Core.Session.sessionTimeout) {

                //     clearInterval(interval);
                //     Core.log('Redirect User to Login Page');

                //     var lastRoute = Core.Session.getLastRoute();

                //     Core.Cookie.set('last-route', lastRoute);
                //     window.location = '/logout';

                // }

                if (Core.Session.sessionTimeout<1) {

                    clearInterval(interval);
                    Core.log('Redirect User to Login Page');

                    var lastRoute = Core.Session.getLastRoute();

                    Core.Cookie.set('last-route', lastRoute);
                    if(!(repoKey)){
                        window.location = '/logout';
                    }

                }

            }, 60000);

            switch(Core.Environment.context()){

                case          'alert/contact' :
                case         'report/contact' : //Core.Ajax('contact-add-carrier','','','init');
                                                Core.Ajax('contact-add-group','','','init');
                                                break;

                case          'alert/history' :
                case             'alert/list' : Core.Ajax('alert-add-contact','','','init');
                                                Core.Ajax('alert-add-contactgroup','','','init');
                                                Core.Ajax('alert-add-landmark','','','init');
                                                Core.Ajax('alert-add-landmarkgroup','','','init');
                                                Core.Ajax('alert-add-vehicle','','','init');
                                                Core.Ajax('alert-add-vehiclegroup','','','init');
                                                break;

                                      default : console.log('Core.Environment.context():'+Core.Environment.context());
            
            }

        },

        setSessionTimeout: function(seconds) {

            seconds = (seconds == 0) ? 60 : seconds; // prevent divide by zero

            Core.Session.sessionTimeout = parseInt(seconds/60) -1; // -1 because setInterval wont fire the until first minute passes so we account for that minute by subtracting a minute
        },

        updateLastRequest: function() {
            Core.log('Session time extended', 'debug');
            Core.Session.availabilityChecked = false;
            return Core.Session.lastRequest = moment();

        },

        getMinutesSinceLastRequest: function() {

            return moment().diff(Core.Session.lastRequest, 'minutes');

        },

        getLastRoute: function(_lastRoute) {

            _lastRoute = _lastRoute || false;

            var lastRoute;

            if (_lastRoute) {
                lastRoute = _lastRoute;
            } else {
                lastRoute = window.location.pathname;
            }

            return lastRoute;
        },

        _launchCheckUserModal: function() {
            Core.log('Launch Check User Modal');
            Core.Dialog.launch('#modal-session-check', 'Are You Still There?', {
                width: '662px'
            },
            {
                hide: function() {
 
                    // we need to reach the Base Controller to extend the session on the server-side
                    $.ajax({
                        url:      '/ajax/utility/heartbeat',
                        type:     'POST',
                        dataType: 'json',
                        data:     {}
                    });

                    // no need to handle the response | this is just a heartbeat | if a code == 2 (session timeout) occurs the ajax prefilter will handle it.

                },
                hidden: function() {

                },
                show: function() {

                },
                shown: function() {

                }
            });
        }


    },

    Header: {

        initMenu: function() {

            var $navMenu  = $('header nav .dropdown-menu'),
                $dropdown = null
            ;

            $('header nav .dropdown-toggle').click(function() {

                var $self = $(this);

                $dropdown = $self.closest('.dropdown');

                if ($dropdown.is('.open')) {
                    _hideMenu();

                } else {
                    _showMenu();
                }

            });

            $navMenu.mouseleave(function() {
                _hideMenu();
            });

            function _hideMenu() {
                $navMenu.slideUp(300, function() {
                    $dropdown.removeClass('open');
                });
            }

            function _showMenu() {
                $navMenu.slideDown(300);
            }
        }
    },

    Help: {

        init: function() {

            Core.Help.showContext(Core.Environment.context());

            var $helpPanel = $('#help-panel');

            $helpPanel.find('span').click(function() {
                if ($helpPanel.is('.collapsed')) {
                    Core.Help._initShowPanel();
                } else {
                    Core.Help._initHidePanel();
                }
            });

        },

        showContext: function(contextId) {
            //TODO: implement help section
            // This will toggle various

            var $helpPanel = $('#help-panel');


        },



        _initShowPanel: function() {

            //Core.log('inside _show');

            var $helpPanel    = $('#help-panel'),
                $contentPanel = $('#main-content')
            ;

            $helpPanel.animate({
                'margin-left': '-200px',
                'width':       '200px',
                'position':    'relative'
            }, 300, function() {
                $helpPanel.removeClass('collapsed').addClass('expanded');
                $helpPanel.find('.container').fadeIn(100);
            });

            $helpPanel.find('span').animate({
                'right': '200px'
            }, 300);

            $contentPanel.animate({
                'margin-right': '200px'
            }, 300);
        },

        _initHidePanel: function() {

           // Core.log('inside _hide');

            var $helpPanel    = $('#help-panel'),
                $contentPanel = $('#main-content')
            ;

            $helpPanel.find('.container').fadeOut(100, function() {

                $helpPanel.animate({
                    'margin-left': '0px',
                    'width':       '0px'
                }, 300, function() {
                    $helpPanel.removeClass('expanded').addClass('collapsed');
                });

                $helpPanel.find('span').animate({
                    'right': '0'
                }, 300);

                $contentPanel.animate({
                    'margin-right': '0px'
                }, 300);
            });
        }
    },

    SystemMessage: {

        show: function(message, code) {
console.log('SystemMessage:show:1');
            //Core.log(code);
            //message = $.trim(Core.StringUtility.htmlEncode(Core.StringUtility.stripTags(message))) || '';
            message = $.trim(Core.StringUtility.stripTags(message)) || '';
            code = parseInt((typeof code !== 'undefined') ? code : 1);

            var $messagePanel = $('#system-message');

            if ( ($messagePanel.is('.collapsed')) && ( (message) && (message != 'undefined') && (message != 'Success') && (message != 'success') && (message != 'success') ) ) {
console.log('SystemMessage:show:2');
                Core.SystemMessage._setMessage(message, code);

                $messagePanel.removeClass('collapsed').addClass('expanded');
                $messagePanel.show().animate({
                    'height': '90px'
                }, 300, function() {
                    setTimeout(function() {
                        //Core.log('timeout reached');
                        Core.SystemMessage.hide();
                    }, 1500);
                });
            }
console.log('SystemMessage:show:3');
        },

        hide: function() {
            var $messagePanel = $('#system-message');

            if ($messagePanel.is('.expanded')) {
                $messagePanel.removeClass('expanded').addClass('collapsed');
                $messagePanel.hide().animate({
                    'height': '0'
                }, 300);
            }
        },

        _setMessage: function(message, code) {

            if (message.length == 0 || (typeof code == 'undefined')) {
                message = 'Action Failure'
            }

            $('#system-message-type').removeClass('text-success')
                                     .removeClass('text-danger')
                                     .addClass((code > 0) ? 'text-danger' : 'text-success')
                                     .text((code > 0) ? 'Error' : 'Success' )
            ;

            $('#system-message-text').text(message);

            // Core.log('Core.SystemMessage', 'group');
            Core.log(message, (code == 0) ? 'info' : 'error');
            // Core.log('Core.SystemMessage', 'groupEnd');
        },

        validation: function(validationErrors) {
            $.each(validationErrors, function(index, value) {

                /*var $element = $('#'+index).siblings('.editable-container').eq(0);

                $element.tooltip({
                    title: value,
                    placement: 'right',
                    trigger: 'manual'
                });

                $element.tooltip('show');*/




            });
        }

    },

    SystemIndicator: {

        busy: function() {

            var indicator   = $('#status'),
                img    = indicator.find('img')
            ;

            img.prop('src', '/assets/media/images/system-busy.gif');

        },

        ready: function() {
            var indicator   = $('#status'),
                img    = indicator.find('img')
            ;

            img.prop('src', '/assets/media/images/system-ready.png');

        },

        error: function() {

            var indicator   = $('#status'),
                img    = indicator.find('img')
            ;

            img.prop('src', '/assets/media/images/system-error.png');

        }
    },

    FormValidation: {

        show: function(selector, message, code) {

            selector = selector || '';

            var $selector = $(selector);

            if ($selector.length && $selector.is('.alert-form-response')) {

                code = parseInt((typeof code !== 'undefined') ? code : 1);

                switch ((typeof message).toLowerCase()) {
                    case 'object':
                        _renderWithObject();
                        break;
                    case 'string':
                        _renderWithString();
                        break;
                    default:
                        _renderWithProcessError('Invalid Message Type');
                        break;
                }
                $selector.addClass('alert');

            } else {
                _renderWithProcessError('"selector" returned nothing or missing ".alert-form-response" class');
            }

            function _renderWithObject() {

                if ($.isEmptyObject(message)) {

                    _renderWithProcessError('Message Object is Empty');

                } else if (code > 0) {

                    var messages = '<ul class="list-group">';

                    $.each(message, function(elemId, errorMessage) {

                        var $elem     = $('#'+elemId),
                            $label    = $('label').filter('[for="'+elemId+'"]'),
                            labelText = $label.text()
                        ;

                        if ($label.length) {
                            messages += '<li class="list-group-item"><strong>'+labelText+'</strong>&nbsp;'+errorMessage+'</li>';
                            $label.addClass('has-error');
                        }

                    });

                    messages += '</ul>';
                    _applyClassFromCode();
                    $selector.html(messages).show();

                } else {
                    _renderWithProcessError('Invalid Message Type for Code');
                }
            }

            function _renderWithString() {
                _applyClassFromCode();
                $selector.text(message).show();
            }

            function _renderWithProcessError(processError) {
                Core.log('Core.FormValidation.show(): '+processError, 'error');
            }

            function _applyClassFromCode() {
                if (code > 0) {
                    $selector.addClass('alert-danger').removeClass('alert-success');
                } else {
                    $selector.addClass('alert-success').removeClass('alert-danger');
                }
            }
        },

        hide: function(selector) {

            selector = selector || '';

            var $selector = $(selector);

            if ($selector.length && $selector.is('.alert-form-response')) {
                $selector.removeClass('alert-danger').removeClass('alert-success').hide();
            }

        },

        clearLabelErrorsIn: function(selector) {

            selector = selector || '';

            var $selector = $(selector);

            if ($selector.length) {
                $selector.find('.has-error').removeClass('has-error');
            }

        }


    },

    Dialog: {

        _defaultOptions: {
            trigger:  false,
            width:    'auto',
            height:   'auto',
            backdrop: true, // includes a modal-backdrop element. Alternatively, specify static for a backdrop which doesn't close the modal on click.
            keyboard: true, // Closes the modal when escape key is pressed.
            show:     true, // Shows the modal when initialized.
            remote:   false // DO NOT USE - If a remote URL is provided, content will be loaded via jQuery's load method and injected into the root of the modal element.
        },

        _defaultCallbacks: {

            show: function() {
                // This event fires immediately when the show instance method is called.
            },

            shown: function() {
                // This event is fired when the modal has been made visible to the user (will wait for CSS transitions to complete).
            },

            hide: function() {
                // This event is fired immediately when the hide instance method has been called.

                // cancel/hide any popovers
            },

            hidden: function() {
                // This event is fired when the modal has finished being hidden from the user (will wait for CSS transitions to complete).
            }
        },

        launch: function(selector, title, options, callbacks) {
            selector  = selector  || '';
            title     = title     || 'Dialog';
            options   = options   || {};
            callbacks = callbacks || {};

            var $selector = $(selector);

            if ($selector.length) {

                function _launch() {

                    options   = jQuery.extend(true, {}, Core.Dialog._defaultOptions, options);
                    //callbacks = jQuery.extend(Core.Dialog._defaultCallbacks, callbacks);

                    $selector.find('.vehicle-label, .modal-title').eq(0).html(title);

                    $selector.modal(options);
                    $selector.find('.modal-dialog').css({
                        'width': options.width

                    });

                    $selector.find('.modal-body').css({
                        'height': options.height
                    });

                    if ($.isFunction(callbacks.show)) {
                        $selector.one('show.bs.modal', function() {
                            callbacks.show()
                        });
                    }

                    if ($.isFunction(callbacks.shown)) {
                        $selector.one('shown.bs.modal', function() {
                            callbacks.shown();
                        });
                    }

                    if ($.isFunction(callbacks.hide)) {
                        $selector.one('hide.bs.modal', function() {
                            callbacks.hide();
                        });
                    }

                    if ($.isFunction(callbacks.hidden)) {
                        $selector.one('hidden.bs.modal', function() {
                            callbacks.hidden();
                        });
                    }
                }

                if (options.trigger) {
                    $('body').one(options.trigger, function() {
                        _launch();
                    });
                } else {
                    _launch();
                }


            } else {
                Core.log('Dialog Error: selector \''+selector+'\' did not return any DOM objects');
            }

        }
    },

    StringUtility: {

        htmlEncode: function(string) {
            return string.replaceAll('&', '&amp;')
                         .replaceAll('"', '&quot;')
                         .replaceAll("'", '&#39;')
                         .replaceAll('<', '&lt;')
                         .replaceAll('>', '&gt;')
            ;
        },

        capitalizeFirstLetter: function(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        },

        stripTags: function(input, allowed) {
            allowed = (((allowed || "") + "").toLowerCase()
                                             .match(/<[a-z][a-z0-9]*>/g) || [])
                                             .join('') // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
            ;
           var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
               commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
           return input.replace(commentsAndPhpTags, '').replace(tags, function($0, $1){
              return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
           });
        },

        isEllipsActive: function(element) {
            element = element || false;

            var output   = false,
                $element = $()
            ;

            if (element) {

                // if jQuery object convert to native object
                if (element instanceof jQuery) {
                   element = element.get();
                }

                $element = $(element);

                output = ($element[0].scrollWidth > $element.width());

            }

            return output;
        },

        ellipsisTruncate: function(string, characterLimit) {

            string = string || '';

            characterLimit = characterLimit || 0;

            var output = string;

            if (output.length > characterLimit && characterLimit > 0) {
                output = output.substr(0, characterLimit-1) + '...';
            }

            return output;

        },

        // date conversion to datetime string of YYYY-MM-DD HH:MM:SS
        filterStartDateConversion: function(toDay, filterDateString) {
            var year        = toDay.getFullYear();
            var month       = toDay.getMonth() + 1;
            var day         = toDay.getDate();
            var hours       = '00';
            var minutes     = '00';
            var seconds     = '00';
            var startDate   = '0000-00-00 00:00:00';
            var selected_days_ago = '0';

            if(filterDateString == 'yesterday') {
                var YesterdayDate = new Date();

                YesterdayDate.setDate(YesterdayDate.getDate() - 1);
                year    = YesterdayDate.getFullYear();
                month   = (YesterdayDate.getMonth()+1);
                day     = (YesterdayDate.getDate());
            } else if (filterDateString != 'today') {
                selected_days_ago = filterDateString.split('-')[1];
                if (selected_days_ago != '') {
                    var DayAgoDate = new Date();

                    DayAgoDate.setDate(DayAgoDate.getDate() - selected_days_ago);
                    year    = DayAgoDate.getFullYear();
                    month   = (DayAgoDate.getMonth()+1);
                    day     = (DayAgoDate.getDate());
                }
            }

            if (month < 10) {
                month = '0' + month;
            }
            if (day < 10) {
                day = '0' + day;
            }

            startDate = year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':'+ seconds;
            return startDate;
        },

        // date conversion to datetime string of YYYY-MM-DD HH:MM:SS
        filterEndDateConversion: function(toDay, filterDateString) {

            var year    = toDay.getFullYear();
            var month   = toDay.getMonth() + 1;
            var day     = toDay.getDate();
            var hours   = toDay.getHours();
            var minutes = toDay.getMinutes();
            var seconds = toDay.getSeconds();

            if(filterDateString == 'yesterday') {
                hours   = '00';
                minutes = '00';
                seconds = '00';
            }

            if (month < 10) {
                month = '0' + month;
            }
            if (day < 10) {
                day = '0' + day;
            }

            endDate = year + '-' + month + '-' + day + ' ' + ((hours != '00' && hours < 10) ? '0' : '') + hours + ':' + ((minutes != '00' && minutes < 10) ? '0' : '') + minutes + ':'+ ((seconds != '00' && seconds < 10) ? '0' : '') + seconds;
            return endDate;
        },

        formatStaticFormValue: function(stringValue) {
            stringValue = stringValue || '';

            return (stringValue) ? Core.StringUtility.stripTags(stringValue) : '<em>No Data</em>';
        },

        getRandomString: function(stringLength) {

            stringLength = parseInt(stringLength) || 10;

            var output   = '',
                possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'
            ;

            for(var i = 0; i < stringLength; i++) {
                output += possible.charAt(Math.floor(Math.random() * possible.length));
            }

            return output;
        },

        jsonToObject: function(json) {
            return $.parseJSON(json);
        },

        objectToJson: function(object) {
            return JSON.stringify(object);
        }
    },

    TimeUtility: {



    },

    Tooltip: {

        _defaultOptions: {
            html: true
        },

        init: function() {

            //$.each('.has-tooltip', function() {
            $('.has-tooltip').each(function() {
                var $self = $(this),
                    data  = $self.data()
                ;

                // ensure there is nop existing tooltip
                $self.tooltip('destroy');

                //Core.log($self);

                var options = {

                    placement: function() {
                        return (data.placement) ? data.placement : 'auto';
                    }

                };

                if ($self.parents('.btn-group, .input-group').length) {
                    $.extend(options, {
                        container: 'body'
                    });
                }

                options = $.extend(true, Core.Tooltip._defaultOptions, options);

                var $editableChild = $self.children('.form-editable').eq(0);
console.log('Core:Tooltip:init:$editableChild:"'+$editableChild+'"');
                // doesnt have and editable child
                if ( ! $editableChild.length) {
                    $self.tooltip(options);

                } else if (Core.StringUtility.isEllipsActive($editableChild)) { // has and editable child AND if has ellipses
                    $self.tooltip(options);
                }

            });

        }
    },

    Popover: {

        _defaultOptions: {
            html: true
        },

        init: function() {

            var $hasPopover = $('.has-popover');

            $hasPopover.each(function() {

                var $self    = $(this),
                    data     = $self.data(),
                    adjusted = null,
                    $content = null,
                    options  = $.extend(true, {}, Core.Popover._defaultOptions, {
                        placement: data.popoverPlacement || 'auto',
                        content:    function() {

                            if ($content == null) {

                                if (data.popoverContentMethod != 'undefined' && data.popoverContentMethod == 'clone') {

                                    // popover is reused within the same context/document
                                    $content = $('#'+data.popoverContentId).contents().clone() || 'Error: Content Not Given';

                                } else {

                                    $content = $('#'+data.popoverContentId).contents().detach() || 'Error: Content Not Given';
                                }
                            }
                            return $content;
                        },
                        container: 'body'
                    }
                );
                Core.log(options.container);

                $self.popover(options);


                // Bootstrap's Tooltip and Popover both utilize the title attribute - this is a workaround since most of our popovers target buttons with tooltips
                $self.on('shown.bs.popover', function(event) {
                    // hide over popovers
                    $hasPopover.not($self).popover('hide');

                    if ($hasPopover.not($self).length) { // if there are other popovers
                        $hasPopover.not($self).data('bs.popover').$tip.hide();
                    }

                    if ( ! $self.data('popoverTitle')) {

                        $self.data('popoverTitle', $('#'+data.popoverTitleId).contents() || 'Error: Title Not Given');
                    }

                    var $htmlTitle    = $('#'+data.popoverTitleId).contents().clone() || 'Error: Title Not Given',
                        $popover      = $self.data('bs.popover').$tip,
                        $popoverTitle = $popover.find('.popover-title'),
                        $activeModal  = $('.modal.in').eq(0),
                        popoverWidth  = data.popoverWidth,
                        $popoverDiv   = $popover.closest('.popover'),
                        $popoverArrow = $popoverDiv.find('.arrow')
                    ;

                    $popoverTitle.html($htmlTitle);

                    $popoverTitle.find('.close').eq(0).add($popover.find('.popover-cancel')).click(function() {
                    //$popover.find('.popover-cancel').click(function() {
                        $self.popover('hide');
                        // there is a bug in Bootstrap - the underlying HTML wrappers for popover DO NOT hide when $().popover('hide') is called.
                        $(this).closest('.popover').hide();
                    });

                    // if popover is in an active modal, append the popover to the modal
                    // workaround to Bootstrap's e.preventDefault on anything not inside a modal
                    if ($activeModal.length) {
                        $activeModal.append($popover.detach());
                    }


                    /* adjust width and arrow */
                    //if (data.popoverPlacement == 'bottom' && popoverWidth != 'undefined') {
                    if (typeof popoverWidth != 'undefined') {

                        $popoverDiv.css('width', popoverWidth);

                        if (adjusted === null) {
                            adjusted = (parseFloat($popoverDiv.css('left')) - (popoverWidth*0.75));
                        }

                        $popoverDiv.css('left', adjusted);
                        $popoverDiv.get(0).style.maxWidth = popoverWidth+'px';

                        if (typeof data.popoverNoPosition == 'undefined') {

                            if (typeof data.popoverPositionLeft != 'undefined') {
                                //$popoverDiv.css('left', data.popoverPositionLeft);
                                $popoverDiv.attr('style', $popoverDiv.attr('style') + ';' + 'left: '+data.popoverPositionLeft+'px !important;');
                            } else {
                                $popoverDiv.attr('style', $popoverDiv.attr('style') + ';' + 'left: auto !important;');
                            }

                            if (typeof data.popoverPositionRight != 'undefined') {
                                //$popoverDiv.css('right', data.popoverPositionRight);
                                $popoverDiv.attr('style', $popoverDiv.attr('style') + ';' + 'right: '+data.popoverPositionRight+'px !important;');
                            } else {
                                $popoverDiv.attr('style', $popoverDiv.attr('style') + ';' + 'right: auto !important;');
                            }
                        }

                        if (typeof data.popoverArrowLeftPosition != 'undefined') {
                            $popoverArrow.css('left', data.popoverArrowLeftPosition+'px');

                        }

                        /*if (typeof data.popoverArrowLeftPosition != 'undefined') {
                            $popoverArrow.css('left', data.popoverArrowLeftPosition+'px');

                        }

                        if (typeof data.popoverPositionLeft != 'undefined') {
                            //$popoverDiv.css('left', data.popoverPositionLeft);
                            $popoverDiv.attr('style', $popoverDiv.attr('style') + ';' + 'left: '+data.popoverPositionLeft+'px !important;');
                        } else {
                            $popoverDiv.attr('style', $popoverDiv.attr('style') + ';' + 'left: auto !important;');
                        }

                        if (typeof data.popoverPositionRight != 'undefined') {
                            //$popoverDiv.css('right', data.popoverPositionRight);
                            $popoverDiv.attr('style', $popoverDiv.attr('style') + ';' + 'right: '+data.popoverPositionRight+'px !important;');
                        } else {
                            $popoverDiv.attr('style', $popoverDiv.attr('style') + ';' + 'right: auto !important;');
                        }*/

                        

                    }

                    //Move Edit Address on Verification Popover window Up so it isn't cut off by viewport
                     if ($(event.currentTarget).attr('data-popover-content-id')=='popover-content-verification-edit-address'){
                        var buttonTop = $(event.currentTarget).position().top;
                        var buttonHeight = $(event.currentTarget).height();
                        $('.popover.left>.arrow').css('top','-1000px');
                        $('div.popover').css('top',buttonTop-$('div.popover').height()+110+'px');
                     }


                });

                $self.on('hidden.bs.popover', function() {
                    // used when a table-action icon invokes a popover
                    $('.editing-tr-data').removeClass('editing-tr-data')
                                         .trigger('mouseleave')
                    ;

                });

                $self.on('Core.PopoverContentChange', function() {

                    var $popover  = $self.data('bs.popover'),
                        $tip      = $popover.$tip,
                        position  = $popover.getPosition(),
                        placement = $self.data('popoverPlacement'),
                        offset    = $popover.getCalculatedOffset(placement, position, $tip.width(), $tip.height())
                    ;

                    $self.data('bs.popover').applyPlacement(offset, placement);

                });
            });
        }
    },

    Editable: {

        _defaultOptions: {

            send: 'always',

            showbuttons: 'left',

            onblur: 'submit',

            emptytext: 'Not Set',

            params: function(params) {
console.log('Core:Editable:_defaultOptions:params');

                var $control  = $('#'+params.name), // form element/control
                    $hook     = $control.parents('.hook-editable-keys').eq(0), // grabs the DOM object that holds primary keys (record ids)
                    keys      = $hook.data(), // parsed primary keys from $hook
                    data      = {}
                ;
                
                if (keys == null) { // covers some cases where the detail panel is not the parents of the form element (i.e. contact group name)
                    keys = $('#detail-panel').find('.hook-editable-keys').eq(0).data();
                }

                data['primary_keys'] = keys;
                data['id']    = params.name;
                data['value'] = params.value;

                return data;
           },

            success: function(response, newValue) {
console.log('Core:Editable:_defaultOptions:success');

                var output = null,
                    $elem  = $();

                if (typeof response != 'undefined' && response.code > 0) {
                    var ve   = response.validation_error,
                        id   = Object.keys(ve)[0]
                    ;

                    $elem = $('#'+id);

                    if ( ! $.isEmptyObject(response.validation_error)) {

                        var ve = response.validation_error,
                            id = Object.keys(ve)[0]
                        ;
                        
                        $elem = $('#'+id)
                        /*
                        var $popover = $elem.parents('div').eq(0).popover({
                            trigger:   'manual',
                            title:     '',
                            content:   ve[id]
                        }).popover('show');
                        */
                        $elem.siblings('.editable-container').find('.editable-error-block').html('').hide();

                        output = ve[id];
                        /*
                        $elem.one('hidden', function(e, reason) {
                            $popover.popover('hide');
                        });
                        */
                    }
                } else if (typeof response != 'undefined') {

                    $elem = $('#'+response.data.id);

                    // Dev Note: jQuery converts data attribute names to camelCase (i.e. data-vehicle-pk="" becomes $obj.data('vehiclePk') when getting)
                    $elem.trigger('Core.FormElementChanged', {
                        value: newValue,
                        pk:    $elem.parents('.hook-editable-keys').data(),
                        response: response
                    });
                    
                    // remove this field error is any
                    Core.Editable.removeError('#'+response.data.id);
                }
                
                // show validation errors if any
                if (! $.isEmptyObject(response.new_error)) {
                    $.each(response.new_error, function (key, error) {
                       Core.Editable.setError('#'+key, error);
                    });
                }

                // process message
                if (typeof response != 'undefined' && ! $.isEmptyObject(response.message)) {
                    // Core.SystemMessage.show(response.message, response.code);
                }

                // update any tooltips
                var $parent = $elem.parents().eq(0);
                if ($parent.is('.has-tooltip')) {
                    $parent.prop('title',newValue).tooltip('fixTitle');
                }
                return output;

            }

        },

        init: function() {
console.log('Core:Editable:init');

            // set edit mode
            $.fn.editable.defaults.mode = 'inline';

            // init each editable element
            $('.form-editable').each(function() {
                var $self = $(this),
                    data  = $self.data(),
                    options = {}
                ;

                options = $.extend(true, {}, Core.Editable._defaultOptions, {
                    disabled: (data.disabled) ? true : false
                });

                // data.required = data.required || {};

                if (data.required) {

                    //Core.log('required');

                    options = $.extend(true, {}, Core.Editable._defaultOptions, {
                        validate: function(value) {
                            var output = null;
                            if ($.trim(value) == '') {
                                output = 'This field is required';
                            }
                            return output;
                        }
                    });
                } else {
                    options = $.extend(true, {}, Core.Editable._defaultOptions, {
                        validate: function() {}
                    });
                }

                // init the input
                $self.editable(options);

                // tabbing between inputs & changing style on-the-fly
                $self.on('shown', function(event, editable) {

                    if (arguments.length == 2) { // ensure the event came from Editable NOT Popover

                        var $self = $(this),
                            $input = $self.data('editable').input.$input
                        ;

                        $input.on('keydown', function(event) {
                            if (event.which == 9) { // tab key
                                event.preventDefault();
                                if (event.shiftKey) { // tab + shift keys
                                    $self.blur()
                                        .parents().prevAll(':has(.editable):first')
                                        .find('.editable:last').editable('show')
                                    ;
                                } else {
                                    $self.blur()
                                        .parents().nextAll(':has(.editable):first')
                                        .find('.editable:first').editable('show')
                                    ;
                                }
                            }

                        });

                        // enforce max-length
                        if ($self.data('editable').input.type == 'text') {

                            $input.prop('maxlength', $self.data('maxlength'));
                        }

                        //restyle injected DOM
                        $self.next()
                             .find('button')
                             .removeClass('btn-sm')
                             .addClass('btn-xs')
                             .css({
                                'position': 'relative',
                                'top':      '5px'
                             })
                        ;

                        //$self.next().find('button').eq(1).hide();

                    }
                });
            });

            var $body = $('body');

            // visual hint to show element is editable
            $body.on('mouseenter', 'a.form-editable', function() {
console.log('Core:Editable:init:a.form-editable:mouseenter');
                var $self = $(this);
                if ( ! $self.data('disabled')) {
                    $self.after('<span class="editable-hint glyphicon glyphicon-pencil"></span>')
                }

            });
            $body.on('mouseleave', 'a.form-editable', function() {
console.log('Core:Editable:init:a.form-editable:mouseleave');
                var $self = $(this),
                    $hint = $self.siblings('.editable-hint')
                ;
                $hint.fadeOut(0, function() {
                    $hint.remove();
                });
            });
            $('a.form-editable').on('shown', function(event, editable) {
console.log('Core:Editable:init:a.form-editable:shown');
                var $self = $(this);
                   $self.parents().eq(0).tooltip('hide');
            });


        },

        setValue: function($element,value) {
console.log('Core:Editable:init:setValue:'+$element.attr('id')+'="'+value+'"');

            if ((value=='')||(value==null)) {

                switch($element.attr('id')){

                    case               'customer-address' : value='<span class="wizard-nodata" title="please click to enter data...">Address</span>';
                                                            break;
                    case                  'customer-city' : value='<span class="wizard-nodata" title="please click to enter data...">City</span>';
                                                            break;
                    case                 'customer-email' : value='<span class="wizard-nodata" title="please click to enter data...">Email</span>';
                                                            break;
                    case            'customer-first-name' : value='<span class="wizard-nodata" title="please click to enter data...">First Name</span>';
                                                            break;
                    case            'customer-home-phone' : value='<span class="wizard-nodata" title="please click to enter data...">Home Phone</span>';
                                                            break;
                    case             'customer-last-name' : value='<span class="wizard-nodata" title="please click to enter data...">Last Name</span>';
                                                            break;
                    case          'customer-mobile-phone' : value='<span class="wizard-nodata" title="please click to enter data...">Mobile Phone</span>';
                                                            break;
                    case                 'customer-state' : value='<span class="wizard-nodata" title="please click to enter data...">State</span>';
                                                            break;
                    case               'customer-zipcode' : value='<span class="wizard-nodata" title="please click to enter data...">Zip</span>';
                                                            break;
                    case                    'device-plan' : value='<span class="wizard-nodata" title="please click to enter data...">Plan</span>';
                                                            break;
                    case         'device-activation-date' : value='<span class="wizard-nodata" title="please click to enter data...">Activation Date</span>';
                                                            break;
                    case       'device-deactivation-date' : value='<span class="wizard-nodata" title="please click to enter data...">Deactivation Date</span>';
                                                            break;
                    case            'device-last-renewed' : value='<span class="wizard-nodata" title="please click to enter data...">Last Renewal Date</span>';
                                                            break;
                    case           'device-purchase-date' : value='<span class="wizard-nodata" title="please click to enter data...">Purchase Date</span>';
                                                            break;
                    case            'device-renewal-date' : value='<span class="wizard-nodata" title="please click to enter data...">Renewal Date</span>';
                                                            break;
                    case                  'device-serial' : value='<span class="wizard-nodata" title="please click to enter data...">Serial</span>';
                                                            break;
                    case                  'device-status' : value='<span class="wizard-nodata" title="please click to enter data...">Status</span>';
                                                            break;
                    case                  'vehicle-color' : value='<span class="wizard-nodata" title="please click to enter data...">Color</span>';
                                                            break;
                    case              'vehicle-installer' : value='<span class="wizard-nodata" title="please click to enter data...">Installer</span>';
                                                            break;
                    case           'vehicle-install-date' : value='<span class="wizard-nodata" title="please click to enter data...">Install Date</span>';
                                                            break;
                    case        'vehicle-install-mileage' : value='<span class="wizard-nodata" title="please click to enter data...">Install Mileage</span>';
                                                            break;
                    case          'vehicle-license-plate' : value='<span class="wizard-nodata" title="please click to enter data...">Lic Plate</span>';
                                                            break;
                    case                'vehicle-loan-id' : value='<span class="wizard-nodata" title="please click to enter data...">Loan ID</span>';
                                                            break;
                    case                   'vehicle-make' : value='<span class="wizard-nodata" title="please click to enter data...">Make</span>';
                                                            break;
                    case                  'vehicle-model' : value='<span class="wizard-nodata" title="please click to enter data...">Model</span>';
                                                            break;
                    case                   'vehicle-name' : value='<span class="wizard-nodata" title="please click to enter data...">Vehicle Name</span>';
                                                            break;
                    case                 'vehicle-serial' : value='<span class="wizard-nodata" title="please click to enter data...">Serial</span>';
                                                            break;
                    case                  'vehicle-stock' : value='<span class="wizard-nodata" title="please click to enter data...">Stock</span>';
                                                            break;
                    case                    'vehicle-vin' : value='<span class="wizard-nodata" title="please click to enter data...">Vin</span>';
                                                            break;
                    case                   'vehicle-year' : value='<span class="wizard-nodata" title="please click to enter data...">Year</span>';
                                                            break;
                                                  default : value='<span class="wizard-nodata" title="please click to enter data...">No Data</span>';
                                                            // value = '[No Data]' ;

                }

            }

            $element.html(value);

            var $parent = $element.parents().eq(0);

            if ($parent.is('.has-tooltip')) {
                $parent.prop('title', value).tooltip('fixTitle');
            }
        },

        setError: function(element, message) {
console.log('Core:Editable:init:setError');

            var $element           = $(element),
                identifier         = $element.prop('id'),
                $label             = $('label[for="'+identifier+'"]').eq(0),
                labelText          = $label.text(),
                errorHTML          = '',
                $incompleteReasons = $('#error-messages'),
                reasonCount        = $incompleteReasons.find('.reason').length,
                separator          = '<span class="reason-separator">&nbsp;&#124;&nbsp;</span>'
            ;


            message = message || '';

            if ($label.length && ! $label.is('.has-error')) {
                $label.addClass('has-error');

                errorHTML = '<span class="reason reason-'+identifier+'"><strong>'+labelText+'</strong> '+message+'</span>';

                //reasonCount = $incompleteReasons.find('.reason').length;
                //Core.log('Reason Count: '+reasonCount);

                if (reasonCount > 0) {
                    errorHTML = separator+errorHTML;
                }

                $incompleteReasons.fadeIn(300).append(errorHTML);
            }
        },

        removeError: function(element) {
console.log('Core:Editable:init:removeError');

            var $element           = $(element),
                identifier         = $element.prop('id'),
                $label             = $('label[for="'+identifier+'"]').eq(0),
                $incompleteReasons = $('#error-messages'),
                reasonCount        =  0,
                $separators        = $incompleteReasons.find('.reason-separator')
            ;

            $label.removeClass('has-error');

            $incompleteReasons.find('.reason-'+identifier).remove();

            reasonCount = $incompleteReasons.find('.reason').length;

            if (reasonCount <= 1) {
                $separators.remove();
            }

            if (reasonCount == 0) {
                $incompleteReasons.fadeOut(300);
                $incompleteReasons.trigger('Core.ErrorsResolved');
            }
        },
        
        disable: function() {
console.log('Core:Editable:init:disable');

            var $body = $('body');
            $body.find('.form-noneditable').editable('disable');
        
        }
    },

    DataTable: {

        _defaultOptions: {
            'sPaginationType': 'full_numbers',
            'sDom' : '<"clearfix"<"pull-left datatable-tool"><"pull-right datatable-tool"p>>t<"clearfix"<"pull-left"i><"pull-right"p>>',
            'iDisplayLength': 10,
            'oLanguage' : {
                'oPaginate' : {
                    'sFirst'       : '<div class="datatable-pagination-fix glyphicon glyphicon-fast-backward"></div>',
                    'sPrevious'    : '<div class="datatable-pagination-fix glyphicon glyphicon-backward"></div>',
                    'sNext'        : '<div class="datatable-pagination-fix glyphicon glyphicon-forward"></div>',
                    'sLast'        : '<div class="datatable-pagination-fix glyphicon glyphicon-fast-forward"></div>',
                    'sInfoEmpty'   : 'No data available.',
                    'sInfoFiltered': ' ( filtered from _MAX_ )'
                }
            },
            'bAutoWidth': false,
            'sAjaxDataProp': 'data', // property to look for in than AJAX/JSON response
            'sServerMethod': 'POST'//function() { return Core.Config.ajaxType} 
        },

        pop: function(pid,text,value,select) {

            var val=Array();

            if(text && text!='undefined'){
                $.each(text.split(';'), function( k, v ) {
                    if(v){
console.log('Core.DataTable.pop:text:'+v);
                        val=v.split('=');
                        if(val[0]){
                            $('#'+pid).find('.'+val[0]).text(val[1]);
                        }
                    }
                });
            }

            if(value && value!='undefined'){
                $.each(value.split(';'), function( k, v ) {
                    if(v){
console.log('Core.DataTable.pop:value:'+v);
                        val=v.split('=');
                        if(val[0]){
                            $('#'+val[0]).val(val[1]);
                        }
                    }
                });
            }

            if(select && select!='undefined'){
                $.each(select.split(';'), function( k, v ) {
                    if(v){
console.log('Core.DataTable.pop:select:'+v);
                        val=v.split('=');
                        if(val[0]){
                            $('#'+val[0]).val(val[1]).change();
                        }
                    }
                });
            }

        },

        createUpdate: function(fid,noskip) {
console.log('Core.DataTable.createUpdate:fid:'+fid);

            var action='';

            var category='';
            var cellcarrier_id='';
            var cellnumber='';
            var city='';
            var coordinates='';
            var confirm='';
            var contact='';
            var contactgroup='';
            var contactmethod='';
            var contactmode='';
            var country='';
            var days='';
            var duration='';
            var email='';
            var endhour=0;
            var firstname='';
            var group='';
            var hours='';
            var landmark='';
            var landmarkgroup='';
            var landmarkgroupname='';
            var landmarkmode='';
            var landmarktrigger='';
            var landmarktype='';
            var landmarktypetext='';
            var lastname='';
            var latitude='';
            var location='';
            var longitude='';
            var name='';
            var overspeed='';
            var p1='';
            var p2='';
            var p3='';
            var p4='';
            var p5='';
            var p6='';
            var p7='';
            var p8='';
            var p9='';
            var p10='';
            var p11='';
            var p12='';
            var p13='';
            var p14='';
            var p15='';
            var p16='';
            var p17='';
            var p18='';
            var p19='';
            var p20='';
            var p21='';
            var p22='';
            var p23='';
            var password='';
            var phone='';
            var radius='';
            var shape='';
            var state='';
            var starthour=0;
            var street_address='';
            var title='';
            var type='';
            var url='/ajax/core/ajax';
            var username='';
            var usertype='';
            var usertype_id='';
            var vehicle='';
            var vehiclegroup='';
            var vehiclegroups='';
            var vehiclegroupname='';
            var vehiclemode='';
            var zip='';

            switch(fid){

                case          'alert-add' : contact = $('#alert-add-contact').val();
                                            contactgroup = $('#alert-add-contactgroup').val();
                                            contactmode = $('#alert-add-contactmode').val();
                                            contactmethod = $('#alert-add-contactmethod').val();
                                            days = $('#alert-add-days').val();
                                            duration = $('#alert-add-duration').val();
                                            hours = $('#alert-add-hours').val();
                                            landmark = $('#alert-add-landmark').val();
                                            landmarkgroup = $('#alert-add-landmarkgroup').val();
                                            landmarkmode = $('#alert-add-landmarkmode').val();
                                            landmarktrigger = $('#alert-add-landmarktrigger').val();
                                            overspeed = $('#alert-add-overspeed').val();
                                            title = $('#alert-add-name').val();
                                            type = $('#alert-add-type').val();
                                            vehicle = $('#alert-add-vehicle').val();
                                            vehiclegroup = $('#alert-add-vehiclegroup').val();
                                            vehiclemode = $('#alert-add-vehiclemode').val();
                                            if(!(title)){ alert('Alert Name Missing'); }
                                            else if((landmarkmode=='single')&&(!(landmark))){ alert('Landmark Name Missing'); }
                                            else if((landmarkmode=='group')&&(!(landmarkgroup))){ alert('Landmark Group Missing'); }
                                            else if((vehiclemode=='single')&&(!(vehicle))){ alert('Vehicle Name Missing'); }
                                            else if((vehiclemode=='group')&&(!(vehiclegroup))){ alert('Vehicle Group Missing'); }
                                            else if((contactmode=='single')&&(!(contact))){ alert('Contact Name Missing'); }
                                            else if((contactmode=='group')&&(!(contactgroup))){ alert('Contact Group Missing'); }
                                            else { action = fid; }
                                            break;

                case        'contact-add' : firstname = $('#contact-add-first-name').val();
                                            lastname = $('#contact-add-last-name').val();
                                            email = $('#contact-add-email').val();
                                            cellcarrier_id = $('#contact-add-carrier').val();
                                            cellnumber = $('#contact-add-cellnumber').val();
                                            contactgroup = $('#contact-add-group').val();
                                            if(!(firstname)){   alert('First Name Missing'); 
                                            } else if(!(lastname)){   alert('Last Name Missing');
                                            } else if(!(email)){   alert('Email Address is Missing'); 
                                            } else if((cellnumber)&&(!(cellcarrier_id))){   alert('Mobile Service Provider Missing');
                                            // } else if(!(cellnumber)){   alert('Mobile Number Missing');
                                            } else { action = fid; }
                                            break;

                case   'contactgroup-add' : name = $('#contactgroup-add-name').val();
                                            if(!(name)){   alert('Group Name Missing'); }
                                            else { action = fid; }
                                            break;

                case       'landmark-add' : city = $('#landmark-add-city').val();
                                            country = $('#landmark-add-country').val();
                                            group = $('#landmark-add-group').val();
                                            landmarktype = $('#landmark-add-type').val();
                                            landmarktypetext = $('#landmark-add-type-other').val();
                                            latitude = $('#landmark-add-latitude').val();
                                            longitude = $('#landmark-add-longitude').val();
                                            radius = $('#landmark-add-radius').val();
                                            shape = $('#landmark-add-shape').val();

console.log('shape:'+shape);
                                            if (shape == 'rectangle' || shape == 'polygon' || shape == 'square') { // prep coordinates for rectangle, polygon, and square

                                                var map = Landmark.Common.addmap,
                                                    buf = [];
                                                // get the coordinates of the temp polygon as an array of objects, each with two properties called 'latitude' and 'longitude'
                                                // (i.e. [{latitude: 12.345, longitude: 45.56}, {latitude: 34.45, longitude: 56.788}, ...]) 
console.log('pointsUpdate');
console.log(pointsUpdate);
                                                if ((shape == 'rectangle' || shape == 'square')) {
                                                    if (pointsUpdate.length == 4) { // rectangle or square needs 4 points to connect
                                                        coordinates = pointsUpdate;
                                                    } else {
                                                        pointsUpdate = Map.getTempPolygonPoints(map);
console.log(pointsUpdate);
                                                        $.each(pointsUpdate, function (key,val) {
                                                            if((val.latitude)&&(val.longitude)){
                                                                if(!(buf)){
                                                                    buf[0] = val.latitude;
                                                                    buf[1] = val.longitude;
                                                                }
                                                                if(coordinates){
                                                                    coordinates += ', '; 
                                                                }
                                                                coordinates += val.latitude+' '+val.longitude;
                                                            }
                                                        });
                                                        if((buf[0])&&(buf[1])){
                                                            coordinates += buf[0]+' '+buf[1]; 
                                                        }
                                                    }
                                                } else if (shape == 'polygon') { // polygon needs at least 3 points to connect (i.e triangle)
                                                    coordinates = '';
                                                    if (pointsUpdate.length >= 3) {
                                                        $.each(pointsUpdate, function (key,val) {
                                                            buf = val.split(' ');
                                                            if(coordinates){
                                                                coordinates += ', '; 
                                                            }
                                                            if((buf[0])&&(buf[1])){
                                                                coordinates += buf[0]+' '+buf[1]; 
                                                            }
                                                        });
                                                    }
                                                }

                                            } else {                                                                // prep coordinates for circle
                                                addMapCoordinates = [{latitude: latitude, longitude: longitude}];
                                                coordinates[0] = addMapCoordinates;
                                            }
console.log('coordinates... ');
console.log(coordinates);
                                            state = $('#landmark-add-state').val();
                                            street_address = $('#landmark-add-street_address').val();
                                            title = $('#landmark-add-name').val();
                                            type = $('#landmark-add-category').val();
                                            zip = $('#landmark-add-zip').val();
                                            if(!(category)){ category='0'; }
                                            if(!(radius)){ radius='330'; }
                                            if(!(shape)){ shape='circle'; }
                                            if(!(title)){   alert('Landmark Name Missing'); }
                                            else if(!(latitude)){ alert('Latitude Missing'); }
                                            else if(!(longitude)){ alert('Longitude Missing'); }
                                            else if(!(group)){ alert('Group Missing'); }
                                            else { action = fid; url='/ajax/landmark/saveLandmark'; }
                                            break;

                case  'landmarkgroup-add' : landmarkgroupname = $('#landmarkgroup-add-name').val();
                                            console.log('#landmarkgroup-add-name:'+landmarkgroupname);
                                            if(!(landmarkgroupname)){   alert('Landmark Group Name Missing'); }
                                            else { action = fid; url='/ajax/landmark/addLandmarkGroup'; }
                                            break;

                case    'repo-create-add' : email = $('#repo-create-email').val();
                                            phone = $('#repo-create-phone').val();
                                            name = $('#repo-create-name').val();
                                            if(!(email)){   
                                                alert('Email Address Missing');
                                            } else { 
                                                var repoArray = {
                                                    email: email,
                                                    phone: phone,
                                                    name: name
                                                };
                                                Core.Ajax('repo',repoArray,currentUnitId,'repo');
                                                $('.repo-create-cancel').trigger('click');
                                            }
                                            break;

                case           'user-add' : firstname = $('#user-add-first-name').val();
                                            lastname = $('#user-add-last-name').val();
                                            username = $('#user-add-username').val();
                                            password = $('#user-add-password').val();
                                            confirm = $('#user-add-confirm').val();
                                            usertype_id = $('#user-add-user-type').val();
                                            email = $('#user-add-email').val();
                                            cellcarrier_id = $('#user-add-carrier').val();
                                            cellnumber = $('#user-add-mobile-number').val();
                                            vehiclegroups = [];
                                            $('#user-add-group').find('li.active').each( function( ) {
                                                vehiclegroups.push($(this).attr('id'));
                                            });
                                            if(!(firstname)){   alert('First Name Missing'); 
                                            } else if(!(lastname)){   alert('Last Name Missing'); 
                                            } else if(!(username)){   alert('Username Missing'); 
                                            } else if(!(usertype_id)){   alert('User Type Missing'); 
                                            // } else if(!(email)){   alert('Email Address is Missing'); 
                                            } else if((cellnumber)&&(!(cellcarrier_id))){   alert('Mobile Service Provider Missing'); 
                                            // } else if(!(cellnumber)){   alert('Mobile Number Missing'); 
                                            } else if(!(password)){   alert('Password Missing'); 
                                            } else if(confirm!=password){   alert('Confirm does not match Password'); 
                                            } else { action = fid; }
                                            break;

                case      'user-type-add' : usertype = $('#user-type-add-name').val();
                                            if(!(usertype)){   alert('User Type Name Missing'); }
                                            else { action = fid; }
                                            break;

                case     'user-type-edit' : usertype = $('#user-type-edit-name').val();
                                            usertype_id = $('#user-type-edit-name').attr('title');
                                            if(!(usertype)){   alert('User Type Name Missing'); }
                                            else if(!(usertype_id)){   alert('SYSTEM ERROR: User Type Id Missing... please contact Technical Support'); }
                                            else { action = fid; }
                                            break;

                case  'vehicle-group-add' : vehiclegroupname = $('#vehicle-group-add-name').val();
                                            if(!(vehiclegroupname)){   alert('Vehicle Group Name Missing'); }
                                            else {
                                                $('#vehicle-group-table').find('dataTables-length').val('-1');
                                                action = fid; 
                                                url='/ajax/vehicle/addVehicleGroup';
                                                popupUnitGroup=1;
                                            }
                                            break;

                                  default : console.log('Core.createUpdate:'+fid);

            }

            if((action)&&(!(ajaxSkip))||(noskip)){
console.log('Core.DataTable.createUpdate:action:1:'+action);

                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: action,
                        category: category,
                        cellcarrier_id: cellcarrier_id,
                        cellnumber: cellnumber,
                        city: city,
                        coordinates: coordinates,
                        confirm: confirm,
                        contact: contact,
                        contactgroup: contactgroup,
                        contactmethod: contactmethod,
                        contactmode: contactmode,
                        country: country,
                        days: days,
                        duration: duration,
                        email: email,
                        endhour: endhour,
                        firstname: firstname,
                        group: group,
                        hours: hours,
                        lastname: lastname,
                        landmark: landmark,
                        landmarkgroup: landmarkgroup,
                        landmarkgroupname: landmarkgroupname,
                        landmarkmode: landmarkmode,
                        landmarktrigger: landmarktrigger,
                        landmarktype: landmarktype,
                        landmarktypetext: landmarktypetext,
                        latitude: latitude,
                        location: location,
                        longitude: longitude,
                        name: name,
                        overspeed: overspeed,
                        p1: p1,
                        p2: p2,
                        p3: p3,
                        p4: p4,
                        p5: p5,
                        p6: p6,
                        p7: p7,
                        p8: p8,
                        p9: p9,
                        p10: p10,
                        p11: p11,
                        p12: p12,
                        p13: p13,
                        p14: p14,
                        p15: p15,
                        p16: p16,
                        p17: p17,
                        p18: p18,
                        p19: p19,
                        p20: p20,
                        p21: p21,
                        p22: p22,
                        p23: p23,
                        password: password,
                        radius: radius,
                        shape: shape,
                        state: state,
                        starthour: starthour,
                        street_address: street_address,
                        title: title,
                        type: type,
                        username: username,
                        usertype_id: usertype_id,
                        usertype: usertype,
                        vehicle: vehicle,
                        vehiclegroupname: vehiclegroupname,
                        vehiclegroup: vehiclegroup,
                        vehiclegroups: vehiclegroups,
                        vehiclemode: vehiclemode,
                        zip: zip
                    },
                    success: function(responseData) {
                        // alert('responseData.alert_id:'+responseData.alert_id);
console.log('Core.DataTable.createUpdate:Ajax:'+url+':'+responseData.action+':'+responseData.code+':'+responseData.message);
                        ajaxSkip='';
                        switch(responseData.action){

                            case           'alert' :    alert(responseData.message);
                                                        break;
                            
                            case       'alert-add' :    $('#alert-add-cancel').trigger('click');
                                                        switch(Core.Environment.context()){
                                                            case             'alert/list' : Core.DataTable.pagedReport('alert-list-table');
                                                                                            break;
                                                                                  default : window.location = '/alert/list' ;
                                                        }
                                                        // alert('New Alert Successfully Created');
                                                        break;

                            case     'contact-add' :    $('#contact-add-cancel').trigger('click');
                                                        // Core.DataTable.pagedReport('contacts-contacts-table');
                                                        $('.contacts-list').trigger('click');
                                                        // alert('New User Successfully Created');
                                                        break;

                           case 'contactgroup-add' :    $('#contactgroup-add-cancel').trigger('click');
                                                        // Core.DataTable.pagedReport('contacts-groups-table');
                                                        $('.contactgroups-list').trigger('click');
                                                        // alert('New User Successfully Created');
                                                        break;

                            case    'landmark-add' :    $('#landmark-add-cancel').trigger('click');
                                                        switch(Core.Environment.context()){
                                                            case       'landmark/verification' : Core.DataTable.pagedReport('landmark-verification-table');
                                                                                                 break;
                                                            case               'landmark/list' : Core.DataTable.pagedReport('landmark-list-table');
                                                                                                 break;
                                                            case                'landmark/map' : Core.DataTable.secondarySidepanelScroll();
                                                                                                 break;
                                                        }
console.log(responseData.message);
                                                        // alert('New Landmark Successfully Created');
                                                        break;

                        case   'landmarkgroup-add' :    $('#landmarkgroup-add-cancel').trigger('click');
                                                        Core.DataTable.pagedReport('landmark-group-table');
                                                        // alert('New Landmark Group Successfully Created');
                                                        break;

                            case        'user-add' :    $('#user-add-cancel').trigger('click');
                                                        Core.DataTable.pagedReport('users-users-table');
                                                        // alert('New User Successfully Created');
                                                        break;

                            case   'user-type-add' :    $('#user-type-add-cancel').trigger('click');
                                                        Core.DataTable.pagedReport('users-type-table');
                                                        // alert('New User Successfully Created');
                                                        break;

                            case  'user-type-edit' :    $('#user-type-edit-cancel').trigger('click');
                                                        Core.DataTable.pagedReport('users-type-table');
                                                        // alert('New User Successfully Created');
                                                        break;

                        case    'vehiclegroup-add' :    $('#vehicle-group-add-cancel').trigger('click');
                                                        // Core.DataTable.pagedReport('vehicle-group-table');
                                                        // alert('New Vehicle Group Successfully Created');
                                                        window.location = '/vehicle/group';
                                                        break;

                        case                'skip' :    // do nothing
                                                        break;

                        case              'logout' :    
                                           default :    console.log('### LOGOUT ### '+responseData.action+':'+responseData.code+':'+responseData.message);
                                                        alert(responseData.action);
                                                        if(!(repoKey)){
                                                            window.location = '/logout';
                                                        }

                                                        
                        }
                        createUpdateCount=0;
                    }
                });

console.log('Core.DataTable.createUpdate:action:2:'+action);
            }

        },

        pagedReport: function(pid,pag,noskip) {
console.log('Core.DataTable.pagedReport:'+pid+':'+pag+':'+noskip);

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

                var sidebarAlertAlert           = $('#sidebar-alert-alert').val();
                var sidebarAlertType            = $('#sidebar-alert-type').val();
                var sidebarContactMode          = $('#sidebar-contact-mode').val();
                var sidebarContactGroup         = $('#sidebar-contact-group').val();
                var sidebarContactMethod        = $('#sidebar-contact-method').val();
                var sidebarContactSingle        = $('#sidebar-contact-single').val();
                var sidebarDateRange            = $('#sidebar-date-range').val();
                var sidebarLandmarkCategories   = $('#sidebar-landmark-categories').val();
                var sidebarLandmarkGroup        = $('#sidebar-landmark-group').val();
                var sidebarReason               = $('#sidebar-reason').val();
                var sidebarReportType           = $('#sidebar-report-type').val();
                var sidebarTerritoryType        = $('#sidebar-territory-type').val();
                var sidebarTriggeredLast        = $('#sidebar-triggered-last').val();
                var sidebarVehicleSingle        = $('#sidebar-vehicle-single').val();
                var sidebarVehicleGroup         = $('#sidebar-vehicle-group').val();
                var sidebarVehicleStatus        = $('#sidebar-vehicle-status').val();
                var sidebarVerification         = $('#sidebar-verification').val();

                var unit_id = '';
                var activeLi = $('.sub-panel-items').find('li').filter('.active').attr('id');
                if (activeLi) {
                    unit_id=activeLi.split('-')[2];
console.log('############################# unit_id = "'+unit_id+'" (li)');
                } else if(currentUnitId) {
                    unit_id = currentUnitId;
console.log('############################# unit_id = "'+unit_id+'" (currentUnitId)');
                }

                var duration = $('#stops-duration').val();
                var date_range = $('#stops-date-range').val();

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

    console.log('Core:DataTable:pagedReport:pid:'+pid+':length:'+length+':search:"'+search+'":date_range:"'+date_range+'"');
    console.log('Core:DataTable:pagedReport:'+pid);
    console.log('Core:DataTable:pagedReport:pageCount:'+pageCount);
    console.log('Core:DataTable:pagedReport:pageTotal:'+pageTotal);
    console.log('breadcrumbs:'+breadcrumbs);

                $.ajax({
                    url: '/ajax/report/getFilteredScheduleReports',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        breadcrumbs: breadcrumbs,
                        duration: duration,
                        daterange: date_range,
                        length: length,
                        pag: pag,
                        pid: pid,
                        repoKey: repoKey,
                        search: search,
                        pageCount: pageCount,
                        pageTotal: pageTotal,
                        sidebarAlertAlert: sidebarAlertAlert,
                        sidebarAlertType: sidebarAlertType,
                        sidebarContactGroup: sidebarContactGroup,
                        sidebarContactMethod: sidebarContactMethod,
                        sidebarContactMode: sidebarContactMode,
                        sidebarContactSingle: sidebarContactSingle,
                        sidebarDateRange: sidebarDateRange,
                        sidebarLandmarkCategories: sidebarLandmarkCategories,
                        sidebarLandmarkGroup: sidebarLandmarkGroup,
                        sidebarReason: sidebarReason,
                        sidebarReportType: sidebarReportType,
                        sidebarTerritoryType: sidebarTerritoryType,
                        sidebarTriggeredLast: sidebarTriggeredLast,
                        sidebarVehicleGroup: sidebarVehicleGroup,
                        sidebarVehicleSingle: sidebarVehicleSingle,
                        sidebarVehicleStatus: sidebarVehicleStatus,
                        sidebarVerification: sidebarVerification,
                        unit_id: unit_id
                    },
                    success: function(responseData) {
                        breadcrumbs='';
                        ajaxSkip='';
// console.log('Core.DataTable.pagedReport:'+responseData.code+':'+responseData.message);
                        if(responseData.pid){
// console.log('Core.DataTable.pagedReport:responseData.pid:'+responseData.pid);
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
                            switch(responseData.pid){

                                case           'stops-report-all' :
                                case        'stops-report-recent' : if(responseData.breadcrumbs>0){
                                                                        var breadcrumbMap = Vehicle.Map.map;
                                                                        switch(Core.Environment.context()){
                                                                            case                'vehicle/list' : breadcrumbMap = Vehicle.Map.map;
                                                                                                                 break;
                                                                        }
                                                                        var arrayCnt=0;
                                                                        var labelCnt=responseData.breadcrumbs;
                                                                        var markerOptions = {} ;
                                                                        Map.clearMarkers(breadcrumbMap);
                                                                        Map.resetMap(breadcrumbMap);
                                                                        Map.resize(breadcrumbMap);
                                                                        $.each( responseData.breadcrumbtrail, function( key, breadcrumb ) {

                                                                            if((1==1)||((breadcrumb.show)&&(breadcrumb.show!='undefined'))){

    console.log('breadcrumbtrail:'+key+':'+arrayCnt+':'+labelCnt+':'+breadcrumb.address);
    console.log(breadcrumb.eventname);
                                                                                switch(responseData.pid){
                                                                                    case           'stops-report-all' : breadcrumb.event_type = 'all' ;
                                                                                                                        break;
                                                                                    case      'stops-report-frequent' : breadcrumb.event_type = 'frequent' ;
                                                                                                                        break;
                                                                                    case        'stops-report-recent' : breadcrumb.event_type = 'recent' ;
                                                                                                                        break;
                                                                                }
                                                                                breadcrumb.unitname = $('#vehicle-map-html-modal-device').html();
                                                                                breadcrumb.formatted_address = breadcrumb.address;

                                                                                markerOptions = {
                                                                                    id: arrayCnt,
                                                                                    type: 'temp',
                                                                                    name: labelCnt,
                                                                                    latitude: breadcrumb.latitude,
                                                                                    longitude: breadcrumb.longitude,
                                                                                    eventname: breadcrumb.eventname, // used in map class to get vehicle marker color
                                                                                    click: function() {
                                                                                        // alert(breadcrumb.address);
                                                                                        Map.openInfoWindow(breadcrumbMap, 'quick_history', breadcrumb.latitude, breadcrumb.longitude, breadcrumb);
                                                                                    }
                                                                                };
                                                                                Map.addMarker(breadcrumbMap, markerOptions, false);

                                                                                arrayCnt++;
                                                                                labelCnt--;

                                                                            }

                                                                        });

                                                                        // Map.updateMapBound(breadcrumbMap);
                                                                        setTimeout("Map.updateMapBound(Vehicle.Map.map, true)",1);
                                                                        // Map.updateMapBoundZoom(breadcrumbMap, true);

                                                                    }
                                                                    if(!(repoKey)){
                                                                        Vehicle.Common.DetailPanel.open('tab-quick-history')
                                                                    }
                                                                    window.setTimeout('Core.Viewport.adjustLayout()',1);
                                                                    break;

                                case      'stops-report-frequent' : if(responseData.breadcrumbs>0){
                                                                        var breadcrumbMap = Vehicle.Map.map;
                                                                        switch(Core.Environment.context()){
                                                                            case                'vehicle/list' : breadcrumbMap = Vehicle.Map.map;
                                                                                                                 break;
                                                                        }
                                                                        var arrayCnt=0;
                                                                        var labelCnt=responseData.breadcrumbs;
                                                                        var markerOptions = {} ;
                                                                        Map.clearMarkers(breadcrumbMap);
                                                                        Map.resetMap(breadcrumbMap);
                                                                        Map.resize(breadcrumbMap);
                                                                        $.each( responseData.breadcrumbtrail, function( key, breadcrumb ) {

                                                                            if((1==1)||((breadcrumb.show)&&(breadcrumb.show!='undefined'))){

    console.log('breadcrumbtrail:'+key+':'+arrayCnt+':'+labelCnt+':'+breadcrumb.address);
    console.log(breadcrumb.eventname);
                                                                                switch(responseData.pid){
                                                                                    case           'stops-report-all' : breadcrumb.event_type = 'all' ;
                                                                                                                        break;
                                                                                    case      'stops-report-frequent' : breadcrumb.event_type = 'frequent' ;
                                                                                                                        break;
                                                                                    case        'stops-report-recent' : breadcrumb.event_type = 'recent' ;
                                                                                                                        break;
                                                                                }
                                                                                breadcrumb.unitname = $('#vehicle-map-html-modal-device').html();
                                                                                breadcrumb.formatted_address = breadcrumb.address;

                                                                                markerOptions = {
                                                                                    id: arrayCnt,
                                                                                    type: 'temp',
                                                                                    name: breadcrumb.mappoint,
                                                                                    latitude: breadcrumb.latitude,
                                                                                    longitude: breadcrumb.longitude,
                                                                                    eventname: breadcrumb.eventname, // used in map class to get vehicle marker color
                                                                                    click: function() {
                                                                                        // alert(breadcrumb.address);
                                                                                        Map.openInfoWindow(breadcrumbMap, 'quick_history', breadcrumb.latitude, breadcrumb.longitude, breadcrumb);
                                                                                    }
                                                                                };
                                                                                Map.addMarker(breadcrumbMap, markerOptions, false);

                                                                                arrayCnt++;
                                                                                labelCnt--;

                                                                            }

                                                                        });

                                                                        // Map.updateMapBound(breadcrumbMap);
                                                                        setTimeout("Map.updateMapBound(Vehicle.Map.map, true)",1);
                                                                        // Map.updateMapBoundZoom(breadcrumbMap, true);

                                                                    }
                                                                    if(!(repoKey)){
                                                                        Vehicle.Common.DetailPanel.open('tab-quick-history')
                                                                    }
                                                                    window.setTimeout('Core.Viewport.adjustLayout()',1);
                                                                    break;

                                case 'verification-report-recent' : Vehicle.Common.DetailPanel.open('tab-verification')
                                                                    window.setTimeout('Core.Viewport.adjustLayout()',1);
                                                                    break;

                                                          default : Core.reportScroll();


                            }
console.log('$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$');
                            switch(Core.Environment.context()){
                                case                'vehicle/list' : switch(responseData.pid){
                                                                        case           'stops-report-all' :
                                                                        case      'stops-report-frequent' :
                                                                        case        'stops-report-recent' :
                                                                        case 'verification-report-recent' : setTimeout("Core.DataTable.pagedReportFix('"+responseData.pid+"')",1000);
                                                                                                            break;
                                                                     }
                                                                     break;
                            }
                        } else {
console.log('NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! ');
console.log(responseData);
                            if(!(repoKey)){
                                window.location = '/logout';
                            }
                        }

                        if((popupUnitGroup)&&(responseData.unitgroup.unitgroup_id)){
                            popupUnitGroup='';
                            $('#edit-unitgroup-'+responseData.unitgroup.unitgroup_id).trigger('click');
                            setTimeout("$('#edit-vehicle-group-title-edit').data('id',"+responseData.unitgroup.unitgroup_id+"');");
                        }

                    }
                });

            }
        },

        pagedReportFix: function(pid) {
            console.log('pagedReportFix');
            var rTop = $('#'+pid).find('.panel-report-scroll').offset().top;
            var mTop = $('#'+pid).closest('.modal-content').offset().top;
            var mHeight = $('#'+pid).closest('.modal-content').height();
            var rHeight = mTop + mHeight - rTop - 10;
            $('#'+pid).find('.panel-report-scroll').height(rHeight);
        },

        secondarySidepanelScroll: function(skipsearch,all) {
            $('#secondary-sidebar-scroll').empty();
            $('#uid-none').trigger('click');
            greenCount=11;
            setTimeout('Core.DataTable.secondarySidepanelScrollWait(\''+skipsearch+'\',\''+all+'\');',100);
        },

        secondarySidepanelScrollWait: function(skipsearch,all) {
            if(greenCount<5){
                if(greenCount>0){
                    Core.DataTable.secondarySidepanelScrollGo(skipsearch,all);
                    greenCount=-1;
                }
            } else {
                setTimeout('Core.DataTable.secondarySidepanelScrollWait(\''+skipsearch+'\',\''+all+'\');',100);
                greenCount--;
            }
        },

        secondarySidepanelScrollGo: function(skipsearch,all) {            
console.log('Core.DataTable.secondarySidepanelScroll:'+skipsearch+':'+all);

            var pid='';
            switch(Core.Environment.context()){
                case                'vehicle/list' : 
                case                 'vehicle/map' : pid='options-vehicles';
                                                     break;
                case                'landmark/map' : pid='options-landmarks';
                                                     break;
            }
            if(pid){
                $('#'+pid).empty();
                $('#'+pid).append('<li>downloading...</li>');
            }
            $('#uid-none').trigger('click');

            var sidebarSearch               = $('#sidebar-search-term').val();
            var sidebarLandmarkCategories   = $('#sidebar-landmark-categories').val();
            var sidebarLandmarkGroup        = $('#sidebar-landmark-group').val();
            var sidebarVehicleGroup         = $('#sidebar-vehicle-group').val();
            var sidebarVehicleStatus        = $('#sidebar-vehicle-status').val();

            if (all=='undefined') {
                all='';
            }
            if (skipsearch=='undefined') {
                skipsearch='';
            }

            if (skipsearch) {
                sidebarSearch = '' ;
                $('#sidebar-search-term').val('');
            }

            $('.quick-icon-active').removeClass('quick-icon-active');
console.log('sidebarVehicleStatus:'+sidebarVehicleStatus);
            switch(sidebarVehicleStatus){
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
                                      default : if($('#metric-'+sidebarVehicleStatus).attr('id')){
                                                  $('#metric-'+sidebarVehicleStatus).addClass('quick-icon-active');
                                                }
            }

            $.ajax({
                url: '/ajax/report/getFilteredScheduleReports',
                type: 'POST',
                dataType: 'json',
                data: {
                    all: all,
                    pid: pid,
                    search: sidebarSearch,
                    sidebarLandmarkGroup: sidebarLandmarkGroup,
                    sidebarLandmarkCategories: sidebarLandmarkCategories,
                    sidebarVehicleGroup: sidebarVehicleGroup,
                    sidebarVehicleStatus: sidebarVehicleStatus
                },
                success: function(responseData) {
console.log('Core.DataTable.secondarySidepanelScroll:A:'+responseData.code+':'+responseData.message);
                    if(responseData.pid){
console.log('Core.DataTable.secondarySidepanelScroll:B:responseData.pid:'+responseData.pid);
                        $('#'+responseData.pid).empty();
                        $('#'+responseData.pid).append(responseData.lis);
                        $('#uid-none').trigger('click');
                    }
console.log('responseData.message:'+responseData.message);
console.log('responseData.alert:'+responseData.alert);
                    if(allNone){
                        allNoneNewList=1;
                        $('#drill').trigger('click');
                    }
                }
            });

        },

        init: function(id, rows, extraOptions) {
console.log('Core.DataTable.init()');
            id   = id || false;
            id   = id.replace('#', '');
            rows = rows || 10;
            extraOptions = extraOptions || {};

            var $table     = $('#'+id),
                output     = false,
                $dataTable = $()
            ;

            //Core.log(id);
            //console.trace();
            if ($table.prop('tagName') != undefined && $table.prop('tagName').toLowerCase() == 'table') {

                extraOptions['iDisplayLength'] = rows;

                $.extend( $.fn.dataTableExt.oStdClasses, {
                    "sSortAsc": "header headerSortDown",
                    "sSortDesc": "header headerSortUp",
                    "sSortable": "header"
                } );

                // merge in custom options
                var options = $.extend(true, {}, Core.DataTable._defaultOptions, extraOptions);

                // init the table
                $dataTable = $table.dataTable(options);

                // merge in custom functions
                $.extend($dataTable, {
                    // allows for easy adding of a row and returning a jQuery object of the added row
                    addData: function(data) {
                        return $($dataTable.fnGetNodes(parseInt($dataTable.fnAddData(data))));
                    }
                });

                // add some bootstrap classes
                $('#'+id+'_length label select').addClass('form-control');
                $('#'+id+'_filter label input').addClass('form-control');
                //$('#'+id+'_filter label').contents().unwrap(); //strip off that label tag Bootstrap doesn't like

            }

            return $dataTable;
        }

    },

    DatePicker: {

        init: function() {

            $('.has-datepicker').each(function() {

                var $self = $(this);

                $self.daterangepicker(
                    {
                        ranges: {
                           'Today':        [moment(), moment()],
                           'Yesterday':    [moment().subtract('days', 1), moment().subtract('days', 1)],
                           'Last 7 Days':  [moment().subtract('days', 6), moment()],
                           'Last 30 Days': [moment().subtract('days', 29), moment()],
                           'Last 60 Days': [moment().subtract('days', 59), moment()],
                           'Last 90 Days': [moment().subtract('days', 89), moment()],
                           'This Month':   [moment().startOf('month'), moment().endOf('month')],
                           'Last Month':   [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                        },
                        dateLimit:  { days: 90 },
                        maxDate:    moment(),   // today
                        startDate:  moment(), // today
                        endDate:    moment(),   // today
                        opens:      ($self.data('datepickerOpens')) ? $self.data('datepickerOpens') : null,
                        applyClass: 'btn-primary'
                    },
                    function(start, end) {
                        /* Callback when user selects a date range */
                        //Core.log(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                    }
                );

                /* block user input */
                $self.on('keydown keyup focus', function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                });

            });

        }

    },

    Upload: {

        init: function() {
console.log('Core:Upload:init');

            var $body = $('body');

            $body.on('click', '.trigger-upload', function() {

                Core.SystemIndicator.busy();

                var $self       = $(this),
                    $form       = $self.closest('form'),
                    target      = $self.data('iframeId'),//'form-target-'+Math.floor(Math.random()*999999),
                    $iframe     = $('<iframe name="'+target+'" id="'+target+'" src="#" class="hide"></iframe>'),
                    $iframeBody = {}
                ;

                $iframe.on('load', function() {
                    Core.SystemIndicator.ready();
console.log('$iframe');
console.log($iframe);
                    $iframeBody = $iframe.contents().find('body').eq(0);
console.log('$iframeBody.text()');
console.log($iframeBody.text());
console.log('$iframeBody.data()');
console.log($iframeBody.data());
console.log('$iframeBody.data(response)');
console.log($iframeBody.data('response'));
                    // Core.log($iframeBody.data('response'));
                    $self.trigger('Core.Upload');
                    $iframe.remove();
                })
                .appendTo($body);


                $form.prop('target',   target)
                     .prop('enctype',  'multipart/form-data')
                     .prop('encoding', 'multipart/form-data')
                     .prop('method',   'post')
                     .submit()
                ;

                switch(Core.Environment.context()){
                    case 'vehicle/commandhistory' : $('#batch-import-csv-file').val('');
                                                    $('#batch-import-confirm').addClass('disabled');
                                                    $('#batch-import-cancel').trigger('click');
                                                    setTimeout("$('#batch-command-table').find('.dataTables-search-btn').trigger('click')",1000);
                                                    break;
                    case   'vehicle/commandqueue' : $('#batch-import-csv-file').val('');
                                                    $('#batch-import-confirm').addClass('disabled');
                                                    $('#batch-import-cancel').trigger('click');
                                                    setTimeout("$('#batch-queue-table').find('.dataTables-search-btn').trigger('click')",1000);
                                                    break;
                                          default : console.log('id:'+$(this).attr('id'));
                }

                return false;

            });

            $body.on('click', '#_submit', function() {

                var _submit = document.getElementById('_submit'), 
                _file = document.getElementById('_file'), 
                _progress = document.getElementById('_progress');

                if(_file.files.length === 0){
                    return;
                }

                var data = new FormData();
                data.append('SelectedFile', _file.files[0]);

                var request = new XMLHttpRequest();
                request.onreadystatechange = function(){
                    if(request.readyState == 4){
                        try {
                            var resp = JSON.parse(request.response);
                        } catch (e){
                            var resp = {
                                status: 'error',
                                data: 'Unknown error occurred: [' + request.responseText + ']'
                            };
                        }
                        console.log(resp.status + ': ' + resp.data);
                    }
                };

                request.upload.addEventListener('progress', function(e){
                    _progress.style.width = Math.ceil(e.loaded/e.total) * 100 + '%';
                }, false);

                request.open('POST', '/ajax/landmark/uploadLandmarks');
                request.send(data);

            });

        },

        getResponse: function(id) {

            id = id || false;

            var output = false;

            if (id) {
                id     = id.replace('#', '');
                output = $('#'+id).contents().find('body').eq(0).data('response');
            }

console.log('Core:Upload:getResponse:id="'+id+'", output="'+output+'"');
            return output;

        }

    },

    Viewport: {

        initResize: function() {

            $('#detail-panel').hide();

console.log('Core:Viewport:initResize');

            $(window).on('resize', function() {
                Core.Tooltip.init();
                Core.Viewport.adjustLayout();
            });

            $('.viewport-adjust-layout').on('click', function() {

                Core.Viewport.adjustLayout();

                switch($(this).attr('id')){

                    case        'vehicle-detail-info-tab' : Core.Map.Refresh('Vehicle.Map.map','',1);
                                                            if((currentUnitId)&&(currentEventData)&&(currentUnitData)){
                                                                Map.openInfoWindow(Vehicle.Map.map, 'unit', currentEventData.latitude, currentEventData.longitude, currentEventData, currentUnitData.moving, currentUnitData.battery, currentUnitData.signal, currentUnitData.satellites, currentUnitData.territoryname);
                                                            }
                                                            window.setTimeout('Core.Viewport.adjustLayout()',1);
                                                            break;

                                                  default : window.setTimeout('Core.Viewport.adjustLayout()',1000);


                }

            });

        },

        contentHeight: 0,

        adjustLayout: function() {
// console.log('adjustLayout');
            adjustLayoutCount=5;
            Core.Viewport.adjustLayoutCountDown();
        },

        adjustLayoutCountDown: function() {
// console.log('adjustLayoutCountDown');

            switch(adjustLayoutCount){

                case              0  :
                case             '0' :  break;

                case              1  :
                case             '1' :  Core.Viewport.adjustLayoutGo();
                                        break;

                             default :  setTimeout('Core.Viewport.adjustLayoutCountDown()',100);
                                        adjustLayoutCount--;

            }

        },

        adjustLayoutGo: function(resetMapSize) {
console.log('adjustLayoutGo');
            adjustLayoutCount=0;

            var viewportHeight = $(window).height(),
                viewportWidth  = $(window).width(),
                $pageContainer = $('#page-container'),
                $mainContent   = $('#main-content'),
                $mapDiv        = $('#map-div'),
                $detailPanel   = $('#detail-panel'),
                $editVehicle   = $('#modal-edit-vehicle-list'),
                $popndrop      = $('#popndrop-vehicle-panel')
            ;
            
            if(!($popndrop.attr('id'))){
                $popndrop      = $('#popndrop-landmark-panel')
            }

            if($('#sidebar-left').is(':visible')){
                viewportWidth = viewportWidth - $('#sidebar-left').width();
            } else {
                viewportWidth = viewportWidth - 1;
            }

            $mainContent.css({
                height: viewportHeight+'px',
                width:  parseInt(viewportWidth + 50)
            });

            $('#sub-panel').css({
                height: viewportHeight+'px'
            });

            viewportHeight = viewportHeight - 99;

            if (repoKey) {

                var newHeight = viewportHeight - $('#stops-report-frequent').find('.panel-report-scroll').offset().top - 38;
                $('#stops-report-frequent').find('.panel-report-scroll').css({ height: newHeight+'px' });

            } else if  ($mapDiv.closest('.modal').length == 0) {

                var windowHeight = $(window).height();
                var panelHeight  = 0;
                var padding      = 99;
                var mapHeight    = 0;
                var panelTop     = 0;
                var panelPop     = 50;

                if ($detailPanel.is(":visible")) {

                    panelHeight  = $detailPanel.height();
                    padding      = 99;
                    mapHeight    = parseInt(windowHeight-panelHeight-padding);
                    panelTop     = parseInt(padding+mapHeight);

                    if($popndrop.hasClass('active')){
                        mapHeight   = mapHeight + panelHeight - panelPop;
                        panelHeight = panelPop;
                        panelTop    = parseInt(padding+mapHeight);
                    }

                    $mapDiv.animate({
                            'height': mapHeight+'px'
                        }, 100, function() {
                    });
                    
                    $detailPanel.animate({
                            'top': panelTop+'px'
                        }, 100, function() {
                    });

                    Core.log('detail-panel visible');

                } else if ($editVehicle.is(":visible")) {

                    panelHeight  = $editVehicle.height();
                    padding      = 99;
                    mapHeight    = parseInt(windowHeight-panelHeight-padding);
                    panelTop     = parseInt(padding+mapHeight);
                    
                    $mapDiv.animate({
                            'height': mapHeight+'px'
                        }, 100, function() {
                    });
                    
                    $editVehicle.animate({
                            'top': panelTop+'px'
                        }, 100, function() {
                    });

                    Core.log('modal-edit-vehicle-list panel visible');

                } else {

                    switch(Core.Environment.context()){

                        case          'vehicle/print' : break;

                                              default : // $mapDiv.animate({
                                                        //         'height': parseInt(viewportHeight-0)+'px'
                                                        //     }, 100, function() {
                                                        // });

                                                        $mapDiv.css({
                                                            height: parseInt(viewportHeight-0)+'px'
                                                        });

// console.log('map is not in modal, mapDiv.height='+viewportHeight);

                    }

                }

            } else {

                $mapDiv.css({
                    height: '375px'
                });

                console.log('map height: '+$mapDiv.css('height'));

                console.log('map in modal');

                console.log($mapDiv);

            }

// console.log('&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&& $pageContainer:'+$pageContainer.height()+':'+$pageContainer.width()+', mapDiv.height='+viewportHeight);

            switch(Core.Environment.context()){

                case            'admin/blank' :
                case            'alert/blank' :
                case         'landmark/blank' :
                case           'report/blank' :
                case          'vehicle/blank' : viewportHeight = $(window).height() - $('.tab-content').offset().top - 5;
                                                $('.tab-content').css({ height: viewportHeight+'px' });
                                                break;

                case           'landmark/map' : setTimeout("Core.Map.Refresh('Landmark.Common.map2',1,1)",400);
                                                break;

                case            'report/list' : viewportHeight = $(window).height();
                                                $pageContainer.closest('.wrap').height(viewportHeight+'px');
                                                $pageContainer.height(viewportHeight+'px');
                                                viewportHeight = viewportHeight - $pageContainer.find('#fx-container').offset().top - 5;
                                                $pageContainer.find('#fx-container').height(viewportHeight+'px');
                                                $pageContainer.find('.block').height(viewportHeight+'px');
                                                break;

                case            'vehicle/map' : if(resetMapSize){
                                                    if((lastLatVehicle)&&(lastLongVehicle)){
                                                        setTimeout('Map.centerMap(Vehicle.Map.map,lastLatVehicle,lastLongVehicle);Map.resize(Vehicle.Map.map);',400);
                                                    } else {
                                                        setTimeout("Map.resize(Vehicle.Map.map);",400);
                                                    }
                                                } else {
                                                    setTimeout("Core.Map.Refresh('Vehicle.Map.map2',1)",400);
                                                }
                                                break;

            }                                                        

// console.log('&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&& $pageContainer:'+$pageContainer.height()+':'+$pageContainer.width()+', mapDiv.height='+viewportHeight);

        },

        initPageTransitions: function(inDuration, outDuration) {
console.log('Core:Viewport:initPageTransitions');

            var $fadeBlocks = $('#main-panel, #secondary-panel, #help-panel, .popover'),
                $faders     = $('a.transition')
            ;

            $fadeBlocks.fadeOut(0, function() {
               $fadeBlocks.fadeIn(inDuration);
            });

            $('body').one('click', $faders.selector, function(event) {

                var $self = $(this),
                    href  = $self.get(0).href
                ;

                event.preventDefault();

                Core.SystemIndicator.busy();

                $fadeBlocks.fadeOut(outDuration, function() {
                    //Core.log('redirect');
                    window.location = href;
                });
            });

        },

        getHeight: function() {
            return $(window).height();
        },

        getWidth: function() {
            return $(window).width();
        }


    },

    ButtonDropdown: {

        init: function() {

            var $buttonDropdowns = $('.btn-dropdown');

            $(document).on('click', '.btn-dropdown ~ .dropdown-menu a', function() {

                var $self   = $(this), // clicked item
                    $button = $self.parents('.dropdown-menu').siblings('.btn-dropdown'),
                    value   = $self.data('value'),
                    label   = $self.text()
                ;

                $button.val(value).text(label).trigger('Core.DropdownButtonChange');

                if ($button.is('.btn-dropdown-toggle-panels')) {

                    var $panels = $('.'+$button.data('panelTargets')),
                        target  = $self.data('toggleTarget')
                    ;

                    Core.log($button.data('panelTargets'));

                    $panels.hide();
                    $('.dropdown-toggle-panel-'+target).fadeIn(300);
                }

            });

            $('.filter-reset').click(function() {

                $buttonDropdowns.each(function() {

                    var $self       = $(this),
                        placeholder = $self.data('filterPlaceholder'),
                        $label      = $self.find('.filter-label').eq(0)
                    ;

                    $label.text(placeholder);
                    $self.val('');


                });

            });

        }

    },

    ButtonGroupToggle: {

        init: function() {

            var $buttonGroups = $('.btn-group-toggle');

            $buttonGroups.each(function() {
                var $self = $(this);
                if ($self.is('.btn-group')) {

                    var $buttons = $self.find('.btn');

                    $buttons.click(function() {
                        var $clicked = $(this);
                        if ( ! $clicked.is('.active')) { // if not active
                            $buttons.removeClass('active');
                            $clicked.addClass('active');
                        }

                    });
                }
            });



        }

    },

    ButtonFileInput: {

        init: function() {

            $(document).on('change', '.btn-file :file', function() {

                var $self    = $(this),
                    label    = $self.val().replace(/\\/g, '/').replace(/.*\//, '')
                ;

                $self.closest('.input-group-file').find('input[type="text"]').val(label);

            });

            $(document).on('click', '.input-group-file input[type="text"]', function() {

                $(this).parents('.input-group-file').find(':file').trigger('click');

            });
        }
    },

    DragDrop: {

        init: function() {

Core.log('Core.DragDrop Init');

            var $dragDropSimple = $('.drag-drop-simple');

            $dragDropSimple.each(function() {

                var $self = $(this),
                    $dragDropContainers = $self.find('.drag-drop-container'),
                    $availableContainer = $self.find('.drag-drop-available'),
                    $assignedContainer  = $self.find('.drag-drop-assigned'),
                    $draggableItems     = $self.find('.drag-drop-item'),
                    scopeIdentifier     = Core.StringUtility.getRandomString(10)
                ;

                $draggableItems.draggable({
                    scope:          scopeIdentifier,
                    containment:    $self,
                    revert:         true,
                    revertDuration: 0,
                    scroll:         false,
                    zIndex:         100,
                    helper:         'clone',
                    appendTo:       $self,
                    start: function(event, ui) {

                        Core.log('Core.DragDrop Draggable Start', 'group');

                        var $self     = $(this),
                            itemCount = parseInt($self.closest('ul').find('.drag-drop-item').filter('.active').length) || 1,
                            $helper   = ui.helper
                        ;

                        Core.log('Dragging '+itemCount+' items', 'info');

                        /* change helper text when dragging multiple */
                        if (itemCount > 1) {
                            $helper.find('.drag-drop-name').text('Dragging '+itemCount+' items');
                        }
                        $helper.find('.drag-drop-name').text('Use Buttons to Move');

                        Core.log('Core.DragDrop Draggable Start', 'groupEnd');

                    }

                });

                // mark item for multi-select and control the Shift+Click
                $draggableItems.on('click', function(event) {
console.log('$draggableItems.on:click:');

                    var $self        = $(this),
                        usedShiftKey = event.shiftKey, // boolean
                        $items       = $self.closest('ul').find('.drag-drop-item')
                    ;

                    if (usedShiftKey) {

                        var clickedItemIndex  = $items.index($self),
                            lastSelectedIndex = $items.index($items.filter('.last-selected').eq(0)),
                            $itemsInRange     = {}
                        ;

                        if (clickedItemIndex > lastSelectedIndex) {
                            // clicked item appears after the previously selected item
                            $itemsInRange = $self.prevUntil('.last-selected');
                        } else if (clickedItemIndex < lastSelectedIndex) {
                            // clicked item appears before the previously selected item
                            $itemsInRange = $self.nextUntil('.last-selected');
                        } else {
                            // the item was clicked twice - no further processing
                        }

                        if ( ! $.isEmptyObject($itemsInRange)) {
                            $itemsInRange.addClass('active');
                        }

                        $items.removeClass('last-selected');

                    } else {
                        $items.removeClass('last-selected');
                        $self.addClass('last-selected');
                    }

                    $(this).toggleClass('active');
                });

                $dragDropContainers.droppable({
                    scope: scopeIdentifier,
                    drop: function(event, ui) {
console.log('$dragDropContainers.droppable');

                        var $draggableItem  = ui.draggable,
                            $destination    = $(this).find('.drag-drop-list'),
                            $source         = $draggableItem.closest('.drag-drop-list'),
                            updatedItems    = [{id: $draggableItem.data('id'), name: $draggableItem.find('.drag-drop-name').text()}],
                            $activeItems    = $source.find('.drag-drop-item').filter('.active').not($draggableItem)
                        ;

                        if ($source.prop('class') == $destination.prop('class')) {
                            Core.log('Cancel Drag/Drop action: Source and Destination are the same', 'info');
                            return;
                        }

                        // append item to destination
                        $destination.append($draggableItem.detach());

                        // append any other selected items to destination

                        // $destination.append(
                        //     $source.find('.drag-drop-item').filter('.active').detach()
                        // );
                        
                        // get the ids and names of any other selected items and then append the items to destination
                        var lastIndex = $activeItems.length - 1;
                        $activeItems.each(function(key, value) {
                            var $me = $(value);
                            updatedItems.push({id: $me.data('id'), name: $me.find('.drag-drop-name').text()});
                            if (key == lastIndex) {
                                $destination.append(
                                    $activeItems.detach()
                                );
                            }
                        });

                        $destination.find('.drag-drop-item').removeClass('active');

                        $self.trigger('Core.DragDrop.Dropped', _getState());

                        function _getState() {
                            var output = {
                                updatedItems: {items: updatedItems, inAssignedGroup: ($destination.is('.drag-drop-assigned'))},
                                available: [],
                                assigned:  []
                            };

                            $availableContainer.find('.drag-drop-item').each(function(index) {
                                var $self = $(this);

                                output.available.push({
                                    id:   $self.data('id'),
                                    name: $self.find('.drag-drop-name').text()
                                });

                            });

                            $assignedContainer.find('.drag-drop-item').each(function(index) {
                                var $self = $(this);

                                output.assigned.push({
                                    id:   $self.data('id'),
                                    name: $self.find('.drag-drop-name').text()
                                });

                            });

                            return output;

                        }

                    }
                    
                });

            });

        },

        activeItemIds: function() {

            var output = [];

            $('.drag-drop-container').each(function(){
                $(this).find('li').each(function(){
                    var current = $(this);
                    if (current.is('.active')) {
                        if (output != '') {
                            output += ','+current.data('id');
                        } else {
                            output = current.data('id');
                        }
                    }
                });
            });
            console.log('activeItemsIds:'+output);

            return output;

        },

        disable: function(selector) {

            selector = selector || '';

            var $self       = $(selector),
                $draggables = $self.find('.drag-drop-item')
            ;

            $self.addClass('disabled');
            $draggables.draggable('option', 'disabled', true);


        },

        enable: function(selector) {

            selector = selector || '';

            var $self       = $(selector),
                $draggables = $self.find('.drag-drop-item')
            ;

            $self.removeClass('disabled');
            $draggables.draggable('option', 'disabled', false);


        },

        _itemExistsAtDestination: function(itemId, $destination) {

            var output = false;

            $destination.find('li').each(function() {
                var $self = $(this),
                    selfId    = $self.data('id')
                ;

                if (itemId == selfId) {
                    output = true;
                    return false; // break
                } else {
                    return true; // continue
                }

            });

            return output;

        },

        _generateGroupMarkup: function(itemId, itemName, itemGroupId, addClass) {
            itemId = itemId || false;
            itemName = itemName || 'Error';
            itemGroupId = itemGroupId || '';
            addClass = addClass || '';

            var output = false;

            if ( ! itemId) {
                return output;
            }
            output = '<li data-id="'+itemId+'" data-group-id="'+itemGroupId+'" class="list-group-item drag-drop-item '+addClass+'">' +
                     '  <div class="list-group-item-text drag-drop-name">'+itemName+'</div>' +
                     '</li>';
            return output;
        },

        _generateScopeIdentifier: function() {

           Core.log('Deprecated function: Use Core.StringUtility.getRandomString(int) instead', 'error');

        },

        destroy: function() {
        
            var $dragDropSimple = $('.drag-drop-simple');

            Core.log('Core.DragDrop.destroy()', 'group');

            //Core.log($dragDropSimple, 'dir');
            
            $dragDropSimple.each(function() {
               
                var $self = $(this),
                    $dragDropContainers = $self.find('.drag-drop-container')
                ;

                //$dragDropContainers.droppable('destroy');

            });

            //$dragDropSimple.find('.drag-drop-container').droppable('destroy');
            $dragDropSimple.find('.drag-drop-container').each(function() {

                var $self = $(this);

                if ($self.data('uiDroppable')) {
                    //$self.droppable('destroy');
                    $self.find('.drag-drop-item').off('click');
                }
            });

            Core.log('Core.DragDrop.destroy()', 'groupEnd');
    
        }

    },

    MasterDetailList: {

        init: function() {

            var $masterDetailLists = $('.master-detail-list');

            $masterDetailLists.each(function() {

                var $self        = $(this),
                    $master      = $self.find('.master-detail-list-master'),
                    $masterItems = $master.find('.list-group-item'),
                    $detailLists = $self.find('.master-detail-list-detail'),
                    itemHeight   = 42,
                    listHeight   = 337//$masterItems.length*itemHeight +1  // +1 for margin
                ;

                $master.add($detailLists).css('height', listHeight);

                $masterItems.on('click', function() {

                    var $clickedItem    = $(this),
                        $detailList     = $('#'+$clickedItem.data('toggle')),
                        detailTitleText = $clickedItem.text(),
                        $detailTitle    = $self.find('.master-detail-detail-title')
                    ;

                    if ($clickedItem.is('.active')) {
                        return;
                    }

                    $masterItems.removeClass('active');
                    $clickedItem.addClass('active');

                    $detailLists.hide();
                    $detailList.fadeIn(300);
                    $detailTitle.text(detailTitleText);
                    $self.trigger('Core.MasterDetailList.detailUpdated');
                });


            });

        }

    },

    Wedge: {

        init: function() {

            $('.wedge').each(function() {

                var $self  = $(this),
                    $inner = $('<div class="wedge-inner"></div>'),
                    data   = $self.data()
                ;

                if (data.wedgeBackground != 'undefined') {
                    $self.css('background-color', data.wedgeBackground);
                }

                if (data.wedgeForeground != 'undefined') {
                    $inner.css('background-color', data.wedgeForeground);
                }

                if (data.wedgeLeftPosition != 'undefined') {
                    $self.css('left', data.wedgeLeftPosition);
                }

                if (data.wedgeRightPosition != 'undefined') {
                    $self.css('right', data.wedgeRightPosition);
                }

                if (data.wedgeYPosition != 'undefined') {
                    $self.css('top', data.wedgeYPosition);
                }

                $self.append($inner);


            });


        }

    },

    MyAccount: {

        Modal:  {
            init: function() {
console.log('Core:MyAccount:Modal');

                var $modal                 = $('#modal-my-account'),
                    $passwordToggle        = $('#change-password-container-toggle'),
                    $passwordContainer     = $('#change-password-container'),
                    $passwordUpdateButton  = $('#my-account-save-password-button'),
                    $formResponseContainer = $('#my-account-form-response')
                ;

                $('#my-account').on('click', function() {
console.log('Core:MyAccount:Modal:#my-account');
                    $('.dropdown-toggle').trigger('click');

                    var $self = $(this);

                    $.ajax({
                        url: '/ajax/account/getMyAccountInfo',
                        type: 'POST',
                        dataType: 'json',
                        success: function(responseData) {
                            if (responseData.code === 0) {
                                if (! $.isEmptyObject(responseData.data.user)) {
                                    var userdata = responseData.data.user;
                                    Core.Dialog.launch($modal.selector, 'My Account', {
                                        width: '90%'
                                    },
                                    {
                                        hidden: function() {
                                            Core.FormValidation.hide($formResponseContainer.selector);
                                            Core.FormValidation.clearLabelErrorsIn($modal.selector);
                                        },
                                        show: function() {

                                            $modal.find('.modal-title').text('').hide();
                                            $passwordToggle.find('a').show();
                                            $('#my-account-password').val('');
                                            $('#my-account-password-confirm').val('');
                                            $passwordContainer.hide();

                                        },
                                        shown: function() {
                
                                            $modal.find('.modal-title').text($self.text()).fadeIn(100);
                                            
                                            // set first name
                                            Core.Editable.setValue($('#my-account-first-name'),  userdata.firstname);

                                            // set last name
                                            Core.Editable.setValue($('#my-account-last-name'),  userdata.lastname);
                                            
                                            // set email
                                            Core.Editable.setValue($('#my-account-email'),  userdata.email);

                                            // set username
                                            Core.Editable.setValue($('#my-account-username'),  userdata.username);
                                            
                                            // (temporarily disable editing password for now; will need to handle editing password differently)
                                            //$('#my-account-password').editable('disable');
                                        }
                                    });                                        
                                }    
                            } else {
                                if (! $.isEmptyObject(responseData.validation_error)) {
                                    //  display validation errors
                                }
                            }
                            
                            if (! $.isEmptyObject(responseData.message) && responseData.code > 0) {
                                // Core.SystemMessage.show(responseData.message, responseData.code);
                            }   
                        }    
                    });

                });

                // toggle password change
                $passwordToggle.find('a').on('click', function() {

                    var $self = $(this);

                    $self.hide();
                    $passwordContainer.fadeIn(300);

                });

                $passwordUpdateButton.on('click', function() {

                    var $password  = $('#my-account-password'),
                        $confirm   = $('#my-account-password-confirm'),
                        okToUpdate = false
                    ;

                    Core.FormValidation.clearLabelErrorsIn($modal.selector);

                    if ( ! $password.val().length) {
                        Core.FormValidation.show($formResponseContainer.selector, {
                            'my-account-password': 'Value not entered'
                        }, 1);
                    } else if ($password.val() != $confirm.val()) {
                        Core.FormValidation.show($formResponseContainer.selector, {
                            'my-account-password-confirm': 'Does not match Password'
                        }, 1);
                    } else {
                        Core.FormValidation.hide($formResponseContainer.selector);
                        Core.FormValidation.clearLabelErrorsIn($modal.selector);
                        okToUpdate = true;
                    }

                    if (okToUpdate) {

                        // do ajax call
                        $.ajax({
                            url: '/ajax/account/updateMyAccountInfo',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                id: 'my-account-password',
                                value: $password.val(),
                                password_confirm: $confirm.val()
                            },
                            success: function(responseData) {
                                if (responseData.code === 0) {
                                    // on success, clear password input fields, hide password container and display Change Password link
                                    $passwordToggle.find('a').show();
                                    $password.val('');
                                    $confirm.val('');
                                    $passwordContainer.hide();
                                } else {
                                    if (! $.isEmptyObject(responseData.validation_error)) {
                                        Core.FormValidation.show($formResponseContainer.selector, responseData.validation_error, responseData.code);
                                    }
                                }
                                
                                if (! $.isEmptyObject(responseData.message)) {
                                    Core.FormValidation.show($formResponseContainer.selector, responseData.message, responseData.code);
                                }   
                            }    
                        });
                    }
                });
            }
        },

        Edit: {
            init: function() {

            }
        }

    },

    Cookie: {

        init: function() {
console.log('core.js: // $.cookie.json = true;');
//             $.cookie.json = true;
        },

        get: function(cookieName) {
console.log('Core.Cookie.get:'+cookieName);

            var c = '';
            var nameEQ = cookieName + "=";
            var ca = document.cookie.split(';');
            for(var i=0;i < ca.length;i++) {
                c = ca[i];
                k = c.split('=')[0];
                v = c.split('=')[1];
console.log('Core.Cookie.get:c:'+c+':testing:"'+cookieName.trim()+'"==="'+k.trim()+'"');
                if (k.trim() === cookieName.trim()) {
console.log('Core.Cookie.get:found:'+k+':'+v);
                    return v;
                }
            }
            return null;

        },

        set: function(cookieName, cookieValue, days) {
console.log('Core.Cookie.set:'+cookieName+':'+cookieValue+':'+days);

            var expires = "";

            if (days) {
                var date = new Date();
                date.setTime(date.getTime()+(days*24*60*60*1000));
                expires = "; expires="+date.toGMTString();
            }

            document.cookie = cookieName+"="+cookieValue+expires+"; path=/";

            return document.cookie;

        },

        clear: function(cookieName) {
console.log('Core.Cookie.clear:'+cookieName);

            Core.Cookie.set(cookieName,"",-1);

        }

    },

    Map: {

        map: undefined,

        mapAddress: undefined,

        Refresh: function (eid,clearAll,resetCount,latitude,longitude) {

            if(clearAll) {
                clearAllMarkers = 1;
            }

            currentRefreshEid = eid;
            if (resetCount) {

                currentRefreshEid = eid;

                mapRepaint=5;
            
            }

console.log('Core.Map.Refresh:mapRepaint:'+mapRepaint+':'+eid+'['+currentRefreshEid+']:'+clearAll);
            if ((eid)&&(eid==currentRefreshEid)){

                mapRepaint--;

                if(mapRepaint==1) {
                   mapRepaint=-1;
    console.log('########################################## Core.Map.Refresh:'+eid);

                    var markerCount=0;
                    var markerRemove={};
                    var markerOptions={};
                    var markerId='';

                    switch(eid){

                    case                'Core.map' :    Map.resetMap(Core.map);
                                                        Map.resize(Core.map);
                                                        break;

                    case        'Vehicle.Map.map2' :    var buffer = [],
                                                            marker = {},
                                                            markerCount = 0,
                                                            index = 0;
                                                        if(! clearAllMarkers){
                                                            console.log($('#secondary-sidebar-scroll').find('li').filter('.active'));
                                                            console.log(Vehicle.Map.map._markerMapIndex);
                                                            $.each($('#secondary-sidebar-scroll').find('li').filter('.active'), function(k, v) {
                                                                if(v){

                                                                    console.log(k);
                                                                    console.log(v);

                                                                    var unit_id = $(v).attr('id').split('-')[2];
                                                                    //Vehicle.Map.map._markerMapIndex[unit_id];

                                                                    marker = Vehicle.Map.map._markerLayer.getLayer(Vehicle.Map.map._markerMapIndex[unit_id]);

                                                                    console.log('looking : '+unit_id);
                                                                    if(marker){
                                                                        console.log('found : '+marker.name);
                                                                        index++;
                                                                
                                                                        marker.unit_id = unit_id;
                                                                        console.log(marker);
                                                                        /*{
                                                                        id: marker.unit_id,
                                                                        name: marker.unitname,
                                                                        latitude: marker.latitude,
                                                                        longitude: marker.longitude,
                                                                        eventname: marker.eventname,
                                                                        click: function() {
                                                                            Map.getVehicleEvent(Vehicle.Map.map, k, unitdata.eventdata.id);
                                                                        }*/
                                                                        
                                                                        if (buffer != undefined && marker != undefined) {
                                                                            buffer.push(marker);
                                                                        }

                                                                        // if (index == count) {
                                                                        //     console.log('last');
                                                                        //     Map.clearMarkers(Vehicle.Map.map);
                                                                        //     Map.resetMap(Vehicle.Map.map);

                                                                        //     $.each(buffer, function( k, v ) {
                                                                        //         Map.addMarker(Vehicle.Map.map, v, true);
                                                                        //     });

                                                                        //     // Map.updateMarkerBound(Vehicle.Map.map); // This calls an empty function
                                                                        //     Map.resize(Vehicle.Map.map);
                                                                        //     Map.updateMapBound(Vehicle.Map.map);
                                                                        //     if(markerCount===1){
                                                                        //         Map.updateMapBoundZoom(Vehicle.Map.map);
                                                                        //     }                                                           
                                                                        // }

                                                                        markerCount++;
                                                                
                                                                    }
                                                                    

                                                                }
                                                            });
                                                            console.log(buffer);
                                                        } else {
console.log('Vehicle.Map.map2:clearAllMarkers:'+clearAllMarkers);
                                                        }

                                                        Map.clearMarkers(Vehicle.Map.map);
                                                        Map.resetMap(Vehicle.Map.map);

                                                        if(markerCount > 0){
                                                            $.each(buffer, function( k, v ) {
                                                                Map.addMarker(Vehicle.Map.map, v, true);
                                                            });
                                                        }

                                                        // Map.updateMarkerBound(Vehicle.Map.map); // This calls an empty function
                                                        Map.resize(Vehicle.Map.map);
                                                        Map.updateMapBound(Vehicle.Map.map);
                                                        if(markerCount===1){
                                                            Map.updateMapBoundZoom(Vehicle.Map.map);
                                                        }
                                                        break;

                    case        'Vehicle.Map.map' :     console.log('Core.Environment.context():'+Core.Environment.context());
                                                        if (Core.Environment.context() == 'vehicle/map') {
                                                            if ( (currentUnitId) && (currentUnitId == currentUnitIdHidePanel) ) {
        console.log('|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||');
                                                                if((lastLatVehicle)&&(lastLongVehicle)){
                                                                    setTimeout('Map.centerMap(Vehicle.Map.map,lastLatVehicle,lastLongVehicle);Map.resize(Vehicle.Map.map);',1000);
                                                                }
                                                            } else {
                                                                if(!(mapZoomBool)) {
        console.log('|||||||||||||||||||| mapZoom');
                                                                    mapZoomBool=1;
                                                                    Map.zoomMap(Vehicle.Map.map,16);
                                                                }
                                                                var markerOptions = {};
                                                                var newLat='';
                                                                var newLlong='';
                                                                Map.clearMarkers(Vehicle.Map.map);
                                                                $('#secondary-sidebar-scroll').find('li.active').each( function() {
                                                                    if($(this).attr('data-event-id')){
                                                                        newLat = $(this).attr('data-lat');
                                                                        newLong = $(this).attr('data-long');
                                                                        markerOptions = {
                                                                                id: $(this).attr('data-event-id'),
                                                                                name: $(this).attr('data-event'),
                                                                                latitude: $(this).attr('data-lat'),
                                                                                longitude: $(this).attr('data-long'),
                                                                                eventname: $(this).attr('data-event'), // used in map class to get vehicle marker color
                                                                                click: function() {
                                                                                    $('#refresh-map-markers').trigger('click');
                                                                                }
                                                                        };
                                                                        Map.addMarker(Vehicle.Map.map, markerOptions, true);
                                                                    }
                                                                });
                                                                if((newLat)&&(newLong)){
                                                                    lastLatVehicle=newLat;
                                                                    lastLongVehicle=newLong;
                                                                }
                                                                if((lastLatVehicle)&&(lastLongVehicle)){
                                                                    Map.centerMap(Vehicle.Map.map,lastLatVehicle,lastLongVehicle);
                                                                    setTimeout('Map.centerMap(Vehicle.Map.map,lastLatVehicle,lastLongVehicle);Map.resize(Vehicle.Map.map);',1000);
                                                                    // Map.updateMapBound(Vehicle.Map.map);
                                                                }
                                                                Map.resize(Vehicle.Map.map);
                                                            }
                                                        } else {
                                                            Map.resetMap(Vehicle.Map.map);
                                                            Map.resize(Vehicle.Map.map);
                                                            Map.updateMapBound(Vehicle.Map.map);
                                                            if(!(mapZoomBool)) {
                                                                mapZoomBool=1;
                                                                Map.zoomMap(Vehicle.Map.map,16);
                                                            }
                                                            if((latitude)&&(longitude)){
                                                                lastLatVehicle=latitude;
                                                                lastLongVehicle=longitude;
                                                            }
                                                            if((lastLatVehicle)&&(lastLongVehicle)){
                                                                Map.centerMap(Vehicle.Map.map,lastLatVehicle,lastLongVehicle);
                                                                setTimeout('Map.centerMap(Vehicle.Map.map,lastLatVehicle,lastLongVehicle);Map.resize(Vehicle.Map.map);',1000);
                                                            }
                                                        }
                                                        break;

                    case 'Landmark.Common.addmap' : if((lastLatVehicle)&&(lastLongVehicle)){
                                                        Map.centerMap(Landmark.Common.addmap,lastLatVehicle,lastLongVehicle);
                                                    } else {
                                                        Map.clearMarkers(Landmark.Common.addmap);
                                                        Map.resetMap(Landmark.Common.addmap);
                                                    }
                                                    // Map.updateMarkerBound(Landmark.Common.addmap); // This calls an empty function
                                                    Map.resize(Landmark.Common.addmap);
                                                    Map.updateMapBound(Landmark.Common.addmap);
                                                    break;

                   case 'Landmark.Common.addmap2' : if((lastLatVehicle)&&(lastLongVehicle)){
                                                        Map.centerMap(Landmark.Common.addmap,lastLatVehicle,lastLongVehicle);
                                                    } else {
                                                        Map.clearMarkers(Landmark.Common.addmap);
                                                        Map.resetMap(Landmark.Common.addmap);
                                                    }
                                                    // Map.updateMarkerBound(Landmark.Common.addmap); // This calls an empty function
                                                    Map.resize(Landmark.Common.addmap);
                                                    Map.updateMapBound(Landmark.Common.addmap);
                                                    Map.zoomMap(Landmark.Common.addmap,4);
                                                    break;

                    case   'Landmark.Common.map2' : if($('#landmark-shape').val()!='polygon'){                   
        console.log('Landmark.Common.map2 : 1');
                                                        if((lastLatVehicle)&&(lastLongVehicle)){
        console.log('Landmark.Common.map2 : 2');
                                                            Map.centerMap(Landmark.Common.map,lastLatVehicle,lastLongVehicle);
                                                        } else {
        console.log('Landmark.Common.map2 : 3');
                                                            Map.clearMarkers(Landmark.Common.map);
                                                            Map.resetMap(Landmark.Common.map);
                                                        }
        console.log('Landmark.Common.map2 : 4');
                                                        // Map.updateMarkerBound(Landmark.Common.map); // This calls an empty function
                                                        if(!(mapZoomBool)) {
        console.log('Landmark.Common.map2 : 5');
                                                            Map.resize(Landmark.Common.map);
        console.log('|||||||||||||||||||| mapZoom');
                                                            mapZoomBool=1;
                                                            // Map.zoomMap(Landmark.Common.map,16);
                                                        }
        console.log('Landmark.Common.map2 : 6');
                                                        Map.updateMapBound(Landmark.Common.map);
        console.log('Landmark.Common.map2 : 7');
                                                    }
                                                    break;

                    case    'Landmark.Common.map' : if (Core.Environment.context() == 'landmark/map') {
                                                        if ( (currentLandmarkId) && (currentLandmarkId == currentLandmarkIdHidePanel) ) {
                                                            if((lastLatLandmark)&&(lastLongLandmark)){
                                                                setTimeout('Map.centerMap(Landmark.Common.map,lastLatVehicle,lastLongVehicle);Map.resize(Landmark.Common.map);',1);
                                                            }
                                                        } else {
                                                            if(!(mapZoomBool)) {
    console.log('|||||||||||||||||||| mapZoom');
                                                                mapZoomBool=1;
                                                                Map.zoomMap(Landmark.Common.map,16);
                                                            }
                                                            var markerOptions = {};
                                                            var newLat='';
                                                            var newLlong='';
                                                            // Map.clearMarkers(Landmark.Common.map);
                                                            $('#secondary-sidebar-scroll').find('li.active').each( function() {
    console.log("$('#secondary-sidebar-scroll').find('li.active').each( function() {");

                                                                if($(this).attr('data-event-id')){
                                                                    newLat = $(this).attr('data-lat');
                                                                    newLong = $(this).attr('data-long');
                                                                    markerOptions = {
                                                                            id: $(this).attr('data-event-id'),
                                                                            name: $(this).attr('data-event'),
                                                                            latitude: $(this).attr('data-lat'),
                                                                            longitude: $(this).attr('data-long'),
                                                                            eventname: $(this).attr('data-event'), // used in map class to get vehicle marker color
                                                                    },
                                                                    polygonOptions = {
                                                                        type: $(this).attr('data-event-shape'),
                                                                        radius: $(this).attr('data-event-radius'),
                                                                        points: $(this).attr('data-event-points')
                                                                    };
                                                                    polygonOptions.points = lastPolygon;

    console.log("a: Map.addMarkerWithPolygon(Landmark.Common.map, markerOptions, false, polygonOptions)");
    console.log(markerOptions);
    console.log(polygonOptions);
                                                                    // Map.addMarkerWithPolygon(Landmark.Common.map, markerOptions, false, polygonOptions);
    console.log("b: Map.addMarkerWithPolygon(Landmark.Common.map, markerOptions, false, polygonOptions)");
                                                                    // Map.addMarker(Landmark.Common.map, markerOptions, false);
                                                                }
                                                            });
                                                            if((newLat)&&(newLong)){
                                                                lastLatVehicle=newLat;
                                                                lastLongVehicle=newLong;
                                                                Map.centerMap(Landmark.Common.map,lastLatVehicle,lastLongVehicle);
                                                                // Map.updateMapBound(Landmark.Common.map);
                                                            }
                                                            Map.resize(Landmark.Common.map);
                                                            if((newLat)&&(newLong)){
                                                                Map.updateMarkerBound(Landmark.Common.map);
                                                                Map.updateMapBound(Landmark.Common.map);
                                                            }
                                                        }
                                                    } else {
                                                        Map.resetMap(Landmark.Common.map);
                                                        Map.resize(Landmark.Common.map);
                                                        Map.updateMapBound(Landmark.Common.map);
                                                        if(!(mapZoomBool)) {
    console.log('mapZoomBool');
                                                            mapZoomBool=1;
                                                            Map.zoomMap(Landmark.Common.map,16);
                                                        }
                                                        if((latitude)&&(longitude)){
    console.log('latitude="'+latitude+'", longitude="'+longitude+'"');
                                                            Map.centerMap(Landmark.Common.map,latitude,longitude);
                                                        }
                                                    }
                                                    // $.each(Landmark.Common.map._markerMapIndex, function( k, v ) {
                                                    //     if($('#vehicle-li-'+k).filter('.active')){
                                                    //         markerCount++;
                                                    //     } else {
                                                    //         markerRemove.push(k);
                                                    //     }
                                                    // });
                                                    // $.each(markerRemove, function( k, v ) {
                                                    //     Map.removeMarker(Landmark.Common.map, v);
                                                    // });
                                                    
                                                    // if((markerCount === 0)||(clearAllMarkers)){
                                                    //     Map.clearMarkers(Landmark.Common.map);
                                                    //     Map.resetMap(Landmark.Common.map);
                                                    // }
                                                    // // Map.updateMarkerBound(Landmark.Common.map); // This calls an empty function
                                                    // Map.resize(Landmark.Common.map);
                                                    // Map.updateMapBound(Landmark.Common.map);
                                                    // if(markerCount===1){
                                                    //     // Map.updateMapBoundZoom(Landmark.Common.map);
                                                    // }
                                                    break;

                    }

                    clearAllMarkers = '';

                } else if(mapRepaint>1){

                    setTimeout('Core.Map.Refresh(\''+eid+'\');',1);

                }

            }

        }

    },


    initMapModal: function() {
    
        // instantiate map
        Core.Map.mapAddress = Map.initMap('modal-map-hook', {zoom: 4});

        var $modal = $('#modal-map-container');

        $(document).on('click', '.address_map_toggle', function() {   
            var $self       = $(this).closest('td'),
                latitude    = $self.data('latitude'),
                longitude   = $self.data('longitude'),
                aveduration = $self.data('average-duration'),
                dow         = $self.data('dow'),
                tod         = $self.data('tod'),
                rank        = $self.data('rank'),
                color       = $self.data('color'),
                label       = $self.data('label'),
                license     = $self.data('license'),
                make        = $self.data('make'),
                model       = $self.data('model'),
                name        = $self.data('name'),
                vin         = $self.data('vin'),
                year        = $self.data('year')
            ;

            var markerOptions = {};
            var event = {};

            if(repoKey){
                event = {
                        infomarker_address: label,
                        latitude: latitude,
                        longitude: longitude,
                        unitname: repoKey + ' (Stop #' + rank + ')'
                    }
                ;
                markerOptions = {
                        id: 987654321,
                        name: label,
                        latitude: latitude,
                        longitude: longitude,
                        eventname: 987654321, // used in map class to get vehicle marker color
                        click: function() {
                            Map.openInfoWindow($map, 'repo', latitude, longitude, event, '4', '5', '6', '7', '','9','10',aveduration,dow,tod,color,license,make,model,vin,year);
                        }
                    }
                ;
            } else {
                event = {
                        formatted_address: label,
                        infomarker_address: label,
                        latitude: latitude,
                        longitude: longitude,
                        rank: rank,
                        unitname: name
                    }
                ;
                markerOptions = {
                        id: 987654321,
                        name: label,
                        latitude: latitude,
                        longitude: longitude,
                        eventname: 987654321, // used in map class to get vehicle marker color
                        click: function() {
                            Map.openInfoWindow($map, 'rank', latitude, longitude, event, '4', '5', '6', '7', '','9','10',aveduration,dow,tod,color,license,make,model,vin,year);
                        }
                    }
                ;
            }

            var $map = {};
            var showHide = true;
            if(repoKey){
                $map = Core.map;
                showHide = true;
                $('#repolink-map').trigger('click');
                setTimeout('Map.centerMap(Core.map,lastLatVehicle,lastLongVehicle);Map.resize(Core.map);',400);
                $('#last-known-location').show();
            } else {
                $map = Vehicle.Map.map;
                setTimeout('Map.centerMap(Vehicle.Map.map,lastLatVehicle,lastLongVehicle);Map.resize(Vehicle.Map.map);',400);
            }
            Map.resize($map);
            Map.resetMap($map);
            Map.clearMarkers($map);
            Map.addMarker($map, markerOptions, showHide);
            Map.updateMarkerBound($map);
            Map.updateMapBound($map);
            lastLatVehicle = latitude;
            lastLongVehicle = longitude;
            // Map.updateMapBoundZoom($map, true);
            if(repoKey){
                Map.openInfoWindow($map, 'repo', latitude, longitude, event, '4', '5', '6', '7', '','9','10',aveduration,dow,tod,color,license,make,model,vin,year);
            } else {
                Map.openInfoWindow($map, 'rank', latitude, longitude, event, '4', '5', '6', '7', '','9','10',aveduration,dow,tod,color,license,make,model,vin,year);
            }
        });
            
        $(document).on('click', '.address_map_link', function() {   
            
            var newheight=$(window).height()-190;
            var newwidth=$(window).width()-150;
            $modal.find('div.modal-dialog').css({
                width: newwidth + 'px'
            });
            $('#modal-map-hook').css({
                height: newheight + 'px',
            });

            var $self       = $(this).closest('td'),
                label       = $self.data('label'),
                latitude    = $self.data('latitude'),
                longitude   = $self.data('longitude'),
                name       = $self.data('name')
            ;

            if($('#modal-edit-vehicle-list').is(':visible')){

                Map.resize(Vehicle.Map.map);
                Map.resetMap(Vehicle.Map.map);
                Map.clearMarkers(Vehicle.Map.map);
                Map.addMarker(
                    Vehicle.Map.map,
                    {
                        id: 999,
                        name: $self.text() + '(' + latitude + ' / ' + longitude + ')',
                        latitude: latitude,
                        longitude: longitude
                    },
                    true
                );
                // Map.showHideLabel(Vehicle.Map.map, 999, true);
                Map.updateMarkerBound(Vehicle.Map.map);
                Map.updateMapBound(Vehicle.Map.map);
                Map.updateMapBoundZoom(Vehicle.Map.map);
                Map.centerMap(Vehicle.Map.map, latitude, longitude,'17');

            } else {

                Core.Dialog.launch('#'+$modal.prop('id'), 'Map', {width: '800px'}, {
                    hide: function() {
                       
                    },
                    hidden: function() {
                        
                    },
                    show: function() {

                    },
                    shown: function() {
                        Map.resize(Core.Map.mapAddress);
                        Map.resetMap(Core.Map.mapAddress);
                        Map.clearMarkers(Core.Map.mapAddress);
                        if (latitude != '' && latitude != 0 && longitude != '' && longitude != 0) {

console.log('Map.addMarker(Core.Map.mapAddress,{latitude:'+latitude+', longitude:'+longitude+',');

                            var event = {
                                    formatted_address: $self.text(),
                                    latitude: latitude,
                                    longitude: longitude,
                                    unitname: name
                            };

                            Map.addMarker(
                                Core.Map.mapAddress,
                                {
                                    id: 999,
                                    // name: $self.text() + '(' + latitude + ' / ' + longitude + ')',
                                    latitude: latitude,
                                    longitude: longitude,
                                    click: function() {
                                        Map.openInfoWindow(Core.Map.mapAddress, 'report', latitude, longitude, event);
                                    }
                                },
                                true
                            );

                            // Map.showHideLabel(Core.Map.mapAddress, 999, true);
                            Map.openInfoWindow(Core.Map.mapAddress, 'report', latitude, longitude, event);
                            Map.updateMarkerBound(Core.Map.mapAddress);
                            Map.updateMapBound(Core.Map.mapAddress);
                            Map.updateMapBoundZoom(Core.Map.mapAddress);
                            Map.centerMap(Core.Map.mapAddress, latitude, longitude,'17');

                        }                             
                    }
                });
                
                switch (label) {
                    case    '' :
                    case 'n/a' : $('#modal-map-title').text('Map');
                                 break;
                       default : $('#modal-map-title').text(name + ': ' + $self.text() + '(' + latitude + ' / ' + longitude + ')');
                }

                Core.FixModal.FixModal('modal-map-container');

            }

        }); 

        $(document).on('click', '.landmark_map_link', function() {   

            var newheight=$(window).height()-190;
            var newwidth=$(window).width()-150;
            $modal.find('div.modal-dialog').css({
                width: newwidth + 'px'
            });
            $('#modal-map-hook').css({
                height: newheight + 'px',
            });

            var $self       = $(this),
                label       = $self.data('label'),
                latitude    = $self.data('latitude'),
                longitude   = $self.data('longitude'),
                mode        = $self.data('mode'),
                radius      = $self.data('radius'),
                shape       = $self.data('shape')
            ;

    console.log('label: '+label);
    console.log('latitude: '+latitude);
    console.log('longitude: '+longitude);
    console.log('mode: '+mode);
    console.log('radius: '+radius);
    console.log('shape: '+shape);

            var TheMap='';
            switch(mode){
                case    'vehicleverification' : TheMap = Vehicle.Map.map;
                                                Map.clearMarkers(TheMap);
                                                addMapCoordinates = null;

                                                if (shape == 'rectangle' || shape == 'polygon' || shape == 'square') { // for rectangles, squares, and polygons
                                                    // get the coordinates of the temp polygon as an array of objects, each with two properties called 'latitude' and 'longitude'
                                                    // (i.e. [{latitude: 12.345, longitude: 45.56}, {latitude: 34.45, longitude: 56.788}, ...]) 
                                                    var coordinates = Map.getTempPolygonPoints(TheMap);
                                                    
                                                    if ((shape == 'rectangle' || shape == 'square')) {
                                                        if (coordinates.length == 4) { // rectangle or square needs 5 points to connect
                                                            addMapCoordinates = coordinates;
                                                        } else {
                                                            console.log('Rectangle landmarks require 2 points');
                                                        }
                                                    } else if (shape == 'polygon') { // polygon needs at least 4 points to connect (i.e triangle)
                                                        if (coordinates.length >= 3) {
                                                            addMapCoordinates = coordinates;
                                                        } else {                                            
                                                            console.log('Polygon landmarks require 3 points');
                                                        }
                                                    }
                                                }

                                                var polygonOptions=Array();
                                                polygonOptions.type = shape;
                                                if (shape == 'circle') {
                                                    polygonOptions.radius = radius;
                                                } else if (shape == 'square') {
                                                    polygonOptions.radius = radius;
                                                    polygonOptions.points = addMapCoordinates;
                                                } else {
                                                    polygonOptions.points = addMapCoordinates;
                                                }

                                    console.log('polygonOptions...');
                                    console.log(polygonOptions);

                                                Map.addMarkerWithPolygon(
                                                    TheMap,
                                                    {
                                                        id: 999,
                                                        name: label + ' (' + latitude + ' / ' + longitude + ')',
                                                        latitude: latitude,
                                                        longitude: longitude,
                                                        click: function() {
                                                            Map.getVehicleEvent(TheMap, unitdata.unit_id, eventdata.id);
                                                        }
                                                    },
                                                    false,
                                                    polygonOptions
                                                );

                                                Map.resetMap(TheMap);
                                                Map.resize(TheMap);
                                                Map.updateMapBound(TheMap);
                                                Map.updateMapBoundZoom(TheMap);
                                                break;

                                      default : TheMap = Core.Map.mapAddress;
                                                Core.Dialog.launch('#'+$modal.prop('id'), 'Map', {width: '90%'}, {
                                                    hide: function() {
                                                       
                                                    },
                                                    hidden: function() {
                                                        
                                                    },
                                                    show: function() {

                                                    },
                                                    shown: function() {

                                                        Map.clearMarkers(TheMap);
                                                        addMapCoordinates = null;

                                                        if (shape == 'rectangle' || shape == 'polygon' || shape == 'square') { // for rectangles, squares, and polygons
                                                            // get the coordinates of the temp polygon as an array of objects, each with two properties called 'latitude' and 'longitude'
                                                            // (i.e. [{latitude: 12.345, longitude: 45.56}, {latitude: 34.45, longitude: 56.788}, ...]) 
                                                            var coordinates = Map.getTempPolygonPoints(TheMap);
                                                            
                                                            if ((shape == 'rectangle' || shape == 'square')) {
                                                                if (coordinates.length == 4) { // rectangle or square needs 5 points to connect
                                                                    addMapCoordinates = coordinates;
                                                                } else {
                                                                    console.log('Rectangle landmarks require 2 points');
                                                                }
                                                            } else if (shape == 'polygon') { // polygon needs at least 4 points to connect (i.e triangle)
                                                                if (coordinates.length >= 3) {
                                                                    addMapCoordinates = coordinates;
                                                                } else {                                            
                                                                    console.log('Polygon landmarks require 3 points');
                                                                }
                                                            }
                                                        }

                                                        var polygonOptions=Array();
                                                        polygonOptions.type = shape;
                                                        if (shape == 'circle') {
                                                            polygonOptions.radius = radius;
                                                        } else if (shape == 'square') {
                                                            polygonOptions.radius = radius;
                                                            polygonOptions.points = addMapCoordinates;
                                                        } else {
                                                            polygonOptions.points = addMapCoordinates;
                                                        }

                                            console.log('polygonOptions...');
                                            console.log(polygonOptions);

                                                        Map.addMarkerWithPolygon(
                                                            TheMap,
                                                            {
                                                                id: 999,
                                                                name: label + ' (' + latitude + ' / ' + longitude + ')',
                                                                latitude: latitude,
                                                                longitude: longitude,
                                                                click: function() {
                                                                    Map.getVehicleEvent(TheMap, unitdata.unit_id, eventdata.id);
                                                                }
                                                            },
                                                            false,
                                                            polygonOptions
                                                        );

                                                        Map.resetMap(TheMap);
                                                        Map.resize(TheMap);
                                                        Map.updateMapBound(TheMap);
                                                        Map.updateMapBoundZoom(TheMap);

                                                    }

                                                });
            }
            
            switch(mode){
                case    'vehicleverification' : // $('#verification-address-click').trigger('click');
console.log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> verification-address-click');
                                                break;
                                      default : switch (label) {
                                                    case    '' :
                                                    case 'n/a' : $('#modal-map-title').text('Landmark');
                                                                 break;
                                                       default : $('#modal-map-title').text('Landmark: '+label);
                                                }
            }

            Core.FixModal.FixModal('modal-map-container');

        });   

    },

    isLoaded: function() {
console.log('Core.isLoaded()');

        var context = Core.Environment.context().split('/');
        if(context[0]=='repo'){
            repoKey=context[1];
            Core.map = Map.initMap('map-div');
            var h = $(window).height() - $('#map-div').offset().top - 5;
            $('#map-div').css({ height: h+'px' });
            $('#details-div').css({ height: h+'px' , 'overflow-y': 'auto' });
            $('#report-div').css({ height: h+'px' });
            Core.Map.Refresh('Core.map','',1);
            Core.DataTable.pagedReport('stops-report-frequent');
        } else {
            Core.Metrics();
            $('#stops-report-all').find('select.dataTables-length').val('5');
            $('#stops-report-frequent').find('select.dataTables-length').val('5');
            $('#stops-report-recent').find('select.dataTables-length').val('5');
            $('#verification-report-recent').find('select.dataTables-length').val('5');
        }

        $(document).on('click', '.wizard-edit', function() {
            if(!(currentLink2Input)){
                currentLink2Input=1;
                $(this).find('.wizard-clickable').trigger('click');
                setTimeout("currentLink2Input='';",1000);
            }
        });

        $(document).on('click', '.wizard-select', function() {
            if(!(currentLink2Select)){
                currentLink2Select=1;
                $(this).find('.wizard-clickable').trigger('click');
                setTimeout("currentLink2Select='';",1000);
            }
        });

        $(document).on('change', '#alert-edit-landmarkmode', function() {
            switch($(this).val()){

                case         '1' :  $('#alert-edit-landmark').trigger('change');
                                    break;

                case         '2' :  $('#alert-edit-landmarkgroup').trigger('change');
                                    break;

            }
        });

        $(document).on('click', '#breadcrumbs', function() {
            breadcrumbs=1;
            $.each($(this).closest('.tab-pane').find('.dataTables-search-btn'), function(k,v){
console.log('breadcrumbs:'+$(this).closest('.report-master').attr('id'));
                if($(this).is(':visible')){
                    $(this).trigger('click');
                }
            });
        });

        $(document).on('click', '.btn, .viewport-adjust-layout', function() {
            switch($(this).attr('id')){
                case          'btn-devices-exporting' : $('#btn-devices-exporting').addClass('active');
                                                        $('#btn-devices-importing').removeClass('active');
                                                        $('#route-exporting').show();
                                                        $('#route-importing').hide();
                                                        setTimeout("Core.DataTable.pagedReport('devices-exporting')",1);
                                                        break;
                case          'btn-devices-importing' : $('#btn-devices-importing').addClass('active');
                                                        $('#btn-devices-exporting').removeClass('active');
                                                        $('#route-importing').show();
                                                        $('#route-exporting').hide();
                                                        setTimeout("Core.DataTable.pagedReport('devices-importing')",1);
                                                        break;
                case 'btn-edit-vehicle-group-devices' : $('#btn-edit-vehicle-group-devices').addClass('active');
                                                        $('#btn-edit-vehicle-group-users').removeClass('active');
                                                        break;
                case   'btn-edit-vehicle-group-users' : $('#btn-edit-vehicle-group-users').addClass('active');
                                                        $('#btn-edit-vehicle-group-devices').removeClass('active');
                                                        break;
                case              'btn-info-customer' : $('#btn-info-customer').addClass('active');
                                                        $('#btn-info-device').removeClass('active');
                                                        $('#btn-info-vehicle').removeClass('active');
                                                        break;
                case                'btn-info-device' : $('#btn-info-customer').removeClass('active');
                                                        $('#btn-info-device').addClass('active');
                                                        $('#btn-info-vehicle').removeClass('active');
                                                        break;
                case               'btn-info-vehicle' : $('#btn-info-customer').removeClass('active');
                                                        $('#btn-info-device').removeClass('active');
                                                        $('#btn-info-vehicle').addClass('active');
                                                        break;
                case                      'btn-tab-1' : $('#btn-tab-1').addClass('active');
                                                        $('#btn-tab-2').removeClass('active');
                                                        $('#btn-tab-3').removeClass('active');
                                                        break;
                case                      'btn-tab-2' : $('#btn-tab-2').addClass('active');
                                                        $('#btn-tab-1').removeClass('active');
                                                        $('#btn-tab-3').removeClass('active');
                                                        Core.fixListGroup('modal-edit-user');
                                                        break;
                case                      'btn-tab-3' : $('#btn-tab-3').addClass('active');
                                                        $('#btn-tab-1').removeClass('active');
                                                        $('#btn-tab-2').removeClass('active');
                                                        Core.fixListGroup('modal-edit-user');
                                                        break;
                case                  'btn-stops-all' : 
                case   'vehicle-detail-quick-history' : $('#btn-stops-all').addClass('active');
                                                        $('#btn-stops-frequent').removeClass('active');
                                                        $('#btn-stops-recent').removeClass('active');
                                                        $('#div-stops-duration').hide();
                                                        Core.DataTable.pagedReport('stops-report-all');
                                                        break;
                case             'btn-stops-frequent' : $('#btn-stops-all').removeClass('active');
                                                        $('#btn-stops-frequent').addClass('active');
                                                        $('#btn-stops-recent').removeClass('active');
                                                        $('#div-stops-duration').show();
                                                        Core.DataTable.pagedReport('stops-report-frequent');
                                                        break;
                case               'btn-stops-recent' : $('#btn-stops-all').removeClass('active');
                                                        $('#btn-stops-frequent').removeClass('active');
                                                        $('#btn-stops-recent').addClass('active');
                                                        $('#div-stops-duration').show();
                                                        Core.DataTable.pagedReport('stops-report-recent');
                                                        break;
               case 'vehicle-detail-verification-tab' : Core.DataTable.pagedReport('verification-report-recent');
                                                        break;
               case     'vehicle-detail-commands-tab' : 
               case         'vehicle-detail-info-tab' : window.setTimeout("Vehicle.Common.DetailPanel.open('"+$(this).attr('id')+"')",1);
                                                        break;
                        
            }
        });

        $(document).on('click', '#sidebar-left-toggle, #sidebar-left-link', function() {
            var left = 0;
            var $sidebarLeft = $('#sidebar-left');
            var $sidebarLeftLink = $('#sidebar-left-link');
console.log("$(document).on('click', '#sidebar-left-toggle'):"+$sidebarLeft.width()+':'+$sidebarLeft.offset().left);
            switch($sidebarLeft.offset().left){
                case                        0 : left = -201;
                                                $sidebarLeft.removeClass('ready-for-touch');
                                                break;
                                      default : left = 0;
                                                $sidebarLeftLink.show();
                                                $sidebarLeft.addClass('ready-for-touch');
            }
            $sidebarLeft.css({ left: left });
console.log("$(document).on('click', '#sidebar-left-toggle'):"+$sidebarLeft.width());
        });

        $(document).on('click', '#hide-vehicle-panel', function() {
            lastMapDTS='';
            currentUnitIdHidePanel=currentUnitId;
            Core.Viewport.adjustLayoutGo();
        });

        $(document).on('click', '#popndrop-landmark-panel, #popndrop-vehicle-panel', function() {
            $(this).toggleClass('active');
            if($(this).hasClass('active')){
                $(this).html('<img src="/assets/media/icons/up.gif" title="Up">');
            } else {
                $(this).html('<img src="/assets/media/icons/down.gif" title="Down">');
            }
            Core.Viewport.adjustLayoutGo(1);
        });

        $(document).on('touchstart', '.popup-landmark-panel, .popup-vehicle-panel, #secondary-sidebar-scroll li', function() {
console.log("$(document).on('touchstart', '.popup-landmark-panel, .popup-vehicle-panel, #secondary-sidebar-scroll li', function() {");
            $(this).trigger('click');
            setTimeout('Core.Viewport.adjustLayout()',500);
            var $sidebarLeft = $('#sidebar-left');
            if($sidebarLeft.hasClass('ready-for-touch')) {
                switch(Core.Environment.context()){
                    case            'report/list' :
                    case           'report/list#' : break;
                                          default : setTimeout("$('#sidebar-left-link').trigger('click');",2000);
                } 
            }
        });

        $(document).on('click', '.popup-landmark-panel, .popup-vehicle-panel, #secondary-sidebar-scroll li', function() {
console.log("$(document).on('click', '.popup-landmark-panel, .popup-vehicle-panel, #secondary-sidebar-scroll li', function() {");
            if($('#popndrop-vehicle-panel').attr('id')){
                $('#popndrop-vehicle-panel').removeClass('active');
                $('#popndrop-vehicle-panel').html('<img src="/assets/media/icons/down.gif" title="Down">');
            } else if($('#popndrop-landmark-panel').attr('id')){
                $('#popndrop-landmark-panel').removeClass('active');
                $('#popndrop-landmark-panel').html('<img src="/assets/media/icons/down.gif" title="Down">');
            }
            // Core.Viewport.adjustLayoutGo();
        });

        $(document).on('click', '#hide-landmark-panel', function() {
            setTimeout('Core.Viewport.adjustLayout()',1);
        });

        $(document).on('click', '.create-update', function() {
console.log('.create-update:click:'+$(this).attr('id'));
            if(!(createUpdateCount)){
                createUpdateCount=1;
                Core.DataTable.createUpdate($(this).attr('id'));
                setTimeout('createUpdateCount=0;',5000);
            }
        });

        $(document).on('click', '.close-form', function() {
console.log('.close-form:click:'+$(this).attr('id'));
            switch($(this).attr('id')){
                case        'user-edit-close' : setTimeout("$('#users-users-table').find('.dataTables-search-btn').trigger('click')",2000);
                                                break;
            }
        });

        $(document).on('click', '.dataTables-begin', function() {
            Core.DataTable.pagedReport($(this).closest('div.report-master').attr('id'),'begin');
        });

        $(document).on('click', '.dataTables-previous', function() {
            Core.DataTable.pagedReport($(this).closest('div.report-master').attr('id'),'down');
        });

        $(document).on('click', '.dataTables-next', function() {
            Core.DataTable.pagedReport($(this).closest('div.report-master').attr('id'),'up');
        });

        $(document).on('click', '.dataTables-end', function() {
            Core.DataTable.pagedReport($(this).closest('div.report-master').attr('id'),'end');
        });

        $(document).on('click', '.permission-checkbox label', function() {
            var cb = $(this).closest('div').find('input');
            if((cb.attr('id'))&&(!(cb.is(':disabled')))){
                if(cb.is(':checked')){
                    cb.prop('checked',false);
                } else {
                    cb.prop('checked',true);
                }
                Core.Ajax(cb.attr('id'),cb.is(':checked'),'','permission');
            }
        });

        $(document).on('change', '.quick-history-options', function() {
            var pid='';
            if($('#btn-stops-all').hasClass('active')){
                pid='stops-report-all';
            } else if($('#btn-stops-recent').hasClass('active')){
                pid='stops-report-recent';
            } else if($('#btn-stops-frequent').hasClass('active')){
                pid='stops-report-frequent';
            }
            if(pid){
                var pag=$('#'+pid).find('dataTables-current-page').text();
                Core.DataTable.pagedReport(pid,pag);
            }
        });

        $(document).on('change', '.sidebar-options', function() {
            Core.DataTable.secondarySidepanelScroll(1);
        });

        $(document).on('keyup', '#sidebar-search-term', function() {
            Core.DataTable.secondarySidepanelScroll();
        });

        $(document).on('click', '.all-none', function( event ) {

            Map.closeInfoWindow(Vehicle.Map.map);
            if(($('#sidebar-left-link').is(':visible'))&&($('#sidebar-left').offset().left===0)&&($(this).attr('id')!='none')){
                $('#sidebar-left-link').trigger('click');
            }

            if(allClear){

                var mode = $(this).attr('id');

                $('#detail-panel').hide();
                Map.clearMarkers(Vehicle.Map.map);
                if(((mode!='all')&&(mode!='drill')&&(mode!='drill-in')&&(mode!='drill-out'))||(allNone!=1)||(allNoneNewList)){
                    allNoneNewList=0;
console.log('resetting the map');
                    Core.Viewport.adjustLayout();
                    Map.resetMap(Vehicle.Map.map);
                }
                Map.resize(Vehicle.Map.map);
                setTimeout("Map.resize(Vehicle.Map.map)",600);

                switch(mode){

                    case                    'all' :
                    case                  'drill' :
                    case               'drill-in' :
                    case              'drill-out' : $('#div-all-none').css({ display: 'none'});
                                                    $('#div-all-none-clear').css({ display: 'block'});
                                    console.log('justTheseDevices');
                                    console.log(justTheseDevices);
                                                    var allIds = [] ;
                                                    var allNames = [] ;
                                                    $.each($('#secondary-sidebar-scroll').find('li'), function(k,v){
                                                        $(this).removeClass('active');
                                                        $(this).removeClass('all-none-active');
                                                        if((!($(this).find('a').hasClass('li-inventory')))&&(!($(this).find('a').hasClass('li-reposession')))){
                                                            if(justTheseDevices[0]>0){
                                                                if ( $.inArray($(this).attr('id').split('-').pop(),justTheseDevices) > -1 ) {
                                                                    $(this).addClass('all-none-active');
                                                                    allIds.push($(this).attr('id').split('-').pop());
                                                                    allNames.push($(this).find('a').text());
                                                                }
                                                            } else {
                                                                $(this).addClass('all-none-active');
                                                                allIds.push($(this).attr('id').split('-').pop());
                                                                allNames.push($(this).find('a').text());
                                                            }
                                                        }
                                                    });
                                                    justTheseDevices=[];
                                                    if(allIds){
                                                        allNone=1;
                                                        var zoomIn = 0;
                                                        if($(this).attr('id')=='drill-out'){
                                                            zoomIn = Map.mapZoomGet(Vehicle.Map.map) - 1;
                                                        } else if($(this).attr('id')=='drill-in'){
                                                            zoomIn = Map.mapZoomGet(Vehicle.Map.map) + 1;
                                                        } else if($(this).attr('id')=='all'){
                                                            zoomIn = 6;
                                                        } else {
                                                            zoomIn = Map.mapZoomGet(Vehicle.Map.map) + 4;
                                                        }
                                                        Core.Ajax(zoomIn,allNames,allIds,'allDevices',$(this).attr('id'));
                                                    }
                                                    break;

                    case                   'none' : $('#div-all-none-clear').css({ display: 'none'});
                                                    $('#div-all-none').css({ display: 'block'});
                                                    allNone=0;
                                                    $.each($('#secondary-sidebar-scroll').find('li'), function(k,v){
                                                        $(this).removeClass('active');
                                                        $(this).removeClass('all-none-active');
                                                    });
                                                    break;

                }
    

            }
            
        });

        $(document).on('click', '.alert-edit-cancel' , function() {
            $('.modal-dialog-backdrop').remove();
            $('#modal-edit-alert').hide();
            $('#modal-edit-alert').removeClass('in');
            $('#modal-edit-alert').attr('aria-hidden',true);
        });

        $(document).on('click', '.alert-edit', function() {
            $('#modal-edit-alert').show();
            $('#modal-edit-alert').addClass('in');
            $('#modal-edit-alert').attr('aria-hidden',false);
            Core.CenterModal('modal-edit-alert');            
        });

        $(document).on('click', '#batch-add', function() {
            $('#batch-devices-available').find('li.active').each(function() {
                $('#batch-devices').append($(this).find('span').html()+"\r\n");
                $(this).removeClass('active');
            });
        });

        $(document).on('click', '#batch-remove', function() {
            $('#batch-devices').text('');
        });

        $(document).on('click', '#batch-commands-confirm', function() {
            if(!($('#batch-command').val())){
                alert('Batch Command Missing');
            } else {
                Core.Ajax('batch-commands',$('#batch-devices').val(),$('#batch-command').val(),'batch-commands');
                $('#batch-commands-cancel').trigger('click');
            }
        });

        $(document).on('click', '#batch-import-csv-file', function() {
            $('#batch-import-confirm').removeClass('disabled');
        });

        $(document).on('click', '.contacts-list', function() {
            $('#contacts-contacts-table').find('.dataTables-search-btn').trigger('click');
        });

        $(document).on('click', '.contactgroups-list', function() {
            $('#contacts-groups-table').find('.dataTables-search-btn').trigger('click');
        });

        $(document).on('click', '.div-nav', function() {
            var eid = $(this).attr('id').split('-').pop();
            $.each($('.div-nav-div'), function() {
                $(this).hide();
            });
            $('.div-nav-'+eid).show();
            $.each($(this).closest('ul.nav').find('li'), function() {
                $(this).removeClass('active');
                $(this).find('a').removeClass('active');
            });
            $(this).addClass('active');
            $(this).closest('li').addClass('active');
        });

        $(document).on('click', '.edit-contact', function() {
            $('#modal-edit-contact').show();
            $('#modal-edit-contact').addClass('in');
            $('#modal-edit-contact').attr('aria-hidden',false);
            Core.CenterModal('modal-edit-contact');            
        });

        $(document).on('click', '.edit-contact-cancel', function() {
            $('.modal-dialog-backdrop').remove();
            $('#modal-edit-contact').hide();
            $('#modal-edit-contact').removeClass('in');
            $('#modal-edit-contact').attr('aria-hidden',true);
            $('#contacts-contacts-table').find('.dataTables-search-btn').trigger('click');
        });

        $(document).on('click', '.edit-contact-group', function() {
            $('#modal-edit-contact-group').show();
            $('#modal-edit-contact-group').addClass('in');
            $('#modal-edit-contact-group').attr('aria-hidden',false);
            Core.CenterModal('modal-edit-contact-group');            
        });

        $(document).on('click', '.edit-contact-group-cancel', function() {
            $('.modal-dialog-backdrop').remove();
            $('#modal-edit-contact-group').hide();
            $('#modal-edit-contact-group').removeClass('in');
            $('#modal-edit-contact-group').attr('aria-hidden',true);
            $('#contacts-groups-table').find('.dataTables-search-btn').trigger('click');
        });

        $(document).on('click', '.landmarkgroup-edit', function() {
            $('#modal-edit-landmark-group').show();
            $('#modal-edit-landmark-group').addClass('in');
            $('#modal-edit-landmark-group').attr('aria-hidden',false);
            Core.CenterModal('modal-edit-landmark-group');            
        });

        $(document).on('click', '.landmark-fix-close', function() {
            $('.modal-dialog-backdrop').remove();
            $('#modal-fix-landmark').hide();
            $('#modal-fix-landmark').removeClass('in');
            $('#modal-fix-landmark').attr('aria-hidden',true);
        });

        $(document).on('click', '.landmark-fix', function() {

            var landmark_id   = $(this).attr('id').split('-').pop();
            $('#landmark-fix-name').val($(this).attr('data-name'));
            $('#landmark-fix-street-address').val($(this).attr('data-streetaddress'));
            $('#landmark-fix-city').val($(this).attr('data-city'));
            $('#landmark-fix-state').val($(this).attr('data-state'));
            $('#landmark-fix-zipcode').val($(this).attr('data-zipcode'));
            $('#landmark-fix-country').val($(this).attr('data-country'));
            $('#landmark-fix-resubmit').attr('data-landmark-id',landmark_id);

            Core.AddMap.Address(1);
            
            console.log('landmark-fix:landmark_id:'+landmark_id);

            $('#modal-fix-landmark').show();
            $('#modal-fix-landmark').addClass('in');
            $('#modal-fix-landmark').attr('aria-hidden',false);
            Core.CenterModal('modal-fix-landmark');
            // Map.openInfoWindow(Landmark.Common.map, 'landmark', landmarkdata.latitude, landmarkdata.longitude, landmarkdata);

            Core.FixModal.FixModal('landmark-fix');

        });

        $(document).on('click', '#landmark-fix-resubmit', function() {
            var landmark_id = $('#landmark-fix-resubmit').attr('data-landmark-id');
            var name = $('#landmark-fix-name').val();
            var streetaddress = $('#landmark-fix-street-address').val();
            var city = $('#landmark-fix-city').val();
            var state = $('#landmark-fix-state').val();
            var zipcode = $('#landmark-fix-zipcode').val();
            var country = $('#landmark-fix-country').val();
            var shape = $('#landmark-fix-shape').val();
            var radius = $('#landmark-fix-radius').val();
            var group = $('#landmark-fix-group').val();
            var category = $('#landmark-fix-category').val();
            var latitude = $('#landmark-fix-latitude').html();
            var longitude = $('#landmark-fix-longitude').html();

            if(landmark_id){

                $.ajax({
                    url: '/ajax/core/fixLandmark',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        territoryupload_id: landmark_id,
                        name: name,
                        streetaddress: streetaddress,
                        city: city,
                        state: state,
                        zipcode: zipcode,
                        country: country,
                        shape: shape,
                        radius: radius,
                        group: group,
                        category: category,
                        latitude: latitude,
                        longitude: longitude
                    },
                    success: function(responseData) {
                        console.log(responseData);
                        $('#landmark-fix-cancel').trigger('click');
                        $('#landmark-incomplete-table').find('.dataTables-search-btn').trigger('click');
                    }
                });

            }

        });

        $(document).on('click', '.landmark-edit', function() {

            var landmark_id = $(this).attr('id').split('-').pop();

            console.log('landmark_id:'+landmark_id);

            if ( (landmark_id) && ( (Core.Environment.context() == 'landmark/list') || (Core.Environment.context() == 'landmark/verification') ) ) {

                currentLandmarkId=landmark_id;

                $.ajax({
                    url: '/ajax/landmark/getLandmarkByIds',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        territory_id: landmark_id
                    },
                    success: function(responseData) {
console.log("$(document).on('click', '.landmark-edit', function() {");

                        if (responseData.code === 0) {

                            var landmarkData = responseData.data;

                            // Update the Map  (if in landmark/list context)
                            if ( (Core.Environment.context() == 'landmark/list') || (Core.Environment.context() == 'landmark/verification') ) {

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
console.log(polygonOptions);

                                    Map.clearMarkers(Landmark.Common.map);
                                    Map.addMarkerWithPolygon(Landmark.Common.map, markerOptions, false, polygonOptions);
                                    // $self.data('latitude', landmarkData.latitude)
                                    //      .data('longitude', landmarkData.longitude);

                                    lastPolygon = landmarkData.coordinates ;

                                    // $('#landmark-li-'+landmarkId).attr('data-shape',landmarkData.shape);
                                    // $('#landmark-li-'+landmarkId).attr('data-radius',landmarkData.radius);
                                    // $('#landmark-li-'+landmarkId).attr('data-points',landmarkData.coordinates);
                                    // $('#landmark-li-'+landmarkId).attr('data-lat',landmarkData.latitude);
                                    // $('#landmark-li-'+landmarkId).attr('data-long',landmarkData.longitude);
                                    // $('#landmark-li-'+landmarkId).attr('data-event-id',landmarkData.territory_id);
                                    // $('#landmark-li-'+landmarkId).attr('data-event',landmarkData.territoryname);
                            
                                    var landmarkdata = responseData.data;
                                    Landmark.Common.DetailPanel.render(landmarkdata);
                                    $('#modal-edit-landmark').show();
                                    $('#modal-edit-landmark').addClass('in');
                                    $('#modal-edit-landmark').attr('aria-hidden',false);
                                    var w = $(window).width() - $('#modal-edit-landmark').find('.modal-content').width();
                                    var h = $(window).height() - Math.round(w/2);
                                    $('#modal-edit-landmark').find('.modal-content').css({ height: h+'px'});
                                    var hh = h - 220;
                                    $('#map-div').css({ height: hh+'px'});
console.log("$('#map-div').attr('height'):"+$('#map-div').attr('height'));
                                    Core.CenterModal('modal-edit-landmark');
                                    Core.Map.Refresh('Landmark.Common.map','',1,landmarkdata.latitude,landmarkdata.longitude);
                                    Map.openInfoWindow(Landmark.Common.map, 'landmark', landmarkdata.latitude, landmarkdata.longitude, landmarkdata);

                                    if (landmarkData.shape == 'rectangle' || landmarkData.shape == 'polygon') {

                                        var title   = landmarkData.territoryname,
                                            radius  = landmarkData.radius,
                                            map     = Landmark.Common.getCurrentMap()
                                        ;
                                        
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
                                            },
                                            drag: function(data) {
                                                if($('.leaflet-popup').is(':visible')){
                                                    $('.leaflet-popup').hide();
                                                }
                                                var title = landmarkData.territoryname,
                                                    radius = landmarkData.radius
                                                ;
                                                Map.updateTempLandmark(map, landmarkData.shape, data.latitude, data.longitude, radius, title);
                                                $locate.val('Waiting...');                                      
                                            },
                                            dragend: function(data) {
                                                // Map.centerMap(map, data.latitude, data.longitude);
                                                Map.reverseGeocode(map, data.latitude, data.longitude, function(data1) {
                                                    if (data1.success == 1) {
                                                        // $locate.val(data1.formatted_address);
                                                        // $addButton.data('latitude', data.latitude)
                                                        //           .data('longitude', data.longitude)
                                                        //           .data('street-address', data1.address_components.address)
                                                        //           .data('city', data1.address_components.city)
                                                        //           .data('state', data1.address_components.state)
                                                        //           .data('zip', data1.address_components.zip)
                                                        //           .data('country', data1.address_components.country);                                                    
                                                    } else {
                                                        // $locate.val('').text('');
                                                        // alert(data1.error);
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

                                        Map.clearMarkers(Landmark.Common.map);
                                        Map.removeTempLandmark(map);
                                        $.each(landmarkData.coordinates, function(k,v) {
                                            if(k<1){
                                                Map.createTempLandmark(map, landmarkData.shape, v.latitude, v.longitude, radius, title, true, {}, events);                                    
                                            } else {
                                                Map.updateTempLandmark(map, landmarkData.shape, v.latitude, v.longitude, radius, title, events, true);
                                            }
                                        });

                                        $.each($('.leaflet-marker-icon'), function(k,v){
                                            $(this).removeClass('mapbox_temp_landmark_icon');
                                            $(this).removeClass('mapbox_temp_landmark_icon_active');
                                            if(k>0){
                                                $(this).addClass('mapbox_temp_landmark_icon');
                                            } else {
                                                $(this).addClass('mapbox_temp_landmark_icon_active');
                                            }
                                        });
                                        
                                    }

                                }
                                                                                
                            }

                        } else {
                            if ($.isEmptyObject(responseData.validation_error, responseData.code) === false) {
                                //  display validation errors
                            }
                        }
                        
                        if ($.isEmptyObject(responseData.message) === false) {
                            //  display message
                            // Core.SystemMessage.show(responseData.message, responseData.code);
                        }
                    }
                });
            }
        });

        $(document).on('click', '.vehicle-edit', function() {

            var unit_id = $(this).attr('id').split('-').pop();

            console.log('unit_id:'+unit_id);

            if ( (unit_id) && (Core.Environment.context() == 'vehicle/list') ) {

                currentUnitId=unit_id;

                $.ajax({
                    url: '/ajax/vehicle/getVehicleInfo',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        unit_id: unit_id
                    },
                    success: function(responseData) {

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

                        if (responseData.code === 0) {

                            var unitdata = {},
                                eventdata = {},
                                eventname = ''
                            ;

                            unitdata = responseData.data;
                            eventdata = unitdata.eventdata;
console.log('unitdata.moving.state:'+unitdata.moving.state);
                            if(unitdata.moving.state==='I'){
                                eventname = 'Inventory' ;
                            } else {
                                eventname = eventdata.eventname ;
                            }
    
                            Map.clearMarkers(Vehicle.Map.map);
                            
                            var markerOptions = {
                                    id: unitdata.unit_id,
                                    name: unitdata.unitname,
                                    latitude: eventdata.latitude,
                                    longitude: eventdata.longitude,
                                    eventname: eventname, // used in map class to get vehicle marker color
                                    click: function() {
                                        $('#vehicle-list-table-'+unit_id).trigger('click');
                                    }
                                }
                            ;

                            Map.addMarker(Vehicle.Map.map, markerOptions, true);

                            // $self.data('event-id', eventdata.id)
                            //      .data('latitude', eventdata.latitude)
                            //      .data('longitude', eventdata.longitude);
                            

                            var unitdata = responseData.data;
                            Vehicle.Common.DetailPanel.render(unitdata);
                            $('#modal-edit-vehicle-list').show();
                            $('#modal-edit-vehicle-list').addClass('in');
                            $('#modal-edit-vehicle-list').attr('aria-hidden',false);
                            Core.CenterModal('modal-edit-vehicle-list');
                            $('#vehicle-detail-info-tab').trigger('click');
                            lastLatVehicle = eventdata.latitude;
                            lastLongVehicle = eventdata.longitude;
                            currentUnitIdHidePanel='99999';
                            Core.Map.Refresh('Vehicle.Map.map','',1,eventdata.latitude,eventdata.longitude);
                            Map.openInfoWindow(Vehicle.Map.map, 'unit', eventdata.latitude, eventdata.longitude, eventdata, unitdata.moving, unitdata.battery, unitdata.signal, unitdata.satellites);
                        } else {
                            if ($.isEmptyObject(responseData.validaton_errors) === false) {
                                //  display validation errors
                                alert(responseData.validaton_errors);
                            }
                        }

                        if ($.isEmptyObject(responseData.message) === false) {
                            //  display messages
                            // alert(responseData.message);
                        }
                    }
                });
            }
        });

        $(document).on('click', '#mark-for-transfer', function() {
            // $('#modal-transfer-authorize-release').showm
        });

        $(document).on('click', '.vehicle-edit-close', function() {
            $('.modal-dialog-backdrop').remove();
            if (Core.Environment.context() == 'vehicle/list') {
                $('#modal-edit-vehicle-list').attr('aria-hidden',true);
                $('#modal-edit-vehicle-list').removeClass('in');
                $('#modal-edit-vehicle-list').hide();
            }
        });

        $(document).on('click', '.edit-landmark-close', function() {
            $('.modal-dialog-backdrop').remove();
            if ( (Core.Environment.context() == 'landmark/list') || (Core.Environment.context() == 'landmark/verification') ) {
                $('#modal-edit-landmark').attr('aria-hidden',true);
                $('#modal-edit-landmark').removeClass('in');
                $('#modal-edit-landmark').hide();
            }
            switch(Core.Environment.context()){

                case  'landmark/verification' : $('#landmark-verification-table').find('.dataTables-search-btn').trigger('click');
                                                break;

            }
        });

        $(document).on('click', '.edit-landmark-group-close', function() {
            $('.modal-dialog-backdrop').remove();
            if (Core.Environment.context() == 'landmark/group') {
                $('#modal-edit-landmark-group').attr('aria-hidden',true);
                $('#modal-edit-landmark-group').removeClass('in');
                $('#modal-edit-landmark-group').hide();
                $('#landmark-group-table').find('.dataTables-search-btn').trigger('click');
            }
        });

        $(document).on('keydown', '.wizard-input', function( event ) {
            if ( event.which == 13 ) {
                $(this).trigger('blur');
            }
        });

        $(document).on('change', '.dataTables-length, .sidebar-filter', function() {
        
            var pid='';

            switch(Core.Environment.context()){
                case               'alert/contact' : pid='contacts-contacts-table';
                                                     if(!($('#'+pid).is(':visible'))){
                                                        pid='contacts-groups-table';
                                                     }
                                                     break;
                case               'alert/history' : pid='alert-history-table';
                                                     break;
                case                  'alert/list' : pid='alert-list-table';
                                                     break;
                case                'admin/export' : pid='devices-importing';
                                                     if(!($('#'+pid).is(':visible'))){
                                                        pid='devices-exporting';
                                                     }
                                                     break;
                case                  'admin/list' :
                case                'device/admin' :
                case                 'device/list' : pid='device-list-table';
                                                     break;
                case                 'admin/users' :
                case                  'users/list' : pid='users-users-table';
                                                     break;
                case             'admin/usertypes' :
                case                  'users/type' : pid='users-type-table';
                                                     break;
                case              'landmark/group' : pid='landmark-group-table';
                                                     break;
                case         'landmark/incomplete' : pid='landmark-incomplete-table';
                                                     break;
                case               'landmark/list' : pid='landmark-list-table';
                                                     break;
                case       'landmark/verification' : pid='landmark-verification-table';
                                                     break;
                case              'report/contact' : pid='contacts-contacts-table';
                                                     if(!($('#'+pid).is(':visible'))){
                                                        pid='contacts-groups-table';
                                                     }
                                                     break;
                case              'report/history' : pid='report-history-table';
                                                     break;
                case            'report/scheduled' : pid='report-scheduled-table';
                                                     break;
                case               'vehicle/group' : pid='vehicle-group-table';
                                                     break;
                case                'vehicle/list' : if($(this).hasClass('sidebar-filter')){
                                                        pid='vehicle-list-table';
                                                     } else {                
                                                        pid=$(this).closest('div.report-master').attr('id');
                                                     }                
                                                     break;
                case                 'vehicle/map' : pid=$(this).closest('div.report-master').attr('id');
                                                     break;
            }
console.log('Core.isLoaded:change:'+pid);
            if(pid){
                $('#'+pid).find('span.dataTables-page-count').html('1');
                Core.DataTable.pagedReport(pid);
            }
        });

        $(document).on('click', '#btn-repo', function() {
            Core.Ajax('repo',$('#repo').val(),currentUnitId,'repo');
        });

        $(document).on('keyup', '.dataTables-search, #sidebar-search', function() {
console.log('Core.isLoaded:keyup:'+$(this).closest('div.report-master').attr('id'));
            Core.DataTable.pagedReport($(this).closest('div.report-master').attr('id'));
        });

        $(document).on('click', '.dataTables-search-btn', function() {
console.log('Core.isLoaded:click:'+$(this).closest('div.report-master').attr('id'));
            Core.DataTable.pagedReport($(this).closest('div.report-master').attr('id'));
        });

        $(document).on('click', '.transfer-list li', function() {
console.log('Core.isLoaded:transfer-list li:click:'+$(this).attr('id'));
            $(this).toggleClass('active');
            var checkbox_id = $(this).attr('id');
            if($(this).hasClass('active')){                
                $('#input-add-user-vehicle-group-'+checkbox_id).prop('checked',true)
            } else {
                $('#input-add-user-vehicle-group-'+checkbox_id).prop('checked',false)
            }
        });

        $(document).on('click', '.selections-select-all, .selections-select-clear', function() {
            var id = $(this).closest('div.form-group').find('ul').attr('id');
            var allClear = $(this).hasClass('selections-select-all');
            var ids = [];
            $.each($(this).closest('div.form-group').find('ul').find('li'), function(){
                ids.push($(this).val());
            });
            Core.Ajax(id,allClear,$('#transfer-vehicle-group-devices-group-from').val(),'selectAllClear',ids);
        });

        $(document).on('click', '.selections-list li', function() {
console.log('Core.isLoaded:selectons-list li:click:'+$(this).attr('id'));
            if($(this).hasClass('active')){
                $(this).removeClass('active');
                $(this).find('input').prop('checked',false);
            } else {
                $(this).addClass('active');
                $(this).find('input').prop('checked',true);
            }
            Core.Ajax($(this).closest('ul').attr('id'),$(this).hasClass('active'),$('#transfer-vehicle-group-devices-group-from').val()+'-'+$(this).val(),'selections');
        });

        $(document).on('click', '.selections-select-all', function() {
            $(this).closest('div.form-group').find('li').addClass('active');
            $(this).closest('div.form-group').find('input').prop('checked',true);
        });

        $(document).on('click', '.selections-select-clear', function() {
            $(this).closest('div.form-group').find('li').removeClass('active');
            $(this).closest('div.form-group').find('input').prop('checked',false);
        });

        $(document).on('change', '#landmark-add-type', function() {
            if($('#div-landmark-add-type-other').is(':visible')){
                $('#div-landmark-add-type-other').hide();
            } else {
                $('#div-landmark-add-type-other').show();
            }
        });

        $(document).on('change', '#sidebar-contact-mode', function() {
            $('#div-sidebar-contact-single').hide();
            $('#div-sidebar-contact-group').hide();
            switch($('#sidebar-contact-mode').val()){
                case                       'single' :
                case                        'group' : $('#div-sidebar-contact-'+$('#sidebar-contact-mode').val()).show();
                                                      break;
            }
        });

        if($('#secondaryNav').length > 0){
            var offset = $('#secondaryNav').offset().top+4;
            var newheight=$(window).height()-offset;
            if(newheight<100){
            } else if ($(document).find('body').find('.wrap').height()!=newheight) {
                $(document).find('body').find('.wrap').height(newheight+'px');
console.log('Wrap: Resized ('+$(document).find('body').find('.wrap').height()+'='+$(window).height()+'-'+offset+') ... '+newheight);
            }             
        }
        
        // $(document).on('click', '.wizard-caret', function() {
        //     $(this).closest('.wizard-div').find('a.wizard-editable').trigger('click');
        // });

        $(document).on('click', '.verification-add-show', function() {
            $('#verification-address-address').val('');
            $('#verification-address-name').val('');
            verificationAddAddress = '';
            verificationAddCity = '';
            verificationAddState = '';
            verificationAddZip = '';
            verificationAddCountry = '';
            verificationAddLat = '';
            verificationAddLng = '';
            $('#div-verification-address-report').css({ display: 'none' });
            $('#div-verification-address-add').css({ display: 'block' });
            Vehicle.Common.DetailPanel.open('tab-verification');
        });

        $('#verification-address-address').bind("change keyup input",function() {
            Core.TriggerDelay('verification-address-locate');
        });

        $(document).on('change', '#verification-address-radius', function() {
            Core.TriggerDelay('verification-address-locate');
            triggerDelay = 1;
        });

        $(document).on('click', '#verification-add-cancel', function() {
            $('#div-verification-address-add').css({ display: 'none' });
            $('#div-verification-address-report').css({ display: 'block' });
        });

        $(document).on('click', '#verification-add-confirm', function() {
            var vAddress = [ $('#verification-address-name').val() , verificationAddLat , verificationAddLng , verificationAddAddress , verificationAddCity , verificationAddState , verificationAddZip , verificationAddCountry , $('#verification-address-radius').val() ] ;
            Core.Ajax('verification-address-new',vAddress,currentUnitId,'verification-address-new','verification-address-new');
            $('#div-verification-address-add').css({ display: 'none' });
            $('#div-verification-address-report').css({ display: 'block' });
        });

        $(document).on('change', '.transfer-form', function() {
            $(this).closest('div.form-group').find('.transfer-search-btn').trigger('click');
        });

        $(document).on('change', '#edit-vehicle-group-title-edit', function() {
            switch($(this).val().trim()){
                case          '' :  alert('Groups May Not Be Blank');
                                    break;
                case   'Default' :  alert('Default is a Reserved Group Name');
                                    break;
                         default : Core.Ajax($(this).attr('id'),$(this).val(),$(this).data('id'),'update');
            }
        });

        $(document).on('keyup', '.transfer-search', function() {
            // $(this).closest('div').find('.transfer-search-btn').trigger('click');
        });

        $(document).on('click', '.transfer-search-btn', function() {
            $(this).closest('div.form-group').find('ul').empty();
            Core.Ajax($(this).attr('id'),$(this).closest('div.transfer-group').find('input').val(),$(this).closest('div.form-group').find('select').val(),'load-transfer-devices');
        });

        $(document).on('click', '.transfer-select-all', function() {
            $(this).closest('div.form-group').find('li').addClass('active');
            $(this).closest('div.form-group').find('li').find('input:checkbox').prop('checked',true);
        });

        $(document).on('click', '.transfer-select-clear', function() {
            $(this).closest('div.form-group').find('li').removeClass('active');
            $(this).closest('div.form-group').find('li').find('input:checkbox').prop('checked',false);
        });

        $(document).on('click', '.input-datepicker', function() {
console.log('input-datepicker');
            datePickerId = $(this).attr('id');
        });

        $(document).on('click', '#landmark-delete-button', function() {
            Core.Wizard.DeleteRecord($(this).attr('id'),currentLandmarkId,'delete-landmark');
        });

        $(document).on('click', '.day', function() {
            if(datePickerId){
                var my = $('th.datepicker-switch').html()+'  ';
                var m='';
                var y=my.split(' ')[1];
                switch(my.split(' ')[0]){
                    case      'January' : m='01'; break;
                    case     'February' : m='02'; break;
                    case        'March' : m='03'; break;
                    case        'April' : m='04'; break;
                    case          'May' : m='05'; break;
                    case         'June' : m='06'; break;
                    case         'July' : m='07'; break;
                    case       'August' : m='08'; break;
                    case    'September' : m='09'; break;
                    case      'October' : m='10'; break;
                    case     'November' : m='11'; break;
                    case     'December' : m='12'; break;
                }
                $('#'+datePickerId).html(m+'/'+$(this).html()+'/'+y);
                switch(datePickerId){
                    case   'vehicle-install-date' : Core.Ajax(datePickerId,$('#'+datePickerId).html(),currentUnitId,'update');
                                                    break;
                }
            }
            datePickerId='';
            // $('.datepicker-days').hide();
            $('.datepicker').hide();
        });

        $(document).on('click', '.btn-transfer', function() {

            if(bool_loadTransferDevices){

    console.log('btn-transfer');
                var source = '';
                var target = '';
                var targetId=1;
                var greenLight=0;
                var targetReturn='Target Group Missing';
                switch($(this).attr('id')){
                    case      'edit-vehicle-group-devices-assign' : targetId = $('#transfer-vehicle-group-devices-group-to').val();
                                                                    break;
                }

                if(targetId){
                    if($(this).hasClass('transfer-to')){
                        source = $(this).closest('.tab-pane').find('ul.list-available');
                        target = $(this).closest('.tab-pane').find('ul.list-assigned');
                        source.find('li').filter('.active').each(function(){
                            greenLight++;
                        });
                        if(greenLight){
                            source.find('li').filter('.active').detach().appendTo(target);
                            target.find('li').removeClass('active');
                            Core.SortList(target.attr('id'));
                            Core.SaveList(target.attr('id'));
                        } else {
                            alert('Please select at least one option to transfer...');
                        }
                    } else if($(this).hasClass('transfer-from')){
                        source = $(this).closest('.tab-pane').find('ul.list-assigned');
                        target = $(this).closest('.tab-pane').find('ul.list-available');
                        source.find('li').filter('.active').each(function(){
                            greenLight++;
                        });
                        if(greenLight){
                            source.find('li').filter('.active').detach().appendTo(target);
                            target.find('li').removeClass('active');
                            Core.SortList(target.attr('id'));
                            if($(this).hasClass('save-to-target')){
                                Core.SaveList(target.attr('id'));
                            } else {
                                Core.SaveList(source.attr('id'));
                            }
                        } else {
                            alert('Please select at least one option to transfer...');
                        }
                    } else {
                        console.log('btn-transfer:click:class-missing');
                    }
                } else {
                    alert(targetReturn);
                }

            } else {
                alert('Sorry, your data is still loading...');
            }

        });

        $(document).on('click', '.list-permissions li', function() {
            if(!(updateDisabled)){
                if(!(dblCheck)){
                    $(this).find('#input-'+$(this).attr('id')).trigger('click');
                }
                if($(this).find('#input-'+$(this).attr('id')).prop('checked')){
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
                Core.Ajax($(this).attr('id'),$(this).find('#input-'+$(this).attr('id')).prop('checked'),$('#user-type-edit-name').attr('data-uid'),'permission');
            }
        });

        $(document).on('click', 'a.permission-toggle-none', function() {
            if(!(updateDisabled)){
                var lids = [];
                $(this).closest('div.form-group').find('input.user-type-permission').each(function(){
                    this.checked = false;
                    $(this).closest('li').removeClass('active');
                    lids.push($(this).closest('li').attr('id'));
                });
                if(lids){
                    Core.Ajax(lids,'false',$('#user-type-edit-name').attr('data-uid'),'permissionsall');
                }
            }
        });

        $(document).on('click', 'a.permission-toggle-all', function() {
            if(!(updateDisabled)){
                var lids = [];
                $(this).closest('div.form-group').find('input.user-type-permission').each(function(){
                    this.checked = true;
                    $(this).closest('li').addClass('active');
                    lids.push($(this).closest('li').attr('id'));
                });
                if(lids){
                    Core.Ajax(lids,'true',$('#user-type-edit-name').attr('data-uid'),'permissionsall');
                }
            }
        });

        $(document).on('click', '.list-permission-groups li', function() {
console.log($(this).attr('id'));
            $('.div-permissions-Admin').hide();
            $('#Admin').removeClass('active');
            $('.div-permissions-Vehicles').hide();
            $('#Vehicles').removeClass('active');
            $('.div-permissions-Landmarks').hide();
            $('#Landmarks').removeClass('active');
            $('.div-permissions-Alerts').hide();
            $('#Alerts').removeClass('active');
            $('.div-permissions-Reports').hide();
            $('#Reports').removeClass('active');
            $(this).addClass('active');
            $('.div-permissions-'+$(this).attr('id')).show();
        });

        $(document).on('click', function() {
            if((lastDropdown)&&(!($(this).hasClass('.wizard-select')))){
                dontDeSelect=lastDropdown;
                Core.Wizard.DeSelect('ul-'+lastDropdown);
            }
        });

        $(document).on('click', '.got-it' , function() {
            Core.Ajax('got-it',$(this).attr('id'),$(this).attr('id'),'got-it');
        });

        $(document).on('click', '#modal-transfer-authorize-release-confirm' , function() {
            Core.Ajax('transfer-authorized',$('#transferee-routing-number').val(),transferManifest,'transfer-authorized');
        });

        $(document).on('click', '#modal-transfer-accept-confirm' , function() {
            Core.Ajax('transfer-accept',transferManifest,transferManifest,'transfer-accept');
            $('#modal-transfer-accept-close').trigger('click');
        });

        $(document).on('click', '#modal-transfer-reject-confirm' , function() {
            Core.Ajax('transfer-reject',transferManifest,transferManifest,'transfer-reject');
        });

        $(document).on('click', '.export-select-all' , function() {
            if($(this).is(':checked')){
                $('#devices-exporting').find('.device-for-export').each(function(){
                    $(this).prop('checked',true);
                });
            } else {
                $('#devices-exporting').find('.device-for-export').each(function(){
                    $(this).prop('checked',false);
                });
            }
        });

        $(document).on('click', '.modal-repo-create-link' , function() {
            Core.Dialog.launch('#modal-repo-create','Create Repossession Link');
            Core.FixModal.FixModal('modal-repo-create');
            Core.fixFooter('modal-repo-create');
        });

        $(document).on('click', '.modal-repo-edit-link' , function() {
            Core.Dialog.launch('#modal-repo-edit','Edit Repossession Link');
            currentRepoUrl = $(this).closest('tr').find('.repo-url').text();
            $('#repo-edit-url').val($(this).closest('tr').find('.repo-url').text());
            $('#repo-edit-email').val($(this).closest('tr').find('.repo-email').text());
            $('#repo-edit-phone').val($(this).closest('tr').find('.repo-phone').text());
            $('#repo-edit-name').val($(this).closest('tr').find('.repo-name').text());
            Core.FixModal.FixModal('modal-repo-edit');
            Core.fixFooter('modal-repo-edit');
        });

        $(document).on('click', '.map-addresses-all' , function() {

            var $link = $(this);
            var $modal = $('#modal-map-container');

            // var newheight=$modal.find('div.modal-content').height()-20;
            var newheight= Math.round($(window).height()*90/100);
            var newwidth=$(window).width()-150;
            $modal.find('div.modal-dialog').css({
                width: newwidth + 'px'
            });
            $modal.find('div.modal-content').css({
                height: newheight + 'px',
            });
            newheight=newheight-160;
            $('#modal-map-hook').css({
                height: newheight + 'px',
            });

            Core.Dialog.launch('#'+$modal.prop('id'), 'Map', {width: '800px'}, {
                hide: function() {
                   
                },
                hidden: function() {
                    
                },
                show: function() {

                },
                shown: function() {
                    Map.clearMarkers(Core.Map.mapAddress);

                    $link.closest('.report-master').find('.address_map_link').each(function(){
                        if(($(this).attr('data-id'))&&($(this).attr('data-name'))&&($(this).attr('data-latitude'))&&($(this).attr('data-longitude'))){
                            Map.addMarker(
                                Core.Map.mapAddress,
                                {
                                    id: $(this).attr('data-id'),
                                    eventname: $(this).attr('data-eventname'),
                                    name: $(this).attr('data-name').replace("'","\'"), // + '(' + $(this).attr('data-latitude') + ' / ' + $(this).attr('data-longitude') + ')',
                                    latitude: $(this).attr('data-latitude'),
                                    longitude: $(this).attr('data-longitude')
                                },
                                true
                            );
                            Map.showHideLabel(Core.Map.mapAddress, $(this).attr('data-id'), true);
                        }
                    });

                    Map.resetMap(Core.Map.mapAddress);
                    Map.resize(Core.Map.mapAddress);
                    Map.updateMarkerBound(Core.Map.mapAddress);
                    Map.updateMapBound(Core.Map.mapAddress);
                    // Map.updateMapBoundZoom(Core.Map.mapAddress);

                }
            });
            
            $('#modal-map-title').text('Map: All Addresses');

        }); 

        $(document).on('click', '#repoKey-refresh' , function() {
            $('#last-known-location').hide();
            $('#repolink-map').trigger('click');
            Core.Ajax('repoKey','repoKey',context[1],'repoKey');
        }); 

        $(document).on('click', '.import-select-all' , function() {
            if($(this).is(':checked')){
                $('#devices-importing').find('.device-for-import').each(function(){
                    $(this).prop('checked',true);
                });
            } else {
                $('#devices-importing').find('.device-for-import').each(function(){
                    $(this).prop('checked',false);
                });
            }
        });

        $(document).on('change', '.repo-edit-input' , function() {
            Core.Ajax($(this).attr('id'),$(this).val(),currentRepoUrl,'update');
        });

        $(document).on('change', '.scheduled-report-edit-save' , function() {
            if(!(formFillBool)){
                Core.ScheduleReportEdit(1);
            }
        });

        $('div.sidebar-div .dropdown-toggle').click(function() {
            var $self = $(this);
            var group = $self.closest('.form-group').attr('id');
            setTimeout("Core.fixDropdownScroll('"+group+"')",500);
        });

        $(document).on('change', '#landmark-add-shape', function() {
            Landmark.AddMap.init();
        });

        $(document).ready(function(){
            $('#map-div').bind('mousewheel', function(e){
                if(allNone){
                    if(e.originalEvent.wheelDelta > 0) {
                        $('#drill-in').trigger('click');
                    } else {
                        $('#drill-out').trigger('click');
                    }
                }
            });
        });

        Core.log('Core JS Loaded');

    },

    fixDropdownScroll: function(eid) {
        var $dropdown = '';
        var screenHeight = 0;
        var dropdownHeight = 0;
        var dropdownTop = 0;
        var dropdownOver = 0;
        $.each($('#'+eid).find('ul.dropdown-menu'), function() {
            $dropdown = $(this);
            screenHeight = $(window).height();
            dropdownTop = $dropdown.offset().top;
            dropdownHeight = screenHeight - dropdownTop - 2;
            if(dropdownHeight < $dropdown.height()){
                $dropdown.css({ height: dropdownHeight+'px' , 'overflow-y': 'scroll' });
            }
        });
    },

    reportScroll: function() {

        $('.panel-report-scroll').each(function(){

            switch (Core.Environment.context()) {

                case              'report/list' :   if($('.panel-report-scroll').is(':visible')){
                                                        var offset = $('.panel-report-scroll').offset().top+54;
                                                        var newheight=$(window).height()-offset;

                                                        if(newheight<30){
                                                            newheight=30;
                                                        }

                                                        if ($('.panel-report-scroll').height()!=newheight) {
                                                            $('.panel-report-scroll').height(newheight+'px');
console.log('Core:reportScroll:.panel-report-scroll: Resized ('+$('.panel-report-scroll').height()+'='+$(window).height()+'-'+offset+'), context: '+Core.Environment.context());
                                                        }
                                                    } else {
                                                        $('.panel-report-scroll').height('100px');
                                                    }
                                                    break;

                                        default :   if($(this).is(':visible')){
                                                        var offset = $(this).offset().top+14;
                                                        var newheight=$(window).height()-offset;

                                                        if(newheight<30){
                                                            newheight=30;
                                                        }

                                                        if ($(this).height()!=newheight) {
                                                            $(this).height(newheight+'px');
console.log('Core:reportScroll:.panel-report-scroll: Resized ('+$(this).height()+'='+$(window).height()+'-'+offset+'), context: '+Core.Environment.context());
                                                        }
                                                    } else {
                                                        $(this).height('100px');
                                                    }

            }

        });

    },

    log: function(input, type) {
        type = type || 'log';

        var isIE = navigator.userAgent.toUpperCase().indexOf('MSIE') >=0 ? 'click' : 'change' ;

        //if (jQuery.browser.msie) {
        if (isIE) {
console.log(input);
        }  else if (Core.Environment.current() == Core.Environment.development) {

            switch (type) {
                case 'info':
                    console.info(input);
                    break;
                case 'warn':
                    console.warn(input);
                    break;
                case 'error':
                    console.error(input);
                    break;
                case 'debug':
                    console.debug(input);
                    break;
                case 'trace':
                    console.trace();
                    break;
                case 'dir':
                    console.dir(input);
                    break;
                case 'dirxml':
                    console.dirxml(input);
                    break;
                case 'group':
                    console.group(input);
                    break;
                case 'groupEnd':
                    console.groupEnd();
                    break;
                case 'time':
                    console.time(input);
                    break;
                case 'timeEnd':
                    console.timeEnd(input);
                    break;
                case 'profile':
                    console.profile();
                    break;
                case 'profileEnd':
                    console.profileEnd();
                    break;
                case 'log':
                default:
                    console.log(input);
                    break;



            }
        }
    },
    
    initKillFormEnterKey: function() {
        $('form :not(textarea)').killEnterKey();
    }

});

jQuery.ajaxSetup({
    timeout:  Core.Config.ajaxTimeout,
    type:     Core.Config.ajaxType,
    dataType: Core.Config.ajaxDataType
});

jQuery.ajaxPrefilter(function(options, originalOptions, jqHXR) {

console.log('jQuery.ajaxPrefilter');

    options.success = function(responseData) {

        //Core.log(responseData);

        if (responseData.code == 2) { // 2 == session timeout

            if (typeof responseData.data.last_route != 'undefined') {

                var lastRoute = Core.Session.getLastRoute(responseData.data.last_route);

                Core.Cookie.set('last-route', lastRoute);
            }

            if(!(repoKey)){
                window.location = '/logout';
            }

        } else if ($.isFunction(originalOptions.success)) {
            originalOptions.success(responseData);
        }
    };

    options.beforeSend = function() {
console.log('jQuery.ajaxPrefilter:options.beforeSend');
        if ($.isFunction(originalOptions.beforeSend)) {
            originalOptions.beforeSend();
        }
        Core.SystemIndicator.busy();
    };

    options.complete = function() {
console.log('jQuery.ajaxPrefilter:options.complete');
        if ($.isFunction(originalOptions.complete)) {
            originalOptions.complete();
        }

        if (originalOptions.modalTrigger) {
            $('body').trigger(originalOptions.modalTrigger);
        }
        Core.Session.updateLastRequest();
        Core.SystemIndicator.ready();
    };

    options.error = function() { // handles: error, timeout, abort, parsererror
console.log('jQuery.ajaxPrefilter:options.error');
        if ($.isFunction(originalOptions.error)) {
            originalOptions.error();
        } else {
            //window.location = '/error/internalservererror';
        }
        Core.SystemIndicator.error();
    };

});

$.fn.clearForm = function() {
console.log('$.fn.clearForm');

    var $self = $(this);

    if ($self.is('form')) {

        $self.find('input, select, textarea').not('hasDatePicker').each(function() {

            var elem = this,
                tag  = elem.nodeName.toLowerCase()
            ;

            if (tag == 'input') {

                switch (elem.getAttribute('type').toLowerCase()) {
                    case 'password':
                    case 'text':
                        elem.value = '';
                        break;
                    case 'radio':
                        $(elem).prop('selected', false);
                        break;
                    case 'checkbox':
                        $(elem).prop('checked', false);
                        break;
                }

            } else if (tag == 'select') {

                $(elem).find('option').filter(':selected').prop('selected', false);

            } else if (tag == 'textarea') {

                elem.value = '';

            }
        });
    }
};

if ($.fn.dataTable) {

    $.fn.dataTableExt.oApi.fnStandingRedraw = function(oSettings) {
console.log('$.fn.dataTableExt.oApi.fnStandingRedraw');

        var $table = $(this);

        if(oSettings.oFeatures.bServerSide === false){
            var before = oSettings._iDisplayStart;

            oSettings.oApi._fnReDraw(oSettings);

            // iDisplayStart has been reset to zero - so lets change it back
            oSettings._iDisplayStart = before;
            oSettings.oApi._fnCalculateEnd(oSettings);
        }

        // draw the 'current' page
        oSettings.oApi._fnDraw(oSettings);

        // give the table time to redraw before triggering custom event
        setTimeout(function() {
            $table.trigger('Core.Datatable.standingRedraw');
        }, 500);
    };
}

// crossbrowser ES5-like keys method
if ( ! Object.keys) {
    Object.keys = function(obj) {
        var keys = [], k;
        for(k in obj) {
            if (Object.prototype.hasOwnProperty.call(obj, k)) {
                keys.push(k);
            }
        }
    return keys;
    };
}

/*Object.prototype.removeItemAtIndex = function (key) {
   if (!this.hasOwnProperty(key))
      return
   if (isNaN(parseInt(key)) || !(this instanceof Array))
      delete this[key]
   else
      this.splice(key, 1)
};*/

String.prototype.replaceAll = function(findString, replaceString, ignore) {
console.log('String.prototype.replaceAll');
    findString    = findString ||'';
    replaceString = replaceString ||'';
    ignore        = ignore ||'';
    return this.replace(new RegExp(findString.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g,"\\$&"),(ignore?"gi":"g")),(typeof(replaceString)=="string")?replaceString.replace(/\$/g,"$$$$"):replaceString);
};

String.prototype.toTitleCase = function() {
    return this.replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
};

(function ($) {

    if (jQuery.browser) return;

    jQuery.browser = {};
    jQuery.browser.mozilla = false;
    jQuery.browser.webkit = false;
    jQuery.browser.opera = false;
    jQuery.browser.msie = false;

    var nAgt = navigator.userAgent;
    jQuery.browser.ua = nAgt;

    jQuery.browser.name = navigator.appName;
    jQuery.browser.fullVersion = '' + parseFloat(navigator.appVersion);
    jQuery.browser.majorVersion = parseInt(navigator.appVersion, 10);
    var nameOffset, verOffset, ix;

    // In Opera, the true version is after "Opera" or after "Version"
    if ((verOffset = nAgt.indexOf("Opera")) != -1) {
        jQuery.browser.opera = true;
        jQuery.browser.name = "Opera";
        jQuery.browser.fullVersion = nAgt.substring(verOffset + 6);
        if ((verOffset = nAgt.indexOf("Version")) != -1) jQuery.browser.fullVersion = nAgt.substring(verOffset + 8);
    }

    // In MSIE < 11, the true version is after "MSIE" in userAgent
    else if ((verOffset = nAgt.indexOf("MSIE")) != -1) {
        jQuery.browser.msie = true;
        jQuery.browser.name = "Microsoft Internet Explorer";
        jQuery.browser.fullVersion = nAgt.substring(verOffset + 5);
    }

    // In TRIDENT (IE11) => 11, the true version is after "rv:" in userAgent
    else if (nAgt.indexOf("Trident") != -1) {
        jQuery.browser.msie = true;
        jQuery.browser.name = "Microsoft Internet Explorer";
        var start = nAgt.indexOf("rv:") + 3;
        var end = start + 4;
        jQuery.browser.fullVersion = nAgt.substring(start, end);
    }

    // In Chrome, the true version is after "Chrome"
    else if ((verOffset = nAgt.indexOf("Chrome")) != -1) {
        jQuery.browser.webkit = true;
        jQuery.browser.name = "Chrome";
        jQuery.browser.fullVersion = nAgt.substring(verOffset + 7);
    }
    // In Safari, the true version is after "Safari" or after "Version"
    else if ((verOffset = nAgt.indexOf("Safari")) != -1) {
        jQuery.browser.webkit = true;
        jQuery.browser.name = "Safari";
        jQuery.browser.fullVersion = nAgt.substring(verOffset + 7);
        if ((verOffset = nAgt.indexOf("Version")) != -1) jQuery.browser.fullVersion = nAgt.substring(verOffset + 8);
    }
    // In Safari, the true version is after "Safari" or after "Version"
    else if ((verOffset = nAgt.indexOf("AppleWebkit")) != -1) {
        jQuery.browser.webkit = true;
        jQuery.browser.name = "Safari";
        jQuery.browser.fullVersion = nAgt.substring(verOffset + 7);
        if ((verOffset = nAgt.indexOf("Version")) != -1) jQuery.browser.fullVersion = nAgt.substring(verOffset + 8);
    }
    // In Firefox, the true version is after "Firefox"
    else if ((verOffset = nAgt.indexOf("Firefox")) != -1) {
        jQuery.browser.mozilla = true;
        jQuery.browser.name = "Firefox";
        jQuery.browser.fullVersion = nAgt.substring(verOffset + 8);
    }
    // In most other browsers, "name/version" is at the end of userAgent
    else if ((nameOffset = nAgt.lastIndexOf(' ') + 1) < (verOffset = nAgt.lastIndexOf('/'))) {
        jQuery.browser.name = nAgt.substring(nameOffset, verOffset);
        jQuery.browser.fullVersion = nAgt.substring(verOffset + 1);
        if (jQuery.browser.name.toLowerCase() == jQuery.browser.name.toUpperCase()) {
            jQuery.browser.name = navigator.appName;
        }
    }
    // trim the fullVersion string at semicolon/space if present
    if ((ix = jQuery.browser.fullVersion.indexOf(";")) != -1) jQuery.browser.fullVersion = jQuery.browser.fullVersion.substring(0, ix);
    if ((ix = jQuery.browser.fullVersion.indexOf(" ")) != -1) jQuery.browser.fullVersion = jQuery.browser.fullVersion.substring(0, ix);

    jQuery.browser.majorVersion = parseInt('' + jQuery.browser.fullVersion, 10);
    if (isNaN(jQuery.browser.majorVersion)) {
        jQuery.browser.fullVersion = '' + parseFloat(navigator.appVersion);
        jQuery.browser.majorVersion = parseInt(navigator.appVersion, 10);
    }
    jQuery.browser.version = jQuery.browser.majorVersion;
})(jQuery);

jQuery.fn.killEnterKey = function(){
   this.bind("keypress", function(e) {
       if (e.keyCode == 13) {
           return false;
       }
   })
};
