<?php
/**
 * Created by PhpStorm.
 * User: tom
 * Date: 1/23/14
 * Time: 5:06 PM
 */

namespace GTC\Component\Utils\Twig;


class Filters
{

    public function ordinalSuffix($number)
    {
        $suffixes = array('th','st','nd','rd','th','th','th','th','th','th');
        if (($number % 100) >= 11 AND ($number % 100) <= 13) {

            $abbreviation = $number. 'th';
        } else {
            $abbreviation = $number. $suffixes[$number % 10];
        }

        return $abbreviation;

    }

    public function hour($hour, $show_hint = true)
    {
        $meridiem       = 'am';
        $hour_friendly  = $hour;
        $hint           = '';

        if ($hour > 12) {
            $meridiem = 'pm';
            $hour_friendly = $hour_friendly - 12;
        }

        if ($hour == 12) {
            $meridiem = 'pm';
            $hint     = ' (noon)';
        }

        if ($hour == 0 ) {
            $hour_friendly = 12;
            $hint          = ' (midnight)';
        }

        if ($hour < 0 OR $hour > 23) {
            return 'Invalid parameter: must be int >=0 and <=23.';
        }

        if ($show_hint === false) {
            $hint = '';
        }

        return sprintf('%1$02d:00 %2$s%3$s', $hour_friendly, $meridiem, $hint);
    }

    public function ellipsis($string_input = '', $character_limit = 0) {

        $output = $string_input;

        if (strlen($output) > $character_limit AND $character_limit > 0) {
            $output = substr($output, 0, $character_limit-1) . '...';
        }

        return $output;

    }

} 