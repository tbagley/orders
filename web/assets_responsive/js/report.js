
$(document).ready(function() {

    /**
     *
     * Page Specific Functionality
     *
     * */
    switch (Core.Environment.context()) {

        /* LIST */
        case 'report/list':
            ReportResponsive.List.SidePanel();
            ReportResponsive.Page.init();
            //ReportResponsive.DataTable.init();                // Conflicts with legacy scipts in web/assets/js/Report.js
            //ReportResponsive.DataTable.initList();            // Conflicts with legacy scipts in web/assets/js/Report.js
            break;

        case 'report/history':
            // ReportResponsive.DataTable.init();               // Conflicts with legacy scipts in web/assets/js/Report.js
            // ReportResponsive.DataTable.initHistory();        // Conflicts with legacy scipts in web/assets/js/Report.js
            break;
        case 'report/scheduled':
            // ReportResponsive.DataTable.init();               // Conflicts with legacy scipts in web/assets/js/Report.js
            // ReportResponsive.DataTable.initScheduled();      // Conflicts with legacy scipts in web/assets/js/Report.js
            break;
        case 'report/contact':
            // ReportResponsive.DataTable.init();               // Conflicts with legacy scipts in web/assets/js/Report.js
            // ReportResponsive.DataTable.initContact();        // Conflicts with legacy scipts in web/assets/js/Report.js
            break;

    }



    ReportResponsive.isLoaded();


});

var ReportResponsive = {};

