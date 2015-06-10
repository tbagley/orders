/*

    POSITION PLUS GPS JS

    File:       /assets/js/positionplusgps.js

    Version:    1.0

    Author:     Todd Bagley, tbagley@positionplusgps.com

    Purpose:    Js/Jquery Class for embedding in Third Party Websites.

    IMPORTANT:  Please contact PositionPlusGPS to request a Software Development Kit (contains: API Keys and a copy of API Documentation).

    API KEYS:   PositionPlus_partner_key = [developers must request from PositionPlusGPS] ;                     
                PositionPlus_subscriber_key = [see documentation for more information] ;                  

    SAMPLE
    FUNCTIONS:  PositionPlusGps.Api.Command('reminder_off',PositionPlus_unit_id);
                PositionPlusGps.Api.Command('reminder_on',PositionPlus_unit_id);
                PositionPlusGps.Api.Command('starter_disable',PositionPlus_unit_id);
                PositionPlusGps.Api.Command('starter_enable',PositionPlus_unit_id);
                PositionPlusGps.Api.Frequent_Stops(PositionPlus_unit_id);
                PositionPlusGps.Api.Stops(PositionPlus_unit_id);
                PositionPlusGps.Api.Last_Event(PositionPlus_unit_id);
                PositionPlusGps.Api.Metrics();
                PositionPlusGps.Api.Users();
                PositionPlusGps.Api.Vehicles();
                PositionPlusGps.Api.Vehicles('installed');
                PositionPlusGps.Api.Vehicles('inventory');
                PositionPlusGps.Api.Vehicles('landmark');
                PositionPlusGps.Api.Vehicles('reminderon');
                PositionPlusGps.Api.Vehicles('repossession');
                PositionPlusGps.Api.Vehicles('starterdisabled');
                PositionPlusGps.Api.Verifications(PositionPlus_unit_id);

*/

var PositionPlus_counter = 0;
var PositionPlus_debug = false;
var PositionPlus_partner_key = 0 ;                     
var PositionPlus_subscriber_key = 0 ;
var PositionPlus_unit_id = 0 ;

var PositionPlusGps = {};

