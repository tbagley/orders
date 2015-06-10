/*

    mapbox JS

    File:       /assets/js/mapbox.js
    Author:     Yang Vang
*/

function MbMap (div_id, opts) {
    var me = this;
    if (div_id == '' || div_id == undefined) {
        alert('A DOM element id is required as the first parameter to initialize the map');
    } else {
        var div = document.getElementById(div_id);
        if (div) {
            me.initialize(div, opts);
        } else {
            switch(div_id){
                case              'map-div' :
                case       'modal-map-hook' :
                case      'popover-map-div' : break;
                                    default : alert('Failed to get DOM container for map initialization ('+div_id+')');
            }
        }
    }
}

MbMap.prototype.initialize = function(div, opts) {
    var me = this;
    opts = opts || {};
    me._latitude = opts.latitude || 37.406948;
    me._longitude = opts.longitude || -96.0634765625;
    me._defaultPolygonColor = '#FF0000'; // red
    me._defaultMarkerColor = 'blue'; // blue
    me._zoom = opts.zoom || 5;
    me._minZoom = opts.minZoom || 2;
    me._maxZoom = opts.maxZoom || 19;
    me._map = undefined;
    me._center = new L.LatLng(me._latitude, me._longitude);
    me._markerMapIndex = {};
    me._tempMarkerMapIndex = {};
    me._markerLayer = L.mapbox.markerLayer();
    me._polygonLayer = L.mapbox.markerLayer();
    me._tempMarkerLayer = L.mapbox.markerLayer();
    //me._iconSize = new L.Point(21, 25);
    //me._iconAnchor = new L.Point(12, 25);
    me._iconSize = new L.Point(20, 30);
    me._iconAnchor = new L.Point(9, 30);
    me._tempIconSize = new L.Point(20, 30);
    me._tempIconAnchor = new L.Point(9, 10);
    
    me._tempMarkerArray = [];

    opts.zoom = me._zoom;
    opts.minZoom = me._minZoom;
    //opts.maxZoom = me._maxZoom;

	//me._map = L.mapbox.map(div, undefined, opts);

	// create the tile layer with correct attribution  
    me._streetJSON = {
        "tilejson": "2.0.0",
        "tiles": ["https://a.tiles.mapbox.com/v3/map-dev.galkko69/{z}/{x}/{y}.png"],  // mapbox tile layer
        //"tiles": ["http://tile.openstreetmap.org/{z}/{x}/{y}.png"],                     // osm tile layer
        "minzoom": me._minZoom,
        "maxzoom": me._maxZoom,
        "geocoder":"https://a.tiles.mapbox.com/v3/examples.map-0l53fhk2/geocode/{query}.jsonp"
        //"attribution": 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>'  
    };
    
    me._satelliteJSON = {
        "tilejson": "2.0.0",
        "tiles": ["https://a.tiles.mapbox.com/v3/map-dev.gam0iokd/{z}/{x}/{y}.png"],  // mapbox tile layer
        //"tiles": ["http://tile.openstreetmap.org/{z}/{x}/{y}.png"],                     // osm tile layer
        "minzoom": me._minZoom,
        "maxzoom": me._maxZoom        
    }
   
//    var tileLayer = L.mapbox.tileLayer(tileJSON);
    me._streetLayer = L.mapbox.tileLayer(me._streetJSON);
    me._satelliteLayer = L.mapbox.tileLayer(me._satelliteJSON);
    
    var street = L.mapbox.tileLayer(me._streetJSON);
    var satellite = L.mapbox.tileLayer(me._satelliteJSON);
    
    opts.layers = [street];
    
    me._map = L.mapbox.map(div, undefined, opts);
    
    var baseMaps = {
        'Road': street,
        'Aerial': satellite    
    };
    
    var baseOptions = {
		"position": 'topleft',
		"collapsed": false
	};
    
    L.control.layers(baseMaps, null, baseOptions).addTo(me._map);
    
    me._map.addLayer(me._markerLayer)
           .addLayer(me._polygonLayer)
           .addLayer(me._tempMarkerLayer)
           .setView(me._center, me._zoom);
console.log(' ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ me._map.setView ~~~~~~~~~~~~~~~~~~~~~~~~~~ '+me._map._container.id);

    if ((opts.callBack !== undefined) && (typeof(opts.callBack) == 'function')) {
		me._map.once('load', function() {
       		opts.callBack();
		});
    }

	/*
	me._map.on('zoomend', function() {
    	Core.log(me._map.getZoom());
	});
	*/

	/*
	me._map.on('contextmenu', function(event) {
    	Core.log('lat: ' + event.latlng.lat + '/ lng: ' + event.latlng.lng);
	});
	*/
       
}

MbMap.prototype.resize = function() {
    Core.log('resize');    
    this._map.invalidateSize();
}

