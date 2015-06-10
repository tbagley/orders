<?php

namespace GTC\Component\Map;

class Mapquest
{
    /**
     * Clean and return the address from using Mapquest Geocoding service
     *
     * @param array $location
     * @return array $ret
     */
    public function cleanMapquestAddress($location)
    {
        $ret = array(
            'street' => '',
            'city'          => '',
            'state'         => '',
            'zipcode'       => '',
            'country'       => ''
        );
        
        if (! empty($location)) {
            if (! empty($location['street'])) {
                $ret['street'] = $location['street'];    
            }
        
            if (! empty($location['adminArea5'])) {
                $ret['city'] = $location['adminArea5'];    
            }

            if (! empty($location['adminArea3'])) {
                $ret['state'] = $location['adminArea3'];    
            }
            
            if (! empty($location['postalCode'])) {
                $ret['zipcode'] = $location['postalCode'];    
            }
            
            if (! empty($location['adminArea1'])) {
                $ret['country'] = ($location['adminArea1'] == 'US') ? 'USA' : $location['adminArea1'];
            }
        }
        
        return $ret;    
    }
}