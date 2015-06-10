<?php

namespace GTC\Framework;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

class DatabaseHelper {

    /**
     *
     *
     * @param null $dbname
     */
    public function load_db($dbname=null)
    {
        if (null !== $dbname) {

            // Use the global object
            $core =& get_instance();

            $config = new Configuration();

            switch ($dbname) {
                case "master":

                    if (!isset($core->db_write)) {
                        $connectionParams = array(
                            'dbname' => $core->config['database']['master']['dbname'],
                            'user' => $core->config['database']['master']['user'],
                            'password' => $core->config['database']['master']['password'],
                            'host' => $core->config['database']['master']['host'],
                            'driver' => $core->config['database']['master']['driver'],
                        );

                        $core->db_write = DriverManager::getConnection($connectionParams, $config);
                    }
                    break;
                case "slave":

                    if (!isset($core->db_read)) {
                        $connectionParams = array(
                            'dbname' => $core->config['database']['slave']['dbname'],
                            'user' => $core->config['database']['slave']['user'],
                            'password' => $core->config['database']['slave']['password'],
                            'host' => $core->config['database']['slave']['host'],
                            'driver' => $core->config['database']['slave']['driver'],
                        );

                        $core->db_read = DriverManager::getConnection($connectionParams, $config);
                    }
                    break;
                default:
                    die('Unknown Database Error');
                    break;
            }
        } else {
            die('Unknown Database Error');
        }


    }
}
