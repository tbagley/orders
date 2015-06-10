/*

    Gmap JS

    File:       /assets/js/gmap.js
    Author:     Yang Vang
*/

function GMap (div_id, opts) {
    var me = this;
    if (div_id == '' || div_id == undefined) {
        alert('A DOM element id is required as the first parameter to initialize the map');
    } else {
        var div = document.getElementById(div_id);
        if (div) {
            me.initialize(div, opts);
        } else {
            alert('Failed to get DOM container for map initialization');
        }
    }
}

GMap.prototype.initialize = function(div, opts) {
    var me = this;
    opts = opts || {};
    me._latitude = opts.latitude || 37.406948;
    me._longitude = opts.longitude || -96.0634765625;
    me._defaultPolygonColor = '#FF0000'; // red
    me._zoom = opts.zoom || 5;
    me._minZoom = opts.minZoom || 3;
    me._maxZoom = opts.maxZoom || 15;
    me._map = undefined;
    var center = new google.maps.LatLng(me._latitude, me._longitude);
    me._markerBound = new google.maps.LatLngBounds();
    me._streetViewService = new google.maps.StreetViewService();
    me._markerArray = new Array();

    var mapOptions = {
        maxZoom:    me._maxZoom,
        minZoom:    me._minZoom,
        zoom: 		me._zoom,
        center: 	center,
        mapTypeId:	google.maps.MapTypeId.ROADMAP,
        mapTypeControlOptions: {
            mapTypeIds: Array(
                google.maps.MapTypeId.ROADMAP,
                google.maps.MapTypeId.SATELLITE
            ),
            style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR
        },
        scaleControl: true
    };
    
    me._map = new google.maps.Map(div, mapOptions);

    if ((opts.callBack !== undefined) && (typeof(opts.callBack) == 'function')) {
		google.maps.event.addListenerOnce(me._map, 'bounds_changed', function() {
			opts.callBack();
		});
	}
/*	
	google.maps.event.addListener(me._map, 'zoom_changed', function(event) {
		console.log(me._map.getZoom());
	});
	
	google.maps.event.addListener(me._map, 'resize', function(event) {
		console.log('resize');
	});
*/
}

GMap.prototype.resize = function() {
    google.maps.event.trigger(this._map, 'resize');
}

GMap.prototype.addMarker = function(options, hideLabel, polygonOptions) {
	var me = this,
		label = options.name,
		lat = options.latitude,
		long = options.longitude
	;

	var point = new google.maps.LatLng(parseFloat(lat), parseFloat(long));

	var marker = new google.maps.Marker({
		position: point,
		map: me._map,
		title: label	
	});

	var hideLabel = hideLabel && true;

	marker.id = options.id;

	marker.label = new InfoBox({
		content:				label, 
		boxClass: 				'google_map_labels google_marker_label', 
		boxStyle: 				{opacity: 1, textAlign: "center"}, 
		position: 				point, 
		closeBoxURL: 			"", 
		isHidden:				hideLabel,
		pixelOffset: 			new google.maps.Size(10,-32), 
		pane: 					"overlayShadow", 
		enableEventPropagation: true,
		disableAutoPan:			true
	});

	marker.label.open(me._map, marker);

	if (polygonOptions != undefined && typeof polygonOptions == 'object') {
        var polygonColor = polygonOptions.polygonColor || me._defaultPolygonColor;
        var points = polygonOptions.points;        
        if (points != undefined && typeof(points) == 'object' && points.length > 0) {
            marker.polygon = new google.maps.Polygon({
            	paths: 			points,
            	clickable:		false,
            	editable:		false,
            	fillColor:		polygonColor, //'#00FF00',
            	fillOpacity:	0.1,
            	strokeColor:	polygonColor, //'#00FF00',
            	strokeWeight:	1,
            	strokeOpacity:	0.9,
            	map:            me._map                        
            });
        }
	}

    if (options.click != undefined && typeof(options.click) == 'function') {            
        google.maps.event.addListener(marker, 'click', function() { 
            options.click();
        });
    }       

    google.maps.event.addListener(marker, 'mouseover', function() { 
        marker.css({ 'z-index': 999999 });
    });

    google.maps.event.addListener(marker, 'mouseout', function() { 
        marker.css({ 'z-index': 100 });
    });

	this._markerArray.push(marker);
	me._markerBound.extend(point);
}

