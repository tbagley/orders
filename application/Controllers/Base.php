<?php

namespace Controllers;

use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;

use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;

use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;

use Symfony\Component\Security\Core\SecurityContext;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\ProviderNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

use Twig_Loader_Filesystem;
use Twig_Environment;

use GTC\Framework\DatabaseHelper;
use GTC\Framework\UserProvider;
use GTC\Component\Utils\Twig;
use GTC\Component\Security\PlainPasswordEncoder;

use Models\Logic\UserLogic;

/**
 * Class Base
 *
 * Parent class for all controllers
 *
 */
class Base
{
    // Symfony\Component\HttpFoundation\Request
    protected $request;

    // Symfony\Component\HttpFoundation\Session\Session;
    protected $session;

    // unit is in seconds
    protected $session_timeout;

    // contains info about authorized user
    protected $user_session;

    // contains info about the route segments
    protected $route_data;

    protected $uri_segments = array();

    // default landing/home page
    protected $defaultRoute;

    // if no theme is set in the account record, always default to this
    protected $defaultTheme;

    // Map API (switch to use one map or the other)
    protected $map_api;

    // Twig_Loader_Filesystem
    private $loader;

    // Twig_Environment
    protected $twig;

    /**
     *
     */
    public function __construct()
    {

        $core =& get_instance();

        $this->session_timeout = $core->config['parameters']['session_timeout'];

        $this->defaultRoute = $core->config['parameters']['default_route'];

        $this->defaultTheme = $core->config['parameters']['default_theme'];

        $this->map_api = $core->config['parameters']['map_api'];
        $this->decarta_api_key = $core->config['parameters']['decarta_api_key'];

        $this->request = $core->request;

        // break up the uri
        $this->uri_segments = explode('/', $this->request->getRequestUri());

        /**
         * Populate route_data, used thoughout the application for context
         */
        $this->route_data['controller'] = (isset($this->uri_segments[1])) ? $this->uri_segments[1] : '';
        $this->route_data['method'] = (isset($this->uri_segments[2])) ? $this->uri_segments[2] : '';
        $this->route_data['route'] = $this->route_data['controller']."/".$this->route_data['method'];

        /**
         * The next section handles user authentication
         * See Symfony Security Component implementation for details
         */

        // life span of this session
        ini_set('session.cookie_lifetime', $this->session_timeout); // absolute time

        //ini_set('session.gc_maxlifetime', 600); // idle time (not needed since the session timeout are extended/resetted per request)
        $this->session = new Session();

        $this->session->start();

        // break out if this is a logout request
        if ($this->route_data['controller'] == 'logout') {
            $this->logoutAction();
        } else if ($this->route_data['controller'] == 'orders') {
        } else if ($this->route_data['controller'] == 'repo') {
        } else if ($this->route_data['controller'] == 'warehouse') {
        } else if (empty($_SERVER['HTTPS'])) {
            $i = array('http://','HTTP://');
            $o = array('https://','https://');
            // header('Location: https://' . str_replace( $i , $o , $_SERVER['HTTP_HOST'] ) . $_SERVER['REQUEST_URI']);
            echo '<html>';
            echo '<head>';
            echo '<META http-equiv="refresh" content="1;URL=https://' . str_replace( $i , $o , $_SERVER['HTTP_HOST'] ) . $_SERVER['REQUEST_URI'] .'">';
            echo '</head>';
            echo '<body style="color:#b3b3b3;">';
            // echo 'Redirecting to https://' . str_replace( $i , $o , $_SERVER['HTTP_HOST'] ) . $_SERVER['REQUEST_URI'] . ' (' . date('H:i:s m/d/Y') . ')' ;
            echo 'loading...' ;
            echo '</body>';
            echo '</html>';
            exit();
        }

        /**
         * Allow these routes as public areas, requires no authentication
         *
         * @todo the Symfony Security Component can handle this better with 'firewalls'
         *
         */
        $public_areas = array('api', 'changepassword', 'demo', 'forgotusername', 'forgotpassword', 'fulfillment', 'login', 'mobile', 'orders', 'orderstatus', 'registration', 'repo', 'sudo', 'warehouse');

        // skip login check for $public_areas
        if (!in_array($this->route_data['controller'], $public_areas)) {
            $this->validateUser();
        }

    }

