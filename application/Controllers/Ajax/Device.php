<?php

namespace Controllers\Ajax;

use GTC\Component\Utils\Date;
use GTC\Component\Utils\Dropdown;
use GTC\Component\Utils\CSV\CSVBuilder;

use Models\Logic\AddressLogic;

use Models\Data\UserData;
use Models\Logic\UserLogic;

use Models\Data\VehicleData;
use Models\Logic\VehicleLogic;

use Symfony\Component\HttpFoundation\Request;



/**
 * Class Device
 *
 */
class Device extends BaseAjax
{    
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->user_logic       = new UserLogic;
        $this->user_data        = new UserData;
        $this->vehicle_data     = new VehicleData;
        $this->vehicle_logic    = new VehicleLogic;
        $this->address_logic    = new AddressLogic;

    }
     
    /**
     * Get the devices by filtered paramaters (called via ajax)
     *
     * POST params: filter_type, vehicle_group_id, unitstatus_id, search_string
     *
     * @return array
     */
    public function getDeviceTransferDataByAccountId()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
        $user_id    = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();

        $vehicle_groups = array();
        $ajax_data['code'] = 0;
        //$ajax_data['data'] = $this->vehicle_logic->getVehiclesByAccountId($account_id);
        $ajax_data['data'] = $this->vehicle_logic->getVehiclesByAccountId($account_id,'','','',$post['vehicle_group_id']);
        $ajax_data['message'] = 'Success';

        $this->ajax_respond($ajax_data);
    }


    /**
     * Get the devices by filtered paramaters (called via ajax)
     *
     * POST params: filter_type, vehicle_group_id, unitstatus_id, search_string
     *
     * @return array
     */
    public function getFilteredDeviceList()
    {
        $ajax_data      = array();
        $post           = $this->request->request->all();
        $account_id     = $this->user_session->getAccountId();
        $user_timezone  = $this->user_session->getUserTimeZone();

        $sEcho = 0;
        if (isset($post['sEcho']) AND $post['sEcho'] != '') {
            $sEcho = $post['sEcho'];
        }

        $output = array(
            "sEcho" => intval($sEcho),
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "data" => array()
        );

        $search_type                    = (isset($post['filter_type']) AND !empty($post['filter_type'])) ? $post['filter_type'] : 'group_filter';
        $params                         = $post;
        $params['vehicle_group_id']     = $post['vehicle_group_id'];
        $params['unitstatus_id']        = $post['unitstatus_id'];
        $params['default_value']        = '-';
        
        $params['user_timezone']        = $user_timezone;
        
        if ($search_type != '') {
            $devices = $this->vehicle_logic->getFilteredDeviceList($account_id, $params);
            if ($devices !== false) {
                
                $output['iTotalRecords']        = (isset($devices['iTotalRecords']) AND ! empty($devices['iTotalRecords'])) ? $devices['iTotalRecords'] : 0;
                $output['iTotalDisplayRecords'] = (isset($devices['iTotalDisplayRecords']) AND ! empty($devices['iTotalDisplayRecords'])) ? $devices['iTotalDisplayRecords'] : 0;
                $output['data']                 = (isset($devices['data']) AND ! empty($devices['data'])) ? $devices['data'] : array();
            }
        }

        echo json_encode( $output );
        exit;
    }

    /**
     * Get the vehicle info by unit_id (called via ajax)
     *
     * unitgroup_columns, unitattribute_columns, customer_columns are all arrays containg string values.
     * The string values represent the column names from their respected table for which you want to retreive the
     * desired data from.
     *
     * ex: $unitattribute_columns = array('unitattribute_id', 'unit_id', 'vin', 'make');
     *
     *	   The unitattribute_columns array should contain column names from the 'unitattribute' table in the
     *	   crossbones database. This example will return you the values from the unitattribute_id, unit_id,
     *	   vin, and make columns for the unit based on the unit_id.
     *
     * POST params: unit_id
     *
     * @return array
     */
    public function getDeviceDataInfo()
    {
        $user_id            = $this->user_session->getUserId();
        $user_timezone      = $this->user_session->getUserTimeZone();
        $post               = $this->request->request->all();
        
        $ajax_data = array();
        $unitgroup_columns = array('unitgroup_id', 'unitgroupname');
        $unitattribute_columns = array('unitattribute_id', 'vin', 'make', 'model', 'year', 'color', 'licenseplatenumber', 'loannumber', 'purchasedate', 'renewaldate', 'lastrenewaldate', 'stocknumber', 'activatedate', 'deactivatedate');
        $customer_columns = array('customer_id', 'firstname', 'lastname', 'streetaddress', 'city', 'state', 'zipcode', 'country', 'homephone', 'cellphone', 'email');
        $unitinstallation_columns = array('unitinstallation_id', 'installer', 'installdate');
        $unitodometer_columns = array('unitodometer_id', 'initialodometer', 'currentodometer');
        $event_id = '';
        $eventdata = array();

    	$start_date = '';
    	$end_date = '';

    	$unit_id = $post['unit_id'];

        if (($unit_info = $this->vehicle_logic->getVehicleInfo($unit_id, $unitgroup_columns, $unitattribute_columns, $customer_columns, $unitinstallation_columns, $unitodometer_columns)) !== false) {
            $ajax_data['code'] = 0;
            $ajax_data['data'] = $unit_info;
            $ajax_data['message'] = 'Successfully retrieve vehicle info';

            $ajax_data['data']['formatted_address']         = $this->address_logic->validateAddress($unit_info['streetaddress'], $unit_info['city'], $unit_info['state'], $unit_info['zipcode'], $unit_info['country']);
            $ajax_data['data']['formatted_cell_phone']      = $this->address_logic->formatPhoneDisplay($unit_info['cellphone']);
            $ajax_data['data']['formatted_home_phone']      = $this->address_logic->formatPhoneDisplay($unit_info['homephone']);
            $ajax_data['data']['formatted_purchasedate']    = (! empty($unit_info['purchasedate']) AND ($unit_info['purchasedate'] != '0000-00-00')) ? Date::utc_to_locale($unit_info['purchasedate'], $user_timezone, 'm/d/Y') : '';
            $ajax_data['data']['formatted_expirationdate']  = (! empty($unit_info['renewaldate']) AND ($unit_info['renewaldate'] != '0000-00-00')) ? Date::utc_to_locale($unit_info['renewaldate'], $user_timezone, 'm/d/Y') : '';
            $ajax_data['data']['formatted_installdate']     = (! empty($unit_info['installdate']) AND ($unit_info['installdate'] != '0000-00-00')) ? Date::utc_to_locale($unit_info['installdate'], $user_timezone, 'm/d/Y') : '';
            $ajax_data['data']['formatted_lastrenewaldate'] = (! empty($unit_info['lastrenewaldate']) AND ($unit_info['lastrenewaldate'] != '0000-00-00')) ? Date::utc_to_locale($unit_info['lastrenewaldate'], $user_timezone, 'm/d/Y') : '';
            $ajax_data['data']['installmileage']            = ! empty($unit_info['initialodometer']) ? $unit_info['initialodometer'] : 0;
            $ajax_data['data']['drivenmileage']             = ! empty($unit_info['currentodometer']) ? $unit_info['currentodometer'] : 0;
            $ajax_data['data']['totalmileage']              = (string) ($ajax_data['data']['installmileage'] + $ajax_data['data']['drivenmileage']);
            $ajax_data['data']['odometer_id']               = $unit_info['unitodometer_id'];
            $ajax_data['data']['stock']                     = $unit_info['stocknumber'];
            $ajax_data['data']['formatted_activatedate']    = (! empty($unit_info['activatedate']) AND ($unit_info['activatedate'] != '0000-00-00')) ? Date::utc_to_locale($unit_info['activatedate'], $user_timezone, 'm/d/Y') : '';
            $ajax_data['data']['formatted_deactivatedate']  = (! empty($unit_info['deactivatedate']) AND ($unit_info['deactivatedate'] != '0000-00-00')) ? Date::utc_to_locale($unit_info['deactivatedate'], $user_timezone, 'm/d/Y') : '';

        } else {
            $ajax_data['code']      = 1;
            $ajax_data['message']   = 'Error';
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Update the vehicle info by unit_id (called via ajax)
     *
     * POST params: unit_id, $status, $vehicle_name, $serial
     *
     * @return array
     */
    public function updateVehicleInfo()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
    	$unit_id    = $post['primary_keys']['vehiclePk'];
        $params     = array();
        $table      = '';

        switch ($post['id']) {
            case 'vehicle-status':
                $params['unitstatus_id'] = $post['value'];
                $table = 'unit';
                break;
            case 'vehicle-name':
                $params['unitname'] = $post['value'];
                $table = 'unit';
                break;
            case 'vehicle-vin':
                $params['vin'] = $post['value'];
                $table = 'unitattribute';
                break;
            case 'vehicle-make':
                $params['make'] = $post['value'];
                $table = 'unitattribute';
                break;
            case 'vehicle-model':
                $params['model'] = $post['value'];
                $table = 'unitattribute';
                break;
            case 'vehicle-year':
                $params['year'] = $post['value'];
                $table = 'unitattribute';
                break;
            case 'vehicle-color':
                $params['color'] = $post['value'];
                $table = 'unitattribute';
                break;
            case 'vehicle-stock':
                $params['stock'] = $post['value'];
                break;
            case 'vehicle-license-plate':
                $params['licenseplatenumber'] = $post['value'];
                $table = 'unitattribute';
                break;
            case 'vehicle-loan-id':
                $params['loannumber'] = $post['value'];
                $table = 'unitattribute';
                break;
            case 'vehicle-install-date':
                $params['user_timezone'] = $this->user_session->getUserTimeZone();
                $params['installdate'] = $post['value'];
                $table = 'unitinstallation';
                break;
            case 'vehicle-installer':
                $params['installer'] = $post['value'];
                $table = 'unitinstallation';
                break;
            case 'vehicle-install-mileage':
                $params['unitodometer_id'] = ! empty($post['primary_keys']['vehicleOdometerId']) ? $post['primary_keys']['vehicleOdometerId'] : 0;
                $params['initialodometer'] = $post['value'];
                $table = 'unitodometer';
            default:
                break;
        }

        if (! empty($params) AND ! empty($unit_id)) {
            $update = $this->vehicle_logic->updateVehicleInfo($unit_id, $params, $table);
            if ($update !== false) {
	            $ajax_data['data'] = $post;
	            // if a unit didn't have a unitodometer record before and one was created for it, 
	            // pass back the unitodometer id to be updated in the vehicle info (needed for inline-editing)
	            if (isset($params['unitodometer_id'])) {
	               if ($params['unitodometer_id'] == 0) {
    	               $ajax_data['data']['unitodometer_id'] = $update;    
    	           }
    	           // strip out any leading zeroes
    	           $ajax_data['data']['value'] = ltrim($post['value'], '0');
	            }
                $ajax_data['code'] = 0;
                $ajax_data['message'] = 'Updated Vehicle Information';
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Failed to Update Vehicle Information';
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Error';
        }

        $this->ajax_respond($ajax_data);
    }


}
