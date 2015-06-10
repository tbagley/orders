/*

    Map JS

    File:       /assets/js/map.js
    Author:     Yang Vang
*/

$(document).ready(function() {

});

var Map = {};

jQuery.extend(Map, {
	
	api: function() {
	   return $('body').data('mapApi');
    },

	decarta_api_key: function() {
	   return $('body').data('decartaApiKey');
    },

	markerColorArray: {
	    'unit': 'unit',
	    'cluster': 'cluster',
	    'Inventory': 'grey',
	    'Ignition On': 'green',
	    'Ignition Off': 'red',
	    'Virtual Ignition On': 'green',
	    'Virtual Ignition Off': 'red',
	    'Stop': 'red',
	    'Stopped': 'red',
	    'Starter Disable': 'black',
	    'Update': 'red',
	    'Reminder On': 'purple',
	    'Power On': 'blue',
	    'Drive': 'green',
	    'Travel Start': 'green',
	    'Low Battery': 'orange',
	    'Low Internal Battery': 'orange',
	    'Battery Disconnect': 'orange'
	},
	//
	// *** COLOR OPTIONS ***
	//
	// .mapbox_marker_icon_blue {
	// .mapbox_marker_icon_red {
	// .mapbox_marker_icon_green {
	// .mapbox_marker_icon_black {
	// .mapbox_marker_icon_grey {
	// .mapbox_marker_icon_orange {
	// .mapbox_marker_icon_purple {
	// .mapbox_marker_icon_teal {
	// .mapbox_marker_icon_yellow {
	
	initMap: function(divId, opts) {
        var opts = opts || undefined,
            map = {}        
        ;

		if (divId !== undefined && divId !== '') {
console.log('Map.api():'+Map.api());
	    	switch (Map.api()) {
                case 'google':
                    map = new GMap(divId, opts);
                    break;
                case 'mapbox':
                    map = new MbMap(divId, opts);
                    break;
                default:
                    break;
	    	}

	    	return map;
	    }		
	},
	
	getAllVehicles: function(gmap) {
		$.ajax({
			url: '/ajax/vehicle/getAllVehicles',
			type: 'GET',
			dataType: 'json',
			success: function(responseData) {
				if (responseData.code === 0) {	//	0 means SUCCESS, > 0 means FAIL
					var vehicles = responseData.data.vehicles;
					if (typeof(vehicles) !== 'undefined' && vehicles.length > 0) {
						var length = vehicles.length;
						$.each(vehicles, function(key, value) {
							var unit = this,
								markerOptions = {
									id: unit.unit_id,
									name: unit.unitname,
									latitude: unit.latitude,
									longitude: unit.longitude,
									click: function() {
										Map.getVehicleEvent(gmap, unit.unit_id, unit.id);
									}	
								}
							;							
							gmap.addMarker(markerOptions);
							if (key == (length - 1)) {
								gmap.updateMapBound();
							}
						});
					}
				} else {
					if ($.isEmptyObject(responseData.message) === false) {
						//	display messages
					}
					
					if ($.isEmptyObject(responseData.validaton_errors) === false) {
						//	display validation errors
					}
				}	
			}					
		});
	},
	
	getVehicleEvent: function(gmap, unitId, eventId) {		
		$.ajax({
			url: '/ajax/vehicle/getEventById',
			type: 'POST',
			dataType: 'json',
			data: {
				unit_id: unitId,
				event_id: eventId
			},
			success: function(responseData) {
				if (responseData.code === 0) {
					var event = responseData.data.eventdata;
					Map.openInfoWindow(gmap, 'unit', event.latitude, event.longitude, event);					
				} else {
					if ($.isEmptyObject(responseData.message) === false) {
						//	display messages
					}
					
					if ($.isEmptyObject(responseData.validation_errors) === false) {
						//	display validation errors
					}
				}
			}
		});
	},
	
	getLandmarkInfo: function(gmap, landmarkId, isIncomplete) {
    	isIncomplete = isIncomplete || false;
    	var method = isIncomplete ? 'getIncompleteLandmarkByIds' : 'getLandmarkByIds',
    	    data = {}
        ;
    	
    	if (isIncomplete) {
        	data.territoryupload_id = landmarkId;
    	} else {
        	data.territory_id = landmarkId;
    	}
    	
		$.ajax({
			url: '/ajax/landmark/' + method + '',
			type: 'POST',
			dataType: 'json',
			data: data,
			success: function(responseData) {
				if (responseData.code === 0) {
					var landmarkData = responseData.data;					
					Map.openInfoWindow(gmap, 'landmark', landmarkData.latitude, landmarkData.longitude, landmarkData);					
				} else {
					if ($.isEmptyObject(responseData.message) === false) {
						//	display messages
					}
					
					if ($.isEmptyObject(responseData.validation_errors) === false) {
						//	display validation errors
					}
				}
			}
		});
	},

	getIncompleteLandmarkInfo: function(gmap, landmarkId) {
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
					Map.openInfoWindow(gmap, 'landmark', landmarkData.latitude, landmarkData.longitude, landmarkData);					
				} else {
					if ($.isEmptyObject(responseData.message) === false) {
						//	display messages
					}
					
					if ($.isEmptyObject(responseData.validation_errors) === false) {
						//	display validation errors
					}
				}
			}
		});
	},

	getVerificationLandmarkInfo: function(gmap, landmarkId) {
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
					Map.openInfoWindow(gmap, 'landmark', landmarkData.latitude, landmarkData.longitude, landmarkData);					
				} else {
					if ($.isEmptyObject(responseData.message) === false) {
						//	display messages
					}
					
					if ($.isEmptyObject(responseData.validation_errors) === false) {
						//	display validation errors
					}
				}
			}
		});
	},
	
	showStreetView: function(divId, latitude, longitude) {
		Map._current_map.showStreetView(divId, latitude, longitude, function() {
			$('#info_window_div').hide().parent('div').css('overflow', 'hidden');
			$('#info_window_street_view_div').show();
		}, function() {
			$('#info_window_div').show().parent('div').css('overflow', 'auto');
			$('#info_window_street_view_div').hide();			
		});
	},
	
	addMarker: function(gmap, markerOptions, hideLabel) {
    	// get markerColor base on markerOptions.eventname from markerColorArray
    	if (markerOptions.eventname != undefined) {
    	    markerOptions.markerColor =  Map.markerColorArray[markerOptions.eventname];
    	}
		gmap.addMarker(markerOptions, hideLabel);	
	},
	
	updateMapBound: function(gmap, useTempBound) {
console.log('updateMapBound:'+gmap+':'+useTempBound);
console.log(gmap);
        useTempBound = useTempBound || false;
		gmap.updateMapBound(useTempBound);
	},
	
	updateMapBoundZoom: function(gmap, useTempBound, mapbubble) {
console.log('updateMapBoundZoom:'+gmap+':'+useTempBound);
console.log(gmap);
        useTempBound = useTempBound || false;
		gmap.updateMapBoundZoom(useTempBound, mapbubble);
	},

	mapZoomGet: function(gmap) {
        return gmap.mapZoomGet();
	},

	mapZoom: function(gmap, zoom) {
        if(zoom){
        	gmap.mapZoom(zoom);
        }
	},
	
	centerMap: function(gmap, latitude, longitude, zoomlevel) {
console.log('centerMap:'+gmap+':'+latitude+':'+longitude+':'+zoomlevel);
// console.log(gmap);
		gmap.centerMap(latitude, longitude, zoomlevel);
	},
	
	zoomMap: function(gmap, zoomlevel) {
		gmap.zoomMap(zoomlevel);
	},
	
	closeInfoWindow: function(gmap, callback) {
		gmap.closeInfoWindow(callback);	
	},
	
	removeMarker: function(gmap, unit_id, callback) {
		gmap.removeMarker(unit_id, callback);
	},
	
	resetMap: function(gmap) {
		gmap.resetMap();
	},
	
	clickMarker: function(gmap, unitId, isTempMarker) {
    	isTempMarker = isTempMarker || false;
		gmap.clickMarker(unitId, isTempMarker);
	},
	
	showHideLabel: function(gmap, unitId, showLabel) {
		gmap.showHideLabel(unitId, showLabel);
	},
	
	clearMarkers: function(gmap, callBack, onlyClearTempMarkers) {
    	onlyClearTempMarkers = onlyClearTempMarkers || false;
		gmap.clearMarkers(callBack, onlyClearTempMarkers);
	},
	
	openInfoWindow: function(gmap, type, latitude, longitude, event, moving, battery, signal, satellites, territoryname, $mobile, unitstatus, aveduration, dow, tod, color, license, make, model, vin, year) {
    	var html = '';
    	
    	if (type == 'unit') {
        	id = event.unit_id;
    		html += '<div class="info_window" id="info_window_div"><table class="map-bubble"><tbody>';
    		html += '<tr><th colspan="3" id="info_window_unit_name" class="map-bubble-title"><i class="gi gi-car"></i>&nbsp;&nbsp;<b>' + event.unitname + '</b></th></tr>';
    		if(territoryname){
    			html += '<tr><th class="pull-right" style="margin-top:4px;">Address:&nbsp;&nbsp;</th><td colspan="2"><b>' + territoryname + '</b><br>' + event.infomarker_address + '</td></tr>';
    		} else {
	    		html += '<tr><th class="pull-right" style="margin-top:4px;">Address:&nbsp;&nbsp;</th><td colspan="2">' + event.infomarker_address + '</td></tr>';
    		}
    		// html += '<tr><th class="pull-right" style="margin-top:4px;">Moving:&nbsp;&nbsp;</th><td colspan="2">' + moving.state + '</td></tr>';
// console.log(moving.sql1);
// console.log(moving.res1);
// console.log(moving.sql2);
// console.log(moving.res2);
    		switch (moving.state){
    			case         'I' : html += '<tr><th class="pull-right" style="margin-top:4px;">Status:&nbsp;&nbsp;</th><td colspan="2"><span class="text-grey">Inventory</span></td></tr>';
    						       break;
    			case         'N' : html += '<tr><th class="pull-right" style="margin-top:4px;">Status:&nbsp;&nbsp;</th><td colspan="2"><span class="text-grey">Installed</span></td></tr>';
    						       break;
    			case          1  :
    			case         '1' :
    			case          3  :
    			case         '3' :
    			case          4  :
    			case         '4' : html += '<tr><th class="pull-right" style="margin-top:4px;">Status:&nbsp;&nbsp;</th><td colspan="2"><span style="color:#5f8f2f;">Moving</span>' + ' <span>( '+moving.duration+' )</span></td></tr>';
    						       break;
    			         default : html += '<tr><th class="pull-right" style="margin-top:4px;">Status:&nbsp;&nbsp;</th><td colspan="2"><span style="color:#ff0000;">Stopped</span>' + ' <span>( '+moving.duration+' )</span></td></tr>';
    		}
    		html += '<tr><th class="pull-right" style="margin-top:4px;">Last Event:&nbsp;&nbsp;</th><td colspan="2"><span>' + event.eventname + '</font></td></tr>';
    		var stale='none';
    		if(moving.stale){
    			stale = 'show';
    		}
    		html += '<tr><th class="pull-right" style="margin-top:4px;">Unit Time:&nbsp;&nbsp;</th><td><span>' + event.display_unittime + '</span></td><td rowspan="2"><div id="map-bubble-stale" style="color:#ff0000;display:'+stale+';font-size:18px;">&nbsp;<i class="gi gi-warning_sign" title="'+moving.stale+'"></i></div></td></tr>';
    		html += '<tr><th class="pull-right" style="margin-top:4px;">Speed:&nbsp;&nbsp;</th><td><span>' + event.speed + ' MPH</span></td></tr>';				
    		html += '<tr><th class="pull-right" style="margin-top:4px;">Lat/Long:&nbsp;&nbsp;</th><td colspan="2">' + event.latitude + ' ' + event.longitude + '</td></tr>';	
    		// html += '<tr><th></th><td colspan="2" class="pull-left"><a href="http://www.google.com/maps/place/'+event.formatted_address.replace(' ','+')+'/@' + event.latitude + ',' + event.longitude + ',1000m/data=!3m1!1e3" target="_googlemap">Google Earth</a></td></tr>';	
			html += '<tr><th></th><td colspan="2" class="pull-left"><a href="http://www.google.com/maps/place//@' + event.latitude + ',' + event.longitude + ',1000m/data=!3m1!1e3" target="_googlemap">Google Maps</a></td></tr>';	
    		html += '<tr><th colspan="3"><table style="width:100%;"><tr>';
    		if((battery.level!=0)&&(battery.level!=null)&&(battery.level!='undefined')){
	    		html += '<th class="pull-right" title="Vehicle Battery"><table><tr><th>&nbsp;</th><th><i class="gi gi-server_plus"></i></th><th>'+battery.level+'&nbsp;Volts</th></tr></table></th>';
    		}
    		if((signal.level!=0)&&(signal.level!=null)&&(signal.level!='undefined')){
	    		html += '<th class="pull-right" title="Cellular Signal"><table><tr><th>&nbsp;</th><th><i class="gi gi-wifi"></i></th><th>'+signal.level+'&nbsp;RSSI</th></tr></table></th>';
    		}
    		if((satellites.level!=0)&&(satellites.level!=null)&&(satellites.level!='undefined')){
	    		html += '<th class="pull-right" title="Satellites"><table><tr><th>&nbsp;</th><th><i class="gi gi-globe"></i></th><th>'+satellites.level+'&nbsp;Satellites&nbsp;</th></tr></table></th>';
    		}
    		if(unitstatus!=2){
				html += '<th class="pull-left" title="Create Repossesion Link"><a href="javascript:void(0);" class="modal-repo-create-link pull-left"><table><tr><th><i class="gi gi-user pull-right text-red"></i></th><th><span class="pull-left text-red">Repo</th></tr></table></a></th>';	
		    	// html += '<tr><th class="pull-right" style="margin-top:4px;">Repo:&nbsp;&nbsp;</th><td colspan="3"><input class="form-control" id="repo" placeholder=" Email Address"></td><td><button class="btn pull-left" id="btn-repo">OK</button></td></tr>';	
    		}
    		html += '</tr></table></th></tr>';
    		if($mobile){
	    		html += '<tr><th colspan="3"><button class="btn btn-default btn-tiny navigation" id="commands">COMMANDS</button></th></tr>';
    		}	
    		html += '</tbody></table></div>';
		} else if (type == 'repo') {
        	html += '<div class="info_window" id="info_window_div"><table class="map-bubble"><tbody>';
    		html += '<tr><th colspan="3" id="info_window_unit_name" class="map-bubble-title"><i class="gi gi-car"></i>&nbsp;&nbsp;<b>' + event.unitname + '</b></th></tr>';
    		if(territoryname){
    			html += '<tr><th class="pull-right" style="margin-top:4px;">Address:&nbsp;&nbsp;</th><td colspan="2"><b>' + territoryname + '</b><br>' + event.infomarker_address + '</td></tr>';
    		} else {
	    		html += '<tr><th class="pull-right" style="margin-top:4px;">Address:&nbsp;&nbsp;</th><td colspan="2">' + event.infomarker_address + '</td></tr>';
    		}
    		switch (event.status){
    			case          1  :
    			case         '1' :
    			case          3  :
    			case         '3' :
    			case          4  :
    			case         '4' : html += '<tr><th class="pull-right" style="margin-top:4px;">Status:&nbsp;&nbsp;</th><td colspan="2"><span style="color:#5f8f2f;">Moving</span>' + ' <span>( '+event.duration+' )</span></td></tr>';
    						       break;
    			         default : html += '<tr><th class="pull-right" style="margin-top:4px;">Status:&nbsp;&nbsp;</th><td colspan="2"><span style="color:#ff0000;">Stopped</span>' + ' <span>( '+event.duration+' )</span></td></tr>';
    		}
    		html += '<tr><th class="pull-right" style="margin-top:4px;">Last Event:&nbsp;&nbsp;</th><td colspan="2"><span>' + event.eventname + '</font></td></tr>';
    		html += '<tr><th class="pull-right" style="margin-top:4px;">Make/Model:&nbsp;&nbsp;</th><td colspan="2"><span>' + make + ' / ' + model + '</font></td></tr>';
    		html += '<tr><th class="pull-right" style="margin-top:4px;">Color/Year:&nbsp;&nbsp;</th><td colspan="2"><span>' + color + ' / ' + year + '</font></td></tr>';
    		html += '<tr><th class="pull-right" style="margin-top:4px;">Lic.Plate:&nbsp;&nbsp;</th><td colspan="2"><span>' + license + '</font></td></tr>';
    		html += '<tr><th class="pull-right" style="margin-top:4px;">Vin:&nbsp;&nbsp;</th><td colspan="2"><span>' + vin + '</font></td></tr>';
    		if(aveduration){
	    		html += '<tr><th class="pull-right" style="margin-top:4px;">Ave Duration:&nbsp;&nbsp;</th><td colspan="2"><span>' + aveduration + '</font></td></tr>';
    		}
    		if(dow){
				html += '<tr><th class="pull-right" style="margin-top:4px;">Day of Week:&nbsp;&nbsp;</th><td colspan="2"><span>' + dow + '</font></td></tr>';
    		}
    		if(tod){
				html += '<tr><th class="pull-right" style="margin-top:4px;">Time of Day:&nbsp;&nbsp;</th><td colspan="2"><span>' + tod + '</font></td></tr>';
    		}
			html += '<tr><th></th><td colspan="2" class="pull-left"><a href="http://www.google.com/maps/place//@' + event.latitude + ',' + event.longitude + ',1000m/data=!3m1!1e3" target="_googlemap">Google Maps</a></td></tr>';	
    		html += '</tbody></table></div>';
		} else if (type == 'locate') {
        	id = event.unit_id;
    		html += '<div class="info_window" id="info_window_div"><table class="map-bubble"><tbody>';
    		html += '<tr><th colspan="2" id="info_window_unit_name" class="map-bubble-title"><i class="gi gi-eye_open"></i>&nbsp;<b>' + event.unitname + '</b></td></tr>';
    		html += '<tr><th colspan="2">&nbsp;</td></tr>';
    		html += '<tr><th class="pull-right" style="margin-top:4px;">Address:&nbsp;&nbsp;</th><td>' + ((event.territoryname != undefined) ? (event.territoryname + '<br>') : '') + event.infomarker_address + '</td></tr>';
    		html += '<tr><th colspan="2">&nbsp;</td></tr>';
    		html += '<tr><th class="pull-right" style="margin-top:4px;">Lat/Long:&nbsp;&nbsp;</th><td>' + event.latitude + ' ' + event.longitude + '</td></tr>';	
    		html += '<tr><th colspan="2">&nbsp;</td></tr>';
    		html += '<tr><th>&nbsp;</th><td><a href="http://www.google.com/maps/place/'+event.formatted_address.replace(' ','+')+'/@' + event.latitude + ',' + event.longitude + ',1000m/data=!3m1!1e3" target="_googlemap">Google Maps</a></td></tr>';	
    		// html += '<tr><td colspan="2"><font color="#c3c2c0">** - Speed may not be accurate due to weak signal.</font></td></tr>';
    		html += '<tr><th colspan="2">&nbsp;</td></tr>';
    		html += '</tbody></table></div>';
		} else if (type == 'landmark') {
    		var measurementUnit = ''; //(parseFloat(event.radius_in_miles) > 1) ? ' Miles' : ' Mile';
    		html += '<div class="info_window" id="info_window_div"><table class="map-bubble"><tbody>';
    		if (event.territorycategory_id==0) {
		    	html += '<tr><th colspan="3" id="info_window_unit_name" class="map-bubble-title"><i class="gi gi-flag"></i>&nbsp;&nbsp;<b>' + event.territoryname + '</b></th></tr>';
    		} else {				
    			html += '<tr><th colspan="3" id="info_window_unit_name" class="map-bubble-title"><span style="color:#ff0000;"><i class="gi gi-lock"></i>&nbsp;&nbsp;<b>' + event.territoryname + '</b></span></th></tr>';
    		}				
    		if (event.formatted_address != '') {
	    		html += '<tr><th class="pull-right" style="margin-top:4px;">Address:&nbsp;&nbsp;</th><td colspan="2">' + event.formatted_address + '</td></tr>';
    		}				
    		html += '<tr><th class="pull-right" style="margin-top:4px;">Lat/Long:&nbsp;&nbsp;</th><td colspan="2">' + event.latitude.substring(0,7) + ' / ' + event.longitude.substring(0,8) + '</td></tr>';	
    		if (event.shape == 'circle' || event.shape == 'square') { 
	    		html += '<tr><th class="pull-right" style="margin-top:4px;">Radius:&nbsp;&nbsp;</th><td colspan="2">' + event.radius_in_miles + measurementUnit + '</td></tr>';
    		}
    		if ((event.territorytype != 'reference') && (event.territorygroup_id != undefined) && (event.territorygroup_id != '') && (parseInt(event.territorygroup_id, 10) > 0) && event.territorygroupname != '') {
    			html += '<tr><th class="pull-right" style="margin-top:4px;">Group:&nbsp;&nbsp;</th><td colspan="2"><span>' + event.territorygroupname + '</td></tr>';
    		}
    		html += '</tbody></table></div>';
		} else if (type == 'quick_history') {
            switch (event.event_type) {
                case 'all':
                case 'recent':
		    		html += '<div class="info_window" id="info_window_div"><table class="map-bubble"><tbody>';
                    html += '<tr><td id="info_window_unit_name" colspan="2"><b>' + event.unitname + '</b></td></tr>';
                    html += '<tr><th>Event:&nbsp;</th><td>' + event.eventname + '</td></tr>';
                    html += '<tr><th>Location:&nbsp;</th><td>' + ((event.territoryname != undefined) ? (event.territoryname + '<br>') : '') + event.formatted_address + '</td></tr>';
                    html += '<tr><th>Date & Time:&nbsp;</th><td><font color="blue">' + event.unittime + '</font></td></tr>';
                    if (event.event_type == 'all') {
                        html += '<tr><th>Speed:&nbsp;</th><td>' + ((event.speed != undefined) ? (event.speed) : '') + '</td></tr>';
                    }
                    if(event.duration){
	                    html += '<tr><th>Duration:&nbsp;</th><td>' + event.duration + '</td></tr>';
                    }
                    html += '</tbody></table></div>';
                    
                    if (parseInt(event.max, 10) > 1) {
                        var prev = (parseInt(event.mappoint, 10) > 1) ? '<span class="info_window_link" onClick="Map.clickMarker(Vehicle.Map.map, ' + (parseInt(event.mappoint, 10) - 1) + ', true);">prev</span>&nbsp;&nbsp;' : '',
                            next = (parseInt(event.mappoint, 10) < event.max) ? '<span class="info_window_link" onClick="Map.clickMarker(Vehicle.Map.map, ' + (parseInt(event.mappoint, 10) + 1) +', true);">next</span>' : ''
                        ;  
                        
                        html += '<div id="quick-history-map-navigation">(' + event.mappoint + ' of ' + event.max + ')&nbsp;&nbsp;&nbsp;' + prev + next + '</div>';
                    }

                    break;
                case 'frequent':
                    html += '<div class="info_window" id="info_window_div"><table class="map-bubble"><tbody>';
                    html += '<tr><td id="info_window_unit_name" colspan="2"><b>' + event.unitname + '</b></td></tr>';
                    // html += '<tr><th># Stops:&nbsp;</th><td>' + event.stops + '</td></tr>';
                    html += '<tr><th>Location:&nbsp;</th><td>' + ((event.territoryname != undefined) ? (event.territoryname + '<br>') : '') + event.formatted_address + '</td></tr>';
                    // html += '<tr><th>Duration:</th><td>' + event.duration + '</td></tr>';
                    html += '</tbody></table></div>';

                    if (parseInt(event.max, 10) > 1) {
                        var prev = (parseInt(event.mappoint, 10) > 1) ? '<span class="info_window_link" onClick="Map.clickMarker(Vehicle.Map.map, ' + (parseInt(event.mappoint, 10) - 1) + ', true);">prev</span>&nbsp;&nbsp;' : '',
                            next = (parseInt(event.mappoint, 10) < event.max) ? '<span class="info_window_link" onClick="Map.clickMarker(Vehicle.Map.map, ' + (parseInt(event.mappoint, 10) + 1) +', true);">next</span>' : ''
                        ;  
                        
                        html += '<div id="quick-history-map-navigation">(' + event.mappoint + ' of ' + event.max + ')&nbsp;&nbsp;&nbsp;' + prev + next + '</div>';
                    }
                    
                    break;
            }    			
		} else if (type == 'rank') {
        	html += '<div class="info_window" id="info_window_div"><table class="map-bubble"><tbody>';
    		html += '<tr><td colspan="2" id="info_window_unit_name"><i class="gi gi-globe"></i>&nbsp;Map&nbsp;Point&nbsp;<b>' + event.rank + '</b></td></tr>';
    		html += '<tr><th class="pull-right">Address:&nbsp;</th><td><a href="http://www.google.com/maps/place/'+event.formatted_address.replace(' ','+')+'/@' + event.latitude + ',' + event.longitude + ',1000m/data=!3m1!1e3" target="_googlemap">' + ((event.territoryname != undefined) ? (event.territoryname + '<br>') : '') + event.formatted_address.replace(/,/g,'<br>') + '</a></td></tr>';
    		html += '<tr><th class="pull-right">Lat/Long:&nbsp;</th><td><a href="http://www.google.com/maps/place/'+event.formatted_address.replace(' ','+')+'/@' + event.latitude + ',' + event.longitude + ',1000m/data=!3m1!1e3" target="_googlemap">' + event.latitude + ' ' + event.longitude + '</a></td></tr>';	
    		html += '<tr><th></th><td><a href="http://www.google.com/maps/place/'+event.formatted_address.replace(' ','+')+'/@' + event.latitude + ',' + event.longitude + ',1000m/data=!3m1!1e3" target="_googlemap">Google Earth</a></td></tr>';	
    		html += '</tbody></table></div>';
		} else if (type == 'report') {
        	html += '<div class="info_window" id="info_window_div"><table class="map-bubble"><tbody>';
    		html += '<tr><td colspan="2" id="info_window_unit_name"><i class="gi gi-car"></i>&nbsp;<b>' + event.unitname + '</b></td></tr>';
    		html += '<tr><th class="pull-right">Address:&nbsp;</th><td><a href="http://www.google.com/maps/place/'+event.formatted_address.replace(' ','+')+'/@' + event.latitude + ',' + event.longitude + ',1000m/data=!3m1!1e3" target="_googlemap">' + ((event.territoryname != undefined) ? (event.territoryname + '<br>') : '') + event.formatted_address.replace(/,/g,'<br>') + '</a></td></tr>';
    		html += '<tr><th class="pull-right">Lat/Long:&nbsp;</th><td><a href="http://www.google.com/maps/place/'+event.formatted_address.replace(' ','+')+'/@' + event.latitude + ',' + event.longitude + ',1000m/data=!3m1!1e3" target="_googlemap">' + event.latitude + ' ' + event.longitude + '</a></td></tr>';	
    		html += '<tr><th></th><td><a href="http://www.google.com/maps/place/'+event.formatted_address.replace(' ','+')+'/@' + event.latitude + ',' + event.longitude + ',1000m/data=!3m1!1e3" target="_googlemap">Google Earth</a></td></tr>';	
    		html += '</tbody></table></div>';
		} else if (type == 'maphint') {
        	html += '<div class="info_window" id="info_window_div"><table class="map-bubble"><tbody>';
    		html += '<tr><td colspan="3" id="info_window_unit_name"><i class="gi gi-car"></i>&nbsp;<b>Vehicle&nbsp;Cluster</b></td></tr>';
    		html += '<tr><th class="pull-right vertical-align-top"><span class="text-grey text-10"><i>Click Cluster Icon again to repaint map with these vehicles</i></span></th><td>&nbsp;</td><td><div class="maphint vertical-align-top">'+event.unitname+'</div></td></tr>';
    		html += '</tbody></table></div>';
		}	
	/*
		html += '<div class="info_window" id="info_window_street_view_div"><span title="Enter full screen" class="right none icon arrow_out" id="info_window_streetview_fullscreen" onClick="Map.showStreetView(\'info_window_street_view_div\', ' + event.latitude + ', ' + event.longitude + ',' + true + ')"></span></div>';
		html += '<div id="street_view"><span class="info_window_link" id="maps_street_view" onClick="Map.showStreetView(\'info_window_street_view_div\', ' + event.latitude + ', ' + event.longitude + ')">Street View</span></div>';
	*/
		if (html != '') {
		  gmap.openInfoWindow(event.latitude, event.longitude, html);
		}
	},
	
	updateMarker: function(gmap, markerId, markerOptions) {
		gmap.updateMarker(markerId, markerOptions);
	},
	
	updateMarkerBound: function(gmap) {
		gmap.updateMarkerBound();
	},
	
	resize: function(gmap) {
    	gmap.resize();
	},
	
	showHideAllLabels: function(gmap, showLabels) {
    	gmap.showHideAllLabels(showLabels);
	},
	
	addMarkers: function(gmap, type, units) {
        if (units != undefined && units.length > 0) {
            // add each unit to the map
            var unitCount = units.length - 1;
            var that = this;
            
            $.each(units, function(key, value) {
                if (type == 'unit') {
                    var unitdata = this,
                        eventdata = unitdata.eventdata,
                        markerOptions = {}
                    ;
                    
                    if (! $.isEmptyObject(eventdata)) {
                    	// get markerColor base on markerOptions.eventname from markerColorArray
                    	if (eventdata.eventname != undefined) {
                    	    eventMarkerColor =  Map.markerColorArray[eventdata.eventname];
                    	}

                        markerOptions = {
                            id: unitdata.unit_id,
                            name: unitdata.unitname,
                            latitude: eventdata.latitude,
                            longitude: eventdata.longitude,
                            markerColor: eventMarkerColor,
                            click: function() {
                                Map.getVehicleEvent(gmap, unitdata.unit_id, eventdata.id);
                            }
                        };
                        
                        gmap.addMarker(markerOptions, false);
                        
                        $('#vehicle-li-'+unitdata.unit_id).addClass('active')
                                                          .data('event-id', eventdata.id)
                                                          .data('latitude', eventdata.latitude)
                                                          .data('longitude', eventdata.longitude);
                    }
                } else if (type == 'landmark') {
                    var landmarkData = this,
                        markerOptions = {
                            id: landmarkData.territory_id,
                            name: landmarkData.territoryname,
                            latitude: landmarkData.latitude,
                            longitude: landmarkData.longitude,
                            click: function() {
                                Map.getLandmarkInfo(gmap, landmarkData.territory_id);
                            }
                        },
                        polygonOptions = {
                            type: landmarkData.shape,
                            radius: landmarkData.radius,
                            points: landmarkData.coordinates
                        }
                    ;

                    Map.addMarkerWithPolygon(gmap, markerOptions, false, polygonOptions);
                    
                    $('#landmark-li-'+landmarkData.territory_id).addClass('active')
                                                               .data('latitude', landmarkData.latitude)
                                                               .data('longitude', landmarkData.longitude);    
                }

                if (key == unitCount) {
                    gmap.updateMapBound();
                }               
            }); 
        }	
	},
	
	addMarkerWithPolygon: function(gmap, markerOptions, hideLabel, polygonOptions) {
        var polygonOpts = {},
    	    points = []
        ;

        if (polygonOptions.polygonColor != undefined) {
            polygonOpts.polygonColor = polygonOptions.polygonColor;
        }

    	switch (polygonOptions.type) {
        	case 'circle':
                var sides = 32,    // # of desired sides for this polygon (less equals faster rendering)
                    points = this.getCirclePoints(gmap, markerOptions.latitude, markerOptions.longitude, polygonOptions.radius, sides)
                ;        	
                polygonOpts.radius = polygonOptions.radius;
                polygonOpts.points = points;
                break;
            case 'square':
                if (polygonOptions.points == undefined) {
                    points = this.getSquarePoints(gmap, markerOptions.latitude, markerOptions.longitude, polygonOptions.radius);
                } else {      
                    for (var i=0; i<polygonOptions.points.length; i++) {
                        points.push(gmap.convertToGLatLng(polygonOptions.points[i].latitude, polygonOptions.points[i].longitude));
                    }
                }
                polygonOpts.radius = polygonOptions.radius;
                polygonOpts.points = points;
                break;
            case 'rectangle':
            case 'polygon':
                if (polygonOptions.points != undefined) {
                    for (var i=0; i<polygonOptions.points.length; i++) {
                        points.push(gmap.convertToGLatLng(polygonOptions.points[i].latitude, polygonOptions.points[i].longitude));
                    }
                }
                polygonOpts.points = points;
                break;
            default:
                break;	   
    	}

        gmap.addMarker(markerOptions, hideLabel, polygonOpts);	
	},
	
	updateMarkerWithPolygon: function(gmap, markerId, markerOptions, polygonOptions) {
        if (polygonOptions != undefined && ! $.isEmptyObject(polygonOptions)) {
            var type = polygonOptions.type;
            if (type != undefined) {                
                switch (type) {
                    case 'circle':
                        var sides = 32;
                        polygonOptions.points = this.getCirclePoints(gmap, markerOptions.latitude, markerOptions.longitude, polygonOptions.radius, sides);
                        break;
                    case 'square':
                        polygonOptions.points = this.getSquarePoints(gmap, markerOptions.latitude, markerOptions.longitude, polygonOptions.radius);
                        break;
                    case 'rectangle':
                        break;
                    case 'polygon':
                        break;        
                }   
            }
        }
        gmap.updateMarker(markerId, markerOptions, polygonOptions);	
	},
	
	
	/**
	 * Geocodes an address and returns a lat/lng pair (uses Decarta's Geocoding Web Service when using Mapbox)
	 *
	 */
	geocode: function(gmap, address, callBack) {
        if (address != undefined && address != '') {

            var address = $.trim(address),
                api = Map.api()
            ;

            if (api == 'mapbox') {  //  if using Mapbox Maps, perform geocode using Decarta
                      
                $.ajax({
                    url: 'https://api.decarta.com/v1/'+Map.decarta_api_key()+'/search/' + encodeURI(address) + '.JSON',
                    type: 'GET',
                    dataType: 'json',
                    success: function(responseData) {
                        var results   = responseData.results
                            ,response = {}
                            ,error    = ''
                            ,result   = undefined
                        
                        if (results.length > 0) {
                            response.address_components = Map.formatDecartaResult(results[0])
                            response.latitude           = response.address_components.latitude;
                            response.longitude          = response.address_components.longitude;
                            response.formatted_address  = response.address_components.formatted_address;
                        } else {
                            error = 'No result found for address: ' + address;
                        }
                        
                        response.success = (error == '') ? 1 : 0;    // 1 means success, 0 means failure
                        response.error = error;
                        
                        if (callBack != undefined && typeof(callBack) == 'function') {
                            callBack(response);    
                        }    
                    }    
                });
            
            } else if (api == 'google') {   // else if using Google Maps, perform geocode using Google's Geocoding Service
                gmap.geocode(address, callBack);                
            }

        } else {
            alert('Cannot geocode due to invalid address');
        }   	
	},

	/**
	 * Formats a Decarta result that was returned from geocoding
	 *
	 */	
	formatDecartaResult: function(result) {

		var pos, response = {
            county			    : result.address.countrySecondarySubdivision
            ,address 		    : result.address.streetName
            ,city			    : result.address.countryTertiarySubdivision
            ,state			    : result.address.countrySubdivision
            ,zip			    : result.address.postalCode
            ,country            : result.address.countryCode
        }

        if (result.address.streetNumber) response.address = result.address.streetNumber+' '+response.address;

        if (typeof result.position == 'string') {
        	pos = result.position.split(',')
            response.latitude  = pos[0]
            response.longitude = pos[1]
        } else {
            response.latitude  = result.position.lat
            response.longitude = result.position.lon
        }

		response.formatted_address = response.address+', '+response.city+', '+response.state+' '+response.zip

        return response
	},

	/**
	 * Reverse geocodes a lat/lng pair and returns an array of address components (uses Decarta)
	 *
	 */	
	reverseGeocode: function(gmap, latitude, longitude, callBack) {
        if (latitude != undefined && latitude != '' && longitude != undefined && longitude != '') {

            callBack = ((callBack != undefined) && (typeof(callBack) == 'function')) ? callBack : undefined;            
            
            if (callBack !== undefined) {
                
                var api = Map.api();
                
                if (api == 'mapbox') {  // if using Mapbox, use Decarta for doing reverse geocoding
                    $.ajax({
	                    url: 'https://api.decarta.com/v1/'+Map.decarta_api_key()+'/reverseGeocode/' + encodeURI(latitude + ',' + longitude) + '.JSON',
                        type: 'GET',
                        dataType: 'json',
                        success: function(responseData) {
                            var results = responseData.addresses,
                                response = {},
                                error = ''
                            
                            if (results.length > 0) {
	                            response.address_components = Map.formatDecartaResult(results[0])
	                            response.latitude           = response.address_components.latitude;
	                            response.longitude          = response.address_components.longitude;
	                            response.formatted_address  = response.address_components.formatted_address;
                            } else if ( (latitude) && (longitude) ) {
                                error = 'No result found for lat/lon: ' + latitude + '/' + longitude;
                            }
                            
                            response.success = (error == '') ? 1 : 0;    // 1 means success, 0 means failure
                            response.error = error;
                            
                            if (callBack != undefined && typeof(callBack) == 'function') {
                                callBack(response);    
                            }    
                        }    
                    });

                } else if (api == 'google') { // else if using Google Maps, use Google's Geocoding Service for reverse geocoding
                    gmap.reverseGeocode(latitude, longitude, callBack);                   
                }
            }
        } else {
            alert('Cannot geocode due to invalid coordinates');
        }    	
	},
	
	createTempMarker: function(gmap, latitude, longitude, title, isDraggable, eventsCallbacks) {
    	if (latitude != undefined && latitude != '' && longitude != undefined && longitude != '') {
        	title = title || '';
        	eventsCallbacks = eventsCallbacks || undefined;
        	isDraggable = isDraggable || false;
        	
            gmap.createTempMarker(latitude, longitude, title, isDraggable, eventsCallbacks);        	
        } else {
        	if($('#modal-add-landmark').is(':visible')){
	            $('#landmark-error').html('Cannot create marker due to invalid coordinates');
        	} else {
	            alert('Cannot create marker due to invalid coordinates');
        	}
        }
	},
    
    createTempPolygon: function(gmap, type, latitude, longitude, radius, polygonColor, callbacks) {
console.log('createTempPolygon');
        if (type != undefined && latitude != undefined && longitude != undefined) {
            var points = [];
            switch (type) {
                case 'circle':
                    radius = radius || 0;
                    points = this.getCirclePoints(gmap, latitude, longitude, radius, 32);
                    break;
                case 'square':
                    radius = radius || 0;
                    points = this.getSquarePoints(gmap, latitude, longitude, radius);
                    break
                case 'rectangle':
                    points = this.getRectanglePoints(gmap, latitude, longitude, callbacks);
                    break;
                case 'polygon':
                    points = this.getPolygonPoints(gmap, latitude, longitude, callbacks);
                    break;
                default:
                    break;
            }
            
            if (points.length > 0) {
                gmap.createTempPolygon(latitude, longitude, radius, points, polygonColor);    
            }    
        } else {
        	if($('#modal-add-landmark').is(':visible')){
	            $('#landmark-error').html('Cannot create polygon due to invalid coordinates and/or radius');    
        	} else {
	            alert('Cannot create polygon due to invalid coordinates and/or radius');    
        	}
        }    
    },    
	
	getCirclePoints: function(gmap, latitude, longitude, radius, points) {

    	radius = parseFloat(radius * 0.00018939393); // convert feet to miles
    	
		// find the raidus in lat/lon
		var earthsradius = 3963; 	// 3963 is the radius of the earth in miles
		var d2r = Math.PI / 180;	// degrees to radians
		var r2d = 180 / Math.PI;   	// radians to degrees
		var rlat = (radius / earthsradius) * r2d;
		var rlng = rlat / Math.cos(latitude * d2r);
		var retPoints = [];

		for (var i=0; i < points+1; i++) { // one extra here makes sure we connect the
		  var theta = Math.PI * (i / (parseInt(points) / 2));
		  var ex = parseFloat(longitude) + (parseFloat(rlng) * Math.cos(theta)); // center a + radius x * cos(theta)
		  var ey = parseFloat(latitude) + (parseFloat(rlat) * Math.sin(theta)); // center b + radius y * sin(theta)
		  
		  retPoints.push(gmap.convertToGLatLng(ey, ex));
		}

		return retPoints;    	
	},
	
	getSquarePoints: function(gmap, lat, lng, radius) {
		
		radius = parseFloat(radius * 0.00018939393); // convert feet to miles

		// find the raidus in lat/lon
		var earthsradius = 3963; 	// 3963 is the radius of the earth in miles
		var d2r = Math.PI / 180;	// degrees to radians
		var r2d = 180 / Math.PI;   	// radians to degrees
		var rlat = (radius / earthsradius) * r2d;
		var rlng = rlat / Math.cos(lat * d2r);
		var points = 4;
		retPoints = [];

		for (var i=0; i < points+1; i++) // one extra here makes sure we connect the
		{
		  var theta = Math.PI * (i / (parseInt(points) / 2));
		  var ex = parseFloat(lng) + (parseFloat(rlng) * Math.cos(theta)); // center a + radius x * cos(theta)
		  var ey = parseFloat(lat) + (parseFloat(rlat) * Math.sin(theta)); // center b + radius y * sin(theta)

		  retPoints.push(gmap.convertToGLatLng(ey, ex));
		}

		squarePoints = [
			gmap.convertToGLatLng(gmap.getLat(retPoints[3]), gmap.getLng(retPoints[0])),
			gmap.convertToGLatLng(gmap.getLat(retPoints[1]), gmap.getLng(retPoints[0])),
			gmap.convertToGLatLng(gmap.getLat(retPoints[1]), gmap.getLng(retPoints[2])),
			gmap.convertToGLatLng(gmap.getLat(retPoints[3]), gmap.getLng(retPoints[2]))
		];
		
		return squarePoints;
	},
	
	getRectanglePoints: function(gmap, latitude, longitude, callbacks) {
    	var points = [],
    	    callbacks = callbacks || undefined
        ;
        
        if (gmap._tempMarkerArray == undefined) {
            gmap._tempMarkerArray = [];
        }

        if (gmap._tempMarkerArray != undefined && gmap._tempMarkerArray.length < 2) {
            gmap.addPointToRectangle(latitude, longitude, callbacks);
            
            for (var i=0; i<gmap._tempMarkerArray.length; i++) {
                points.push(gmap._tempMarkerArray[i].getLatLng());
            }

        } else {
            alert('Rectangles are limited to 2 points. Please drag the points to edit the rectangle.');
        }	
        
        return points;
	},

	getPolygonPoints: function(gmap, latitude, longitude, callbacks) {
        var points = [],
            callbacks = callbacks || undefined
        ;
        
        if (gmap._tempMarkerArray == undefined) {
            gmap._tempMarkerArray = [];
        }

        if (gmap._tempMarkerArray != undefined && gmap._tempMarkerArray.length < 10) {
            
            gmap.addPointToPolygon(latitude, longitude, callbacks);
            
            for (var i=0; i<gmap._tempMarkerArray.length; i++) {
                points.push(gmap._tempMarkerArray[i].getLatLng());
            }
            
        } else {
            alert('Polygons are limited to 10 points.');
        }
        
        return points;	
	},
	
	updateTempMarker: function(gmap, params) {
// console.log('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! updateTempMarker');
    	if (typeof(params) == 'object' && ! $.isEmptyObject(params)) {
            gmap.updateTempMarker(params);
        }    	
	},

	updateTempPolygon: function(gmap, params) {
// console.log('updateTempPolygon');
    	if (typeof(params) == 'object' && ! $.isEmptyObject(params) && params.type != undefined) {
        	var latitude = params.latitude || gmap._tempPolygon.latitude,
        	    longitude = params.longitude || gmap._tempPolygon.longitude,
        	    radius = params.radius || gmap._tempPolygon.radius,
        	    callbacks = params.callbacks || undefined,
        	    points = []
            ;
            
            switch (params.type) {
                case 'circle':
                    points = this.getCirclePoints(gmap, latitude, longitude, radius, 32);
                    break;
                case 'square':
                    points = this.getSquarePoints(gmap, latitude, longitude, radius);
                    break;
                case 'rectangle':
                    points = this.getRectanglePoints(gmap, latitude, longitude, callbacks);
                    break;
                case 'polygon':
                    points = this.getPolygonPoints(gmap, latitude, longitude, callbacks);
                    break;
                default:
                    break;    
            }
            
            if (points.length > 0) {
                gmap.updateTempPolygon(latitude, longitude, radius, points, params.type);
            }
        }
	},
		
	createTempLandmark: function(gmap, type, latitude, longitude, radius, title, isDraggable, markerCallback, pointCallback) {
console.log('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! createTempMarker');
    	isDraggable = (type == 'circle' || type == 'square') ? true : false;
    	var polygonColor = '#FF0000';
    	if (type != 'rectangle' && type != 'polygon') {
	        this.createTempMarker(gmap, latitude, longitude, title, isDraggable, markerCallback);
        }
        this.createTempPolygon(gmap, type, latitude, longitude, radius, polygonColor, pointCallback);	
	},
	
	updateTempLandmark: function(gmap, type, latitude, longitude, radius, title, pointCallback, isDraggable) {
// console.log('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! updateTempMarker');
		if(isDraggable != false){
			isDraggable = true;
		}
    	if (type != 'rectangle' && type != 'polygon') {
            this.updateTempMarker(gmap, {draggable: isDraggable, latitude: latitude, longitude: longitude, title: title});
        }
        this.updateTempPolygon(gmap, {type: type, latitude: latitude, longitude: longitude, radius: radius, callbacks: pointCallback});    	
	},
	
	removeTempLandmark: function(gmap) {
    	gmap.removeTempMarker();
    	gmap.removeTempPolygon();
	},
	
	doesTempLandmarkExist: function(gmap) {
console.log('gmap._tempMarker='+gmap._tempMarker+', gmap._tempPolygon='+gmap._tempPolygon)
    	if(gmap._tempMarker){
	    	return true;
    	} else if(gmap._tempPolygon){
	    	return true;
    	} else {
	    	return false;
    	}
	},

	addMapClickListener: function(gmap, clickCallback) {
    	if (clickCallback != undefined && typeof(clickCallback) == 'function') {
            gmap.addMapClickListener(clickCallback);
        }	
	},
	
	removeMapClickListener: function(gmap) {//, callback) {
	    //callback = (callback != undefined && typeof(callback) == 'function') ? callback : undefined;
        gmap.removeMapClickListener();	
	},
	
	hasTempMarkers: function(gmap) {
        return gmap.hasTempMarkers();	
	},
	
	clickMap: function(gmap, lat, lng) {
        if (lat != '' && lng != '') {
            gmap.clickMap(lat, lng);
        }	
	},
	
	hideMarker: function(gmap, id) {
        if (id != '') {
            gmap.hideMarker(id);
        }	
	},
	
	showMarker: function(gmap, id, callback) {
    	if (id != '') {
            callback = (callback != undefined && typeof callback == 'function') ? callback : undefined;
        	gmap.showMarker(id, callback);
    	}
	},
	
	getTempMarkerArray: function(gmap) {
        return gmap.getTempMarkerArray();	
	},
	
	getTempPolygonPoints: function(gmap) {
        return gmap.getTempPolygonPoints();
	},
	
	getTempMarkerPosition: function(gmap) {
        return gmap.getTempMarkerPosition();	
	}
});