GMap.prototype.resetMap = function() {
	var center = new google.maps.LatLng(37.406948, -96.0634765625);
	this._map.panTo(center);
	this._map.setZoom(5);
	this._markerBound = new google.maps.LatLngBounds(center, center);
	this.closeInfoWindow();
}

GMap.prototype.mapZoom = function(zoom) {
    if(zoom>0){
        this._map.setZoom(zoom);
    }
}

GMap.prototype.openInfoWindow = function(lat, long, html) {
	var coordinate = new google.maps.LatLng(lat, long);
	var opts = {
		content: html,
		position: coordinate
	};
	
	if (this._infoWindow == undefined) {
		this._infoWindow = new google.maps.InfoWindow(opts);	
	} else {
		this._infoWindow.setOptions(opts);
	}
	
	this._infoWindow.setMap(this._map);
	
	this._map.setCenter(coordinate);
}

GMap.prototype.updateMapBound = function() {
    this._map.fitBounds(this._markerBound);
    this._map.panTo(this._markerBound.getCenter());
}

GMap.prototype.updateMapBoundZoom = function(bound) {
console.log('updateMapBoundZoom:'+updateMapBoundZoom);
    if(bound>0){
        this._map.setZoom(bound);
    }
    this._map.panTo(this._markerBound.getCenter());
}

GMap.prototype.centerMap = function(latitude, longitude) {
	this._map.panTo(new google.maps.LatLng(latitude, longitude));
}

GMap.prototype.closeInfoWindow = function() {
	if (this._infoWindow != undefined) {
		this._infoWindow.close();
	}
}

GMap.prototype.removeMarker = function(id) {
	var length = this._markerArray.length,
		i = ''
	;
	
	if (length > 0) {
		for (i=0; i<length; i++) {
			if (this._markerArray[i].id == id) {
				this._markerArray[i].setMap(null);
				this._markerArray[i].label.close();
				if (this._markerArray[i].polygon != undefined) {
    				this._markerArray[i].polygon.setMap(null);
				}
				break;
			}
		}
		
		if (i !== '') {
			this._markerArray.splice(i, 1);
		}		
	}
}

GMap.prototype.showStreetView = function(divId, latitude, longitude, successCallBack, closeCallBack) {
		var me = this;
		var point = new google.maps.LatLng(latitude, longitude);
		var options = {position: point, enableCloseButton: true};
		var htmlElement = '';

		htmlElement = document.getElementById(divId);		
		me._pano = new google.maps.StreetViewPanorama(htmlElement, options);
		
		me._streetViewService.getPanoramaByLocation(point, 50, function(panoData, status) {
			if (status == google.maps.StreetViewStatus.OK) {
				if ((successCallBack !== undefined) && (typeof(successCallBack) == 'function')) {
					if ((closeCallBack !== undefined) && (typeof(closeCallBack) == 'function')) {
						google.maps.event.addListenerOnce(me._pano, 'closeclick', function() {
							closeCallBack();	
						});
					}
					successCallBack();
					me._pano.setVisible(true);								
				}				
			} else {
				switch (status) {
					case google.maps.StreetViewStatus.UNKNOWN_ERROR:
						errorMsg = 'Error: The request could not be process due to an unknown error.'
						break;
					case google.maps.StreetViewStatus.ZERO_RESULTS:
						errorMsg = 'Error: There is no streetview near by.'
						break; 
				}								
				alert(errorMsg);
			}
	});	
}

GMap.prototype.clickMarker = function(id) {
	var length = this._markerArray.length;
	if (length > 0) {
		for (var i=0; i<length; i++) {
			if (this._markerArray[i].id == id) {
				google.maps.event.trigger(this._markerArray[i], 'click');
				break;
			}
		}		
	}	
}

GMap.prototype.showHideLabel = function(id, showLabel) {
	var length = this._markerArray.length;
	if (length > 0) {
		for (var i=0; i<length; i++) {
			if (this._markerArray[i].id == id) {
				if (showLabel == true) {
					this._markerArray[i].label.show();
				} else {
					this._markerArray[i].label.hide();
				}
				break;
			}
		}		
	}	
}

GMap.prototype.showHideAllLabels = function(showLabel) {
	var length = this._markerArray.length;
	if (length > 0) {
		for (var i=0; i<length; i++) {
			if (showLabel == true) {
				this._markerArray[i].label.show();
			} else {
				this._markerArray[i].label.hide();
			}
		}		
	}	
}

