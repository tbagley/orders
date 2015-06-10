<?php
namespace GTC\Component\SMS;

use Services_Twilio;
use Services_Twilio_RequestValidator;

/**
 * A utility class used for easy interaction with the Twilio SMS API
 *
 * @author syang
 */
class TwilioSMS extends BaseSMS
{
    protected $api;
    
    /**
     * This is the sms inbound web service url used by Twilio
     * 
     * @todo Todd modify this url as needed and update the Twilio account to reflect this
     * @link https://www.twilio.com/user/account/phone-numbers/incoming
     * 
     * @var string $requestURL
     */
    protected $requestURL = 'http://twilio.positionplusgps.com/twilio/smsinbound';
    
    protected $accountSID = 'AC5363cc6b124856092c387e22903f810e';
    protected $authToken = '80344cd69ef04d4db3c551979e630175';
    protected $from = '+17148040596';
    
    /**
     * Testing Credentials
     * @link https://www.twilio.com/docs/api/rest/test-credentials
     */
    protected $test_accountSID = 'AC5348f082095ba1fc18197eb6db0fb290';
    protected $test_authToken = '05768ff47a0ca9b5b32c7b1550141b1d';
    protected $test_from = '+15005550006';
    

    public function __construct()
    {
        //the twilio api library
        $this->api = new Services_Twilio($this->accountSID, $this->authToken);
    }
    
    /**
     * Set the API to testing mode (will not charge account or update production data)
     * 
     * Avoid using the production phone for the "from" phone number
     * 
     * @link https://www.twilio.com/docs/api/rest/test-credentials
     */
    public function setTestMode()
    {
        $this->api = new Services_Twilio($this->test_accountSID, $this->test_authToken);
        $this->from = $this->test_from;
    }
    
    /**
     * Send the specified sms message
     * 
     * @param int $to phone number to send message to
     * @param string $body the sms message
     * @return boolean|object
     */
    public function send($to, $body)
    {
        try {
            return $this->api->account->messages->create(array(
                "From" => $this->from,
                "To" => $to,
                "Body" => $body,
            ));
        } catch (Services_Twilio_RestException $e) {
            $this->setError($e->getMessage());
            return FALSE;
        }
    }
    
    /**
     * Authenticate the web request by validating the twilio signature
     * 
     * @param string $twilio_signature This value must be obtained from the header: "X-Twilio-Signature"
     * @param array $post_data This is the post values sent by Twilio and is needed to validate the request
     * @return boolean Returns true if the web request is valid; false otherwise
     */
    public function authenticateRequest($twilio_signature, $post_data)
    {
        $validator = new Services_Twilio_RequestValidator($this->authToken);

        if ($validator->validate($twilio_signature, $this->requestURL, $post_data)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}