MbMap.prototype.addMarker = function(options, hideLabel, polygonOptions) {
    //Core.log(polygonOptions);
    // hideLabel=true;
    var me = this,
        label = options.name,
        lat = options.latitude,
        long = options.longitude,
        marker = {}, 
        icon = {},
        point = new L.LatLng(lat, long),
        hideLabel = (hideLabel == true) ? ' hidden' : '',
        type = options.type || ''
    ;

    // set provided marker color, else default marker color
    if (options.markerColor != undefined) {
        var markerColor = '_'+options.markerColor;
    } else {
        var markerColor = '_'+me._defaultMarkerColor;
    }
   
    icon = L.divIcon({
		iconSize:	me._iconSize,
		iconAnchor: me._iconAnchor,
        className: 'mapbox_marker_icon mapbox_marker_icon'+markerColor,
    	html: '<div class="mapbox_marker_label'+hideLabel+'">'+label+'</div>'
    });
  
    marker = new L.Marker(point, {icon: icon});

    marker.mouseover = "this.css({'z-index':300});";
    marker.mouseout = "this.css({'z-index':200});";
    marker.name = label;
    marker.latitude = lat;
    marker.longitude = long;
    marker.unit_id = (options.unit_id != undefined ? options.unit_id : undefined);
    marker.click = options.click;
    marker.eventname = options.eventname;

    // add polygon if needed
	if (polygonOptions != undefined && typeof polygonOptions == 'object') {
        var points = polygonOptions.points,
            style,
            polygonColor = polygonOptions.polygonColor || me._defaultPolygonColor
        ;                

		style = {
			color: polygonColor, //'#00FF00',
			opacity: 0.5,
			weight: 1,
			fillColor: polygonColor, //'#00FF00',
			fillOpacity: 0.1,
			clickable: false			
		};		
		
		marker.polygon = new L.Polygon(points, style);
		
		//me._map.addLayer(marker.polygon);	
		me._polygonLayer.addLayer(marker.polygon);
	}	

	if (options.click != undefined && typeof(options.click) == 'function') {			
		marker.on('click', function() {
			options.click();
		});
	}	

    if (options.mouseover != undefined && typeof(options.mouseover) == 'function') {            
        marker.on('mouseover', function() {
            options.mouseover();
        });
    }   

    if (options.mouseout != undefined && typeof(options.mouseout) == 'function') {            
        marker.on('mouseout', function() {
            options.mouseout();
        });
    }   

    if (type == '') {	
        me._markerLayer.addLayer(marker);
        me._markerMapIndex[options.id] = marker._leaflet_id;
    } else if (type == 'temp') {
        me._tempMarkerLayer.addLayer(marker);    
        me._tempMarkerMapIndex[options.id] = marker._leaflet_id;    
    }

	// store vehicle/landmark id - leaflet marker associating id pairs for update/remove    
//    me._markerMapIndex[options.id] = marker._leaflet_id;
}

MbMap.prototype.resetMap = function() {
// console.log('MbMap.prototype.resetMap');
    var me = this;
    
    me._map.setView(me._center, me._zoom);
console.log(' ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ me._map.setView ~~~~~~~~~~~~~~~~~~~~~~~~~~ '+me._map._container.id);
	me.closeInfoWindow();
}

MbMap.prototype.mapZoomGet = function() {
    var me = this;
    return me._map.getZoom();
}

MbMap.prototype.mapZoom = function(zoom) {
// console.log('MbMap.prototype.mapZoom');
    if(zoom>0){
console.log('mapZoom');
        var me = this;
        me._map.setView(me._center, zoom);
console.log(' ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ me._map.setView ~~~~~~~~~~~~~~~~~~~~~~~~~~ '+me._map._container.id);
    }
}

MbMap.prototype.openInfoWindow = function(lat, long, html) {	
	var me = this;
	if (me._popUp == undefined) {        
console.log('Core.Environment.context():'+Core.Environment.context());
        switch(Core.Environment.context()){

            case           'landmark/map' : me._popUp = new L.Popup({offset: new L.Point(200, 55), autoPanPadding: new L.Point(20, 20), closeOnClick: false});
                                            break;

            case            'vehicle/map' : 
                                  default : me._popUp = new L.Popup({offset: new L.Point(200, 115), autoPanPadding: new L.Point(20, 20), closeOnClick: false});
                                            break;

        }
    		
	} else {
    	me._map.removeLayer(me._popUp);
	}
	
	if (html != '') {
		var point = new L.LatLng(lat, long);

		me._map.panTo(point);
		me._popUp.setLatLng(point).setContent(html).openOn(me._map);
console.log('Core.Environment.context():'+Core.Environment.context());
	}	
console.log('openInfoWindow:'+lat+'/'+long);
}

MbMap.prototype.updateMapBound = function(useTempBound,mapbubble) {
// console.log('MbMap.prototype.updateMapBound:'+useTempBound);
    var me = this,
        useTempBound = useTempBound || false,
        bound = (useTempBound == true) ? me._tempMarkerLayer.getBounds() : ((me._polygonLayer.getLayers().length > 0) ? me._polygonLayer.getBounds() : me._markerLayer.getBounds()) 
    ;

    if (bound.isValid()) {
        var latlong = bound.getCenter();
        if(mapbubble){
            latlong.lat = latlong.lat; // + 0.0200;
        }
    	me._map.fitBounds(bound);
    	me._map.panTo(latlong);
	}
}

