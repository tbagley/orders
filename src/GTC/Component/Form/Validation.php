<?php

namespace GTC\Component\Form;

use GTC\Component\Form\FieldType;

/**
 * Class Validation
 *
 * This class attempts to make validation a snap
 *
 * This class works with the GTC\Form\FieldType class
 *
 *
 */
class Validation
{
    private $fieldTypes;
    private $fieldErrors;
    private $errors;

    public function __construct()
    {
        $fieldType = new FieldType;

        $this->fieldTypes = $fieldType->getFieldTypes();

        // Field specific error messages
        $this->fieldErrors = array(
            'allowedchars'  => array(
                'special'           => '%s field is unable to accept: "" (%s)',
                'alpha'             => '%s field only allows letters, "_", and "-" (%s)',
                'alphanumeric'      => '%s field only allows letters, numbers, "_", and "-" (%s)',
                'numeric'           => '%s field only allows numbers (%s)',
                'email'             => '%s field only allows valid email characters (%s)',
                'phone'             => '%s field only allows 10 digits, numbers and "-" (%s)',
                'address'           => '%s field only allows letters, numbers, "#", "-", "." (%s)',
                'record_id'         => '%s is invalid (%s)',
                'vin'               => '%s field only allows letters (except I, O, Q) and numbers (%s)',
                'year'              => '%s field only allows numbers from 1000 to 9999 (%s)',
                'date'              => '%s field only allows numbers and "-" (%s)',
                'odometer'          => '%s field only allows numbers from 0 to 999999 (%s)',
                'natural_number'    => '%s field only allows numbers greater than or equal to 0 (%s)'
            ),
            'required'      => '%s field is required (%s)',
            'minlength'     => '%s field requires a minimum length of %s characters (%s)',
            'maxlength'     => '%s field allows a maximum length of %s characters (%s)'
        );
        
        $this->errors = array();
    }

    /**
     *
     */
    public function getFieldTypes()
    {
        return $this->fieldTypes;
    }

    /**
     * Set validation error message
     *  
     * @param array|string $error
     * @return void
     */
    public function setErrorMessage($error)
    {
        if (! is_array($error)) {
            if ($error != '') {
                $this->errors[] = $error;    
            }    
        } else {   
            $this->errors = array_merge($this->errors, $error);
        }
    }

    /**
     * Get validation error message
     *
     * @return array
     */
    public function getErrorMessage()
    {
        if (count($this->errors) > 0) {
            $tmp = $this->errors;
            $this->errors = array();
            return $tmp;
        }
        return false;
    }
    
    /**
     * Clear validation error message
     */
    public function clearError()
    {
        $this->errors = array();
    }
    
