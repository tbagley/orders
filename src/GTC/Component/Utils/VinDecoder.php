<?php

namespace GTC\Component\Utils;

class VinDecoder 
{
    /**
    * Private members
    *
    */
    // user's access code to use VINquery's vin decoder web service
    private $key = '';
    
    // VINquery's report type options: 0 - BASIC, 1 - STANDARD, 2 - EXTENDED, 3 - LITE
    // we'll default this to 2 because this report type comes with the trial package
    private $report_type = 2;
    
    // base url for VINquery's vin decoder web service
    private $url = 'http://ws.vinquery.com/restxml.aspx?';
    
    private $errors = array();
    
    /**
    * Constructor
    * NOTE: This class uses VINquery's XML Vin Decoding Web Service (www.vinquery.com)
    *
    */
    public function __construct($key)
    {
        if (! empty($key)) {
            $this->key = $key;
        } else {
            $this->setErrorMessage('Unable to instantiate the VinDecoder class because no key was provided');
        }
    }

    /**
     * Set Report Type
     * NOTE: can only be a number from 0 - 3 (0 - BASIC, 1 - STANDARD, 2 - EXTENDED, 3 - LITE)
     *
     * @param int $report_type
     * @return void
     */
    public function setReportType($report_type)
    {
        if (is_numeric($report_type) AND $report_type >= 0 AND $report_type <= 3) {
            $this->report_type = $report_type;
        } else {
            $this->setErrorMessage('The report type can only be number from 0 - 3');    
        }    
    }
        
    /**
     * Decode a vin using VINquery's vin decoder web service and return make, model, and year of the vehicle (can return more info if needed)
     *
     * @param string $vin
     * @return array
     */
    public function decodeVin($vin)
    {
        $ret = array();
        if (! empty($vin)) {
            if (! empty($this->key)) {
                if (! empty($this->url)) {
            		$url = $this->url . 'accessCode=' . $this->key . '&vin=' . $vin . '&reportType=' . $this->report_type;
            		
            		// collects the xml formatted data from the page and convert them to an associative array
            		$result = simplexml_load_file($url);
                    $json_encode = json_encode($result);
                    $json_decode = json_decode($json_encode, TRUE);

            		if (! empty($json_decode)) {
                        if ($json_decode['VIN']['@attributes']['Status'] == 'SUCCESS') {                // if vin was successfully decoded, return make, model, and year of vehicle
                           $ret['make']    = isset($json_decode['VIN']['Vehicle'][0]) ? $json_decode['VIN']['Vehicle'][0]['@attributes']['Make'] : $json_decode['VIN']['Vehicle']['@attributes']['Make'] ;
                           $ret['model']   = isset($json_decode['VIN']['Vehicle'][0]) ? $json_decode['VIN']['Vehicle'][0]['@attributes']['Model'] : $json_decode['VIN']['Vehicle']['@attributes']['Model'];
                           $ret['year']    = isset($json_decode['VIN']['Vehicle'][0]) ? $json_decode['VIN']['Vehicle'][0]['@attributes']['Model_Year'] : $json_decode['VIN']['Vehicle']['@attributes']['Model_Year']; 
                        } else {                                                                        // else if the decoding process failed, return VINquery's error message
                            if (! empty($json_decode['VIN']['Message']['@attributes']['Value'])) {
                               $error = $json_decode['VIN']['Message']['@attributes']['Value'];
                            } else {
                               $error = 'Failed to decode vin due to an unknown error';
                            }
                            $this->setErrorMessage($error);
                        }	
            		} else {
                		$this->setErrorMessage('No result found');
            		}                            
                } else {
                    $this->setErrorMessage('No url was provided');
                }    
            } else {
                $this->setErrorMessage('No key was provided');
            }
        } else {
            $this->setErrorMessage('No vin was provided to be decoded');    
        }
        return $ret;    
    }

    /**
     * Gets and clears any existing error messages
     *
     * @return array|bool returns array of error messages or false if no errors
     */
    public function getErrorMessage()
    {
        if (count($this->errors) > 0) {
            $tmp = $this->errors;
            $this->errors = array();
            return $tmp;
        }
        return false;
    }

    /**
     * Set/Append an error message to the internal error array
     *
     * @param string $message
     * @return void
     */
    public function setErrorMessage($message)
    {
        if (! is_array($message)) {
            if ($message != '') {
                $this->errors[] = $message;
            }
        } else {
            $this->errors = array_merge($this->errors, $message);
        }
    }

    /**
     * Checks for the existence of error messages
     *
     * @return bool
     */
    public function hasError()
    {
        if (count($this->errors) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Clear all previous errors
     *
     * @return void
     */
    public function clearError()
    {
        $this->errors = array();
    }
}