MbMap.prototype.updateMapBoundZoom = function(useTempBound,mapbubble) {
console.log('MbMap.prototype.updateMapBoundZoom:'+useTempBound);
    var me = this,
        useTempBound = useTempBound || false,
        bound = (useTempBound == true) ? me._tempMarkerLayer.getBounds() : ((me._polygonLayer.getLayers().length > 0) ? me._polygonLayer.getBounds() : me._markerLayer.getBounds()) 
    ;

    if (bound.isValid()) {
        var latlong = bound.getCenter();
        if(mapbubble){
            // latlong.lat = latlong.lat + 0.0200;
        }
        me._map.panTo(latlong);
        if (useTempBound>0) {
    console.log('MbMap.prototype.updateMapBoundZoom:zoom:'+useTempBound);
            me._map.setZoom(useTempBound);
        } else if (bound==true) {
    console.log('MbMap.prototype.updateMapBoundZoom:zoom:10');
            me._map.setZoom(10);
        }
    }
}

MbMap.prototype.centerMap = function(latitude, longitude, zoomlevel) {
console.log('MbMap.prototype.centerMap:'+latitude+':'+longitude+':'+zoomlevel);
    if(zoomlevel>0){
        var me = this;
            me._map.setZoom(zoomlevel);
    }
    this._map.panTo(new L.LatLng(latitude, longitude));
}

MbMap.prototype.zoomMap = function(zoomlevel) {
console.log('MbMap.prototype.zoomMap:'+zoomlevel);
    if(zoomlevel>0){
        var me = this;
        me._map.setView(me._center, zoomlevel);
console.log(' ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ me._map.setView ~~~~~~~~~~~~~~~~~~~~~~~~~~ '+me._map._container.id);
    }
}

MbMap.prototype.closeInfoWindow = function(callback) {
	var me = this;
	if (me._popUp != undefined) {
		me._map.removeLayer(me._popUp);
	}
	
	if (callback != undefined && typeof callback == 'function') {
    	setTimeout(function() {
    	   callback();
        }, 500);
	}
}

MbMap.prototype.removeMarker = function(id, callback) {
    var markerId = this._markerMapIndex[id],
        markerBuffer = {},
        marker = {}
    ;
    
    if (markerId != '' && markerId != undefined) {
        marker = this._markerLayer.getLayer(markerId);
        if (marker != '' && marker != undefined) {
            if (marker.polygon != undefined) {
                //this._map.removeLayer(marker.polygon);    
                this._polygonLayer.removeLayer(marker.polygon._leaflet_id);
            }

// console.log('this._markerLayer.removeLayer('+marker+')');
            this._markerLayer.removeLayer(marker);
            $.each(this._markerMapIndex, function( k, v ) {
                if(k!=id){
                    markerBuffer[k]=v;
// console.log('MbMap.prototype.removeMarker['+k+'] *** KEEPING '+k+':'+v);
                } else {
// console.log('MbMap.prototype.removeMarker['+k+'] *** REMOVING '+k+':'+v);
                }
            });
            this._markerMapIndex = markerBuffer;
            // this._markerMapIndex[id] = undefined;                
        }
    }
    
    if (typeof callback == 'function') {
    	setTimeout(function() {
    	   callback();
        }, 500);        
    }
}
/*
GMap.prototype.showStreetView = function(divId, latitude, longitude, successCallBack, closeCallBack) {
		var me = this;
		var point = new L.LatLng(latitude, longitude);
		var options = {position: point, enableCloseButton: true};
		var htmlElement = '';

		htmlElement = document.getElementById(divId);		
		me._pano = new L.StreetViewPanorama(htmlElement, options);
		
		me._streetViewService.getPanoramaByLocation(point, 50, function(panoData, status) {
			if (status == L.StreetViewStatus.OK) {
				if ((successCallBack !== undefined) && (typeof(successCallBack) == 'function')) {
					if ((closeCallBack !== undefined) && (typeof(closeCallBack) == 'function')) {
						L.event.addListenerOnce(me._pano, 'closeclick', function() {
							closeCallBack();	
						});
					}
					successCallBack();
					me._pano.setVisible(true);								
				}				
			} else {
				switch (status) {
					case L.StreetViewStatus.UNKNOWN_ERROR:
						errorMsg = 'Error: The request could not be process due to an unknown error.'
						break;
					case L.StreetViewStatus.ZERO_RESULTS:
						errorMsg = 'Error: There is no streetview near by.'
						break; 
				}								
				alert(errorMsg);
			}
	});	
}
*/

MbMap.prototype.clickMarker = function(id, isTempMarker) {
    
    isTempMarker = isTempMarker || false;
    
    var markerId = (! isTempMarker) ? this._markerMapIndex[id] : this._tempMarkerMapIndex[id],
        marker = {}
    ;
    
    if (markerId != '' && markerId != undefined) {
        marker = (! isTempMarker) ? this._markerLayer.getLayer(markerId) : this._tempMarkerLayer.getLayer(markerId);
        if (marker != '' && marker != undefined) {
            marker.fire('click');
        }                
    }
}

