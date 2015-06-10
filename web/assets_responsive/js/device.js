
$(document).ready(function() {

    /**
     *
     * Page Specific Functionality
     *
     * */
    switch (Core.Environment.context()) {

        /* LIST */
        case 'admin/list':
        case 'device/list':
            // DeviceResponsive.DataTable.init();
            // DeviceResponsive.DataTable.initList();
            Core.DataTable.pagedReport('device-list-table');
            break;

        /* LIST */
        case 'admin/export':
        case 'device/export':
            $('#btn-devices-importing').trigger('click');
            break;

    }

    DeviceResponsive.isLoaded();

});

var DeviceResponsive = {};

jQuery.extend(DeviceResponsive, {

    isLoaded: function() {
        console.log('Responsive Device JS Loaded');
    },

    DataTable: {

        init: function() {

            /* Initialize Bootstrap Datatables Integration */
            webApp.datatables();

        },

        initList: function() {

            /* Initialize Datatables */
            $('#device-list-example-datatable').dataTable({
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
