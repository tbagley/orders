<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressHelper;

use GTC\Component\Utils\Date;
use GTC\Component\Map\Decarta;

use Models\Logic\AddressLogic;
use Models\Logic\TerritoryLogic;
use Models\Logic\CronLogic;

//error_reporting(0);

/**
 * @author
 *
 */
class ProcessIncompleteTerritoryCommand extends Command
{
    private $cronName   = "processincompleteterritory";

    public  $output     = "";

    public  $yml_out    = "";

    public  $alias_list = array();

    /**
     * @param
     */
    public function __construct()
    {
        parent::__construct();

        $this->territory_logic  = new TerritoryLogic;
        $this->address_logic    = new AddressLogic;
        $this->cron_logic       = new CronLogic;

        // process if the specified cron is still currently running, if so false is returned to stop this process from running
        if (! $this->cron_logic->processAllowCronRun($this->cronName)) {
            exit();
        }

        $core =& get_instance();
        $this->decarta_api_key = $core->config['parameters']['decarta_api_key'];
    }

    public function configure()
    {
        $this->setName($this->cronName);
        $this->setDescription('This will process incomplete territories');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        ob_start();

        $start_time     = microtime(true);

        $fp = fopen(LOGPATH.'cronlog.txt', 'a+');
        //$fp2 = fopen(LOGPATH.'crons/processincompleteterritory.txt', 'a+');

        $this->output = $output;

        //fwrite($fp, "\n### ".date('Y-m-d H:i:s')." Starting ProcessIncompleteTerritory\n");
        //fwrite($fp2, "\n### ".date('Y-m-d H:i:s')." Starting ProcessIncompleteTerritory\n");
        echo "\n### ".date('Y-m-d H:i:s')." Starting ProcessIncompleteTerritory\n";
        //echo "\n### ".date('Y-m-d H:i:s')." Starting ProcessIncompleteTerritory\n";
        
        $territories = $this->territory_logic->getIncompleteTerritoriesForProcess();

        if (! empty($territories)) {
            foreach ($territories as $t) {
                $address = '';
                $type = '';
                $save = array();
                $update = array();
                
                if ($t['reason'] == 'requires geo'  AND 
                    (! empty($t['streetaddress'])   OR 
                     ! empty($t['city'])            OR 
                     ! empty($t['state'])           OR 
                     ! empty($t['zipcode'])         OR 
                     ! empty($t['country']))) {     // only geocode if there is at least one address component to geocode
                    
                    $address = $this->address_logic->validateAddress($t['streetaddress'], $t['city'], $t['state'], $t['zipcode'], $t['country']);
                    $type = 'address';
                
                } else if ($t['reason'] == 'requires rgeo' AND 
                           ! empty($t['latitude']) AND 
                           ! empty($t['longitude'])) {
                
                    $address = $t['latitude'] . ',' . $t['longitude'];
                    $type = 'reverse'; 
                
                } else {
                    $update['reason'] = 'invalid address or coordinates';
                }
                
                if ($type !== '' AND $address !== '') {
                    if ($type == 'address')
                    {
                        $url = 'http://api.decarta.com/v1/' . $this->decarta_api_key . '/search/' . urlencode($address) . '.JSON';
                    }
                    else if ($type == 'reverse')
                    {
                        $url = 'http://api.decarta.com/v1/' . $this->decarta_api_key . '/reverseGeocode/' . urlencode($address) . '.JSON';
                    }
                    else
                    {
                        continue;
                    }
            		$c = curl_init();
            		curl_setopt($c, CURLOPT_URL, $url);
            		curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
            		curl_setopt($c, CURLOPT_FORBID_REUSE, TRUE);
            		curl_setopt($c, CURLOPT_FRESH_CONNECT, TRUE);
            		$result = curl_exec($c);
            		$response = curl_getinfo($c);
            		curl_close($c);

            		if (! empty($response)) {
                	   if ($response['http_code'] == 200) {
                    	   if (! empty($result)) {
                        	   $result = json_decode($result, TRUE);
                    	       $save = $t;
                    	       if ($type == 'address') {   // if a geocode was performed, update the incomplete territory with the returned latitude and longitude
                                   if (! empty($result['results'])) {
                                       $result = $result['results'][0];
                            	       $save['latitude']       = $result['position']['lat'];
                            	       $save['longitude']      = $result['position']['lon'];       
                                   } else {
                                       $update['reason'] = 'no location found';
                                   }
                    	       } else {                    // else if an rgeo was performed, update the incomplete territory with the returned address components
                                   if (! empty($result['addresses'])) {
                                       $result = $result['addresses'][0];
                        	           $location               = Decarta::cleanDecartaResult($result);
                            	       $save['streetaddress']  = $location['street'];
                            	       $save['city']           = $location['city'];
                            	       $save['state']          = $location['state'];   
                            	       $save['zipcode']        = $location['zipcode'];
                            	       $save['country']        = $location['country'];
                                   } else {
                                       $update['reason'] = 'no location found';
                                   }
                    	       }    
                    	   }
                	   } else {
                    	   $update['reason'] = 'request failed';
                	   }	
            		} else {
                		$update['reason'] = 'no response from server';
            		}
        		} else {
            		$update['reason'] = 'invalid address or coordinates';
        		}
                
        		if (! empty($update)) {       // failed to rgeo/geocode or missing rgeo/geocode components - update the incomplete landmark as processed
            	   
            	   $update['process'] = 1;
            	   $update['processdate'] = Date::locale_to_utc(date('Y-m-d H:i:s'), SERVER_TIMEZONE);
            	   $this->territory_logic->updateIncompleteTerritory($t['territoryupload_id'], $update, 'territoryupload');   	
        		
        		} else if (! empty($save)) {  // rgeo/geocoding successful - create the new landmark and delete the incomplete landmark

                    // unset fields that don't need to be save into the territory table (the 'process' column is an exception - see next line)
                    unset($save['territoryupload_id'], $save['reference'], $save['processdate'], $save['reason'], $save['user_id']);
                    
                    // This 'process' field is needed when validating the address of an incomplete territory for any missing components.
                    // It indicates whether the missing components, if there are any, are from decarta or the user (address with missing components will be valid as long as it's has already been geo/rgeo)
                    $save['process'] = 1;
                    
                    $save['active'] = 1;
                    $save['boundingbox'] = $this->territory_logic->getBoundingBoxValue($save['shape'], array($save['latitude'] . ' ' . $save['longitude']), $save['radius']);

                    $saved_territory = $this->territory_logic->saveIncompleteToTerritory($t['account_id'], $t['user_id'], $t['territoryupload_id'], $save);

                    // if we fail to create a new territory from the incomplete territory, update incomplete territory
                    if ($saved_territory !== true) {
                        $params = array(
                            'reason'        => 'failed to save territory',
                            'process'       => 1,
                            'processdate'   => Date::locale_to_utc(date('Y-m-d H:i:s'), SERVER_TIMEZONE)
                        );
                        
                        // update the reason for failing
                        if (is_array($saved_territory) AND ! empty($saved_territory)) {
                            $params['reason'] = array_pop($saved_territory);
                        }
                        
                        $this->territory_logic->updateIncompleteTerritory($t['territoryupload_id'], $params, 'territoryupload');    
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
