<?php
/**
 * 
 */

namespace GTC\Component\SimCards;

class Kore
{
    private $CI					= FALSE;
    private $config				= array();
    private $transaction_url	= '';
    private $report_url			= '';
    private $account			= 'standard';
    private $kore_error_code	= FALSE;
    private	$kore_error_string	= FALSE;
    private $curl_error_code	= FALSE;
    private	$curl_error_string	= FALSE;

    /**
     * Constructor
     *
     * Loads the configuration settings for Kore
     *
     * @access	public
     */
    function __construct()
    {
        /*
        $this->config['standard_username'] = 'gtcapi';
        $this->config['standard_password'] = 'ydxem6d';
        $this->config['standard_eapcode'] = 'EAP012791045';// old=EAP01279158 standard GTC Gold CWilson

        $this->config['lowdata_username'] = 'gtapi';
        $this->config['lowdata_password'] = 'P54bt9!';
        $this->config['lowdata_eapcode'] = 'EAP01283217';// low data

        $this->config['superlowdata_username'] = 'gtsubapi';
        $this->config['superlowdata_password'] = 'P54bt9!';
        $this->config['superlowdata_eapcode'] = 'EAP01785379';

        */

        $this->config['transaction'] = 'https://prismproapi.koretelematics.com/4/TransactionalAPI.svc/json';
        $this->config['report'] = 'https://prismproapi.koretelematics.com/4/ReportingAPI.svc/json';
        $this->config['test_transaction'] = 'https://prismproapi.sandbox.koretelematics.com/4/TransactionalAPI.svc/json';
        $this->config['test_report'] = 'https://prismproapi.sandbox.koretelematics.com/4/ReportingAPI.svc/json';
        $this->config['test_mode'] = FALSE; // Set this to FALSE for live processing
        $this->config['version'] = '4.0';

        if ($this->config['test_mode'])
        {
            $this->transaction_url = $this->config['test_transaction'];
            $this->report_url = $this->config['test_report'];
        }
        else
        {
            $this->transaction_url = $this->config['transaction'];
            $this->report_url = $this->config['report'];
        }
    }

    public function setUsername($username)
    {
        $this->config['username'] = $username;
    }

    public function setPassword($password)
    {
        $this->config['password'] = $password;
    }

    public function setEAP($eap)
    {
        $this->config['eapcode'] = $eap;
    }

    public function setTesting($testing)
    {
        $this->config['test_mode'] = $testing;
    }

    private function _execute($url, $post_fields)
    {
        $output = NULL;
        $json_data = '';

        if (is_array($post_fields))
        {
            $url_fields = array();
            foreach ($post_fields as $key => $value)
            {
                $url_fields[urlencode($key)] = urlencode($value);
            }
            $json_data = json_encode($url_fields);
        }
        else
        {
            $this->kore_error_code = 1002;
            $this->kore_error_string = 'Invalid parameters';
        }

        if ( ! $this->kore_error_code)
        {
            $ch = curl_init();
            $opts = array(
                CURLOPT_URL				=> $url,
                CURLOPT_USERAGENT		=> "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:26.0) Gecko/20100101 Firefox/26.0",
                CURLOPT_HTTPAUTH		=> CURLAUTH_BASIC,
                CURLOPT_USERPWD			=> $this->config['username'].':'.$this->config['password'],
                CURLOPT_POST			=> TRUE,
                CURLOPT_SSL_VERIFYPEER	=> FALSE,
                CURLOPT_POSTFIELDS		=> $json_data,
                CURLOPT_RETURNTRANSFER	=> TRUE,
                CURLOPT_VERBOSE			=> FALSE,
                CURLOPT_HTTPHEADER		=> array('Content-Type: application/json; charset=utf-8','Content-Length:'.strlen($json_data))
            );

            curl_setopt_array($ch, $opts);

            $output = curl_exec($ch);

            if (curl_errno($ch) > 0)
            {
                $this->curl_error_code = curl_errno($ch);
                $this->curl_error_string = curl_error($ch);
            }
            else if (strpos($output, 'Server Error') === FALSE)
            {
                $output = $this->object_to_array(json_decode($output));

                if (isset($output['errorCode']) && $output['errorCode'] > 0)
                {
                    $this->kore_error_code = $output['errorCode'];
                    $this->kore_error_string = $output['errorMessage'];
                    $output = FALSE;
                }
            }
            else
            {
                $this->kore_error_code = 1003;
                $this->kore_error_string = 'Server Error';
                $output = FALSE;
            }
            curl_close($ch);
        }
        return $output;
    }

