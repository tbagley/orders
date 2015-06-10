<?php

namespace GTC\Component\Utils;

class Date
{

    /**
     * Converts a locale Date/Time to UTC
     *
     * @access	public
     * @param	datetime
     * @param	string timezone locale
     * @param	string format of date/time returned
     * @return	datetime
     */
	public function locale_to_utc($time = '', $timezone = 'America/Los_Angeles', $format = 'Y-m-d H:i:s')
	{
		try
		{
			$date_obj = new \DateTime($time, new \DateTimeZone($timezone));
			$date_obj->setTimeZone(new \DateTimeZone('UTC'));

			if ($format == 'SECONDS')
			{
				return strtotime($date_obj->format('Y-m-d H:i:s'));
			}
			return $date_obj->format($format);
		}
		catch (Exception $e)
		{
			return FALSE;
		}
	}

    /**
     * Converts a UTC Date/Time to a locale
     *
     * @access	public
     * @param	datetime
     * @param	string timezone locale
     * @param	string format of date/time returned
     * @return	datetime
     */
	public function utc_to_locale($time = '', $timezone = 'America/Los_Angeles', $format = 'Y-m-d H:i:s')
	{
		try
		{
			$date_obj = new \DateTime($time, new \DateTimeZone('UTC'));
			$date_obj->setTimeZone(new \DateTimeZone($timezone));

			if ($format == 'SECONDS')
			{
				return strtotime($date_obj->format('Y-m-d H:i:s'));
			}
			return $date_obj->format($format);
		}
		catch (Exception $e)
		{
			return FALSE;
		}
	}

    /**
     * Converts a locale Date/Time to another locale
     *
     * @access	public
     * @param	datetime
     * @param	string timezone locale
     * @param	string timezone locale
     * @param	string format of date/time returned
     * @return	datetime
     */
	public function locale_to_locale($time = '', $from_timezone = 'America/Los_Angeles', $to_timezone = 'UTC', $format = 'Y-m-d H:i:s')
	{
		try
		{
			$date_obj = new \DateTime($time, new \DateTimeZone($from_timezone));
			$date_obj->setTimeZone(new \DateTimeZone($to_timezone));

			if ($format == 'SECONDS')
			{
				return strtotime($date_obj->format('Y-m-d H:i:s'));
			}
			return $date_obj->format($format);
		}
		catch (Exception $e)
		{
			return FALSE;
		}
	}

    /**
     * @param $datetime YYYY-MM-DD HH:MM:SS
     * @param $format 
     * @param bool $die
     */
    public function date_to_display($datetime, $format = 'Y-m-d H:i:s')
    {
    	if (empty($datetime)) {
    		$datetime = date("Y-m-d H:i:s");
    	}
    	return date($format, strtotime($datetime));	 
    }

    /**
     * DateTime to Timespan
     *
     * Takes 2 date times and returns the difference in a display of time.
     *
     * @access	public
     * @param	Datetime
     * @param	Datetime
     * @param	Bool
     * @return	integer
     */    
	public function datetime_to_timespan($datetime1, $datetime2, $absolute = FALSE)
	{
		if ( ! empty($datetime1) AND ! empty($datetime2)) {
			$ret = '';

			$dt_1 = new \DateTime($datetime1, new \DateTimeZone('UTC'));
			$dt_2 = new \DateTime($datetime2, new \DateTimeZone('UTC'));
			$diff = $dt_1->diff($dt_2, $absolute);

			if ($diff->y > 0) {
				$ret.= $diff->y . ' Year';
				$ret.= ($diff->y > 1) ? 's, ' : ', ';
			}

			if ($diff->m > 0) {
				$ret.= $diff->m . ' Month';
				$ret.= ($diff->m > 1) ? 's, ' : ', ';
			}

			if ($diff->d > 0) {
				$ret.= $diff->d . ' Day';
				$ret.= ($diff->d > 1) ? 's, ' : ', ';
			}

			if ($diff->h > 0) {
				$ret.= $diff->h . ' Hour';
				$ret.= ($diff->h > 1) ? 's, ' : ', ';
			}

			if ($diff->i > 0) {
				$ret.= $diff->i . ' Minute';
				$ret.= ($diff->i > 1) ? 's, ' : ', ';
			}

			if ($diff->s > 0) {
				$ret.= $diff->s . ' Second';
				$ret.= ($diff->s > 1) ? 's, ' : ', ';
			}
			return substr($ret, 0, -2);
		}
		return 0;
	}    