MbMap.prototype.showHideLabel = function(id, showLabel) {
// console.log('********************************************** MbMap.prototype.showHideLabel:'+id+':'+showLabel);
    var me = this,
        markerId = this._markerMapIndex[id]
    ;
    
    if (markerId != '' && markerId != undefined) {
        var marker = this._markerLayer.getLayer(markerId);
        if (marker != '' && marker != undefined) {
            if (showLabel == true) {
                oldClass = 'mapbox_marker_label hidden';
                newClass = 'mapbox_marker_label';
            } else {
                oldClass = 'mapbox_marker_label';
                newClass = 'mapbox_marker_label hidden';  
            }
            
            marker.options.icon.options.html = marker.options.icon.options.html.replace(oldClass, newClass); 
            
            this._markerLayer.removeLayer(markerId).addLayer(marker);
        }
    }
}

MbMap.prototype.showHideAllLabels = function(showLabel) {
    var me = this;
    me._markerLayer.eachLayer(function(layer) {
        if (showLabel == true) {
            oldClass = 'mapbox_marker_label hidden';
            newClass = 'mapbox_marker_label';
        } else {
            oldClass = 'mapbox_marker_label';
            newClass = 'mapbox_marker_label hidden';  
        }
        
        layer.options.icon.options.html = layer.options.icon.options.html.replace(oldClass, newClass); 
        
        var newLayer = layer;
        
        me._markerLayer.removeLayer(layer).addLayer(newLayer);        
    });    	
}

MbMap.prototype.clearMarkers = function(callBack, onlyClearTempMarkers) {
    var me = this,
        onlyClearTempMarkers = onlyClearTempMarkers || false
    ;
    
    if (onlyClearTempMarkers == false) {  // clear all markers if 'onlyClearTempMarkers' is set to false
        me._markerLayer.clearLayers();
        me._polygonLayer.clearLayers();
        
        me._markerMapIndex = {};
    }
    
    // clear temp markers
    me._tempMarkerLayer.clearLayers();
    
    me._tempMarkerMapIndex = {};
    
    if ((callBack != undefined) && (typeof callBack == 'function')) {
        callBack();
    }
}

MbMap.prototype.updateMarker = function(id, markerOptions, polygonOptions) {
    if (markerOptions != undefined || polygonOptions != undefined) {
        var me = this,
            markerId = '',
            marker = {},
            polygon = {}
        ;
        
        markerId = me._markerMapIndex[id];
        
        if (markerId != '' && markerId != undefined) {
            marker = me._markerLayer.getLayer(markerId);
            if (marker != '' && marker != undefined) {
                if (markerOptions.latitude != undefined && markerOptions.longitude != undefined) {
                    marker.setLatLng(new L.LatLng(markerOptions.latitude, markerOptions.longitude))
                }
                
                if (markerOptions.click != undefined && typeof markerOptions.click == 'function') {
                    marker.off('click');
                    marker.on('click', function() {
                        markerOptions.click();        
                    });    
                }
                
                if (polygonOptions != undefined && polygonOptions.points != undefined && marker.polygon != undefined) {
                    var polygonColor = polygonOptions.polygonColor || me._defaultPolygonColor;
            		var style = {
            			color: polygonColor, //'#00FF00',
            			opacity: 0.5,
            			weight: 1,
            			fillColor: polygonColor, //'#00FF00',
            			fillOpacity: 0.1,
            			clickable: false			
            		};

                    //me._map.removeLayer(marker.polygon);
            		me._polygonLayer.removeLayer(marker.polygon);
            		
            		marker.polygon = new L.Polygon(polygonOptions.points, style);
            		
            		//me._map.addLayer(marker.polygon);
            		me._polygonLayer.addLayer(marker.polygon);	           
                }
                
                me._markerLayer.removeLayer(markerId).addLayer(marker);
            }
        }
    }	
}


/**
 * Update the markerBound array (function if not use, it's required as placeholder) 
 *
 */
MbMap.prototype.updateMarkerBound = function() {
    // this function is not needed for mapbox
    // BUT it's called from the application so it's needed
    // here as a place holder
}

MbMap.prototype.initGeocoder = function() {
    alert('Geocoding service not yet available');
/*
    var me = this;
    if (this._geocoder == undefined) {
        this._geocoder = new L.mapbox.geocoder(me._tileJSON);   
    }
    Core.log('geocoder done');
*/
}


MbMap.prototype.geocode = function(address, callBack) {
    alert('Geocoding service not yet available');
/*
    this.initGeocoder();
    var returnData = {},
        that = this
    ;
    
    this._geocoder.geocode({'address': address}, function(results, status){
        if (status == L.GeocoderStatus.OK) {
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
*/	       
}