jQuery.extend(ReportResponsive, {

    isLoaded: function() {

        console.log('Responsive Report JS Loaded');
    },

    DataTable: {

        init: function() {

            /* Initialize Bootstrap Datatables Integration */
            webApp.datatables();


        },

        initList: function() {

            /* Initialize Datatables */
            $('#report-list-example-datatable').dataTable({
                //"aoColumnDefs": [{"bSortable": false, "aTargets": [4]}],
                "iDisplayLength": 10,
                "aLengthMenu": [[15, 30, 50, -1], [15, 30, 50, "All"]]
            });

            /* Add classes to select and input */
            $('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
            $('.dataTables_length select').addClass('form-control');
        },

        initHistory: function() {


            /* Initialize Datatables */
            $('#report-history-example-datatable').dataTable({
                //"aoColumnDefs": [{"bSortable": false, "aTargets": [4]}],
                "iDisplayLength": 10,
                "aLengthMenu": [[15, 30, 50, -1], [15, 30, 50, "All"]]
            });
            
            /* Add classes to select and input */
            $('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
            $('.dataTables_length select').addClass('form-control');
        },

        initScheduled: function() {


            /* Initialize Datatables */
            $('#report-scheduled-example-datatable').dataTable({
                //"aoColumnDefs": [{"bSortable": false, "aTargets": [4]}],
                "iDisplayLength": 10,
                "aLengthMenu": [[15, 30, 50, -1], [15, 30, 50, "All"]]
            });
            
            /* Add classes to select and input */
            $('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
            $('.dataTables_length select').addClass('form-control');
        },

        initContact: function() {


            /* Initialize Datatables */
            $('#report-contact-example-datatable').dataTable({
                //"aoColumnDefs": [{"bSortable": false, "aTargets": [4]}],
                "iDisplayLength": 10,
                "aLengthMenu": [[15, 30, 50, -1], [15, 30, 50, "All"]]
            });
            
            /* Add classes to select and input */
            $('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
            $('.dataTables_length select').addClass('form-control');
        },


        search: function() {


        }
    },

    Page: {

        init: function() {

            $('#page-container').closest('.wrap').height(parseInt(screen.availHeight-150)+'px');
            $('#page-container').height('100%');

            var offset = $('#secondary-sidebar-scroll').offset();
            $('#secondary-sidebar-scroll').height(parseInt(screen.availHeight-offset.top-100)+'px');

        }

    },

    List: {

        SidePanel: function() {

            $('ul.list-group li.list-group-item').hover(function() {
                $( this ).find('div').hide();
            }, function() {
                $( this ).find('div').show();
            });

            $('ul.sidebar-btn-group li').click(function() {
                $(this).closest('div').find('.sidebar-btn-group-box').val($(this).attr('id'));
                $(this).closest('div').find('.sidebar-btn-group-box').text($(this).find('a').text());
                
                switch($(this).closest('div').find('.sidebar-btn-group-box').attr('id')){
                
                    case    'report-vehicle-mode' : $('#div-report-vehicle').hide();
                                                    $('#div-report-vehicle-group').hide();
                                                    switch($('#report-vehicle-mode').attr('value')){
                                                        case         'group' :  $('#div-report-vehicle-group').show();
                                                                                break;
                                                        case        'single' :  $('#div-report-vehicle').show();
                                                                                break;
                                                    }

                    case   'report-landmark-mode' : $('#div-report-landmark').hide();
                                                    $('#div-report-landmark-group').hide();
                                                    switch($('#report-landmark-mode').attr('value')){
                                                        case         'group' :  $('#div-report-landmark-group').show();
                                                                                break;
                                                        case        'single' :  $('#div-report-landmark').show();
                                                                                break;
                                                    }
                }
            });

            $('ul.sub-panel-items li').click(function() {
                $('#select-report-type').val($(this).attr('id'));
                $('#select-report-type').text($(this).find('a').text());
                ReportResponsive.List.TogglePanel($('#select-report-type').val(),$('#select-report-type').text(),'');
            });

        },

        TogglePanel: function(v,t,i) {

                $('#div-report-alert-type').hide();
                $('#div-report-date-range').hide();
                $('#div-report-landmark-mode').hide();
                $('#div-report-landmark-group').hide();
                $('#div-report-landmark-mode').hide();
                $('#div-report-not-reported-in').hide();
                $('#div-report-speed-filter').hide();
                $('#div-report-stationary-filter').hide();
                $('#div-report-stop-threshold').hide();
                $('#div-report-total-miles-filter').hide();
                $('#div-report-users').hide();
                $('#div-report-vehicle').hide();
                $('#div-report-vehicle-group').hide();
                $('#div-report-vehicle-mode').hide();
                $('#div-report-verification').hide();

                if(i<0){
                } else {
                    i=1;
                }
                $('#report-name').val(t+' Report '+i);

            switch(t){

                case    'Address Verification' :    $('#div-report-vehicle-mode').show();
                                                    $('#div-report-verification').show();
                                                    break;

                case                   'Alert' :    $('#div-report-vehicle-mode').show();
                                                    $('#div-report-date-range').show();
                                                    $('#div-report-alert-type').show();
                                                    break;

                case          'Detailed Event' :    $('#div-report-vehicle').show();
                                                    $('#div-report-date-range').show();
                                                    break;

                case          'Frequent Stops' :    $('#div-report-vehicle').show();
                                                    $('#div-report-date-range').show();
                                                    $('#div-report-stop-threshold').show();
                                                    break;

                case                'Landmark' :    $('#div-report-vehicle-mode').show();
                                                    $('#div-report-date-range').show();
                                                    $('#div-report-landmark-mode').show();
                                                    break;

                case          'Last Ten Stops' :    $('#div-report-vehicle').show();
                                                    break;

                case         'Mileage Summary' :    $('#div-report-vehicle-mode').show();
                                                    $('#div-report-total-miles-filter').show();
                                                    break;

                case           'Non Reporting' :    $('#div-report-vehicle-mode').show();
                                                    $('#div-report-not-reported-in').show();
                                                    break;

                case           'Speed Summary' :    $('#div-report-vehicle-mode').show();
                                                    $('#div-report-date-range').show();
                                                    $('#div-report-speed-filter').show();
                                                    break;

                case 'Starter Disable Summary' :    $('#div-report-vehicle-mode').show();
                                                    break;

                case              'Stationary' :    $('#div-report-vehicle-mode').show();
                                                    $('#div-report-stationary-filter').show();
                                                    break;

                case                    'Stop' :    $('#div-report-vehicle').show();
                                                    $('#div-report-date-range').show();
                                                    $('#div-report-stop-threshold').show();
                                                    break;

                case            'User Command' :    $('#div-report-date-range').show();
                                                    $('#div-report-users').show();
                                                    break;

                case     'Vehicle Information' :    $('#div-report-date-range').show();
                                                    break;

                                    default :   console.log('TogglePanel: '+v+'/'+t+'/'+i);

            }

        }

    }

});
