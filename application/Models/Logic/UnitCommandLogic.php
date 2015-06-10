<?php

namespace Models\Logic;

use Models\Data\UnitCommandData;
use Models\Data\UnitData;
use Models\Data\VehicleData;
use Models\Logic\UnitLogic;
use Models\Logic\VehicleLogic;
use GTC\Component\Utils\Date;
use GTC\Component\Form\Validation;
use GTC\Component\SMS\TwilioSMS;

class UnitCommandLogic extends UnitLogic
{
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        $this->unitcommand_data = new UnitCommandData;
        $this->unit_data = new UnitData;
        $this->unit_logic = new UnitLogic;
        $this->vehicle_data = new VehicleData;
        $this->validator = new Validation;
    }

    public function smsTwilio($params)
    {
        $twilio = $this->unitcommand_data->smsTwilio($params);
        return $twilio ;
    }

    /**
     * Locate unit
     *
     * @params int unit_id
     * @params bool enable
     *
     * @return bool
     */    
    function locateOnDemand($unit_id, $enable) 
    {
        $this->validator->validate('record_id', $unit_id);
        
        // if ($enable !== true AND $enable !== false) {
        //     $this->setErrorMessage('Invalid Parameter');
        // }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }
        
        if (! $this->hasError()) {

            // get the unit's raw buzzer command
            $unit_command_buffer = $this->unitcommand_data->getUnitCommand($unit_id,'Locate');
            $unit_command = $unit_command_buffer[0] ;

            // get unit's sim info
            $unit_sms_info =  $this->unit_data->getUnitSimInfo($unit_id);

            if (empty($unit_sms_info)) {
                 $this->setErrorMessage('Failed to retrieve SIM information for this unit');   
            } else {
            
                // $unit_command = array_pop($unit_command);
                $unit_sms_info = array_pop($unit_sms_info);
                
                // process and send command
                if (! empty($unit_sms_info['provider_id']) AND ! empty($unit_sms_info['msisdn']) AND ! empty($unit_command['command'])) {
                    $params = array(
                        'system_id'             => $unit_sms_info['provider_id'],
                        'unit_id'               => $unit_id,
                        'msisdn'                => $unit_sms_info['msisdn'],
                        'message'               => $unit_command['command'],
                        'messagedate'           => Date::locale_to_utc(date('Y-m-d H:i:s'), SERVER_TIMEZONE),
                        'systemmessagestatus'   => '0'
                        
                    );
                    
                    // send command
                    if($unit_sms_info['twilio']=='Y' || $unit_sms_info['twilio']=='y'){
                        $twiliosms = new TwilioSMS();
                        $twilio_message = $twiliosms->send($unit_sms_info['msisdn'], $unit_command['command']);
                        $params['messagestatus'] = 'Sent';
                    }
// $this->setErrorMessage('$unit_sms_info[twilio]:' . $unit_sms_info['twilio'] . ':$twilio_message:' . $twilio_message);
                    $command_sent = $this->unitcommand_data->logSmsMessage($params);
                    
                    if ($command_sent !== false) {
                        return true;
                    } else {
                        $this->setErrorMessage('Failed to send command due to database issue');
                    }
                    
                } else {
                    $this->setErrorMessage('A sim provider was not found for this unit:' . $unit_sms_info['provider_id'] . ':' . $unit_sms_info['msisdn'] . ':' . $unit_command['command']);
                }
                $this->setErrorMessage('unit_id:' . $unit_id . ':enable:' . $enable);
            }
        } else {
            $this->setErrorMessage('unit_id:' . $unit_id . ':enable:' . $enable);
        }
        
        return false;
    }

    /**
     * Enable/Disable buzzer for the unit
     *
     * @params int unit_id
     * @params bool enable
     *
     * @return bool
     */    
    function toggleReminder($unit_id, $enable) 
    {
        $this->validator->validate('record_id', $unit_id);
        
        if ($enable !== true AND $enable !== false) {
            $this->setErrorMessage('Invalid Parameter');
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }
        
        if (! $this->hasError()) {

            // get the unit's raw buzzer command
            $unit_command = $this->unitcommand_data->getUnitCommand($unit_id, (($enable === true) ? 'Reminder On' : 'Reminder Off'));

            // get unit's sim info
            $unit_sms_info =  $this->unit_data->getUnitSimInfo($unit_id);

            if (empty($unit_command)) {
                $this->setErrorMessage('This unit type does not have a Reminder On/Off command');
            } else if (empty($unit_sms_info)) {
                 $this->setErrorMessage('Failed to retrieve SIM information for this unit');   
            } else {
            
                $unit_command = array_pop($unit_command);
                $unit_sms_info = array_pop($unit_sms_info);
                
                // process and send command
                if (! empty($unit_sms_info['provider_id']) AND ! empty($unit_sms_info['msisdn']) AND ! empty($unit_command['command'])) {
                    $params = array(
                        'system_id'             => $unit_sms_info['provider_id'],
                        'unit_id'               => $unit_id,
                        'msisdn'                => $unit_sms_info['msisdn'],
                        'message'               => $unit_command['command'],
                        'messagedate'           => Date::locale_to_utc(date('Y-m-d H:i:s'), SERVER_TIMEZONE),
                        'systemmessagestatus'   => '0'
                        
                    );
                    
                    // send command
                    if($unit_sms_info['twilio']=='Y' || $unit_sms_info['twilio']=='y'){
                        $twiliosms = new TwilioSMS();
                        $twilio_message = $twiliosms->send($unit_sms_info['msisdn'], $unit_command['command']);
                        $params['messagestatus'] = 'Sent';
                    }
                    $command_sent = $this->unitcommand_data->logSmsMessage($params);
                    
                    if ($command_sent !== false) {
                        return true;
                    } else {
                        $this->setErrorMessage('Failed to send command due to database issue');
                    }
                    
                } else {
                    $this->setErrorMessage('A sim provider was not found for this unit');
                }
            }
        }
        
        return false;
    }

    /**
     * Enable/Disable starter for the unit
     *
     * @params int unit_id
     * @params bool enable
     *
     * @return bool
     */    
    function toggleStarter($unit_id, $enable) 
    {
        $this->validator->validate('record_id', $unit_id);
        
        if ($enable !== true AND $enable !== false) {
            $this->setErrorMessage('Invalid Parameter');
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }
        
        if (! $this->hasError()) {

            // get the unit's raw starter command
            $unit_command = $this->unitcommand_data->getUnitCommand($unit_id, (($enable === true) ? 'Starter Enable' : 'Starter Disable'));

            // get unit's sim info
            $unit_sms_info =  $this->unit_data->getUnitSimInfo($unit_id);

            if (empty($unit_command)) {
                $this->setErrorMessage('This unit type does not have a Starter Enable/Disable command');
            } else if (empty($unit_sms_info)) {
                 $this->setErrorMessage('Failed to retrieve SIM information for this unit');   
            } else {
            
                $unit_command = array_pop($unit_command);
                $unit_sms_info = array_pop($unit_sms_info);
                
                // process and send command
                if (! empty($unit_sms_info['provider_id']) AND ! empty($unit_sms_info['msisdn']) AND ! empty($unit_command['command'])) {
                    $params = array(
                        'system_id'             => $unit_sms_info['provider_id'],
                        'unit_id'               => $unit_id,
                        'msisdn'                => $unit_sms_info['msisdn'],
                        'message'               => $unit_command['command'],
                        'messagedate'           => Date::locale_to_utc(date('Y-m-d H:i:s'), SERVER_TIMEZONE),
                        'systemmessagestatus'   => '0'
                        
                    );
                    
                    // send command
                    if($unit_sms_info['twilio']=='Y' || $unit_sms_info['twilio']=='y'){
                        $twiliosms = new TwilioSMS();
                        $twilio_message = $twiliosms->send($unit_sms_info['msisdn'], $unit_command['command']);
                        $params['messagestatus'] = 'Sent';
                    }
                    $command_sent = $this->unitcommand_data->logSmsMessage($params);
                    
                    if ($command_sent !== false) {
                        return true;
                    } else {
                        $this->setErrorMessage('Failed to send command due to database issue');
                    }
                    
                } else {
                    $this->setErrorMessage('A sim provider was not found for this unit');
                }
            }
        }
        
        return false;
    }

    public function getCommandResponse($unit_id, $event_id)
    {

        if(! empty($unit_id) AND ! empty($event_id)) {

            // $this->validator->validate('record_id', $unit_id);
            $unit_sms_info = $this->unit_data->getUnitSimInfo($unit_id);

            if (empty($unit_sms_info)) {
                $this->setErrorMessage('Failed to retrieve SIM information for this unit');   
            } else {
                $unit_sms_info = array_pop($unit_sms_info);
                $last_message = $this->unitcommand_data->getLastSmsMessage($unit_sms_info['msisdn']);
                if (empty($last_message)) {
                    $this->setErrorMessage('Failed to retrieve last message for this unit');   
                } else {

                    $event = $this->vehicle_data->getEventByIdForUnitSince($unit_id, $unit_sms_info['unit_db'], $last_message[0]['messagedate'], $event_id, $unit_sms_info['msisdn']);

                    // 
                    // Payment Reminder ON/OFF + TWILIO Workaround - Todd Bagley
                    // 
                    switch ($event_id){
                        
                        case      109 :
                        case      110 : $event['event_id'] = 'X'; // No Value
                                        if (stristr($event['message'],',3011,0',true)){
                                            $event['event_id'] = '110'; // OFF
                                        } else if (stristr($event['message'],',3011,1',true)){
                                            $event['event_id'] = '109'; // ON
                                        } else if (stristr($event['message'],',3011,2',true)){
                                            $event['event_id'] = '109'; // ON
                                        }
                                        $event['id'] = $unit_id;
                                        $event['servertime'] = $event['createdate'];
                                        break;
                    
                    }
                    // 
                    // Payment Reminder ON/OFF + TWILIO Workaround - Todd Bagley
                    // 

                    if ($event['event_id'] == $event_id) {
                        return $event;
                        exit();
                    } else if ($event['event_id']) {
                        $this->setErrorMessage('Last Reported Event Id Does Not Match');   
                    } else {
                        $this->setErrorMessage('Event Id(s) Missing for ' . $event_id);   
                    }

                }
            }

        }

        return false;

    }
    
    /**
     * Get error messages (calls the parent method)
     *
     * @return bool|array
     */ 
    public function getErrorMessage()
    {
        return parent::getErrorMessage();
    }
}