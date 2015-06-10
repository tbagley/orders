<?php

namespace GTC\Component\Utils;

class Arrayhelper
{
    /**
     * Usort Compare
     *
     * Sorts arrays by a numeric value, referenced by its key. (tabs helper)
     *
     * @author Michael Driskell
     * @package Fleet/Helpers/My_array
     *
     * @param $key
     * @return void
     */
	public function usort_compare_desc($key)
	{
		return function ($a, $b) use ($key)
		{
			if (strtolower($a[$key]) == strtolower($b[$key])) {
				return 0;
			}

			return (strtolower($a[$key]) > strtolower($b[$key])) ? -1 : 1;
		};
	}
	
    /**
     * Usort Compare
     *
     * Sorts arrays by a numeric value, referenced by its key. (tabs helper)
     *
     * @author Michael Driskell
     * @package Fleet/Helpers/My_array
     *
     * @param $key
     * @return void
     */
	public function usort_compare_asc($key)
	{
		return function ($a, $b) use ($key)
		{
			if (strtolower($a[$key]) == strtolower($b[$key])) {
				return 0;
			}

			return (strtolower($a[$key]) < strtolower($b[$key])) ? -1 : 1;
		};
	}

}