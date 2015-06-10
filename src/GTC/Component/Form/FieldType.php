<?php

namespace GTC\Component\Form;

/**
 * Class FieldType
 *
 * This class attempts to define a set of common 'field types'
 *
 * This class along with the GTC\Form\Validation class make validation a snap
 *
 *
 */
class FieldType
{
    private $fieldTypes;

    public function __construct()
    {
        $types = array();

        /**
         * legalchars options: alpha, alpha numeric, webfriendly, numeric, email, phone
         *
         */

        // people names
        $types['first_last_name'] = array(
            'minlength'=>2,
            'maxlength'=>16,
            'allowedchars'=>'alpha',
            'label'=>'First and Last Name'
        );

        $types['full_name'] = array('minlength'=>3, 'maxlength'=>32, 'allowedchars'=>'alpha', 'label'=>'First and Last Name');
        $types['phone_number'] = array('minlength'=>10, 'maxlength'=>16, 'allowedchars'=>'phone', 'label'=>'Phone Number');

        // address and parts
        $types['address_full'] = array('minlength'=>3, 'maxlength'=>64, 'allowedchars'=>'address', 'label'=>'Full Address');
        $types['address_street'] = array('minlength'=>3, 'maxlength'=>32, 'allowedchars'=>'alpha', 'label'=>'Street Address');
        $types['address_city'] = array('minlength'=>3, 'maxlength'=>16, 'allowedchars'=>'alpha', 'label'=>'City');
        $types['address_state'] = array('minlength'=>2, 'maxlength'=>16, 'allowedchars'=>'alpha', 'label'=>'State');
        $types['address_zip'] = array('minlength'=>5, 'maxlength'=>5, 'allowedchars'=>'numeric', 'label'=>'Zipcode');
        $types['address_name'] = array('minlength'=>3, 'maxlength'=>16, 'allowedchars'=>'address', 'label'=>'Address Name'); // example: verification address name

        // account stuff
        $types['email'] = array('minlength'=>6, 'maxlength'=>32, 'allowedchars'=>'email', 'label'=>'Email');
        $types['username'] = array('minlength'=>4, 'maxlength'=>16, 'allowedchars'=>'alphanumeric', 'label'=>'Username');
        // 20141029 - $types['password'] =    array('minlength'=>8, 'maxlength'=>32, 'allowedchars'=>'alphanumeric', 'label'=>'Password');
        $types['password'] =    array('minlength'=>4, 'maxlength'=>32, 'allowedchars'=>'alphanumeric', 'label'=>'Password');
        $types['usertype_name'] =	array('minlength'=>3, 'maxlength'=>16, 'allowedchars'=>'alphanumeric', 'label'=>'UserType Name');

        // group related
        $types['group_name'] =	array('minlength'=>3, 'maxlength'=>32, 'allowedchars'=>'special', 'label'=>'Group Name'); // example: contact groups, landmark groups, vehicle groups

        // unit related
        $types['unit_name'] = array('minlength' => 3, 'maxlength' => 32, 'allowedchars'=>'alphanumeric', 'label'=>'Vehicle Name', 'label'=>'Unit Name');
        $types['event_type'] = array('minlength' => 1, 'maxlength' => 16, 'allowedchars'=>'alpha', 'label'=>'Event Type');

        // vehicle related
        $types['vehicle_name'] = array('minlength' => 3, 'maxlength' => 32, 'allowedchars'=>'alphanumeric', 'label'=>'Vehicle Name');
        $types['vehicle_make'] = array('minlength' => null, 'maxlength' => 16, 'allowedchars'=>'alpha', 'label'=>'Vehicle Make');
        $types['vehicle_model'] = array('minlength' => null, 'maxlength' => 16, 'allowedchars'=>'alphanumeric', 'label'=>'Vehicle Model');
        $types['vehicle_color'] = array('minlength' => null, 'maxlength' => 16, 'allowedchars'=>'alpha', 'label'=>'Vehicle Color');
        $types['vehicle_year'] = array('minlength'=>null, 'maxlength'=>4, 'allowedchars'=>'year', 'label'=>'Vehicle Year');

        $types['serial_number'] = array('minlength'=>3, 'maxlength'=>32, 'allowedchars'=>'alphanumeric', 'label'=>'Serial Number');
        $types['vin_number'] = array('minlength'=>null, 'maxlength'=>17, 'allowedchars'=>'vin', 'label'=>'VIN Number');
        $types['license_plate'] = array('minlength'=>null, 'maxlength'=>8, 'allowedchars'=>'alphanumeric', 'label'=>'License Plate');
        $types['loan_id'] =	array('minlength'=>null, 'maxlength'=>16, 'allowedchars'=>'alphanumeric', 'label'=>'Loan ID');
        $types['stock_number'] = array('minlength'=>null, 'maxlength'=>32, 'allowedchars'=>'alphanumeric', 'label'=>'Stock Number');
        $types['odometer'] = array('minlength'=>1, 'maxlength'=>6, 'allowedchars'=>'odometer', 'label'=>'Vehicle Odometer');
        $types['installer_name'] = array('minlength'=>null, 'maxlength'=>32, 'allowedchars'=>'alphanumeric', 'label'=>'Installer Name');
        $types['customer_first_last_name'] = array(
            'minlength'=>null,
            'maxlength'=>16,
            'allowedchars'=>'alpha',
            'label'=>'Customer First and Last Name'
        );
        $types['customer_address_full'] = array('minlength'=>null, 'maxlength'=>64, 'allowedchars'=>'address', 'label'=>'Customer Address');
        $types['customer_address_city'] = array('minlength'=>null, 'maxlength'=>16, 'allowedchars'=>'alpha', 'label'=>'Customer City');
        $types['customer_address_zip'] = array('minlength'=>null, 'maxlength'=>5, 'allowedchars'=>'numeric', 'label'=>'Customer Zipcode');
        $types['customer_phone_number'] = array('minlength'=>null, 'maxlength'=>16, 'allowedchars'=>'phone', 'label'=>'Customer Phone Number');
        $types['customer_email'] = array('minlength'=>null, 'maxlength'=>32, 'allowedchars'=>'email', 'label'=>'Customer Email');

        // alert related
        $types['alert_name'] = array('minlength'=>3, 'maxlength'=>32, 'allowedchars'=>'alphanumeric', 'label'=>'Alert Name');

        // territory related
        $types['landmark_name'] = $types['territory_name'] = array('minlength' => 3, 'maxlength' => 32, 'allowedchars'=>'alphanumeric', 'label'=>'Landmark Name');

        // report related
        $types['report_name'] = array('minlength' => 3, 'maxlength' => 32, 'allowedchars'=>'alphanumeric', 'label'=>'Report Name');  // report and scheduled report name
        
        // entity id (i.e. unit_id, unitgroup_id, account_id, user_id, etc...)
        $types['record_id'] = array('minlength'=>1, 'allowedchars'=>'record_id', 'label'=>'Record ID');
        
        // generic validations
        $types['numeric'] = array('minlength'=>1,'allowedchars'=>'numeric', 'label'=>'Numeric');
        $types['alphanumeric'] = array('minlength'=>1,'allowedchars'=>'alphanumeric', 'label'=>'Alpha Numeric');
        $types['alpha'] = array('minlength'=>1,'allowedchars'=>'alpha', 'label'=>'Alpha');
        $types['natural_number'] = array('minlength'=>1,'allowedchars'=>'natural_number', 'label'=>'Natural Number');
        
        // date related
        $types['year'] = array('minlength'=>4, 'maxlength'=>4, 'allowedchars'=>'year', 'label'=>'Year');
        $types['date'] = array('minlength'=>10, 'maxlegnth'=>10, 'allowedchars'=>'date', 'label'=>'Date');

        $this->fieldTypes = $types;
    }

    /**
     *
     */
    public function getFieldTypes()
    {
        return $this->fieldTypes;
    }

}