<?php

namespace GTC\Component\Utils\CSV;

/**
 * CSVReader Class
 *
 *
 * @package		All
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Chris Gressang
 */
class CSVReader
{    
	var $fields				= false;		/** columns names retrieved after parsing */ 
	var $separator			= ',';			/** default separator used to explode each line */
	var $enclosure			= '"';			/** enclosure used to decorate each field */

	var $max_row_size		= 0;			/** maximum row size to be used for decoding (default = 0, no limit) */

	var $errors				= array();		/** */
	var $malformed_data		= array();		/** */

	var $file_path			= '';
	var $has_header			= false;
	var $expected_columns	= 0;
	var $file_resource		= false;
	var $got_header			= false;


	/**
	 * Set separator
	 *
	 * @param    string
	 * @return    void
	 */
	function setSeparator($separator) 
	{
		if ( $separator != '') {
			$this->separator = $separator;
		}
	}

	/**
	 * Set enclosure
	 *
	 * @param    string
	 * @return    void
	 */
	function setEnclosure($enclosure) 
	{
		if ( ! empty($enclosure)) {
			$this->enclosure = $enclosure;
		}
	}

	/**
	 * Set Max Row Size
	 *
	 * @param    string
	 * @return    void
	 */
	function setMaxRowSize($max_row_size)
	{
		if ( ! empty($max_row_size) AND is_numeric($max_row_size)) {
			$this->max_row_size = $max_row_size;
		}
	}

	/**
	 * Set Max Row Size
	 *
	 * @param    string
	 * @return    void
	 */
	function setFile($file_path, $expected_columns, $has_header = true)
	{
		if ( ! empty($file_path) AND strlen($file_path) > 2) {
			$this->file_path = $file_path;
		}

		if ( ! empty($expected_columns) AND is_numeric($expected_columns)) {
			$this->expected_columns = $expected_columns;
		}

		if ($has_header) {
			$this->has_header = $has_header;
		}
	}

	/**
	 * Parse a file containing CSV formatted data.
	 *
	 * @param    string
	 * @param    int
	 * @param    boolean
	 * @return    array
	 */
	function parseFile($file_path, $expected_columns, $has_header = true)
	{
		$content = false;
		$file = fopen($file_path, 'r');

		if ($has_header) {
			$this->fields = fgetcsv($file, $this->max_row_size, $this->separator, $this->enclosure);
			if ( ! is_array($this->fields)) {
				$this->setErrorMessage('err_file_data');
				return false;
			}
		}

		while (($row = fgetcsv($file, $this->max_row_size, $this->separator, $this->enclosure)) != false)
		{
			if ($row[0] != null) {  // skip empty lines				
				if (count($row) == $expected_columns) {
					if ( ! $content) {
						$content = array();
					}

					if ($has_header) {
						$items = array();

						foreach ($this->fields as $id => $field) {
							if ( isset($row[$id]) ) {
								$items[$field] = $row[$id];
							}
						}
						$content[] = $items;
					} else {
						$content[] = $row;
					}
				} else {
					$this->malformed_data[] = $this->enclosure.implode($this->enclosure.$this->separator.$this->enclosure, $row).$this->enclosure;
				}
			}
		}
		fclose($file);
		return $content;
	}

	/**
	 * Parse a file containing CSV formatted data line by line.
	 *
	 * @return    array
	 */
	function parseFileByLine($skip_empty_lines = true)
	{
		$content = false;
		$file = fopen($this->file_path, 'r');
		
		if ( ! $this->got_header AND $this->has_header) {
			$this->fields = fgetcsv($file, $this->max_row_size, $this->separator, $this->enclosure);
			if ( ! is_array($this->fields)) {
				$this->setErrorMessage('err_file_data');
				return false;
			}
		}

		if ( ! $this->file_resource) {
			if ( ! ($this->file_resource = fopen($this->file_path, 'r'))) {
				$this->setErrorMessage('err_file_path');
				return false;
			}
		}

		if (($row = fgetcsv($this->file_resource, $this->max_row_size, $this->separator, $this->enclosure)) != false) {
			if ( ! $skip_empty_lines OR $row[0] != null) {  // skip empty lines
				if ($this->expected_columns === -1) {  //figure out how many rows there are
					$this->expected_columns = count($row);
				}
				
				if (count($row) == $this->expected_columns)	{
					if ( ! $content) {
						$content = array();
					}

					if ($this->has_header) {
						$items = array();

						// I prefer to fill the array with values of defined fields
						foreach ($this->fields as $id => $field) {
							if( isset($row[$id]) ) {
								$items[strtolower(str_replace(' ', '', $field))] = $row[$id];
							}
						}
						return $items;
					} else {
						return $row;
					}
				} else {
					$this->malformed_data[] = $this->enclosure.implode($this->enclosure.$this->separator.$this->enclosure, $row).$this->enclosure;
					return $this->parseFileByLine();
				}
			}
		}
		return false;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Malformed Data
	 *
	 * @return    array
	 */
	function getMalformedData()
	{
		if (count($this->malformed_data) > 0) {
			return $this->malformed_data;
		}
		return false;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Error messages
	 *
	 * @return	array
	 */
	function getErrorMessage()
	{
		if (count($this->errors) > 0) {
			$tmp = $this->errors;
			$this->errors = array();
			return $tmp;
		}
		return false;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Error messages
	 *
	 * @return	array
	 */
	function setErrorMessage($message)
	{
		if ($message != '') {
			$this->errors[] = $message;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Has Error
	 *
	 * @return	array
	 */
	function hasError()
	{
		if (count($this->errors) > 0) {
			return true;
		}
		return false;
	}

	// --------------------------------------------------------------------

	/**
	 * Clear Error
	 *
	 * @return	array
	 */
	function clearError()
	{
		$this->errors = array();
	}
}

/* End of file CSVReader.php */
/* Location: ../src/GTC/Component/Utils/CSV/CSVReader.php */