MbMap.prototype.reverseGeocode = function(latitude, longitude, callBack) {
    Core.log('reverse geocode');
/*
    callBack({
            success: 1,
            latitude: latitude, 
            longitude: longitude, 
            formatted_address: '123 Fake St, Temecula, CA, 12345, USA', 
            address_components: {
                address: '123 Fake St',
                city: 'Temecula',
                state: 'CA',
                zip: '12345',
                country: 'USA'
            }});

    this.initGeocoder();
    var returnData = {},
        that = this
    ;
    
    this._geocoder.reverseQuery([longitude, latitude], function(error, result){
        Core.log(result);
        if (status == L.GeocoderStatus.OK) {
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
*/	       
}
/*
MbMap.prototype.cleanAddress = function(address) {


}
*/
MbMap.prototype.createTempMarker = function(latitude, longitude, title, isDraggable, eventsCallbacks) {
    var me = this,
        point = new L.LatLng(latitude, longitude),
        title = title || '',
        isDraggable = isDraggable || false,
        icon = {}
    ;
    
    if (me._tempMarker != undefined) {
    	me._map.removeLayer(me._tempMarker);
    }

    icon = L.divIcon({
    	iconSize:	me._iconSize,
    	iconAnchor: me._iconAnchor,    
        className: 'mapbox_marker_icon',     
    	html: '<div class="mapbox_marker_label hidden">'+title+'</div>'
    });
    
    me._tempMarker = new L.Marker(point, {draggable: isDraggable, icon: icon});
    
    me._tempMarker.latitude = latitude;
    me._tempMarker.longitude = longitude;
  
    me._map.addLayer(me._tempMarker);
    me._map.panTo(point);
    
    this.setTempMarkerEvents(eventsCallbacks);

// console.log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> MbMap.prototype.createTempMarker:point:'+point);
}

MbMap.prototype.setTempMarkerEvents = function(eventsCallbacks) {
    if ((eventsCallbacks != undefined) && (typeof(eventsCallbacks) == 'object')) {
        var me = this;
        
        if ((eventsCallbacks.click != undefined) && (typeof(eventsCallbacks.click) == 'function')) {
            me._tempMarker.off('click');
            me._tempMarker.on('click', function(event) {
                var latLng = event.target.getLatLng();			
            	eventsCallbacks.click({latitude: latLng.lat, longitude: latLng.lng});
            });   
        }
        
        if ((eventsCallbacks.drag != undefined) && (typeof(eventsCallbacks.drag) == 'function')) {
            me._tempMarker.off('drag');
            me._tempMarker.on('drag', function(event) {
                var latLng = event.target.getLatLng();
/*                
                Core.log('dragging...');
                var mapBound = me._map.getBounds();
                Core.log('map bound: ' + mapBound);
                Core.log('cursor coord: ' + latLng.lat + ' / ' + latLng.lng);
                if (latLng != undefined && mapBound != undefined) {
                    //me._map.fire('click', {latlng: latLng});
                    //me.panMap(latLng, mapBound);
                    //me._map.panBy(me._map.latLngToLayerPoint(latLng));
                }
                me._drag = true;
*/                
            	eventsCallbacks.drag({latitude: latLng.lat, longitude: latLng.lng});
            });
        }
        
        if ((eventsCallbacks.dragend != undefined) && (typeof(eventsCallbacks.dragend) == 'function')) {
            me._tempMarker.off('dragend');
            me._tempMarker.on('dragend', function(event) {
                me._preventClick = true;
                var latLng = event.target.getLatLng();
                eventsCallbacks.dragend({latitude: latLng.lat, longitude: latLng.lng});
            });    
        }
/*
        me._map.on('mouseout', function(event) {
            Core.log('mouse out');
            if (me._drag == true) {
                setTimeout(function() {
                    Core.log('pan map');
                    Core.log(event);
                    var latlng = me._map.mouseEventToLatLng(event);
                    me.panMap(new L.LatLng(parseFloat(latlng.lat) + 0.0025, latlng.lng), me._map.getBounds());    
                    me._map.fire('mouseout');
                }, 500);
            }    
        });
*/
    }    
}
/*
// TODO: force map to auto pan when dragging marker near the edge of the map
MbMap.prototype.panMap = function(latLng, mapBound) {
    Core.log(latLng);
    Core.log(mapBound);
    var me = this,
        lat = latLng.lat,
        lng = latLng.lng,
        north = mapBound.getNorth(),
        south = mapBound.getSouth(),
        east = mapBound.getEast(),
        west = mapBound.getWest()
    ;
    
    if (lat != undefined) {
//        if (lat >= north) {
        if (lat >= (parseFloat(north) - 0.0015)) {
            Core.log('moving north: ' + north);
            //me._map.panTo(latLng);
            if (me._tempMarker != undefined) {
                Core.log('update marker');
                me._map.panBy([0, -100]);
                //var point = new L.LatLng(me._map.getBounds().getNorth(), lng);
                //me._tempMarker.setLatLng(new L.LatLng(parseFloat(lat)+0.0025, lng));                
                //me._tempMarker.setLatLng(point);
                //me._map.fire('click', {latlng: point});
            }
        }
        
        if (lat <= south) {
            Core.log('moving south: ' + south);
            me._map.panBy([0, 100]);
        }
    }
    
    if (lng != undefined) {
        if (lng >= east) {
            Core.log('moving right: ' + east);
            me._map.panBy([100, 0]);
        }
        
        if (lng <= west) {
            Core.log('moving left: ' + west);
            me._map.panBy([-100, 0]);
        }
    }
}
*/
MbMap.prototype.createTempPolygon = function(latitude, longitude, radius, points, polygonColor, type) {
console.log('MbMap.prototype.createTempPolygon:'+latitude+':'+longitude+':'+radius+':'+points+':'+polygonColor+':'+type);
    if (points != undefined && typeof(points) == 'object' && points.length > 0) {
        var me = this,
            style = {},
            polygonColor = polygonColor || me._defaultPolygonColor
        ;
               
        if (me._tempPolygon != undefined) {
            me._map.removeLayer(me._tempPolygon);
            me._tempPolygon = undefined;
        }
        
		style = {
			color: polygonColor, //'#00FF00',
			opacity: 0.5,
			weight: 1,
			fillColor: polygonColor, //'#00FF00',
			fillOpacity: 0.1,
			clickable: false			
		};        
       
        me._tempPolygon = new L.Polygon(points, style);
        
        me._map.addLayer(me._tempPolygon);
        
        if (points.length > 1) {
            // me._map.fitBounds(me._tempPolygon.getBounds()); // for creating circles and squares, adjust viewport to fit shape
        } else {
            // me._map.setZoom(14);                            // for creating polygons, set zoom level to 14
        }
                            
        me._tempPolygon.latitude = latitude;
        me._tempPolygon.longitude = longitude;
        me._tempPolygon.radius = radius; 
    }    
}

