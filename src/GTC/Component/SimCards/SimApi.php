<?php
/**
 *
 */

namespace GTC\Component\SimCards;

use GTC\Component\SimCards\Kore;
use GTC\Component\SimCards\Aeris;

class SimApi
{

    public function setup()
    {

    }

    /**
     * @param $provider
     * @param $esn
     * @param $imei
     * @param $simnumber
     * @return array(status,simphone,error)
     */
    public function Activate($provider, $esn, $imei, $simnumber)
    {

        $out = array();
        $out['status'] = "";
        $out['simPhone'] = "";
        $out['error'] = "";

        // GTC FLEET TEST: 89014104212576394606

        if (strtolower($provider) == "kore" ){
            $api = $this->_Kore_API_Setup();

            $info = $api->deviceInformation($simnumber);

            $out['status'] = $info['response']['d']['status'];
            $out['simPhone'] = $info['response']['d']['MSISDNOrMDN'];
            $out['error'] = $api->getErrors();

            if($out['status'] !== null && $out['status'] != "Active" && $out['status'] != "Pending"){//TODO more maybe needed
                $activeinfo = $api->activateDevice($simnumber);
                $out['error'] = $api->getErrors();
            }

        }
        else if(strtolower($provider) == "aeris") {

        }
        else{
            $out['status'] = "Active";
            $out['simPhone'] = "";
            $out['error'] = "";
        }

        return $out;
    }

    public function Deactivate()
    {

    }

    public function Information($provider, $esn, $imei, $simnumber)
    {
        if (strtolower($provider) == "kore" ){
            $api = $this->_Kore_API_Setup();

            $info = $api->deviceInformation($simnumber);

            $out['status'] = $info['response']['d']['status'];
            $out['simPhone'] = $info['response']['d']['MSISDNOrMDN'];
            $out['error'] = $api->getErrors();
        }
        else if(strtolower($provider) == "aeris") {

        }

        return $out;
    }

    private function _Kore_API_Setup()
    {
        $api = new Kore();

        $api->setUsername(KORE_USERNAME);
        $api->setPassword(KORE_PASSWORD);
        $api->setEAP(KORE_EAP);
        $api->setTesting(KORE_TEST);

        return $api;
    }

    private function _Kore_deviceInformation()
    {

    }



}