jQuery.extend(PositionPlusGps, {

    Ajax: function (buffer) {

        $.ajax({
            url: 'https://api.positionplusgps.com/api/ajax',
            type: 'POST',
            dataType: 'json',
            data: {
                partner_key: PositionPlus_partner_key,
                subscriber_key: PositionPlus_subscriber_key,
                command: buffer['command'],
                command_type: buffer['command_type'],
                metric: buffer['metric'],
                unit_id: buffer['unit_id']
            },
            success: function(responseData) {
                if((responseData)&&(PositionPlus_debug)){
                    $.each(responseData, function( k1, v1 ) {
                        console.log('PositionPlusGPS : '+k1);
                        $.each(v1, function( k2, v2 ) {
                            $.each(v2, function( k3, v3 ) {
                                console.log(k3+'='+v3);
                            });
                            console.log(' ');
                            console.log(' ');
                        });
                        console.log(' ');
                    });
                }
                if(responseData.recheck){
                    if(PositionPlus_counter<10){
                        PositionPlusGps.Api.Check(buffer['command_type'],buffer['unit_id']);
                    } else {
                        PositionPlusGpsOutput.Action('Process Timed Out');
                    }
                    $.each(responseData, function( k1, v1 ) {
                        $.each(v1, function( k2, v2 ) {
                            $.each(v2, function( k3, v3 ) {
                                console.log(buffer['command']+':'+buffer['command_type']+':'+v3+' ('+PositionPlus_counter+')');
                            });
                        });
                    });
                    console.log('PositionPlusGPS: Success: Check initiated...') ;
                } else if(buffer['command']=='check') {
                    $.each(responseData, function( k1, v1 ) {
                        $.each(v1, function( k2, v2 ) {
                            $.each(v2, function( k3, v3 ) {
                                console.log(buffer['command']+':'+buffer['command_type']+':'+v3+' ('+PositionPlus_counter+'/10)');
                            });
                        });
                    });
                    console.log('PositionPlusGPS: Success: '+buffer['command_type']) ;
                    PositionPlusGpsOutput.Action(responseData);
                } else if(buffer['command']=='command') {
                    $.each(responseData, function( k1, v1 ) {
                        $.each(v1, function( k2, v2 ) {
                            $.each(v2, function( k3, v3 ) {
                                console.log(buffer['command']+':'+buffer['command_type']+':'+v3);
                            });
                        });
                    });
                    PositionPlusGpsOutput.Action(responseData);
                } else {
                    PositionPlusGpsOutput.Action(responseData);
                    return responseData;
                }
            }
        });

    },

    Api: {

        Check: function (command_type,unit_id) {
            PositionPlus_counter++;
            var buffer = [] ;
            buffer['command'] = 'check' ;
            buffer['command_type'] = command_type ;
            buffer['unit_id'] = unit_id ;
            buffer['serialnumber'] = unit_id ;
            return PositionPlusGps.Ajax(buffer);
        },

        Command: function (command_type,unit_id) {
            PositionPlus_counter = 0;
            var buffer = [] ;
            buffer['command'] = 'command' ;
            buffer['command_type'] = command_type ;
            buffer['unit_id'] = unit_id ;
            buffer['serialnumber'] = unit_id ;
            return PositionPlusGps.Ajax(buffer);
        },

        Frequent_Stops: function (unit_id) {
            var buffer = [] ;
            buffer['command'] = 'frequent_stops' ;
            buffer['unit_id'] = unit_id ;
            buffer['serialnumber'] = unit_id ;
            return PositionPlusGps.Ajax(buffer);
        },

        Last_Event: function (unit_id) {
            var buffer = [] ;
            buffer['command'] = 'last_event' ;
            buffer['unit_id'] = unit_id ;
            buffer['serialnumber'] = unit_id ;
            return PositionPlusGps.Ajax(buffer);
        },

        Metrics: function () {
            var buffer = [] ;
            buffer['command'] = 'metrics' ;
            return PositionPlusGps.Ajax(buffer);
        },

        Stops: function (unit_id) {
            var buffer = [] ;
            buffer['command'] = 'stops' ;
            buffer['unit_id'] = unit_id ;
            buffer['serialnumber'] = unit_id ;
            return PositionPlusGps.Ajax(buffer);
        },

        Update: function (params) {
            var buffer = [] ;
            buffer['command'] = 'update' ;
            buffer['installdate'] = params['installdate'] ;
            buffer['installer'] = params['installer'] ;
            buffer['unitname'] = params['unitname'] ;
            buffer['vin'] = params['vin'] ;
            buffer['unit_id'] = unit_id ;
            buffer['serialnumber'] = unit_id ;
            return PositionPlusGps.Ajax(buffer);
        },

        Users: function () {
            var buffer = [] ;
            buffer['command'] = 'users' ;
            return PositionPlusGps.Ajax(buffer);
        },

        Vehicles: function (metric) {
            var buffer = [] ;
            buffer['command'] = 'vehicles' ;
            buffer['metric'] = metric ;
            return PositionPlusGps.Ajax(buffer);
        },

        Verifiction_Add: function (params) {
            var buffer = [] ;
            buffer['command'] = 'verification_add' ;
            buffer['city'] = params['city'] ;
            buffer['country'] = params['country'] ;
            buffer['latitude'] = params['latitude'] ;
            buffer['longitude'] = params['longitude'] ;
            buffer['radius'] = params['radius'] ;
            buffer['streetaddress'] = params['streetaddress'] ;
            buffer['territoryname'] = params['territoryname'] ;
            buffer['zipcode'] = params['zipcode'] ;
            buffer['unit_id'] = unit_id ;
            buffer['serialnumber'] = unit_id ;
            return PositionPlusGps.Ajax(buffer);
        },

        Verifications: function (unit_id) {
            var buffer = [] ;
            buffer['command'] = 'verifications' ;
            buffer['unit_id'] = unit_id ;
            buffer['serialnumber'] = unit_id ;
            return PositionPlusGps.Ajax(buffer);
        }

    }

});
