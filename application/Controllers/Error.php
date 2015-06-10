<?php

//use Zend\Permissions\Acl\Role\RoleInterface;
namespace Controllers;

/**
 * Class Error
 *
 */
class Error extends BasePage
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }
     
    /**
     *
     */
    public function index()
    {
    	$this->pagenotfound();
    }

    /**
     *
     */
    public function pagenotfound() {

        $view_data = array(
            'error_title' => '404 - Page Not Found',
            'error_text'  => 'The page you have requested was not found.'
        );

        $this->render("page/error/error.html.twig", $view_data);
    }

    /**
     *
     */
    public function internalservererror() {

        $view_data = array(
            'error_title' =>   '500 - Internal Server Error',
            'error_text'  => 'Opps, an error was encountered. We apologise for the inconvenience'
        );

        $this->render("page/error/error.html.twig", $view_data);
    }


}