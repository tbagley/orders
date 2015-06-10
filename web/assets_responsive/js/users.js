
$(document).ready(function() {

    /**
     *
     * Page Specific Functionality
     *
     * */
    switch (Core.Environment.context()) {

        /* LIST */
        case 'admin/users':
        case 'users/list':
            UsersResponsive.DataTable.init();
            UsersResponsive.DataTable.initList();
            Core.DataTable.pagedReport('users-users-table');
            break;

        case 'admin/usertypes':
        case 'users/type':
            UsersResponsive.DataTable.init();
            UsersResponsive.DataTable.initUserType();
            Core.DataTable.pagedReport('users-type-table');
            break;
    }

    UsersResponsive.isLoaded();

});

var UsersResponsive = {};

jQuery.extend(UsersResponsive, {

    isLoaded: function() {

        console.log('Responsive Users JS Loaded');
    },

    DataTable: {

        init: function() {

            /* Initialize Bootstrap Datatables Integration */
            webApp.datatables();


        },

        initList: function() {

            /* Initialize Datatables */
            $('#user-list-example-datatable').dataTable({
                //"aoColumnDefs": [{"bSortable": false, "aTargets": [4]}],
                "iDisplayLength": 10,
                "aLengthMenu": [[15, 30, 50, -1], [15, 30, 50, "All"]]
            });

            /* Add classes to select and input */
            $('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
            $('.dataTables_length select').addClass('form-control');
        },

        initUserType: function() {


            /* Initialize Datatables */
            $('#user-type-example-datatable').dataTable({
                //"aoColumnDefs": [{"bSortable": false, "aTargets": [4]}],
                "iDisplayLength": 10,
                "aLengthMenu": [[15, 30, 50, -1], [15, 30, 50, "All"]]
            });
            
            /* Add classes to select and input */
            $('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
            $('.dataTables_length select').addClass('form-control');
        },


    },


    

});
