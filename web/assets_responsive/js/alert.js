
$(document).ready(function() {

    /**
     *
     * Page Specific Functionality
     *
     * */
    switch (Core.Environment.context()) {

        /* LIST */
        case 'alert/list':
            // AlertResponsive.DataTable.init();
            // AlertResponsive.DataTable.initList();
            Core.DataTable.pagedReport('alert-list-table');
            break;

        case 'alert/history':
            // AlertResponsive.DataTable.init();
            // AlertResponsive.DataTable.initHistory();
            Core.DataTable.pagedReport('alert-history-table');
            break;

        case 'alert/contact':
            // AlertResponsive.DataTable.init();
            // AlertResponsive.DataTable.initContact();
            Core.DataTable.pagedReport('contacts-contacts-table','',1);
            Core.DataTable.pagedReport('contacts-groups-table','',1);
            break;

    }

    AlertResponsive.isLoaded();

});

var AlertResponsive = {};

jQuery.extend(AlertResponsive, {

    isLoaded: function() {
        console.log('Responsive Alert JS Loaded');
    },

    DataTable: {

        init: function() {

            /* Initialize Bootstrap Datatables Integration */
            webApp.datatables();

        },

        initList: function() {

            /* Initialize Datatables */
            $('#alert-list-example-datatable').dataTable({
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
            $('#alert-history-example-datatable').dataTable({
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
            $('#alert-contact-example-datatable').dataTable({
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
