/*

    Contact JS

    File:       /assets/js/contact.js
    Author:     Tom Leach
*/

$(document).ready(function() {

    Contact.isLoaded();

    /**
     *
     * Common Functionality for Contact Pages
     *
     */

    Contact.DataTables.init();
    Contact.initModal();
    Contact.SecondaryPanel.initContactSearch();
    Contact.initContactMethod();
    Contact.initGroupAssignment();
    Contact.initContactGroupAssignmentHelp();
    Contact.initAddContact();
    Contact.initAddContactGroup();
    Contact.GroupAssignment.initOptionToggle();


    /**
     *
     * Page Specific Functionality
     *
     */
    switch (Core.Environment.context()) {


    }

});

var Contact = {};

jQuery.extend(Contact, {


    isLoaded: function() {

        Core.log('Contact JS Loaded');
    },

    DataTables: {

        init: function() {

            Contact.DataTables.contactList = Core.DataTable.init('contact-list-table', 20, {
                'oLanguage': {
                    'sInfo': 'Showing _START_ to _END_ of _TOTAL_ Contacts'
                },
                'bServerSide': true,
                'sAjaxSource':   '/ajax/contact/getFilteredContacts',
                'aoColumns': [
                    { 'mDataProp': 'contactname' },
                    { 'mDataProp': 'contactgroupname' },
                    { 'mDataProp': 'contact_method' },
                    { 'mDataProp': 'details' }
                ],
                'aoColumnDefs': [
                    { 'sClass': 'col-contact-name no-wrap',    'aTargets': [0] },
                    { 'sClass': 'col-contact-group no-wrap',   'aTargets': [1] },
                    { 'sClass': 'col-contact-type no-wrap',    'aTargets': [2] },
                    { 'sClass': 'col-contact-details no-wrap', 'aTargets': [3] }
                ],
                'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
                    // set <a> tag name and add data-landmark-id attribute to tr
                    $('td:eq(0)', nRow).html('<a href="#">'+aData.contactname+'</a>');
                    $(nRow).data('contactId', aData.contact_id);

                    return nRow;
                },
                'fnServerParams': function (aoData) {

                    var contactgroup_id = $('#select-contact-group-filter').val().trim();
                    var contact_type    = $('#select-contact-type-filter').val().trim();
                    var search_string   = '';
                    var filter_type     = 'group_filter';
                    var $searchContact  = $();

                    if (Core.Environment.context() == 'alert/contact' || Core.Environment.context() == 'report/contact') {
                        $searchContact = $('#text-alert-search');
                    }

                    var searchContactString = $searchContact.val().trim();

                    if (typeof(searchContactString) != 'undefined' && searchContactString != '')
                    {
                        search_string   = searchContactString;
                        contactgroup_id = 'All';
                        contact_type    = 'All';
                        filter_type     = 'string_search';

                    }

                    aoData.push({name: 'contactgroup_id', value: contactgroup_id});
                    aoData.push({name: 'search_string', value: search_string});
                    aoData.push({name: 'contact_type', value: contact_type});
                    aoData.push({name: 'filter_type', value: filter_type});
                }
            });

            Contact.DataTables.contactGroups = Core.DataTable.init('contact-group-table', 20, {
                    'oLanguage': {
                        'sInfo': 'Showing _START_ to _END_ of _TOTAL_ Contact Groups'
                    },
                    'bServerSide': true,
                    'sAjaxSource':   '/ajax/contact/getFilteredContactGroups',
                    'aoColumns': [
                        { 'mDataProp': 'contactgroupname' },
                        { 'mDataProp': 'contact_method' },
                        { 'mDataProp': 'total_contact' }
                    ],
                    'aoColumnDefs': [
                        { 'sClass': 'col-contact-group no-wrap',    'aTargets': [0] },
                        { 'sClass': 'col-contact-type no-wrap',     'aTargets': [1] },
                        { 'sClass': 'col-contact-contacts no-wrap', 'aTargets': [2] }
                    ],
                    'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
                        // set <a> tag name and add data-landmark-id attribute to tr
                        $('td:eq(0)', nRow).html('<a href="#">'+aData.contactgroupname+'</a>');
                        $(nRow).data('contactgroupId', aData.contactgroup_id);

                        return nRow;
                    },
                    'fnServerParams': function (aoData) {

                        var method_type     = $('#select-contact-type-filter').val().trim();
                        var search_string   = '';
                        var filter_type     = 'method_filter';
                        var $searchContact  = $();

                        if (Core.Environment.context() == 'alert/contact' || Core.Environment.context() == 'report/contact') {
                            $searchContact = $('#text-alert-search');
                        }

                        var searchContactGroupString = $searchContact.val().trim();

                        if (typeof(searchContactGroupString) != 'undefined' && searchContactGroupString != '')
                        {
                            search_string           = searchContactGroupString;
                            method_type             = 'All';
                            filter_type             = 'string_search';

                        }

                        aoData.push({name: 'search_string', value: search_string});
                        aoData.push({name: 'method_type', value: method_type});
                        aoData.push({name: 'filter_type', value: filter_type});
                    }
                });

        }
    },

    initModal: function() {

        // edit contact
        $(document).on('click', '.col-contact-name a', function() {

            var $self = $(this),
                $trNode = $self.closest('tr'),
                contactId = $trNode.attr('id').split('-')[2],
                $modal = $('#modal-contact')
            ;

            if (contactId != undefined && contactId != '') {
                $.ajax({
                    url: '/ajax/contact/getContactById',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        contact_id: contactId
                    },
                    success: function(responseData) {
                        if (responseData.code === 0) {
                            var contactData = responseData.data.contact;
                            if (! $.isEmptyObject(contactData)) {
                                Core.Dialog.launch($modal.selector, 'Test', {
                                    width: '500px'
                                },
                                {
                                    hidden: function() {
                                        // redraw contact table
                                        Contact.DataTables.contactList.fnStandingRedraw();

                                        // hide Delete section
                                        var $toggle = $('#contact-more-options-toggle').find('small');
                                        if ($toggle.text() == 'Show Less Options') {
                                            $toggle.trigger('click');
                                        }
                                    },
                                    show: function() {

                                        $modal.find('.modal-title').text('').hide();

                                    },
                                    shown: function() {

                                        $modal.find('.modal-title').text($self.text()).fadeIn(100);
                                        $('#detail-panel').find('.hook-editable-keys').data('contactPk', contactId);
                                        $('#detail-panel').find('.hook-editable-keys').data('contactSms', contactData.cellnumber);
                                        $('#detail-panel').find('.hook-editable-keys').data('contactCarrier', contactData.cellcarrier_id);

                                        // set contact name
                                        Core.Editable.setValue($('#contact-first-name'), contactData.firstname);
                                        Core.Editable.setValue($('#contact-last-name'), contactData.lastname);

                                        // set email
                                        Core.Editable.setValue($('#contact-email'), contactData.email);

                                        // clear error highlighted sms/carrier field label
                                        Core.Editable.removeError('#contact-sms');
                                        Core.Editable.removeError('#contact-carrier');

                                        // set cellnumber
                                        if (contactData.formatted_cellnumber != undefined && contactData.formatted_cellnumber != 0) {
                                            Core.Editable.setValue($('#contact-sms'), contactData.formatted_cellnumber);
                                            if (contactData.cellcarrier_id == 0) {
                                                Core.Editable.setError('#contact-carrier');
                                            }
                                        } else {
                                            Core.Editable.setValue($('#contact-sms'),  null);
                                        }

                                        // set cell carrier
                                        Core.Editable.setValue($('#contact-carrier'), contactData.cellcarrier_id);
                                    }
                                });
                            }
                        } else {
                            if (! $.isEmptyObject(responseData.validaton_error)) {
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

        // edit contact group
        $(document).on('click', '.col-contact-group a', function() {

            var $self = $(this),
                $trNode = $self.closest('tr'),
                contactGroupId = $trNode.attr('id').split('-')[2],
                $modal = $('#modal-contact-group')
            ;

            if (contactGroupId != undefined && contactGroupId != '') {
                $.ajax({
                    url: '/ajax/contact/getContactGroupById',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        contactgroup_id: contactGroupId
                    },
                    success: function(responseData) {
                        if (responseData.code === 0) {
                            var contactData = responseData.data.contactgroup;
                            //Core.log(contactData);
                            if (! $.isEmptyObject(contactData)) {
                                Core.Dialog.launch($modal.selector, 'Test', {
                                    width: '612px'
                                },
                                {
                                    hidden: function() {
                                        // redraw contact table
                                        Contact.DataTables.contactGroups.fnStandingRedraw();

                                        // hide 'More Options' section
                                        var $toggle = $('#contact-group-more-options-toggle').find('small');
                                        if ($toggle.text() == 'Show Less Options') {
                                            $toggle.trigger('click');
                                        }
                                    },
                                    show: function() {

                                        $modal.find('.modal-title').text('').hide();

                                    },
                                    shown: function() {

                                        $('#detail-panel').find('.hook-editable-keys').data('contactGroupPk', contactGroupId);

                                        $modal.find('.modal-title').text($self.text()).fadeIn(100);
                                        Core.Editable.setValue($('#contact-group-name'),  $modal.find('.modal-title').text());

                                        var $contactGroupAvailable = $('#contact-groups-available'),
                                            $contactGroupAssigned = $('#contact-groups-assigned'),
                                            $contactGroupAvailableList = $contactGroupAvailable.find('.list-group'),
                                            $contactGroupAssignedList = $contactGroupAssigned.find('.list-group')
                                        ;

                                        // clear available and assigned group list
                                        $($contactGroupAvailable.selector+','+$contactGroupAssigned.selector).find('.list-group').html('');

                                        // create available contact list
                                        if (! $.isEmptyObject(contactData.assigned_contacts)) {
                                            var assigned_contacts = contactData.assigned_contacts;
                                            //Core.log(assigned_contacts);
                                            $.each(assigned_contacts, function() {
                                                $contactGroupAssignedList.append(Contact.GroupAssignment.generateGroupMarkup(this.contact_id, this.contactname, this.sms_enabled, this.email_enabled));
                                            });
                                        }

                                        // create assigned contact list
                                        if (! $.isEmptyObject(contactData.available_contacts)) {
                                            var available_contacts = contactData.available_contacts;
                                            //Core.log(available_contacts);
                                            $.each(available_contacts, function() {
                                                $contactGroupAvailableList.append(Contact.GroupAssignment.generateGroupMarkup(this.contact_id, this.contactname, this.sms_enabled, this.email_enabled));
                                            });
                                        }

                                        Contact.initGroupAssignment();
                                    }
                                });
                            }
                        } else {
                            if (! $.isEmptyObject(responseData.validaton_error)) {
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

        // more options

        var $optionsToggle = $('#contact-more-options-toggle'),
            $toggleLabel   = $optionsToggle.find('small')
        ;

        $optionsToggle.on('click', function() {

            if ($toggleLabel.text() == 'Show More Options') {
                $toggleLabel.text('Show Less Options');
            } else {
                $toggleLabel.text('Show More Options');
            }

            $('#contact-more-options').slideToggle(300);

        });

        // delete contact
        $(document).on('click', '#popover-contact-delete-confirm', function() {
            var contactId = $('#detail-panel').find('.hook-editable-keys').data('contactPk'),
                $modal = $('#modal-contact')
            ;

            if (contactId != undefined && contactId != '') {
                $.ajax({
                    url: '/ajax/contact/deleteContact',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        contact_id: contactId
                    },
                    success: function(responseData) {
                        if (responseData.code === 0) {
                            // close 'Delete Contact' popover
                            $('#popover-contact-delete-cancel').trigger('click');

                            // close 'Edit Contact' modal
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

        // delete contact group
        $(document).on('click', '#popover-contact-group-delete-confirm', function() {
            var contactGroupId = $('#detail-panel').find('.hook-editable-keys').data('contactGroupPk'),
                $modal = $('#modal-contact-group')
            ;

            if (contactGroupId != undefined && contactGroupId != '') {
                $.ajax({
                    url: '/ajax/contact/deleteContactGroup',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        contactgroup_id: contactGroupId
                    },
                    success: function(responseData) {
                        if (responseData.code === 0) {
                            // close 'Delete Contact Group' popover
                            $('#popover-contact-group-delete-cancel').trigger('click');

                            // close 'Edit Contact' modal
                            $modal.find('.modal-footer button').trigger('click');

                            // update Contact Group dropdown in fitler
                            Contact.SecondaryPanel.updateContactGroupFilterDropdown();
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

        // on contact group name changed, update contact group filter drop down
        $('#contact-group-name').on('Core.FormElementChanged', function() {
            // update Contact Group dropdown in fitler
            Contact.SecondaryPanel.updateContactGroupFilterDropdown();
        });

        // on contact sms and carrier add/updates
        $(document).on('Core.FormElementChanged', '#contact-sms, #contact-carrier', function(event, extraParams) {

            // update detail values accordingly with return response values
            if (! $.isEmptyObject(extraParams)) {
                var $self = $(this);
                if (extraParams.response.data.value != null) {
                    if ($self.prop('id') == 'contact-sms') {
                        $('#detail-panel').find('.hook-editable-keys').data('contactSms', extraParams.response.data.value);
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
                        if ($('#contact-sms').text() == 'Not Set') {
                            if (! $.isEmptyObject(extraParams.response.validation_error)) {
                                Core.Editable.setError('#contact-sms');
                                alert(extraParams.response.validation_error[0]);
                            }
                        }
                    }
                }
            }

        });

    },

    initGroupAssignment: function() {

        $('.contact-group-method').not('.disabled').draggable({
            containment: '#contact-group-assignment-container',
            revert:      true,
            revertDuration: 0,
            //snap:        '.contact-group-droppable',
            scroll:      false,
            zIndex:      100,
            helper:      'clone',
            appendTo:    '#contact-group-assignment-container'
        });


        $('.contact-group-droppable').droppable({
            accept: '.contact-group-method',
            drop: function(event, ui) {

               var $draggableItem        = ui.draggable,
                   $container            = $draggableItem.closest('li'),
                   //contactGroupId      = $container.data('contactGroupId'),
                   contactId             = $container.data('contactId'),
                   $destination          = $(this).find('.list-group'),
                   $source               = $draggableItem.closest('.contact-group-droppable') ,
                   name                  = $container.find('.list-group-item-text').text(),
                   destinationSmsState   = false,
                   destinationEmailState = false,
                   draggedMethod         = $draggableItem.is('.contact-group-method-sms') ? 'sms' : 'email',
                   contactGroupId        = $('#detail-panel').find('.hook-editable-keys').data('contactGroupPk')
               ;

               if ($draggableItem.is('.disabled')) {
                   return;
               }

               // dragging to current/same droppable region
               if ($source.closest('.contact-group-droppable').prop('id') == $destination.closest('.contact-group-droppable').prop('id')) {
                   //Core.log('Cancel Drag/Drop action: Source and Destination are the same');
                   return;
               }

               // dragged sms
               if (draggedMethod == 'sms') {
                   destinationSmsState = true;
                   $draggableItem.addClass('disabled');
               }

               // dragged email
               if (draggedMethod == 'email') {
                   destinationEmailState = true;
                   $draggableItem.addClass('disabled');
               }

               // update droppable regions
               if (_contactExistsAtDestination(contactId, $destination)) {

                   _updateContactAtDestination(contactId, $destination, draggedMethod);

                   $draggableItem.closest('li').detach();

               } else {
                   $destination.append(Contact.GroupAssignment.generateGroupMarkup(contactId, name, destinationSmsState, destinationEmailState));
               }

               // update the contact from its source (remove contact if both email and sms are disabled)
                _updateContactAtSource(contactId, $source);

               // re-init so dropped items will function properly
               Contact.initGroupAssignment();

               // update the assigned contact
               Contact.getAssignedContact(contactId, function(contact) {
                   $.ajax({
                       url: '/ajax/contact/updateContactGroupContact',
                       type: 'POST',
                       dataType: 'json',
                       data: {
                           contactgroup_id: contactGroupId,
                           contact_id: contactId,
                           contact: contact
                       },
                       success: function(responseData) {
                            if (responseData.code !== 0) {
                              if (! $.isEmptyObject(responseData.message))
                              {
                                 Core.SystemMessage.show(responseData.message, responseData.code);
                              }
                              else
                              {
                                 Core.SystemMessage.show('An unknown error has occurred.', responseData.code);
                              }
                            }
                       }
                   });
               });
            }
        });

        function _contactExistsAtDestination(contactId, $destination) {

            var output = false;

            $destination.find('li').each(function() {
                var $self = $(this),
                    id    = $self.data('contactId')
                ;

                if (contactId == id) {
                    output = true;
                    return false; // break
                } else {
                    return true; // continue
                }

            });

            return output;
        }

        function _updateContactAtDestination(contactId, $destination, method) {
            $destination.find('li').each(function() {
                var $self = $(this),
                    id    = $self.data('contactId')
                ;

                if (contactId == id)  {
                    $destination.find('.contact-group-method-'+method).removeClass('disabled');
                    return false; // break
                } else {
                    return true; // continue
                }

            });


        }

        function _updateContactAtSource(contactId, $source) {
            $source.find('li').each(function() {
                var $self = $(this),
                    id    = $self.data('contactId')
                ;

                if (contactId == id)  {
                    if ($self.find('.contact-group-method-sms').is('.disabled') && $self.find('.contact-group-method-email').is('.disabled')) {
                        $self.detach();
                    }
                    return false; // break
                } else {
                    return true; // continue
                }
            });
        }

    },

    initContactGroupAssignmentHelp: function() {
        $('#contact-group-assignment-help-toggle').click(function() {

            var $self = $(this).find('small');

            if ($self.text() == 'I Need Help') {
                $self.text('Hide');
            } else {
                $self.text('I Need Help');
            }

            $('#contact-group-assignment-help').fadeToggle(300);
        });
    },

    getAssignedContact: function(contactId, callback) {
/*
        var assigned = [];

        $('#contact-groups-assigned').find('.contact-group-draggable').each(function(key, value) {
            var $self     = $(this),
                datum     = {
                    'contact_id': $self.data('contactId'),//$self.data('contactGroupId'),
                    'sms':        ($self.find('.contact-group-method-sms').eq(0).is('.disabled')) ? false : true,
                    'email':      ($self.find('.contact-group-method-email').eq(0).is('.disabled')) ? false : true
                }
            ;

            assigned.push(datum);
        });

        return assigned;
*/
        var $assigned = $('#contact-groups-assigned').find('.contact-group-draggable');
        if ($assigned.length > 0) {
            var lastIndex = $assigned.length - 1;
            $assigned.each(function(index, value) {
                var $self   = $(this),
                    id      = $self.data('contactId')
                ;

                if (id == contactId) {
                    //Core.log('found: '+id);
                    callback({
                        'contact_id': $self.data('contactId'),
                        'sms':        ($self.find('.contact-group-method-sms').eq(0).is('.disabled')) ? false : true,
                        'email':      ($self.find('.contact-group-method-email').eq(0).is('.disabled')) ? false : true
                    });
                } else if (index == lastIndex) {
                    //Core.log('not found');
                    callback({});
                }
            });
        } else {
            callback({});
        }
    },

    initContactMethod: function() {

        var $container                  = $('.contact-method-container'),
            $contactEmail               = $('#contact-new-email'),
            $contactSMS                 = $('#contact-new-sms'),
            $contactSMSCarrier          = $('#contact-new-carrier'),
            $contactSMSCarrierContainer = $('.contact-carrier-container'),
            $contactGroup               = $('#contact-assign-group-new'),
            $contactMethod              = $('#alert-contact-method-new'),
            $methods                    = $container.find('.contact-method'),
            $methodEmail                = $methods.filter('.contact-method-email'),
            $methodSMS                  = $methods.filter('.contact-method-sms'),
            $methodBoth                 = $methods.filter('.contact-method-both'),
            emailOk                     = false,
            smsOk                       = false,
            smsCarrierOk                = false,
            bothOk                      = false,
            groupOk                     = false
        ;

        $(document).on({
            'keyup': function() {

                var $self = $(this);

                // email
                if ($self.is($contactEmail)) {
                    emailOk = ($self.val().length) ? true : false;
                }

                // sms
                if ($self.is($contactSMS)) {
                    smsOk = ($self.val().length) ? true : false;
                }

                _contactMethodBehavior();

            },
            'Core.DropdownButtonChange': function() {

                var $self = $(this);

                // sms carrier
                if ($self.is($contactSMSCarrier)) {
                    smsCarrierOk = ($self.val().length) ? true : false;
                }

                // contact group
                if ($self.is($contactGroup)) {
                    groupOk = ($self.val() != 'none');
                    //Core.log('Group Changed');
                }

                _contactMethodBehavior();

            }
        }, $contactEmail.selector+', '+$contactSMS.selector+', '+$contactSMSCarrier.selector+', '+$contactGroup.selector);

        function _contactMethodBehavior() {

            // both email and sms (and carrier) have values
            bothOk = (emailOk && smsOk && smsCarrierOk);

            // toggle sms carrier container
            if (smsOk) {
                $contactSMSCarrierContainer.fadeIn(300);
            } else {
                $contactSMSCarrierContainer.fadeOut(300);
            }

            // all fields are set
            if (bothOk) {
                if (groupOk) {
                    $container.fadeIn(300);
                } else {
                    $container.fadeOut(300);
                }
                $methodBoth.show();
                $methodSMS.show();
                $methodEmail.show();
                return;
            }

            // sms and sms carrier are set
            if (smsOk && smsCarrierOk) {
                if (groupOk) {
                    $container.fadeIn(300);
                }
                $methodSMS.show();
            } else {
                $methodSMS.hide();
                $methodBoth.hide();
                if ( ! emailOk || ! groupOk) {
                    $container.fadeOut(300);
                }
                if ($contactMethod.val() == 'sms' || $contactMethod.val() == 'both') {
                    $contactMethod.val('false').html('&nbsp;');
                }
            }

            // email is set
            if (emailOk) {
                if (groupOk) {
                    $container.fadeIn(300);
                }
                $methodEmail.show();
            } else {
                $methodEmail.hide();
                $methodBoth.hide();
                if ( ! smsOk || ! smsCarrierOk || ! groupOk) {
                    $container.fadeOut(300);
                }
                if ($contactMethod.val() == 'email' || $contactMethod.val() == 'both') {
                    $contactMethod.val('false').html('&nbsp;');
                }
            }

            // nothing set
            if ( ! emailOk && ! smsOk && ! smsCarrierOk  || ( ! groupOk )) {
                $container.fadeOut(300);
            }
            if (! groupOk) {
                //Core.log('Group Not Okay');
                $container.fadeOut(300);
            }
        }

    },

    initAddContact: function() {
        var $body                       = $('body'),
            $popover                    = $('#popover-contact-new'),
            $popoverContainer           = $('#popover-contact-container'),
            $contactMethodContainer     = $('.contact-method-container'),
            $contactSMSCarrierContainer = $('.contact-carrier-container')
        ;

        // Add Contact
        $body.on('click', '#popover-new-contact-confirm', function() {
            var $contactFirstName   = $('#contact-new-first-name'),
                $contactLastName   = $('#contact-new-last-name'),
                $contactEmail       = $('#contact-new-email'),
                $contactSMS         = $('#contact-new-sms'),
                $contactSMSCarrier  = $('#contact-new-carrier'),
                $contactGroup       = $('#contact-assign-group-new'),
                $contactMethod      = $('#alert-contact-method-new'),
                validation          = [],
                data                = {}
            ;

            // validate contact name
            if ($contactFirstName.val() != '' && $contactLastName.val() != '') {
                data.contact_firstname = $contactFirstName.val();
                data.contact_lastname = $contactLastName.val();
            } else {
                validation.push('First Name and Last Name cannot be empty');
            }

            // validate contact email and/or sms and sms carrier
            if ($contactEmail.val() != '' || ($contactSMS.val() != '' && $contactSMSCarrier.val() != '')) {
                if ($contactEmail.val() != '') {
                    var email = $contactEmail.val(),
                        filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/
                    ;

                    if (filter.test(email)) {
                        data.contact_email = $contactEmail.val();
                    } else {
                        validation.push('Invalid E-Mail Address');
                    }
                }

                // validate SMS number
                if ($contactSMS.val() != '' && $contactSMSCarrier != '') {
                    var sms = $contactSMS.val(),
                        filter = /^\d+$/
                    ;

                    sms = sms.replace(/-/g, '');

                    if (filter.test(sms) && (sms.length == 10)) {
                        data.contact_sms = sms;
                        data.contact_sms_carrier = $contactSMSCarrier.val();
                    } else {
                        validation.push('Invalid SMS Number');
                    }
                }
            } else {
                validation.push('Please enter an E-Mail and/or SMS Number with selected SMS Carrier');
            }

            // validate group if selected
            if ($contactGroup.val() != '') {
                data.contactgroup_id = $contactGroup.val();
            }

            // validate contact method if selected
            if (($contactMethod.val() == 'all' || $contactMethod.val() == 'email' || $contactMethod.val() == 'sms') && ($contactGroup.val() != '')) {
                data.contact_method = $contactMethod.val();
            }

            if (validation.length == 0) {
                $.ajax({
                    url: '/ajax/contact/addContact',
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    success: function(responseData) {
                        if (responseData.code === 0) {
                            // redraw contact table
                            Contact.DataTables.contactList.fnStandingRedraw();

                            // close 'Add Contact' popover
                            $('#popover-new-contact-cancel').trigger('click');
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

        // reset all options in the 'Add Contact' popover to their corresponding default values (their first option)
        $popover.on('hide.bs.popover', function() {
            // reset 'Add Contact' popover fields/options only if the popover is opened
            // (i.e. a 'hide' event triggered from the opening of other popovers shouldn't reset this popover's fields)
            if ($popoverContainer.is(':visible')) {
                $popoverContainer.find('input[type="text"]').val('');

                $uls = $popoverContainer.find('ul');

                $.each($uls, function() {
                    $(this).find('li a').eq(0).trigger('click');
                });

                $contactMethodContainer.hide();
                $contactSMSCarrierContainer.hide();
            }
        });
    },

    initAddContactGroup: function() {
        var $body             = $('body'),
            $popover          = $('#popover-contact-group-new'),
            $contactGroupName = $('#contact-group-new-name')
        ;

        // save contact group
        $body.on('click', '#popover-new-contact-group-confirm', function() {
            var name = $contactGroupName.val();
            if (name != undefined && name != '') {
                $.ajax({
                    url: '/ajax/contact/addContactGroup',
                    data: 'POST',
                    dataType: 'json',
                    data: {
                        contactgroup_name: name
                    },
                    success: function(responseData) {
                        if (responseData.code === 0) {
                            // redraw contact group table
                            Contact.DataTables.contactGroups.fnStandingRedraw();

                            // close Add Contact Group popover
                            $('#popover-new-contact-group-cancel').trigger('click');

                            // update Contact Group dropdown in fitler
                            Contact.SecondaryPanel.updateContactGroupFilterDropdown();
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

        $popover.on('hidden.bs.popover', function() {
            $contactGroupName.val('').text('');
        });

    },

    SecondaryPanel: {

        initContactSearch: function() {

            var $searchContact          = $(),
                $searchGo               = $(),
                searchContactString     = '',
                $searchContactMethod    = $('#select-contact-type-filter'),
                $searchContactGroup     = $('#select-contact-group-filter'),
                $contactNavTabs         = $('#contact-nav-tabs').find('li')
            ;

            if (Core.Environment.context() == 'alert/contact' || Core.Environment.context() == 'report/contact') {
                $searchContact = $('#text-alert-search');
                $searchGo = $('#alert-search-go');
            }

            /**
             *
             * Hide Contact Group dropdown filter when view contact groups datatable
             *
             */
            $contactNavTabs.on('click', function () {
                var $self   = $(this),
                    text    = $self.find('a').text(),
                    $searchContactGroupFormGroup = $searchContactGroup.closest('.form-group'),
                    $contactGroupFilter = $('#select-contact-group-filter')
                ;

                if (text == 'Groups') {
                    if (! $searchContactGroupFormGroup.hasClass('hidden')) {
                        $searchContactGroupFormGroup.addClass('hidden');
                    }

                    if ($self.is('.active') !== true) {

                        if (Core.Environment.context() == 'alert/contact' || Core.Environment.context() == 'report/contact') {
                            // reset fitlers & search
                            $('#text-alert-search').val('').text('');

                            $('#select-contact-type-filter').val('all').text('All');

                            if ($contactGroupFilter.val() != '') {
                                $contactGroupFilter.val('all').text('All');
                            }
                        }

                        Contact.DataTables.contactGroups.fnDraw();

                    }
                } else {
                    if ($searchContactGroupFormGroup.hasClass('hidden')) {
                        $searchContactGroupFormGroup.removeClass('hidden');
                    }

                    if ($self.is('.active') !== true) {
                        if (Core.Environment.context() == 'alert/contact' || Core.Environment.context() == 'report/contact') {
                            // reset fitlers & search
                            $('#text-alert-search').val('').text('');

                            $('#select-contact-type-filter').val('all').text('All');

                            if ($contactGroupFilter.val() != '') {
                                $contactGroupFilter.val('all').text('All');
                            }
                        }

                        Contact.DataTables.contactList.fnDraw();
                    }
                }
            });

            /**
             *
             * On keyup when searching contacts using search string
             *
             */
            $searchContact.on('keyup', function () {
                var activeTab = $contactNavTabs.filter('.active').find('a').text(),
                    dataTable = (activeTab == 'List') ? Contact.DataTables.contactList : Contact.DataTables.contactGroups
                ;

                // get current search string
                var searchContactString = $searchContact.val().trim();

                if (searchContactString.length > 1) {
                    dataTable.fnDraw();
                } else if (searchContactString.length == 0) {
                    dataTable.fnDraw({});
                }

                $searchContactMethod.val('all').text('All');
                if ($searchContactGroup.val() != '') {
                    $searchContactGroup.val('all').text('All');
                }
            });

            /**
             *
             * On Search Button Click when searching contacts using search string
             *
             */
            $searchGo.on('click', function () {
                var activeTab = $contactNavTabs.filter('.active').find('a').text(),
                    dataTable = (activeTab == 'List') ? Contact.DataTables.contactList : Contact.DataTables.contactGroups
                ;

                // get current search string
                var searchContactString = $searchContact.val().trim();

                if (searchContactString != '') {
                    dataTable.fnDraw();
                }

                $searchContactMethod.val('all').text('All');
                if ($searchContactGroup.val() != '') {
                    $searchContactGroup.val('all').text('All');
                }
            });

            /**
             *
             * On Change of Contact Method Filtering on contact filter search
             *
             */
            $searchContactMethod.on('Core.DropdownButtonChange', function() {
                var activeTab = $contactNavTabs.filter('.active').find('a').text(),
                    dataTable = (activeTab == 'List') ? Contact.DataTables.contactList : Contact.DataTables.contactGroups
                ;

                $searchContact.val('');
                dataTable.fnDraw();
            });

            /**
             *
             * On Change of Contact Group Filtering on contact filter search (for contact list datatable only)
             *
             */
            $searchContactGroup.on('Core.DropdownButtonChange', function() {
                $searchContact.val('');
                Contact.DataTables.contactList.fnDraw();
            });
        },

        updateContactGroupFilterDropdown: function() {
            $.ajax({
                url: '/ajax/contact/getContactGroupsByAccountId',
                type: 'POST',
                dataType: 'json',
                success: function(responseData) {
                    if (responseData.code === 0) {
                        var $contactGroupFilter = $('#select-contact-group-filter'),
                            $contactGroupList = $('#select-contact-group-filter').siblings('ul').eq(0)
                        ;

                        $contactGroupList.html('');

                        if (! $.isEmptyObject(responseData.data.contactgroups)) {
                            $contactGroupList.append('<li><a href="#" data-value="all">All</a></li>');
                            $.each(responseData.data.contactgroups, function() {
                                var $self = $(this)[0];
                                $contactGroupList.append('<li><a href="#" data-value="'+$self.contactgroup_id+'">'+$self.contactgroupname+'</a></li>');
                            });
                        } else {
                            $contactGroupFilter.val('').text('None').addClass('disabled');
                            $contactGroupFilter.siblings('button').eq(0).addClass('disabled');
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
    },

    GroupAssignment: {

        generateGroupMarkup: function (id, name, smsEnabled, emailEnabled) {

            id           = id || false;
            name         = name || 'Group Name Error';
            smsEnabled   = smsEnabled || false;
            emailEnabled = emailEnabled || false;


            var output = false;

            if ( ! id) {
                return output;
            }

            output =  $('<li data-contact-id="'+id+'" class="list-group-item contact-group-draggable">'+
                        '    <div class="clearfix">'+
                        '         <div class="pull-left">'+
                        '             <span class="list-group-item-text">'+name+'</span>'+
                        '         </div>'+
                        '        <div class="pull-right">' +
                        '             <div class="clearfix method-container">'+
                        '                 <div class="left-method pull-left">' +
                        '                     <div class="contact-group-method contact-group-method-sms'+(smsEnabled ? '' : ' disabled')+'">'+
                        '                         <span class="glyphicon glyphicon-phone glyphicon-align-center"></span>'+
                        '                     </div>' +
                        '                 </div>'+
                        '                 <div class="right-method pull-left">' +
                        '                     <div class="contact-group-method contact-group-method-email'+(emailEnabled ? '' : ' disabled')+'">'+
                        '                         <span class="glyphicon glyphicon-envelope"></span>'+
                        '                     </div>' +
                        '                 </div>' +
                        '             </div>'+
                        '         </div>'+
                        '     </div>'+
                        ' </li>'
            );

            return output;
        },

        initOptionToggle: function() {
            var $optionsToggle = $('#contact-group-more-options-toggle'),
                $toggleLabel   = $optionsToggle.find('small')
            ;

            $optionsToggle.on('click', function() {

                if ($toggleLabel.text() == 'Show More Options') {
                    $toggleLabel.text('Show Less Options');
                } else {
                    $toggleLabel.text('Show More Options');
                }

                $('#contact-group-more-options').slideToggle(300);

            });
        }

    }


});
