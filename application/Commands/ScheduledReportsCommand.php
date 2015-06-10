<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressHelper;

use GTC\Component\Utils\Date;
use GTC\Component\Utils\CSV\CSVBuilder;
use GTC\Component\Utils\PDF\PDFDataAdapter;
use GTC\Component\Utils\PDF\TCPDFBuilder;

use Models\Logic\AlertLogic;
use Models\Logic\VehicleLogic;
use Models\Data\VehicleData;
use Models\Logic\TerritoryLogic;
use Models\Logic\AlertCronLogic;
use Models\Logic\CronLogic;
use Models\Logic\ReportLogic;

//error_reporting(0);

/**
 * @author
 *
 */
class ScheduledReportsCommand extends Command
{
    private $cronName   = "scheduledreports";

    public  $output     = "";

    public  $yml_out    = "";

    public  $alias_list = array();

    /**
     * @param
     */
    public function __construct()
    {
        parent::__construct();

        /*
        $this->vehicle_data     = new VehicleData;
        $this->vehicle_logic    = new VehicleLogic;
        $this->alert_logic      = new AlertLogic;
        $this->territory_logic  = new TerritoryLogic;
        $this->alert_cron_logic = new AlertCronLogic;
        */
        $this->cron_logic       = new CronLogic;
        $this->report_logic     = new ReportLogic;

        // process if the specified cron is still currently running, if so false is returned to stop this process from running
        if (! $this->cron_logic->processAllowCronRun($this->cronName)) {
            exit();
        }
    }

    public function configure()
    {
        $this->setName($this->cronName);
        $this->setDescription('This will process scheduled reports');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        ob_start();

        $start_time     = microtime(true);

        $fp = fopen(LOGPATH.'cronlog.txt', 'a+');
        //$fp2 = fopen(LOGPATH.'crons/scheduledreports.txt', 'a+');

        $this->output = $output;

        //fwrite($fp, "\n### ".date('Y-m-d H:i:s')." Starting ScheduledReports\n");
        //fwrite($fp2, "\n### ".date('Y-m-d H:i:s')." Starting ScheduledReports\n");
        echo "\n### ".date('Y-m-d H:i:s')." Starting ScheduledReports\n";
        //echo "\n### ".date('Y-m-d H:i:s')." Starting ScheduledReports\n";
        
        // get the current server time
        $search_time = Date::locale_to_utc(date('Y-m-d H:i:s'), SERVER_TIMEZONE);
        
        // get all the scheduled reports that needs to be run
        $scheduled_reports = $this->report_logic->getScheduledReportsToRun($search_time);

        if (! empty($scheduled_reports)) {

            $file_path = ROOTPATH . 'temp_files/';
            
            foreach($scheduled_reports as $index => $report) {

                $contact_emails = $files = array();
                
                // check for valid contact emails
                if (! empty($report['contacts'])) {
                    foreach($report['contacts'] as $contact) {
                        if (! empty($contact['email'])) {
                            $contact_emails[] = $contact['email'];
                        }        
                    }
                }
                
                // continue processing report only if there are assigned contacts with emails
                if (! empty($contact_emails)) {
                    // set report method to Schedule for when logging
                    $report['method'] = 'Scheduled';
                    
                    // set report format
                    $report['report_output'] = strtolower($report['format']);
                    
                    $report['report_name'] = $report['schedulereportname'];
                    
                    // convert endtime from utc to user's local
                    $local_time = Date::utc_to_locale($report['nextruntime'], $report['user_timezone'], 'SECONDS');
                    
                    // set starttime and endtime
                    $report['endtime'] = date('Y-m-d H:i:s', $local_time);                    
                    switch($report['schedule']){
                        case 'Daily':
                            $report['starttime'] = date('Y-m-d H:i:s', strtotime('-1 day', $local_time));
                            break;
                        case 'Weekly':
                            $report['starttime'] = date('Y-m-d H:i:s', strtotime('-1 week', $local_time));
                            break;
                        case 'Monthly':
                            $report['starttime'] = date('Y-m-d H:i:s', strtotime('-1 month', $local_time));
                            break;     
                    }

                    // run report
                    $report_output = $this->report_logic->runReport($report);
                    
                    if ($report_output !== false) {
                        // create filename
                        $file_name = date('Ymd') . '_' . preg_replace('/[^A-Za-z0-9]/','_',trim($report['schedulereportname'])) . '.' . $report['report_output']; 
                        $fullpath = $file_path . $file_name;
                        
                        if ($report['report_output'] == 'csv') {   // create csv
                            $csv_builder = new CSVBuilder();
                            $csv_builder->setSeparator(',');
                            $csv_builder->setClosure('"');
                            $csv_builder->setFields($report_output['columns']);
                            $export_data = $csv_builder->format($report_output['data'])->getFormattedRows();
                            
                            if (! empty($export_data)) {
                                $fstream = fopen($fullpath, 'w+');
                                if ($fstream !== false) {
                                    if (fwrite($fstream, $export_data)) {
                                        if (file_exists($fullpath)) {
                                            $files[] = $fullpath;
                                        }    
                                    }
                                }
                            }                   
                        } else {                            // create pdf
                            //ini_set('memory_limit', '128M');
                            $pdf_output = PDFDataAdapter::format($report_output);
                            $pdf_builder = new TCPDFBuilder('L');
                            $pdf_builder->create($pdf_output);
                            $pdf_builder->Output($fullpath, 'F');
                            
                            if (file_exists($fullpath)) {
                                $files[] = $fullpath;
                            }
                        }
                        
                        // update last and next run time
                        $next_runtime = $this->report_logic->calculateNextRuntime($report);
                        $this->report_logic->updateScheduledReport($report['schedulereport_id'], array('lastruntime' => $report['nextruntime'], 'nextruntime' => $next_runtime));
                        
                        // email reports as attachments
                        if (! empty($files)) {
                            
                            $failed_recipients = array();
    
                            // Create the mail transport configuration
                            $transport = \Swift_SmtpTransport::newInstance(EMAIL_HOST, EMAIL_PORT, EMAIL_SECURITY);
                            $transport->setUsername(EMAIL_USERNAME);
                            $transport->setPassword(EMAIL_PASSWORD);
    
                            // Create the message
                            $message = \Swift_Message::newInstance();
                            $message->setSubject(preg_replace('/[^A-Za-z0-9]/','_',trim($report['schedulereportname'])) . '_' . $report['format']);
                            $message->setBody('<html><body>You can open these attachments at anytime.<br>Some attachments may take a while to load because of the size.<br></body></html>', 'text/html');
                            //$message->setFrom('');  // NTD: determine if dealers will have email domains or not
                            $message->setFrom(array('report@'.EMAIL_FROM_DOMAIN => EMAIL_FROM_DOMAIN)); // NTD: determine if dealers will have email domains or not

                            $message->setTo($contact_emails);
                            
                            // iterate through and attach files to email
                            foreach($files as $filepath) {
                                if (file_exists($filepath)) {
                                    // Create Attachment and add it to the message
                                    $message->attach(\Swift_Attachment::fromPath($filepath));   
                                }
                            }
                            
                            // Send the email
                            $mailer = \Swift_Mailer::newInstance($transport);
                            $mailer->send($message, $failed_recipients);
                            
                            /*
                            if (! empty($failed_recipients)) {
                                print_r('failed to send attachment');
                            }
                            */
                            // iterate through and remove files
                            foreach($files as $filepath) {
                                if (file_exists($filepath)) {
                                    unlink($filepath);   
                                }
                            }
                        }
                    }   
                }
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