MbMap.prototype.updateTempMarker = function(params) {
    var me = this;
    if (me._tempMarker != undefined) {
        if (params.latitude != undefined && params.longitude != undefined) {
            var point = new L.LatLng(params.latitude, params.longitude);
            me._tempMarker.setLatLng(point);
            me._tempMarker.latitude = params.latitude;
            me._tempMarker.longitude = params.longitude;
        }
        
        if (params.title != undefined) {
            var icon = L.divIcon({
        		iconSize:	me._iconSize,
        		iconAnchor: me._iconAnchor,    
                className: 'mapbox_marker_icon',     
            	html: '<div class="mapbox_marker_label hidden">'+params.title+'</div>'
            });
                        
            me._tempMarker.setIcon(icon);
        }
    }    
// console.log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> MbMap.prototype.updateTempMarker:point:'+point);
}

MbMap.prototype.updateTempPolygon = function(latitude, longitude, radius, points, type) {
    var me = this;
    if (this._tempPolygon != undefined) {
        if (points != undefined && typeof(points) == 'object' && points.length > 0) {
            switch(type) {
                case 'circle':
                    this._tempPolygon.latitude = latitude;
                    this._tempPolygon.longitude = longitude;
                    this._tempPolygon.radius = radius;
                    break;
                case 'square':
                    this._tempPolygon.latitude = latitude;
                    this._tempPolygon.longitude = longitude;
                    this._tempPolygon.radius = radius;
                    break;
                case 'rectangle':
                    var bound = new L.LatLngBounds(points);
                    me.updateTempMarker({latitude: bound.getCenter().lat, longitude: bound.getCenter().lng});
                    points = [
                        new L.LatLng(points[0].lat, points[0].lng),
                        new L.LatLng(points[0].lat, points[1].lng),
                        new L.LatLng(points[1].lat, points[1].lng),
                        new L.LatLng(points[1].lat, points[0].lng)//,
                        //new L.LatLng(points[0].lat, points[0].lng)
                    ];
                    break;
                case 'polygon':
// console.log('!!!!!!!!!!!!!!!!!!!!!!!!! MbMap.prototype.updateTempPolygon:'+latitude+':'+longitude+':'+radius+':'+points+':'+type);
                    // var bound = new L.LatLngBounds(points);                   
                    // me.updateTempMarker({latitude: bound.getCenter().lat, longitude: bound.getCenter().lng});                    
                    break;    
            }

            this._tempPolygon.setLatLngs(points);

            switch(type) {
                case 'polygon':
                    var pointCounter=0;
// console.log('!!!!!!!!!!!!!!!!!!!!!!!!! MbMap.prototype.updateTempPolygon:'+latitude+':'+longitude+':'+radius+':'+points+':'+type);
                    $.each($('.leaflet-marker-icon'), function(k,v){
                        $(this).removeClass('mapbox_temp_landmark_icon');
                        $(this).removeClass('mapbox_temp_landmark_icon_active');
                        if(k>0){
                            $(this).addClass('mapbox_temp_landmark_icon');
                        } else {
                            $(this).addClass('mapbox_temp_landmark_icon_active');
                        }
                    });
// console.log('!!!!!!!!!!!!!!!!!!!!!!!!! MbMap.prototype.updateTempPolygon:'+latitude+':'+longitude+':'+radius+':'+points+':'+type);
                    break;    
            }
        }
    }
}

MbMap.prototype.removeTempMarker = function() {
    var me = this;
    if (me._tempMarker != undefined) {
        me._map.removeLayer(me._tempMarker);        
        me._tempMarker = undefined;    
    }
    
    // clear temp markers representing temp landmark points
    if (me._tempMarkerArray != undefined) {
        for (var i=0; i<me._tempMarkerArray.length; i++) {
            me._map.removeLayer(me._tempMarkerArray[i]);
            if (i == (me._tempMarkerArray.length - 1)) {
                me._tempMarkerArray = [];
            }
        }
    }    
}