    /**
     * Seconds to Timespan
     *
     * Takes seconds and converts it into a display of time.
     *
     * @access	public
     * @param	Int
     * @param	Bool
     * @param	Bool
     * @param	Bool
     * @param	Bool
     * @param	Bool
     * @return	integer
     */
	public function seconds_to_timespan($seconds, $day = TRUE, $hour = TRUE, $minute = TRUE, $second = TRUE, $abbreviation = FALSE)
	{
		if ( ! empty($seconds) AND is_numeric($seconds)) {
			$ret = '';

            if($abbreviation) {
    			if ($day) {
    				$c_day = intval($seconds/86400);
    				$seconds = $seconds % 86400;
    
    				if ($c_day > 0) {
    					$ret.= $c_day . 'd ';
    				}
    			}
    
    			if ($hour) {
    				$c_hour = intval($seconds/3600);
    				$seconds = $seconds%3600;
    
    				if ($c_hour > 0) {
    					$ret.= $c_hour . 'h ';
    				}
    			}
    
    			if ($minute) {
    				$c_min = intval($seconds/60);
    				$seconds = $seconds%60;
    
    				if ($c_min > 0) {
    					$ret.= $c_min . 'm ';
    				}
    			}
    
    			if ($second) {
    				$c_sec = $seconds;
    
    				if ($c_sec > 0) {
    					$ret.= $c_sec . 's ';
    				}
    			}
    			return substr($ret, 0, -1);
            } else {
    			if ($day) {
    				$c_day = intval($seconds/86400);
    				$seconds = $seconds % 86400;
    
    				if ($c_day > 0) {
    					$ret.= $c_day . ' Day';
    					$ret.= ($c_day > 1) ? 's, ' : ', ';
    				}
    			}
    
    			if ($hour) {
    				$c_hour = intval($seconds/3600);
    				$seconds = $seconds%3600;
    
    				if ($c_hour > 0) {
    					$ret.= $c_hour . ' Hour';
    					$ret.= ($c_hour > 1) ? 's, ' : ', ';
    				}
    			}
    
    			if ($minute) {
    				$c_min = intval($seconds/60);
    				$seconds = $seconds%60;
    
    				if ($c_min > 0) {
    					$ret.= $c_min . ' Minute';
    					$ret.= ($c_min > 1) ? 's, ' : ', ';
    				}
    			}
    
    			if ($second) {
    				$c_sec = $seconds;
    
    				if ($c_sec > 0) {
    					$ret.= $c_sec . ' Second';
    					$ret.= ($c_sec > 1) ? 's, ' : ', ';
    				}
    			}
    			return substr($ret, 0, -2);
			}

		}
		return '';
	}

    /**
     * Time Difference Seconds
     *
     * Takes 2 date times and returns the difference in seconds
     *
     * @access	public
     * @param	Datetime
     * @param	Datetime
     * @return	integer
     */	
	public function time_difference_seconds($datetime1, $datetime2, $absolute = FALSE)
	{
		if ( ! empty($datetime1) AND ! empty($datetime2)) {
			$diff = strtotime($datetime1) - strtotime($datetime2);

			if ($absolute) {
				$diff*= ($diff < 0) ? -1 : 1;
			}
			return $diff;
		}
		return 0;
	}

    /**
     * Time Difference Seconds
     *
     * Takes 2 date times and returns the difference in seconds
     *
     * @access	public
     * @param	Datetime
     * @param	Datetime
     * @return	integer
     */	
	public function convertDurationTimeToSeconds($duration)
	{
        if (! empty($duration)) {
            $duration = explode("-", $duration);
            switch ($duration[1]) {
                case 'min':
                    //no break
                case 'mins':
                        return $seconds = $duration[0] * 60;
                    break;
                case 'hr':
                    //no break
                case 'hrs':
                        return $seconds = $duration[0] * 60 * 60;
                    break;
                case 'day':
                    //no break
                case 'days':
                        return $seconds = $duration[0] * 24 * 60 * 60;
                    break;
            }
        }

        return 0;
	}
}
