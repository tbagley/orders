/*

    Alert JS

    File:       /assets/js/alert.js
    Author:     Tom Leach
*/

$(document).ready(function() {

    Alert.isLoaded();

    /**
     *
     * Common Functionality for Alerts Pages
     *
     */
    Core.Editable.disable();

    /**
     *
     * Page Specific Functionality
     *
     */
    switch (Core.Environment.context()) {

        /* LIST */
        case 'alert/list':
            Alert.List.DataTables.init();
            Alert.List.initModal();
            Alert.Common.SecondaryPanel.initAlertSearch();
            Alert.List.initAddAlert();
            break;

        /* HISTORY */
        case 'alert/history':
            Alert.History.DataTables.init();
            Alert.List.initModal();
            Alert.List.initAddAlert();
            Alert.Common.SecondaryPanel.initAlertSearch();
            Alert.History.initExportAlertHistory();
            break;
    }



});

var Alert = {};
var shouldUpdate = false;

jQuery.extend(Alert, {

    isLoaded: function() {

        Core.log('Alert JS Loaded');
    },

    List: {

        DataTables: {

            init: function() {

                Alert.List.DataTables.alertList = Core.DataTable.init('alert-list-table', 20, {
                    'oLanguage': {
                        'sInfo': 'Showing _START_ to _END_ of _TOTAL_ Alerts'
                    },
                    //"aLengthMenu": [[20, 50, 100]],
                    //"sScrollY": "400px",
                    //"bScrollCollapse": true,
                    'bServerSide': true,
                    'sAjaxSource':   '/ajax/alert/getFilteredAlerts',
                    'aoColumns': [
                        { 'mDataProp': 'alertname' },
                        { 'mDataProp': 'alerttypename' },
                        { 'mDataProp': 'unitname' },
                        { 'mDataProp': 'contactname' },
                        { 'mDataProp': 'uniteventdate' }
                    ],
                    'aoColumnDefs': [
                        { 'sClass': 'col-alert-name no-wrap',           'aTargets': [0] },
                        { 'sClass': 'col-alert-type no-wrap',           'aTargets': [1] },
                        { 'sClass': 'col-alert-vehicles no-wrap',       'aTargets': [2] },
                        { 'sClass': 'col-alert-contacts no-wrap',       'aTargets': [3] },
                        { 'sClass': 'col-alert-last-triggered no-wrap', 'aTargets': [4] }
                    ],
                    'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
                        // set <a> tag name and add data-landmark-id attribute to tr
                        $('td:eq(0)', nRow).html('<a href="#">'+aData.alertname+'</a>');
                        $(nRow).data('alertId', aData.alert_id);

                        // if the alert was set for a unit group, display the unit group name
                        if (aData.unitgroup_id != '' && aData.unitgroup_id != null && aData.unitgroupname != '' && aData.unitgroupname != null) {
                            $('td:eq(2)', nRow).text(aData.unitgroupname);
                        }

                        // if the alert was set for a contact group, display the contact group name
                        if (aData.contactgroup_id != '' && aData.contactgroup_id != null && aData.contactgroupname != '' && aData.contactgroupname != null) {
                            $('td:eq(3)', nRow).text(aData.contactgroupname);
                        }

                        return nRow;
                    },
                    'fnServerParams': function (aoData) {

                        var vehiclegroup_id = $('#sidebar-vehicle-single').val().trim();
                        var alert_type      = $('#select-alert-type-filter').val().trim();
                        var contactgroup_id = $('#select-contact-group-filter').val().trim();
                        var search_string   = '';
                        var filter_type     = 'group_filter';

                        var searchAlertString = $('#text-alert-search').val().trim();
                        if (typeof(searchAlertString) != 'undefined' && searchAlertString != '')
                        {
                            search_string   = searchAlertString;
                            vehiclegroup_id = 'All';
                            contactgroup_id = 'All';
                            alert_type      = 'All';
                            filter_type     = 'string_search';

                        }

                        aoData.push({name: 'vehiclegroup_id', value: vehiclegroup_id});
                        aoData.push({name: 'contactgroup_id', value: contactgroup_id});
                        aoData.push({name: 'search_string', value: search_string});
                        aoData.push({name: 'alert_type', value: alert_type});
                        aoData.push({name: 'filter_type', value: filter_type});
                    }
                });

            }
        },

        initModal: function() {

            // listener for when an alert name is clicked in the Alerts datatable
            $(document).on('click', '.col-alert-name a', function() {

            	shouldUpdate = false;

                var $self = $(this),
                    $trNode = $self.closest('tr'),
                    alertId = $trNode.attr('id').split('-')[2],
                    $modal = $('#modal-alert-list')
                ;

                if (alertId != undefined) {
                    $.ajax({
                        url: '/ajax/alert/getAlertById',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            alert_id: alertId
                        },
                        success: function(responseData) {
                            if (responseData.code === 0) {
                                var alertData = responseData.data.alert;
                                if (! $.isEmptyObject(alertData)) {
                                    Core.Dialog.launch($modal.selector, 'Test', {
                                        width: '700px'
                                    },
                                    {
                                        hidden: function() {
                                            Alert.List.DataTables.alertList.fnStandingRedraw();
                                        },
                                        show: function() {

                                            $modal.find('.modal-title').text('').hide();

                                            // reset More Options
                                            $('#alert-more-options-toggle').find('small').text('Show More Options');
                                            $('#alert-more-options').hide();

                                        },
                                        shown: function() {

                                            $modal.find('.modal-title').text($self.text()).fadeIn(100);
                                            $('#detail-panel').find('.hook-editable-keys').data('alertPk', alertId);

                                            // set alert name
                                            Core.Editable.setValue($('#alert-name'), alertData.alertname);

                                            var alertTypeId = alertData.alerttype_id;
                                            var alertType = '';

                                            switch (alertTypeId) {
                                                /*case '1':
                                                    alertType = 'boundary';
                                                    break;*/
                                                case '2':
                                                    alertType = 'extended-stop';
                                                    break;
                                                case '3':
                                                    alertType = 'landmark';
                                                    break;
                                                case '4':
                                                    alertType = 'low-voltage';
                                                    break;
                                                case '5':
                                                    alertType = 'moving';
                                                    break;
                                                case '6':
                                                    alertType = 'non-reporting';
                                                    break;
                                                case '7':
                                                    alertType = 'over-speed';
                                                    break;
                                                case '8':
                                                    alertType = 'tow';
                                                    break;
                                            }

                                            // indicator for if user has permission to edit alerts
                                            var editable = $('#alert-list-table').data('editable');

                                            // disable date/time range options
                                            $('#alert-days').editable('disable').data('disabled', true);
                                            $('#alert-hours').siblings().filter(':button').addClass('disabled');
                                            $('#alert-hours-start').editable('disable').data('disabled', true);
                                            $('#alert-hours-start').siblings().filter(':button').addClass('disabled');
                                            $('#alert-hours-end').editable('disable').data('disabled', true);
                                            $('#alert-hours-end').siblings().filter(':button').addClass('disabled');

                                            // if the alert type is not 'low-voltage', 'tow', 'moving', trigger a click on the corresponding alert type in the dropdown
                                            if (alertType != 'low-voltage' && alertType != 'tow' && alertType != 'moving') {

                                                $('#alert-type').siblings('ul').eq(0).find('a').filter('[data-value="'+alertTypeId+'"]').trigger('click');

                                                // endable date/time range if alert not these types
                                                if (alertType != 'over-speed' && alertType != 'non-reporting' && alertType != 'extended-stop' && editable == 1) {
                                                    $('#alert-days').editable('enable').data('disabled', false);
                                                    $('#alert-hours').siblings().filter(':button').removeClass('disabled');
                                                    $('#alert-hours-start').editable('enable').data('disabled', false);
                                                    $('#alert-hours-start').siblings().filter(':button').removeClass('disabled');
                                                    $('#alert-hours-end').editable('enable').data('disabled', false);
                                                    $('#alert-hours-end').siblings().filter(':button').removeClass('disabled');
                                                }
                                            } else {    // else if the alert type is 'low-voltage', don't trigger a click b/c it's set up to automatically save low-voltage alerts on click
                                                if (alertType == 'low-voltage') {
                                                    $('#alert-type').val(alertTypeId).text('Low Vehicle Voltage');
                                                }
                                                if (alertType == 'tow') {
                                                    $('#alert-type').val(alertTypeId).text('Tow Alert');
                                                }
                                                if (alertType == 'moving' && editable == 1) {
                                                    $('#alert-type').val(alertTypeId).text('Moving');
                                                    // enable date/time range options
                                                    $('#alert-days').editable('enable').data('disabled', false);
                                                    $('#alert-hours').siblings().filter(':button').removeClass('disabled');
                                                    $('#alert-hours-start').editable('enable').data('disabled', false);
                                                    $('#alert-hours-start').siblings().filter(':button').removeClass('disabled');
                                                    $('#alert-hours-end').editable('enable').data('disabled', false);
                                                    $('#alert-hours-end').siblings().filter(':button').removeClass('disabled');
                                                }

                                                $('.dropdown-toggle-panel-alert-type-landmark-mode, .dropdown-toggle-panel-alert-type-boundary-mode, .dropdown-toggle-panel-alert-type-extended-stop, .dropdown-toggle-panel-alert-type-over-speed, .dropdown-toggle-panel-alert-type-non-reporting').hide();

                                            }

                                            // set alert landmark/landmark group
                                            if ((alertType == 'landmark' || alertType == 'boundary') && ((alertData.territory_id != null && parseInt(alertData.territory_id) > 0) || (alertData.territorygroup_id != null && parseInt(alertData.territorygroup_id) > 0))) {
                                                var mode = (alertData.territory_id != null) ? 'single' : 'group',
                                                    territory_id = (alertData.territory_id != null) ? alertData.territory_id : alertData.territorygroup_id
                                                ;

                                                // select the territory mode ('single' or 'group')
                                                $('#alert-'+alertType+'-mode').siblings('ul').eq(0).find('a').filter('[data-value="'+mode+'"]').trigger('click');

                                                // select the assigned landmark/landmark group
                                                Core.Editable.setValue($('#alert-'+alertType+'-'+mode), territory_id);
                                            }
                                            else
                                            {
	                                            $('#alert-'+alertType+'-mode').siblings('ul').eq(0).find('a').filter('[data-value="all"]').trigger('click');
	                                            Core.Editable.setValue($('#alert-'+alertType+'-'+mode), '');
                                            }

                                            // set alert vehicle/vehicle group
                                            if ((alertData.unit_id != null && parseInt(alertData.unit_id) > 0) || (alertData.unitgroup_id != null && parseInt(alertData.unitgroup_id) > 0)) {
                                                var mode = (alertData.unit_id != null) ? 'single' : 'group',
                                                    unitId = (alertData.unit_id != null) ? alertData.unit_id : alertData.unitgroup_id
                                                ;

                                                shouldUpdate = true;
                                                $('#alert-vehicle-mode').siblings('ul').eq(0).find('a').filter('[data-value="'+mode+'"]').trigger('click');

                                                Core.Editable.setValue($('#alert-vehicle-'+mode), unitId);
                                            }
                                            else
                                            {
                                            	$('#alert-vehicle-mode').siblings('ul').eq(0).find('a').filter('[data-value="all"]').trigger('click');
                                                Core.Editable.setValue($('#alert-vehicle-'+mode), '');
                                            }

                                            // set trigger
                                            if (alertData.alerttrigger != null && alertData.alerttrigger != '') {
                                                switch (alertType) {
                                                    case 'extended-stop':
                                                        Core.Editable.setValue($('#alert-extended-stop-duration'), alertData.alerttrigger);
                                                        break;
                                                    case 'non-reporting':
                                                        Core.Editable.setValue($('#alert-non-reporting-duration'), alertData.alerttrigger);
                                                        break;
                                                    case 'over-speed':
                                                        Core.Editable.setValue($('#alert-over-speed'), alertData.alerttrigger);
                                                        break;
                                                    case 'low-voltage':
                                                        // for now, users cannot select voltage threshold
                                                        //Core.Editable.setValue($('#alert-voltage'), alertData.alerttrigger);
                                                        break;
                                                    case 'tow':
                                                        // for now, users cannot select tow

                                                        break;
                                                    case 'moving':
                                                        // for now, users cannot select moving

                                                        break;
                                                    case 'landmark':
                                                        shouldUpdate = false
                                                        $('#alert-landmark-trigger').siblings('ul').eq(0).find('a').filter('[data-value="'+alertData.alerttrigger+'"]').trigger('click');
                                                        shouldUpdate = true
                                                        break;
                                                }
                                            }

                                            // set days
                                            var day = (alertData.day != null && alertData.day != '') ? alertData.day : '';
                                            Core.Editable.setValue($('#alert-days'), day);

                                            // set hours type
                                            var hour = (alertData.time != null && alertData.time != '') ? alertData.time : '';
                                            if (hour !== '') {
                                                var hourText = displayAlertRangePanel = 'none';
                                                if (hour == 'range') {
                                                    hourText = 'In Range';
                                                    displayAlertRangePanel = 'block';

                                                    $('#detail-panel').find('.hook-editable-keys').data('hourStart', alertData.starthour);
                                                    $('#detail-panel').find('.hook-editable-keys').data('hourEnd', alertData.endhour);

                                                    var startHour = (alertData.starthour != null && alertData.starthour != '') ? alertData.starthour : '',
                                                        endHour = (alertData.endhour != null && alertData.endhour != '') ? alertData.endhour : ''
                                                    ;

                                                    Core.Editable.setValue($('#alert-hours-start'), startHour);
                                                    Core.Editable.setValue($('#alert-hours-end'), endHour);
                                                } else {
                                                    hourText = 'All';
                                                }

                                                //$('#alert-hours').siblings('ul').eq(0).find('a').filter('[data-value="'+hour+'"]').trigger('click');
                                                $('#alert-hours').val(hour).text(hourText);
                                                $('.toggle-panel-alert-range').css('display', displayAlertRangePanel);
                                            }

                                            // set contacts
                                            var contactMode = '',
                                                contactId = '',
                                                method = ''
                                            ;

                                            if (alertData.alert_contact_id != null && parseInt(alertData.alert_contact_id) > 0) {
                                                contactMode = 'single';
                                                contactId = alertData.alert_contact_id;
                                                method = alertData.alert_contact_method;
                                            } else if (alertData.contactgroup_id != null && parseInt(alertData.contactgroup_id) > 0) {
                                                contactMode = 'group';
                                                contactId = alertData.contactgroup_id;
                                            } else {
                                                contactMode = 'reportonly';
                                                contactId = 0;
                                            }

                                            $('#alert-contact-mode').siblings('ul').eq(0).find('a').filter('[data-value="'+contactMode+'"]').trigger('click');
                                            Core.Editable.setValue($('#alert-contact-'+contactMode), contactId);
                                            Core.Editable.setValue($('#alert-contact-method'), method);
                                        }
                                    });
                                }
                            } else {
                                if (! $.isEmptyObject(responseData.validaton_errors)) {
                                    //	display validation errors
                                }
                            }
                            /*
                            if (! $.isEmptyObject(responseData.message)) {
                                Core.SystemMessage.show(responseData.message, responseData.code);
                            }
                            */
                        }
                    });
                }
            });

            // More Options
            $('#alert-more-options-toggle').on('click', function() {

                var $self    = $(this),
                    $selfText = $self.find('small'),
                    selfTextValue = $selfText.text()
                ;

                if (selfTextValue == 'Show More Options') {
                    $selfText.text('Show Less Options')
                } else if (selfTextValue == 'Show Less Options') {
                    $selfText.text('Show More Options')
                }

                $('#alert-more-options').slideToggle();
            });

            // Delete alert
            $(document).on('click', '#popover-alert-delete-confirm', function() {
                var $modal = $('#modal-alert-list'),
                    alertId = $('#detail-panel').find('.hook-editable-keys').data('alertPk')
                ;

                if (alertId != undefined && alertId != '') {
                    $.ajax({
                        url: '/ajax/alert/deleteAlert',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            alert_id: alertId
                        },
                        success: function(responseData) {
                            if (responseData.code === 0) {
                                // close 'Delete Alert' popover
                                $('#popover-alert-delete-cancel').trigger('click');

                                // close 'Edit Alert' modal
                                $modal.find('.modal-footer button').trigger('click');

                                // update alert table
                                Alert.List.DataTables.alertList.fnStandingRedraw();

                            } else {
                                if (! $.isEmptyObject(responseData.validaton_errors)) {
                                    //	display validation errors
                                }
                            }

                            if (! $.isEmptyObject(responseData.message)) {
                                Core.SystemMessage.show(responseData.message, responseData.code);
                            }
                        }
                    });
                }
            });

            // Listener for updating 'all' hours or 'low-voltage' alert type (simulate inline editing)
            $(document).on('Core.DropdownButtonChange', '#alert-hours, #alert-type', function() {
                var $self   = $(this),
                    value   = $self.val(),
                    id      = $self.prop('id'),
                    alertId = $('#detail-panel').find('.hook-editable-keys').data('alertPk'),
                    data    = {},
                    editable = $('#alert-list-table').data('editable') // indicator for if user has permission to edit alerts
                ;

                data = {
                    "primary_keys" :
                        {
                            "alertPk" : alertId
                        },
                        "id" : id,
                        "value" : value
                };

                if (id == 'alert-type' && editable == 1) {
                    //enable date/time option
                    $('#alert-days').editable('enable').data('disabled', false);
                    $('#alert-hours').siblings().filter(':button').removeClass('disabled');
                    $('#alert-hours-start').editable('enable').data('disabled', false);
                    $('#alert-hours-start').siblings().filter(':button').removeClass('disabled');
                    $('#alert-hours-end').editable('enable').data('disabled', false);
                    $('#alert-hours-end').siblings().filter(':button').removeClass('disabled');

                    if (value == 4 || value == 7 || value == 6 || value == 8 || value == 2) {
                        // reset the day and hour to be all
                        $('#alert-days').val('all').text('All');
                        $('#alert-hours').val('all').text('All');
                        $('.toggle-panel-alert-range').css('display', 'none');
                        // disable date/time range options
                        $('#alert-days').editable('disable').data('disabled', true);
                        $('#alert-hours').siblings().filter(':button').addClass('disabled');
                        $('#alert-hours-start').editable('disable').data('disabled', true);
                        $('#alert-hours-start').siblings().filter(':button').addClass('disabled');
                        $('#alert-hours-end').editable('disable').data('disabled', true);
                        $('#alert-hours-end').siblings().filter(':button').addClass('disabled');
                    }
                }

                if ((id == 'alert-hours' && value == 'all') || (id == 'alert-type' && value == 4) || (id == 'alert-type' && value == 8) || (id == 'alert-type' && value == 5)) {
                    $.ajax({
                        url: '/ajax/alert/updateAlertInfo',
                        type: 'POST',
                        dataType: 'json',
                        data: data,
                        success: function(responseData) {
                            if (responseData.code === 0) {

                                // update alert table
                                //Alert.List.DataTables.alertList.fnStandingRedraw();
                                // reset start and end hour
                                if (id == 'alert-hours' && value == 'all') {
                                    $('#detail-panel').find('.hook-editable-keys').data('hourStart', 0);
                                    $('#detail-panel').find('.hook-editable-keys').data('hourStart', 0);
                                    Core.Editable.setValue($('#alert-hours-start'), null);
                                    Core.Editable.setValue($('#alert-hours-end'), null);
                                }

                            } else {
                                if (! $.isEmptyObject(responseData.validaton_errors)) {
                                    //	display validation errors
                                }
                            }

                            if (! $.isEmptyObject(responseData.message)) {
                                Core.SystemMessage.show(responseData.message, responseData.code);
                            }
                        }
                    });
                }
            });

            $(document).on('Core.DropdownButtonChange', '#alert-landmark-trigger', function() {
	            if(shouldUpdate)
	            {
	                $.ajax({
	                    url: '/ajax/alert/updateAlertInfo',
	                    type: 'POST',
	                    dataType: 'json',
	                    data: {
                            "primary_keys" :
                                {
                                    "alertPk" : $('#detail-panel').find('.hook-editable-keys').data('alertPk')
                                },
                                "id" : "alert-landmark-trigger",
                                "value" : $('#alert-landmark-trigger').val()
                        },
	                    success: function(responseData) {
	                        if (! $.isEmptyObject(responseData.message)) {
	                            Core.SystemMessage.show(responseData.message, responseData.code);
	                        }
	                    }
	                });
				}
            });

            $('#alert-type-landmark-all').click(function() {
                var $landmarkMode = $('#alert-landmark-mode'),
                    alertId = $('#detail-panel').find('.hook-editable-keys').data('alertPk'),
                    value = $('#alert-landmark-mode').val(),
                    data = {}
                ;

                data = {
                    "primary_keys" :
                        {
                            "alertPk" : alertId
                        },
                        "id" : "alert-landmark-all",
                        "value" : value
                };

                if(shouldUpdate)
                {
                    // trigger a submit on the landmark mode
                    $.ajax({
                        url: '/ajax/alert/updateAlertInfo',
                        type: 'POST',
                        dataType: 'json',
                        data: data,
                        success: function(responseData) {
                            if (responseData.code === 0) {

                            } else {
                                if (! $.isEmptyObject(responseData.validaton_errors)) {
                                    //	display validation errors
                                }
                            }

                            if (! $.isEmptyObject(responseData.message)) {
                                Core.SystemMessage.show(responseData.message, responseData.code);
                            }
                        }
                    });
                }
            });

            // automatically trigger a submit on the vehicle mode if "all vehicles" was selected
            $('#alert-vehicle-all').click(function() {
                var $vehicleMode = $('#alert-vehicle-mode'),
                    alertId = $('#detail-panel').find('.hook-editable-keys').data('alertPk'),
                    value = $('#alert-vehicle-mode').val(),
                    data = {}
                ;

                data = {
                    "primary_keys" :
                        {
                            "alertPk" : alertId
                        },
                        "id" : "alert-vehicle-all",
                        "value" : value
                };

	            if(shouldUpdate)
	            {
	                // trigger a submit on the vehicle mode
	                $.ajax({
	                    url: '/ajax/alert/updateAlertInfo',
	                    type: 'POST',
	                    dataType: 'json',
	                    data: data,
	                    success: function(responseData) {
	                        if (responseData.code === 0) {

	                        } else {
	                            if (! $.isEmptyObject(responseData.validaton_errors)) {
	                                //	display validation errors
	                            }
	                        }

	                        if (! $.isEmptyObject(responseData.message)) {
	                            Core.SystemMessage.show(responseData.message, responseData.code);
	                        }
	                    }
	                });
				}
				shouldUpdate=true;
            });

            // automatically trigger a submit on the contact method if single contact has been updated
            $(document).on('Core.FormElementChanged', '#alert-contact-single', function() {
                var $contactMethod = $('#alert-contact-method'),
                    alertId = $('#detail-panel').find('.hook-editable-keys').data('alertPk'),
                    value = '',
                    data = {}
                ;

                switch ($contactMethod.text()) {
                    case 'E-Mail':
                        value = 'email';
                        break;
                    case 'SMS':
                        value = 'sms';
                        break;
                    case 'All':
                        value = 'all';
                        break;
                }

                data = {
                    "primary_keys" :
                        {
                            "alertPk" : alertId
                        },
                        "id" : "alert-contact-method",
                        "value" : value
                };

                if (value != '') {
                    // trigger a submit on the contact method
                    $contactMethod.editable('submit', {
                        url: '/ajax/alert/updateAlertInfo',
                        data: data,
                        success: function(responseData) {
                            if (responseData.code === 0) {
                                // do nothing
                            } else {
                                // trigger click on the inline editable element to display errors
                                $contactMethod.trigger('click');
                                $contactMethod.siblings('.editable-container').find('.editable-input select.form-control').css('border-color', '#B94A48');
                                $contactMethod.siblings('.editable-container').find('.editable-error-block').html(responseData.validation_error['alert-contact-method']).show().css('color', '#B94A48');
                            }

                            if (! $.isEmptyObject(responseData.message)) {
                                Core.SystemMessage.show(responseData.message, responseData.code);
                            }
                        }
                    });
                }
            });

            // automatically trigger a submit on the contact method if single contact has been updated
            $(document).on('Core.FormElementChanged', '#alert-hours-start, #alert-hours-end', function(event, extraParams) {
                if (! $.isEmptyObject(extraParams)) {
                    var $self = $(this);
                    if (extraParams.response.data.value != null && extraParams.response.data.value != '') {
                        if ($self.prop('id') === 'alert-hours-start') {
                            $('#detail-panel').find('.hook-editable-keys').data('hourStart', extraParams.response.data.value);
                        } else {
                            $('#detail-panel').find('.hook-editable-keys').data('hourEnd', extraParams.response.data.value);
                        }
                    }
                }
            });

        },

        initAddAlert: function() {

            var $body = $('body'),
                $popover = $('#popover-alert-new'),
                $alertTypeNew = $('#alert-type-new')
            ;

            // enable/disable date/time range base on alerttype selection
            $alertTypeNew.on('Core.DropdownButtonChange', function() {
                alertTypeId   = $alertTypeNew.val();
                if (alertTypeId == 2 || alertTypeId == 4 || alertTypeId == 6 || alertTypeId == 7 || alertTypeId == 8) {
                    // set default values
                    $('#alert-days-new').val('all').text('All');
                    $('#alert-hours-new').val('all').text('All');
                    $('#alert-hours-start-new').val('0').text('12:00 am (midnight)');
                    $('#alert-hours-end-new').val('0').text('12:00 am (midnight)');
                    $('.toggle-panel-alert-range-new').css('display', 'none');

                    // disable date/time range options
                    $('#alert-days-new').addClass('disabled');
                    $('#alert-days-new').siblings().filter(':button').addClass('disabled');
                    $('#alert-hours-new').addClass('disabled');
                    $('#alert-hours-new').siblings().filter(':button').addClass('disabled');
                    $('#alert-hours-start-new').addClass('disabled');
                    $('#alert-hours-start-new').siblings().filter(':button').addClass('disabled');
                    $('#alert-hours-end-new').addClass('disabled');
                    $('#alert-hours-end-new').siblings().filter(':button').addClass('disabled');
                } else {
                    //enable date/time range options
                    $('#alert-days-new').removeClass('disabled');
                    $('#alert-days-new').siblings().filter(':button').removeClass('disabled');
                    $('#alert-hours-new').removeClass('disabled');
                    $('#alert-hours-new').siblings().filter(':button').removeClass('disabled');
                    $('#alert-hours-start-new').removeClass('disabled');
                    $('#alert-hours-start-new').siblings().filter(':button').removeClass('disabled');
                    $('#alert-hours-end-new').removeClass('disabled');
                    $('#alert-hours-end-new').siblings().filter(':button').removeClass('disabled');
                }
            });

            // save alert
            $body.on('click', '#popover-new-alert-confirm', function() {

                var alertName       = $('#alert-new-name').val(),
                    alertTypeId     = $('#alert-type-new').val(),
                    alertTypeLabel  = $('#alert-type-new').text(),
                    vehicleMode     = $('#alert-vehicle-mode-new').val()
                    alertDays       = $('#alert-days-new').val(),
                    alertHours      = $('#alert-hours-new').val(),
                    contactMode     = $('#alert-contact-mode-new').val(),
                    data            = {},
                    validation      = []
                ;

                // validate the alert name
                if (alertName != '') {
                    data['alert-new-name'] = alertName;
                } else {
                    validation.push('Alert Name cannot be blank');
                }

                // validate the alert type
                if (alertTypeId != '') {
                    data['alert-new-type'] = alertTypeId;

                    alertType = '';
                    switch (alertTypeId) {
                        /*case '1':
                            alertType = 'boundary';
                            break;*/
                        case '2':
                            alertType = 'extended-stop';
                            break;
                        case '3':
                            alertType = 'landmark';
                            break;
                        case '4':
                            alertType = 'low-voltage';
                            break;
                        case '5':
                            alertType = 'moving';
                            break;
                        case '6':
                            alertType = 'non-reporting';
                            break;
                        case '7':
                            alertType = 'over-speed';
                            break;
                        case '8':
                            alertType = 'tow';
                            break;
                    }

                    if (alertType == 'landmark' || alertType == 'boundary') {   // if the alert type is 'landmark' or 'boundary'

                        data['alert-'+alertType+'-mode-new'] = $('#alert-'+alertType+'-mode-new').val();

                        // get territory mode based on alert type (i.e. landmark or boundary)
                        var mode = data['alert-'+alertType+'-mode-new'];

                        // validate the territory mode
                        if (mode == 'single' || mode == 'group' || mode == 'all') {

                            // get territory/territory group id
                            var territory_id = $('#alert-'+alertType+'-'+mode+'-new').val();

                            // validate the territory/territorygroup_id
                            if (territory_id != undefined && territory_id != '') {
                                data['alert-'+alertType+'-'+mode+'-new'] = territory_id;
                            } else {
                            	if (mode == 'single' || mode == 'group')
                            	{
                                	validation.push('Invalid '+alertTypeLabel+'/'+alertTypeLabel+' group'); // need to get the ale
                                }
                            }

                        } else {
                            validation.push('Invalid '+alertTypeLabel+' mode');
                        }
                        if (alertType == 'landmark')
                        {
                            data['alert-landmark-trigger-new'] = $('#alert-landmark-trigger-new').val();
                        }
                    } else if (alertType == 'extended-stop') {                  // else if the alert type is 'extended stop'
                        var duration = $('#alert-extended-stop-duration-new').val();

                        // validate the time duration
                        if (duration != undefined && duration != '') {
                            data['alert-extended-stop-duration-new'] = duration;
                        }
                    } else if (alertType == 'non-reporting') {                  // else if the alert type is 'non reporting'
                        var duration = $('#alert-non-reporting-duration-new').val();

                        // validate the time duration
                        if (duration != undefined && duration != '') {
                            data['alert-non-reporting-duration-new'] = duration;
                        }
                    } else if (alertType == 'over-speed') {                  // else if the alert type is 'over speed'
                        var overspeed = $('#alert-over-speed-new').val();

                        // validate the time duration
                        if (overspeed != undefined && overspeed != '') {
                            data['alert-over-speed-new'] = overspeed;
                        }
                    } else if (alertType == 'low-voltage') {                    // else if the alert type is 'low voltage'
                        /* // for now, users cannot select the voltage threshold
                        var voltage = $('#alert-voltage-new').val();

                        // validate the voltage
                        if (voltage != undefined && voltage != '') {
                            data['alert-voltage-new'] = voltage;
                        }
                        */
                    } else if (alertType == 'tow') {                  // else if the alert type is 'tow'
                        // no duration value

                    } else if (alertType == 'moving') {                  // else if the alert type is 'moving'
                        // no duration value

                    }
                } else {
                    validation.push('Invalid Alert Type');
                }

                // validate the vehicle mode
                if (vehicleMode == 'single' || vehicleMode == 'group') {
                    data['alert-vehicle-mode-new'] = vehicleMode;

                    // get unit/unitgroup id using the vehicle mode value
                    var unit_id = $('#alert-vehicle-'+vehicleMode+'-new').val();

                    // validate the unit/unitgroup id
                    if (unit_id != undefined && unit_id != '') {
                        data['alert-vehicle-'+vehicleMode+'-new'] = unit_id;
                    } else {
                        validation.push('Invalid vehicle/vehicle group');
                    }
                } else if (vehicleMode == 'all') {
                	data['alert-vehicle-mode-new'] = vehicleMode;
                } else {
                    validation.push('Invalid Vehicle Mode');
                }

                // validate the alert days
                if (alertDays == 'all' || alertDays == 'weekday' || alertDays == 'weekend') {
                    data['alert-days-new'] = alertDays;
                } else {
                    validation.push('Invalid Days');
                }

                // validate the hours
                if (alertHours == 'all' || alertHours == 'range') {
                    data['alert-hours-new'] = alertHours;
                    if (alertHours == 'all') {
                        data['alert-hours-start-new'] = 0;
                        data['alert-hours-end-new'] = 0;
                    } else {
                        var startHour = $('#alert-hours-start-new').val();
                        var endHour = $('#alert-hours-end-new').val();

                        if (((parseInt(endHour) - parseInt(startHour)) > 0) || (endHour == 0)) {
                            data['alert-hours-start-new'] = startHour;
                            data['alert-hours-end-new'] = endHour;
                        } else {
                            validation.push('Hour Start has to be before Hour End');
                        }
                    }
                } else {
                    validation.push('Invalid Hours');
                }

                // validate the alert contacts
                if (contactMode == 'single' || contactMode == 'group' || contactMode == 'reportonly') {
                    data['alert-contact-mode-new'] = contactMode;
                    // validate contact/contactgroup id
                    if (contactMode == 'single' || contactMode == 'group')
                    {
	                    var contact_id = $('#alert-contact-'+contactMode+'-new').val();
	                    if (contact_id != undefined && contact_id != '') {
	                        data['alert-contact-'+contactMode+'-new'] = contact_id;
	                    } else {
	                        validation.push('Invalid contact');
	                    }
	
	                    // if contact mode is 'single', validate contact method
	                    if (contactMode == 'single') {
	                        var method = $('#alert-contact-method-new').val();
	                        if (method == 'all' || method == 'sms' || method == 'email') {
	                            data['alert-contact-method-new'] = method;
	                        } else {
	                            validation.push('Invalid Contact Method');
	                        }
	                    }
	                }
                } else {
                    validation.push('Invalid Contact Mode');
                }

                if (validation.length == 0) {
                    $.ajax({
                        url: '/ajax/alert/addAlert',
                        type: 'POST',
                        dataType: 'json',
                        data: data,
                        success: function(responseData) {
                            if (responseData.code === 0) {
                                if (Core.Environment.context() == 'alert/list') {
                                    // redraw alert table
                                    Alert.List.DataTables.alertList.fnStandingRedraw();
                                }

                                if (Core.Environment.context() == 'alert/history') {
                                    // after adding alert history page, redirect user to alert management page
                                    window.location = '/alert/list';
                                }

                                // close 'Add Alert' popover
                                $('#popover-new-alert-cancel').trigger('click');
                            } else {
                                if (! $.isEmptyObject(responseData.validation_error)) {
                                    // display validation errors
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

            // reset all options in the 'Add Alert' popover to their corresponding default values (their first option)
            $popover.on('hide.bs.popover', function() {
                // reset 'Add Alert' popover fields/options only if the popover is opened
                // (i.e. a 'hide' event triggered from the opening of other popovers shouldn't reset this popover's fields)
                $content = $('.add-alert-popover-inner');
                if ($content.is(':visible')) {
                    $content.find('input[type="text"]').val('');

                    $uls = $content.find('ul');

                    $.each($uls, function() {
                        $(this).find('li a').eq(0).trigger('click');
                    });
                }
            });
        }
    },

    History: {

        DataTables: {

            init: function() {

                Alert.History.DataTables.historyList = Core.DataTable.init('history-list-table', 20, {
                    'oLanguage': {
                        'sInfo': 'Showing _START_ to _END_ of _TOTAL_ History Alerts'
                    },
                    //"aLengthMenu": [[20, 50, 100]],
                    //"sScrollY": "400px",
                    //"bScrollCollapse": true,
                    'bServerSide': true,
                    'sAjaxSource':   '/ajax/alert/getAlertHistory',
                    'aoColumns': [
                        { 'mDataProp': 'deviceeventdate' },
                        { 'mDataProp': 'triggerdate' },
                        { 'mDataProp': 'alerttypename' },
                        { 'mDataProp': 'alertname' },
                        { 'mDataProp': 'formatted_address' },
                        { 'mDataProp': 'unitname' },
                        { 'mDataProp': 'contactname' }
                    ],
                    'aaSorting': [
                        [0,'desc'], // trigger date
                        //[1,'desc'] // event date
                        [3,'asc'] // alert name
                    ],
                    'aoColumnDefs': [
                        { 'sClass': 'col-alert-event-datelast-triggered no-wrap', 'aTargets': [0] },
                        { 'sClass': 'col-alert-last-triggered no-wrap',     'aTargets': [1] },
                        { 'sClass': 'col-alert-type no-wrap',           'aTargets': [2] },
                        { 'sClass': 'col-alert-name no-wrap',           'aTargets': [3] },
                        { 'sClass': 'col-alert-address no-wrap',        'aTargets': [4] },
                        { 'sClass': 'col-alert-vehicles no-wrap',       'aTargets': [5] },
                        { 'sClass': 'col-alert-contacts no-wrap',       'aTargets': [6] }
                    ],
                    /*
                    'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
                        // set <a> tag name and add data-landmark-id attribute to tr
                        //$('td:eq(3)', nRow).html(aData.alertname);
                        $(nRow).data('alertId', aData.alert_id);

                        return nRow;
                    },
                    */
                    'fnServerParams': function (aoData) {

                        var alert_id      = $('#select-alert-id-filter').val().trim();
                        var vehiclegroup_id = $('#sidebar-vehicle-single').val().trim();
                        var alert_type      = $('#select-alert-type-filter').val().trim();
                        var contactgroup_id = $('#select-contact-group-filter').val().trim();
                        var search_string   = '';
                        var filter_type     = 'group_filter';
						var startDate       = '';
						var endDate         = '';
						var toDay           = new Date();
                        var dateFilter      = $('#select-history-range-last-filter').val().trim();

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

                        var searchAlertString = $('#text-alert-search').val().trim();
                        if (typeof(searchAlertString) != 'undefined' && searchAlertString != '')
                        {
                            search_string   = searchAlertString;
                            vehiclegroup_id = 'All';
                            contactgroup_id = 'All';
                            alert_type      = 'All';
                            filter_type     = 'string_search';
                        }

                        aoData.push({name: 'start_date', value: startDate});
                        aoData.push({name: 'end_date', value: endDate});
                        aoData.push({name: 'alert_id', value: alert_id});
                        aoData.push({name: 'vehiclegroup_id', value: vehiclegroup_id});
                        aoData.push({name: 'contactgroup_id', value: contactgroup_id});
                        aoData.push({name: 'search_string', value: search_string});
                        aoData.push({name: 'alert_type', value: alert_type});
                        aoData.push({name: 'filter_type', value: filter_type});
                    }
                });

            }
        },

        initExportAlertHistory: function() {
            $(document).on('click', '#popover-alert-history-export-csv-confirm, #popover-alert-history-export-pdf-confirm', function() {

                var exportFormat    = $(this).prop('id') == 'popover-alert-history-export-pdf-confirm' ? 'pdf' : 'csv';
                var alert_id        = $('#select-alert-id-filter').val().trim();
                var vehiclegroup_id = $('#sidebar-vehicle-single').val().trim();
                var alert_type      = $('#select-alert-type-filter').val().trim();
                var contactgroup_id = $('#select-contact-group-filter').val().trim();
                var search_string   = '';
                var filter_type     = 'group_filter';
				var startDate       = '';
				var endDate         = '';
				var toDay           = new Date();
                var dateFilter      = $('#select-history-range-last-filter').val().trim();
                startDate = Core.StringUtility.filterStartDateConversion(toDay, dateFilter);
                endDate   = Core.StringUtility.filterEndDateConversion(toDay, dateFilter);
                startDate = startDate.replace(' ', '_');
                endDate   = endDate.replace(' ', '_');

                var searchAlertString = $('#text-alert-search').val().trim();
                if (typeof(searchAlertString) != 'undefined' && searchAlertString != '')
                {
                    search_string   = searchAlertString;
                    vehiclegroup_id = search_string;
                    contactgroup_id = 'all';
                    alert_type      = 'all';
                    filter_type     = 'string_search';

                }

                window.location = '/ajax/alert/exportAlertHistory/'+exportFormat+'/' +filter_type+'/'+vehiclegroup_id+'/'+contactgroup_id+'/'+alert_id+'/'+alert_type+'/'+startDate+'/'+endDate;
            });
        }
    },

    Common: {
        SecondaryPanel: {

            initAlertSearch: function() {

                var $alertSearch                = $('#text-alert-search');
                var $alertSearchGo              = $('#alert-search-go');
                var $alertVehicleGroupFilter    = $('#sidebar-vehicle-single');
                var $alertContactGroupFilter    = $('#select-contact-group-filter');
                var $alertTypeFilter            = $('#select-alert-type-filter');
                var $historyAlertDateFilter     = $('#select-history-range-last-filter');
                var $historyAlertIdFilter       = $('#select-alert-id-filter');
                var $secondaryPanelPagination   = $('#secondary-panel-pagination');
                var $selectAlertSearchTab       = $('#select-alert-search-tab');

                /**
                 *
                 * On keyup when searching alerts using search string
                 *
                 */
                $alertSearch.on('keyup', function () {

                    // get current search string
                    var searchAlertString = $alertSearch.val().trim();

                    if (Core.Environment.context() == 'alert/list') {

                        if (searchAlertString.length > 1) {
                            Alert.List.DataTables.alertList.fnDraw();
                        } else if (searchAlertString.length == 0) {
                            Alert.List.DataTables.alertList.fnDraw({});
                        }

                        $('#select-alert-type-filter').val('ALL').text('All');
                    }

                    if (Core.Environment.context() == 'alert/history') {

                        if (searchAlertString.length > 1) {
                            Alert.History.DataTables.historyList.fnDraw();
                        } else if (searchAlertString.length == 0) {
                            Alert.History.DataTables.historyList.fnDraw({});
                        }

                        $('#select-alert-type-filter').val('ALL').text('All');
                        $('#select-alert-id-filter').val('All').text('All');
                        $('#select-history-range-last-filter').val('last-1-days').text('1 Day');
                    }

                    $('#sidebar-vehicle-single').val('All').text('All');
                    $('#select-contact-group-filter').val('ALL').text('All');

                });

                /**
                 *
                 * On Search Button Click when searching alerts using search string
                 *
                 */
                $alertSearchGo.on('click', function () {
                    // get current search string
                    var searchAlertString = $alertSearch.val().trim();

                    if (Core.Environment.context() == 'alert/list') {
                        if (searchAlertString != '') {
                            Alert.List.DataTables.alertList.fnDraw();
                        }
                        $('#select-alert-type-filter').val('ALL').text('All');
                    }

                    if (Core.Environment.context() == 'alert/history') {
                        // clear out the search box before redrawing table
                        Alert.History.DataTables.historyList.fnDraw();

                        $('#select-alert-type-filter').val('ALL').text('All');
                        $('#select-alert-id-filter').val('All').text('All');
                        $('#select-history-range-last-filter').val('last-1-days').text('1 Days');
                    }

                    $('#sidebar-vehicle-single').val('All').text('All');
                    $('#select-contact-group-filter').val('ALL').text('All');
                });

                /**
                 *
                 * On Change of Alert Type Filtering on alert filter search
                 *
                 */
                $alertTypeFilter.on('Core.DropdownButtonChange', function() {

                    $('#text-alert-search').val('');

                    if (Core.Environment.context() == 'alert/list') {
                        // clear out the search box before redrawing table
                        Alert.List.DataTables.alertList.fnDraw();
                    }

                    if (Core.Environment.context() == 'alert/history') {
                        // clear out the search box before redrawing table
                        Alert.History.DataTables.historyList.fnDraw();
                    }
                });

                /**
                 *
                 * On Change of Vehicle Group Filtering on alert filter search
                 *
                 */
                $alertVehicleGroupFilter.on('Core.DropdownButtonChange', function() {

                    $('#text-alert-search').val('');

                    if (Core.Environment.context() == 'alert/list') {
                        // clear out the search box before redrawing table
                        Alert.List.DataTables.alertList.fnDraw();
                    }

                    if (Core.Environment.context() == 'alert/history') {
                        // clear out the search box before redrawing table
                        Alert.History.DataTables.historyList.fnDraw();
                    }

                });

                /**
                 *
                 * On Change of Contact Group Filtering on alert filter search
                 *
                 */
                $alertContactGroupFilter.on('Core.DropdownButtonChange', function() {

                    $('#text-alert-search').val('');

                    if (Core.Environment.context() == 'alert/list') {
                        // clear out the search box before redrawing table
                        Alert.List.DataTables.alertList.fnDraw();
                    }

                    if (Core.Environment.context() == 'alert/history') {
                        // clear out the search box before redrawing table
                        Alert.History.DataTables.historyList.fnDraw();
                    }
                });

                /**
                 *
                 * On Change of Vehicle Group Filtering on alert filter search
                 *
                 */
                $historyAlertDateFilter.on('Core.DropdownButtonChange', function() {

                    $('#text-alert-search').val('');

                    if (Core.Environment.context() == 'alert/history') {
                        // clear out the search box before redrawing table
                        Alert.History.DataTables.historyList.fnDraw();
                    }

                });

                /**
                 *
                 * On Change of Contact Group Filtering on alert filter search
                 *
                 */
                $historyAlertIdFilter.on('Core.DropdownButtonChange', function() {

                    $('#text-alert-search').val('');

                    if (Core.Environment.context() == 'alert/history') {
                        // clear out the search box before redrawing table
                        Alert.History.DataTables.historyList.fnDraw();
                    }
                });




            }

        }

    }


});