GMap.prototype.clearMarkers = function(callBack) {
	var length = this._markerArray.length;
	if (length > 0) {
		
		this.closeInfoWindow();
		
		for (var i=0; i<length; i++) {
		
			this._markerArray[i].setMap(null);
			this._markerArray[i].label.hide();
			if (this._markerArray[i].polygon != undefined) {
    			this._markerArray[i].polygon.setMap(null);
			}			
			
			if (i == (length - 1)) {
				this._markerArray = new Array();
//				this.resetMap();
                if ((callBack == undefined) && (typeof callBack == 'function')) {
                    callBack();
                }
			}
		}	
	}
}

GMap.prototype.updateMarker = function(id, markerOptions, polygonOptions) {
	var me = this,
	    length = me._markerArray.length;
	if (length > 0) {
		for (var i=0; i<length; i++) {
			if (this._markerArray[i].id == id) {
				this._markerArray[i].setPosition(new google.maps.LatLng(markerOptions.latitude, markerOptions.longitude));
				
				if ((markerOptions.click != undefined) && (typeof(markerOptions.click) == 'function')) {
					google.maps.event.clearListeners(this._markerArray[i]);
					google.maps.event.addListener(this._markerArray[i], 'click', function() {
						markerOptions.click();	
					});					
				}
				
				if (this._markerArray[i].polygon != undefined) {
    				if (polygonOptions != undefined && typeof polygonOptions == 'object') {
        				var polygonColor = polygonOptions.polygonColor || me._defaultPolygonColor;
        				if (polygonOptions.points != undefined) {
            				this._markerArray[i].polygon.setOptions({
                            	paths: 			polygonOptions.points,
                            	clickable:		false,
                            	editable:		false,
                            	fillColor:		polygonColor, //'#00FF00',
                            	fillOpacity:	0.1,
                            	strokeColor:	polygonColor, //'#00FF00',
                            	strokeWeight:	1,
                            	strokeOpacity:	0.9,
                            	map:            me._map                        
                            });                           
        				}
    				}
				}
				
				break;
			}		
		}	
	}	
}

GMap.prototype.updateMarkerBound = function() {
	this._markerBound = new google.maps.LatLngBounds();
	var length = this._markerArray.length;
	if (length > 0) {		
		for (var i=0; i<length; i++) {
			this._markerBound.extend(this._markerArray[i].getPosition());
		}	
	}
}

GMap.prototype.initGeocoder = function() {
    if (this._geocoder == undefined) {
        this._geocoder = new google.maps.Geocoder();    
    }
}

GMap.prototype.geocode = function(address, callBack) {
    this.initGeocoder();
    var returnData = {},
        that = this
    ;
    
    this._geocoder.geocode({'address': address}, function(results, status){
        if (status == google.maps.GeocoderStatus.OK) {
            Core.log(results);
            var coords = results[0].geometry.location;
            if (coords != null) { 
                if ((callBack != undefined) && typeof(callBack) == 'function') {
                    returnData.success = 1;
                    returnData.formatted_address = results[0].formatted_address;
                    returnData.latitude = results[0].geometry.location.lat();
                    returnData.longitude = results[0].geometry.location.lng();
                    returnData.partial_match = results[0].partial_match;
                    
                    if (results[0].address_components.length > 0) {
                        returnData.address_components = that.cleanGoogleAddress(results[0].address_components);
                    }
                    
                    callBack(returnData);
                }
            }
        } else {
            returnData = {
                success: 0,
                error: 'Geocoding failed due to: ' + status
            };
                   
            if ((callBack != undefined) && typeof(callBack) == 'function') {
                callBack(returnData);
            }
        }
    });	       
}

GMap.prototype.reverseGeocode = function(latitude, longitude, callBack) {
    this.initGeocoder();
    var returnData = {},
        that = this
    ;
    
    this._geocoder.geocode({'location': new google.maps.LatLng(latitude, longitude)}, function(results, status){
        if (status == google.maps.GeocoderStatus.OK) {
            if ((callBack != undefined) && typeof(callBack) == 'function') {
                returnData.success = 1;
                returnData.formatted_address = results[0].formatted_address;
                returnData.latitude = results[0].geometry.location.lat();
                returnData.longitude = results[0].geometry.location.lng();
                returnData.partial_match = results[0].partial_match;
                
                if (results[0].address_components.length > 0) {
                    returnData.address_components = that.cleanGoogleAddress(results[0].address_components);
                }
                
                callBack(returnData);
            }
        } else {
            returnData = {
                success: 0,
                error: 'Geocoding failed due to: ' + status
            };

            if ((callBack != undefined) && typeof(callBack) == 'function') {
                callBack(returnData);
            }
        }
    });	       
}


