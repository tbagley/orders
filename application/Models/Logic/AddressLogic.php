<?php

namespace Models\Logic;

use Models\Logic\BaseLogic;

class AddressLogic extends BaseLogic
{
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     *	Validates and formats an address with the given address components
     *
     *	@params string $street_address
     *	@params string $city
     *	@params string $state
     *	@params string $zipcode
     *	@params string $country
     *
     *	@return string
     */
    public function validateAddress($street_address, $city, $state, $zipcode, $country)
    {
        $address = '';
        if (! empty($street_address)) {
            $address .= $street_address . ', ';
        }

        if (! empty($city)) {
            $address .= $city . ', ';
        }

        if (! empty($state)) {
            $address .= $state . ', ';
        }

        if (! empty($zipcode)) {
            $address .= $zipcode . ', ';
        }

        if (! empty($country)) {
            $address .= $country;
        }
        
        $address = trim($address, ', ');

        return $address;
    }

    /**
     *	Validates and formats an address with the given address components
     *
     *	@params string $street_address
     *	@params string $city
     *	@params string $state
     *	@params string $zipcode
     *	@params string $country
     *
     *	@return string
     */
    public function createUniqueEventAddress($street_address, $city, $state, $zipcode, $country)
    {
        $address = '';
        if (! empty($street_address)) {
            $address .= $street_address . ',';
        }

        if (! empty($city)) {
            $address .= $city . ',';
        }

        if (! empty($state)) {
            $address .= $state . ',';
        }

        if (! empty($zipcode)) {
            $address .= $zipcode . ',';
        }

        if (! empty($country)) {
            $address .= $country;
        }

        return str_replace(" ", "", $address);
    }

    /**
     *	Validates and formats a phone number for display
     *
     *	@params string $phone
     *
     *	@return string
     */
    public function formatPhoneDisplay($phone)
    {
        $phone = preg_replace("/[^0-9]/", "", $phone);
        if (strlen($phone) == 10) {
            return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
        } else {
            return $phone;
        }
    }

    /**
     *	Validates and formats a phone number for display
     *
     *	@params string $phone
     *
     *	@return string
     */
    public function formatPhoneForSaving($phone)
    {
        $phone = preg_replace("/[^0-9]/", "", $phone);
        if (strlen($phone) == 10) {
            return $phone;
        } else {
            return '';
        }
    }

}