MbMap.prototype.removeTempPolygon = function() {
console.log('MbMap.prototype.removeTempPolygon');
    var me = this;
    if (me._tempPolygon != undefined) {
        me._map.removeLayer(me._tempPolygon);
        me._tempPolygon = undefined;    
    }    
}

MbMap.prototype.addMapClickListener = function(callBack) {
    var me = this,
        data = {};
    		
    if (me._mapClickListener != undefined) {
    	me._map.off('click');
    }
    
    // fix for a mapbox/leaft api bug where a 'click' event is triggered after a 'dragend' 
    me._preventClick = false;
    
    me._mapClickListener = me._map.on('click', function(event) {
        data.result = event;
        data.latitude = event.latlng.lat;
        data.longitude = event.latlng.lng;
        if (callBack != undefined && typeof(callBack) == 'function' && me._preventClick == false) {
            callBack(data);
        }
    });   
}

MbMap.prototype.removeMapClickListener = function() {
    if (this._mapClickListener != undefined) {
       this._map.off('click');
       this._mapClickListener = undefined;
    }
}

MbMap.prototype.convertToGLatLng = function(latitude, longitude) {
    if (latitude != undefined && latitude != '' && longitude != undefined && longitude != '') {
        return (new L.LatLng(latitude, longitude));
    }    
}

MbMap.prototype.getLat = function(latlng) {
    if (latlng != undefined && (typeof latlng == 'object') && (latlng.lat != undefined)) {
        return latlng.lat;
    }
}

MbMap.prototype.getLng = function(latlng) {
    if (latlng != undefined && (typeof latlng == 'object') && (latlng.lng != undefined)) {
        return latlng.lng;
    }    
}

MbMap.prototype.hasTempMarkers = function() {
    return this._tempMarkerLayer.getLayers().length;
}

MbMap.prototype.clickMap = function(lat, lng) {
    if (lat != '' && lng != '') {
        this._map.fire('click', {
            latlng: {
                lat: lat,
                lng: lng
            }
        });
    }
}

MbMap.prototype.hideMarker = function(id) {
// console.log('********************************************** MbMap.prototype.mideMarker:'+id);
    if (id != '' && id != undefined) {
        var markerId = this._markerMapIndex[id],
            marker = {}
        ;
        
        if (markerId != undefined && markerId != '') {
            marker = this._markerLayer.getLayer(markerId)

            if (marker != undefined) {

                this._hiddenMarker = marker;
                
                if (marker.polygon != undefined) {
                    //this._map.removeLayer(marker.polygon);
                    this._polygonLayer.removeLayer(marker.polygon._leaflet_id);
                }

                this._markerLayer.removeLayer(markerId);//.addLayer(marker);
            }
        }
    }    
}

MbMap.prototype.showMarker = function(id, callback) {
// console.log('********************************************** MbMap.prototype.showMarker:'+id+':'+callback);
    if (id != '' && id != undefined) {
        var markerId = this._markerMapIndex[id],
            marker = {}
        ;
        
        if (markerId != undefined && markerId != '') {
            if (this._hiddenMarker != undefined && this._hiddenMarker._leaflet_id == markerId) {
                
                marker = this._hiddenMarker;
                this._markerLayer.addLayer(marker);
                
                if (marker.polygon != undefined) {
                    //this._map.addLayer(marker.polygon);
                    this._polygonLayer.addLayer(marker.polygon);
                }
                
                if (callback != undefined && typeof callback == 'function') {
                    callback();
                }
            }
        }
    }    
}

MbMap.prototype.addPointToRectangle = function(latitude, longitude, callbacks) {
    var me = this;
    if (me._tempMarkerArray == undefined || me._tempMarkerArray.length < 2) {
        if (me._tempMarkerArray == undefined) {
            me._tempMarkerArray = [];
        }

        var point = new L.LatLng(latitude, longitude);
        
        var icon = L.divIcon({
    		iconSize:	me._tempIconSize,
    		iconAnchor: me._tempIconAnchor,   
            className: 'mapbox_temp_landmark_icon'//'mapbox_marker_icon'
        });
      
        var marker = new L.Marker(point, {icon: icon, draggable: true});
        
        me._tempMarkerArray.push(marker);
        
        me._map.addLayer(marker);            
        
        marker.on('drag', function(event) {
            var points = [];
            for (var i = 0; i < me._tempMarkerArray.length; i++) {
                points.push(me._tempMarkerArray[i].getLatLng());
                if (i == (me._tempMarkerArray.length - 1)) {
                    me.updateTempPolygon(null, null, null, points, 'rectangle');
                    if (callbacks != undefined && callbacks.drag != undefined && (typeof callbacks.drag == 'function')) {
                        callbacks.drag({latitude: me._tempMarker.latitude, longitude: me._tempMarker.longitude});
                    }  
                }
            }
            
            //me.updateTempPolygon(null, null, null, me._tempMarkerArray, 'rectangle'); 
        });
        
        if (callbacks != undefined && callbacks.dragend != undefined && (typeof callbacks.dragend == 'function')) {
            marker.on('dragend', function() {
                callbacks.dragend({latitude: me._tempMarker.latitude, longitude: me._tempMarker.longitude});    
            });
        }

        var points = [];
        for (var i=0; i<me._tempMarkerArray.length; i++) {
            points.push(me._tempMarkerArray[i].getLatLng()); 
            if (i == (me._tempMarkerArray.length - 1)) {
                me.updateTempPolygon(null, null, null, points, 'rectangle');  
            }    
        }

        //me.updateTempPolygon(null, null, null, me._tempMarkerArray, 'rectangle');
    } else {
        alert('Rectangles are limited to 2 points. Please drag the points to edit the rectangle.');
    } 
}

