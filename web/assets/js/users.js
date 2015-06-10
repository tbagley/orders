/*

    Users JS

    File:       /assets/js/Users.js
    Author:     Tom Leach
*/

$(document).ready(function() {

    Users.isLoaded();

    /**
     *
     * Page Specific Functionality
     *
     */
    switch (Core.Environment.context()) {

        /* USERS LIST */
        case 'admin/users':
        case 'users/list':
            Users.User.DataTable.init();
            Users.User.Modal.init();
            Users.User.Modal.initExportUsers();
            Users.User.Edit.init();
            Users.User.SecondaryPanel.initUserSearch();
            Users.User.Popover.init();
            break;
        /* USERS TYPE */
        case 'admin/usertypes':
        case 'users/type':
            Users.UserType.DataTable.init();
            Users.UserType.Modal.init();
            Users.UserType.SecondaryPanel.initUserTypeSearch();
            Users.UserType.Popover.init();
            break;
        default:
            break;

    }

});

var Users = {};

jQuery.extend(Users, {

    isLoaded: function() {

        Core.log('Users JS Loaded');

    },

    User: {

        Edit: {

            init: function() {
                // Group Assignment
                $('#user-vehicle-group-assignment, #user-landmark-group-assignment').on('Core.DragDrop.Dropped', function(event, extraParams) {

                    if (! $.isEmptyObject(extraParams)) {
                        var $self                       = $(this),
                            id                          = $self.prop('id'),
                            type                        = (id == 'user-vehicle-group-assignment') ? 'vehicle' : 'landmark',
                            updatedItems                = extraParams.updatedItems.items,
                            method                      = (extraParams.updatedItems.inAssignedGroup === true) ? 'add'+(type.charAt(0).toUpperCase()+type.slice(1))+'GroupToUser' : 'remove'+(type.charAt(0).toUpperCase()+type.slice(1))+'GroupFromUser',
                            userId                      = $('#detail-panel').find('.hook-editable-keys').data('userPk') || 0,
                            $assignAllGroupsCheckbox    = $('#user-'+type+'-groups-all'),
                            data                        = {}
                        ;

                        if (updatedItems != undefined && updatedItems.length != 0 && userId != undefined && userId != 0) {
                            data.user_id = userId;
                            
                            if (type == 'vehicle') {
                                data.vehiclegroups = updatedItems;
                            } else if (type == 'landmark') {
                                data.landmarkgroups = updatedItems;
                            }
                            
                            $.ajax({
                                url: '/ajax/'+type+'/'+method,
                                type: 'POST',
                                dataType: 'json',
                                data: data,
                                success: function(responseData) {
                                    Core.log('Users.User.Modal Update Vehicle/Landmark Group - on Core.DragDrop.Dropped', 'group');

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

                                    Core.log('Users.User.Modal Update Vehicle/Landmark Group - on Core.DragDrop.Dropped', 'groupEnd');
                                }
                            });

                        }
                        
                        // enable/disable 'Assign All Groups' checkbox depending on if there are any available groups
                        if (extraParams.available.length > 0) {
                            $assignAllGroupsCheckbox.prop('disabled', false);
                        } else {
                            $assignAllGroupsCheckbox.prop('disabled', true);
                        } 
                    }
                });


                // More Options
                $(document).on('click', '#user-more-options-toggle', function() {
                    
                    var $optionsToggle = $(this),
                        $toggleLabel   = $optionsToggle.find('small')
                    ;
                    
                    if ($toggleLabel.text() == 'Show More Options') {
                        $toggleLabel.text('Show Less Options');
                    } else {
                        $toggleLabel.text('Show More Options');
                    }

                    $('#user-more-options').slideToggle(300);

                });
            }

        },

        DataTable: {

            init: function() {

                Users.User.DataTable.userList = Core.DataTable.init('user-list-table', 20, {
                    'oLanguage': {
                        'sInfo': 'Showing _START_ to _END_ of _TOTAL_ Users'
                    },
                    'bServerSide': true,
                    'sAjaxSource':   '/ajax/users/getFilteredUsers',
                    'aoColumns': [
                        { 'mDataProp': 'name' },
                        { 'mDataProp': 'username' },
                        { 'mDataProp': 'usertype' },
                        { 'mDataProp': 'userstatusname' },
                        { 'mDataProp': 'email' },
                        { 'mDataProp': 'cellnumber' }//,
                        //{ 'mDataProp': 'lastlogin' }
                    ],
                    'aaSorting': [
                        [0,'asc'],
                        [1,'asc']
                    ],
                    'aoColumnDefs': [
                        { 'sClass': 'col-name no-wrap',       'aTargets': [0] },
                        { 'sClass': 'col-username no-wrap',   'aTargets': [1] },
                        { 'sClass': 'col-userstatus no-wrap', 'aTargets': [2] },
                        { 'sClass': 'col-type no-wrap',       'aTargets': [3] },
                        { 'sClass': 'col-email no-wrap',      'aTargets': [4] }//,
                        //{ 'sClass': 'col-last-login no-wrap', 'aTargets': [5] }
                    ],
                    'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
                        // set <a> tag name and add data-landmark-id attribute to tr
                        $('td:eq(0)', nRow).html('<a href="#">'+aData.name+'</a>');
                        $(nRow).data('userId', aData.user_id);

                        return nRow;
                    },
                    'fnServerParams': function (aoData) {

                        var user_type       = $('#user-type-filter').val().trim();
                        var user_status     = $('#user-status-filter').val().trim();
                        var search_string   = '';
                        var filter_type     = 'group_filter';

                        var searchUserString = $('#text-user-search').val().trim();
                        if (typeof(searchUserString) != 'undefined' && searchUserString != '')
                        {
                            search_string   = searchUserString;
                            user_role       = 'All';
                            filter_type     = 'string_search';
                        }

                        //aoData.push({name: 'user_role', value: user_role});
                        aoData.push({name: 'search_string', value: search_string});
                        aoData.push({name: 'filter_type', value: filter_type});
                        aoData.push({name: 'usertype_id', value: user_type});
                        aoData.push({name: 'userstatus_id', value: user_status});
                    }
                });

            }

        },

        Modal: {

            init: function() {

console.log('Users.User.Modal.init()');

                var $userEditNav = $('#user-edit-nav');

                $(document).on('click', '.col-name a', function() {

                    var $self = $(this),
                        $trNode = $self.closest('tr'),
                        userId = $trNode.attr('id').split('-')[2],
                        $modal = $('#modal-user-list')
                    ;

                    if (userId != undefined && userId != '') {
                        $.ajax({
                            url: '/ajax/users/getUserById',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                user_id: userId
                            },
                            success: function(responseData) {
                                if (responseData.code === 0) {
                                    var userData = responseData.data.user;

                                    if (! $.isEmptyObject(userData)) {
                    
                                        Core.Dialog.launch($modal.selector, 'User', {
                                            width: '650px'
                                        },
                                        {
                                            hidden: function() {
                                                // redraw user list table
                                                Users.User.DataTable.userList.fnStandingRedraw();
                                                
                                                // hide 'More Options' section
                                                var $toggle = $('#user-more-options-toggle').find('small');
                                                if ($toggle.text() == 'Show Less Options') {
                                                    $toggle.trigger('click');    
                                                }

                                                // destroy any DragDrop containers after closing modal
                                                Core.DragDrop.destroy();
                                                
                                                // change back to User Detail tab after closing modal
                                                $userEditNav.find('li a').eq(0).trigger('click'); 
                                                
                                                // uncheck Assign All Vehicle/Landmark Groups checkboxes
                                                $('#user-vehicle-groups-all, #user-landmark-groups-all').prop({'disabled': false, 'checked': false});
  
                                                // re-enable drag drop containers
                                                Core.DragDrop.enable('#user-vehicle-group-assignment');
                                                Core.DragDrop.enable('#user-landmark-group-assignment');
                                                

                                            },
                                            hide: function() {
                                                $('#user-details-tab').find('.editable-cancel').trigger('click');    
                                            },
                                            show: function() {

                                                $modal.find('.modal-title').text('').hide()
                    
                                            },
                                            shown: function() {
                                                
                                                // enable access to Vehicle/Landmark Groups tabs for only Active user
                                                //if (userData.userstatusname != undefined && userData.userstatusname != '' && userData.userstatusname == 'Active') {
                                                    $userEditNav.find('li a').filter(function() {
                                                        return (/Vehicle Groups/.test(this.innerHTML) || /Landmark Groups/.test(this.innerHTML));    
                                                    }).parents('li').removeClass('disabled');    
                                                /*} else {
                                                    $userEditNav.find('li a').filter(function() {
                                                        return (/Vehicle Groups/.test(this.innerHTML) || /Landmark Groups/.test(this.innerHTML));    
                                                    }).parents('li').addClass('disabled');                                                    
                                                }*/
                                                
                                                var $userVehicleGroupAssignment = $('#user-vehicle-group-assignment'),
                                                    $userLandmarkGroupAssignment = $('#user-landmark-group-assignment'),
                                                    $userVehicleGroupAvailable = $userVehicleGroupAssignment.find('.drag-drop-available'),
                                                    $userVehicleGroupAssigned = $userVehicleGroupAssignment.find('.drag-drop-assigned'),
                                                    $userLandmarkGroupAvailable = $userLandmarkGroupAssignment.find('.drag-drop-available'),
                                                    $userLandmarkGroupAssigned = $userLandmarkGroupAssignment.find('.drag-drop-assigned')
                                                ; 
                                                
                                                $('#detail-panel').find('.hook-editable-keys').data('userPk', userId)

                                                $('#detail-panel').find('.hook-editable-keys').data('contactPk', userData.contact_id);
                                                // set cell number
                                                $('#detail-panel').find('.hook-editable-keys').data('user-cell', userData.cellnumber);
                                                // set cell carrier
                                                $('#detail-panel').find('.hook-editable-keys').data('contactCarrier', userData.cellcarrier_id);

                                                $modal.find('.modal-title').text($self.text()).fadeIn(100);

                                                // set first name
                                                Core.Editable.setValue($('#user-firstname'),  userData.firstname);

                                                // set last name
                                                Core.Editable.setValue($('#user-lastname'),  userData.lastname);
                                                
                                                // set email
                                                Core.Editable.setValue($('#user-email'),  userData.email);

                                                // clear error highlighted sms/carrier field label
                                                Core.Editable.removeError('#user-cell');
                                                Core.Editable.removeError('#contact-carrier');

                                                // set cellnumber
                                                if (userData.formatted_cellnumber != undefined && userData.formatted_cellnumber != 0) {
                                                    Core.Editable.setValue($('#user-cell'),  userData.formatted_cellnumber);
                                                    if ( userData.cellcarrier_id == 0) {
                                                        Core.Editable.setError('#contact-carrier');
                                                    }
                                                } else {
                                                    Core.Editable.setValue($('#user-cell'),  null);
                                                }

                                                // set cell carrier
                                                Core.Editable.setValue($('#contact-carrier'),  userData.cellcarrier_id);

                                                // set user type
                                                Core.Editable.setValue($('#user-usertype'),  userData.usertype_id);
                                                
                                                // clear list of available & assigned vehicle/landmark groups
                                                $($userVehicleGroupAvailable.selector+','+$userVehicleGroupAssigned.selector+','+$userLandmarkGroupAvailable.selector+','+$userLandmarkGroupAssigned.selector).html('');
                                                
                                                if (! $.isEmptyObject(userData.available_vehiclegroups)) {
                                                    $.each(userData.available_vehiclegroups, function() {
                                                        $userVehicleGroupAvailable.append(Core.DragDrop._generateGroupMarkup(this.unitgroup_id, this.unitgroupname));        
                                                    });    
                                                } else {
                                                    $('#user-vehicle-groups-all').prop('disabled', true);
                                                }
                                                
                                                if (! $.isEmptyObject(userData.assigned_vehiclegroups)) {
                                                    $.each(userData.assigned_vehiclegroups, function() {
                                                        $userVehicleGroupAssigned.append(Core.DragDrop._generateGroupMarkup(this.unitgroup_id, this.unitgroupname));    
                                                    });                                                    
                                                }

                                                if (! $.isEmptyObject(userData.available_territorygroups)) {
                                                    $.each(userData.available_territorygroups, function() {
                                                        $userLandmarkGroupAvailable.append(Core.DragDrop._generateGroupMarkup(this.territorygroup_id, this.territorygroupname));    
                                                    });    
                                                } else {
                                                    $('#user-landmark-groups-all').prop('disabled', true);
                                                }
                                                
                                                if (! $.isEmptyObject(userData.assigned_territorygroups)) {
                                                    $.each(userData.assigned_territorygroups, function() {
                                                        $userLandmarkGroupAssigned.append(Core.DragDrop._generateGroupMarkup(this.territorygroup_id, this.territorygroupname));
                                                    });                                                    
                                                }
                                                
                                                // init DragDrop containers
                                                Core.DragDrop.init();
                                            }
                                        });
                                    }
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
                    }

                });

                // on user cell and carrier add/updates
                $(document).on('Core.FormElementChanged', '#user-cell, #contact-carrier', function(event, extraParams) {
        
                    // update detail values accordingly with return response values
                    if (! $.isEmptyObject(extraParams)) {
                        var $self = $(this);
                        if (extraParams.response.data.value != null) {
                            if ($self.prop('id') == 'user-cell') {
                                $('#detail-panel').find('.hook-editable-keys').data('userCell', extraParams.response.data.value);
                                if (extraParams.response.data.value == '') {
                                    $('#detail-panel').find('.hook-editable-keys').data('contactCarrier', 0);
                                    Core.Editable.setValue($('#contact-carrier'), '');
                                } else if ($('#contact-carrier').text() == 'Not Set') {
                                    // highlight require carrier field label
                                    if (! $.isEmptyObject(extraParams.response.validation_error)) {
                                        Core.Editable.setError('#contact-carrier');
                                        alert(extraParams.response.validation_error[0]);
                                    }
                                }
                            } else {
                                $('#detail-panel').find('.hook-editable-keys').data('contactCarrier', extraParams.response.data.value);
                                // highlight require sms field label
                                if ($('#user-cell').text() == 'Not Set') {
                                    if (! $.isEmptyObject(extraParams.response.validation_error)) {
                                        Core.Editable.setError('#user-cell');
                                        alert(extraParams.response.validation_error[0]);
                                    }
                                }
                            }
                        }
                    }
                });

                // assign group(s) to user
                $('#user-group-assign').on('click', function() {
                    makeAnAssignment('#user-vehicle-group-assignment',0);
                });

                // unassign group(s) from user
                $('#user-group-unassign').on('click', function() {
                    makeAnAssignment('#user-vehicle-group-assignment',1);
                });

                // assign landmarks(s) to user
                $('#user-landmark-group-assign').on('click', function() {
                    makeAnAssignment('#user-landmark-group-assignment',0);
                });

                // unassign landmark(s) from user
                $('#user-landmark-group-unassign').on('click', function() {
                    makeAnAssignment('#user-landmark-group-assignment',1);
                });

                function makeAnAssignment($eid,$action) {
console.log('makeAnAssignment:'+$eid);

                    var dragDropSelector    = $eid,
                        userId              = $('#detail-panel').find('.hook-editable-keys').data('userPk') || 0,
                        groups              = [],
                        data                = {},
                        $dragDrop           = $(dragDropSelector),
                        $availableContainer = $dragDrop.find('.drag-drop-available').eq(0),
                        $assignedContainer  = $dragDrop.find('.drag-drop-assigned').eq(0)
                    ;

                    Core.DragDrop.disable(dragDropSelector);
                    
                    if ($action===0) {
                        $availableGroups = $(dragDropSelector).find('.drag-drop-available li');
                        if (dragDropSelector == '#user-vehicle-group-assignment') {
                            controller = 'vehicle';
                            method = 'addVehicleGroupToUser';
                        } else if (dragDropSelector == '#user-landmark-group-assignment') {
                            controller = 'landmark';    
                            method = 'addLandmarkGroupToUser';
                        }
                    } else {
                        $availableGroups = $(dragDropSelector).find('.drag-drop-assigned li');
                        if (dragDropSelector == '#user-vehicle-group-assignment') {
                            controller = 'vehicle';
                            method = 'removeVehicleGroupFromUser';
                        } else if (dragDropSelector == '#user-landmark-group-assignment') {
                            controller = 'landmark';    
                            method = 'removeLandmarkGroupFromUser';
                        }
                    }
                    
                    if ($availableGroups.length > 0) {
                        if (userId > 0) {
                            data.user_id = userId;
                            
                            var lastIndex = $availableGroups.length - 1;

                            $.each($availableGroups, function(index, value) {

                                var $that = $(value);
                                
                                if ($that.is('.active')) {

                                    groups.push({id: $that.data('id'), name: $that.find('.drag-drop-name').text()});
console.log('groups:push:'+$that.data('id')+':'+$that.find('.drag-drop-name').text());

                                }
                                
                                if (index === lastIndex) {
                                    
                                    if ($that.length > 0) {
                                        
                                        if (dragDropSelector == '#user-vehicle-group-assignment') {
                                            data.vehiclegroups = groups;
                                        } else if (dragDropSelector == '#user-landmark-group-assignment') {
                                            data.landmarkgroups = groups;
                                        }
                                        
    console.log('ajax:'+controller+':'+method);

                                        $.ajax({
                                            url: '/ajax/'+controller+'/'+method,
                                            type: 'POST',
                                            dataType: 'json',
                                            data: data,
                                            success: function(responseData) {
                                                if (responseData.code === 0) {

                                                    $.ajax({
                                                        url: '/ajax/users/getUserById',
                                                        type: 'POST',
                                                        dataType: 'json',
                                                        data: {
                                                            user_id: userId
                                                        },
                                                        success: function(responseData) {
                                                            if (responseData.code === 0) {
                                                                var userData = responseData.data.user,
                                                                    $modal = $('#modal-user-list');

                                                                // enable access to Vehicle/Landmark Groups tabs for only Active user
                                                                //if (userData.userstatusname != undefined && userData.userstatusname != '' && userData.userstatusname == 'Active') {
                                                                    $userEditNav.find('li a').filter(function() {
                                                                        return (/Vehicle Groups/.test(this.innerHTML) || /Landmark Groups/.test(this.innerHTML));    
                                                                    }).parents('li').removeClass('disabled');    
                                                                /*} else {
                                                                    $userEditNav.find('li a').filter(function() {
                                                                        return (/Vehicle Groups/.test(this.innerHTML) || /Landmark Groups/.test(this.innerHTML));    
                                                                    }).parents('li').addClass('disabled');                                                    
                                                                }*/
                                                                
                                                                var $userVehicleGroupAssignment = $('#user-vehicle-group-assignment'),
                                                                    $userLandmarkGroupAssignment = $('#user-landmark-group-assignment'),
                                                                    $userVehicleGroupAvailable = $userVehicleGroupAssignment.find('.drag-drop-available'),
                                                                    $userVehicleGroupAssigned = $userVehicleGroupAssignment.find('.drag-drop-assigned'),
                                                                    $userLandmarkGroupAvailable = $userLandmarkGroupAssignment.find('.drag-drop-available'),
                                                                    $userLandmarkGroupAssigned = $userLandmarkGroupAssignment.find('.drag-drop-assigned')
                                                                ; 
                                                                
                                                                $('#detail-panel').find('.hook-editable-keys').data('userPk', userId)

                                                                $('#detail-panel').find('.hook-editable-keys').data('contactPk', userData.contact_id);
                                                                // set cell number
                                                                $('#detail-panel').find('.hook-editable-keys').data('user-cell', userData.cellnumber);
                                                                // set cell carrier
                                                                $('#detail-panel').find('.hook-editable-keys').data('contactCarrier', userData.cellcarrier_id);

                                                                //$modal.find('.modal-title').text($self.text()).fadeIn(100);

                                                                // set first name
                                                                Core.Editable.setValue($('#user-firstname'),  userData.firstname);

                                                                // set last name
                                                                Core.Editable.setValue($('#user-lastname'),  userData.lastname);
                                                                
                                                                // set email
                                                                Core.Editable.setValue($('#user-email'),  userData.email);

                                                                // clear error highlighted sms/carrier field label
                                                                Core.Editable.removeError('#user-cell');
                                                                Core.Editable.removeError('#contact-carrier');

                                                                // set cellnumber
                                                                if (userData.formatted_cellnumber != undefined && userData.formatted_cellnumber != 0) {
                                                                    Core.Editable.setValue($('#user-cell'),  userData.formatted_cellnumber);
                                                                    if ( userData.cellcarrier_id == 0) {
                                                                        Core.Editable.setError('#contact-carrier');
                                                                    }
                                                                } else {
                                                                    Core.Editable.setValue($('#user-cell'),  null);
                                                                }

                                                                // set cell carrier
                                                                Core.Editable.setValue($('#contact-carrier'),  userData.cellcarrier_id);

                                                                // set user type
                                                                Core.Editable.setValue($('#user-usertype'),  userData.usertype_id);
                                                                
                                                                // clear list of available & assigned vehicle/landmark groups
                                                                $($userVehicleGroupAvailable.selector+','+$userVehicleGroupAssigned.selector+','+$userLandmarkGroupAvailable.selector+','+$userLandmarkGroupAssigned.selector).html('');
                                                                
                                                                if (! $.isEmptyObject(userData.available_vehiclegroups)) {
                                                                    $.each(userData.available_vehiclegroups, function() {
                                                                        $userVehicleGroupAvailable.append(Core.DragDrop._generateGroupMarkup(this.unitgroup_id, this.unitgroupname));        
                                                                    });    
                                                                } else {
                                                                    $('#user-vehicle-groups-all').prop('disabled', true);
                                                                }
                                                                
                                                                if (! $.isEmptyObject(userData.assigned_vehiclegroups)) {
                                                                    $.each(userData.assigned_vehiclegroups, function() {
                                                                        $userVehicleGroupAssigned.append(Core.DragDrop._generateGroupMarkup(this.unitgroup_id, this.unitgroupname));    
                                                                    });                                                    
                                                                }

                                                                if (! $.isEmptyObject(userData.available_territorygroups)) {
                                                                    $.each(userData.available_territorygroups, function() {
                                                                        $userLandmarkGroupAvailable.append(Core.DragDrop._generateGroupMarkup(this.territorygroup_id, this.territorygroupname));    
                                                                    });    
                                                                } else {
                                                                    $('#user-landmark-groups-all').prop('disabled', true);
                                                                }
                                                                
                                                                if (! $.isEmptyObject(userData.assigned_territorygroups)) {
                                                                    $.each(userData.assigned_territorygroups, function() {
                                                                        $userLandmarkGroupAssigned.append(Core.DragDrop._generateGroupMarkup(this.territorygroup_id, this.territorygroupname));
                                                                    });                                                    
                                                                }
                                                                
                                                                // init DragDrop containers
                                                                Core.DragDrop.init();
                                                            }
                                                        }
                                                    });

                                                } else {
                                                    if (! $.isEmptyObject(responseData.validation_error)) {
                                                        // display validation errors    
                                                    }
                                                    
                                                    // if only some units were able to be added, only detach the ones that were added
                                                    if (! $.isEmptyObject(responseData.data) && ! $.isEmptyObject(responseData.data.failed_groups)) {
                                                        if (! $.isEmptyObject(responseData.data.added_groups)) {
                                                            $.each(responseData.data.added_groups, function(key, value) {
                                                                $assignedContainer.append($availableGroups.filter('[data-id="'+value.id+'"]').detach());
                                                            });
                                                        }
                                                        
                                                        alert(responseData.message);
                                                                
                                                    }

                                                }
                                                
                                                if (! $.isEmptyObject(responseData.message)) {
                                                    Core.SystemMessage.show(responseData.message, responseData.code);
                                                }                                            
                                            }    
                                        });

                                    } else {
                                        Core.SystemMessage.show('Please Make a Selection', 1);
                                    }        
                                }
                            });
                        } else {
                            Core.SystemMessage.show('Invalid user id', 1);
                        }
                    } else {
                        Core.SystemMessage.show('There are no available groups to be assigned to this user', 1);
                    }
                        
                    Core.DragDrop.enable(dragDropSelector);

                }

                $('.drag-drop-toggle').on('change', function() {
console.log('.drag-drop-toggle');
                    var $self               = $(this),
                        $label              = $self.siblings('label'),
                        isChecked           = $self.is(':checked'),
                        dragDropSelector    = '#'+$self.closest('.row').find('.drag-drop').prop('id'),
                        userId              = $('#detail-panel').find('.hook-editable-keys').data('userPk') || 0,
                        groups              = [],
                        data                = {},
                        $dragDrop           = $(dragDropSelector),
                        $availableContainer = $dragDrop.find('.drag-drop-available').eq(0),
                        $assignedContainer  = $dragDrop.find('.drag-drop-assigned').eq(0)
                    ;

                    if (isChecked) {

                        Core.DragDrop.disable(dragDropSelector);
                        $label.removeClass('text-muted');
                        
                        $availableGroups = $(dragDropSelector).find('.drag-drop-available li');
                        
                        if (dragDropSelector == '#user-vehicle-group-assignment') {
                            controller = 'vehicle';
                            method = 'addVehicleGroupToUser';
                        } else if (dragDropSelector == '#user-landmark-group-assignment') {
                            controller = 'landmark';    
                            method = 'addLandmarkGroupToUser';
                        }

                        if ($availableGroups.length > 0) {
                            if (userId > 0) {
                                data.user_id = userId;
                                
                                var lastIndex = $availableGroups.length - 1;
    
                                $.each($availableGroups, function(index, value) {
                                    var $that = $(value);
                                    
                                    groups.push({id: $that.data('id'), name: $that.find('.drag-drop-name').text()});
                                    
                                    if (index === lastIndex) {
                                        
                                        if (dragDropSelector == '#user-vehicle-group-assignment') {
                                            data.vehiclegroups = groups;
                                        } else if (dragDropSelector == '#user-landmark-group-assignment') {
                                            data.landmarkgroups = groups;
                                        }
                                        
                                        $.ajax({
                                            url: '/ajax/'+controller+'/'+method,
                                            type: 'POST',
                                            dataType: 'json',
                                            data: data,
                                            success: function(responseData) {
                                                if (responseData.code === 0) {
                                                    // remove all available groups
                                                    $assignedContainer.append($availableGroups.detach());
                                                } else {
                                                    if (! $.isEmptyObject(responseData.validation_error)) {
                                                        // display validation errors    
                                                    }
                                                    
                                                    // if only some units were able to be added, only detach the ones that were added
                                                    if (! $.isEmptyObject(responseData.data) && ! $.isEmptyObject(responseData.data.failed_groups)) {
                                                        if (! $.isEmptyObject(responseData.data.added_groups)) {
                                                            $.each(responseData.data.added_groups, function(key, value) {
                                                                $assignedContainer.append($availableGroups.filter('[data-id="'+value.id+'"]').detach());
                                                            });
                                                        }
                                                        
                                                        alert(responseData.message);
                                                                
                                                    }
                                                }
                                                
                                                if (! $.isEmptyObject(responseData.message)) {
                                                    Core.SystemMessage.show(responseData.message, responseData.code);
                                                }                                            
                                            }    
                                        });
                                    }        
                                });
                            } else {
                                Core.SystemMessage.show('Invalid user id', 1);
                            }
                        } else {
                            Core.SystemMessage.show('There are no available groups to be assigned to this user', 1);
                        }
                    } else {

                        Core.DragDrop.enable(dragDropSelector);
                        $label.addClass('text-muted');
                        
                        // disable check box if there are no available groups
                        if ($(dragDropSelector).find('.drag-drop-available li').length == 0) {
                            $self.prop('disabled', true);
                        }
                    }

                });

                // prevent access to Vehicle/Landmark Groups tabs for non-Active user                
                $userEditNav.on('click', 'li', function() {
                    var $self = $(this);
                    
                    if ($self.is('.disabled')) {
                        return false;
                    }    
                });
            },

            initExportUsers: function () {
                
                var $body = $('body');

                /**
                 * Export Filter Landmark List table
                 *
                 */
                $body.on('click', '#popover-user-list-export-confirm', function() {
                    var $secondaryPanelPagination   = $('#secondary-panel-pagination');
                    var searchUserString        = $('#text-user-search').val().trim();
                    var search_string               = searchUserString;
                    var user_role            = 'all';//$('#user-role-filter').val().trim();

                    if (search_string != '') {
                        window.location = '/ajax/users/exportFilteredUsersList/string_search/' + search_string + '/All';
                    } else {
                        window.location = '/ajax/users/exportFilteredUsersList/group_filter/' + user_role;
                    }
                });   
            }
        },
        
        Popover: {
            
            init: function() {
                
                var $firstName          = $('#user-first-name-new'),
                    $lastName           = $('#user-last-name-new'),
                    $email              = $('#user-email-new'),
                    $userType           = $('#user-type-new'),
                    $popover            = $('#popover-user-new'),
                    $username           = $('#user-username-new'),
                    $password           = $('#user-password-new'),
                    $passwordConfirm    = $('#user-confirm-new'),
                    $cellnumber         = $('#user-cell-new'),
                    $carrier            = $('#user-carrier-new')
                ;
                                
                // Add new user
                $(document).on('click', '#popover-new-user-confirm', function() {
                    
                    var fname           = $firstName.val(),
                        lname           = $lastName.val(),
                        email           = $email.val(),
                        userType        = $userType.val(),
                        username        = $username.val(),
                        password        = $password.val(),
                        passwordConfirm = $passwordConfirm.val(),
                        cellnumber      = $cellnumber.val(),
                        cellcarrier_id  = $carrier.val(),
                        validation      = [],
                        data            = {}
                    ;
                    
                    // validate first name & last name
                    if (fname != '' && lname != '') {
                        data.first_name = fname;
                        data.last_name = lname;
                    } else {
                        validation.push('First Name and Last Name cannot be blank');
                    }
                    
                    // validate email
                    if (email != '') {
                        var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                        
                        if (filter.test(email)) {
                            data.email = email;    
                        } else {
                            validation.push('Invalid E-Mail Address');
                        }                        
                    } else {
                        validation.push('E-Mail Address cannot be blank');                        
                    }
                    
                    // validate user type
                    if (userType != '') {
                        data.usertype_id = userType;     
                    } else {
                        validation.push('Type is invalid');
                    }
                    
                    //validate username
                    if (username != '') {
                        data.username = username;
                    } else {
                        validation.push('Username cannot be blank');
                    }
                    
                    //validate password
                    if (password != '' && password.length > 7) {
                        if (passwordConfirm !== '' && passwordConfirm.length > 7 && passwordConfirm === password) {
                            data.password = password;
                        } else {
                            validation.push('The Confirm password is incorrect');
                        }
                    } else {
                        validation.push('Password has to be a minimun of 8 characters');
                    }
                    
                    //validate cellphone & carrier if provided
                    if (cellnumber != '' && cellcarrier_id != '') {
                        data.cellnumber = cellnumber;
                        data.cellcarrier_id = cellcarrier_id;
                    }
                    
                    if (validation.length == 0) {
                        $.ajax({
                            url: '/ajax/users/addUser',
                            type: 'POST',
                            dataType: 'json',
                            data: data,
                            success: function(responseData) {
                                if (responseData.code === 0) {
                                    // close 'Add User' popover
                                    $('#popover-new-user-cancel').trigger('click');
                                    
                                    // redraw table
                                    Users.User.DataTable.userList.fnStandingRedraw();
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
                
                // reset all options in the 'Add User' popover to their corresponding default values (their first option)
                $popover.on('hide.bs.popover', function() {
                    // reset 'Add User' popover fields/options only if the popover is opened 
                    // (i.e. a 'hide' event triggered from the opening of other popovers shouldn't reset this popover's fields)
                    if ($('#user-first-name-new').is(':visible')) {
                        var $popoverContainer = $('.popover-content');
                        $popoverContainer.find('input[type="text"], input[type="password"]').val('');
                        
                        $uls = $popoverContainer.find('ul');
                        
                        $.each($uls, function() {
                            $(this).find('li a').eq(0).trigger('click');                      
                        });
                    }
                });
                
                // Delete user
                $(document).on('click', '#popover-user-delete-confirm', function() {
                    var userId = $('#detail-panel').find('.hook-editable-keys').data('userPk'),
                        $modal = $('#modal-user-list')
                    ;
        
                    if (userId != undefined && userId != '') {
                        $.ajax({
                            url: '/ajax/users/deleteUser',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                user_id: userId
                            },
                            success: function(responseData) {
                                if (responseData.code === 0) {
                                    // close 'Edit User' popover
                                    $('#popover-user-delete-cancel').trigger('click');
                                    
                                    // close 'Edit User' modal
                                    $modal.find('.modal-footer button').trigger('click');
                                } else {
                                    if (! $.isEmptyObject(responseData.validation_errors)) {
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
            }

        },
        

        SecondaryPanel: {
        
            initUserSearch: function() {
                var $userSearch     = $('#text-user-search');
                var $userSearchGo   = $('#user-search-go');
                var $userTypeFilter = $('#user-type-filter');
                var $userStatusFilter = $('#user-status-filter');

                /**
                 *
                 * On keyup when searching alerts using search string 
                 *
                 */
                $userSearch.on('keyup', function () {
                    
                    // get current search string
                    var searchUserString = $userSearch.val().trim();

                    if (searchUserString.length > 1) {
                        Users.User.DataTable.userList.fnDraw();
                    } else if (searchUserString.length == 0) {
                        Users.User.DataTable.userList.fnDraw();
                    }

                    $('#user-type-filter').val('ALL').text('All');
                    $('#user-status-filter').val('ALL').text('All');
                });

                /**
                 *
                 * On Search Button Click when searching alerts using search string 
                 *
                 */
                $userSearchGo.on('click', function () {
                    // get current search string
                    var searchUserString = $userSearch.val().trim();

                    if (searchUserString != '') {
                        Users.User.DataTable.userList.fnDraw();
                    }

                    $('#user-type-filter').val('ALL').text('All');
                    $('#user-status-filter').val('ALL').text('All');
                });
                
                /**
                 *
                 * On Change of User Type Filtering on user filter search
                 *
                 */
                $userTypeFilter.on('Core.DropdownButtonChange', function() {

                    $('#text-user-search').val('');

                    // clear out the search box before redrawing table
                    Users.User.DataTable.userList.fnDraw();
                });

                /**
                 *
                 * On Change of User Status Filtering on user filter search
                 *
                 */
                $userStatusFilter.on('Core.DropdownButtonChange', function() {

                    $('#text-user-search').val('');

                    // clear out the search box before redrawing table
                    Users.User.DataTable.userList.fnDraw();
                });
            }
        }

    },

    UserType: {

        DataTable: {

            init: function() {
console.log('Users.UserType.DataTable');
                Users.UserType.DataTable.typeList = Core.DataTable.init('user-type-list-table', 20, {
                    'oLanguage': {
                        'sInfo': 'Showing _START_ to _END_ of _TOTAL_ User Types'
                    },
                    'bServerSide': true,
                    'sAjaxSource':   '/ajax/users/getFilteredUserTypeList',
                    'aoColumns': [
                        { 'mDataProp': 'usertype' },
                        { 'mDataProp': 'total_count' },
                        { 'mDataProp': 'editable' }
                    ],
                    /*'aaSorting': [
                        [0,'asc'],
                        [1,'asc']
                    ],*/
                    'aoColumnDefs': [
                        { 'sClass': 'col-type no-wrap',  'aTargets': [0] },
                        { 'sClass': 'col-count no-wrap', 'aTargets': [1] },
                        { 'sClass': 'col-editable no-wrap', 'aTargets': [2] }
                    ],
                    'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
                        // set <a> tag name and add data-landmark-id attribute to tr
                        $('td:eq(0)', nRow).html('<a href="#">'+aData.usertype+'</a>');
                        if(aData.editable == 'no') {
                            $('td:eq(2)', nRow).html('<span class="user-type-editable user-type-preset glyphicon glyphicon-ban-circle"></span>');
                        } else {
                            $('td:eq(2)', nRow).html('<span class="user-type-editable user-type-custom glyphicon glyphicon-ok"></span>');
                        }

                        $(nRow).data('userTypeId', aData.usertype_id);

                        return nRow;
                    },
                    'fnServerParams': function (aoData) {

                        //var user_role = $('#user-role-filter').val().trim();
                        var search_string = '';
                        //var filter_type = 'string_search';

                        var searchUserTypeString = $('#text-user-type-search').val().trim();
                        if (typeof(searchUserTypeString) != 'undefined' && searchUserTypeString != '')
                        {
                            search_string = searchUserTypeString;
                            //filter_type = 'string_search';
                        }

                        //aoData.push({name: 'user_role', value: user_role});
                        aoData.push({name: 'search_string', value: search_string});
                        //aoData.push({name: 'filter_type', value: filter_type});
                    }
                });
            }

        },

        Popover: {
            
            init: function() {
                
                var $userTypeName  = $('#user-type-name-new'),
                    $popover    = $('#popover-user-type-new')
                ;
                                
                // Add new user
                $(document).on('click', '#popover-new-user-type-confirm', function() {
                    
                    var usertype_name   = $userTypeName.val(),
                        validation      = [],
                        data            = {}
                    ;

                    // validate first name & last name
                    if (usertype_name != '') {
                        data.usertype = usertype_name;
                    } else {
                        validation.push('User Type cannot be blank');
                    }
                    
console.log('new user type:'+$userTypeName.val()+':'+usertype_name+':'+data.usertype);

                    if (validation.length == 0) {
                        $.ajax({
                            url: '/ajax/users/addUserType',
                            type: 'POST',
                            dataType: 'json',
                            data: data,
                            success: function(responseData) {
                                if (responseData.code === 0) {
                                    // close 'Add User' popover
                                    $('#popover-new-user-type-cancel').trigger('click');
                                    
                                    // redraw table
                                    Users.UserType.DataTable.typeList.fnStandingRedraw();
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

                // Delete usertype
                $(document).on('click', '#popover-user-type-delete-confirm', function() {
                    var userTypeId = $('#detail-panel').find('.hook-editable-keys').data('userTypePk'),
                        $modal = $('#modal-user-type-list')
                    ;
        
                    if (userTypeId != undefined && userTypeId != '') {
                        $.ajax({
                            url: '/ajax/users/deleteUserType',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                usertype_id: userTypeId
                            },
                            success: function(responseData) {
                                if (responseData.code === 0) {
                                    // close 'Delete User Type' popover
                                    $('#popover-user-type-delete-cancel').trigger('click');

                                    // close 'Edit User Type' modal
                                    $('#user-type-permission-close').trigger('click');
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
                });
            }

        },

        SecondaryPanel: {

            initUserTypeSearch: function() {
console.log('Users.UserType.SecondaryPanel.initUserTypeSearch');

                var $userUserTypeSearch     = $('#text-user-type-search');
                var $userUserTypeSearchGo   = $('#user-type-search-go');
    
                /**
                 *
                 * On keyup when searching alerts using search string 
                 *
                 */
                $userUserTypeSearch.on('keyup', function () {
                    
                    // get current search string
                    var searchUserTypeString = $userUserTypeSearch.val().trim();
    
                    if (searchUserTypeString.length > 1) {
                        Users.UserType.DataTable.typeList.fnDraw();
                    } else if (searchUserTypeString.length == 0) {
                        Users.UserType.DataTable.typeList.fnDraw();
                    }
    
                });
    
                /**
                 *
                 * On Search Button Click when searching alerts using search string 
                 *
                 */
                $userUserTypeSearchGo.on('click', function () {
                    // get current search string
                    var searchUserTypeString = $userUserTypeSearch.val().trim();
    
                    if (searchUserTypeString != '') {
                        Users.UserType.DataTable.typeList.fnDraw();
                    }
                });
            }

        },

        Modal: {

            init: function() {
console.log('Users.UserType.Modal.init');

                var $currentAssociatedPermissions = [];

                $(document).on('click', '.col-type a', function() {

console.log('Users.UserType.Modal.init:click:.col-type a');

                    var $self         = $(this),
                        $trNode       = $self.closest('tr'),
                        userTypeId    = $trNode.attr('id').split('-')[2],
                        userTypeLabel = $self.text(),
                        $modal        = $('#modal-user-type-list'),
                        $alert        = $('#user-type-preset-alert'),
                        $checkboxes   = $modal.find('.user-type-permission'),
                        $namePanels   = $('.user-type-name-panel')
                    ;

                    $currentAssociatedPermissions = [];     // for associated permissions

                    // reset displayed permissions tabs
                    if (typeof($('.master-detail-list-master').find('li').eq(0)) != 'undefined') {
                        // show first permission category on opening detail
                        $masterCategoryList = $('.master-detail-list-master').find('li').eq(0).click()
                        // clear all checkboxes
                        $.each($('.master-detail-list-detail').find('li'), function() {
                            $(this).find(':checkbox').prop('checked', false);
                        });
                    }

                    if (userTypeId != undefined) {
                        //set the usertype id for use when saving
                        $('#detail-panel').find('.hook-editable-keys').eq(0).data('user-type-pk', userTypeId);
                    
                        $.ajax({
                            url: '/ajax/users/getUserTypeById',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                usertype_id: userTypeId
                            },
                            success: function(responseData) {
console.log('Users.UserType.Modal.init:/ajax/users/getUserTypeById:'+responseData.code+':'+responseData.message);
                                if (responseData.code === 0) {
                                    var usertypeData = responseData.data;

                                    // if Preset/canned User Type
                                    if ($trNode.find('.user-type-editable').eq(0).is('.user-type-preset')) {
                                        $alert.show();
                                        $('#user-type-name-static-panel').show();
                                        $('#user-type-name-static').text(userTypeLabel);
                                        $('#permission-toggle-all').prop('disabled', true);
                                        $checkboxes.prop('disabled', true);
                                        $('#user-type-permission-save').hide();
                                        $('#user-type-permission-close').show();
                                        $('#user-type-permission-close-alt').show();
                                        $('#user-type-more-options-toggle').hide();
                                    } else {
                                        $('#user-type-name-editable-panel').show();
                                        Core.Editable.setValue($('#user-type-name'), userTypeLabel);
                                        $('#permission-toggle-all').prop('disabled', false);
                                        $checkboxes.prop('disabled', false);
                                        $('#user-type-permission-save').show();
                                        $('#user-type-permission-close').show();
                                        $('#user-type-permission-close-alt').hide();

                                        // show delete option only if usertype has no user association to this type
                                        $('#user-type-more-options-toggle').hide();
                                        if ($trNode.find('.col-count').eq(0).text() == '0') {
                                            $('#user-type-more-options-toggle').show();
                                        }
                                    }

                                    Core.Dialog.launch($modal.selector, 'User Type', {
                                        width: '700px'
                                    },
                                    {
                                        hidden: function() {
                                            // redraw usertype list table
                                            Users.UserType.DataTable.typeList.fnStandingRedraw();
                                            
                                            $alert.hide();
                                            $namePanels.hide();
                                            $checkboxes.closest('li').off('click');
                                            $checkboxes.attr('checked', false);

                                            // hide 'More Options' section
                                            var $toggle = $('#user-type-more-options-toggle').find('small');
                                            if ($toggle.text() == 'Show Less Options') {
                                                $toggle.trigger('click');    
                                            }
                                        },
                                        show: function() {
                
                                            //$modal.find('.modal-title').text('')

                                        },
                                        shown: function() {

                                            // if permissions not empty, then check associated permission for each category for this usertype
                                            if (! $.isEmptyObject(usertypeData.permissions)) {
                                                $.each(usertypeData.permissions, function(p_key, p_value) {
                                                    $('#'+p_value.permissioncategoryname+'-permission-'+p_value.permission_id).prop('checked', true).removeClass('locked');

                                                    // if this permission is locked, disable and lock this permission checkbox
                                                    if(p_value.locked == 1 && $('#'+p_value.permissioncategoryname+'-permission-'+p_value.permission_id).prop('checked')) {
                                                        $('#'+p_value.permissioncategoryname+'-permission-'+p_value.permission_id).prop('disabled', true).addClass('locked');
                                                    }

                                                    // save usertype associated permissions for comparison when saving
                                                    $currentAssociatedPermissions.push(p_value.permission_id);
                                                });
                                            }
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

                // on usertype permissions save
                $(document).on('click', '#user-type-permission-save', function() {
                    
                    var userTypeId          = $('#detail-panel').find('.hook-editable-keys').eq(0).data('user-type-pk'),
                        validation          = [],
                        assign_permission   = [],
                        remove_permission   = [],
                        data                = {};

                    $permissions = $('.master-detail-list-detail').find('li');
                    $.each($permissions, function() {
                        var $self       = $(this),
                        $checkbox       = $self.find(':checkbox'),
                        permission_id   = $(this).find('input').attr('id').split('-')[2];

                        if ($checkbox.is(':checked')) {
                            if (permission_id != '' && ($.inArray(permission_id, $currentAssociatedPermissions) == -1)) {
                                // if permission id is valid and is not currently associated, but now is checked so add permission id to be associated
                                assign_permission.push(permission_id);
                            }
                        } else {
                            if (permission_id != '' && ($.inArray(permission_id, $currentAssociatedPermissions) > -1)) {
                                // if permission id is valid and was associated, but now is not checked so add permission id to be removed
                                remove_permission.push(permission_id);
                            }
                        }
                    });

                    if (userTypeId != '') {
                        data.usertype_id = userTypeId;
                    } else {
                        validation.push('Invalid User Type');
                    }

                    if ($.isEmptyObject(assign_permission) && $.isEmptyObject(remove_permission)) {
                        // if there is no update made, throw message 
                        validation.push('There is no change made');
                    } else {
                        if (! $.isEmptyObject(assign_permission)) {
                            data.permission_add = assign_permission;
                        }
    
                        if (! $.isEmptyObject(remove_permission)) {
                            data.permission_remove = remove_permission;
                        }
                    }

                    // must be a valid usertype
                    if (validation.length == 0) {
                        $.ajax({
                            url: '/ajax/users/addUserTypePermission',
                            type: 'POST',
                            dataType: 'json',
                            data: data,
                            success: function(responseData) {
console.log('Users.UserType.Modal:click:#user-type-permission-save:/ajax/users/addUserTypePermission:'+responseData.code+':'+responseData.message);
                                if (responseData.code === 0) {
                                    // close usertype permission
                                    $('#user-type-permission-close').trigger('click');
                                    Users.UserType.Modal.init();
   
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

                /* TOGGLE ALL */
                var $permissionToggleAll = $('#permission-toggle-all'),
                    $masterDetail        = $('#user-type-master-detail'),
                    $detailLists         = $masterDetail.find('.master-detail-list-detail')
                ;

                // toggle reset on category change
                $masterDetail.on('Core.MasterDetailList.detailUpdated', function() {

                    $permissionToggleAll.prop('checked', false);
                });

                $detailLists.find(':checkbox').on('change', function() {
                    var $self     = $(this),
                        isChecked = $self.prop('checked')
                    ;

                    if ( ! isChecked) {
                        $permissionToggleAll.prop('checked', false);
                    }

                });


                // perform toggle
                $permissionToggleAll.on('change', function() {

                    var $self     = $(this),
                        isChecked = $self.prop('checked'),
                        $activeDetailList = $detailLists.filter(':visible')
                    ;

                    // find current checkbox list that are not locked
                    $activeDetailList.find(':checkbox').filter(':not(.locked)').prop('checked', isChecked);
                });

                // More Options
                $(document).on('click', '#user-type-more-options-toggle', function() {
                    
                    var $optionsToggle = $(this),
                        $toggleLabel   = $optionsToggle.find('small')
                    ;
                    
                    if ($toggleLabel.text() == 'Show More Options') {
                        $toggleLabel.text('Show Less Options');
                    } else {
                        $toggleLabel.text('Show More Options');
                    }

                    $('#user-type-more-options').slideToggle(300);

                });

            }
            
            
            
        }
    }


});