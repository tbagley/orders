/*

    POSITION PLUS GPS JS

    File:       /assets/js/positionplusgps.js

    Version:    1.0

    Author:     Todd Bagley, tbagley@positionplusgps.com

    Purpose:    Provision API Development Partners with working js/jquery code sample demonstrating how to set API Key Variables and leverage PositionPlusGPS API functions. 

*/

$(document).ready(function() {
    PositionPlus_debug = false;
    PositionPlus_partner_key = 'apikey1234567890' ;                     
    PositionPlus_subscriber_key = 'apikey1234567890' ;                  
    PositionPlus_unit_id = '30432';
    // PositionPlusGps.Api.Command('reminder_off',PositionPlus_unit_id);
    // PositionPlusGps.Api.Command('reminder_on',PositionPlus_unit_id);
    // PositionPlusGps.Api.Command('starter_disable',PositionPlus_unit_id);
    // PositionPlusGps.Api.Command('starter_enable',PositionPlus_unit_id);
    // PositionPlusGps.Api.Frequent_Stops(PositionPlus_unit_id);
    // PositionPlusGps.Api.Stops(PositionPlus_unit_id);
    // PositionPlusGps.Api.Last_Event(PositionPlus_unit_id);
    PositionPlusGps.Api.Metrics();
    // PositionPlusGps.Api.Users();
    // PositionPlusGps.Api.Vehicles();
    // PositionPlusGps.Api.Vehicles('installed');
    // PositionPlusGps.Api.Vehicles('inventory');
    // PositionPlusGps.Api.Vehicles('landmark');
    // PositionPlusGps.Api.Vehicles('reminderon');
    // PositionPlusGps.Api.Vehicles('repossession');
    // PositionPlusGps.Api.Vehicles('starterdisabled');
    // PositionPlusGps.Api.Verifications(PositionPlus_unit_id);
});

var PositionPlusGpsOutput = {};

jQuery.extend(PositionPlusGpsOutput, {

    Action: function (responseData) {

        console.log('PositionPlusGpsOutput.Action');
        console.log(responseData);

        //
        //
        //
        //
        //
        // developer's code goes here
        //
        $('#api_out').empty();
        $.each(responseData, function( k1, v1 ) {
            $('#api_out').append('<hr><b>'+k1+'</b><p>');
            $.each(v1, function( k2, v2 ) {
                $.each(v2, function( k3, v3 ) {
                    $('#api_out').append(k3+'='+v3+'<br>');
                });
                $('#api_out').append('&nbsp;<br>');
            });
            $('#api_out').append('&nbsp;');
        });

    }

});

