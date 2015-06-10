<?php

/**
 *
 * This application attempts to be compliant with PSR recommendations found at
 * http://www.php-fig.org/
 *
 * This is a NO FRAMEWORK framework application, mostly based on the Symfony 2 components
 *
 */

// PSR-0 autoloader, generated by Composer
include "vendor/autoload.php";

use GTC\Framework\App;
use GTC\Framework\DatabaseHelper;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Yaml\Yaml;

// mimic codeigniter
define('ROOTPATH', './');
define('LOGPATH', './logs/');

/**
 * Get instance parameters from a config file
 */
$config = Yaml::parse(ROOTPATH.'config/parameters.yml');

/**
 * Set to location of server
 */
define('SERVER_TIMEZONE', $config['parameters']['server_timezone']);

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

$core->config = $config;

// on script shutdown function, run customErrorHandler function
register_shutdown_function('customErrorHandler');

$db = new DatabaseHelper;

$db->load_db('master');
$db->load_db('slave');

$console = new Application('Crossbones', 'def alpha');

//
$console->add(new Commands\CheckAlertsCommand());

//
$console->add(new Commands\SendAlertsCommand());

//
$console->add(new Commands\ScheduledReportsCommand());

//
$console->add(new Commands\CurrentOdometerCommand());

//
$console->add(new Commands\ResetTestDataCommand());

//
$console->add(new Commands\ProcessIncompleteTerritoryCommand());

// wrap it up
return $console;



/**
 *  Custom Error Handling function called when there is a fatal error and script shutdown is striggered
 *  get the last error and if error type is fatal error (1), then log the fatal error to file
 */
function customErrorHandler()
{
    // get last error
    $error = error_get_last();

    $error_message = "";

    //1    E_ERROR (integer)     Fatal run-time errors. These indicate errors that can not be recovered from, such as a memory allocation problem. Execution of the script is halted.
    //2    E_WARNING (integer)     Run-time warnings (non-fatal errors). Execution of the script is not halted.
    //8    E_NOTICE (integer)     Run-time notices. Indicate that the script encountered something that could indicate an error, but could also happen in the normal course of running a script.
    //256    E_USER_ERROR (integer)     User-generated error message. This is like an E_ERROR, except it is generated in PHP code by using the PHP function trigger_error(). ie trigger_error("This is a test error", 256);

    switch ($error['type']) {
        case 1:
        case 256:
            //$fd = fopen(LOGPATH.'fatal_error_log.txt', 'a+');

            $error_message = "FATAL ERROR: [{$error['type']}] {$error['message']} on line {$error['line']} in file {$error['file']}";
            $error_message .= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")\n";
            $error_message .= "Script Aborting...\n";

            // write last error message to file
            //fwrite($fd, "\n### ".date('Y-m-d H:i:s')."\n     $error_message\n\n");
            //fclose($fd);

            exit(1);
            break;

        case 2:
            $error_message = "WARNING: [{$error['type']}] {$error['message']} on line {$error['line']} in file {$error['file']}\n";
            break;

        case 8:
            $error_message = "NOTICE: [{$error['type']}] {$error['message']} on line {$error['line']} in file {$error['file']}\n";
            break;

        default:
            //$error_message = "UNKNOWN: [{$error['type']}] {$error['message']} on line {$error['line']} in file {$error['file']}\n";
            break;
    }

    // write last error message to file
    if (! empty($error_message)) {
        $fd = fopen(LOGPATH.'fatal_error_log.txt', 'a+');
        fwrite($fd, "\n### ".date('Y-m-d H:i:s')."\n     $error_message\n\n");
        fclose($fd);
    }
}