<?php

namespace GTC\Component\Utils;

class Measurement
{
    /**
     * Radius convert from feet to fraction in miles
     *
     * @param int $radius (in feet)
     *
     * @return string
     */
	public function radiusFeetToFractionConverter($radius, $format = 'fraction', $measurement = 'Mile')
	{
        if (isset($radius) AND ! empty($radius)) {
            $radius = sprintf('%01.3f', ($radius * 0.00018939393));     // convert feet to miles
            $converted_radius = 0;
            
            if ($format == 'fraction') {

                if (!is_int($radius)) {
                    $float_value = floatval($radius);
                    $denominator = round(1 / $float_value);
                    
                    $fraction = "";
                    if ($denominator > 1) {
                        $fraction = "1/{$denominator}";
                    }
                    
                    if ($fraction != '') {
                        $converted_radius = $fraction;
                    } else {
                        $converted_radius = substr($radius, 0, -4);
                    }
                } else {
                    $converted_radius = substr($radius, 0, -4);
                }
            } else if ($format == 'decimal') {
                $converted_radius = (float) $radius;
            }
            
            if ($converted_radius > 1) {
                $measurement = $measurement.'s';
            }
            
            if ($converted_radius == 0) {
                return 'n/a';
            } else {
                return $converted_radius.' '.$measurement;
            }
        } else {
            return 'n/a';
        }
	}

    /**
     * Radius convert from fraction to feet
     *
     * @param int $radius (fraction mile string)
     *
     * @return int
     */
	public function radiusMileToFeetConverter($radius)
	{
        //$radius = ((is_numeric($radius) AND ! empty($radius)) ? (trim($radius) * 5280) : 0);
        //$radius = (float) sprintf('%01.3f', ($radius * 0.00018939393)); // convert feet to miles 
        $radius_feet_conversion = array("1/8"       => 660,
                                        "1/4"       => 1320,
                                        "1/2"       => 2640,
                                        ".125"      => 660,
                                        ".25"       => 1320,
                                        ".5"        => 2640,
                                        "0.125"     => 660,
                                        "0.25"      => 1320,
                                        "0.5"       => 2640,
                                        "1"         => 5280,
                                        "3"         => 15840,
                                        "5"         => 26400,
                                        "1/16 Mile"      => 330,
                                        "1/8 Mile"      => 660,
                                        "1/4 Mile"      => 1320,
                                        "1/2 Mile"      => 2640,
                                        ".125 Mile"     => 660,
                                        ".25 Mile"      => 1320,
                                        "5 Mile"        => 26400,
                                        "0.125 Mile"    => 660,
                                        "0.25 Mile"     => 1320,
                                        "0.5 Mile"      => 2640,
                                        "1 Mile"        => 5280,
                                        "3 Miles"       => 15840,
                                        "5 Miles"       => 26400
                                        );

        if (isset($radius) AND ! empty($radius)) {
            if (array_key_exists($radius, $radius_feet_conversion)) {
                return $radius_feet_conversion[$radius];
            }
        }
        
        return 0;
	}

    /**
     * Radius convert from feet to miles
     *
     * @param int $radius (fraction mile string)
     *
     * @return int
     */
	public function radiusFeetToMileConverter($radius)
	{
        if (isset($radius) AND ! empty($radius)) {
            return $radius = (float) sprintf('%01.3f', ($radius * 0.00018939393)); // convert feet to miles
        }

        return 0;
	}

}