    /**
     * Ran for any non-public page
     */
    protected function validateUser()
    {
        /**
         * First set up all the Symfony Security Component requirements
         */

        $defaultEncoder = new MessageDigestPasswordEncoder('sha512', true, 5000);

        $userProvider = new UserProvider();

        $userChecker = new UserChecker();

        $encoderFactory = new EncoderFactory(array(
            'GTC\Framework\User' => $defaultEncoder,
        ));

        //$anonymousKey = uniqid();

        $authenticationProviders = array(
            // validates AnonymousToken instances
            //new AnonymousAuthenticationProvider($anonymousKey),
            // retrieve the user for a UsernamePasswordToken
            new DaoAuthenticationProvider($userProvider, $userChecker, 'secured_area', $encoderFactory)
        );

        $authenticationManager = new AuthenticationProviderManager($authenticationProviders);

        $voters = array(
            // votes if any attribute starts with a given prefix
            new RoleVoter('ROLE_')
        );

        $accessDecisionManager = new AccessDecisionManager($voters);

        $securityContext = new SecurityContext($authenticationManager, $accessDecisionManager);

        $token = $this->session->get('_security_' . 'secured_area');

        if (null === $this->session || null === $token) {

            $securityContext->setToken(null);

            /* child class should overload this method as needed, since each child respond differently */
            $this->denyRequest();
            //$this->session->clear();

        } else {

            $token = unserialize($token);

            $user = $token->getUser();

            //$token->setUser($userProvider->refreshUser($user));

            $securityContext->setToken($token);

            $this->user_session = $user;
        }

        /**
         * Configure the users access to domain objects
         */
        $this->access =  $this->user_session->getAccess();

        // extend/reset session timeout
        $this->session->migrate(FALSE, $this->session_timeout);

    }

    /**
     *
     * Process a login request
     *
     * @param $post
     * @return bool|array
     */
    protected function validateLogin($post, $isEncoded = false)
    {

        /**
         * First set up all the Symfony Security Component requirements
         */

        $userProvider = new UserProvider();

        $userChecker = new UserChecker();

        if ($isEncoded) {
            $defaultEncoder = new PlainPasswordEncoder();
        } else {
            $defaultEncoder = new MessageDigestPasswordEncoder('sha512', true, 5000);
        }

        $encoderFactory = new EncoderFactory(array(
            'GTC\Framework\User' => $defaultEncoder,
        ));

        //$anonymousKey = uniqid();

        $authenticationProviders = array(
            // validates AnonymousToken instances
            //new AnonymousAuthenticationProvider($anonymousKey),
            // retrieve the user for a UsernamePasswordToken
            new DaoAuthenticationProvider($userProvider, $userChecker, 'secured_area', $encoderFactory)
        );

        $authenticationManager = new AuthenticationProviderManager($authenticationProviders);

        $voters = array(
            // votes if any attribute starts with a given prefix
            new RoleVoter('ROLE_')
        );

        $accessDecisionManager = new AccessDecisionManager($voters);

        $securityContext = new SecurityContext($authenticationManager, $accessDecisionManager);


        try {

            $this->load_db('slave');

            $username = $post['_username'];
            $password = $post['_password']; // try 'foo123foo'
            $usernamePasswordToken = new UsernamePasswordToken($username, $password, 'secured_area');

            $authenticationManager->authenticate($usernamePasswordToken);
            $securityContext->setToken($usernamePasswordToken);

            if ($securityContext->isGranted('ROLE_ACCOUNT_OWNER') || $securityContext->isGranted('ROLE_ACCOUNT_USER')) {

                // Access granted, set up persistence mechanism
                // @Sai need to set timeout of session
                $this->session->set('_security_' . 'secured_area', serialize($securityContext->getToken()));

                // now grab user info from token to check if this is the first time user is logging in
                $user = $securityContext->getToken()->getUser();

                // for account subusers:
                // check if this is a first time login, then enable and create contact record

                if ($user->isFirstLogin()) {

                    $this->load_db('master');

                    $user_id = $user->getUserId();

                    $this->user_logic = new UserLogic;

                    $user_activated = $this->user_logic->activateUser($user_id);
                    
                    if ($user_activated === false) {
                        return $this->user_logic->getErrorMessage();
                    }
                }
                
                return true;

            } else {

                // Access denied

                $error = array(
                    'last_username' => $post['_username'], 
                    'error' => array(
                        'message' => 'Invalid role'
                    )
                );
                
                return $error;
            }
        } catch (AuthenticationException $e) {

            // send error back to login form
            $error = array(
                'last_username' => $post['_username'], 
                'error' => array(
                    'message' => $e->getPrevious()->getMessage()
                )
            );
            
            return $error;

        } catch (ProviderNotFoundException $e) {
            die('Provider could not be found');
        }

        return false;

    }

