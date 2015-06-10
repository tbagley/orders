<?php

namespace GTC\Component\Utils;

class TigerGeocoder 
{
    /**
    * Private members
    *
    */
    private $host = '';
    
    private $port = '';
    
    private $dbname = '';
    
    private $username = '';
    
    private $password = '';
    
    private $connection = '';
    
    /**
    * Constructor
    *
    */
    public function __construct()
    {
        $this->host       = '192.168.3.81';
        $this->port       = '5432';
        $this->dbname     = 'tiger2011_db';
        $this->username   = 'gtc';
        $this->password   = 'osm';
    }

    /**
    * Connect to the database via pg_connect
    *
    */    
    public function connectToDB()
    {
        $this->connection = pg_connect("host={$this->host} port={$this->port} dbname={$this->dbname} user={$this->username} password={$this->password}");
        if ($this->connection) {
            return true;    
        }
        return false;    
    }

    /**
    * Perform reverse geocode (only for locations in the United States)
    *
    * @param float lat (latitude)
    * @param float long (longitude)
    *
    * @return array result
    */    
    public function reverseGeocode($lat = '', $long = '')
    {
        $result = array(
            'success' => 0, // 0 means failure and 1 means success (defaults to failure)
            'address_components' => array(),
            'latitude' => $lat,
            'longitude' => $long,
            'formatted_address' => ''
        );

        $error = '';
                    
        if ($lat != '' AND is_numeric($lat) AND $long != '' AND is_numeric($long)) {
            
            $ret = array(
                'address' => '',
                'city'    => '',
                'state'   => '',
                'zip'     => '',
                'country' => ''
            );
/*
            $query = "SELECT tlid, mtfcc, fullname, fromhnl, tohnl, fromhnr, tohnr, placefpr, placefpl, placeL, placeR, zipL, zipR, statefp, countyfp, ST_distance(wkb_geometry, ST_GeomFromText('Point({$long} {$lat})', 32767)) AS dist 
                      FROM TIGERLINE WHERE wkb_geometry && ST_expand(ST_geomFromText('Point({$long} {$lat})', 32767),0.00542544) AND mtfcc LIKE 'S%' ORDER BY dist LIMIT 1";                    
            
            $res = pg_query($this->connection, $query);
            
            while ($row = pg_fetch_assoc($res)) {
    			$to = "";
    			$from = "";
    			$street = trim($row['fullname']);
    			$city = "";
    			$zip = "";
    			$state = $this->state($row['statefp']);
    			$address = "";
    			
    			if (trim($row['tohnl']) != "") {
    				$to = trim($row['tohnl']);
    			} else {
    				$to = trim($row['tohnr']);
    			}
    			
    			if (trim($row['fromhnl']) != "") {
    				$from = trim($row['fromhnl']);
    			} else {
    				$from = trim($row['fromhnr']);
    			}
    			
    			if (trim($row['placel']) != "") {
    				$city = trim($row['placel']);
    			} else {
    				$city = trim($row['placer']);
    			}
    			
    			if (trim($row['zipl']) != "") {
    				$zip = trim($row['zipl']);
    			} else {
    				$zip = trim($row['zipr']);
    			}
    			
    			if ($from == "") {
    				$address = "{$to} {$street}";
    			} else if ($to == "") {
    				$address = "{$from} {$street}";
    			} else {
    				$address = "{$from}-{$to} {$street}";
    			}
    			
    			$address = trim($address);
*/

            // make a curl to the local Tiger server for rgeo
            $url = 'http://192.168.3.81/rgeo/index.php?x=' . $long . '&y=' . $lat;
            
            $c = \curl_init();
            \curl_setopt($c, CURLOPT_URL, $url);
            \curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
            \curl_setopt($c, CURLOPT_FORBID_REUSE, TRUE);
            \curl_setopt($c, CURLOPT_FRESH_CONNECT, TRUE);
            $output = \curl_exec($c);
            $response = \curl_getinfo($c);
            \curl_close($c);
            
            if ( ! empty($response)) {
                if ($response['http_code'] == 200) {
                    if (! empty($output)) {
                    
                        $output = array_map('trim', explode('|', $output));
            			
            			if (! empty($output) AND count($output) > 3) {
                            $ret['address'] = $output[3];
                            $ret['city'] = $output[1];
                            $ret['state'] = $output[0];
                            $ret['zip'] = $output[2];

                			// if any result was returned from the rgeo, it means that the coordinates were in the US (Tiger rgeo only works with coordinates within the US)
                			if ($ret['address'] != '' OR $ret['city'] != '' OR $ret['state'] != ''  OR $ret['zip'] != '') { 
                    			$ret['country'] = 'USA';
                			}
                			
                            $formatted_address = $this->formatAddress($ret['address'], $ret['city'], $ret['state'], $ret['zip'], $ret['country']);
                
                            if ($formatted_address != '') {
                                $result['success'] = 1;
                                $result['formatted_address'] = $formatted_address;
                                $result['address_components'] = $ret;
                            }
            			} else {
                			$error = 'No address was found for this coordinate';
            			}
                    } else {
                        $error = 'No address was found for this coordinate';
                    }
                } else {
                    $error = 'Request to reverse geocode failed';    
                }
            } else {
                $error = 'Request to reverse geocode failed';
            }
        } else {
            $error = 'Invalid latitude and/or longitude';    
        }
        
        //  set error (if there's any)
        $result['error'] = $error;
        
        return $result;    
    }
    