GMap.prototype.cleanGoogleAddress = function(googleAddress) {

    var returnAddress = {
            streetNumber:	'',
            streetName: 	'',
            county:			'',
            address: 		'',
            city:			'',
            state:			'',
            zip:			'',
            country: 		''
        },
        length = googleAddress.length,
        component = {}
    ;

    for (var i=0; i<length; i++) {
        component = googleAddress[i];
        
        switch(googleAddress[i].types[0]) {
            case 'street_number':
                returnAddress.streetNumber 			= (component.long_name !== undefined) ? component.long_name : '';
                break;
            case 'route':
                returnAddress.streetName 			= (component.long_name !== undefined) ? component.long_name : '';
                break;
            case 'neighborhood':
            case 'locality':
                returnAddress.city 					= (component.long_name !== undefined) ? component.long_name : '';
                break;
            case 'administrative_area_level_2':
                returnAddress.county 				= (component.long_name !== undefined) ? component.long_name : '';
                break;
            case 'administrative_area_level_1':
                returnAddress.state 				= (component.short_name !== undefined) ? component.short_name : '';
                break;
            case 'country':
                returnAddress.country		 		= (component.short_name !== undefined) ? component.short_name : '';
                break;
            case 'postal_code':
                returnAddress.zip		 			= (component.long_name !== undefined) ? component.long_name : '';
                break;
        }		        
    }

    returnAddress.address = ((returnAddress.streetNumber !== '') ? (returnAddress.streetNumber + ' ') : '') + ((returnAddress.streetName !== '') ? returnAddress.streetName: ''); 
    returnAddress.address = returnAddress.address == undefined ? '' : returnAddress.address;
    returnAddress.country = ((returnAddress.country == 'US') ? 'USA' : returnAddress.country);

    return returnAddress;
}

GMap.prototype.createTempMarker = function(latitude, longitude, title, isDraggable, eventsCallbacks) {
    var that = this,
        latlng = new google.maps.LatLng(latitude, longitude),
        title = title || '',
        isDraggable = isDraggable || false
    ;
    
    if (this._tempMarker != undefined) {
    	this._tempMarker.setMap(null);
    }
    
    this._tempMarker = new google.maps.Marker({position: latlng, draggable: isDraggable});
    this._tempMarker.label = new InfoBox({
    	content:				title, 
    	boxClass: 				'google_map_labels', 
    	boxStyle: 				{opacity: 1, textAlign: "center"}, 
    	position: 				latlng, 
    	closeBoxURL: 			"", 
    	isHidden:				false,
    	pixelOffset: 			0, 
    	pane: 					"overlayShadow", 
    	enableEventPropagation: true
    });
    		
    this._tempMarker.setMap(that._map);
    this._tempMarker.label.open(that._map, that._tempMarker);
    this._map.panTo(latlng);
    
    this.setTempMarkerEvents(eventsCallbacks);
}

GMap.prototype.setTempMarkerEvents = function(eventsCallbacks) {
    if ((eventsCallbacks != undefined) && (typeof(eventsCallbacks) == 'object')) {
        var that = this;
        
        if ((eventsCallbacks.click != undefined) && (typeof(eventsCallbacks.click) == 'function')) {
            google.maps.event.addListener(that._tempMarker, 'click', function(event) {			
            	eventsCallbacks.click({latitude: event.latLng.lat(), longitude: event.latLng.lng()});
            });   
        }
        
        if ((eventsCallbacks.drag != undefined) && (typeof(eventsCallbacks.drag) == 'function')) {
            google.maps.event.addListener(that._tempMarker, 'drag', function(event) {			
            	eventsCallbacks.drag({latitude: event.latLng.lat(), longitude: event.latLng.lng()});
            });
        }
        
        if ((eventsCallbacks.dragend != undefined) && (typeof(eventsCallbacks.dragend) == 'function')) {
            google.maps.event.addListener(that._tempMarker, 'dragend', function(event) {		
                eventsCallbacks.dragend({latitude: event.latLng.lat(), longitude: event.latLng.lng()});				
            });    
        }    
    }    
}

