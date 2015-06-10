/*

    Account JS

    File:       /assets/js/Account.js
    Author:     Tom Leach
*/

$(document).ready(function() {

    Device.isLoaded();

    /**
     *
     * Common Functionality for Alerts Pages
     *
     */

    /**
     *
     * Page Specific Functionality
     *
     */
    switch (Core.Environment.context()) {

        /* DEVICE LIST */
        case               'admin/list' :
        case             'device/admin' :
        case              'device/list' :   Device.List.DataTable.init();
                                            Device.List.Modal.init();
                                            Device.List.Edit.init();
                                            Device.List.SecondaryPanel.initDeviceSearch();
                                            Device.List.DetailPanel.initInfoTab();
                                            //Device.List.Popover.init();
                                            break;

                                default :   break;

    }

});

var Device = {};

jQuery.extend(Device, {



    isLoaded: function() {

        Core.log('Device JS Loaded');


    },

    List: {

        Edit: {

            init: function() {
                // Group Assignment
                $('#vehicle-group-assignment').on('Core.DragDrop.Dropped', function(event, extraParams) {

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
                
                // Search Available Vehicles By Name
                var $availableVehicleSearch     = $('#filter-available-text');
                var $availableVehicleSearchGo   = $('#filter-available-go');
                
                $(document).on('keyup', $availableVehicleSearch.selector, function () {
                    // get current search string
                    var searchvehiclestring = $availableVehicleSearch.val().trim();

                    if (searchvehiclestring.length > 1 || searchvehiclestring.length == 0) {
                        Device.List.Edit.getFilteredAvailableVehicles(searchvehiclestring, 'available');
                    }
                });
                
                $(document).on('click', $availableVehicleSearchGo.selector, function () {
                    // get current search string
                    var searchvehiclestring = $availableVehicleSearch.val().trim();

                    if (searchvehiclestring != '') {
                        Device.List.Edit.getFilteredAvailableVehicles(searchvehiclestring, 'available');
                    }
                });

                var $assignedVehicleSearch     = $('#filter-assigned-text');
                var $assignedVehicleSearchGo   = $('#filter-assigned-go');
                
                $(document).on('keyup', $assignedVehicleSearch.selector, function () {
                    // get current search string
                    var searchvehiclestring = $assignedVehicleSearch.val().trim();
                    var selectedDestinationGroupId  = $('#vehicle-group-destination').val();

                    if (selectedDestinationGroupId > 0 && (searchvehiclestring.length > 1 || searchvehiclestring.length == 0 )) {
                        Device.List.Edit.getFilteredAvailableVehicles(searchvehiclestring, 'assigned');
                    }
                });
                
                $(document).on('click', $assignedVehicleSearchGo.selector, function () {
                    // get current search string
                    var searchvehiclestring = $assignedVehicleSearch.val().trim();
                    var selectedDestinationGroupId  = $('#vehicle-group-destination').val();

                    if (selectedDestinationGroupId > 0 && searchvehiclestring != '') {
                        Device.List.Edit.getFilteredAvailableVehicles(searchvehiclestring, 'assigned');
                    }
                });

            },
            
            getFilteredAvailableVehicles: function(searchString, groupColumn) {
                
                searchString = searchString || '';
                groupColumn = groupColumn || '';
                searchFromGroupId = 0;
                            
                var $vehicleGroupAssignment = $('#vehicle-group-assignment');
                $searchGroupList            = $vehicleGroupAssignment.find('.drag-drop-'+groupColumn);
                selectedSourceGroupId       = $('#vehicle-group-source').val();
                selectedDestinationGroupId  = $('#vehicle-group-destination').val();
                
                if (groupColumn == 'available') {
                    // searching on left side (available)
                    searchFromGroupId = (selectedSourceGroupId != 'all') ? selectedSourceGroupId : '';
                    
                    // hide vehicles on left side
                    $searchGroupList.find('li').hide();
                } else {
                    // searching on right side (assigned)
                    searchFromGroupId = (selectedDestinationGroupId != 0) ? selectedDestinationGroupId : '';

                    // hide and move all right side vehicles to left side
                    $destinationVehicles = $searchGroupList.find('li').hide();
                    $vehicleGroupAssignment.find('.drag-drop-available').append($destinationVehicles.detach());
                }
                
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
                            // successfule and if vehicles found, then create list of found vehicles
                            if (! $.isEmptyObject(responseData.data.units)) {
                                var searchedVehicles = responseData.data.units;
                                $.each(searchedVehicles, function() {

                                    if (groupColumn == 'assigned') {
                                        // if searched on right side, find found vehicle on leftside and move to right side
                                        foundVehicle = $vehicleGroupAssignment.find('.drag-drop-available li').filter('[data-id="'+this.unit_id+'"]').detach();
                                        $vehicleGroupAssignment.find('.drag-drop-assigned').append(foundVehicle.show());
                                    } else {
                                        // if searched on left side, show the found vehicle already on left side
                                        $searchGroupList.find('li').filter('[data-id="'+this.unit_id+'"]').show();
                                    }
                                });
                            }

                            if (selectedDestinationGroupId != 0) {
                                // if vehicle group id is same group on right side, dont show it
                                $vehicleGroupAssignment.find('.drag-drop-available li').filter('.transfer-group-id-'+selectedDestinationGroupId).hide();
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

        DataTable: {

            init: function() {

                Device.List.DataTable.deviceList = Core.DataTable.init('device-list-table', 20, {
                    'oLanguage': {
                        'sInfo': 'Showing _START_ to _END_ of _TOTAL_ Devices'
                    },
                    'bServerSide': true,
                    'sAjaxSource':   '/ajax/device/getFilteredDeviceList',
                    'aoColumns': [
                        { 'mDataProp': 'name' },
                        { 'mDataProp': 'unitgroupname' },
                        { 'mDataProp': 'serialnumber' },
                        { 'mDataProp': 'vin' },
                        { 'mDataProp': 'make' },
                        { 'mDataProp': 'model' },
                        { 'mDataProp': 'year' },
                        { 'mDataProp': 'color' },
                        { 'mDataProp': 'licenseplatenumber' },
                        { 'mDataProp': 'loannumber' },
                        { 'mDataProp': 'unitstatusname' },
                        { 'mDataProp': 'formatted_purchasedate' },
                        { 'mDataProp': 'formatted_expirationdate' }
                    ],
                    'aaSorting': [
                        [0,'asc'],
                        [1,'asc']
                    ],
                    'aoColumnDefs': [
                        { 'sClass': 'col-name no-wrap',       'aTargets': [0] },
                        { 'sClass': 'col-groupname no-wrap',   'aTargets': [1] },
                        { 'sClass': 'col-type no-wrap', 'aTargets': [2] },
                        { 'sClass': 'col-type no-wrap',       'aTargets': [3] },
                        { 'sClass': 'col-type no-wrap',      'aTargets': [4] },
                        { 'sClass': 'col-type no-wrap', 'aTargets': [5] },
                        { 'sClass': 'col-type no-wrap', 'aTargets': [6] },
                        { 'sClass': 'col-type no-wrap', 'aTargets': [7] },
                        { 'sClass': 'col-type no-wrap', 'aTargets': [8] },
                        { 'sClass': 'col-type no-wrap', 'aTargets': [9] },
                        { 'sClass': 'col-type no-wrap', 'aTargets': [10] },
                        { 'sClass': 'col-type no-wrap', 'aTargets': [11] },
                        { 'sClass': 'col-type no-wrap', 'aTargets': [12] }
                    ],
                    'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
                        // set <a> tag name and add data-landmark-id attribute to tr
                        $('td:eq(0)', nRow).html('<a href="#">'+aData.unitname+'</a>');
                        $(nRow).data('deviceId', aData.unit_id);

                        return nRow;
                    },
                    'fnServerParams': function (aoData) {

                        var group_id        = $('#vehicle-group-filter').val().trim();
                        var device_status   = $('#device-status-filter').val().trim();
                        var search_string   = '';
                        var filter_type     = 'group_filter';

                        var searchDeviceString = $('#text-device-search').val().trim();
                        if (typeof(searchDeviceString) != 'undefined' && searchDeviceString != '')
                        {
                            search_string   = searchDeviceString;
                            group_id        = 'all';
                            device_status   = 'all';
                            filter_type     = 'string_search';
                        }

                        aoData.push({name: 'search_string', value: search_string});
                        aoData.push({name: 'filter_type', value: filter_type});
                        aoData.push({name: 'vehicle_group_id', value: group_id});
                        aoData.push({name: 'unitstatus_id', value: device_status});
                    }
                });

            }

        },

        Modal: {

            init: function() {

                var $deviceEditNav = $('#device-edit-nav');

                $(document).on('click', '.col-name a', function() {

                    var $self = $(this),
                        $trNode = $self.closest('tr'),
                        unitId = $trNode.attr('id').split('-')[2],
                        $modal = $('#modal-vehicle-list')
                    ;

                    if (unitId != undefined) {
                        $.ajax({
                            url: '/ajax/device/getDeviceDataInfo',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                unit_id: unitId
                            },
                            success: function(responseData) {
                                if (responseData.code === 0) {
                                    var unitdata = responseData.data;
        
                                    Device.List.DetailPanel.render(unitdata);
    
                                    Core.Dialog.launch('#'+$modal.prop('id'), unitdata.unitname, {
                                            width: '1080px'
                                        }, {
                                            hidden: function() {
                                                Device.List.DetailPanel.reset();
                                                Device.List.DataTable.deviceList.fnStandingRedraw();
                                            },
                                            shown: function() {
    
                                            }
                                        });                                                                                   
    
                                } else {
                                    if ($.isEmptyObject(responseData.validaton_errors) === false) {
                                        //	display validation errors
                                    }
                                }
                            }
                        });
                    }
                });

                // for device transfer
                $(document).on('click', '#button-device-transfer', function() {

                    var $modal = $('#modal-device-transfer');

                    Core.Dialog.launch('#'+$modal.prop('id'), 'Device Transfer', {
                        width: '600px'
                    }, 
                    {
                        hidden: function() {

                            Device.List.DataTable.deviceList.fnStandingRedraw();
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
                        shown: function() {

                            $.ajax({
                                url: '/ajax/device/getDeviceTransferDataByAccountId',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                },
                                success: function(responseData) {
                                    if (responseData.code === 0) {
                                        var unitdata = responseData.data;

                                        // update available list                            
                                        var $vehicleGroupAssignment = $('#vehicle-group-assignment'),
                                            $vehicleAvailableList   = $vehicleGroupAssignment.find('.drag-drop-available'),
                                            $vehicleAssignedList    = $vehicleGroupAssignment.find('.drag-drop-assigned')
                                        ;
                                        
                                        // clear available
                                        $vehicleAvailableList.html('');
                                        $vehicleAssignedList.html('');
                                                        
                                        // create available vehicle list
                                        if (! $.isEmptyObject(unitdata)) {
                                            var available_vehicles = unitdata;
                                            $.each(available_vehicles, function() {
                                                groupIdClass = 'transfer-group-id-'+this.unitgroup_id;
                                                $vehicleAvailableList.append(Core.DragDrop._generateGroupMarkup(this.unit_id, this.unitname, this.unitgroup_id, groupIdClass));
                                            });
                                        }

                                        Core.DragDrop.init();   
                            
                                    } else {
                                        if ($.isEmptyObject(responseData.validaton_errors) === false) {
                                            //	display validation errors
                                        }
                                    }
                                }
                            });
                        }
                    });
                });

                // listener for changing vehicle bulk transfer dropdown groups
                $('#vehicle-group-source, #vehicle-group-destination').on('Core.DropdownButtonChange', function() {
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

                    // source dropdown manipulation: show all group options but that of the selected destination group and it's own selected group
                    $sourceDropdownList.filter(':not(.'+$groupIdTransferDestinationClass+')').show();
                    $sourceDropdownList.filter('.'+$groupIdTransferDestinationClass).hide();
                    $sourceDropdownList.filter('.'+$groupIdTransferSourceClass).hide();
                    
                    // destination dropdown manipulation: show all group options but that of the selected source group and it's own selected group
                    $destinationDropdownList.filter(':not(.'+$groupIdTransferSourceClass+')').show();
                    $destinationDropdownList.filter('.'+$groupIdTransferDestinationClass).hide();
                    $destinationDropdownList.filter('.'+$groupIdTransferSourceClass).hide();

                    // display all available vehicles on left side
                    $availableVehicleList.show();

                    if ($clickedVehicleGroupButton == 'vehicle-group-source') {
                    
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
                    } else {
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
                    }
                   
                   Core.DragDrop.destroy();
                   Core.DragDrop.init();
                });

                // prevent access to Vehicle/Landmark Groups tabs for non-Active user                
                $deviceEditNav.on('click', 'li', function() {
                    var $self = $(this);
                    
                    if ($self.is('.disabled')) {
                        return false;
                    }    
                });
            }
        },
        

        SecondaryPanel: {
        
            initDeviceSearch: function() {
                var $deviceSearch       = $('#text-device-search');
                var $deviceSearchGo     = $('#device-search-go');
                var $vehicleGroupFilter = $('#vehicle-group-filter');
                var $deviceStatusFilter = $('#device-status-filter');

                /**
                 *
                 * On keyup when searching alerts using search string 
                 *
                 */
                $deviceSearch.on('keyup', function () {
                    
                    // get current search string
                    var searchDeviceString = $deviceSearch.val().trim();

                    if (searchDeviceString.length > 1) {
                        Device.List.DataTable.deviceList.fnDraw();
                    } else if (searchDeviceString.length == 0) {
                        Device.List.DataTable.deviceList.fnDraw();
                    }

                    $('#vehicle-group-filter').val('all').text('All');
                    $('#device-status-filter').val('all').text('All');
                });

                /**
                 *
                 * On Search Button Click when searching alerts using search string 
                 *
                 */
                $deviceSearchGo.on('click', function () {
                    // get current search string
                    var searchDeviceString = $deviceSearch.val().trim();

                    if (searchDeviceString != '') {
                        Device.List.DataTable.deviceList.fnDraw();
                    }

                    $('#vehicle-group-filter').val('all').text('All');
                    $('#device-status-filter').val('all').text('All');
                });
                
                /**
                 *
                 * On Change of User Type Filtering on user filter search
                 *
                 */
                $vehicleGroupFilter.on('Core.DropdownButtonChange', function() {

                    $('#text-device-search').val('');

                    // clear out the search box before redrawing table
                    Device.List.DataTable.deviceList.fnDraw();
                });

                /**
                 *
                 * On Change of User Status Filtering on user filter search
                 *
                 */
                $deviceStatusFilter.on('Core.DropdownButtonChange', function() {

                    $('#text-device-search').val('');

                    // clear out the search box before redrawing table
                    Device.List.DataTable.deviceList.fnDraw();
                    //Device.List.DataTable.deviceList.fnStandingRedraw();
                });
            }
        },
        
        DetailPanel: {

            /**
             *
             * Populates and prepares the Detail Panel after AJAX calls are made for vehicle specific data
             *
             * */
            render: function(unitdata, callBack) {
                if ((unitdata != undefined) && (typeof(unitdata) == 'object') && (! $.isEmptyObject(unitdata))) {
                   
                    //if (Core.Environment.context() == 'device/list') {
                        $container = $('#detail-panel');
                    //}
                   
                   // populate vehicle id
                   $container.find('.hook-editable-keys').eq(0).data('vehicle-pk', unitdata.unit_id).data('vehicle-odometer-id', unitdata.odometer_id);
                   
                    /***************
                     *
                     * VEHICLE INFO
                     *
                     ***************/
                    var $vehicleStatus         = $('#vehicle-status'),
                        $vehicleName           = $('#vehicle-name'),
                        $vehicleSerial         = $('#vehicle-serial'),
                        $vehicleGroup          = $('#vehicle-group'),
                        $vehicleVin            = $('#vehicle-vin'),
                        $vehicleMake           = $('#vehicle-make'),
                        $vehicleModel          = $('#vehicle-model'),
                        $vehicleYear           = $('#vehicle-year'),
                        $vehicleColor          = $('#vehicle-color'),
                        $vehicleStock          = $('#vehicle-stock'),
                        $vehicleLicPlate       = $('#vehicle-license-plate'),
                        $vehicleLoanId         = $('#vehicle-loan-id'),
                        $vehicleInstallDate    = $('#vehicle-install-date'),
                        $vehicleInstaller      = $('#vehicle-installer'),
                        $vehicleInstallMileage = $('#vehicle-install-mileage'),
                        $vehicleDrivenMiles    = $('#vehicle-driven-miles'),
                        $vehicleTotalMileage   = $('#vehicle-total-mileage')
                    ;

                    // editable
                    Core.Editable.setValue($vehicleStatus, unitdata.unitstatus_id);
                    Core.Editable.setValue($vehicleName, unitdata.unitname);
                    Core.Editable.setValue($vehicleGroup, unitdata.unitgroup_id);
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
                    $devicePlan.html(Core.StringUtility.formatStaticFormValue());
                    $devicePurchaseDate.html(Core.StringUtility.formatStaticFormValue(unitdata.formatted_purchasedate));
                    $deviceActivationDate.html(Core.StringUtility.formatStaticFormValue());
                    $deviceRenewalDate.html(Core.StringUtility.formatStaticFormValue(unitdata.formatted_expirationdate));
                    $deviceLastRenewed.html(Core.StringUtility.formatStaticFormValue(unitdata.formatted_lastrenewaldate));
                    $deviceActivationDate.html(Core.StringUtility.formatStaticFormValue(unitdata.formatted_activatedate));
                    $deviceDeactivationDate.html(Core.StringUtility.formatStaticFormValue(unitdata.formatted_deactivatedate));


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
                        Device.List.DetailPanel.open(callBack);
                   // }, 1000);

                }
            },

            open: function(callBack) {

                /**
                 *
                 * Open up the detail panel
                 *
                 * */
                if (Core.Environment.context() == 'decive/list') {
                    var $mapDiv      = $('#map-div'),
                        $detailPanel = $('#detail-panel')
                    ;
                    
                    $mapDiv.animate({
                        'height': '400px'
                    }, 300, function() { 
                        if ((callBack != undefined) && (typeof callBack == 'function')) {
                            callBack();
                        }
                    });

                    $detailPanel.slideDown(300).addClass('open');
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
                                    
                if (Core.Environment.context() == 'device/list') {
                    $container        = $('#modal-vehicle-list');
                    $containerNav     = $('#vehicle-list-info-tab');
                    $containerSection = $('#vehicle-info-button');                   
                }

                // trigger click on all inline-editable cancel buttons when detail panel closes
                $container.find('button').filter('.editable-cancel').trigger('click');
                
                // reset tabs to defaults (Info Tab - Vehicle Information)
                $containerNav.trigger('click');
                $containerSection.trigger('click');
            },

            initClose: function() {

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
                        'height': '800px'
                    }, 300, function() {

                        Map.resize(Vehicle.Map.map);
                        if ($detailPanel.is('.open')) {
                            $detailPanel.removeClass('open');
                        }
                        
                        Device.List.DetailPanel.reset();                              
                        
                        if ((callBack != undefined) && (typeof(callBack) == 'function')) {
                            callBack();
                        }
                    });

                    $detailPanel.slideUp(300);
                });
            },
            
            initInfoTab: function() {
                var $vehicleOdometer    = $('#vehicle-install-mileage'),
                    $detailPanel        = $('#detail-panel')
                ;
                
                $vehicleOdometer.on('Core.FormElementChanged', function(event, extraParams) {

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

                });                    
            }
        }

    }


});