<?php

namespace GTC\Component\Utils\CSV;

/**
 * CSVBuilder Class
 *
 *
 * @package		All
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Daniel Demetroulis
 */
class CSVBuilder
{           
	private $_fields			= FALSE;		/** columns names (database name => field name)*/ 
	private $_separator			= ',';			/** default separator used to implode each line */
	private $_formatted_rows	= '';			/** finished csv formatted string, used to echo into file **/
	private $_NEWLINE			= "\n";			/** one of the special characters used to denote a new row **/
	private $_CRETURN			= "\r";			/** one of the special characters used to denote a new row **/
	private $_errors			= array();		/** holds array of errors in the case of failure **/
	private $_closure           = '';           /** one of the special characters that each field will be enclosed in **/

	/**
	 * Set separator
	 *
	 * @param    string
	 * @return    void
	 */

	function setSeparator($separator)
	{
		if ($separator != '') {
			$this->_separator = $separator;
		}
	}
	
	/**
	 * Set closure
	 *
	 * @param    string
	 * @return    void
	 */

	function setClosure($closure)
	{
		if ($closure != '') {
			$this->_closure = $closure;
		}
	}

	/**
	 * Set fields
	 *
	 * @param    string
	 * @return    void
	 */

	function setFields($fields)
	{
		if (is_array($fields) AND ! empty($fields)) {
			$this->_fields = $fields;
		}
	}

	/**
	 * format CSV
	 *
	 * @param    array
	 * @return    object
	 */

	function format($rows = array())
	{
		//add header field
		$header = implode("{$this->_closure}{$this->_separator}{$this->_closure}",$this->_fields);
		$header = $this->_closure . $header . $this->_closure;
		$formatted_rows[] = $header;
		
		//$formatted_rows[] = implode($this->_separator,$this->_fields);
		
		foreach($rows as $row) {
			//add each row of data
			$formatted_rows[] = $this->_cleanRow($row);
		}
		
		//separate each row by line endings
		$this->_formatted_rows = implode("{$this->_CRETURN}{$this->_NEWLINE}",$formatted_rows);
		if ( ! $this->_formatted_rows) {
			$this->setErrorMessage('Could not create csv');
		}
		return $this;
	}
	
	/**
	 * export csv
	 *
	 * @param    string
	 * @return   boolean
	 */
	function export($title)
	{
		if ( ! empty($this->_errors)) {
			return FALSE;
		} else {
			//output csv file for download
			$filename = date("Y-m-d")."_".$title.".csv";		
			header('Content-type: application/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			header("Pragma: no-cache");
			header("Expires: 0");
			echo $this->_formatted_rows;
		}
		return TRUE;
	}
	
	/**
	 * get formatted rows string
	 *
	 * @return   boolean | array
	 */
	function getFormattedRows()
	{
		if ( ! empty($this->_errors)) {
			return FALSE;
		} else {
    		return $this->_formatted_rows;
		}
		return TRUE;
	}	
	
	/**
	 * filter out bad characters
	 *
	 * @param    array
	 * @return   strings
	 */
	function _cleanRow($row)
	{
		//remove the separator and line endings if they are already part of a row, and return imploded string
		$new_row = array();
		$clean_row = '';
		foreach($this->_fields as $key => $field) {
			//$new_row[$key] = str_replace(array($this->_separator,$this->_NEWLINE,$this->_CRETURN),' ',$row[$key]);
			$clean_row = str_replace(array($this->_NEWLINE,$this->_CRETURN),' ',$row[$key]);
			$clean_row = $this->_closure . $clean_row . $this->_closure;
			$new_row[$key] = $clean_row;
		}
		return implode($this->_separator,$new_row);
	}

	/**
	 * Get Error messages
	 *
	 * @return	array
	 */

	function getErrorMessage()
	{
		if (count($this->_errors) > 0) {
			$tmp = $this->_errors;
			$this->_errors = array();
			return $tmp;
		}
		return FALSE;
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
			$this->_errors[] = $message;
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
		if (count($this->_errors) > 0) {
			return TRUE;
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Clear Error
	 *
	 * @return	array
	 */
	function clearError()
	{
		$this->_errors = array();
	}
}

/* End of file CSVBuilder.php */
/* Location: ../src/GTC/Component/Utils/CSV/CSVBuilder.php */