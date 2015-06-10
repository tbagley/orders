<?php
 
namespace Controllers;

use Models\Data\ContactData;
use Models\Logic\ContactLogic;
use GTC\Component\Utils\CSV\CSVBuilder;
use Models\Data\UserData;
use Models\Logic\UserLogic;
use GTC\Component\Form\Validation;

/**
 * Class Mobile
 *
 * Controller for Mobile website
 *
 */
class Mobile extends BasePage
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        array_push($this->css_files, 'account');
        array_push($this->js_files, 'account');
        array_push($this->js_files, '../vendor/jquery/jquery-ui-1.10.3.custom');
        
        $this->load_db('master');
        $this->load_db('slave');

        $this->user_logic = new UserLogic;

    }

    /**
     *
     * @route mobile /mobile
     */
    public function mobile()
    {
        $view_data = array() ;

        $validation = new Validation;
        // add field types array to view for additional client-side validation
        $view_data['validation'] = $validation->getFieldTypes();

        $view_data['environment'] = md5(ENVIRONMENT);
        $view_data['context'] = $this->route_data['route'];
        $view_data['map_api'] = $this->map_api;
        $view_data['decarta_api_key'] = $this->decarta_api_key;
        $view_data['session_timeout'] = $this->session_timeout;
        $view_data['message'] = null;

        $error = array();
        
        $post = $this->request->request->all();

        // /**
        //  * if _username is submitted, this is a login attempt
        //  */
        if (isset($post['_username'])) {

            $login_attempt = $this->validateLogin($post);

            if(true === $login_attempt) {
            } else {
                // get errors
            }

        } else if (isset($post['_logout'])) {

            $this->session->remove('_security_' . 'secured_area');

        } else if (isset($post['_remind_user'])) {

            if (isset($post['_email'])) {

                $user = $this->user_logic->getUserByEmail($post['_email']);

                if($user) {
                    if($this->user_logic->resendUserLoginInfo($user[0]['user_id'], $user[0]['email'], $user[0]['username'], $user[0]['fullname'])) {
                        $view_data['message'] = 'Your username has been sent to your email address.' ;
                    }
                } else {
                    $view_data['message'] = 'The email address you entered could not be found.' ;
                }

            }

        } else if (isset($post['_remind_pswd'])) {

            if (isset($post['__username'])) {

                $user = $this->user_logic->getUserByUsername($post['__username']);

                if($user) {
                    if($this->user_logic->sendTokenAccess($user[0]['user_id'], $user[0]['email'], ucwords($user[0]['firstname'] . ' ' . $user[0]['lastname']) )) {
                        $view_data['message'] = 'Please check your Inbox / Spam Filter for a link to change your password.' ; // . $user[0]['user_id'] .':'. $user[0]['email'] .':'. ucwords($user[0]['firstname'] . ' ' . $user[0]['lastname']) ;
                    }
                } else {
                    $view_data['message'] = 'The username you entered could not be found.' ; 
                }

            }

        }

        $token = $this->session->get('_security_' . 'secured_area');

        if (null !== $token) {
            $view_data['loggedin']=1;
        } else {
            $view_data['loggedin']=0;
        }

        $url=$this->user_logic->getURL($_SERVER['HTTP_HOST']);
        
        if(!empty($url))
        {
            $view_data['logo']=$url['logo'];
            $view_data['browser_title']=$url['title'];
        }

        echo $this->twig->render("page/mobile/mobile.html.twig", $view_data);

        exit();

    }
 
 }