<?php

namespace Models\Logic;

use Models\Logic\BaseLogic;

//use GTC\Component\Utils\Date;
//use GTC\Component\Utils\Arrayhelper;
//use GTC\Component\Utils\CSV\CSVReader;

use Swift\Transport\Validate;

class CronLogic extends BaseLogic
{
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();

    }

    /**
     * Determines if the specified cron is still currently running
     *
     * Uses exec() and greps for PIDs
     *
     * @param string $script
     * @return bool
     */
    public function processAllowCronRun($cron)
    {
        // hardcoded for now since some of the crons are not running maybe due to existing hanging process
        // remove this to turn this back on
        return true;
        
        // look for all process with this $cron_name
		$result = exec("ps aux | grep '" . $cron . "' | grep -v grep", $pids);

        // currently set for scheduledreports
		if ($cron == 'scheduledreports' AND count($pids) > 1) {

			foreach ($pids as $row) {

				$ps = preg_split('/ +/', $row);

				if (getmypid() != $ps[1]) {

				    // kill older script processes and let new process run
					exec('kill -9 ' . $ps[1]);
					return true;
				}
			}
		} else if (count($pids) > 1) {

            // return false to trigger condition to exit this current cron process request (and let existing process run)
			return false;
		}
		
		// allow this cron process to run, return true
		return true;
    }

}