    /**
     * Check if there is any validation errors
     *
     * @return bool
     */
    public function hasError()
    {
        if (count($this->errors) > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * Validate user input by field type
     *
     * @param string $type
     * @param string $input
     * @return void
     */
    public function validate($type, $input)
    {
        if (! empty($type) AND isset($this->fieldTypes[$type])) {

            $field = $this->fieldTypes[$type] ;

            if (isset($field['minlength'])) {
                $this->checkMinLength($input, $field['minlength'], $field['label']);
            }
            
            if (isset($field['maxlength'])) {
                $this->checkMaxLength($input, $field['maxlength'], $field['label']);
            }
    
            if (isset($field['allowedchars'])) {
                $this->checkAllowedChars($input, $field['allowedchars'], $field['label'], $type);
            }
        } else {
            $this->setErrorMessage('Invalid field type');    
        }
    }

    /**
     * Validate minimum length of input
     *
     * @param string $input
     * @param numeric $min_length
     * @param string $label
     * @param return void
     */
    private function checkMinLength($input, $min_length, $label)
    {
        if (strlen(trim($input)) < $min_length) {
            $this->setErrorMessage(sprintf($this->fieldErrors['minlength'], $label, $min_length, $input));
        }
    }

    /**
     * Validate maximum length of input
     *
     * @param string $input
     * @param numeric $max_length
     * @param string $label
     * @param return void
     */    
    private function checkMaxLength($input, $max_length, $label)
    {
        if (strlen(trim($input)) > $max_length) {
            $this->setErrorMessage(sprintf($this->fieldErrors['maxlength'], $label, $max_length, $input));    
        }
    }

    /**
     * Validate the allowed characters for a user input
     *
     * @param string $input
     * @param string $type
     * @param string $label
     * @param string $key
     * @return void
     */
    private function checkAllowedChars($input, $type, $label, $key)
    {
        switch ($type) {
            case 'alphanumeric':
                // // accepts only letters, numbers, spaces, underscores, dashes 
                // if (preg_match('/[^A-Z0-9\s\_\-]/i', $input)) {
                //     $this->setErrorMessage(array($key => sprintf($this->fieldErrors['allowedchars']['alphanumeric'], $label, $input)));
                // }
                // break;
            case 'special':
                // accepts these type of characters in the following format: abc123.%+@abc.com
                if (preg_match('/^[A-Z0-9\.\_\%\+\@\&]/i', $input) === 0 && $input!='') {
                    $this->setErrorMessage(array($key => sprintf($this->fieldErrors['allowedchars']['special'], $label, $input)));    
                }
                break;
            case 'alpha':
                // accepts only letters, spaces, underscores, dashes
                if (preg_match('/[^A-Z\s\_\-]/i', $input)) {
                    $this->setErrorMessage(array($key => sprintf($this->fieldErrors['allowedchars']['alpha'], $label, $input)));
                }
                break;
            case 'numeric':
                // accepts only numbers
                if (! is_numeric($input)) {
                    $this->setErrorMessage(array($key => sprintf($this->fieldErrors['allowedchars']['numeric'], $label, $input)));    
                }
                break;
            case 'record_id':
                // accepts all natural numbers except 0
                if (! is_numeric($input) OR $input < 1) {
                    $this->setErrorMessage(array($key => sprintf($this->fieldErrors['allowedchars']['record_id'], $label, $input)));    
                    $this->setErrorMessage(array($key => sprintf($this->fieldErrors['allowedchars']['record_id'], $label, $input)));    
                }
                break;
            case 'email':
                // accepts these type of characters in the following format: abc123.%+@abc.com
                if (preg_match('/^[A-Z0-9\.\_\%\+]+@[A-Z0-9]+\.[A-Z]{2,4}$/i', $input) === 0 && $input!='') {
                    $this->setErrorMessage(array($key => sprintf($this->fieldErrors['allowedchars']['email'], $label, $input)));    
                }
                break;
            case 'phone':
                // accepts numbers, parenthese, dash, space
                if (preg_match('/^[\(|\s]?[0-9]{3}[\)|\-|\s]?[0-9]{3}[\-|\s]?[0-9]{4}$/', $input) === 0 && $input!='') {
                    $this->setErrorMessage(array($key => sprintf($this->fieldErrors['allowedchars']['phone'], $label, $input)));
                }
                break;
            case 'address':
                // accepts all letters, numbers, period, underscore, dash, and pound
                if (preg_match('/[^A-Z0-9\-\s\_\.\#]/i', $input)) {
                    $this->setErrorMessage(array($key => sprintf($this->fieldErrors['allowedchars']['address'], $label, $input)));    
                }
                break;
            case 'vin':
                // accepts all letters (except I, O, Q) and natural numbers
                if (preg_match('/[^A-HJ-NPR-Z0-9]/i', $input)) {
                    $this->setErrorMessage(array($key => sprintf($this->fieldErrors['allowedchars']['vin'], $label, $input)));
                }
                break;
            case 'year':
                // accepts any years from 1000-9999
                if ((strlen(trim($input)) > 0) AND (preg_match('/^[1-9]{1}[0-9]{3}$/', $input) === 0)) {
                    $this->setErrorMessage(array($key => sprintf($this->fieldErrors['allowedchars']['year'], $label, $input)));    
                }
                break;
            case 'date':
                // accepts numbers, foward slash, and dash in the following format: MM-DD-YYYY or MM/DD/YYYY
                if (preg_match('/^[0-9]{2}[\-|\/]{1}[0-9]{2}[\-|\/]{1}[1-9]{1}[0-9]{3}$/', $input) === 0) {
                    $this->setErrorMessage(array($key => sprintf($this->fieldErrors['allowedchars']['date'], $label, $input)));    
                }
                break;
            case 'odometer':
                if (! is_numeric($input) OR $input < 0 OR $input > 999999) {
                    $this->setErrorMessage(array($key => sprintf($this->fieldErrors['allowedchars']['odometer'], $label, $input)));    
                }
                break;
            case 'natural_number':
                if (! is_numeric($input) OR $input < 0) {
                    $this->setErrorMessage(array($key => sprintf($this->fieldErrors['allowedchars']['natural_number'], $label, $input)));
                }
                break;
        }
    }
}