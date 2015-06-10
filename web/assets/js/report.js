/*

    Report JS

    File:       /assets/js/report.js
    Author:     Tom Leach
*/

var cookieArray = new Array();

$(document).ready(function() {

    /**
     *
     * Page Specific Functionality
     *
     */
    switch (Core.Environment.context()) {

        /* LIST */
        case 'report/list':
            Report.List.initReportParams();
            Report.List.initRemove();
            Report.List.initExport();
            Report.List.initRebuildReports();
            Report.List.initReloadReport();
            break;

        case 'report/history':
            Report.Scheduled.DataTables.initPaged();
            Core.DataTable.pagedReport('report-history-table');
            // Report.History.DataTables.init();
            // Report.History.Modal.init();
            // Report.History.initReportHistorySearch();
            break;

        /* SCHEDULED */
        case 'report/scheduled':
            Report.Scheduled.DataTables.initPaged();
            Core.DataTable.pagedReport('report-scheduled-table');
            // Report.Scheduled.DataTables.init();
            // Report.Scheduled.initModal();
            // Report.Scheduled.initScheduleReportSearch();
            break;

        /* CONTACTS */
        case 'report/contact':
            Report.Scheduled.DataTables.initPaged();
            Core.DataTable.pagedReport('contacts-contacts-table','',1);
            Core.DataTable.pagedReport('contacts-groups-table','',1);
            break;

        /* ALL OTHER ROUTES ARE REPORTS */
        default:
            Report.List.initReportParams();
            break

    }

    Report.isLoaded();

});

var Report = {};

