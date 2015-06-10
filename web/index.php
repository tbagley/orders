<?php

define ( 'CYCLE_START' , microtime(true) ) ;

/**
 *
 * This application attempts to be compliant with PSR recommendations found at
 * http://www.php-fig.org/
 *
 * This is a NO FRAMEWORK framework application, mostly based on the Symfony 2 components
 *
 */

// PSR-0 autoloader, generated by Composer
include "../vendor/autoload.php";


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\EventListener\RouterListener;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

use Symfony\Component\HttpKernel\Controller\ControllerResolver;

use Symfony\Component\HttpKernel\HttpKernel;

use Symfony\Component\Yaml\Yaml;

use GTC\Framework\App;


/*
 *---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 *---------------------------------------------------------------
 *
 * You can load different configurations depending on your
 * current environment. Setting the environment also influences
 * things like logging and error reporting.
 *
 * This can be set to anything, but default usage is:
 *
 *     development
 *     testing
 *     production
 *
 */

define('ENVIRONMENT', 'development');

if (defined('ENVIRONMENT'))
{
    switch (ENVIRONMENT)
    {
        case 'development':
            error_reporting(E_ALL);
            break;

        case 'testing':
        case 'production':
            error_reporting(0);
            break;

        default:
            exit('The application environment is not set correctly.');
    }
}


/**
 * Set up the application root path variable
 */
$root_path = '../';

if (realpath($root_path) !== FALSE) {
    $root_path = realpath($root_path).'/';
}

// make it global
define('ROOTPATH', str_replace("\\", "/", $root_path));


/**
 * Get instance parameters from a config file
 */
$config = Yaml::parse(ROOTPATH.'config/parameters.yml');

/**
 * Set to location of server
 */
define('SERVER_TIMEZONE',  $config['parameters']['server_timezone']);

/**
 * Defined Email Configuration Settings
 */
define('EMAIL_HOST', $config['parameters']['email_host']);
define('EMAIL_PORT', $config['parameters']['email_port']);
define('EMAIL_SECURITY', $config['parameters']['email_security']);
define('EMAIL_USERNAME', $config['parameters']['email_username']);
define('EMAIL_PASSWORD', $config['parameters']['email_password']);

/**
 * Domain used to send emails 'from'
 */
define('EMAIL_FROM_DOMAIN', $config['parameters']['email_from_domain']);

/**
 * User registration expiry (48 hours in seconds)
 */
define('USER_REGISTRATION_EXPIRY', $config['parameters']['user_registration_expiry']);


/*
 * --------------------------------------------------------------------
 * START THE REQUEST PROCESSING
 * --------------------------------------------------------------------
 */

// GTC\Framework\App
$core = new App;

/**
 * Codeigniter inspired singleton
 */
function get_instance(){
    global $core;
    return $core;
}

// pass config vars to core object
$core->config = $config;

// Symfony\Component\HttpFoundation\Request
$request = Request::createFromGlobals();

// pass request object to controller layer
$core->request = $request;

/**
 * set up the routes
 */
$routes = array();

// get the routes from a config file
$routes_config = Yaml::parse(ROOTPATH.'config/routes.yml');

$routes = new RouteCollection();

foreach($routes_config as $key => $val) {
    $routes->add($key, new Route($val['pattern'], $val['defaults']));
}

$context = new RequestContext();
$context->fromRequest($request);

$matcher = new UrlMatcher($routes, $context);

// handles invalid routes
try {
    $matcher->match($request->getPathInfo());     
} catch (ResourceNotFoundException $e) {    
    header('Location: /error/pagenotfound');
}

$dispatcher = new EventDispatcher();
$dispatcher->addSubscriber(new RouterListener($matcher));

$resolver = new ControllerResolver();

$kernel = new HttpKernel($dispatcher, $resolver);

// handles database error
try {
    $kernel->handle($request)->send();
} catch (PDOException $e) {
    header('Location: /error/internalservererror');
}
/* End of file index.php */


/**
 * print_r alternative
 * the 'b' is for better because it includes the <pre> tag
 *
 * @param $array
 * @param bool $die
 */
function print_rb($array, $die = true) {
    echo "<pre>";
    print_r($array);
    echo "</pre>";

    if ($die) {
        die();
    }
}