    private function object_to_array($object)
    {
        if ( ! is_object($object))
        {
            return $object;
        }

        $array = array();
        foreach (get_object_vars($object) as $key => $value)
        {
            if (is_array($value))
            {
                foreach ($value as $index => $val)
                {
                    $array[$key][$index] = $this->object_to_array($val);
                }
            }
            else
            {
                $array[$key] = $this->object_to_array($value);
            }
        }
        return $array;
    }

    public function activateDevice($device_number, $eap_code = '')
    {
        //set eap code to one of the defauls if not set by user
        if( $eap_code == '' )
        {
            $eap_code = $this->config['eapcode'];
        }

        $url = $this->transaction_url.'/activateDevice';
        $fields = array(
            'deviceNumber' => $device_number,
            'EAPCode' => $eap_code
        );

        $response = $this->_execute($url, $fields);

        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function deactivateDevice($device_number, $flag_scrap = 'false')
    {
        $url = $this->transaction_url.'/deactivateDevice';
        $fields = array(
            'deviceNumber' => $device_number,
            'flagScrap' => $flag_scrap
        );

        $response = $this->_execute($url, $fields);

        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function queryPlans()
    {
        $url = $this->transaction_url.'/queryPlanCodesForNextPeriod';
        $fields = array();

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function modifyDevicePlan($device_number, $plan_code)
    {
        $url = $this->transaction_url.'/modifyDevicePlanForNextPeriod';
        $fields = array(
            'deviceNumber' => $device_number,
            'planCode' => $plan_code
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function modifyDeviceThresholds($device_number,
        $flag_daily_data = FALSE,
        $flag_daily_sms = FALSE,
        $flag_monthly_data = FALSE,
        $flag_monthly_sms = FALSE,
        $daily_data = NULL,
        $daily_sms = NULL,
        $monthly_data = NULL,
        $monthly_sms = NULL)
    {
        $url = $this->transaction_url.'/modifyDeviceThresholds';
        $fields = array(
            'deviceNumber' => $device_number,
            'flagSetDailyDataThreshold' => $flag_daily_data,
            'flagSetDailySMSThreshold' => $flag_daily_sms,
            'flagSetMonthlyDataThreshold' => $flag_monthly_data,
            'flagSetMonthlySMSThreshold' => $flag_monthly_sms,
            'dailyDataThreshold' => $daily_data,
            'dailySMSThreshold' => $daily_sms,
            'monthlyDataThreshold' => $monthly_data,
            'monthlySMSThreshold' => $monthly_sms
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function listServiceTypes()
    {
        $url = $this->transaction_url.'/queryServiceTypeCodes';
        $fields = array();

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function listFeatures($service_type_code)
    {
        $url = $this->transaction_url.'/queryFeatureCodes';
        $fields = array(
            'serviceTypeCode' => $service_type_code
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function modifyDeviceFeatures($device_number, $feature_codes, $voice_dispatch_number)
    {
        $url = $this->transaction_url.'/modifyDeviceFeatures';
        $fields = array(
            'deviceNumber' => $device_number,
            'lstFeatureCodes' => $feature_codes,
            'voiceDispatchNumber' => $voice_dispatch_number
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function statusofProvisionRequest($tracking_number)
    {
        $url = $this->transaction_url.'/queryProvisioningRequestStatus';
        $fields = array(
            'trackingNumber' => $tracking_number
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function deviceInformation($device_number)
    {
        $url = $this->transaction_url.'/queryDevice';
        $fields = array(
            'deviceNumber' => $device_number
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function cdmaDeviceCodes()
    {
        $url = $this->transaction_url.'/queryCDMADeviceCodes';
        $fields = array();

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function uploadESN($esn, $device_code)
    {
        $url = $this->transaction_url.'/uploadESN';
        $fields = array(
            'ESN' => $esn,
            'deviceCode' => $device_code
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function modifyDeviceCustomFields($device_number,
        $custom_field1,
        $custom_field2,
        $custom_field3,
        $custom_field4,
        $custom_field5,
        $custom_field6)
    {
        $url = $this->transaction_url.'/modifyDeviceCustomInfo';
        $fields = array(
            'deviceNumber' => $device_number,
            'customField1' => $custom_field1,
            'customField2' => $custom_field2,
            'customField3' => $custom_field3,
            'customField4' => $custom_field4,
            'customField5' => $custom_field5,
            'customField6' => $custom_field6
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function queryEAPCodes()
    {
        $url = $this->transaction_url.'/queryEAPCodes';
        $fields = array();

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function eapInformation($eap_code)
    {
        $url = $this->transaction_url.'/queryEAPDetails';
        $fields = array(
            'EAPCode' => $eap_code
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function ticketInformation($ticket_number)
    {
        $url = $this->transaction_url.'/queryTicket';
        $fields = array(
            'ticketNumber' => $ticket_number
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function registerDevice($kore_device_number_list, $non_kore_msisdn_list)
    {
        $url = $this->transaction_url.'/registerDevices';
        $fields = array(
            'KOREDeviceNumberList' => $kore_device_number_list,
            'nonKOREMSISDNList' => $non_kore_msisdn_list
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function unregisterDevice($kore_device_number_list, $non_kore_msisdn_list)
    {
        $url = $this->transaction_url.'/unregisterDevices';
        $fields = array(
            'KOREDeviceNumberList' => $kore_device_number_list,
            'nonKOREMSISDNList' => $non_kore_msisdn_list
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function queryRegistrationRequestStatus($registration_id)
    {
        $url = $this->transaction_url.'/queryRegistrationRequestStatus';
        $fields = array(
            'registrationId' => $registration_id
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function queryRegistrationStatus($kore_device_number_list, $non_kore_msisdn_list)
    {
        $url = $this->transaction_url.'/queryRegistrationStatus';
        $fields = array(
            'KOREDeviceNumberList' => $kore_device_number_list,
            'nonKOREMSISDNList' => $non_kore_msisdn_list
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function locateDevices($kore_device_number_list, $non_kore_msisdn_list)
    {
        $url = $this->transaction_url.'/locateDevices';
        $fields = array(
            'KOREDeviceNumberList' => $kore_device_number_list,
            'nonKOREMSISDNList' => $non_kore_msisdn_list
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function queryLocateStatus($locate_id)
    {
        $url = $this->transaction_url.'/queryLocateRequestStatus';
        $fields = array(
            'locateId' => $locate_id
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function queryLocateDetails($locate_id)
    {
        $url = $this->transaction_url.'/queryLocateRequestDetails';
        $fields = array(
            'locateId' => $locate_id
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function queryLocateFullDetails($locate_id, $msisdn, $device_number)
    {
        $url = $this->transaction_url.'/queryFullDetailsForDeviceLocate';
        $fields = array(
            'locateId' => $locate_id,
            'MSISDN' => $msisdn,
            'deviceNumber' => $device_number
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function querySuccessfulLocates($msisdn, $device_number)
    {
        $url = $this->transaction_url.'/querySuccessfulLocatesForDevice';
        $fields = array(
            'MSISDN' => $msisdn,
            'deviceNumber' => $device_number
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function queryNonKoreDevice($msisdn)
    {
        $url = $this->transaction_url.'/queryNonKOREDevice';
        $fields = array(
            'MSISDN' => $msisdn
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function createScheduledLocate($kore_device_number_list,
        $non_kore_msisdn_list,
        $start_date,
        $mins,
        $repeats,
        $to_email,
        $cc_email)
    {
        $url = $this->transaction_url.'/createScheduledLocateTask';
        $fields = array(
            'KOREDeviceNumberList' => $kore_device_number_list,
            'nonKOREMSISDNList' => $non_kore_msisdn_list,
            'startDate' => $start_date,
            'minsBetweenRepeats' => $min,
            'numRepeats' => $repeats,
            'toEmail' => $to_email,
            'ccEmail' => $cc_email

        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function cancelScheduledLocate($task_id)
    {
        $url = $this->transaction_url.'/cancelScheduledLocateTask';
        $fields = array(
            'taskId' => $task_id
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function queryActiveScheduledLocate()
    {
        $url = $this->transaction_url.'/queryActiveScheduledLocateTasks';
        $fields = array();

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function queryScheduledLocate($task_id)
    {
        $url = $this->transaction_url.'/queryScheduledLocateTask';
        $fields = array(
            'taskId' => $task_id
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function queryScheduledLocateDevices($task_id)
    {
        $url = $this->transaction_url.'/queryScheduledLocateTaskDevices';
        $fields = array(
            'taskId' => $task_id
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function queryScheduledLocateHistory($task_id)
    {
        $url = $this->transaction_url.'/queryScheduledLocateTaskHistory';
        $fields = array(
            'taskId' => $task_id
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function queryLocateServiceStatus()
    {
        $url = $this->transaction_url.'/queryLOCATEServiceStatus';
        $fields = array();

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function transationPing()
    {
        $url = $this->transaction_url.'/ping';
        $fields = array();

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    //
    // Reporting
    //

    public function dailyCsvFile($file_name)
    {
        $url = $this->report_url.'/downloadDailyCSV';
        $fields = array(
            'fileName' => $file_name
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function billingSummary($file_name)
    {
        $url = $this->report_url.'/downloadSummaryFile';
        $fields = array(
            'fileName' => $file_name
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function listAvailableReport($type_of_file)
    {
        $url = $this->report_url.'/queryAvailableReportFiles';
        $fields = array(
            'typeOfReport' => $type_of_file
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function locateTransactionHistory($s_type,
        $device_number,
        $msisdn,
        $d_type,
        $user_id,
        $custom1,
        $custom2,
        $custom3,
        $custom4,
        $custom5,
        $custom6,
        $locate_id,
        $loc_status,
        $date_located_min,
        $dateLocated_max,
        $date_completed_min,
        $date_completed_max,
        $latitude_min,
        $latitude_max,
        $longitude_min,
        $longitude_max,
        $country,
        $state_name,
        $county,
        $city,
        $start_index,
        $max_rows)
    {
        $url = $this->report_url.'/queryLocateTransactionHistory';
        $fields = array(
            'sType' => $s_type,
            'deviceNumber' => $device_number,
            'MSISDN' => $msisdn,
            'dType' => $d_type,
            'userId' => $user_id,
            'custom1' => $custom1,
            'custom2' => $custom2,
            'custom3' => $custom3,
            'custom4' => $custom4,
            'custom5' => $custom5,
            'custom6' => $custom6,
            'locateId' => $locate_id,
            'locStatus' => $loc_status,
            'dateLocatedMin' => $date_located_min,
            'dateLocatedMax' => $date_located_max,
            'dateCompletedMin' => $date_completed_min,
            'dateCompletedMax' => $date_completed_max,
            'latitudeMin' => $latitude_min,
            'latitudeMax' => $latitude_max,
            'longitudeMin' => $longitude_min,
            'longitudeMax' => $longitude_max,
            'country' => $country,
            'stateName' => $state_name,
            'county' => $county,
            'city' => $city,
            'startIndex' => $start_index,
            'maxRows' => $max_rows
        );

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function reportPing()
    {
        $url = $this->report_url.'/ping';
        $fields = array();

        $response = $this->_execute($url, $fields);
        if ($response)
        {
            return array('response' => $response, 'request' => array('url' => $url, 'parameters' => $fields));
        }
        return FALSE;
    }

    public function getErrors()
    {
        $tmp = FALSE;
        if ($this->kore_error_code)
        {
            $tmp = array('condition' => $this->kore_error_code, 'message' => $this->kore_error_string);
            $this->kore_error_code = FALSE;
            $this->kore_error_string = FALSE;
        }
        else if ($this->curl_error_code)
        {
            $tmp = array('condition' => $this->curl_error_code, 'message' => $this->curl_error_string);
            $this->curl_error_code = FALSE;
            $this->curl_error_string = FALSE;
        }
        return $tmp;
    }
}
