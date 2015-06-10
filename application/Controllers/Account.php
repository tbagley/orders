<?php
 
namespace Controllers;

use Models\Data\ContactData;
use Models\Logic\ContactLogic;
use GTC\Component\Utils\CSV\CSVBuilder;
use Models\Data\UserData;
use Models\Logic\UserLogic;
use Models\Data\VehicleData;
use Models\Logic\VehicleLogic;

/**
 * Class Account
 *
 * Controller for Account Actions
 *
 */
class Account extends BasePage
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

        // the login route in this controller does not require this stuff
        if ($this->route_data['controller'] !== 'login') {

            $this->contact_logic = new ContactLogic;
            $this->user_data = new UserData;
        } else {
            $this->vehicle_logic = new VehicleLogic;
        }

    }

    /**
     *
     */
    public function index()
    {
        $this->login();
    }

    /**
     * demo login as user
     *
     * @route demo /demo/{sudoStr}
     */
    public function demo($sudoStr)
    {
        $user = $this->user_data->getUserBySudo($sudoStr);
		
        $error = array();
            
        if (empty($user)) {
            $error = array(
                'error' => array(
                    'message' => 'Invalid Token'
                )
            );
        } else {

            $this->user_data->updateUserInfo($user[0]['user_id'], array(
                'sudo' => null
            ));

            $login_attempt = $this->validateLogin(array(
                '_username' => $user[0]['username']
                ,'_password' => $user[0]['password']
            ), /* $isEncoded */ true);

            if(true === $login_attempt) {
                header("Location: /vehicle/demo");
            } else {
                // get errors
                $error = $login_attempt;
            }
        }
    }

    /**
     * Primary login route for entire application
     *
     * @route login /login
     */
    public function legal()
    {
        $user_id = $this->user_session->getUserId();

        $post = $this->request->request->all();

        $legal = $this->user_logic->setLegalId($user_id,$post['_legal-id']);

        $view_data = array();

        header("Location: /vehicle/map");
        exit();

    }

    /**
     * Primary login route for entire application
     *
     * @route login /login
     */
    public function login()
    {
        // $this-user_data->masterResetPassword('ownerone','password');

    	$error = array();
        
        $post = $this->request->request->all();

        /**
         * if _username is submitted, this is a login attempt
         */
        if (isset($post['_username'])) {

            $login_attempt = $this->validateLogin($post);

            if(true === $login_attempt) {
                $result = $this->vehicle_logic->codebaseMetrics('Controlers/Account.php::login',$post['_username'],'login',$_SERVER);
                $this->landingAction($post);
            } else {
                // get errors
                $error = $login_attempt;
            }

        }

        // useful if we need to disable login form
        $login_restriction = null;

        // check if user is already logged in
        $token = $this->session->get('_security_' . 'secured_area');

        if (null !== $token) {
            // show login template, but display 'already logged in' message
            $login_restriction = 'already_logged_in';
        }

        $view_data = array();

        $view_data['login_restriction'] = $login_restriction;

        if (! empty($error)) {
            // obfuscate any database error message
            if (isset($error['error']) AND isset($error['error']['message']) AND (preg_match('/(SQLSTATE|MySQL)/', $error['error']['message']) === 1)) {   
                $error['error']['message'] = 'Unknown Database Error';
            }
            
            $view_data = array_merge($view_data, $error);
        }

        $url=$this->user_logic->getURL($_SERVER['HTTP_HOST']);
        
        if(!empty($url))
        {
	        $view_data['logo']=$url['logo'];
	        $view_data['browser_title']=$url['title'];
            $view_data['apple_touch_icon'] = $url['apple_touch_icon'];
            if(!($view_data['apple_touch_icon'])){
                $view_data['apple_touch_icon'] = 'apple-touch-114-whitelabel.png';
            }
        }

        $this->render("page/account/login.html.twig", $view_data);
    }

    /**
     * Registration
     *
     * @route registration /registration/{token}
     */
    public function registration($token)
    {
        $post = $this->request->request->all();

        $view_data = array();
        
        $url=$this->user_logic->getURL($_SERVER['HTTP_HOST']);
        
        if(!empty($url))
        {
	        $view_data['logo']=$url['logo'];
	        $view_data['browser_title']=$url['title'];
            $view_data['apple_touch_icon'] = $url['apple_touch_icon'];
            if(!($view_data['apple_touch_icon'])){
                $view_data['apple_touch_icon'] = 'apple-touch-114-whitelabel.png';
            }
        }
        
        /**
         * if _username is submitted, this is a login attempt after a successful user registration
         * (need to call login() since the user's current url is still the one to the registration page)
         */
        if (isset($post['_username'])) {
            $this->login();
            exit;
        }

        if (isset($post['username']) AND isset($post['password'])) {  // if the username & the password are set, validate registration info
            $params = $errors = array();

            if (! empty($post['user-id'])) {
                $user_id = $post['user-id'];
            } else {
                $errors[] = 'Invalid user id';
            }

            // get SMS Number and Carrier if provided by user (optional)
            if (! empty($post['user-sms'])) {
                $last_sms = $params['cellnumber'] = $post['user-sms'];
            }

            if (! empty($post['sms-carrier'])) {
                $last_sms_carrier = $params['cellcarrier_id'] = $post['sms-carrier'];
            }

            if (! empty($post['user-timezone'])) {
                $params['timezone_id'] = $post['user-timezone'];
            } else {
                $errors[] = 'Please select a Timezone';
            }

            if (! empty($post['username'])) {
                $params['username'] = $post['username'];
            } else {
                $errors[] = 'Username field cannot be empty';
            }

            if (! empty($post['password'])) {
                $params['password'] = $post['password'];
            } else {
                $errors[] = 'Password field cannot be empty';
            }

            if (empty($post['user-term-agreement']) OR $post['user-term-agreement'] !== 'on') {
                $errors[] = 'You have to agree to the Terms and Conditions of Use';
            }

            if (empty($errors)) {
                $updated = $this->user_logic->validateNewUser($user_id, $token, $params);
                if ($updated !== false) {

                    // success - user has been activated, display login page with success message
                    $view_data['success']['message'] = 'User registration completed. Please login with your Username and Password.';
                    $this->render('page/account/login.html.twig', $view_data);
                } else {
                    // failed - display registration page with error message(s)
                    $errors = $this->user_logic->getErrorMessage();
                    if (empty($errors)) {
                        $errors[] = 'An unknown error occurred';
                    }

                    $view_data['error']['message'] = $errors;

                    if (! empty($params)) {
                        $params['user_id'] = $user_id;
                        $params['firstname'] = (! empty($post['user-first-name'])) ? $post['user-first-name'] : '';
                        $params['lastname'] = (! empty($post['user-last-name'])) ? $post['user-last-name'] : '';
                        $params['email'] = (! empty($post['user-email'])) ? $post['user-email'] : '';
                        $view_data['userdata'] = $params;

                        if (! empty($last_sms)) {
                            $view_data['last_sms'] = $last_sms;
                        }

                        if (! empty($last_sms_carrier)) {
                            $view_data['last_sms_carrier'] = $last_sms_carrier;
                        }
                    }

                    $view_data['timezones'] = $this->user_logic->getTimezones();
                    $view_data['sms_carriers'] = $this->contact_logic->getSMSCarrierOptions();

                    $this->render('page/account/registration.html.twig', $view_data);
                }
            } else {

                $view_data['error']['message'] = $errors;

                if (! empty($params)) {
                    $params['user_id'] = $user_id;
                    $params['firstname'] = (! empty($post['user-first-name'])) ? $post['user-first-name'] : '';
                    $params['lastname'] = (! empty($post['user-last-name'])) ? $post['user-last-name'] : '';
                    $params['email'] = (! empty($post['user-email'])) ? $post['user-email'] : '';
                    $view_data['userdata'] = $params;

                    if (! empty($last_sms)) {
                        $view_data['last_sms'] = $last_sms;
                    }

                    if (! empty($last_sms_carrier)) {
                        $view_data['last_sms_carrier'] = $last_sms_carrier;
                    }
                }

                $view_data['timezones'] = $this->user_logic->getTimezones();
                $view_data['sms_carriers'] = $this->contact_logic->getSMSCarrierOptions();
                $this->render('page/account/registration.html.twig', $view_data);
            }

        } else if (! empty($token)) {                         // else validate token and redirect them back to the appropriate page

            $userdata = $this->user_logic->getUserByInvitationToken($token);

            if ($userdata !== false) {
                if (! empty($userdata)) {
                    $view_data['userdata'] = $userdata;
                    $view_data['timezones'] = $this->user_logic->getTimezones();
                    $view_data['sms_carriers'] = $this->contact_logic->getSMSCarrierOptions();
                    $this->render('page/account/registration.html.twig', $view_data);
                } else {
                    $view_data['error']['message'] = 'Could not retrieve user data using this token';
                    $this->render('page/account/login.html.twig', $view_data);
                }
            } else {
                // get error message
                $errors = array_pop($this->user_logic->getErrorMessage());

                if (empty($errors)) {
                    $errors = 'An unknown error occurred';
                }

                $view_data['error']['message'] = $errors;
                $this->render('page/account/login.html.twig', $view_data);
            }
        } else {
            $view_data['error']['message'] = 'Invalid user registration token';
            $this->render('page/account/login.html.twig', $view_data);
        }
    }

    /**
     * Forgot Username
     *
     * @route forgotusername /forgotusername
     */
    public function forgotusername()
    {
        $error = array();
        
        $post = $this->request->request->all();

        /**
         * if _email is submitted, this is a forgotusername attempt
         */
        if (isset($post['_email'])) {

            $user = $this->user_logic->getUserByEmail($post['_email']);

            if($user) {
            	if($this->user_logic->resendUserLoginInfo($user[0]['user_id'], $user[0]['email'], $user[0]['username'], $user[0]['fullname']))
            	{
                	$error['success'] = array('message'=>'Your username has been sent to your email address.');
                }
            } else {
                // get errors
                $error['error'] = array('message'=>'The email address you entered could not be found, please try again.');
            }
        }

        // useful if we need to disable login form
        $login_restriction = null;

        // check if user is already logged in
        $token = $this->session->get('_security_' . 'secured_area');

        if (null !== $token) {
            // show login template, but display 'already logged in' message
            $login_restriction = 'already_logged_in';
        }

        $view_data = array();

        $view_data['login_restriction'] = $login_restriction;

        if (! empty($error)) {
            // obfuscate any database error message
            if (isset($error['error']) AND isset($error['error']['message']) AND (preg_match('/(SQLSTATE|MySQL)/', $error['error']['message']) === 1)) {   
                $error['error']['message'] = 'Unknown Database Error';
            }
            
            $view_data = array_merge($view_data, $error);
        }

        $url=$this->user_logic->getURL($_SERVER['HTTP_HOST']);
        
        if(!empty($url))
        {
	        $view_data['logo']=$url['logo'];
	        $view_data['browser_title']=$url['title'];
            $view_data['apple_touch_icon'] = $url['apple_touch_icon'];
            if(!($view_data['apple_touch_icon'])){
                $view_data['apple_touch_icon'] = 'apple-touch-114-whitelabel.png';
            }
        }
        
        $this->render("page/account/forgotusername.html.twig", $view_data);
    }

    /**
     * Forgot Password
     *
     * @route forgotpassword /forgotpassword
     */
    public function forgotpassword()
    {
        $error = array();
        
        $post = $this->request->request->all();

        /**
         * if _username is submitted, this is a forgotpassword attempt
         */
        if (isset($post['_username'])) {

            $user = $this->user_logic->getUserByUsername($post['_username']);

            if($user) {
            	if($this->user_logic->sendTokenAccess($user[0]['user_id'], $user[0]['email'], $user[0]['fullname']))
            	{
                	$error['success'] = array('message'=>'Please check your Inbox / Spam Filter for a link to change your password.');
                }
            } else {
                // get errors
                $error['error'] = array('message'=>'The username you entered could not be found, please try again.');
            }
        }

        // useful if we need to disable login form
        $login_restriction = null;

        // check if user is already logged in
        $token = $this->session->get('_security_' . 'secured_area');

        if (null !== $token) {
            // show login template, but display 'already logged in' message
            $login_restriction = 'already_logged_in';
        }

        $view_data = array();

        $view_data['login_restriction'] = $login_restriction;

        if (! empty($error)) {
            // obfuscate any database error message
            if (isset($error['error']) AND isset($error['error']['message']) AND (preg_match('/(SQLSTATE|MySQL)/', $error['error']['message']) === 1)) {   
                $error['error']['message'] = 'Unknown Database Error';
            }
            
            $view_data = array_merge($view_data, $error);
        }

        $url=$this->user_logic->getURL($_SERVER['HTTP_HOST']);
        
        if(!empty($url))
        {
	        $view_data['logo']=$url['logo'];
	        $view_data['browser_title']=$url['title'];
            $view_data['apple_touch_icon'] = $url['apple_touch_icon'];
            if(!($view_data['apple_touch_icon'])){
                $view_data['apple_touch_icon'] = 'apple-touch-114-whitelabel.png';
            }
        }
        
        $this->render("page/account/forgotpassword.html.twig", $view_data);
    }

    /**
     * Change Password
     *
     * @route changepassword /changepassword/{token}
     */
    public function changepassword($token)
    {
        $error = array();
        
        $post = $this->request->request->all();
        $user_id=$this->user_logic->getToken($token);
        
		if($user_id)
		{
			$page="page/account/changepassword.html.twig";
			
			/**
	         * if password is submitted, this is a changepassword attempt
	         */
	        if (isset($post['password'])) {
				
				$params=array(
					"password" => $post['password']
				);
	            
	            $user = $this->user_logic->updateUserInfo($user_id, $params);
	
	            if($user) {
	            	$error['success'] = array('message'=>'Your password has been changed successfully.');
	                $this->user_data->disableTempToken($token);
	            } else {
	                // get errors
	                $errors = $this->user_logic->getErrorMessage();
	                if(count($errors)==0)
	                {
		                $error_message='There was an error changing your password, please try again.';
	                }
	                else
	                {
		                $error_message=$errors[0];
	                }
	                $error['error'] = array('message'=>$error_message);
	            }
	        }
		}
		else
		{
	        header('Location: /login');
		}

        // useful if we need to disable login form
        $login_restriction = null;

        // check if user is already logged in
        $token = $this->session->get('_security_' . 'secured_area');

        if (null !== $token) {
            // show login template, but display 'already logged in' message
            $login_restriction = 'already_logged_in';
        }

        $view_data = array();

        $view_data['login_restriction'] = $login_restriction;

        if (! empty($error)) {
            // obfuscate any database error message
            if (isset($error['error']) AND isset($error['error']['message']) AND (preg_match('/(SQLSTATE|MySQL)/', $error['error']['message']) === 1)) {   
                $error['error']['message'] = 'Unknown Database Error';
            }
            
            $view_data = array_merge($view_data, $error);
        }

        $url=$this->user_logic->getURL($_SERVER['HTTP_HOST']);
        
        if(!empty($url))
        {
	        $view_data['logo']=$url['logo'];
	        $view_data['browser_title']=$url['title'];
            $view_data['apple_touch_icon'] = $url['apple_touch_icon'];
            if(!($view_data['apple_touch_icon'])){
                $view_data['apple_touch_icon'] = 'apple-touch-114-whitelabel.png';
            }
        }
        
        $this->render($page, $view_data);
	}

    /**
     * um login as user
     *
     * @route sudo /sudo/{sudoStr}
     */
    public function sudo($sudoStr)
    {
        $user = $this->user_data->getUserBySudo($sudoStr);

        $error = array();
            
        if (empty($user)) {
            $error = array(
                'error' => array(
                    'message' => 'Invalid Token'
                )
            );
        } else {

            $this->user_data->updateUserInfo($user[0]['user_id'], array(
                'sudo' => null
            ));

            $login_attempt = $this->validateLogin(array(
                '_username' => $user[0]['username']
                ,'_password' => $user[0]['password']
            ), /* $isEncoded */ true);

            if(true === $login_attempt) {
                $this->landingAction();
            } else {
                // get errors
                $error = $login_attempt;
            }
        }

        // useful if we need to disable login form
        $login_restriction = null;

        // check if user is already logged in
        $token = $this->session->get('_security_' . 'secured_area');

        if (null !== $token) {
            // show login template, but display 'already logged in' message
            $login_restriction = 'already_logged_in';
        }

        $view_data = array();

        $view_data['login_restriction'] = $login_restriction;

        if (! empty($error)) {
            // obfuscate any database error message
            if (isset($error['error']) AND isset($error['error']['message']) AND (preg_match('/(SQLSTATE|MySQL)/', $error['error']['message']) === 1)) {   
                $error['error']['message'] = 'Unknown Database Error';
            }
            
            $view_data = array_merge($view_data, $error);
        }

        $url=$this->user_logic->getURL($_SERVER['HTTP_HOST']);
        
        if(!empty($url))
        {
	        $view_data['logo']=$url['logo'];
	        $view_data['browser_title']=$url['title'];
            $view_data['apple_touch_icon'] = $url['apple_touch_icon'];
            if(!($view_data['apple_touch_icon'])){
                $view_data['apple_touch_icon'] = 'apple-touch-114-whitelabel.png';
            }
        }
        
        $this->render("page/account/login.html.twig", $view_data);
    }
}