    /**
    * Get state abbreviation from code
    *
    */     
    private function state($state_code)
    {
    	$states = array(
                	"01"=>"AL",
                	"02"=>"AK",
                	"04"=>"AZ",
                	"05"=>"AR",
                	"06"=>"CA",
                	"08"=>"CO",
                	"09"=>"CT",
                	"10"=>"DE",
                	"11"=>"DC",
                	"12"=>"FL",
                	"13"=>"GA",
                	"15"=>"HI",
                	"16"=>"ID",
                	"17"=>"IL",
                	"18"=>"IN",
                	"19"=>"IA",
                	"20"=>"KS",
                	"21"=>"KY",
                	"22"=>"LA",
                	"23"=>"ME",
                	"24"=>"MD",
                	"25"=>"MA",
                	"26"=>"MI",
                	"27"=>"MN",
                	"28"=>"MS",
                	"29"=>"MO",
                	"30"=>"MT",
                	"31"=>"NE",
                	"32"=>"NV",
                	"33"=>"NH",
                	"34"=>"NJ",
                	"35"=>"NM",
                	"36"=>"NY",
                	"37"=>"NC",
                	"38"=>"ND",
                	"39"=>"OH",
                	"40"=>"OK",
                	"41"=>"OR",
                	"42"=>"PA",
                	"44"=>"RI",
                	"45"=>"SC",
                	"46"=>"SD",
                	"47"=>"TN",
                	"48"=>"TX",
                	"49"=>"UT",
                	"50"=>"VT",
                	"51"=>"VA",
                	"53"=>"WA",
                	"54"=>"WV",
                	"55"=>"WI",
                	"56"=>"WY",
                	"60"=>"AS",
                	"66"=>"GU",
                	"68"=>"MH",
                	"69"=>"MP",
                	"72"=>"PR",
                	"74"=>"UM",
                	"78"=>"VI");
    	
    	return $states[$state_code];
    }
    
    /**
    * Format address
    *
    * @return string
    */ 
    public function formatAddress($address = '', $city = '', $state = '', $zip = '', $country = '') 
    {
        $formatted_address = '';
        
        if ($address != '') {
            $formatted_address .= $address . ', ';    
        }
        
        if ($city != '') {
            $formatted_address .= $city . ', ';
        }
        
        if ($state != '') {
            $formatted_address .= $state . ', ';
        }

        if ($zip != '') {
            $formatted_address .= $zip . ', ';
        }
        
        if ($country != '') {
            $formatted_address .= $country;    
        }
        
        if ($formatted_address != '') {
            $formatted_address = trim($formatted_address);  // trim white space
            $formatted_address = trim($formatted_address, ','); // trim beginning and ending commas    
        }       
        
        return $formatted_address;
    }

    /**
    * Close connection to the database
    *
    */    
    public function closeConnection()
    {
        return pg_close($this->connection);
    }       
}