MbMap.prototype.addPointToPolygon = function(latitude, longitude, callbacks) {
    var me = this;
    if (me._tempMarkerArray == undefined || me._tempMarkerArray.length < 10) {
        if (me._tempMarkerArray == undefined) {
            me._tempMarkerArray = [];
        }

        var point = new L.LatLng(latitude, longitude);
        
        var icon = L.divIcon({
    		iconSize:	me._tempIconSize,
    		iconAnchor: me._tempIconAnchor,    
            className: 'mapbox_temp_landmark_icon'//'mapbox_marker_icon'
        });
      
        var marker = new L.Marker(point, {icon: icon, draggable: true});
        
        me._tempMarkerArray.push(marker);
        
        me._map.addLayer(marker);            
        
        marker.on('drag', function(event) {
// console.log('marker.on(drag) : '+latitude+' / '+longitude);
// console.log('.leaflet-popup-pane');
            if($('.map-bubble').is(':visible')){
                $('.leaflet-popup-pane').hide();
            }
// console.log(event);
            var points = [];       
            for (var i=0; i < me._tempMarkerArray.length; i++) {               
                points.push(me._tempMarkerArray[i].getLatLng());              
                // if (i == (me._tempMarkerArray.length - 1)) {
                //     me.updateTempPolygon(null, null, null, points, 'polygon'); 
                //     if (callbacks != undefined && callbacks.drag != undefined && (typeof callbacks.drag == 'function')) {
                //         callbacks.drag({latitude: latitude, longitude: longitude});
                //     } 
                // }    
            }
            me.updateTempPolygon(null, null, null, points, 'polygon'); 
            
            //me.updateTempPolygon(null, null, null, me._tempMarkerArray, 'polygon'); 
        });
        
        // remove temp polygon markers when clicked
        marker.on('click', function(event) {
console.log('marker.on(click) : '+latitude+' / '+longitude);
// console.log(event);
            me._map.removeLayer(marker);
            me._tempMarkerArray.splice(me._tempMarkerArray.indexOf(marker), 1);
            var points = [];            
            for (var i=0; i < me._tempMarkerArray.length; i++) {
                points.push(me._tempMarkerArray[i].getLatLng());
                // if (i == (me._tempMarkerArray.length - 1)) {
                //     me.updateTempPolygon(null, null, null, points, 'polygon');
                //     if (callbacks != undefined && callbacks.click != undefined && (typeof callbacks.click == 'function')) {
                //         callbacks.click({latitude: me._tempMarker.latitude, longitude: me._tempMarker.longitude});    
                //     }                     
                // }    
            }
            me.updateTempPolygon(null, null, null, points, 'polygon');
            //me.updateTempPolygon(null, null, null, me._tempMarkerArray, 'polygon'); 
            Core.Wizard.Polygon(me);
        });
        
        marker.on('dragend', function() {
console.log('marker.on(dragend)');
            Core.Wizard.Polygon(me);
        });

        var points = [];
        for (var i=0; i < me._tempMarkerArray.length; i++) {
            points.push(me._tempMarkerArray[i].getLatLng()); 
            if (i == (me._tempMarkerArray.length - 1)) {
                me.updateTempPolygon(null, null, null, points, 'polygon');  
            }    
        }

       // me.updateTempPolygon(null, null, null, me._tempMarkerArray, 'polygon');        
    } else {
        alert('Polygons are limited to 10 points. Please drag the points to edit the polygon.');
    } 
}

MbMap.prototype.getTempMarkerArray = function() {
    if (this._tempMarkerArray == undefined) {
        return [];
    } else {
        return this._tempMarkerArray;
    }
}

MbMap.prototype.getTempPolygonPoints = function() {
    var points = [];  

    if (this._tempPolygon != undefined) {
        var markers = this._tempPolygon.getLatLngs();
		// get points
		for (var i=0; i<markers.length; i++) {
			points.push({
				latitude:	markers[i].lat,
				longitude:	markers[i].lng
			});
		}
    }
  
    return points;
}

MbMap.prototype.getTempMarkerPosition = function() {
    var point = {},
        me = this
    ;
    
    if (me._tempMarker != undefined) {
        point = {
            latitude: me._tempMarker.latitude,
            longitude: me._tempMarker.longitude 
        }
    }
    
    return point;
}