    /**
     *
     * @param string $action alternative action that requires configuration in the login template, e.g. 'alreadyloggedin'
     *
     */
    protected function loginAction()
    {

        switch(strtolower($_SERVER['HTTP_HOST'])) {

            case      'api.positionplusgps.com' : header("Location: /api");
                                                  break;

            case   'mobile.positionplusgps.com' :
            case     'mobi.positionplusgps.com' :
            case        'm.positionplusgps.com' : header("Location: /mobile");
                                                  break;

            case   'orders.positionplusgps.com' : header("Location: /orders");
                                                  break;

                                        default : header("Location: /login");

        }

        exit;
    }

    /**
     * @route logout /logout
     */
    public function logoutAction()
    {

        // destroy persistent mechanism data
        $this->session->remove('_security_' . 'secured_area');

        header("Location: /");
        exit;

    }

    /**
     * Take user to default landing page/route
     */
    protected function landingAction($post = NULL){

        if (isset($post['_last-route']) && $post['_last-route'] !== '') {

            // send to last page before session timeout
            $lastRoute = $post['_last-route'];
            header("Location: {$lastRoute}");

        } else {

            // send to designated home page
            header("Location: ".$this->defaultRoute);

        }

        exit;
    }

    /**
     * Set up Twig for any controller that needs it
     */
    protected function load_twig(){

        // Twig configuation
        $this->loader = new Twig_Loader_Filesystem(array(
            '../application/views'
        ));
        $this->twig = new Twig_Environment($this->loader, array(
            'cache' => (ENVIRONMENT == 'development') ? FALSE : '../cache_twig',
            'auto_reload' => TRUE,
            'strict_variables' => TRUE
        ));

        // add Twig custom filters
        $custom_filters = new Twig\Filters();

        $ordinal_suffix_filter = new \Twig_SimpleFilter('ordinal_suffix', array($custom_filters, 'ordinalSuffix'));
        $this->twig->addFilter($ordinal_suffix_filter);

        $hour_filter = new \Twig_SimpleFilter('hour', array($custom_filters, 'hour'));
        $this->twig->addFilter($hour_filter);

        $ellipsis_filter = new \Twig_SimpleFilter('ellipsis', array($custom_filters, 'ellipsis'));
        $this->twig->addFilter($ellipsis_filter);
    }

    /**
     *
     *
     * @param null $dbname
     */
    protected function load_db($dbname=null)
    {
        // GTC\Framework\DatabaseHelper;
        $db_helper = new DatabaseHelper;

        $db_helper->load_db($dbname);

    }

}
