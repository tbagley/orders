<?php

namespace Controllers\Ajax;

/**
 * Class Utility
 *
 * For handling asynchronous utility calls i.g. heartbeat
 *
 */
class Utility extends BaseAjax
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

    }

    public function heartbeat()
    {

        // Base Controller will extend the session or take over the request and issue a code 2 if the session has expired

        $ajax_data = array(
            'code'             => 0,
            'message'          => 'session time extended',
            'validation_error' => array(),
            'data'             => array()
        );

        $this->ajax_respond($ajax_data);
    }

    /**
     * Fake a Code 2 (Session Timeout)
     *
     * !! NOT TO BE USED IN PRODUCTION CODE !!
     *
     * useful when trying to debug session timeout issues
     *
     * example use in javascript console:
     *
     *  $.ajax({
            url: '/ajax/utility/fakeTimeout'
     *  });
     *
     * The code 2 status is picked up by jQuery.ajaxPrefilter() and will redirect to login screen
     *
     */
    public function fakeTimeout() {
        $last_route = $this->request->server->get('HTTP_REFERER', $this->route_data['route']);

        $ajax_data = array(
            "code" => 2,
            "data" => array("last_route"=> $last_route),
            "message" => array(),
            "validation_error" => array("Your session has expired")
        );

        $this->ajax_respond($ajax_data);
        exit();
    }


}