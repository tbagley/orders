<?php

namespace GTC\Component\Unit;

/**
 * Class Unit
 */
class Unit {

    /**
     * Converts Heading in Degrees to a 1-2 character abbreviation of a Cardinal/Ordinal Direction
     *
     * @param int|float $heading
     * @return string
     */
	public function headingToDirection($heading)
	{
		$directions = array('N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW', 'N');
		return $directions[round($heading/45)];
	}

}