GMap.prototype.createTempPolygon = function(latitude, longitude, radius, points, polygonColor) {
    if (points != undefined && typeof(points) == 'object' && points.length > 0) {
        if (this._tempPolygon != undefined) {
            this._tempPolygon.setMap(null);
            this._tempPolygon = undefined;
        }

        var that = this;
        var polygonColor = polygonColor || this._defaultPolygonColor;
        
        this._tempPolygon = new google.maps.Polygon({
        	paths: 			points,
        	clickable:		false,
        	editable:		false,
        	fillColor:		polygonColor, //'#00FF00',
        	fillOpacity:	0.1,
        	strokeColor:	polygonColor, //'#00FF00',
        	strokeWeight:	1,
        	strokeOpacity:	0.9,
        	map:            that._map                        
        });
                            
        this._tempPolygon.latitude = latitude;
        this._tempPolygon.longitude = longitude;
        this._tempPolygon.radius = radius; 
    }    
}

GMap.prototype.updateTempMarker = function(params) {
    if (this._tempMarker != undefined) {
        if (params.latitude != undefined && params.longitude != undefined) {
            var point = new google.maps.LatLng(params.latitude, params.longitude);
            this._tempMarker.setPosition(point);
        }
        
        if (params.title != undefined) {
            this._tempMarker.label.setContent(params.title);
        }
    }    
}

GMap.prototype.updateTempPolygon = function(latitude, longitude, radius, points) {
    if (this._tempPolygon != undefined) {
        if (points != undefined && typeof(points) == 'object' && points.length > 0) {

            var that = this;
            var polygonColor = this._defaultPolygonColor;
            
            this._tempPolygon.setOptions({
            	paths: 			points,
            	clickable:		false,
            	editable:		false,
            	fillColor:		polygonColor, //'#00FF00',
            	fillOpacity:	0.1,
            	strokeColor:	polygonColor, //'#00FF00',
            	strokeWeight:	1,
            	strokeOpacity:	0.9,
            	map:            that._map                        
            });
                                
            this._tempPolygon.latitude = latitude;
            this._tempPolygon.longitude = longitude;
            this._tempPolygon.radius = radius;
        
        }
    }    
}

GMap.prototype.removeTempMarker = function() {
    if (this._tempMarker != undefined) {
        
        if (this._tempMarker.label != undefined) {
            this._tempMarker.label.setMap(null);
            this._tempMarker.label = undefined;    
        }
        
        this._tempMarker.setMap(null);        
        this._tempMarker = undefined;    
    }    
}

GMap.prototype.removeTempPolygon = function() {
    if (this._tempPolygon != undefined) {
        this._tempPolygon.setMap(null);
        this._tempPolygon = undefined;    
    }    
}

GMap.prototype.addMapClickListener = function(callBack) {
    var that = this,
        data = {}
    ;
    		
    if (this._mapClickListener != undefined) {
    	google.maps.event.removeListener(this._mapClickListener);
    }
    
    this._mapClickListener = google.maps.event.addListener(that._map, 'click', function(event) {
        data.result = event;
        data.latitude = event.latLng.lat();
        data.longitude = event.latLng.lng();
        if (callBack != undefined && typeof(callBack) == 'function') {
            callBack(data);
        }
    });   
}

GMap.prototype.removeMapClickListener = function() {
    var that = this;
    if (this._mapClickListener != undefined) {
        google.maps.event.removeListener(that._mapClickListener);
    }
}

GMap.prototype.convertToGLatLng = function(latitude, longitude) {
    if (latitude != undefined && latitude != '' && longitude != undefined && longitude != '') {
        return (new google.maps.LatLng(latitude, longitude));
    }    
}

GMap.prototype.getLat = function(latlng) {
    if (latlng != undefined && (typeof latlng == 'object') && (latlng.lat != undefined) && (typeof latlng.lat == 'function')) {
        return latlng.lat();
    }
}

GMap.prototype.getLng = function(latlng) {
    if (latlng != undefined && (typeof latlng == 'object') && (latlng.lng != undefined) && (typeof latlng.lng == 'function')) {
        return latlng.lng();
    }    
}