jQuery.extend(Report, {

    isLoaded: function() {
        console.log('Report JS Loaded');
    },

    List: {

        initReportParams: function() {
            var $subPanel         = $('#sub-panel'),
                $subPanelItems    = $('#sub-panel-items'),
                $paramsPanel      = $subPanel.find('#report-params-panel'),
                $paramsPanelTitle = $paramsPanel.find('.secondary-panel-heading'),
                $reports          = $subPanel.find('.list-group-item'),
                $panels           = $paramsPanel.find('.report-params'),
                $goBack           = $paramsPanel.find('.go-back'),
                $hint             = $('.report-hint'),
                $runButton        = $('#report-run'),
                $reportTypeSelect = $('#report-type-select')
            ;
            
            $('#report-dates').val('Today'); // reset date range value on page load

            $reportTypeSelect.on('Core.DropdownButtonChange', function() {
                $('#report-type-'+$(this).val()).trigger('click');
            });

            /* reveal report params */
            $reports.on('click', function() {

                var $self          = $(this),
                    data           = $self.data(),
                    $activePanel   = $subPanel.find('.'+data.reportParamsId),
                    route          = data.reportRoute,
                    reportType     = data.reportType,
                    reportTypeId   = data.reportTypeId,
                    reportTypeText = $.trim($self.text()),
                    $reportName    = $('#report-name'),
                    $reportTabs    = $('#report-result-tabs'),
                    reportTypeName = $self.find('span').text().trim()
                ;
                //Core.log(data.reportParamsId);

                $reportTypeSelect.val(reportTypeId);
                $reportTypeSelect.find('.filter-label').text(
                    $reportTypeSelect.siblings('.dropdown-menu').find('a').filter('[data-value="'+reportTypeId+'"]').text()
                );

                // for Detailed Event Report, only allow Single Vehicle Mode
                var $vehicleMode     = $('#report-vehicle-mode'),
                    $vehicleModeForm = $vehicleMode.closest('.form-group'),
                    $reportDates     = $('#report-dates'),
                    $reportDatesForm = $reportDates.closest('.form-group')
                    //reportType = data.reportParamsId.substring(14)
                ;

                // enforce Single Vehicle Mode only for these reports
                switch (reportType) {
                    case 'detail':
                    case 'stop':
                    case 'frequentStops':
                    case 'lastTenStops':
                        $vehicleMode.siblings('ul').find('li a').filter('[data-value="single"]').trigger('click');
                        $vehicleModeForm.hide();
                        break;
                    default:
                        $vehicleMode.siblings('ul').find('li a').filter('[data-value="all"]').trigger('click');
                        
                        // hide Vehicle Mode for User Command report
                        if (reportType != 'userCommand') {
                            $vehicleModeForm.show();
                        } else {
                            $vehicleModeForm.hide();
                        }
                        
                        break;
                }
                
                // hide Date Range for these reports
                switch (reportType) {
                    case 'mileageSummary':
                    case 'nonReporting':
                    case 'lastTenStops':
                    case 'starterDisableSummary':
                    case 'stationary':
                    case 'vehicleInformation':
                    case 'verificationOfReference':
                        $reportDatesForm.hide();
                        break;
                    default:
                        $reportDatesForm.show();
                        break;
                }

                // special vehicle mode show/hide cases
                if (reportType == 'stationary' || reportType == 'vehicleInformation') {
                    $vehicleMode.siblings('ul').find('li a').filter(':not([data-value="all"])').hide();
                    $vehicleMode.siblings('button').eq(0).addClass('disabled');
                } else {
                    $vehicleMode.siblings('ul').find('li a').filter(':not([data-value="all"])').show();
                    $vehicleMode.siblings('button').eq(0).removeClass('disabled');
                }

                // special vehicle mode show/hide cases must come after above stationary case
                if(reportType == 'nonReporting') {
                    //hide single vehicle option
                    $vehicleMode.siblings('ul').find('li a').filter('[data-value="single"]').hide();
                } else {
                    $vehicleMode.siblings('ul').find('li a').filter('[data-value="single"]').show();
                }

                //$paramsPanelTitle.text(data.reportTitle);
                $paramsPanel.fadeIn(300);
                $panels.hide();
                $hint.fadeOut(300);
                $activePanel.show();
                /*
                $('#reportDates').daterangepicker({
                    action: 'update',
                    startDate: '01/01/2014',
                    endDate: '01/01/2014'
                });
                */
                $runButton.data('reportRoute', route).data('reportType', reportType).data('reportTypeId', reportTypeId).data('reportTypeName', reportTypeName);

                Report.List.updateParamsForm(reportTypeText);

            });

            /* hide report params */
            $goBack.on('click', function() {
                $paramsPanel.fadeOut(300);
                $hint.fadeIn(300);
            });

            var $body = $('body');
            
            $('#scheduled-contact-mode').on('change', function() {
                $('.dropdown-toggle-panel-scheduled-contact-group').hide();
                $('.dropdown-toggle-panel-scheduled-contact-single').hide();
                console.log('#scheduled-contact-mode:show:'+$('#scheduled-contact-mode').val());
                $('.dropdown-toggle-panel-scheduled-contact-'+$('#scheduled-contact-mode').val()).show();
            });

            $('#scheduled-recurrence').on('change', function() {
                $('#div-scheduled-Weekly').hide();
                $('#div-scheduled-Monthly').hide();
                console.log('#scheduled-recurrence:show:'+$('#scheduled-recurrence').val());
                switch($('#scheduled-recurrence').val()){
                    case                      'Monthly' :
                    case                       'Weekly' :   $('#div-scheduled-'+$('#scheduled-recurrence').val()).show();
                                                            break;
                }
            });

            $body.on('click', '#secondary-sidebar-scroll li, .sub-panel-items li', function() {
                var $self       = $(this),
                    cookieCount = 0,
                    reportName  = $self.text()
                ;

                cookieCount = Core.Cookie.get(reportName.toLowerCase().replace(' ','-'));
                if(cookieCount<1){
                    cookieCount=1;
                }
                $('#report-name').val(reportName+' Report '+cookieCount);
            });


            $body.on('click', '#report-run, #report-schedule, #report-schedule-popup-save', function() {
            //$runButton.on('click', function() {

                var $self          = $(this),
                    data           = $('#report-run').data(),
                    reportTypeId   = $('#select-report-type').val(),
                    reportRoute    = data.reportRoute,
                    reportType     = $('#select-report-type').val(),
                    reportTypeName = $('#select-report-type').text(),
                    startTime      = new Date(),
                    endTime        = new Date(),
                    vehicleMode    = $('#report-vehicle-mode').val()
                ;

                /* reportType to mapReportTypeId */
                switch(reportTypeId){
                    case   1  :
                    case  '1' : reportType = 'alert';                      // Alert
                                break ;
                    case  '3' : 
                    case   3  : reportType = 'detail';                     // Detailed Event
                                vehicleMode = 'single';
                                break ;
                    case  '4' : 
                    case   4  : reportType = 'frequentStops';              // Frequent Stops
                                vehicleMode = 'single';
                                break ;
                    case  '5' : 
                    case   5  : reportType = 'landmark';                   // Landmark
                                break ;
                    case  '6' : 
                    case   6  : reportType = 'mileageSummary';             // Mileage Summary
                                break ;
                    case  '7' : 
                    case   7  : reportType = 'nonReporting';               // Non Reporting
                                break ;
                    case  '8' : 
                    case   8  : reportType = 'speedSummary';               // Speed Summary
                                break ;
                    case  '9' : 
                    case   9  : reportType = 'starterDisableSummary';      // Starter Disable Summary
                                break ;
                    case '10' : 
                    case  10  : reportType = 'stationary';                // Stationary
                                break ;
                    case '11' : 
                    case  11  : reportType = 'stop';                      // Stop
                                vehicleMode = 'single';
                                break ;
                    case '12' : 
                    case  12  : reportType = 'userCommand';               // User Command
                                break ;
                    case '13' : 
                    case  13  : reportType = 'vehicleInformation';        // Vehicle Information
                                break ;
                    case '14' : 
                    case  14  : reportType = 'verificationOfReference';   // Address Verification
                                break ;
                    case '15' : 
                    case  15  : reportType = 'lastTenStops';              // Last Ten Stops
                                vehicleMode = 'single';
                                break ;
                }

                /* if run button is disabled */
                if ($self.is('#report-run') && $runButton.hasClass('max-reached')) {

                    var blinkCount   = 0,
                        blinkMax     = 8,
                        interval     = -1,
                        intervalTime = 100,
                        $hint        = $('#max-report-hint')
                    ;

                    interval = setInterval(function() {
                        blinkCount++;
                        $hint.toggleClass('highlight');
                        if (blinkCount == blinkMax ) {
                            clearInterval(interval)
                        }
                    }, intervalTime);

                    return false;
                }

                //window.location = data.reportRoute;

                if (reportType != undefined && reportType != '' && reportTypeId != undefined && reportTypeId != '') {
                    var postData = {},
                        validation = [],
                        exportParams = ''
                    ;

                    postData.reporttype = reportType;
                    postData.reporttype_id = reportTypeId;

                    postData.reporttype_name = reportTypeName;
                    
                    /**
                     * Validate Report Name
                     */
                    var reportName = $('#report-name').val();
                    if (reportName != undefined && reportName != '') {
                        postData.report_name = reportName;
                    } else {
                        validation.push('A Report Name is required');
                    }
                    
                    /**
                     * Vehicle Validation
                     */
console.log('vehicleMode:'+vehicleMode);
                    if (vehicleMode == 'all' || vehicleMode == 'single' || vehicleMode == 'group') {
                        postData.unit_mode = vehicleMode;
                        if (vehicleMode == 'group') {
                            var unitGroup = $('#report-vehicle-group').val();
                            if (unitGroup != undefined && unitGroup != '') {
                                postData.unitgroup_id = parseInt(unitGroup);
                            } else {
                                validation.push('Please select a Vehicle Group');
                            }
                        } else if (vehicleMode == 'single') {
                            var unit = $('#report-vehicle-single').val();
                            if (unit != undefined && unit != '') {
                                postData.unit_id = parseInt(unit);
                            } else {
                                validation.push('Please select a Vehicle');
                            }
                        }
                    } else {
                        validation.push('Please select a Vehicle Mode');    
                    }
console.log('vehicleMode:vehicleMode:'+vehicleMode);
console.log('vehicleMode:postData.unit_id:'+postData.unit_id);
console.log('vehicleMode:postData.unitgroup_id:'+postData.unitgroup_id);

                    /**
                     * Date Range Validation
                     */
                    if ($('#report-dates').is(':visible')) {
                        var dateRange = $('#report-dates').val();
                        if (dateRange != undefined && dateRange != '') {
                            postData.filter_daterange   = dateRange;

                            //postData.end_date           = $('#report-dates').data('endDate');
                            //postData.start_date         = $('#report-dates').data('startDate');

                            var d                       = new Date();
                            d.setHours(23);
                            d.setMinutes(59);
                            d.setSeconds(59);    
                            postData.end_date           = d.toLocaleString();
                            d.setHours(0);
                            d.setMinutes(0);
                            d.setSeconds(0);
//console.log('dateRange:'+dateRange);                        
                            switch(dateRange) {
                                case    'Last Month' :  d.setDate(1);
                                                        postData.start_date = d.toLocaleString(d.setMonth(d.getMonth()-1));
                                                        d.setHours(23);
                                                        d.setMinutes(59);
                                                        d.setSeconds(59);
                                                        d.setMonth(d.getMonth()+1);
                                                        postData.end_date = d.toLocaleString(d.setDate(d.getDate()-1));
                                                        break;
                                case    'This Month' :  d.setDate(1);
                                                        postData.start_date = d.toLocaleString();
                                                        d.setHours(23);
                                                        d.setMinutes(59);
                                                        d.setSeconds(59);
                                                        d.setMonth(d.getMonth()+1);
                                                        postData.end_date = d.toLocaleString(d.setDate(d.getDate()-1));
                                                        break;
                                case   'Last 90 Days' : postData.start_date = d.toLocaleString(d.setDate(d.getDate()-89));
                                                        break;
                                case   'Last 60 Days' : postData.start_date = d.toLocaleString(d.setDate(d.getDate()-59));
                                                        break;
                                case   'Last 30 Days' : postData.start_date = d.toLocaleString(d.setDate(d.getDate()-29));
                                                        break;
                                case    'Last 7 Days' : postData.start_date = d.toLocaleString(d.setDate(d.getDate()-6));
                                                        break;
                                case      'Yesterday' : postData.start_date = d.toLocaleString(d.setDate(d.getDate()-1));
                                                        d.setHours(23);
                                                        d.setMinutes(59);
                                                        d.setSeconds(59);
                                                        postData.end_date = d.toLocaleString(d.setDate(d.getDate()));
                                                        break;
                                case   'Custom Range' : $('#div-date-dropdown').hide();
                                                        $('#div-date-custom').show();
                                                        break;
                                              default : postData.start_date = d.toLocaleString();
                            }

                            startTime   = new Date(postData.start_date);
                            endTime     = new Date(postData.end_date);    

                            var currdate= (startTime.getMonth()+ 1) + '/' + startTime.getDate() + '/' + startTime.getFullYear();
                             $('#date-range-picker_start').val(currdate);
                            currdate= (endTime.getMonth()+ 1) + '/' + endTime.getDate() + '/' + endTime.getFullYear();
                            $('#date-range-picker_end').val(currdate);

                            if (startTime.getTime() > endTime.getTime()) { 
                                validation.push('Start Date must be before End Date');        
                            }
                         } else {
                            validation.push('Please select a Date Range');
                        }
console.log('*** date range *** : '+postData.filter_daterange);
                    } else {
console.log('*** custom date ***');

                        startTime   = new Date();
                        endTime     = new Date();    

                        var currdate= (startTime.getMonth()+ 1) + '/' + startTime.getDate() + '/' + startTime.getFullYear();

                        if(! $('#date-range-picker_start').val()) {
                            $('#date-range-picker_start').val(currdate);
                        }
                        if(! $('#date-range-picker_end').val()) {
                            $('#date-range-picker_end').val(currdate);
                        }
                        postData.start_date = $('#date-range-picker_start').val() + ' 12:00:00 AM';
                        postData.end_date = $('#date-range-picker_end').val() + ' 11:59:59 PM';
                        startTime   = new Date(postData.start_date);
                        endTime     = new Date(postData.end_date);    
                    }
console.log('startTime:'+startTime);
console.log('endTime:'+endTime);

                    var startDate = new Date(postData.start_date);
                    var endDate = new Date(postData.end_date);
                    /**
                     * Validate Report Specific Criteria
                     */
console.log('reportType:'+reportType);
                    switch (reportType) {
                        case 'alert':
                            var alertType = $('#report-alert').val();
                            if (alertType != undefined && alertType != '') {
                                postData.filter_alert_type = alertType;    
                            } else {
                                validation.push('Please select an Alert Type');
                            }
                            if (postData.unit_mode != 'single') {   // if more than one vehicle was selected, restrict the date range to 30 days
                                if (postData.start_date != undefined && postData.start_date != '' && postData.end_date != undefined && postData.end_date != '') {
                                    //Core.log(startDate.getTime());
                                    //Core.log(endDate.getTime());
                                    startTime = startDate.getTime();
                                    endTime = endDate.getTime();
                                    
                                    if ((endTime - startTime) > (30 * 86400000)) {  // 30 days in milliseconds
                                        validation.push('Date Range cannot exceed 30 days if Vehicle Groups or All Vehicles was selected');
                                    }
                                } else {
                                    validation.push('Invalid Date Range');
                                }        
                            }
                            break;
                        case 'detail':
                            if (postData.start_date != undefined && postData.start_date != '' && postData.end_date != undefined && postData.end_date != '') {
                                startTime = startDate.getTime();
                                endTime = endDate.getTime();
                                
                                if ((endTime - startTime) > (91 * 86400000)) {  // 90 days in milliseconds
                                    validation.push('Date Range cannot exceed 90 days');
                                }
                            } else {
                                //validation.push('Invalid Date Range');
                            } 
                            break;                            
                        case 'lastTenStops':    // no need for other criteria
                            exportParams += '0/0';
                            break;
                        case 'stop':
                            var stopDuration = $('#report-stop').val();
                            if (stopDuration != undefined && stopDuration != '') {
                                postData.filter_minutes = stopDuration;
                            } else {
                                validation.push('Please select a Stop Threshold');    
                            }
                            break;
                        case 'frequentStops':
                            var stopDuration = $('#report-stop').val()/*,
                                stopNumber = $('#report-frequent-stops-number').val()
                                */
                            ;
                            
                            if (stopDuration != undefined && stopDuration != '') {
                                postData.filter_minutes = stopDuration;   
                            } else {
                                validation.push('Please select an Average Duration Threshold');    
                            }
                            /*
                            if (stopNumber != undefined && stopNumber != '') {
                                postData.filter_stop_number = stopNumber;
                            } else {
                                validation.push('Please select a # of Stops Threshold');    
                            }
                            */
                            break;
                        case 'boundary':
                        case 'landmark':
                            var territoryMode = $('#report-'+reportType+'-mode').val(),
                                type = (reportType.charAt(0).toUpperCase() + reportType.slice(1))
                            ;
                            
                            if (territoryMode == 'all' || territoryMode == 'single' || territoryMode == 'group') {
                                postData.territory_mode = territoryMode;
                                if (territoryMode == 'single') {                 // pick a single territory
                                    var territory = $('#report-'+reportType+'-single').val();
                                    if (territory != undefined && territory != '') {
                                        postData.territory_id = territory;
                                    } else {
                                        validation.push('Please select a ' + type);
                                    }
                                } else if (territoryMode == 'group') {    // pick a territory group
                                    var territoryGroup = $('#report-'+reportType+'-group').val();
                                    if (territoryGroup != undefined && territoryGroup != '') {
                                        postData.territorygroup_id = territoryGroup;
                                    } else {
                                        validation.push('Please select a ' + type + ' Group');
                                    }
                                }
                                
                                // restrict date range to 30 days if selecting Landmark group and All Landmarks
                                if (vehicleMode != 'single') {
                                    if (postData.start_date != undefined && postData.start_date != '' && postData.end_date != undefined && postData.end_date != '') {
                                        
                                        startTime = startDate.getTime();
                                        endTime = endDate.getTime();
                                        
                                        if ((endTime - startTime) > (30 * 86400000)) {  // 30 days in milliseconds
                                            validation.push('Date Range cannot exceed 30 days if Vehicle Groups or All Vehicles was selected');
                                        }
                                    } else {
                                        validation.push('Invalid Date Range');
                                    }    
                                }
                            } else {
                                validation.push('Please select a ' + type + ' Mode');    
                            }
                            break;
                        case 'mileageSummary': // no need for date range
                            var totalMilesFilter = $('#report-mileage-summary').val();
                            if (totalMilesFilter != undefined && totalMilesFilter != '') {
                                postData.filter_miles = totalMilesFilter;
                            } else {
                                validation.push('Please select a Total Miles filter');    
                            }
                            break;
                        case 'nonReporting':   // no need for date range
                            var days = $('#report-non-reporting').val();
                            if (days != undefined && days != '') {
                                postData.filter_days = days;
                            } else {
                                validation.push('Invalid # of Stops Threshold');    
                            }
                            break;
                        case 'stationary':      // no need for date range
                            var days = $('#report-stationary').val();
                            if (days != undefined && days != '') {
                                postData.filter_days = days;
                            } else {
                                validation.push('Please select a Days filter');    
                            }
                            break;
                        case 'userCommand':
                            var userId = $('#report-user-command').val();
                            if (userId != undefined && userId != '') {
                                postData.user_id = userId;
                            } else {
                                validation.push('Please select a User');    
                            }
                            break;
                        case 'verificationOfReference':       // (aka 'verification') no need for date range
                            var verifiedFilter = $('#report-reference').val();
                            if (verifiedFilter != undefined && verifiedFilter != '') {
                                postData.filter_verified = verifiedFilter;
                            } else {
                                validation.push('Please select a Verification filter');    
                            }
console.log('reportType:verificationOfReference:postData.filter_verified:"'+postData.filter_verified+'"');
                            break;
                        case 'vehicleInformation': // no need for date range
                            break;
                        case 'speedSummary':
                            var speed = $('#report-speed-summary').val();
                            if (speed != undefined && speed != '') {
                                postData.filter_speed = speed;
                            } else {
                                validation.push('Please select a Speed filter');
                            }
                            
                            // restrict date range to 30 days
                            if (postData.start_date != undefined && postData.start_date != '' && postData.end_date != undefined && postData.end_date != '') {
                                
                                startTime = startDate.getTime();
                                endTime = endDate.getTime();
                                
                                if ((endTime - startTime) > (30 * 86400000)) {  // 30 days in milliseconds
                                    validation.push('Date Range cannot exceed 30 days for this report');
                                }
                            } else {
                                validation.push('Invalid Date Range');
                            }
                            break;
                        case 'starterDisableSummary': // no need for date range ?
                            break;
                        default:
                            validation.push('Invalid report type (report)');                            
                            break;
                    }                                      

                    if ($self.attr('id') == 'report-schedule-popup-save') {
                        //popover-report-schedule-form

                        /**
                         * Recurrance Validation
                         */
                        var recurrence = $('#scheduled-recurrence').val();
                            postData.schedule_recurrence = recurrence;
                        if (recurrence == 'Weekly') {
                            var scheduleDay = $('#scheduled-day').val();
                            if (scheduleDay != undefined && scheduleDay != '') {
                                postData.schedule_day = scheduleDay;
                            } else {
                                validation.push('Please select a Day of Week');
                            }
                        } else if (recurrence == 'Monthly') {
                            var scheduleMonthly = $('#scheduled-monthly').val();
                            if (scheduleMonthly != undefined && scheduleMonthly != '') {
                                postData.schedule_monthly = scheduleMonthly;
                            } else {
                                validation.push('Please select a Day of Month');
                            }
                        }

                        var scheduleTime = $('#scheduled-time').val();
                        if (scheduleTime != undefined && scheduleTime != '') {
                            postData.schedule_time = scheduleTime;
                        } else {
                            validation.push('Please select a Time To Send');
                        }

                        var scheduleFormat = $('#scheduled-format').val();
                        if (scheduleFormat != undefined && scheduleFormat != '') {
                            postData.schedule_format = scheduleFormat;
                        } else {
                            validation.push('Please select a report Format');
                        }

                        /**
                         * Contact Validation
                         */
                        var contactMode = $('#scheduled-contact-mode').val();
                        if (contactMode == 'single' || contactMode == 'group') {
                            postData.contact_mode = contactMode;
                            if (contactMode == 'group') {
                                var contactGroup = $('#scheduled-contact-group').val();
                                if (contactGroup != undefined && contactGroup != '') {
                                    postData.contactgroup_id = contactGroup;
                                } else {
                                    validation.push('Please select a Contact Group');
                                }
                            } else if (contactMode == 'single') {
                                var contact = $('#scheduled-contact-single').val();
                                if (contact != undefined && contact != '') {
                                    postData.contact_id = contact;
                                } else {
                                    validation.push('Please select a Contact');
                                }
                            }
                        } else {
                            validation.push('Please select a Contact Mode');    
                        }

                    }
console.log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> $(#report-name).val():'+$('#report-name').val());
// $.each(validation, function(k, v) { 
//   console.log('validation:'+k+'="'+v+'"'); 
// });
                    if (validation.length == 0) {
                        if ($self.prop('id') == 'report-run') {

//postData.filter_alert_type = 'test';
// console.log('###########################################################');
// $.each(postData, function(k, v) { 
// console.log('/ajax/report/runReport:postData['+k+']="'+v+'"'); 
// });
// console.log('###########################################################');

                            $.ajax({
                                url: '/ajax/report/runReport',
                                type: 'POST',
                                dataType: 'json',
                                data: postData,
                                success: function(responseData) {
// console.log('responseData:'+responseData.code+':'+responseData.message);
                                    if (responseData.code === 0) {
                                        // return report html markup and append it to the DOM    
                                        //$('#main-content').find('.container').append('<br>'+responseData.data.report);

                                        var reporthistory_id = (responseData.data.reporthistory_id != undefined) ? responseData.data.reporthistory_id : 0;
                                        
                                        if(responseData.data.report){
                                            responseData.data.report = responseData.data.report.replace(/#Verified#/g,'<span class="label label-success">Verified</span>').replace(/#Not Verified#/g,'<span class="label label-danger">Not Verified</span>');
                                        }

                                        var uniqueString = Report.List.renderReport(
                                            responseData.data.report_type_name,
                                            responseData.data.head,
                                            responseData.data.report,
                                            reporthistory_id
                                        );

                                        $('#report-landing-container').hide();
                                        $('#report-tabs-container').fadeIn(300);

                                        var reportType = responseData.data.report_type_name; //$('#report-tab-'+uniqueString).find('.report-result-params').data('reportType');

                                        Report.Cookie.Tabs.update('add', reporthistory_id, reportName, reportType, false);
                                        Report.Cookie.TabTypes.update('increment', reportType);
                                        // Report.List.updateParamsForm(reportType);
                                        Report.List.buildTooltips();

                                        window.setTimeout('Core.reportScroll()',1);

                                    } else {
                                        if (! $.isEmptyObject(responseData.validation_error)) {
                                            // display validation errors
                                        }
                                        if ($.isEmptyObject(responseData.message) === false) {
                                            Core.SystemMessage.show(responseData.message, responseData.code);
                                        }
                                   }

                                    if (! $.isEmptyObject(responseData.message)) {
                                        Core.SystemMessage.show(responseData.message, responseData.code);
                                    }    
                                }
                                    
                            });

                        } else if ($self.prop('id') == 'report-schedule-popup-save') {

console.log('report-schedule-popup-save');

                            //schedule report save
                            $.ajax({
                                url: '/ajax/report/saveScheduleReport',
                                type: 'POST',
                                dataType: 'json',
                                data: postData,
                                success: function(responseData) {
console.log('/ajax/report/saveScheduleReport:responseData:'+responseData.code+':'+responseData.message);
                                    if (responseData.code === 0) {
                                        $('#report-schedule-popup-cancel').trigger('click');
                                        window.location='/report/scheduled';
                                    } else {
                                        if (! $.isEmptyObject(responseData.validation_error)) {
                                            // display validation errors
                                            Core.SystemMessage.show(responseData.message, responseData.code);
                                        }
                                    }
                                    
                                    if (! $.isEmptyObject(responseData.message)) {
                                        Core.SystemMessage.show(responseData.message, responseData.code);
                                    }    
                                }
                            });

                        }
                        
                        
                        //Core.log(postData);    
                    } else {
                        alert(validation.join('\n'));
                    }                        
                } else {
                    alert('Invalid Report Type (report)');
                }

            });


        },

        initRemove: function() {

            var $tabsContainer = $('#report-result-tabs'),
                $tabs = $tabsContainer.find('li'),
                $landingContainer = $('#report-landing-container'),
                $closeButtons = $tabs.find('.report-close')

            ;

            $tabsContainer.on('click', '.report-close', function() {

                var $self = $(this),
                    $tab  = $self.closest('li'),
                    $title = $tab.find('.report-title').html(),
                    $panes  = $('#report-result-panes').find('.tab-pane'),
                    $pane = $panes.filter($tab.find('a').attr('href')),
                    reportType = $pane.find('.report-result-params').data('reportType')
                ;

console.log('title:'+$title);

                Report.Cookie.Tabs.update('remove', $pane.data('reportHistoryId'), $title, null, null);

                $tab.trigger('mouseleave'); // dismiss tooltip
                $tab.add($pane).remove();

                // if there are no more report tabs
                if ( ! $tabsContainer.find('li').length) {
                    // hide tab container and show landing container
                    $('#report-exports').hide();
                    $('#report-tabs-container').hide();
                    $landingContainer.show();
                } else {
                    // must requery the dom since a tab has been removed
                    $tabs = $tabsContainer.find('li');
                    $tabs.first().find('a').trigger('click');
                }

                if ($tabs.length < 8 ) {
                    $('#report-run').removeClass('max-reached');
                }

            });


        },

        updateParamsForm: function(reportTypeText, resetForm) {

            resetForm = resetForm || false;
            if (resetForm) {
                Core.Cookie.clear(reportType);
                //Report.List.updateParams
                //$('.go-back').trigger('click');
            }

            /* Default Report Name & Report Count Max. */
            var $reportName     = $('#report-name'),
                $reportTabs     = $('#report-result-tabs'),
                reportType      = reportTypeText.toLowerCase().replace(' ','-').trim();
                cookieCount     = 0,
                reportTypeCount = 0,
                reportCount     = $reportTabs.find('li').length,
                maxReportCount  = 88
            ;
            cookieCount = Core.Cookie.get(reportType);
console.log('aa ++++++++++++++++++++++++++++++++++++ reportTypeText:'+reportType+':'+cookieCount);
            if(cookieCount<1){
                cookieCount=1;
            }
console.log('b ++++++++++++++++++++++++++++++++++++ reportTypeText:'+reportType+':'+cookieCount);
            if(reportTypeText){
                cookieCount++;
console.log('c ++++++++++++++++++++++++++++++++++++ reportTypeText:'+reportType+':'+cookieCount);
                Core.Cookie.clear(reportType);
console.log('d ++++++++++++++++++++++++++++++++++++ reportTypeText:'+reportType+':'+cookieCount);
                Core.Cookie.set(reportType,cookieCount);
console.log('e ++++++++++++++++++++++++++++++++++++ reportTypeText:'+reportType+':'+cookieCount);
                reportTypeCount = $reportTabs.find('li').filter('.report-type-'+reportTypeText.toLowerCase()).length,
                $reportName.val(reportTypeText.toTitleCase()+' Report '+cookieCount);
            }
console.log('f ++++++++++++++++++++++++++++++++++++ reportTypeText:'+reportType+':'+cookieCount);

            if (reportCount < maxReportCount) {

                $reportName.add($('#report-run')).prop('disabled', false).removeClass('disabled');
                $reportName.add($('#report-run')).removeClass('max-reached');

            } else {

                $reportName.add($('#report-run')).prop('disabled', true).addClass('disabled');
                $reportName.add($('#report-run')).addClass('max-reached');

            }

        },

        renderReport: function(reportType, htmlHead, htmlReport, reportHistoryId) {//, exportLink) {

            reportType  = $.trim(reportType).replace(' ', '-').toLowerCase() || '';
            htmlHead    = htmlHead || '';
            htmlReport  = htmlReport || '';

            var $tabsContainer  = $('#report-result-tabs'),
                $newTab         = null,
                $reportName     = $('#report-name'),
                $panesContainer = $('#report-result-panes'),
                $newPane        = null,
                uniqueString    = Core.StringUtility.getRandomString(10)
            ;

            $newTab = $(
                '<li class="active report-type-'+reportType+'">' +
                '   <a href="#report-tab-'+uniqueString+'" data-toggle="tab">' +
                '       <div class="clearfix">' +
                '           <div class="pull-left">' +
                '               <span class="report-title">'+$reportName.val()+'</span>' +
                '           </div>' +
                '           <div class="pull-right">' +
                '               <span class="report-close close">&times;</span>' +
                '           </div>' +
                '       </div>' +
                '   </a>' +
                '</li>'
            );



            $newPane = $(
                '<div class="tab-pane active" id="report-tab-'+uniqueString+'" data-report-history-id="'+reportHistoryId+'">'+
                    '<div class="panel-body panel-report-scroll">' +
                    '   <h1>'+$reportName.val()+'</h1>' +
                    '   <div class="well well-sm">' +
                    '       <div class="clearfix">' +
                             htmlHead +
                    '       </div>' +
                    '   </div>' +
                        htmlReport +
                    '</div>' +
                '</div>'
            );

            /*if (htmlSummary.length) {
                $(
                    '<div class="panel-footer">' +
                    '   <h3>Summary of '+$reportName.val()+'</h3>' +
                        htmlSummary +
                    '</div>'
                ).insertAfter($newPane.find('.panel-body'));
            }*/

            $tabsContainer.find('li').removeClass('active');
            $panesContainer.find('.tab-pane').removeClass('active');

            $tabsContainer.prepend($newTab);
            $panesContainer.append($newPane);

            //var $addedTab = $tabsContainer.find('li').filter('.active');
            //$newTab.addClass('has-tooltip').data('placement', 'bottom').attr('title', $newTab.find('.report-title').text());
            //$newTab.next().addClass('has-tooltip').data('placement', 'auto').attr('title', $newTab.find('.report-title').text());

            //Core.Tooltip.init();

            Report.List.updateParamsForm(reportType, true);

            $('#report-exports').show();

            return uniqueString;

        },

        initExport: function() { 
            // Export a report to CSV or PDF
            $(document).on('click', '#popover-report-list-export-pdf-confirm, #popover-report-list-export-csv-confirm', function() {
                var $self = $(this),
                    exportFormat = $self.prop('id') == 'popover-report-list-export-pdf-confirm' ? 'pdf' : 'csv',
                    reportHistoryId = $('#report-result-tabs').siblings('.panel-report').eq(0).find('.tab-pane.active').data('reportHistoryId')
                ;
                
                if (reportHistoryId != undefined && reportHistoryId != '' && reportHistoryId != 0) {
                    window.location = '/ajax/report/exportReport/'+exportFormat+'/'+reportHistoryId;
                } else {
                    alert('Please run a report to before exporting');
                }
            });
        },

        initRebuildReports: function() {

            var cookie = Report.Cookie.Tabs.get();

            if (cookie.length) {
                // Report.List.rebuildReports(Report.Cookie.Tabs.get());
            }
        },

        rebuildReports: function(reports) {

            Core.log('rebuildReports', 'group');

            $('#report-exports').add('#report-tabs-container').show();

            $('#report-landing-container').hide();

            if (! reports) {
                return;
            }
            $.each(reports, function(index, report) {

                var htmlHead   = '',
                    htmlReport = ''
                ;

                if (report.autoload) {
                    // do ajax request for report

                    $.ajax({
                        url: '/ajax/report/exportReport/html/'+report.id,
                        type: 'POST',
                        dataType: 'json',
                        data: {},
                        success: function(responseData) {
                            if (responseData.code === 0) {

                                var reporthistory_id = report.id;//(responseData.data.reporthistory_id != undefined) ? responseData.data.reporthistory_id : 0;

                                var uniqueString =  Report.List.renderReport(
                                    responseData.data.report_type_name,
                                    responseData.data.head,
                                    responseData.data.report,
                                    reporthistory_id
                                );



                                var $pane = $('#report-tab-'+uniqueString),
                                    $tab  = $('#report-result-tabs').find('li a[href="#report-tab-'+uniqueString+'"]'),
                                    title = $pane.find('.report-result-params').data('reportTitle')
                                ;

                                //$tab.closest('li').attr('title', title).data('placement', 'bottom').addClass('has-tooltip');
                                //Core.Tooltip.init();

                                $pane.find('h1').text(title);
                                $tab.find('.report-title').text(title);
                                //Core.log($tab.find('.report-title'), 'info');

                                $('#report-landing-container').hide();
                                $('#report-tabs-container').fadeIn(300);

                                // set autoload to false
                                Report.Cookie.Tabs.update('update', reporthistory_id, title, responseData.data.report_type_name, false);

                                Report.List.buildTooltips();

                            } else {
                                if (! $.isEmptyObject(responseData.validation_error)) {
                                    // display validation errors
                                }
                            }

                            if (! $.isEmptyObject(responseData.message)) {
                                //Core.SystemMessage.show(responseData.message, responseData.code);
                            }
                        }

                    });


                } else {

                    // show refresh report UI

                   // Core.log('Reload Button Added');

                    htmlHead =
                        '<button type="button" class="btn btn-default btn-load-report report-result-params" data-report-history-id="'+report.id+'" data-report-type="'+report.typename+'" >' +
                        '   Reload Report' +
                        '   <span class="loading-report">&nbsp;<img alt="Loading..." src="/assets/media/images/system-busy.gif" height="16" width="20.3" /></span>' +
                        '</button>'
                    ;

                    var uniqueString = Report.List.renderReport(report.typename, htmlHead, htmlReport, report.id),
                        $pane        = $('#report-tab-'+uniqueString),
                        $tab         = $('li a[href="#report-tab-'+uniqueString+'"]'),
                        title        = report.title//decodeURIComponent(report.title).replace(/\+/g, ' ')
                    ;

                    //Core.log('unique: '+uniqueString);

                    $tab.find('.report-title').text(title);
                    $pane.find('h1').text(title);

                    Report.List.buildTooltips();
                }

                //Core.log($tab, 'debug');

            });

            Core.log('rebuildReports', 'groupEnd');

        } ,

        initReloadReport: function() {

            $('#report-tabs-container').on('click', '.btn-load-report', function() {

                //Core.log('clicked Reload Report Button');

                var $self        = $(this),
                    historyId    = $self.data('reportHistoryId'),
                    //unique       = $self.closest('tab-pane')
                    $currentPane = $self.closest('.tab-pane'),
                    $currentTab  = $('#report-result-tabs').find('a[href="#'+$currentPane.prop('id')+'"]'),
                    $loading     = $self.find('.loading-report')
                ;

                $.ajax({
                    url: '/ajax/report/exportReport/html/'+historyId,
                    type: 'POST',
                    dataType: 'json',
                    data: {},
                    beforeSend: function() {
                        $loading.show();
                    },
                    success: function(responseData) {
                        if (responseData.code === 0) {


                            $currentPane.add($currentTab).remove();

                            var uniqueString =  Report.List.renderReport(
                                responseData.data.report_type_name,
                                responseData.data.head,
                                responseData.data.report,
                                historyId
                            );



                            var $pane = $('#report-tab-'+uniqueString),
                                $tab  = $('#report-result-tabs').find('li a[href="#report-tab-'+uniqueString+'"]'),
                                title = $pane.find('.report-result-params').data('reportTitle')
                            ;

                            $pane.find('h1').text(title);
                            $tab.find('.report-title').text(title);

                            $('#report-landing-container').hide();
                            $('#report-tabs-container').fadeIn(300);

                            // set autoload to false
                            Report.Cookie.Tabs.update('update', historyId, title, responseData.data.report_type_name, false);

                            Report.List.buildTooltips();

                        } else {
                            if (! $.isEmptyObject(responseData.validation_error)) {
                                // display validation errors
                            }
                        }

                        if (! $.isEmptyObject(responseData.message)) {
                            //Core.SystemMessage.show(responseData.message, responseData.code);
                        }
                    }

                });

            });


        } ,

        buildTooltips: function() {

            $('#report-result-tabs').find('li').each(function() {

                var $self = $(this),
                    title = $self.find('.report-title').text()
                ;

                $self.addClass('has-tooltip')
                     .attr('title', title)
                     .data('placement', 'bottom')
                ;


            });

            Core.Tooltip.init();
        }

    },

    Scheduled: {

        SecondaryPanel: {

        },

        DataTables: {

            initPaged: function() {

                $(document).on('change', '#scheduled-contact-mode', function() {
                    $('.dropdown-toggle-panel-scheduled-contact-group').hide();
                    $('.dropdown-toggle-panel-scheduled-contact-single').hide();
                    console.log('#scheduled-contact-mode:show:'+$('#scheduled-contact-mode').val());
                    $('.dropdown-toggle-panel-scheduled-contact-'+$('#scheduled-contact-mode').val()).show();
                });

                $(document).on('change', '#scheduled-recurrence', function() {
                    $('#div-scheduled-Weekly').hide();
                    $('#div-scheduled-Monthly').hide();
                    console.log('#scheduled-recurrence:show:'+$('#scheduled-recurrence').val());
                    switch($('#scheduled-recurrence').val()){
                        case                      'Monthly' :
                        case                       'Weekly' :   $('#div-scheduled-'+$('#scheduled-recurrence').val()).show();
                                                                break;
                    }
                });

            },

            init: function() {

                Report.Scheduled.DataTables.reportListTable = Core.DataTable.init('scheduled-reports-table', 20, {
                    'oLanguage': {
                        'sInfo': 'Showing _START_ to _END_ of _TOTAL_ Scheduled Reports'
                    },
                    'bServerSide': true,
                    'sAjaxSource':   '/ajax/report/getFilteredScheduleReports',
                    'aoColumns': [
                        { 'mDataProp': 'schedulereportname' },
                        { 'mDataProp': 'reporttypename' },
                        { 'mDataProp': 'contactname' },
                        //{ 'mDataProp': 'unitname' },
                        //{ 'mDataProp': 'territoryname' },
                        { 'mDataProp': 'schedule' },
                        { 'mDataProp': 'scheduleday' },
                        { 'mDataProp': 'sendhour' },
                        { 'mDataProp': 'nextruntime' }
                    ],
                    'aoColumnDefs': [
                        { 'sClass': 'col-name no-wrap', 'aTargets': [0] },
                        { 'sClass': 'col-type ',        'aTargets': [1] },
                        { 'sClass': 'col-contact',      'aTargets': [2] },
                        //{ 'sClass': 'col-name',         'aTargets': [3] },
                        //{ 'sClass': 'col-name',         'aTargets': [4] },
                        { 'sClass': 'col-recurrence',   'aTargets': [3] },
                        { 'sClass': 'col-recurrence',   'aTargets': [4] },
                        { 'sClass': 'col-recurrence',   'aTargets': [5] },
                        { 'sClass': 'col-next',         'aTargets': [6] }
                    ],
                    'fnRowCallback': function( nRow, aData, iDisplayIndex ) {
console.log('Report:Scheduled:DataTables:init:fnRowCallback');
                        // set <a> tag name and add data-landmark-id attribute to tr
                        $('td:eq(0)', nRow).html('<a href="#">'+aData.schedulereportname+'</a>');
                        $(nRow).data('schedulereportId', aData.schedulereport_id);
                        
                        // if the alert was set for a contact group, display the contact group name
                        if (aData.contactgroup_id != '' && aData.contactgroup_id != null && aData.contactgroupname != '' && aData.contactgroupname != null) {
                            $('td:eq(2)', nRow).text(aData.contactgroupname);            
                        } else if (aData.contact_id != '' && aData.contact_id != null && aData.contactname != '' && aData.contactname != null) {
                            $('td:eq(2)', nRow).text(aData.contactname);
                        }

                        /*
                        // if the alert was set for a unit group, display the unit group name
                        if (aData.unitgroup_id != '' && aData.unitgroup_id != null && aData.unitgroupname != '' && aData.unitgroupname != null) {
                            $('td:eq(3)', nRow).text(aData.unitgroupname);
                        } else if (aData.unitselection == 'all') {
                            $('td:eq(3)', nRow).text('All Vehicles');
                        }

                        // if the alert was set for a unit group, display the unit group name
                        if (aData.territorygroup_id != '' && aData.territorygroup_id != null && aData.territorygroupname != '' && aData.territorygroupname != null) {
                            $('td:eq(4)', nRow).text(aData.territorygroupname);
                        } else if (aData.territoryselection == 'all') {
                            $('td:eq(4)', nRow).text('All Landmarks');
                        }*/

                        return nRow;
                    },
                    'fnServerParams': function (aoData) {
console.log('Report:Scheduled:DataTables:init:fnServerParams');
                        var report_type     = $('#report-filter-type').val().trim();
                        var contactmode     = $('#report-filter-contact-mode').val().trim();
                        var contactgroup_id = $('#report-filter-contact-group').val().trim();
                        var contact_id      = $('#report-filter-contact').val().trim();

                        /*
                        var vehiclemode     = $('#report-filter-vehicle-mode').val().trim();
                        var vehiclegroup_id = $('#report-filter-vehicle-group').val().trim();
                        var vehicle_id      = $('#report-filter-vehicle').val().trim();

                        var landmarkmode     = $('#report-filter-landmark-mode').val().trim();
                        var landmarkgroup_id = $('#report-filter-landmark-group').val().trim();
                        var landmark_id      = $('#report-filter-landmark').val().trim();
                        */

                        var recurrance      = $('#report-filter-recurrence').val().trim();

                        var search_string   = '';
                        var filter_type     = 'group_filter';

                        var searchReportString = $('#text-report-search').val().trim();
                        if (typeof(searchReportString) != 'undefined' && searchReportString != '')
                        {
                            search_string   = searchReportString;
                            report_type     = 'all';
                            contactmode     = 'all';
                            contactgroup_id = '';
                            contact_id      = '';
                            
                            /*
                            vehiclemode     = 'all';
                            vehiclegroup_id = 'all';
                            vehicle_id      = 'all';

                            landmarkmode     = 'all';
                            landmarkgroup_id = 'all';
                            landmark_id      = 'all';                            
                            */
                            
                            recurrance      = 'all';

                            filter_type     = 'string_search';
                        }

                        aoData.push({name: 'search_string', value: search_string});
                        aoData.push({name: 'contactmode', value: contactmode});
                        aoData.push({name: 'contactgroup_id', value: contactgroup_id});
                        aoData.push({name: 'contact_id', value: contact_id});
                        
                        /*
                        aoData.push({name: 'vehiclemode', value: contactmode});
                        aoData.push({name: 'vehiclegroup_id', value: vehiclegroup_id});
                        aoData.push({name: 'vehicle_id', value: vehicle_id});

                        aoData.push({name: 'territorymode', value: landmarkmode});
                        aoData.push({name: 'territorygroup_id', value: landmarkgroup_id});
                        aoData.push({name: 'territory_id', value: landmark_id});
                        */
                        
                        aoData.push({name: 'reporttype_id', value: report_type});
                        aoData.push({name: 'recurrance', value: recurrance});

                        aoData.push({name: 'filter_type', value: filter_type});
                    }

                });

            }

        },

        initModal: function() {

            var $modal  = $('#modal-scheduled-report'),
                $body   = $('body')
            ;

            // listener for when an alert name is clicked in the Alerts datatable
            $(document).on('click', '.col-name a', function() {

                var $self = $(this),
                    $trNode = $self.closest('tr'),
                    scheduleReportId = $trNode.attr('id').split('-')[2],
                    $modal = $('#modal-scheduled-report')
                ;

                if (scheduleReportId != undefined) {
                    $.ajax({
                        url: '/ajax/report/getScheduleReportById',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            schedulereport_id: scheduleReportId
                        },
                        success: function(responseData) {
                            if (responseData.code === 0) {
                                var reportData = responseData.data.report;
                                if (! $.isEmptyObject(reportData)) {
                                    Core.Dialog.launch($modal.selector, 'Schedule Report', {
                                        width: '850px'
                                    },
                                    {
                                        hidden: function() {
console.log('Report:Scheduled:DataTables:initModal:Report.Scheduled.DataTables.reportListTable.fnStandingRedraw()');
                                            Report.Scheduled.DataTables.reportListTable.fnStandingRedraw();
                                        },
                                        show: function() {
                                        
                                            $modal.find('.modal-title').text('').hide();

                                            // reset More Options
                                            $('#report-more-options-toggle').find('small').text('Show More Options');
                                            $('#report-more-options').hide();
                                            
                                            // don't allow loading update
                                            Report.Scheduled.allowUpdate = false;
                                        },
                                        shown: function() {

                                            // reset More Options
                                            $('#report-more-options-toggle').find('small').text('Show More Options');
                                            $('#report-more-options').hide();

                                            $modal.find('.modal-title').text($self.text()).fadeIn(100);
                                            $('#detail-panel').find('.hook-editable-keys').data('reportPk', scheduleReportId);
                                            $('#detail-panel').find('.hook-editable-keys').data('reportTypeId', reportData.reporttype_id);

                                            // set alert name
                                            Core.Editable.setValue($('#report-name'), reportData.schedulereportname);
                                            Core.Editable.setValue($('#report-schedule-name'), reportData.schedulereportname);
                                            
                                            // trigger/set this reporttype
                                            $('#report-schedule-type').siblings('ul').eq(0).find('a').filter('[data-value="'+reportData.reporttype_id+'"]').trigger('click');

                                            var $vehicleMode        = $('#report-vehicle-mode'),
                                                $vehicleModeForm    = $vehicleMode.closest('.form-group'),
                                                $reportDates        = $('#report-dates'),
                                                $reportDatesForm    = $reportDates.closest('.form-group')
                                            ;

                                            reportTypeId = reportData.reporttype_id;
                                            
                                            // enforce Single Vehicle Mode only for these reports
                                            switch (reportTypeId) {
                                                case '3'://'detail':
                                                case '11'://'stop':
                                                case '4'://'frequentStops':
                                                case '15'://'lastTenStops':
                                                    $vehicleMode.siblings('ul').find('li a').filter('[data-value="single"]').trigger('click');
                                                    $vehicleModeForm.hide();
                                                    break;
                                                default:
                                                    $vehicleMode.siblings('ul').find('li a').filter('[data-value="all"]').trigger('click');
                                                    if (reportTypeId != '12') {  // hide Vehicle Mode for User Command report
                                                        $vehicleModeForm.show();
                                                    } else {
                                                        $vehicleModeForm.hide();
                                                    }
                                                    break;
                                            }
                                            
                                            // hide Date Range for these reports
                                            switch (reportTypeId) {
                                                case '6'://'mileageSummary':
                                                case '7'://'nonReporting':
                                                case '15'://'lastTenStops':
                                                case '9'://'starterDisableSummary':
                                                case '10'://'stationary':
                                                case '13'://'vehicleInformation':
                                                case '14'://'verificationOfReference':
                                                    $reportDatesForm.hide();
                                                    break;
                                                default:
                                                    $reportDatesForm.show();
                                                    break;
                                            }
                                            
                                            // special vehicle mode show/hide case
                                            if (reportTypeId == '10' || reportTypeId == '13') {
                                                $vehicleMode.siblings('ul').find('li a').filter(':not([data-value="all"])').hide();
                                                $vehicleMode.addClass('disabled');
                                                $vehicleMode.siblings('button').eq(0).addClass('disabled');
                                            } else {
                                                $vehicleMode.siblings('ul').find('li a').filter(':not([data-value="all"])').show();
                                                $vehicleMode.removeClass('disabled');
                                                $vehicleMode.siblings('button').eq(0).removeClass('disabled');
                                            }
                                            
                                            // special vehicle mode show/hide case must come after above stationary case
                                            if(reportTypeId == '7') {
                                                //hide single vehicle option
                                                $vehicleMode.siblings('ul').find('li a').filter('[data-value="single"]').hide();
                                            } else {
                                                $vehicleMode.siblings('ul').find('li a').filter('[data-value="single"]').show();
                                            }

                                            // show and set associated filtering for the particular report type
                                            Core.Editable.setValue($('#report-alert-type'), reportData.alerttype_id);

                                            //'frequent Stops':
                                            Core.Editable.setValue($('#report-frequent-stops-duration-threshold'), parseInt(reportData.minute));
                                            //'mileage summary': mileage filter
                                            Core.Editable.setValue($('#report-mileage-filter'), parseInt(reportData.mile));

                                            //'non reporting': non reporting day threshold
                                            Core.Editable.setValue($('#report-not-reporting'), parseInt(reportData.day));

                                            //'speed': speed mph threshold
                                            Core.Editable.setValue($('#report-speed-filter'), parseInt(reportData.mph));

                                            //'stationary': day threshold
                                            Core.Editable.setValue($('#report-stationary-filter'), parseInt(reportData.day));

                                            //'stop':  day threshold
                                            Core.Editable.setValue($('#report-stop-threshold'), parseInt(reportData.minute));

                                            //'user command': day threshold
                                            Core.Editable.setValue($('#report-user-command-user'), reportData.filter_user_id);

                                            //'reference': verification option
                                            Core.Editable.setValue($('#report-reference'), reportData.verification);

                                            //'Landmark':
                                            if (reportData.territory_id != undefined && reportData.territory_id != '') {
                                                Core.Editable.setValue($('#report-landmark-single'), reportData.territory_id);
                                            } else if (reportData.territorygroup_id != undefined && reportData.territorygroup_id != '') {
                                                Core.Editable.setValue($('#report-landmark-group'), reportData.territorygroup_id);
                                            }

                                            if (reportData.landmark_mode != undefined && reportData.landmark_mode != '') {
                                                $('#report-landmark-mode').siblings('ul').eq(0).find('a').filter('[data-value="'+reportData.landmark_mode+'"]').trigger('click');
                                            }

                                            // set vehicle section
                                            if (reportData.unit_id != undefined && reportData.unit_id != '') {
                                                Core.Editable.setValue($('#report-vehicle-single'), reportData.unit_id);
                                            } else if (reportData.unitgroup_id != undefined && reportData.unitgroup_id != '') {
                                                Core.Editable.setValue($('#report-vehicle-group'), reportData.unitgroup_id);
                                            }

                                            if (reportData.vehicle_mode != undefined && reportData.vehicle_mode != '') {
                                                $('#report-vehicle-mode').siblings('ul').eq(0).find('a').filter('[data-value="'+reportData.vehicle_mode+'"]').trigger('click');
                                            }

                                            // date range, clear first
                                            $('#report-dates').val('');
                                            if (reportData.range != undefined && reportData.range != '') {
                                                reportRange = '';
                                                dateRange   = reportData.range.replace(/\s{2,}/g, ' ').split(' ');

                                                if (dateRange[1] == 'month') {
                                                    if (parseInt(dateRange[0]) == 0) {
                                                        reportRange = 'This Month';
                                                    } else {
                                                        reportRange = 'Last Month';
                                                    }
                                                } else {
                                                    if (parseInt(dateRange[0]) == 0) {
                                                        reportRange = 'Today';
                                                    } else if (parseInt(dateRange[0]) == 1) {
                                                        reportRange = 'Yesterday';
                                                    } else {
                                                        reportRange = 'Last '+dateRange[0]+' Days';
                                                    }
                                                }

                                                $('#report-dates').val(reportRange);
                                            }
                                            
                                            //schedule
                                            $('#scheduled-recurrence').siblings('ul').eq(0).find('a').filter('[data-value="'+reportData.schedule+'"]').trigger('click');
                                            $('#detail-panel').find('.hook-editable-keys').data('schedule', reportData.schedule);
                                            if (reportData.schedule != undefined && reportData.schedule == 'Monthly') {
                                                Core.Editable.setValue($('#scheduled-monthly'), reportData.monthday);
                                                $('#detail-panel').find('.hook-editable-keys').data('monthDay', reportData.monthday);
                                            } else if (reportData.schedule != undefined && reportData.schedule == 'Weekly') {
                                                Core.Editable.setValue($('#scheduled-day'), reportData.scheduleday);
                                                $('#detail-panel').find('.hook-editable-keys').data('scheduleyDay', reportData.scheduleday);
                                            }
                                            
                                            // send hour
                                            $('#detail-panel').find('.hook-editable-keys').data('sendHour', reportData.sendhour);
                                            Core.Editable.setValue($('#scheduled-time'), parseInt(reportData.sendhour));
                                            
                                            //format
                                            Core.Editable.setValue($('#scheduled-format'), reportData.format);
                                            
                                            // contact
                                            if (reportData.contact_id != undefined && reportData.contact_id != '') {
                                                $('#report-contact-mode').siblings('ul').eq(0).find('a').filter('[data-value="single"]').trigger('click');
                                                Core.Editable.setValue($('#report-contact-single'), reportData.contact_id);
                                            } else if (reportData.contactgroup_id != undefined && reportData.contactgroup_id != '') {
                                                $('#report-contact-mode').siblings('ul').eq(0).find('a').filter('[data-value="group"]').trigger('click');
                                                Core.Editable.setValue($('#report-contact-group'), reportData.contactgroup_id);
                                            }
                                            
                                            // allow update
                                            Report.Scheduled.allowUpdate = true;
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

            // Listener for changing the report type dropdown option in the edit modal
            $body.on('Core.DropdownButtonChange', '#report-schedule-type', function() {

                var $self               = $(this),
                    $vehicleMode        = $('#report-vehicle-mode'),
                    $vehicleModeForm    = $vehicleMode.closest('.form-group'),
                    $reportDates        = $('#report-dates'),
                    $reportDatesForm    = $reportDates.closest('.form-group'),
                    $reportTypeId       = $('#report-schedule-type').val(),
                    reportType          = $('#report-schedule-type').siblings('ul').eq(0).find('a').filter('[data-value="'+$reportTypeId+'"]').data('reportType'),
                    id                  = $self.prop('id'),
                    value               = $self.val(),
                    reportId            = $('#detail-panel').find('.hook-editable-keys').data('reportPk'),
                    doUpdate            = false
                ;

                if ($self.prop('id') == 'report-schedule-type') {
                    // update the reportTypeId on the model for update use
                    $('#detail-panel').find('.hook-editable-keys').data('reportTypeId', $reportTypeId);

                    // enforce Single Vehicle Mode only for these reports
                    switch (reportType) {
                        case 'detail':
                        case 'stop':
                        case 'frequentStops':
                        case 'lastTenStops':
                            $vehicleMode.siblings('ul').find('li a').filter('[data-value="single"]').trigger('click');
                            $vehicleModeForm.hide();
                            break;
                        default:
                            $vehicleMode.siblings('ul').find('li a').filter('[data-value="all"]').trigger('click');
                            if (reportType != 'userCommand') {  // hide Vehicle Mode for User Command report
                                $vehicleModeForm.show();
                            } else {
                                $vehicleModeForm.hide();
                            }
                            break;
                    }
    
                    // hide Date Range for these reports
                    switch (reportType) {
                        case 'mileageSummary':
                        case 'nonReporting':
                        case 'lastTenStops':
                        case 'starterDisableSummary':
                        case 'stationary':
                        case 'vehicleInformation':
                        case 'verificationOfReference':
                            $reportDatesForm.hide();
                            break;
                        default:
                            $reportDatesForm.show();
                            break;
                    }
                    
                    // special vehicle mode show/hide case
                    if (reportType == 'stationary' || reportType == 'vehicleInformation') {
                        $vehicleMode.siblings('ul').find('li a').filter(':not([data-value="all"])').hide();
                        $vehicleMode.addClass('disabled');
                        $vehicleMode.siblings('button').eq(0).addClass('disabled');
                    } else {
                        $vehicleMode.siblings('ul').find('li a').filter(':not([data-value="all"])').show();
                        $vehicleMode.removeClass('disabled');
                        $vehicleMode.siblings('button').eq(0).removeClass('disabled');
                    }
                    
                    // special vehicle mode show/hide case must come after above stationary case
                    if(reportType == 'nonReporting') {
                        //hide single vehicle option
                        $vehicleMode.siblings('ul').find('li a').filter('[data-value="single"]').hide();
                    } else {
                        $vehicleMode.siblings('ul').find('li a').filter('[data-value="single"]').show();
                    }

                    // special vehicle mode show/hide case must come after above stationary case
                    if(reportType == 'alert') {
                        Core.Editable.setValue($('#report-alert-type'), '');
                    }

                    // special vehicle mode show/hide case must come after above stationary case
                    if(reportType == 'verificationOfReference') {
                        Core.Editable.setValue($('#report-reference'), '');
                    }

                }

                // simulate auto saving for these reporttypes: Detailed Event, Starter Disable, Vehicle Info, Verification, Last ten stop
                if (id == 'report-schedule-type') {
                    if (value == '3' || value == '5' || value == '9' || value == '13' || value == '15') {
                        data = {
                            "primary_keys" : 
                                {
                                    "reportPk" : reportId 
                                },
                                "id" : id,
                                "value" : value
                        };
                        
                        doUpdate = true;
                    }

                    if (Report.Scheduled.allowUpdate === true) {
                        if (doUpdate) {
                            $.ajax({
                                url: '/ajax/report/updateScheduledReportInfo',
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
                    }
                }
            });

            // Listener for updating vehicle/landmark mode or recurrence dropdown
            $body.on('Core.DropdownButtonChange', '#report-vehicle-mode, #report-landmark-mode, #scheduled-recurrence', function() {
                var $self           = $(this),
                    value           = $self.val(),
                    id              = $self.prop('id'),
                    reportId        = $('#detail-panel').find('.hook-editable-keys').data('reportPk'),
                    reportTypeId    = $('#report-schedule-type').val(),
                    data            = {},
                    doUpdate        = false;
                    editable        = $('#report-list-table').data('editable') // indicator for if user has permission to edit alerts
                ;

                data = {
                    "primary_keys" : 
                        {
                            "reportPk" : reportId,
                            "reportTypeId" : reportTypeId
                        },
                        "id" : id,
                        "value" : value
                };

                if ((id == 'report-vehicle-mode' || id == 'report-landmark-mode') && value == 'all') {
                    // must have filtering values if vehicle or landmark mode is all
                    if (reportTypeId == '1') {
                        textvalue = $('#report-alert-type').text();
                        if ( textvalue == 'Not Set') {
                            doUpdate = false;
                        } else {
                            doUpdate = true;
                        }
                    } else if (reportTypeId == '6') {
                        textvalue = $('#report-mileage-filter').text();
                        if ( textvalue == 'Not Set') {
                            doUpdate = false;
                        } else {
                            doUpdate = true;
                        }
                    } else if (reportTypeId == '7') {
                        textvalue = $('#report-not-reporting').text();
                        if ( textvalue == 'Not Set') {
                            doUpdate = false;
                        } else {
                            doUpdate = true;
                        }
                    } else if (reportTypeId == '8') {
                        textvalue = $('#report-speed-filter').text();
                        if ( textvalue == 'Not Set') {
                            doUpdate = false;
                        } else {
                            doUpdate = true;
                        }
                    } else if (reportTypeId == '10') {
                        textvalue = $('#report-stationary-filter').text();
                        if ( textvalue == 'Not Set') {
                            doUpdate = false;
                        } else {
                            doUpdate = true;
                        }
                    } else if (reportTypeId == '14') {
                        textvalue = $('#report-reference').text();
                        if ( textvalue == 'Not Set') {
                            doUpdate = false;
                        } else {
                            doUpdate = true;
                        }
                    } else {
                        doUpdate = true;
                    }
                }

                // if recurrence is daily, simulate auto saving
                if (id == 'scheduled-recurrence' && value == 'Daily') {
                    doUpdate = true;
                }

                if (Report.Scheduled.allowUpdate === true) {
                    if (doUpdate) {
                        $.ajax({
                            url: '/ajax/report/updateScheduledReportInfo',
                            type: 'POST',
                            dataType: 'json',
                            data: data,
                            success: function(responseData) {
                                if (responseData.code === 0) {
                                    // update table
                                    //Report.Scheduled.DataTables.reportListTable.fnStandingRedraw();
console.log('Report:Scheduled:DataTables:initModal://Report.Scheduled.DataTables.reportListTable.fnStandingRedraw()');
                                    
                                    // update dropdowns
                                    if (id == 'report-landmark-mode' && value == 'all') {
                                        Core.Editable.setValue($('#report-landmark-single'), '');
                                        Core.Editable.setValue($('#report-landmark-group'), '');
                                    }

                                    if (id == 'report-vehicle-mode' && value == 'all') {
                                        Core.Editable.setValue($('#report-vehicle-single'), '');
                                        Core.Editable.setValue($('#report-vehicle-group'), '');
                                    }

                                    if (id == 'scheduled-recurrence' && value == 'Daily') {
                                        Core.Editable.setValue($('#scheduled-day'), '');
                                        Core.Editable.setValue($('#scheduled-monthly'), '');
                                    } else if (id == 'scheduled-recurrence' && value == 'Weekly') {
                                        Core.Editable.setValue($('#scheduled-monthly'), '');
                                    } else if (id == 'scheduled-recurrence' && value == 'Monthly') {
                                        Core.Editable.setValue($('#scheduled-day'), '');
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
                }
                
            });


            // Listener for updated vehicle/landmark single and group updates
            $body.on('Core.FormElementChanged', '#report-vehicle-single, #report-vehicle-group, #report-landmark-single, #report-landmark-group', function(event, extraParams) {

                extraParams = extraParams || {
                    value: false,
                    id:    false
                };

                // require value and pk
                if (! $.isEmptyObject(extraParams.data)) {

                    var id = extraParams.data.id;
                    var value = extraParams.data.value;

                    // update dropdowns
                    if (id == 'report-landmark-group' && value != '0') {
                        Core.Editable.setValue($('#report-landmark-single'), '');
                    } else if (id == 'report-landmark-single' && value != '0') {
                        Core.Editable.setValue($('#report-landmark-group'), '');
                    }

                    if (id == 'report-vehicle-group' && value != '0') {
                        Core.Editable.setValue($('#report-vehicle-single'), '');
                    } else if (id == 'report-vehicle-single' && value != '0') {
                        Core.Editable.setValue($('#report-vehicle-group'), '');
                    }
                }                                                   
            });

            // Listener for updating 'date range' (simulate inline editing)
           $body.on('DatePicker.change', function(){
                var $dateRange  = $('#report-dates'),
                    reportId    = $('#detail-panel').find('.hook-editable-keys').data('reportPk'),
                    value       = $('#report-dates').val(),
                    range       = value.split('-'),
                    data        = {}
                ;

                if (range.length < 2) {
                    data = {
                        "primary_keys" : 
                            {
                                "reportPk" : reportId 
                            },
                            "id" : "report-dates",
                            "value" : value
                    };
                    
                    if (value != '') {
    
                        $.ajax({
                            url: '/ajax/report/updateScheduledReportInfo',
                            type: 'POST',
                            dataType: 'json',
                            data: data,
                            success: function(responseData) {
                                if (responseData.code === 0) {
                                    // update alert table
                                    //Report.Scheduled.DataTables.reportListTable.fnStandingRedraw();
console.log('Report:Scheduled:DataTables:initModal://Report.Scheduled.DataTables.reportListTable.fnStandingRedraw()');
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
                } else {
                    alert('Custom Date Range Not Invalid for Scheduled Reports.');
                }
            });

            //  When Name Changed
            $('#report-schedule-name').on('Core.FormElementChanged', function(event, extraParams) {
                extraParams = extraParams || {
                    value: false,
                    pk:    false
                };

                // require value and pk
                if (! $.isEmptyObject(extraParams.value) && !$.isEmptyObject(extraParams.pk)) {

                    // update title in modal
                    $('#modal-scheduled-report').find('.modal-title').text(extraParams.value);      
                    // update title in table row
                    $('#schedulereport-tr-'+extraParams.pk.reportPk).find('td a').text(extraParams.value);
                }
            });

            // More Options
            $('#report-more-options-toggle').on('click', function() {
                var $self    = $(this),
                    $selfText = $self.find('small'),
                    selfTextValue = $selfText.text()
                ;

                if (selfTextValue == 'Show More Options') {
                    $selfText.text('Show Less Options')
                } else if (selfTextValue == 'Show Less Options') {
                    $selfText.text('Show More Options')
                }

                $('#report-more-options').slideToggle();
            });
            
            // Delete schedule report
            $body.on('click', '#popover-report-delete-confirm', function() {
                var $modal = $('#modal-scheduled-report'),
                    reportId = $('#detail-panel').find('.hook-editable-keys').data('reportPk')
                ;

                if (reportId != undefined && reportId != '') {
                    $.ajax({
                        url: '/ajax/report/deleteScheduledReport',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            schedulereport_id: reportId
                        },
                        success: function(responseData) {
                            if (responseData.code === 0) {
                                // close 'Delete Report' popover
                                $('#popover-report-delete-cancel').trigger('click');

                                // close 'Edit Report' modal
                                $modal.find('.modal-footer button').trigger('click');
                                
                                // update schedule report table
                                Report.Scheduled.DataTables.reportListTable.fnStandingRedraw();
console.log('Report:Scheduled:DataTables:initModal:Report.Scheduled.DataTables.reportListTable.fnStandingRedraw()');                                                                        
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

        },
        
        initScheduleReportSearch: function() {

            var $reportSearch       = $('#text-report-search');
            var $reportSearchGo     = $('#report-search-go');
            var $contactModeFilter  = $('#report-filter-contact-mode');
            var $recurranceFilter   = $('#report-filter-recurrence');
            var $body = $('body');
            /*
            var $vehicleModeFilter     = $('#report-filter-vehicle-mode');
            var $vehicleGroupFilter = $('#report-filter-vehicle-group');
            var $vehicleFilter      = $('#report-filter-vehicle');
            var $landmarkModeFilter     = $('#report-filter-landmark-mode');
            var $landmarkGroupFilter = $('#report-filter-landmark-group');
            var $landmarkFilter      = $('#report-filter-landmark');
            */

            /**
             *
             * On keyup when searching alerts using search string 
             *
             */
            $reportSearch.on('keyup', function () {
                
                // get current search string
                var searchReportString = $reportSearch.val().trim();

                if (searchReportString.length > 1) {
                    Report.Scheduled.DataTables.reportListTable.fnDraw();
console.log('Report:Scheduled:DataTables:initScheduleReportSearch:Report.Scheduled.DataTables.reportListTable.fnDraw()');
                } else if (searchReportString.length == 0) {
                    Report.Scheduled.DataTables.reportListTable.fnDraw();
console.log('Report:Scheduled:DataTables:initScheduleReportSearch:Report.Scheduled.DataTables.reportListTable.fnDraw()');
                }
                
                $('#report-filter-type').val('all').text('All');
                $('#report-filter-contact-mode').val('all').text('All Contacts');
                $('#report-filter-contact-group').val('').text('Select One');
                $('#report-filter-contact').val('').text('Select One');
                
                /*
                $('#report-filter-vehicle-mode').val('all').text('All Vehicles');
                $('#report-filter-vehicle-group').val('all').text('All');
                $('#report-filter-vehicle').val('all').text('All');
                $('#report-filter-landmark-mode').val('all').text('All Landmarks');
                $('#report-filter-landmark-group').val('all').text('All');
                $('#report-filter-landmark').val('all').text('All');
                */
                
                $('#report-filter-recurrence').val('all').text('All');

            });

            /**
             *
             * On Search Button Click when searching alerts using search string 
             *
             */
            $reportSearchGo.on('click', function () {
                // get current search string
                var searchReportString = $reportSearch.val().trim();

                if (searchReportString != '') {
                    Report.Scheduled.DataTables.reportListTable.fnDraw();
console.log('Report:Scheduled:DataTables:initScheduleReportSearch:Report.Scheduled.DataTables.reportListTable.fnDraw()');
                }
                $('#report-filter-type').val('all').text('All');
                $('#report-filter-contact-mode').val('all').text('All Contacts');
                $('#report-filter-contact-group').val('').text('Select One');
                $('#report-filter-contact').val('').text('Select One');
                
                /*
                $('#report-filter-vehicle-mode').val('all').text('All Vehicles');
                $('#report-filter-vehicle-group').val('all').text('All');
                $('#report-filter-vehicle').val('all').text('All');
                $('#report-filter-landmark-mode').val('all').text('All Landmarks');
                $('#report-filter-landmark-group').val('all').text('All');
                $('#report-filter-landmark').val('all').text('All');
                */
                
                $('#report-filter-recurrence').val('all').text('All');

            });

            /**
             *
             * On Filter dropdown changes, redraw schedulereport datatable list
             *
             */
            $body.on('Core.DropdownButtonChange', '#report-filter-type, #report-filter-contact-group, #report-filter-contact, #report-filter-recurrence, #report-filter-contact-mode', function() {

                // clear out the search box before redrawing table
                $('#text-report-search').val('');

                if ($(this).prop('id') == 'report-filter-contact-mode') {
                    $('#report-filter-contact-group').val('').text('Select One');
                    $('#report-filter-contact').val('').text('Select One');
                }

                // redraw table
                Report.Scheduled.DataTables.reportListTable.fnDraw();
console.log('Report:Scheduled:DataTables:initScheduleReportSearch:Report.Scheduled.DataTables.reportListTable.fnDraw()');
            });

        }
        

    },

    History: {

        DataTables: {

            init: function() {

                Report.History.DataTables.reportHistoryTable = Core.DataTable.init('report-history-table', 20, {
                    'oLanguage': {
                        'sInfo': 'Showing _START_ to _END_ of _TOTAL_ Reports Ran'
                    },
                    'bServerSide': true,
                    'sAjaxSource':   '/ajax/report/getFilteredReportHistory',
                    'aoColumns': [
                        { 'mDataProp': 'reporttypename' },
                        { 'mDataProp': 'reporthistoryname' },
                        { 'mDataProp': 'method' },
                        { 'mDataProp': 'username' },
                        { 'mDataProp': 'reportrantime' },
                        { 'mDataProp': 'link' }
                    ],
                    'aaSorting': [
                        [4,'desc'],
                        [0,'asc']
                    ],
                    'aoColumnDefs': [
                        { 'sClass': 'col-type ',        'aTargets': [0] },
                        { 'sClass': 'col-name no-wrap', 'aTargets': [1] },
                        { 'sClass': 'col-method ',      'aTargets': [2] },
                        { 'sClass': 'col-user',         'aTargets': [3] },
                        { 'sClass': 'col-date',         'aTargets': [4] },
                        { 'sClass': 'col-link',         'aTargets': [5] }
                    ],
                    'fnRowCallback': function( nRow, aData, iDisplayIndex ) {

                        $('td:eq(5)', nRow).html('<a href="#" id="reporthistory-id-'+aData.reporthistory_id+'"><span class="run-history glyphicon glyphicon-new-window"></span></a>');
                        $(nRow).data('reportHistoryId', aData.reporthistory_id);

                        return nRow;
                    },
                    'fnServerParams': function (aoData) {

                        var report_type     = $('#report-type-filter').val().trim();
                        var user_id         = $('#report-generated-filter').val().trim();
                        var dateRange       = $('#report-date-filter');
                        var dayRange        = dateRange.val();
                        var startTime       = dateRange.data('startDate');
                        var endTime         = dateRange.data('endDate');

                        var search_string   = '';
                        var filter_type     = 'group_filter';

                        var searchReportString = $('#text-reporthistory-search').val().trim();
                        if (typeof(searchReportString) != 'undefined' && searchReportString != '')
                        {
                            search_string   = searchReportString;
                            report_type     = 'all';
                            user_id         = 'all';
                            dayRange        = '';
                            startTime       = '';
                            endTime         = '';
                            filter_type     = 'string_search';
                        }

                        aoData.push({name: 'search_string', value: search_string});
                        aoData.push({name: 'user_id', value: user_id});
                        aoData.push({name: 'starttime', value: startTime});
                        aoData.push({name: 'endtime', value: endTime});
                        aoData.push({name: 'dayrange', value: dayRange});
                        aoData.push({name: 'reporttype_id', value: report_type});
                        aoData.push({name: 'filter_type', value: filter_type});

                    }

                });

            }
        },

        Modal: {

            init: function() {

                var $historyTable = $('#report-history-table'),
                    $modal        = $('#modal-history-confirm')
                ;


                $historyTable.on('click', '.run-history', function() {

                    var $self           = $(this),
                        $trNode         = $self.closest('tr'),
                        title           = 'Confirm Rerunning of '+$self.closest('tr').find('.col-name').text(),
                        data            = $self.data(),
                        historyId       = $self.closest('a').prop('id').split('-')[2],
                        reportHistoryId = $trNode.attr('id').split('-')[2],
                        historyTitle    = $self.closest('tr').find('.col-name').text(),
                        reportTitle     = $self.closest('tr').find('.col-name').text(),
                        historyType     = $self.closest('tr').find('.col-type').text().toLowerCase().replace(/ /g, '-'),
                        cookieCount     = Report.Cookie.Tabs.get().length,
                        $runButton      = $modal.find('.report-history-run')
                    ;


                    if (cookieCount == 8) {
                        alert("You've reached the maximum of 8 concurrent reports");
                        return false;
                    }

                    Core.Dialog.launch($modal.selector, title,
                        {
                            width: '500px'
                        },
                        {
                            hidden: function() {

                            },
                            show: function() {

                                $('.popover').find('.close').trigger('click');

                            },
                            shown: function() {
                                $modal.find('.report-history-run').data('historyId', historyId)
                                                                  .data('historyTitle', historyTitle)
                                                                  .data('historyType', historyType)
                                ;
                            }
                        }
                    );
                });

                /* clicking the run button */
                $modal.find('.report-history-run').on('click', function() {

                    var $self = $(this);

                    Report.Cookie.Tabs.update('add', $self.data('historyId'), $self.data('historyTitle'), $self.data('historyType'), true);
                    Report.Cookie.TabTypes.update('increment', $self.data('historyType'));
                    $modal.fadeOut(300);

                    $('#secondary-nav').find('a[href="/report/list"]').trigger('click');
                });

            }

        },

        initReportHistorySearch: function() {

            var $reportSearch               = $('#text-reporthistory-search');
            var $reportSearchGo             = $('#reporthistory-search-go');
            var $reportHistoryDatePicker    = $('#report-date-filter');
            var $body = $('body');

            /**
             *
             * On keyup when searching alerts using search string 
             *
             */
            $reportSearch.on('keyup', function () {
                
                // get current search string
                var searchReportString = $reportSearch.val().trim();

                if (searchReportString.length > 1) {
                     Report.History.DataTables.reportHistoryTable.fnDraw();
                } else if (searchReportString.length == 0) {
                     Report.History.DataTables.reportHistoryTable.fnDraw();
                }
                
                $('#report-type-filter').val('all').text('All');
                $('#report-generated-filter').val('all').text('All');
                $('#report-date-filter').val('');
                $('#report-date-filter').data('startDate', '');
                $('#report-date-filter').data('endDate', '');
            });

            /**
             *
             * On Search Button Click when searching alerts using search string 
             *
             */
            $reportSearchGo.on('click', function () {
                // get current search string
                var searchReportString = $reportSearch.val().trim();

                if (searchReportString != '') {
                     Report.History.DataTables.reportHistoryTable.fnDraw();
                }
                
                $('#report-type-filter').val('all').text('All');
                $('#report-generated-filter').val('all').text('All');
                $('#report-date-filter').val('');
                $('#report-date-filter').data('startDate', '');
                $('#report-date-filter').data('endDate', '');

            });

            /**
             *
             * On Filter dropdown changes, redraw schedulereport datatable list
             *
             */
            $body.on('Core.DropdownButtonChange', '#report-type-filter, #report-generated-filter', function() {

                // clear out the search box before redrawing table
                $('#text-reporthistory-search').val('');

                // redraw table
                 Report.History.DataTables.reportHistoryTable.fnDraw();
            });
            
            /**
             *
             * On Date Range Filter dropdown changes, redraw reporthistory datatable list
             *
             */
           $reportHistoryDatePicker.on('DatePicker.change', function(){

               // clear out the search box before redrawing table
               $('#text-reporthistory-search').val('');
               
               Report.History.DataTables.reportHistoryTable.fnDraw();
           });

        },

        initRun: function() {

            var $subPanel = $('#sub-panel');

            $subPanel.on('click', 'li', function() {

                var $self      = $(this),
                    data       = $self.data(),
                    reportType = data.reportType
                ;

                window.location = '/ajax/report/'+reportType+'/run';

            });

        }

    },

    Cookie: {

        Tabs: {

            cookieName: 'report_tabs',

            get: function() {

                var cookie = Core.Cookie.get(Report.Cookie.Tabs.cookieName),
                    output = null
                ;

                if (cookie) {
                    output = cookie
                } else {
                    // output = Report.Cookie.Tabs.set([]);
                    output = 1;
                }

                return output;
            },

            set: function(object) {

                return Core.Cookie.set(Report.Cookie.Tabs.cookieName, object);

            },

            /**
             * Updates and Sets the Report Cookie
             *
             * action == 'update' updates an existing report in the cookie // all params required
             *           'remove' removes an existing report in the cookie // only id param is required
             *           'add'    add a new report to the cookie           // all params are required
             *
             * @param action
             * @param id
             * @param title
             * @param typename
             * @param autoload
             */
            update: function(action, id, title, typename, autoload) {

console.log('update: function(action='+action+', id='+id+', title='+title+', typename='+typename+', autoload='+autoload+')'+cookie+'=='+cookieCount);

                var cookieCount = title.split(' ').pop();

                var cookie = Report.Cookie.Tabs.get();

                switch (action) {

                    case 'remove':

                        // $.each(cookie, function(index, cookieReport) {

                        //     var cont = true;

                        //     if (cookieReport.id == id) {

                        //         if (cookie.hasOwnProperty(index)) {
                        //             if (isNaN(parseInt(index)) || ! (cookie instanceof Array)) {
                        //                 delete cookie[index];
                        //             } else {
                        //                 cookie.splice(index, 1);
                        //             }
                        //         }
                        //         cont =  false;
                        //     }

                        //     return cont;
                        // });

                        Core.Cookie.clear(typename);

                        break;

                    case 'add':

                        cookieCount++;

                        Core.Cookie.set(typename,cookieCount);

                        // cookie.push({
                        //     id: parseInt(id),
                        //     title : title,
                        //     typename: typename,
                        //     autoload: autoload
                        // });

                        break;

                    case 'update':

                        Core.Cookie.set(typename,cookieCount);

                        // $.each(cookie, function(index, cookieReport) {

                        //     var cont = true;

                        //     if (cookieReport.id == id) {

                        //         $.extend(cookie[index], {
                        //             id: parseInt(id),
                        //             title : title,
                        //             typename: typename,
                        //             autoload: (autoload == true)
                        //         });
                        //         cont =  false;
                        //     }

                        //     return cont;
                        // });

                        break;
                }

                return typename+' '+cookieCount;
            },

            toConsole: function() {
                Core.log(Report.Cookie.Tabs.get());
            }
        },

        TabTypes: {

            cookieName: 'report_tab_types',

            get: function() {

                var cookie = Core.Cookie.get(Report.Cookie.TabTypes.cookieName),
                    output = null
                ;

                if (cookie) {
                    output = cookie

                } else {
                    output = Report.Cookie.TabTypes.set({});
                }

                return output;

            },

            set: function(object) {

                return Core.Cookie.set(Report.Cookie.TabTypes.cookieName, object);

            },

            update: function(action, type) {

                if(type){
                    type = type.replace(/\-/g, '').replace(/ /g, '').toLowerCase();
                }

                var cookie = Report.Cookie.TabTypes.get();
                var cookie = cookieArray;

                if ( ! cookie.hasOwnProperty(type)) {
                    cookie[type] = 0;
                }

                Core.log(cookie);

                switch (action) {

                    case 'increment':

                        cookie[type]++;
                        break;

                    case 'decrement':

                        cookie[type]--;
                        if (cookie[type] < 0) {
                            cookie[type] = 0
                        };
                        break;
                }

                return Report.Cookie.TabTypes.set(cookie);

            },

            getTypeCount: function(type) {

                type = type.replace(/\-/g, '').replace(/ /g, '').toLowerCase();

                var cookie = Report.Cookie.TabTypes.get();

                if ( ! cookie.hasOwnProperty(type)) {
                    cookie[type] = 0;
                }

                return cookie[type];

            },

            toConsole: function() {
                Core.log(Report.Cookie.TabTypes.get());
            }

        }



    }

});