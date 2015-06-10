<?php

namespace GTC\Component\Map;

/**
 * A utility class for handling geocoding and reverse geocoding calls to Decarta
 *
 * @author Yang Vang
 */ 
class DecartaGeocoder 
{
    /**
    * Private members
    *
    */
    private  $api_key = 'f3d14f93bd32df23a749d016b554912c';
    
    /**
    * Constructor
    *
    */
    public function __construct($api_key = '')
    {
        if (! empty($api_key)) {
            $this->api_key = $api_key;
        }   
    }

    /**
     * Clean and return the address from using Decarta Geocoding service
     *
     * @param array $result
     * @return array $ret
     */
    public function cleanDecartaResult($result)
    {
        $ret = array(
            'street'  => '',
            'city'    => '',
            'state'   => '',
            'zipcode' => '',
            'country' => ''
        );
        
        if (! empty($result) AND ! empty($result['address'])) {
            if (! empty($result['address']['streetName'])) {
                $ret['street'] = $result['address']['streetName'];
            }
            
            if (! empty($result['address']['countryTertiarySubdivision'])) {
                $result['city'] = $result['address']['countryTertiarySubdivision'];
            }
            
            if (! empty($result['address']['countrySubdivision'])) {
                $result['state'] = $result['address']['countrySubdivision'];
            }
            
            if (! empty($result['address']['postalCode'])) {
                $result['zipcode'] = $result['address']['postalCode'];
            }
            
            if (! empty($result['address']['countryCode'])) {
                $result['country'] = $result['address']['countryCode'];
            }
            
            if (! empty($result['address']['streetNumber'])) {
                $ret['street'] = $result['address']['streetNumber'] . ' ' . $ret['street'];
            }
        }
        
        return $ret;    
    }

    /**
     * Validate the coordinates for rgeo
     *
     * @param float $lat
     * @param float $lng
     * @return array $ret
     */
    public function reverseGeocode($lat, $lng) 
    {
        $return = array(
            'success' => 0,
            'result' => array(),
            'error' => array()
        );

        if (empty($lat) OR ! is_numeric($lat) OR empty($lng) OR ! is_numeric($lng)) {
            $return['error'][] = 'Invalid coordinates';
        }
        
        if (empty($return['error'])) {
            return $this->sendGeoRequest('reverseGeocode', $lat.','.$lng);
    	}
    	
    	return $return;            
    }

    /**
     * Validate the address components for geocoding
     *
     * @param array $address_components (an associative array containing the address fields 'streetaddress','city','state','zipcode','country')
     * @return array $ret
     */
    public function geocode($address_components) 
    {
        $return = array(
            'success' => 0,
            'result' => array(),
            'error' => array()
        );
        
        if (empty($address_components)) {
            $return['error'][] = 'No address provided';    
        } else {
            if (! empty($address_components['streetaddress']) AND 
                ! empty($address_components['city']) AND
                ! empty($address_components['state']) AND
                ! empty($address_components['zipcode'])
            ) {
                $formatted_address = rtrim(implode(',',$address_components), ',');  
                
            } else {
                $return['error'][] = 'Missing address components';     
            }
        }
        
        if (empty($return['error'])) {
            return $this->sendGeoRequest('search', $formatted_address);
    	}
    	
    	return $return;                  
    }

    /**
     * Makes cURL request to Decarta for geo/rgeo services
     *
     * @param string $method the name of the api call (i.e. 'search' = geo, 'reverseGeocode' = rgeo)
     * @param string $address the formatted string query for the api call (i.e. '37.345,-130.678' format for rgeo or '123 Fake St,Hemet,CA,92563,USA' format for geo)
     * @return array $ret
     */
    private function sendGeoRequest($method, $address) 
    {
        $return = array(
            'success' => 0,
            'result' => array(
                'latitude' => '',
                'longitude' => '',
                'street' => '',
                'city' => '',
                'state' => '',
                'zipcode' => '',
                'country' => ''
            ),
            'error' => array()
        );

        if (empty($method) OR (! empty($method) AND ! in_array($method, array('search','reverseGeocode')))) {
            $return['error'][] = 'Invalid Decarta API call';
        }
        
        if (empty($address)) {
            $return['error'][] = 'Invalid address/latlng components';
        }
        
        if (empty($return['error'])) {
            $url = 'http://api.decarta.com/v1/' . $this->api_key . '/' . $method . '/' . urlencode($address) . '.JSON';
            
    		$c = \curl_init();
    		\curl_setopt($c, CURLOPT_URL, $url);
    		\curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
    		\curl_setopt($c, CURLOPT_FORBID_REUSE, TRUE);
    		\curl_setopt($c, CURLOPT_FRESH_CONNECT, TRUE);
    		$result = \curl_exec($c);
    		$response = \curl_getinfo($c);
    		\curl_close($c);
    
    		if (! empty($response)) {
        	   if ($response['http_code'] == 200) {
            	   if (! empty($result)) {

            	       $return['success'] = 1;
            	               	       
            	       $result = json_decode($result, TRUE);
            	   
                       if (! empty($result['results'])) {
                           $position                       = $result['results'][0];
                	       $return['result']['latitude']   = $position['position']['lat'];
                	       $return['result']['longitude']  = $position['position']['lon'];       
                       }

                       if (! empty($result['addresses'])) {
                           $result                             = $result['addresses'][0];
            	           $location                           = $this->cleanDecartaResult($result);
                	       $return['result']['streetaddress']  = $location['street'];
                	       $return['result']['city']           = $location['city'];
                	       $return['result']['state']          = $location['state'];   
                	       $return['result']['zipcode']        = $location['zipcode'];
                	       $return['result']['country']        = $location['country'];
                       } 
            	   } else {
                	   $return['error'][] = 'No result found';
            	   }
        	   } else {
                   $return['error'][] = 'Decarta request failed (' . $url . ' : ' . $response['http_code'] . ')';
                   // $return['error'][] = 'Decarta request failed';
        	   }
            }
        }
        
        return $return;
    }  
}