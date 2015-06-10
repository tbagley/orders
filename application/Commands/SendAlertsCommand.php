<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressHelper;

use Models\Logic\AlertLogic;
use Models\Logic\VehicleLogic;
use Models\Logic\AlertCronLogic;
use Models\Logic\CronLogic;

use GTC\Component\Utils\Date;

//error_reporting(0);

/**
 * @author
 *
 * This cron runs every 10 minutes
 *
 */
class SendAlertsCommand extends Command
{
    private $cronName   = "sendalerts";

    public  $output     = "";

    public  $yml_out    = "";

    public  $alias_list = array();

    // can change this value to increase number of alerts pulled to send out
    // increasing to high number can overload system if too many alerts being pulled to send per run
    // 500 is just an estimate decision when cron was initially developed, can increase if needed
    private $alertSendThreshold = 500;

    /**
     * @param
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->alert_logic      = new AlertLogic;
        $this->vehicle_logic    = new VehicleLogic;
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
        $this->setDescription('This will send queued alerts');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        ob_start();

        $start_time = microtime(true);

        $fp = fopen(LOGPATH.'cronlog.txt', 'a+');
        //$fp2 = fopen(LOGPATH.'crons/sendalertslog.txt', 'a+');

        $this->output = $output;

        //fwrite($fp, "\n### ".date('Y-m-d H:i:s')." Starting SendAlerts\n");
        //fwrite($fp2, "\n### ".date('Y-m-d H:i:s')." Starting SendAlerts\n");
        echo "\n### ".date('Y-m-d H:i:s')." Starting SendAlerts\n";
        //echo "\n### ".date('Y-m-d H:i:s')." Starting SendAlerts\n";

        // pull the Alerts to send
        $emails  = $this->alert_cron_logic->getAlertSendEmail($this->alertSendThreshold);

        if (isset($emails) AND ! empty($emails)) {
            foreach ($emails as $index => $email) {
                $alert  = array();
                // pull each alert info
                if ( ! isset($alert[$email['alert_id']])) {
                    $alert[$email['alert_id']] = $this->alert_logic->getAlertDataInfoById($email['alert_id']);
                }
                
                $unit               = $this->vehicle_logic->getVehicleInfoById($email['unit_id']);                      // get unit info
                $event              = $this->vehicle_logic->getVehicleDataEventInfo($unit, $email['unitevent_id']);     // get alert event info

                if ($event AND ! empty($alert)) {
                    $tmp_contact   = array();
                    $contact_email = array();
                    $contact_cell  = array();

                    // get email and cell contact info
                    foreach ($alert[$email['alert_id']]['contacts'] as $contact) {
                        if ($alert[$email['alert_id']]['method'] == 'email') {
                            // if alert contact method is email
                            if (! empty($contact['email'])) {
                                $contact_email[] = $contact['email'];
                            }
                        }
                        else if ($alert[$email['alert_id']]['method'] == 'sms') {
                            // if alert contact method is sms/cellphone
                            if (! empty($contact['cellnumber'])) {
                                $contact_cell[] = $contact['cellnumber']."@".$contact['gateway'];
                            }
                        } else {
                            // if alert contact method is both email and sms/cellphone
                            if (! empty($contact['email'])) {
                                $contact_email[] = $contact['email'];
                            }
                            if (! empty($contact['cellnumber'])) {
                                $contact_cell[] = $contact['cellnumber']."@".$contact['gateway'];
                            }
                        }
    
                        $tmp_contact[] = array($contact['email'] => $alert[$email['alert_id']]['method']);
                    }

                    // Send the Alert
                    // UNCOMMENT THIS TO ACTIVATE SENDING OF ALERTS
                    $this->alert_cron_logic->sendAlertEmails($alert, $unit, $email, $event, $contact_email, $contact_cell);

                    // set the alert sent date and time
                    $alert_send_datetime = Date::locale_to_utc(date('Y-m-d H:i:s'), SERVER_TIMEZONE);

                    //fwrite($fp2, "\nAlert Sent: account_id = ".$email['account_id'].", alert_id = ".$email['alert_id'].", unit_id =".$email['unit_id'].", event_id = ".$email['unitevent_id']."\n");
                    //echo "\nAlert Sent: account_id = ".$email['account_id'].", alert_id = ".$email['alert_id'].", unit_id =".$email['unit_id'].", event_id = ".$email['unitevent_id']."\n";
                    
                    if ( ! empty($tmp_contact))
                    {
                        // save territoryname if the location is in a landmark
                        $email['territoryname'] = (! empty($event['landmark']) AND ! empty($event['landmark']['territoryname'])) ? $event['landmark']['territoryname'] : '';
                        
                        // save address components
                        $email['streetaddress'] = (! empty($event['streetaddress'])) ? $event['streetaddress'] : '';
                        $email['city'] = (! empty($event['city'])) ? $event['city'] : '';
                        $email['state'] = (! empty($event['state'])) ? $event['state'] : '';
                        $email['zipcode'] = (! empty($event['zipcode'])) ? $event['zipcode'] : '';
                        $email['country'] = (! empty($event['country'])) ? $event['country'] : '';
                        
                        $isNonReportingAlertType = ($alert[$email['alert_id']]['alerttype_id'] == 6); // 6 Non Reporting
                        $email['uniteventdate'] = ( isset($event['unittime']) AND !empty($event['unittime']) AND !$isNonReportingAlertType ) ? $event['unittime'] : $alert_send_datetime;
                        $this->alert_cron_logic->logAlertHistory($email, $alert_send_datetime);
                        unset($tmp_contact);
                    }
                    
                    // update the sent Alert to being sent and date time alert was sent
                    $this->alert_cron_logic->updateAlertSend($email['alertsend_id'], array('sent' => 1, 'sentdate' => $alert_send_datetime));
                    
                }
            }
        }

        //remove all older alert emails that have been sent
        // for 5% of the time this cron runs, this condition will pass and run
        if (mt_rand(1,100) < 5)
        {
            // uncomment to start deleting already sent alerts
            $this->alert_cron_logic->deleteAlertSent();
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