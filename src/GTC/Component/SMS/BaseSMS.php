<?php
namespace GTC\Component\SMS;

/**
 * Description of BaseSMS
 *
 * @author syang
 */
class BaseSMS
{
    //put your code here
    protected $errors = array();
    
    public function setError($error)
    {
        $this->errors[] = $error;
    }
    
    public function hasErrors()
    {
        return !emptry($this->errors);
    }
    
    public function clearErrors()
    {
        $this->errors = array();
    }
    
    public function getErrors($clear = true)
    {
        $errors = $this->errors;
        if($clear) {
            $this->clearErrors();
        }
        
        return $errors;
    }
}
