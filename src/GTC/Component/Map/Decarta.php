<?php

namespace GTC\Component\Map;

class Decarta
{
    /**
     * Clean and return the address from using Decarta Geocoding service
     *
     * @param array $result
     * @return array $ret
     */
    public function cleanDecartaResult($result)
    {
        $ret = array(
            'street'  => $result['address']['streetName'],
            'city'    => $result['address']['countryTertiarySubdivision'],
            'state'   => $result['address']['countrySubdivision'],
            'zipcode' => $result['address']['postalCode'],
            'country' => $result['address']['countryCode']
        );
        
        if (! empty($result['address']['streetNumber'])) {
            $ret['street'] = $result['address']['streetNumber'] . ' ' . $ret['street'];
        }
        
        return $ret;    
    }
}