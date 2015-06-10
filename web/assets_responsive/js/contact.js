
$(document).ready(function() {

    $('#contactTabs a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');      
    });

    /**
     *
     * Page Specific Functionality
     *
     * */
    switch (Core.Environment.context()) {

        case            'alert/contact' :
        case           'report/contact' :   // ContactResponsive.DataTable.init();
                                            // ContactResponsive.DataTable.initContactList();
                                            // ContactResponsive.DataTable.initContactGroup();
                                            Core.DataTable.pagedReport('contacts-contacts-table','',1);
                                            Core.DataTable.pagedReport('contacts-groups-table','',1);
                                            break;

    }

    ContactResponsive.isLoaded();

});

var ContactResponsive = {};

jQuery.extend(ContactResponsive, {

    isLoaded: function() {
        console.log('Responsive Contact JS Loaded');
    },

    DataTable: {

        init: function() {

            /* Initialize Bootstrap Datatables Integration */
            webApp.datatables();


        },

        initContactList: function() {

            /* Initialize Datatables */
            $('#contact-list-example-datatable').dataTable({
                //"aoColumnDefs": [{"bSortable": false, "aTargets": [4]}],
                "iDisplayLength": 10,
                "aLengthMenu": [[15, 30, 50, -1], [15, 30, 50, "All"]]
            });

            /* Add classes to select and input */
            $('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
            $('.dataTables_length select').addClass('form-control');
        },

        initContactGroup: function() {


            /* Initialize Datatables */
            $('#contact-group-example-datatable').dataTable({
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
