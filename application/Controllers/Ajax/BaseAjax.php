<?php

namespace Controllers\Ajax;

use GTC\Component\Form\Validation;

use Controllers\Base;

class BaseAjax extends Base
{
    /**
     * AJAX return data
     *
     * @var array
     */
    protected $ajax_data = array(
        'code'				=> 1,		// > 0 means FAIL, 0 means SUCCESS
        'data'				=> array(),
        'message' 			=> '',
        'validation_error'	=> array()
    );

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->load_db('slave');
        $this->load_db('master');
    }
    
    /**
     * Overloaded method used to deny request (session timed out)
     */
    protected function denyRequest()
    {
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

    /**
     * @param $ajax_data
     */
    protected function ajax_respond($ajax_data)
    {
        if (FALSE === isset($ajax_data['code']) OR FALSE === isset($ajax_data['message'])) {
            echo json_encode(array("code"=>1, "message"=>"Missing Code or Message"));
            die();
        }

        $ajax_data = array_merge($this->ajax_data, $ajax_data);

        if (empty($ajax_data)) {
            echo json_encode(array("code"=>1, "message"=>"object is invalid"));
            die();
        }

        echo json_encode($ajax_data);
        exit;
    }

    /**
     *
     * Ajax Page views returns should call this method when ready to present the page
     *
     * @param $template
     * @param $view_data
     */
    protected function ajax_render($template, $view_data)
    {
        // this ajax render uses Twig
        $this->load_twig();

        // pass the logged in user to the view
        $view_data['user'] = $this->user_session;

        // pass the controller & route vars to the view
        // this could be cleaned up as route and context are redundant
        $view_data['controller'] = $this->route_data['controller'];
        $view_data['method'] = $this->route_data['method'];
        $view_data['route'] = $this->route_data['route'];
        $view_data['context'] = $this->route_data['route'];

        // pass the access data to the view
        $view_data['environment'] = md5(ENVIRONMENT);

        // pass this to javascript
        $view_data['session_timeout'] = $this->session_timeout;

        // pass access data to view
        $view_data['access'] = (NULL !== $this->user_session) ? $this->user_session->getAccess() : array();

        $validation = new Validation;
        // add field types array to view for additional client-side validation
        $view_data['validation'] = $validation->getFieldTypes();

        // echo out the response
        echo $this->twig->render($template, $view_data);
        exit;
    }
}
