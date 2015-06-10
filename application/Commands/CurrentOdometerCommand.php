<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressHelper;

use Models\Logic\VehicleLogic;
use Models\Data\VehicleData;
use Models\Logic\UserLogic;
use Models\Logic\CronLogic;

//error_reporting(0);

/**
 * @author
 *
 */
class CurrentOdometerCommand extends Command
{
    private $cronName   = "currentodometer";

    public  $output     = "";

    public  $yml_out    = "";

    public  $alias_list = array();

    // can change this value to increase number of events pulled for a unit
    // increasing to high number can overload system if too many units and/or cron being run too often
    // 500 is just an estimate decision when cron was initially developed, can increment if needed
    private $eventPullThreshold = 500;

    /**
     * @param
     */
    public function __construct()
    {
        parent::__construct();

        //$this->vehicle_data   = new VehicleData;
        $this->vehicle_logic    = new VehicleLogic;
        $this->user_logic       = new UserLogic;
        $this->cron_logic       = new CronLogic;

        // process if the specified cron is still currently running, if so false is returned to stop this process from running
        if (! $this->cron_logic->processAllowCronRun($this->cronName)) {
            exit();
        }
    }

    public function configure()
    {
        $this->setName($this->cronName);
        $this->setDescription('This will update the current odometer for active units');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        ob_start();

        $start_time     = microtime(true);

        $fp = fopen(LOGPATH.'cronlog.txt', 'a+');
        //$fp2 = fopen(LOGPATH.'crons/currentodometer.txt', 'a+');

        $this->output = $output;

        //fwrite($fp, "\n### ".date('Y-m-d H:i:s')." Starting CurrentOdometer\n");
        //fwrite($fp2, "\n### ".date('Y-m-d H:i:s')." Starting CurrentOdometer\n");
        echo "\n### ".date('Y-m-d H:i:s')." Starting CurrentOdometer\n";
        //echo "\n### ".date('Y-m-d H:i:s')." Starting CurrentOdometer\n";

        // get all system accounts
        $account_status = array(1); // 1 = active account
        $accounts = $this->user_logic->getAccounts($account_status);

        if (! empty($accounts)) {

            $current_account = 0;

            // for each account, get all active units
            foreach ($accounts as $key => $account) {
                if ($account['account_id'] != $current_account) {
                    // get account active vehicles
                    $units = array();
                    $units = $this->vehicle_logic->getActiveVehicles($account['account_id']);

                    if (! empty($units)) {
                        foreach ($units as $key => $unit) {
                            if (isset($unit['unitodometer_id']) AND ! empty($unit['unitodometer_id'])) {

                            	// if the unit already has a unitodemeter record in the db, get it
                            	$unitodometer = $this->vehicle_logic->getUnitOdometer($unit['unitodometer_id']);
                            }
                            else
                            {
                            	// if the unit does not have a unitodemeter record in the db, create it
                            	$unitodometer_id = $this->vehicle_logic->createUnitOdometer(array("initialodometer" => 0));

                            	if ($unitodometer_id !== false)
                            	{
                            		// if successful, update the unit with the new odometer id
									$update = $this->vehicle_logic->updateUnitInfo($unit['unit_id'], array('unitodometer_id' => $unitodometer_id));

									// after updating the unit with the unitodometer_id, create the array for usage
									$unitodometer = array(

										"unitodometer_id" => $unitodometer_id,
										"odometerevent_id" => 0,
										"initialodometer" => 0,
										"currentodometer" => 0
									);
								}
                            }

                            if(isset($unitodometer) AND ! empty($unitodometer) AND isset($unitodometer['odometerevent_id']))
                            {
	                            // get events data for this unit
	                            $events = $this->vehicle_logic->getVehicleUnitEventsAfterId($unit, $unitodometer['odometerevent_id'], $this->eventPullThreshold);

	                            if (! empty($events) AND count($events) > 1) {
	                                $initial        = true;
	                                $id             = 0;
	                                $last_mileage   = 0;
	                                $total_distance = 0;
	                                $did_update		= false;

	                                foreach ($events as $event) {

	                                	if($event['event_id']==8)
	                                	{
		                                	$did_update=true;
	                                	}

	                                    $id = $event['id'];
	                                    $travel_mileage = 0;

	                                    if (! $initial) {
	                                        $travel_mileage = ($event['distance'] - $last_mileage);
	                                        if ($travel_mileage > 0) {
	                                            $total_distance += $travel_mileage;
	                                        }
	                                    } else {
	                                        $initial = false;
	                                    }

	                                    $last_mileage = $event['distance'];
	                                }

	                                // if event id > last processed id, and total traveled distance is > 0,  then calculate current unit odometer
	                                if ($id > $unitodometer['odometerevent_id']) {
	                                    if ($total_distance > 0 || $did_update) {
	                                        $unitodometer['currentodometer'] = $unitodometer['currentodometer'] + $total_distance;

	                                        if($unit["unitstatus_id"]==2)
	                                        {
	                                        	// if the vehicle has driven any distance and the status is still set to "inventory", change it to "installed"
	                                        	$this->vehicle_logic->updateUnitInfo($unit['unit_id'], array('unitstatus_id' => 1));
	                                        }
	                                    }

	                                    $this->vehicle_logic->updateUnitOdometer($unitodometer['unitodometer_id'], array('odometerevent_id' => $id, 'currentodometer' => (int)$unitodometer['currentodometer']));
	                                }
	                            } else if (count($events) == 1) {
	                                //need at least two events. 1 event is most likely the last recored event
	                            }
							}
                        }
                    }

                    unset($units);
                    $current_account = $account['account_id'];
                }
            }
        }

        $end_time = microtime(true);

        //fwrite($fp, "\### ".number_format((float)($end_time - $start_time), 2)." sec\n");
        //fwrite($fp2, "\### ".date('Y-m-d H:i:s')."\n");
        echo "\n### ".number_format((float)($end_time - $start_time), 2)." sec\n";
        //echo "\### ".date('Y-m-d H:i:s')."\n";

        fwrite($fp, ob_get_contents());
        //fwrite($fp2, ob_get_contents());
        ob_end_clean();

        $output->writeln("<fg=red>Done</fg=red>");

        die();
    }
}
