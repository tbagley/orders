
$(document).ready(function() {

    /**
     *
     * Page Specific Functionality
     *
     * */
    switch (Core.Environment.context()) {

        /* MAP */
        case             'landmark/map' :   break;

        /* LIST */
        case            'landmark/list' :   // LandmarkResponsive.DataTable.init();
                                            // LandmarkResponsive.DataTable.initList();
                                            Core.DataTable.pagedReport('landmark-list-table');
                                            break;

        case      'landmark/incomplete' :   // LandmarkResponsive.DataTable.init();
                                            // LandmarkResponsive.DataTable.initList();
                                            Core.DataTable.pagedReport('landmark-incomplete-table');
                                            break;

        case           'landmark/group' :   // LandmarkResponsive.DataTable.init();
                                            // LandmarkResponsive.DataTable.initList();
                                            Core.DataTable.pagedReport('landmark-group-table');
                                            break;

        case    'landmark/verification' :   // LandmarkResponsive.DataTable.init();
                                            // LandmarkResponsive.DataTable.initList();
                                            Core.DataTable.pagedReport('landmark-verification-table');
                                            break;

    }

    LandmarkResponsive.isLoaded();

});

var LandmarkResponsive = {};

jQuery.extend(LandmarkResponsive, {

    isLoaded: function() {
        console.log('Responsive Landmark JS Loaded');
    },

    DataTable: {

        init: function() {

            /* Initialize Bootstrap Datatables Integration */
            webApp.datatables();

        },

        initList: function() {

            /* Initialize Datatables */
            $('#landmark-list-example-datatable').dataTable({
                //"aoColumnDefs": [{"bSortable": false, "aTargets": [4]}],
                "iDisplayLength": 10,
                "aLengthMenu": [[15, 30, 50, -1], [15, 30, 50, "All"]]
            });

            /* Add classes to select and input */
            $('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
            $('.dataTables_length select').addClass('form-control');

        },

        initIncomplete: function() {


            /* Initialize Datatables */
            $('#landmark-incomplete-example-datatable').dataTable({
                //"aoColumnDefs": [{"bSortable": false, "aTargets": [4]}],
                "iDisplayLength": 10,
                "aLengthMenu": [[15, 30, 50, -1], [15, 30, 50, "All"]]
            });
            
            /* Add classes to select and input */
            $('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
            $('.dataTables_length select').addClass('form-control');
        },

        initGroup: function() {


            /* Initialize Datatables */
            $('#landmark-group-example-datatable').dataTable({
                //"aoColumnDefs": [{"bSortable": false, "aTargets": [4]}],
                "iDisplayLength": 10,
                "aLengthMenu": [[15, 30, 50, -1], [15, 30, 50, "All"]]
            });
            
            /* Add classes to select and input */
            $('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
            $('.dataTables_length select').addClass('form-control');
        },

        initVerification: function() {


            /* Initialize Datatables */
            $('#landmark-verification-example-datatable').dataTable({
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


    

});
