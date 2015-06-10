<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressHelper;

use Models\Logic\AlertLogic;
use Models\Logic\VehicleLogic;
use Models\Data\VehicleData;
use Models\Logic\TerritoryLogic;
use Models\Logic\AlertCronLogic;
use Models\Logic\CronLogic;

//error_reporting(0);
 
/**
 * @author
 *
 */
class CheckAlertsCommand extends Command
{
    private $cronName   = "checkalerts";

    public  $output     = "";

    public  $yml_out    = "";

    public  $alias_list = array();

    /**
     * @param
     */
    public function __construct()
    {
        parent::__construct();

        $this->vehicle_data     = new VehicleData;
        $this->vehicle_logic    = new VehicleLogic;
        $this->alert_logic      = new AlertLogic;
        $this->territory_logic  = new TerritoryLogic;
        $this->alert_cron_logic = new AlertCronLogic;
        $this->cron_logic       = new CronLogic;

        // process if the specified cron is still currently running, if so false is returned to stop this process from running
        if (! $this->cron_logic->processAllowCronRun($this->cronName)) {
            exit();
        }
    }

    public function configure()
    {
        $this->setName($this->cronName);
        $this->setDescription('This will check for alerts');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        ob_start();

        $start_time     = microtime(true);

        $fp = fopen(LOGPATH.'cronlog.txt', 'a+');
        //$fp2 = fopen(LOGPATH.'crons/checkalertslog.txt', 'a+');
        
        $this->output = $output;

        //fwrite($fp, "\n### ".date('Y-m-d H:i:s')." Starting CheckAlerts\n");
        //fwrite($fp2, "\n### ".date('Y-m-d H:i:s')." Starting CheckAlerts\n");
        echo "\n### ".date('Y-m-d H:i:s')." Starting CheckAlerts\n";
        //echo "\n### ".date('Y-m-d H:i:s')." Starting CheckAlerts\n";
        
        // get all Alerts to process for event triggering alerts
        $alerts = $this->alert_logic->getAlerts('', true);
        
        if (! empty($alerts)) {

            $account_units   = array();
            $current_account = 0;
            
            foreach ($alerts as $key => $alert) {

                // pull units for each account once
                if ($alert['account_id'] != $current_account) {
                    // if done with current account, do updates for the current account units before moving to next account
                    if (! empty($account_units)) {
                        $this->alert_cron_logic->updateUnitAlertStatus($account_units);
                        //fwrite($fp2, "\nAccount_id: ".$current_account.", End Processing and update UnitAlertStatus\n");
                        //echo "\nAccount_id: ".$current_account.", End Processing and update UnitAlertStatus\n";
                        unset($account_units);
                    }
                    
                    //fwrite($fp2, "\nAccount_id: ".$alert['account_id'].", Start Processing for this account\n");
                    //echo "\nAccount_id: ".$alert['account_id'].", Start Processing for this account\n";

                    // get account vehicle data for this current alert
                    $units = $this->vehicle_logic->getVehicleDataInfoByAccountId($alert['account_id']);

                    // set up process info for each unit
                    foreach ($units as $key => $unit) {
                        $unit['process_to_id']              = 0;
                        $unit['update']                     = FALSE;
                        $account_units[$unit['unit_id']]    = $unit;
                    }

                    unset($units);
                    $current_account = $alert['account_id'];
                }

                // process for alert triggers
                if ($alert['unit'] == 'All') {
                    //All Units
                    foreach ($account_units as $index => $unit) {
                        $this->alert_cron_logic->getVehicleEvents($account_units, $index);
                        $this->alert_cron_logic->processAlertEvents($alert, $account_units[$index]);
                    }
                }
                else if ($alert['unit'] == 'Group') {
                    //Group of Units
                    foreach ($account_units as $index => $unit) {
                        if ($unit['unitgroup_id'] == $alert['unitgroup_id']) {
                            $this->alert_cron_logic->getVehicleEvents($account_units, $index);
                            $this->alert_cron_logic->processAlertEvents($alert, $account_units[$index]);
                        }
                    }
                } else {
                    //Single Unit
                    foreach ($account_units as $index => $unit) {
                        if ($unit['unit_id'] == $alert['unit_id']) {
                            $this->alert_cron_logic->getVehicleEvents($account_units, $index);
                            $this->alert_cron_logic->processAlertEvents($alert, $account_units[$index]);
                        }
                    }
                }
            }

            if ( ! empty($account_units)) {
                // do update for the last account units
                $this->alert_cron_logic->updateUnitAlertStatus($account_units);
                //fwrite($fp2, "\nAccount_id: ".$current_account.", End Processing and update UnitAlertStatus\n");
            }
        }

        $end_time = microtime(true);

        //fwrite($fp, "\n### ".number_format((float)($end_time - $start_time), 2)." sec\n");
        //fwrite($fp2, "\n### ".date('Y-m-d H:i:s')."\n");
        echo "\n### ".number_format((float)($end_time - $start_time), 2)." sec\n";
        //echo "\n### ".date('Y-m-d H:i:s')."\n";

        fwrite($fp, ob_get_contents());
        //fwrite($fp2, ob_get_contents());
        ob_end_clean();

        $output->writeln("<fg=red>Done</fg=red>");

        die();
    }
}