<?php

namespace Models\Logic;

use Models\Logic\BaseLogic;
use Models\Data\OrdersData;
use Models\Data\VehicleData;
use Models\Logic\LandmarkLogic;
use Models\Logic\TerritoryLogic;
use Models\Logic\AddressLogic;
use Models\Logic\UserLogic;

use GTC\Component\Utils\Date;
use GTC\Component\Utils\Arrayhelper;
use GTC\Component\Utils\CSV\CSVBuilder;
use GTC\Component\Unit\Unit;

use Swift\Transport\Validate;

use GTC\Component\Form\Validation;

class OrdersLogic extends BaseLogic
{
    /**
     * Container for error messages
     *
     * @var array
     */
    private $errors = array();

    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        //$this->load->model('data/vehicle_data');
        //$this->load->model('logic/address_logic');

        $this->orders_data      = new OrdersData;
        $this->vehicle_data     = new VehicleData;
        $this->address_logic    = new AddressLogic;
        $this->landmark_logic   = new LandmarkLogic;
        $this->territory_logic  = new TerritoryLogic;
        $this->validator        = new Validation;
    }

    /**
     * @return array
     */
    public function createNewAccounts($params,$account_id)
    {
        return $this->orders_data->createNewAccounts($params,$account_id);
    }

    /**
     * @return array
     */
    public function createNewAccountsNotice($post)
    {
        $emails = $this->orders_data->ordersEmailsAccounts_id($post['params']['accounts_id']);
        if($emails){
            $emails['to'] = $emails['customer_email'] ;
            $emails['to_name'] = $emails['customer_name'] ;
            $emails['cc1'] = $emails['rep_email'] ;
            $emails['cc1_name'] = $emails['rep_name'] ;

            // Create the mail transport configuration
            $transport = \Swift_SmtpTransport::newInstance(EMAIL_HOST, EMAIL_PORT, EMAIL_SECURITY);
            $transport->setUsername(EMAIL_USERNAME);
            $transport->setPassword(EMAIL_PASSWORD);
            
            // Create the message
            $message = \Swift_Message::newInstance();
            $message->setSubject('PositonPlusGPS: New Account for ' . $post['params']['company']);
            $message->setBody('<html><body>A new account has been created.<p><ul>u:' . $post['params']['account']['account_username'] . '<br>p:' . $post['params']['account']['account_password'] . '</ul><p><a href="http://track.positionplusgps.com/login">Click here to access the login screen</a><p>Thank you for choosing PositonPlusGPS.<p>www.positionplusgps.com</body></html>', 'text/html');

            $message->setFrom(array('fulfillment@'.$_SERVER['SERVER_NAME'] => $_SERVER['SERVER_NAME']));
            $message->setTo(array($emails['to'] => $emails['to_name']));
            $message->setCc(array($emails['cc1'] => $emails['cc1_name']));
            $message->setBcc(array('monitor@positionplusgps.com' => 'NEW ACCOUNT'));

            // Send the email
            $mailer = \Swift_Mailer::newInstance($transport);
            $mailer->send($message, $failed_recipients);                                     
        }
    }

    /**
     * @return array
     */
    public function ordersDecrypt($val)
    {
        return $this->decryptCc($val);
    }

    /**
     * @return array
     */
    public function fulfillmentNotice($post)
    {
        $createDate = $this->orders_data->ordersCreateDate($post['params']['orders_id']);
        $emails = $this->orders_data->ordersEmails($post['params']['orders_id']);

$emails['customer_email'] = 'tbagley@positionplusgps.com' ;
$emails['customer_name'] = 'TESTING' ;
$emails['m2m_email'] = 'monitor@positionplusgps.com' ;
$emails['m2m_name'] = 'TESTING' ;

        if($emails['wholesale']){
            $emails['to'] = $emails['rep_email'] ;
            $emails['to_name'] = $emails['rep_name'] ;
        } else {
            $emails['to'] = $emails['customer_email'] ;
            $emails['to_name'] = $emails['customer_name'] ;
            $emails['cc1'] = $emails['rep_email'] ;
            $emails['cc1_name'] = $emails['rep_name'] ;
            $emails['cc2'] = $emails['m2m_email'] ;
            $emails['cc2_name'] = $emails['m2m_name'] ;
        }

        // Create the mail transport configuration
        $transport = \Swift_SmtpTransport::newInstance(EMAIL_HOST, EMAIL_PORT, EMAIL_SECURITY);
        $transport->setUsername(EMAIL_USERNAME);
        $transport->setPassword(EMAIL_PASSWORD);
        
        // Create the message
        $message = \Swift_Message::newInstance();
        $message->setSubject('PositonPlusGPS Order #' . $post['params']['orders_id']);

        switch($post['params']['status_id']) {

            case                          1  : 
            case                         '1' : $message->setBody('<html><body>Order #' . $post['params']['orders_id'] . ' has been approved for processsing.<p><a href="http://orders.positionplusgps.com/orderstatus/' . $createDate . $post['params']['orders_id'] . '">Click here to view Order Details and Current Status</a><p>Thank you for your business.<p>PositonPlusGPS<br>www.positionplusgps.com</body></html>', 'text/html');
                                               break ; 

            case                          4  : 
            case                         '4' : $message->setBody('<html><body>Order #' . $post['params']['orders_id'] . ' has been shipped.<p><a href="http://orders.positionplusgps.com/orderstatus/' . $createDate . $post['params']['orders_id'] . '">Click here to view Order Details and Current Status</a><p>Thank you for your business.<p>PositonPlusGPS<br>www.positionplusgps.com</body></html>', 'text/html');
                                               break ; 

                                     default : $message->setBody('<html><body>Status:' . $post['params']['status_id'] . '</body></html>', 'text/html');

        }
        // $message->setBody('<html><body>Status:' . $post['params']['status_id'] . '</body></html>', 'text/html');

        $message->setFrom(array('fulfillment@'.$_SERVER['SERVER_NAME'] => $_SERVER['SERVER_NAME']));
        $message->setTo(array($emails['to'] => $emails['to_name']));
        if(($emails['cc1'])&&($emails['cc2'])){
            $message->setCc(array($emails['cc1'] => $emails['cc1_name'],$emails['cc2'] => $emails['cc2_name']));
            $message->setBcc(array('monitor@positionplusgps.com' => 'ORDERS','claudial@positionplusgps.com' => 'Claudia Lopez','nicolea@positionplusgps.com' => 'Nicole Apostolakos'));
        } else if($emails['cc1']){
            $message->setCc(array($emails['cc1'] => $emails['cc1_name']));
            $message->setBcc(array('monitor@positionplusgps.com' => 'ORDERS'));
        } else {            
            $message->setBcc(array('monitor@positionplusgps.com' => 'ORDERS'));
        }

        // Send the email
        $mailer = \Swift_Mailer::newInstance($transport);
        $mailer->send($message, $failed_recipients);                                     
    }

    /**
     * @return array
     */
    public function fulfillmentStatus($post)
    {
        return $this->orders_data->fulfillmentStatus($post);
    }

    /**
     * @return array
     */
    public function fulfillmentUser($post)
    {
        return $this->orders_data->fulfillmentUser($post);
    }

    /**
     * Get reports by filtered paramaters (called via ajax)
     *
     * @return array
     */
    public function getReport($params)
    {
        if($params['pageCount']<1){
            $params['pageCount']=1;
        }

        $sqlPlaceHolder = array();

        $params['strpos'] = strtolower ( $params['search'] ) ;
        $params['search'] = str_replace ( "'" , "\'" , $params['search'] ) ;

        $report['message'] = 'pid="' . $params['pid'] . '"';                                             
        $report['code'] = 1; 
        $report['pag'] = $params['pag'];
        $report['pid'] = $params['pid'];
        $report['length'] = $params['length'];
        $report['search'] = $params['search'];
        $report['records'] = 0;
        $report['mobile'] = $params['mobile'];

        $evenOdd = 'report-even-odd ';

        $report['code'] = 0; 

        // $report['code'] = 0;
        // $report['thead'] = '<tr><th class="tiniwidth">'.$params['pid'].'</th></tr>';
        // $report['tbody'] = '<tr><th class="tiniwidth">'.$params['pid'].'</th></tr>';
        // return $report;
        // exit();
        if($params['search']){
            $search = ' AND ( o.orders_id LIKE \'' . $params['search'] . '%\' OR o.orders_id LIKE \'%' . $params['search'] . '%\' OR a.company LIKE \'' . $params['search'] . '%\' OR a.company LIKE \'%' . $params['search'] . '%\' OR a.contact LIKE \'' . $params['search'] . '%\' OR a.contact LIKE \'%' . $params['search'] . '%\' OR a.email LIKE \'' . $params['search'] . '%\' OR a.email LIKE \'%' . $params['search'] . '%\' OR r.name LIKE \'' . $params['search'] . '%\' OR r.name LIKE \'%' . $params['search'] . '%\' OR r.email LIKE \'' . $params['search'] . '%\' OR r.email LIKE \'%' . $params['search'] . '%\' OR m2m.name LIKE \'' . $params['search'] . '%\' OR m2m.name LIKE \'%' . $params['search'] . '%\' OR m2m.e_code LIKE \'' . $params['search'] . '%\' OR m2m.e_code LIKE \'%' . $params['search'] . '%\' OR m2m.email LIKE \'' . $params['search'] . '%\' OR m2m.email LIKE \'%' . $params['search'] . '%\' OR ship.shipping_method LIKE \'' . $params['search'] . '%\' OR ship.shipping_method LIKE \'%' . $params['search'] . '%\' OR p.payment_method LIKE \'' . $params['search'] . '%\' OR p.payment_method LIKE \'%' . $params['search'] . '%\' OR o.createdate LIKE \'' . $params['search'] . '%\' OR o.createdate LIKE \'%' . $params['search'] . '%\' OR o.updated LIKE \'' . $params['search'] . '%\' OR o.updated LIKE \'%' . $params['search'] . '%\' OR o.approvedate LIKE \'' . $params['search'] . '%\' OR o.approvedate LIKE \'%' . $params['search'] . '%\' OR o.invoicedate LIKE \'' . $params['search'] . '%\' OR o.invoicedate LIKE \'%' . $params['search'] . '%\' )' ;
            $report['search'] = $params['search'];
            $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
        }
                                                                                                                                                                                            
        switch ($params['pid']){

            case   'fulfillment-accounting-table' : $report['code'] = 0; 

                                                    $report['message'] = '&nbsp;<p>';
                                                    if($params['search']){
                                                        $search = ' AND ( s.statename LIKE \'' . $params['search'] . '%\' OR s.statename LIKE \'%' . $params['search'] . '%\' OR s.updated LIKE \'' . $params['search'] . '%\' OR s.updated LIKE \'%' . $params['search'] . '%\' OR f.user LIKE \'' . $params['search'] . '%\' OR f.user LIKE \'%' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                    }

                                                    $sql = "SELECT s.*,
                                                            f.user as f_user,
                                                            f.email as f_email
                                                            FROM marketing.states s
                                                            LEFT JOIN marketing.fulfillment f ON f.fulfillment_id = s.fulfillment_id
                                                            WHERE s.statename IS NOT NULL"
                                                            . $search
                                                            . " ORDER BY statename ASC";

                                                    $rows = $this->orders_data->getReport($sql, $sqlPlaceHolder);

                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>State</th><th>Rate</th><th>Updated</th><th>Updated By</th></tr>';
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){

                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }

                                                            if(!($row['company'])){
                                                                $row['company'] = $row['accountname'] ; 
                                                            }

                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['statename'] . '</td>'
                                                                . '<td class="' . $evenOdd . '"><input class="form-control states" id="states-' . $row['states_id'] . '" value="' . number_format( $row['taxrate'] / 100 , 2 , '.' , ',' ) . '">&nbsp;%</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['updated'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['f_user'] . '<br>' . $row['f_email'] . '</td>'
                                                                . '</tr>'; 
                                                        }

                                                    }

                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="5">&nbsp;<p><span class="center text-40 text-grey">Sorry</span><p><span class="center text-24 text-grey">... no records found</span></td></tr>';
                                                    }

                                                    $report['pageCount'] = $params['pageCount'];

                                                    $report['pageTotal'] = $page;

                                                    break;

            case       'fulfillment-credit-table' : $report['code'] = 0; 

                                                    $report['message'] = '&nbsp;<p>';
                                                    if($params['search']){
                                                        $search = ' AND ( a.company LIKE \'' . $params['search'] . '%\' OR a.company LIKE \'%' . $params['search'] . '%\' OR a.updated LIKE \'' . $params['search'] . '%\' OR a.updated LIKE \'%' . $params['search'] . '%\' OR f.user LIKE \'' . $params['search'] . '%\' OR f.user LIKE \'%' . $params['search'] . '%\' OR r.name LIKE \'' . $params['search'] . '%\' OR r.name LIKE \'%' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                    }

                                                    $sql = "SELECT a.*,
                                                            f.user as f_user,
                                                            f.email as f_email,
                                                            r.name as r_name,
                                                            r.email as r_email,
                                                            r.phone as r_phone
                                                            FROM marketing.accounts a
                                                            LEFT JOIN marketing.fulfillment f ON f.fulfillment_id = a.fulfillment_id
                                                            LEFT JOIN marketing.reps r ON r.reps_id = a.reps_id
                                                            WHERE a.company != ''
                                                            AND a.reps_id != 1"
                                                            . $search
                                                            . " ORDER BY a.company ASC";

                                                    $rows = $this->orders_data->getReport($sql, $sqlPlaceHolder);

                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Company</th><th>Contact</th><th>Address</th><th>Rep</th><th>COD/NC</th><th>CHECK</th><th>TERMS</th><th>Updated</th><th>Updated By</th></tr>';
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){

                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }

                                                            if(!($row['company'])){
                                                                $row['company'] = $row['accountname'] ; 
                                                            }

                                                            $checked_check = null ;
                                                            if( $row['credit_check'] != 'f' ) {
                                                                $checked_check = 'checked' ;
                                                            }

                                                            $checked_cod = null ;
                                                            if( $row['credit_cod'] == 't' ) {
                                                                $checked_cod = 'checked' ;
                                                            }

                                                            $checked_terms = null ;
                                                            if( $row['credit_terms'] == 't' ) {
                                                                $checked_terms = 'checked' ;
                                                            }

                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['company'] . '<br>' . $row['phone'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['contact'] . '<br>' . $row['email'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['address_billing'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['r_name'] . '<br>' . $row['r_email'] . '</td>'
                                                                . '<td class="' . $evenOdd . '"><input class="form-control payment" id="payment-credit_cod-' . $row['accounts_id'] . '" type="checkbox" ' . $checked_cod . '></td>'
                                                                . '<td class="' . $evenOdd . '"><input class="form-control payment" id="payment-credit_check-' . $row['accounts_id'] . '" type="checkbox" ' . $checked_check . '></td>'
                                                                . '<td class="' . $evenOdd . '"><input class="form-control payment" id="payment-credit_terms-' . $row['accounts_id'] . '" type="checkbox" ' . $checked_terms . '></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['updated'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['f_user'] . '<br>' . $row['f_email'] . '</td>'
                                                                . '</td>'
                                                                . '</tr>'; 
                                                        }

                                                    }

                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="10"">&nbsp;<p><span class="center text-40 text-grey">Sorry</span><p><span class="center text-24 text-grey">... no records found</span></td></tr>';
                                                    }

                                                    $report['pageCount'] = $params['pageCount'];

                                                    $report['pageTotal'] = $page;

                                                    break;

            case      'fulfillment-pending-table' : $report['code'] = 0; 
                                                    $report['message'] = '&nbsp;<p>';
                                                    $sql = "SELECT o.*,
                                                            a.company as account_name,
                                                            a.contact as account_contact,
                                                            a.email as account_email,
                                                            a.fax as account_fax,
                                                            a.phone as account_phone,
                                                            ac.cost as accessories_cost,
                                                            ac.nickname as accessories_name,
                                                            m2m.e_code as m2m_e_code,
                                                            m2m.email as m2m_email,
                                                            m2m.phone as m2m_phone,
                                                            m2m.name as m2m_name,
                                                            p.payment_method as payment,
                                                            r.email as rep_email,
                                                            r.fax as rep_fax,
                                                            r.name as rep_name,
                                                            r.phone as rep_phone,
                                                            s.plan as plan,
                                                            ship.shipping_method as shipping,
                                                            states.statename as taxes_state_name,
                                                            um.manufacturer as manufacturer,
                                                            uv.version as version,
                                                            v.nickname as product_name
                                                            FROM marketing.orders o
                                                            LEFT JOIN marketing.accounts a ON a.accounts_id = o.accounts_id
                                                            LEFT JOIN marketing.accessories ac ON ac.accessories_id = o.accessories_id
                                                            LEFT JOIN marketing.payment p ON p.payment_id = o.payment_id
                                                            LEFT JOIN marketing.reps r ON r.reps_id = o.reps_id
                                                            LEFT JOIN marketing.shipping ship ON ship.shipping_id = o.shipping_id
                                                            LEFT JOIN marketing.states states ON states.states_id = o.taxes_state
                                                            LEFT JOIN marketing.subscription s ON s.subscription_id = o.subscription_id
                                                            LEFT JOIN marketing.versions v ON v.versions_id = o.versions_id
                                                            LEFT JOIN unitmanagement.unitversion uv ON uv.unitversion_id = o.unitversion_id
                                                            LEFT JOIN unitmanagement.unitmanufacturer um ON um.unitmanufacturer_id = uv.unitmanufacturer_id
                                                            LEFT JOIN backoffice.m2m_reps m2m ON m2m.m2m_repID = o.m2m_repID
                                                            WHERE o.status_id < 1
                                                            AND o.active = ''"
                                                            . $search
                                                            . " ORDER BY o.createdate ASC";

                                                    $rows = $this->orders_data->getReport($sql, $sqlPlaceHolder);

        $report['message'] = $sql . ' - ' . implode(', ', $sqlPlaceHolder);

                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Order</th><th>Value</th><th>Actual</th><th>%</th><th>Quantity</th><th>Customer</th><th>Ecode</th><th>Sales&nbsp;Rep</th><th>Shipping</th><th>Payment&nbsp;Method</th><th>Purchase Order</th><th>Created</th><th>Updated</th><th class="tinywidth">Delete</th></tr>';
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){

                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }

                                                            $row['cc_exp'] = $this->decryptCc($row['cc_exp']); 
                                                            $row['cc_num'] = $this->decryptCc($row['cc_num']); 
                                                            $row['cc_ver'] = $this->decryptCc($row['cc_ver']);
                                                            $cc = null ;
                                                            if ( ( $row['cc_num'] ) && ( $row['payment_id'] == 2 ) ) {
                                                                $cc = ' title=" ' . $row['cc_num'] . ', CCV=' . $row['cc_ver'] . ' EXP=' . $row['cc_exp'] . '"' ;
                                                            }

                                                            $actual = $this->zActual($row);

                                                            $percent_of_target = number_format( $actual / $row['grand_total'] * 100 , 2 , '.' , ',' ) ;

                                                            if($percent_of_target==100){
                                                                $percent_of_target=100;
                                                                $onTarget = null ;
                                                            } else if($percent_of_target>0){
                                                                $onTarget = ' text-green' ;
                                                            } else {
                                                                $onTarget = ' text-red' ;
                                                            }

                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '"><a class="status details" href="javascript:void(0);" id="approve_' . $row['orders_id'] . '">' . $row['orders_id'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">$' . number_format( $row['grand_total'] / 100 , 2 , '.' , ',' ) . '</td>'
                                                                . '<td class="' . $evenOdd . $onTarget . '">$' . number_format( $actual / 100 , 2 , '.' , ',' ) . '</td>'
                                                                . '<td class="' . $evenOdd . $onTarget . '">' . $percent_of_target . '%</td>'
                                                                . '<td class="' . $evenOdd . '">' . number_format( $row['quantity'] , 0 , '.' , ',' ) . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['account_name'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['e_code'] . '</td>'
                                                                . '<td class="' . $evenOdd . '" title="' . $row['rep_email'] . '">' . $row['rep_name'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['shipping'] . '</td>'
                                                                . '<td class="text-red ' . $evenOdd . '"' . $cc . '>' . $row['payment'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['po'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['createdate'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['updated'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' 
                                                                . '&nbsp;&nbsp;<a class="wizard-editable text-grey-8 order-delete" id="delete-' . $row['orders_id'] . '" href="javascript:void(0);"><img src="\assets\media\icons\delete.gif" style="height:12px;width:12px;" alt="X" title="Delete record?"></a>&nbsp;'
                                                                . '</td>'
                                                                . '</tr><tr>' 
                                                                . '<td class="' . $evenOdd . '" colspan="21">'
                                                                . $this->zOrder($row,'approve','APPROVE')
                                                                . '</td>'
                                                                . '</tr>'; 
                                                        }

                                                    }

                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="21">&nbsp;<p><span class="center text-24 text-green">Yes, this queue is empty!</span><p><span class="center text-24 text-grey">... all orders have been processed</span></td></tr>';
                                                    }

                                                    $report['pageCount'] = $params['pageCount'];

                                                    $report['pageTotal'] = $page;

                                                    break;

            case     'fulfillment-approved-table' : $report['code'] = 0; 
                                                    $report['message'] = '&nbsp;<p>';
                                                    $sql = "SELECT o.*,
                                                            a.company as account_name,
                                                            a.contact as account_contact,
                                                            a.email as account_email,
                                                            a.fax as account_fax,
                                                            a.phone as account_phone,
                                                            ac.cost as accessories_cost,
                                                            ac.nickname as accessories_name,
                                                            m2m.e_code as m2m_e_code,
                                                            m2m.email as m2m_email,
                                                            m2m.phone as m2m_phone,
                                                            m2m.name as m2m_name,
                                                            p.payment_method as payment,
                                                            r.email as rep_email,
                                                            r.fax as rep_fax,
                                                            r.name as rep_name,
                                                            r.phone as rep_phone,
                                                            s.plan as plan,
                                                            ship.shipping_method as shipping,
                                                            states.statename as taxes_state_name,
                                                            um.manufacturer as manufacturer,
                                                            uv.version as version,
                                                            v.nickname as product_name
                                                            FROM marketing.orders o
                                                            LEFT JOIN marketing.accounts a ON a.accounts_id = o.accounts_id
                                                            LEFT JOIN marketing.accessories ac ON ac.accessories_id = o.accessories_id
                                                            LEFT JOIN marketing.payment p ON p.payment_id = o.payment_id
                                                            LEFT JOIN marketing.reps r ON r.reps_id = o.reps_id
                                                            LEFT JOIN marketing.shipping ship ON ship.shipping_id = o.shipping_id
                                                            LEFT JOIN marketing.states states ON states.states_id = o.taxes_state
                                                            LEFT JOIN marketing.subscription s ON s.subscription_id = o.subscription_id
                                                            LEFT JOIN marketing.versions v ON v.versions_id = o.versions_id
                                                            LEFT JOIN unitmanagement.unitversion uv ON uv.unitversion_id = o.unitversion_id
                                                            LEFT JOIN unitmanagement.unitmanufacturer um ON um.unitmanufacturer_id = uv.unitmanufacturer_id
                                                            LEFT JOIN backoffice.m2m_reps m2m ON m2m.m2m_repID = o.m2m_repID
                                                            WHERE o.status_id = 1"
                                                            . $search
                                                            . " ORDER BY o.updated DESC";

                                                    $rows = $this->orders_data->getReport($sql, $sqlPlaceHolder);

        $report['message'] = $sql . ' - ' . implode(', ', $sqlPlaceHolder);

                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Order</th><th>Customer</th><th>Quantity</th><th>Inventory</th><th>Created</th><th>Approved</th><th>Updated</th></tr>';
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){

                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }

                                                            $row['cc_exp'] = null; 
                                                            $row['cc_num'] = null; 
                                                            $row['cc_ver'] = null;
                                                            // $row['cc_exp'] = $this->decryptCc($row['cc_exp']); 
                                                            // $row['cc_num'] = $this->decryptCc($row['cc_num']); 
                                                            // $row['cc_ver'] = $this->decryptCc($row['cc_ver']);
                                                            // $cc = null ;
                                                            // if ( ( $row['cc_num'] ) && ( $row['payment_id'] == 2 ) ) {
                                                            //     $cc = ' title=" ' . $row['cc_num'] . ', CCV=' . $row['cc_ver'] . ' EXP=' . $row['cc_exp'] . '"' ;
                                                            // }

                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '"><a class="status details" href="javascript:void(0);" id="inventory_' . $row['orders_id'] . '">' . $row['orders_id'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['account_name'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . number_format( $row['quantity'] , 0 , '.' , ',' ) . '</td>'
                                                                . '<td class="' . $evenOdd . '"><textarea id="inventory-' . $row['orders_id'] . '" rows="2" placeholder=" Serial & Box Numbers..."></textarea></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['createdate'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['approvedate'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['updated'] . '</td>'
                                                                . '</tr><tr>' 
                                                                . '<td class="' . $evenOdd . '" colspan="20">'
                                                                . $this->zOrder($row,'inventory','INVENTORY')
                                                                . '</td>'
                                                                . '</tr>'; 
                                                        }

                                                    }

                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="9">&nbsp;<p><span class="center text-24 text-green">Yes, this queue is empty!</span><p><span class="center text-24 text-grey">... all orders have been processed</span></td></tr>';
                                                    }

                                                    $report['pageCount'] = $params['pageCount'];

                                                    $report['pageTotal'] = $page;

                                                    break;

            case  'fulfillment-inventoried-table' : $report['code'] = 0; 
                                                    $report['message'] = '&nbsp;<p>';
                                                    $sql = "SELECT o.*,
                                                            a.company as account_name,
                                                            a.contact as account_contact,
                                                            a.email as account_email,
                                                            a.fax as account_fax,
                                                            a.phone as account_phone,
                                                            ac.cost as accessories_cost,
                                                            ac.nickname as accessories_name,
                                                            m2m.e_code as m2m_e_code,
                                                            m2m.email as m2m_email,
                                                            m2m.phone as m2m_phone,
                                                            m2m.name as m2m_name,
                                                            p.payment_method as payment,
                                                            r.email as rep_email,
                                                            r.fax as rep_fax,
                                                            r.name as rep_name,
                                                            r.phone as rep_phone,
                                                            s.plan as plan,
                                                            ship.shipping_method as shipping,
                                                            states.statename as taxes_state_name,
                                                            um.manufacturer as manufacturer,
                                                            uv.version as version,
                                                            v.nickname as product_name
                                                            FROM marketing.orders o
                                                            LEFT JOIN marketing.accounts a ON a.accounts_id = o.accounts_id
                                                            LEFT JOIN marketing.accessories ac ON ac.accessories_id = o.accessories_id
                                                            LEFT JOIN marketing.payment p ON p.payment_id = o.payment_id
                                                            LEFT JOIN marketing.reps r ON r.reps_id = o.reps_id
                                                            LEFT JOIN marketing.shipping ship ON ship.shipping_id = o.shipping_id
                                                            LEFT JOIN marketing.states states ON states.states_id = o.taxes_state
                                                            LEFT JOIN marketing.subscription s ON s.subscription_id = o.subscription_id
                                                            LEFT JOIN marketing.versions v ON v.versions_id = o.versions_id
                                                            LEFT JOIN unitmanagement.unitversion uv ON uv.unitversion_id = o.unitversion_id
                                                            LEFT JOIN unitmanagement.unitmanufacturer um ON um.unitmanufacturer_id = uv.unitmanufacturer_id
                                                            LEFT JOIN backoffice.m2m_reps m2m ON m2m.m2m_repID = o.m2m_repID
                                                            WHERE o.status_id = 2"
                                                            . $search
                                                            . " ORDER BY o.updated DESC";

                                                    $rows = $this->orders_data->getReport($sql, $sqlPlaceHolder);

        $report['message'] = $sql . ' - ' . implode(', ', $sqlPlaceHolder);

                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Order</th><th>Customer</th><th>Shipping Method</th><th>Tracking Code</th><th>Created</th><th>Approved</th><th>Updated</th></tr>';
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){

                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }

                                                            $row['cc_exp'] = null; 
                                                            $row['cc_num'] = null; 
                                                            $row['cc_ver'] = null;
                                                            // $row['cc_exp'] = $this->decryptCc($row['cc_exp']); 
                                                            // $row['cc_num'] = $this->decryptCc($row['cc_num']); 
                                                            // $row['cc_ver'] = $this->decryptCc($row['cc_ver']);
                                                            // $cc = null ;
                                                            // if ( ( $row['cc_num'] ) && ( $row['payment_id'] == 2 ) ) {
                                                            //     $cc = ' title=" ' . $row['cc_num'] . ', CCV=' . $row['cc_ver'] . ' EXP=' . $row['cc_exp'] . '"' ;
                                                            // }

                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '"><a class="status details" href="javascript:void(0);" id="label_' . $row['orders_id'] . '">' . $row['orders_id'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['account_name'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['shipping'] . '</td>'
                                                                . '<td class="' . $evenOdd . '"><input class="form-control" id="shipping_track-' . $row['orders_id'] . '" placeholder=" Shipment Tracking Code..."></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['createdate'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['approvedate'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['updated'] . '</td>'
                                                                . '</tr><tr>' 
                                                                . '<td class="' . $evenOdd . '" colspan="20">'
                                                                . $this->zOrder($row,'label','LABEL GENERATED FOR')
                                                                . '</td>'
                                                                . '</tr>'; 
                                                        }

                                                    }

                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="9">&nbsp;<p><span class="center text-24 text-green">Yes, this queue is empty!</span><p><span class="center text-24 text-grey">... all orders have been processed</span></td></tr>';
                                                    }

                                                    $report['pageCount'] = $params['pageCount'];

                                                    $report['pageTotal'] = $page;

                                                    break;

            case      'fulfillment-labeled-table' : $report['code'] = 0; 
                                                    $report['message'] = '&nbsp;<p>';
                                                    $sql = "SELECT o.*,
                                                            a.company as account_name,
                                                            a.contact as account_contact,
                                                            a.email as account_email,
                                                            a.fax as account_fax,
                                                            a.phone as account_phone,
                                                            ac.cost as accessories_cost,
                                                            ac.nickname as accessories_name,
                                                            m2m.e_code as m2m_e_code,
                                                            m2m.email as m2m_email,
                                                            m2m.phone as m2m_phone,
                                                            m2m.name as m2m_name,
                                                            p.payment_method as payment,
                                                            r.email as rep_email,
                                                            r.fax as rep_fax,
                                                            r.name as rep_name,
                                                            r.phone as rep_phone,
                                                            s.plan as plan,
                                                            ship.shipping_method as shipping,
                                                            states.statename as taxes_state_name,
                                                            um.manufacturer as manufacturer,
                                                            uv.version as version,
                                                            v.nickname as product_name
                                                            FROM marketing.orders o
                                                            LEFT JOIN marketing.accounts a ON a.accounts_id = o.accounts_id
                                                            LEFT JOIN marketing.accessories ac ON ac.accessories_id = o.accessories_id
                                                            LEFT JOIN marketing.payment p ON p.payment_id = o.payment_id
                                                            LEFT JOIN marketing.reps r ON r.reps_id = o.reps_id
                                                            LEFT JOIN marketing.shipping ship ON ship.shipping_id = o.shipping_id
                                                            LEFT JOIN marketing.states states ON states.states_id = o.taxes_state
                                                            LEFT JOIN marketing.subscription s ON s.subscription_id = o.subscription_id
                                                            LEFT JOIN marketing.versions v ON v.versions_id = o.versions_id
                                                            LEFT JOIN unitmanagement.unitversion uv ON uv.unitversion_id = o.unitversion_id
                                                            LEFT JOIN unitmanagement.unitmanufacturer um ON um.unitmanufacturer_id = uv.unitmanufacturer_id
                                                            LEFT JOIN backoffice.m2m_reps m2m ON m2m.m2m_repID = o.m2m_repID
                                                            WHERE o.status_id = 3"
                                                            . $search
                                                            . " ORDER BY o.updated DESC";

                                                    $rows = $this->orders_data->getReport($sql, $sqlPlaceHolder);

        $report['message'] = $sql . ' - ' . implode(', ', $sqlPlaceHolder);

                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Order</th><th>Customer</th><th>Shipping Method</th><th>Tracking Number</th><th>Created</th><th>Approved</th><th>Updated</th></tr>';
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){

                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }

                                                            $row['cc_exp'] = null; 
                                                            $row['cc_num'] = null; 
                                                            $row['cc_ver'] = null;
                                                            // $row['cc_exp'] = $this->decryptCc($row['cc_exp']); 
                                                            // $row['cc_num'] = $this->decryptCc($row['cc_num']); 
                                                            // $row['cc_ver'] = $this->decryptCc($row['cc_ver']);
                                                            // $cc = null ;
                                                            // if ( ( $row['cc_num'] ) && ( $row['payment_id'] == 2 ) ) {
                                                            //     $cc = ' title=" ' . $row['cc_num'] . ', CCV=' . $row['cc_ver'] . ' EXP=' . $row['cc_exp'] . '"' ;
                                                            // }

                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '"><a class="status details" href="javascript:void(0);" id="shipped_' . $row['orders_id'] . '">' . $row['orders_id'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['account_name'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['shipping'] . '</td>'
                                                                . '<td class="' . $evenOdd . '"><a href="https://www.fedex.com/apps/fedextrack/?tracknumbers=' . $row['shipping_track'] . '&cntry_code=us" target="_fedex">' . $row['shipping_track'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['createdate'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['approvedate'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['updated'] . '</td>'
                                                                . '</tr><tr>' 
                                                                . '<td class="' . $evenOdd . '" colspan="20">'
                                                                . $this->zOrder($row,'shipped','CONFIRM SHIPMENT FOR')
                                                                . '</td>'
                                                                . '</tr>'; 
                                                        }

                                                    }

                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="9">&nbsp;<p><span class="center text-24 text-green">Yes, this queue is empty!</span><p><span class="center text-24 text-grey">... all orders have been processed</span></td></tr>';
                                                    }

                                                    $report['pageCount'] = $params['pageCount'];

                                                    $report['pageTotal'] = $page;

                                                    break;

            case      'fulfillment-shipped-table' : $report['code'] = 0; 
                                                    $report['message'] = '&nbsp;<p>';
                                                    $sql = "SELECT o.*,
                                                            a.company as account_name,
                                                            a.contact as account_contact,
                                                            a.email as account_email,
                                                            a.fax as account_fax,
                                                            a.phone as account_phone,
                                                            ac.cost as accessories_cost,
                                                            ac.nickname as accessories_name,
                                                            m2m.e_code as m2m_e_code,
                                                            m2m.email as m2m_email,
                                                            m2m.phone as m2m_phone,
                                                            m2m.name as m2m_name,
                                                            p.payment_method as payment,
                                                            r.email as rep_email,
                                                            r.fax as rep_fax,
                                                            r.name as rep_name,
                                                            r.phone as rep_phone,
                                                            s.plan as plan,
                                                            ship.shipping_method as shipping,
                                                            states.statename as taxes_state_name,
                                                            um.manufacturer as manufacturer,
                                                            uv.version as version,
                                                            v.nickname as product_name
                                                            FROM marketing.orders o
                                                            LEFT JOIN marketing.accounts a ON a.accounts_id = o.accounts_id
                                                            LEFT JOIN marketing.accessories ac ON ac.accessories_id = o.accessories_id
                                                            LEFT JOIN marketing.payment p ON p.payment_id = o.payment_id
                                                            LEFT JOIN marketing.reps r ON r.reps_id = o.reps_id
                                                            LEFT JOIN marketing.shipping ship ON ship.shipping_id = o.shipping_id
                                                            LEFT JOIN marketing.states states ON states.states_id = o.taxes_state
                                                            LEFT JOIN marketing.subscription s ON s.subscription_id = o.subscription_id
                                                            LEFT JOIN marketing.versions v ON v.versions_id = o.versions_id
                                                            LEFT JOIN unitmanagement.unitversion uv ON uv.unitversion_id = o.unitversion_id
                                                            LEFT JOIN unitmanagement.unitmanufacturer um ON um.unitmanufacturer_id = uv.unitmanufacturer_id
                                                            LEFT JOIN backoffice.m2m_reps m2m ON m2m.m2m_repID = o.m2m_repID
                                                            WHERE o.status_id = 4"
                                                            . $search
                                                            . " ORDER BY o.updated DESC";

                                                    $rows = $this->orders_data->getReport($sql, $sqlPlaceHolder);

        $report['message'] = $sql . ' - ' . implode(', ', $sqlPlaceHolder);

                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Order</th><th>Customer</th><th>Invoice Number</th><th>Created</th><th>Approved</th><th>Updated</th></tr>';
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){

                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }

                                                            $row['cc_exp'] = $this->decryptCc($row['cc_exp']); 
                                                            $row['cc_num'] = $this->decryptCc($row['cc_num']); 
                                                            $row['cc_ver'] = $this->decryptCc($row['cc_ver']);
                                                            $cc = null ;
                                                            if ( ( $row['cc_num'] ) && ( $row['payment_id'] == 2 ) ) {
                                                                $cc = ' title=" ' . $row['cc_num'] . ', CCV=' . $row['cc_ver'] . ' EXP=' . $row['cc_exp'] . '"' ;
                                                            }

                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '"><a class="status details" href="javascript:void(0);" id="invoiced_' . $row['orders_id'] . '">' . $row['orders_id'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['account_name'] . '</td>'
                                                                . '<td class="' . $evenOdd . '"><input class="form-control" id="invoiced-' . $row['orders_id'] . '" placeholder=" Invoice Number..."' . $cc . '></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['createdate'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['approvedate'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['updated'] . '</td>'
                                                                . '</tr><tr>' 
                                                                . '<td class="' . $evenOdd . '" colspan="20">'
                                                                . $this->zOrder($row,'invoiced','INVOICE GENERATED FOR')
                                                                . '</td>'
                                                                . '</tr>'; 
                                                        }

                                                    }

                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="9">&nbsp;<p><span class="center text-24 text-green">Yes, this queue is empty!</span><p><span class="center text-24 text-grey">... all orders have been processed</span></td></tr>';
                                                    }

                                                    $report['pageCount'] = $params['pageCount'];

                                                    $report['pageTotal'] = $page;

                                                    break;

            case     'fulfillment-invoiced-table' : $report['code'] = 0; 
                                                    $report['message'] = '&nbsp;<p>';
                                                    $sql = "SELECT o.*,
                                                            a.company as account_name,
                                                            a.contact as account_contact,
                                                            a.email as account_email,
                                                            a.fax as account_fax,
                                                            a.phone as account_phone,
                                                            ac.cost as accessories_cost,
                                                            ac.nickname as accessories_name,
                                                            m2m.e_code as m2m_e_code,
                                                            m2m.email as m2m_email,
                                                            m2m.phone as m2m_phone,
                                                            m2m.name as m2m_name,
                                                            p.payment_method as payment,
                                                            r.email as rep_email,
                                                            r.fax as rep_fax,
                                                            r.name as rep_name,
                                                            r.phone as rep_phone,
                                                            s.plan as plan,
                                                            ship.shipping_method as shipping,
                                                            states.statename as taxes_state_name,
                                                            um.manufacturer as manufacturer,
                                                            uv.version as version,
                                                            v.nickname as product_name
                                                            FROM marketing.orders o
                                                            LEFT JOIN marketing.accounts a ON a.accounts_id = o.accounts_id
                                                            LEFT JOIN marketing.accessories ac ON ac.accessories_id = o.accessories_id
                                                            LEFT JOIN marketing.payment p ON p.payment_id = o.payment_id
                                                            LEFT JOIN marketing.reps r ON r.reps_id = o.reps_id
                                                            LEFT JOIN marketing.shipping ship ON ship.shipping_id = o.shipping_id
                                                            LEFT JOIN marketing.states states ON states.states_id = o.taxes_state
                                                            LEFT JOIN marketing.subscription s ON s.subscription_id = o.subscription_id
                                                            LEFT JOIN marketing.versions v ON v.versions_id = o.versions_id
                                                            LEFT JOIN unitmanagement.unitversion uv ON uv.unitversion_id = o.unitversion_id
                                                            LEFT JOIN unitmanagement.unitmanufacturer um ON um.unitmanufacturer_id = uv.unitmanufacturer_id
                                                            LEFT JOIN backoffice.m2m_reps m2m ON m2m.m2m_repID = o.m2m_repID
                                                            WHERE o.status_id = 5"
                                                            . $search
                                                            . " ORDER BY o.updated DESC";

                                                    $rows = $this->orders_data->getReport($sql, $sqlPlaceHolder);

        $report['message'] = $sql . ' - ' . implode(', ', $sqlPlaceHolder);

                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Order</th><th>Customer</th><th>Quantity</th><th>Revenue</th><th>Rep</th><th>Invoice Number</th><th>Invoice Date</th><th>Created</th><th>Approved</th><th>Updated</th></tr>';
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){

                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }

                                                            $row['cc_exp'] = $this->decryptCc($row['cc_exp']); 
                                                            $row['cc_num'] = $this->decryptCc($row['cc_num']); 
                                                            $row['cc_ver'] = $this->decryptCc($row['cc_ver']);
                                                            $cc = null ;
                                                            if ( ( $row['cc_num'] ) && ( $row['payment_id'] == 2 ) ) {
                                                                $cc = ' title=" ' . $row['cc_num'] . ', CCV=' . $row['cc_ver'] . ' EXP=' . $row['cc_exp'] . '"' ;
                                                            }

                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '"><a class="status details" href="javascript:void(0);" id="completed_' . $row['orders_id'] . '">' . $row['orders_id'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['account_name'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . number_format( $row['quantity'] , 0 , '.' , ',' ) . '</td>'
                                                                . '<td class="' . $evenOdd . '">$' . number_format( $row['grand_total'] / 100 , 2 , '.' , ',' ) . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['rep_name'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['invoice_number'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['invoicedate'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['createdate'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['approvedate'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['updated'] . '</td>'
                                                                . '</tr><tr>' 
                                                                . '<td class="' . $evenOdd . '" colspan="20">'
                                                                . $this->zOrder($row,'completed')
                                                                . '</td>'
                                                                . '</tr>'; 
                                                        }

                                                    }

                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="9">&nbsp;<p><span class="center text-24 text-green">Yes, this queue is empty!</span><p><span class="center text-24 text-grey">... all orders have been processed</span></td></tr>';
                                                    }

                                                    $report['pageCount'] = $params['pageCount'];

                                                    $report['pageTotal'] = $page;

                                                    break;

            case    'fulfillment-read_only-table' : $report['code'] = 0; 
                                                    $report['message'] = '&nbsp;<p>';
                                                    $sql = "SELECT o.*,
                                                            a.company as account_name,
                                                            a.contact as account_contact,
                                                            a.email as account_email,
                                                            a.fax as account_fax,
                                                            a.phone as account_phone,
                                                            ac.cost as accessories_cost,
                                                            ac.nickname as accessories_name,
                                                            m2m.e_code as m2m_e_code,
                                                            m2m.email as m2m_email,
                                                            m2m.phone as m2m_phone,
                                                            m2m.name as m2m_name,
                                                            p.payment_method as payment,
                                                            r.email as rep_email,
                                                            r.fax as rep_fax,
                                                            r.name as rep_name,
                                                            r.phone as rep_phone,
                                                            s.plan as plan,
                                                            ship.shipping_method as shipping,
                                                            states.statename as taxes_state_name,
                                                            um.manufacturer as manufacturer,
                                                            uv.version as version,
                                                            v.nickname as product_name
                                                            FROM marketing.orders o
                                                            LEFT JOIN marketing.accounts a ON a.accounts_id = o.accounts_id
                                                            LEFT JOIN marketing.accessories ac ON ac.accessories_id = o.accessories_id
                                                            LEFT JOIN marketing.payment p ON p.payment_id = o.payment_id
                                                            LEFT JOIN marketing.reps r ON r.reps_id = o.reps_id
                                                            LEFT JOIN marketing.shipping ship ON ship.shipping_id = o.shipping_id
                                                            LEFT JOIN marketing.states states ON states.states_id = o.taxes_state
                                                            LEFT JOIN marketing.subscription s ON s.subscription_id = o.subscription_id
                                                            LEFT JOIN marketing.versions v ON v.versions_id = o.versions_id
                                                            LEFT JOIN unitmanagement.unitversion uv ON uv.unitversion_id = o.unitversion_id
                                                            LEFT JOIN unitmanagement.unitmanufacturer um ON um.unitmanufacturer_id = uv.unitmanufacturer_id
                                                            LEFT JOIN backoffice.m2m_reps m2m ON m2m.m2m_repID = o.m2m_repID
                                                            WHERE o.status_id < 10"
                                                            . $search
                                                            . " ORDER BY o.status_id ASC , o.updated DESC";
                                                    $rows = $this->orders_data->getReport($sql, $sqlPlaceHolder);

        $report['message'] = $sql . ' - ' . implode(', ', $sqlPlaceHolder);

                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Order</th><th>Revenue</th><th>Customer</th><th>Product</th><th>Quantity</th><th>Unit&nbsp;Price</th><th>ARPU</th><th>Rep</th><th>Subscription</th><th>Status</th><th>Last&nbsp;Update</th></tr>';
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){

                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }

                                                            if($row['invoice_number']<1){
                                                                $row['invoice_number']='';
                                                            }
                                                            if($row['approvedate']=='0000-00-00 00:00:00'){
                                                                $row['approvedate']='';
                                                            }

                                                            switch($row['status_id']){
                                                                case              0  :
                                                                case             '0' : $row['status_id'] = 'Pending Approval' ;
                                                                                       break;
                                                                case              1  :
                                                                case             '1' : $row['status_id'] = 'Approved' ;
                                                                                       break;
                                                                case              2  :
                                                                case             '2' : $row['status_id'] = 'Inventoried' ;
                                                                                       break;
                                                                case              3  :
                                                                case             '3' : $row['status_id'] = 'Labeled' ;
                                                                                       break;
                                                                case              4  :
                                                                case             '4' : $row['status_id'] = 'Shipped' ;
                                                                                       break;
                                                                case              5  :
                                                                case             '5' : $row['status_id'] = 'Invoiced' ;
                                                                                       break;
                                                            }

                                                            $row['cc_exp'] = null; 
                                                            $row['cc_num'] = null; 
                                                            $row['cc_ver'] = null;
                                                            // $row['cc_exp'] = $this->decryptCc($row['cc_exp']); 
                                                            // $row['cc_num'] = $this->decryptCc($row['cc_num']); 
                                                            // $row['cc_ver'] = $this->decryptCc($row['cc_ver']);
                                                            // $cc = null ;
                                                            // if ( ( $row['cc_num'] ) && ( $row['payment_id'] == 2 ) ) {
                                                            //     $cc = ' title=" ' . $row['cc_num'] . ', CCV=' . $row['cc_ver'] . ' EXP=' . $row['cc_exp'] . '"' ;
                                                            // }

                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '"><a class="status details" href="javascript:void(0);" id="completed_' . $row['orders_id'] . '">' . $row['orders_id'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">$' . number_format( $row['grand_total'] / 100 , 2 , '.' , ',' ) . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['account_name'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['product_name'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . number_format( $row['quantity'] , 0 , '.' , ',' ) . '</td>'
                                                                . '<td class="' . $evenOdd . '">$' . number_format( $row['rate'] / 100 , 2 , '.' , ',' ) . '</td>'
                                                                . '<td class="' . $evenOdd . '">$' . number_format( $row['extended_total'] / 100 / $row['quantity'] , 2 , '.' , ',' ) . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['rep_name'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['plan'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['status_id'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['updated'] . '</td>'
                                                                . '</tr><tr>' 
                                                                . '<td class="' . $evenOdd . '" colspan="20">'
                                                                . $this->zOrder($row,'completed')
                                                                . '</td>'
                                                                . '</tr>'; 
                                                        }

                                                    }

                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="9">&nbsp;<p><span class="center text-24 text-green">Yes, this queue is empty!</span><p><span class="center text-24 text-grey">... all orders have been processed</span></td></tr>';
                                                    }

                                                    $report['pageCount'] = $params['pageCount'];

                                                    $report['pageTotal'] = $page;

                                                    break;

            case   'fulfillment-read_write-table' : $report['code'] = 0; 
                                                    $report['message'] = '&nbsp;<p>';
                                                    $sql = "SELECT o.*,
                                                            a.company as account_name,
                                                            a.contact as account_contact,
                                                            a.email as account_email,
                                                            a.fax as account_fax,
                                                            a.phone as account_phone,
                                                            ac.cost as accessories_cost,
                                                            ac.nickname as accessories_name,
                                                            m2m.e_code as m2m_e_code,
                                                            m2m.email as m2m_email,
                                                            m2m.phone as m2m_phone,
                                                            m2m.name as m2m_name,
                                                            p.payment_method as payment,
                                                            r.email as rep_email,
                                                            r.fax as rep_fax,
                                                            r.name as rep_name,
                                                            r.phone as rep_phone,
                                                            s.plan as plan,
                                                            ship.shipping_method as shipping,
                                                            states.statename as taxes_state_name,
                                                            um.manufacturer as manufacturer,
                                                            uv.version as version,
                                                            v.nickname as product_name
                                                            FROM marketing.orders o
                                                            LEFT JOIN marketing.accounts a ON a.accounts_id = o.accounts_id
                                                            LEFT JOIN marketing.accessories ac ON ac.accessories_id = o.accessories_id
                                                            LEFT JOIN marketing.payment p ON p.payment_id = o.payment_id
                                                            LEFT JOIN marketing.reps r ON r.reps_id = o.reps_id
                                                            LEFT JOIN marketing.shipping ship ON ship.shipping_id = o.shipping_id
                                                            LEFT JOIN marketing.states states ON states.states_id = o.taxes_state
                                                            LEFT JOIN marketing.subscription s ON s.subscription_id = o.subscription_id
                                                            LEFT JOIN marketing.versions v ON v.versions_id = o.versions_id
                                                            LEFT JOIN unitmanagement.unitversion uv ON uv.unitversion_id = o.unitversion_id
                                                            LEFT JOIN unitmanagement.unitmanufacturer um ON um.unitmanufacturer_id = uv.unitmanufacturer_id
                                                            LEFT JOIN backoffice.m2m_reps m2m ON m2m.m2m_repID = o.m2m_repID
                                                            WHERE o.status_id < 10"
                                                            . $search
                                                            . " ORDER BY o.status_id ASC , o.updated DESC";
                                                    $rows = $this->orders_data->getReport($sql, $sqlPlaceHolder);

        $report['message'] = $sql . ' - ' . implode(', ', $sqlPlaceHolder);

                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Order #</th><th>Customer</th><th>Sales Rep</th><th>Inventory</th><th>Shipping</th><th>Invoice</th><th>Status</th><th>Last&nbsp;Update</th></tr>';
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){

                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }

                                                            switch($row['active']){
                                                                case           1  :
                                                                case          '1' : $deletedRecord = 'deleted-record ' ;
                                                                                    $readonly = ' readonly' ;
                                                                                    $evenOdd = $deletedRecord ;
                                                                                    break;
                                                                          default : $deletedRecord = null ;
                                                                                    $readonly = null ;
                                                            }

                                                            if($row['invoice_number']<1){
                                                                $row['invoice_number']='';
                                                            }
                                                            if($row['approvedate']=='0000-00-00 00:00:00'){
                                                                $row['approvedate']='';
                                                            }

                                                            switch($row['status_id']){
                                                                case              0  :
                                                                case             '0' : $row['status_id'] = 'Pending Approval' ;
                                                                                       break;
                                                                case              1  :
                                                                case             '1' : $row['status_id'] = 'Approved' ;
                                                                                       break;
                                                                case              2  :
                                                                case             '2' : $row['status_id'] = 'Inventoried' ;
                                                                                       break;
                                                                case              3  :
                                                                case             '3' : $row['status_id'] = 'Labeled' ;
                                                                                       break;
                                                                case              4  :
                                                                case             '4' : $row['status_id'] = 'Shipped' ;
                                                                                       break;
                                                                case              5  :
                                                                case             '5' : $row['status_id'] = 'Invoiced' ;
                                                                                       break;
                                                            }

                                                            $row['cc_exp'] = null; 
                                                            $row['cc_num'] = null; 
                                                            $row['cc_ver'] = null;
                                                            // $row['cc_exp'] = $this->decryptCc($row['cc_exp']); 
                                                            // $row['cc_num'] = $this->decryptCc($row['cc_num']); 
                                                            // $row['cc_ver'] = $this->decryptCc($row['cc_ver']);
                                                            // $cc = null ;
                                                            // if ( ( $row['cc_num'] ) && ( $row['payment_id'] == 2 ) ) {
                                                            //     $cc = ' title=" ' . $row['cc_num'] . ', CCV=' . $row['cc_ver'] . ' EXP=' . $row['cc_exp'] . '"' ;
                                                            // }

                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '"><a class="status details" href="javascript:void(0);" id="completed_' . $row['orders_id'] . '">' . $row['orders_id'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['account_name'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['rep_name'] . '</td>'
                                                                . '<td class="' . $evenOdd . '"><textarea class="' . $deletedRecord . 'read-write" id="inventory-' . $row['orders_id'] . '" rows="2" placeholder=" Box and Serial Number(s)..."' . $cc . $readonly .'>' . $row['inventory'] . '</textarea></td>'
                                                                . '<td class="' . $evenOdd . '"><textarea class="' . $deletedRecord . 'read-write" id="shipping_track-' . $row['orders_id'] . '" rows="2" placeholder=" Shipping Tracking Number..."' . $cc . $readonly . '>' . $row['shipping_track'] . '</textarea></td>'
                                                                . '<td class="' . $evenOdd . '"><textarea class="' . $deletedRecord . 'read-write" id="invoice_number-' . $row['orders_id'] . '" rows="2" placeholder=" Invoice Number(s)..."' . $cc . $readonly . '>' . $row['invoice_number'] . '</textarea></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['status_id'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['updated'] . '</td>'
                                                                . '</tr><tr>' 
                                                                . '<td class="' . $evenOdd . '" colspan="11">'
                                                                . $this->zOrder($row,'completed')
                                                                . '</td>'
                                                                . '</tr>'; 
                                                        }

                                                    }

                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="11">&nbsp;<p><span class="center text-24 text-green">Yes, this queue is empty!</span><p><span class="center text-24 text-grey">... all orders have been processed</span></td></tr>';
                                                    }

                                                    $report['pageCount'] = $params['pageCount'];

                                                    $report['pageTotal'] = $page;

                                                    break;

            case  'fulfillment-activations-table' : $report['code'] = 0; 
                                                    $report['message'] = '&nbsp;<p>';
                                                    $sqlPlaceHolder[] = '0000-00-00 00:00:00' ;
                                                    $sql = "SELECT o.*,
                                                            a.company as account_name,
                                                            a.contact as account_contact,
                                                            a.email as account_email,
                                                            a.fax as account_fax,
                                                            a.phone as account_phone,
                                                            ac.cost as accessories_cost,
                                                            ac.nickname as accessories_name,
                                                            m2m.e_code as m2m_e_code,
                                                            m2m.email as m2m_email,
                                                            m2m.phone as m2m_phone,
                                                            m2m.name as m2m_name,
                                                            p.payment_method as payment,
                                                            r.email as rep_email,
                                                            r.fax as rep_fax,
                                                            r.name as rep_name,
                                                            r.phone as rep_phone,
                                                            s.plan as plan,
                                                            ship.shipping_method as shipping,
                                                            states.statename as taxes_state_name,
                                                            um.manufacturer as manufacturer,
                                                            uv.version as version,
                                                            v.nickname as product_name
                                                            FROM marketing.orders o
                                                            LEFT JOIN marketing.accounts a ON a.accounts_id = o.accounts_id
                                                            LEFT JOIN marketing.accessories ac ON ac.accessories_id = o.accessories_id
                                                            LEFT JOIN marketing.payment p ON p.payment_id = o.payment_id
                                                            LEFT JOIN marketing.reps r ON r.reps_id = o.reps_id
                                                            LEFT JOIN marketing.shipping ship ON ship.shipping_id = o.shipping_id
                                                            LEFT JOIN marketing.states states ON states.states_id = o.taxes_state
                                                            LEFT JOIN marketing.subscription s ON s.subscription_id = o.subscription_id
                                                            LEFT JOIN marketing.versions v ON v.versions_id = o.versions_id
                                                            LEFT JOIN unitmanagement.unitversion uv ON uv.unitversion_id = o.unitversion_id
                                                            LEFT JOIN unitmanagement.unitmanufacturer um ON um.unitmanufacturer_id = uv.unitmanufacturer_id
                                                            LEFT JOIN backoffice.m2m_reps m2m ON m2m.m2m_repID = o.m2m_repID
                                                            WHERE o.status_id > 4
                                                            AND o.status_id < 10
                                                            AND o.active = ''
                                                            AND o.m2m_repID > 0
                                                            AND o.e_code_activation = ?"
                                                            . $search
                                                            . " ORDER BY o.approvedate ASC";

                                                    $rows = $this->orders_data->getReport($sql, $sqlPlaceHolder);

        $report['message'] = $sql . ' - ' . implode(', ', $sqlPlaceHolder);

                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Order</th><th>Customer</th><th>E Code</th><th>VZW Rep</th><th>Details</th><th>Sales Rep</th><th>Quantity</th><th>Approved</th><th>Updated</th></tr>';
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){

                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }

                                                            $row['cc_exp'] = null; 
                                                            $row['cc_num'] = null; 
                                                            $row['cc_ver'] = null;
                                                            // $row['cc_exp'] = $this->decryptCc($row['cc_exp']); 
                                                            // $row['cc_num'] = $this->decryptCc($row['cc_num']); 
                                                            // $row['cc_ver'] = $this->decryptCc($row['cc_ver']);
                                                            // $cc = null ;
                                                            // if ( ( $row['cc_num'] ) && ( $row['payment_id'] == 2 ) ) {
                                                            //     $cc = ' title=" ' . $row['cc_num'] . ', CCV=' . $row['cc_ver'] . ' EXP=' . $row['cc_exp'] . '"' ;
                                                            // }

                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '"><a class="status details" href="javascript:void(0);" id="activations_' . $row['orders_id'] . '">' . $row['orders_id'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '"><a class="backoffice-check" href="javascript:void(0);" id="backoffice-' . $row['orders_id'] . '">' . $row['account_name'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['m2m_e_code'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['m2m_name'] . '</td>'
                                                                . '<td class="' . $evenOdd . '"><textarea class="form-control activations" id="activations-' . $row['orders_id'] . '" placeholder="Activation Notes...">' . $row['e_code_notes'] . '</textarea></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['rep_name'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . number_format( $row['quantity'] , 0 , '.' , ',' ) . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['approvedate'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['updated'] . '</td>'
                                                                . '</tr><tr>' 
                                                                . '<td class="' . $evenOdd . '" colspan="10" id="results-backoffice-' . $row['orders_id'] . '"></td>'
                                                                . '</tr><tr>' 
                                                                . '<td class="' . $evenOdd . '" colspan="10">'
                                                                . $this->zOrder($row,'activations','CONFIRM ACTIVATIONS')
                                                                . '</td>'
                                                                . '</tr>'; 
                                                        }

                                                    }

                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="10">&nbsp;<p><span class="center text-24 text-green">Yes, this queue is empty!</span><p><span class="center text-24 text-grey">... all orders have been processed</span></td></tr>';
                                                    }

                                                    $report['pageCount'] = $params['pageCount'];

                                                    $report['pageTotal'] = $page;

                                                    break;

    case   'fulfillment-activations_issued-table' : $report['code'] = 0; 
                                                    $report['message'] = '&nbsp;<p>';
                                                    $sqlPlaceHolder[] = '0000-00-00 00:00:00' ;
                                                    $sql = "SELECT o.*,
                                                            a.company as account_name,
                                                            a.contact as account_contact,
                                                            a.email as account_email,
                                                            a.fax as account_fax,
                                                            a.phone as account_phone,
                                                            ac.cost as accessories_cost,
                                                            ac.nickname as accessories_name,
                                                            m2m.e_code as m2m_e_code,
                                                            m2m.email as m2m_email,
                                                            m2m.phone as m2m_phone,
                                                            m2m.name as m2m_name,
                                                            p.payment_method as payment,
                                                            r.email as rep_email,
                                                            r.fax as rep_fax,
                                                            r.name as rep_name,
                                                            r.phone as rep_phone,
                                                            s.plan as plan,
                                                            ship.shipping_method as shipping,
                                                            states.statename as taxes_state_name,
                                                            um.manufacturer as manufacturer,
                                                            uv.version as version,
                                                            v.nickname as product_name
                                                            FROM marketing.orders o
                                                            LEFT JOIN marketing.accounts a ON a.accounts_id = o.accounts_id
                                                            LEFT JOIN marketing.accessories ac ON ac.accessories_id = o.accessories_id
                                                            LEFT JOIN marketing.payment p ON p.payment_id = o.payment_id
                                                            LEFT JOIN marketing.reps r ON r.reps_id = o.reps_id
                                                            LEFT JOIN marketing.shipping ship ON ship.shipping_id = o.shipping_id
                                                            LEFT JOIN marketing.states states ON states.states_id = o.taxes_state
                                                            LEFT JOIN marketing.subscription s ON s.subscription_id = o.subscription_id
                                                            LEFT JOIN marketing.versions v ON v.versions_id = o.versions_id
                                                            LEFT JOIN unitmanagement.unitversion uv ON uv.unitversion_id = o.unitversion_id
                                                            LEFT JOIN unitmanagement.unitmanufacturer um ON um.unitmanufacturer_id = uv.unitmanufacturer_id
                                                            LEFT JOIN backoffice.m2m_reps m2m ON m2m.m2m_repID = o.m2m_repID
                                                            WHERE o.status_id > 4
                                                            AND o.status_id < 10
                                                            AND o.active = ''
                                                            AND o.m2m_repID > 0
                                                            AND o.e_code_activation != ?"
                                                            . $search
                                                            . " ORDER BY o.approvedate ASC";

                                                    $rows = $this->orders_data->getReport($sql, $sqlPlaceHolder);

        $report['message'] = $sql . ' - ' . implode(', ', $sqlPlaceHolder);

                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Order</th><th>Customer</th><th>E Code</th><th>VZW Rep</th><th>Details</th><th>Sales Rep</th><th>Quantity</th><th>Approved</th><th>Updated</th></tr>';
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){

                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }

                                                            $row['cc_exp'] = null; 
                                                            $row['cc_num'] = null; 
                                                            $row['cc_ver'] = null;
                                                            // $row['cc_exp'] = $this->decryptCc($row['cc_exp']); 
                                                            // $row['cc_num'] = $this->decryptCc($row['cc_num']); 
                                                            // $row['cc_ver'] = $this->decryptCc($row['cc_ver']);
                                                            // $cc = null ;
                                                            // if ( ( $row['cc_num'] ) && ( $row['payment_id'] == 2 ) ) {
                                                            //     $cc = ' title=" ' . $row['cc_num'] . ', CCV=' . $row['cc_ver'] . ' EXP=' . $row['cc_exp'] . '"' ;
                                                            // }

                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '"><a class="status details" href="javascript:void(0);" id="activations_' . $row['orders_id'] . '">' . $row['orders_id'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '"><a class="backoffice-check" href="javascript:void(0);" id="backoffice-' . $row['orders_id'] . '">' . $row['account_name'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['m2m_e_code'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['m2m_name'] . '</td>'
                                                                . '<td class="' . $evenOdd . '"><textarea class="form-control activations" placeholder="Activation Notes..." readonly>' . $row['e_code_notes'] . '</textarea></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['rep_name'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . number_format( $row['quantity'] , 0 , '.' , ',' ) . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['approvedate'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['updated'] . '</td>'
                                                                . '</tr><tr>' 
                                                                . '<td class="' . $evenOdd . '" colspan="10" id="results-backoffice-' . $row['orders_id'] . '"></td>'
                                                                . '</tr><tr>' 
                                                                . '<td class="' . $evenOdd . '" colspan="10">'
                                                                . $this->zOrder($row,'completed')
                                                                . '</td>'
                                                                . '</tr>'; 
                                                        }

                                                    }

                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="8">&nbsp;<p><span class="center text-40 text-grey">Sorry</span><p><span class="center text-24 text-grey">... no records found</span></td></tr>';
                                                    }

                                                    $report['pageCount'] = $params['pageCount'];

                                                    $report['pageTotal'] = $page;

                                                    break;

            case         'orders-submitted-table' : $report['code'] = 0; 
                                                    $report['message'] = '&nbsp;<p>';
                                                    $sql = "SELECT o.*,
                                                            a.company as account_name,
                                                            a.contact as account_contact,
                                                            a.email as account_email,
                                                            a.fax as account_fax,
                                                            a.phone as account_phone,
                                                            ac.cost as accessories_cost,
                                                            ac.nickname as accessories_name,
                                                            m2m.e_code as m2m_e_code,
                                                            m2m.email as m2m_email,
                                                            m2m.phone as m2m_phone,
                                                            m2m.name as m2m_name,
                                                            p.payment_method as payment,
                                                            r.email as rep_email,
                                                            r.fax as rep_fax,
                                                            r.name as rep_name,
                                                            r.phone as rep_phone,
                                                            s.plan as plan,
                                                            ship.shipping_method as shipping,
                                                            states.statename as taxes_state_name,
                                                            um.manufacturer as manufacturer,
                                                            uv.version as version,
                                                            v.nickname as product_name
                                                            FROM marketing.orders o
                                                            LEFT JOIN marketing.accounts a ON a.accounts_id = o.accounts_id
                                                            LEFT JOIN marketing.accessories ac ON ac.accessories_id = o.accessories_id
                                                            LEFT JOIN marketing.payment p ON p.payment_id = o.payment_id
                                                            LEFT JOIN marketing.reps r ON r.reps_id = o.reps_id
                                                            LEFT JOIN marketing.shipping ship ON ship.shipping_id = o.shipping_id
                                                            LEFT JOIN marketing.states states ON states.states_id = o.taxes_state
                                                            LEFT JOIN marketing.subscription s ON s.subscription_id = o.subscription_id
                                                            LEFT JOIN marketing.versions v ON v.versions_id = o.versions_id
                                                            LEFT JOIN unitmanagement.unitversion uv ON uv.unitversion_id = o.unitversion_id
                                                            LEFT JOIN unitmanagement.unitmanufacturer um ON um.unitmanufacturer_id = uv.unitmanufacturer_id
                                                            LEFT JOIN backoffice.m2m_reps m2m ON m2m.m2m_repID = o.m2m_repID
                                                            WHERE o.reps_id = ?
                                                            AND o.status_id < 10"
                                                            . $search
                                                            . " ORDER BY o.updated DESC";

                                                    $rows = $this->orders_data->getReport($sql, array($params['rep_id']));

        $report['message'] = $sql . ' - ' . implode(', ', $sqlPlaceHolder);

                                                    $in  = array( '-' , ' ' , ':' ) ;
                                                    $out = array( '' , '' , '' ) ;

                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Order</th><th>Customer</th><th>VZW Rep</th><th>Quantity</th><th>Value</th><th>Actual</th><th>Created</th><th>Approved</th><th>Tracking Number</th><th>Invoice Number</th><th>Invoice Date</th><th>Activation Date</th><th>Updated</th></tr>';
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){

                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }

                                                            if($row['approvedate']=='0000-00-00 00:00:00'){
                                                                $row['approvedate'] = null ;
                                                            }
                                                            if($row['invoicedate']=='0000-00-00 00:00:00'){
                                                                $row['invoicedate'] = null ;
                                                            }
                                                            if($row['invoice_number']<1){
                                                                $row['invoice_number'] = 'Pending' ;
                                                            }
                                                            if($row['e_code_activation']=='0000-00-00 00:00:00'){
                                                                $row['e_code_activation'] = null ;
                                                            }

                                                            $actual = $this->zActual($row);

                                                            $row['cc_exp'] = null; 
                                                            $row['cc_num'] = null; 
                                                            $row['cc_ver'] = null;
                                                            // $row['cc_exp'] = $this->decryptCc($row['cc_exp']); 
                                                            // $row['cc_num'] = $this->decryptCc($row['cc_num']); 
                                                            // $row['cc_ver'] = $this->decryptCc($row['cc_ver']);
                                                            // $cc = null ;
                                                            // if ( ( $row['cc_num'] ) && ( $row['payment_id'] == 2 ) ) {
                                                            //     $cc = ' title=" ' . $row['cc_num'] . ', CCV=' . $row['cc_ver'] . ' EXP=' . $row['cc_exp'] . '"' ;
                                                            // }

                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '"><a class="status details" href="javascript:void(0);" id="completed_' . $row['orders_id'] . '">' . $row['orders_id'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['account_name'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['m2m_name'] . ' ' . $row['m2m_e_code'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . number_format( $row['quantity'] , 0 , '.' , ',' ) . '</td>'
                                                                . '<td class="' . $evenOdd . '">$' . number_format( $row['grand_total'] / 100 , 2 , '.' , ',' ) . '</td>'
                                                                . '<td class="' . $evenOdd . '">$' . number_format( $actual / 100 , 2 , '.' , ',' ) . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['createdate'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['approvedate'] . '</td>'
                                                                . '<td class="' . $evenOdd . '"><a href="https://www.fedex.com/apps/fedextrack/?tracknumbers=' . $row['shipping_track'] . '&cntry_code=us" target="_fedex">' . $row['shipping_track'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '"><a href="https://orders.positionplusgps.com/orderstatus/' . str_replace( $in , $out , $row['createdate'] ) . $row['orders_id'] . '" target="_orderstatus">' . $row['invoice_number'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['invoicedate'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['e_code_activation'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['updated'] . '</td>'
                                                                . '</tr><tr>' 
                                                                . '<td class="' . $evenOdd . '" colspan="20">'
                                                                . $this->zOrder($row,'completed')
                                                                . '</td>'
                                                                . '</tr>'; 
                                                        }

                                                    }

                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="11">&nbsp;<p><span class="center text-24 text-green">No orders found...</span><p><span class="center text-24 text-grey">please place an order.</span></td></tr>';
                                                    }

                                                    $report['pageCount'] = $params['pageCount'];

                                                    $report['pageTotal'] = $page;

                                                    break;

                                         default :  $report['code'] = 0; 
                                                    $report['thead'] = '<tr><th>Models/Logic/ReportLogic.php:getReport</td></tr>'; 
                                                    $report['tbody'] = '<tr><td>' . $params['pid'] . '</td></tr>'; 
                                                    $report['records'] = 0;
                                                    
        }

        if ( ! ( $report['lastReport'] ) ) {
            $report['lastReport'] = ' ';
        }

        return $report;

    }

    /**
     * @return array
     */
    public function ordersAccessories($accessories_id,$quantity)
    {
        return $this->orders_data->ordersAccessories($accessories_id,$quantity);
    }

    /**
     * @return array
     */
    public function ordersBackoffice($params)
    {
        return $this->orders_data->ordersBackoffice($params);
    }

    /**
     * @return array
     */
    public function ordersCompanies($rep,$search)
    {
        return $this->orders_data->ordersCompanies($rep,$search);
    }

    /**
     * @return array
     */
    public function ordersCompany($accounts_id)
    {
        return $this->orders_data->ordersCompany($accounts_id);
    }

    /**
     * @return array
     */
    public function ordersDelete($params)
    {
        return $this->orders_data->ordersDelete($params);
    }

    /**
     * @return array
     */
    public function ordersDetail($uri)
    {
        return $this->orders_data->ordersDetail($uri);
    }

    /**
     * @return array
     */
    public function ordersEcode($e_code)
    {
        return $this->orders_data->ordersEcode($e_code);
    }

    /**
     * @return array
     */
    public function ordersHandling($shipping_id,$handling)
    {
        return $this->orders_data->ordersHandling($shipping_id,$handling);
    }

    /**
     * @return array
     */
    public function ordersOptions($value,$rep)
    {
        return $this->orders_data->ordersOptions($value,$rep);
    }

    /**
     * @return array
     */
    public function ordersOrder($params)
    {
        $params['cc_exp'] = $this->encryptCc($params['cc_exp']);
        $params['cc_num'] = $this->encryptCc($params['cc_num']);
        $params['cc_ver'] = $this->encryptCc($params['cc_ver']);
        return $this->orders_data->ordersOrder($params);
    }

    /**
     * @return array
     */
    public function ordersOverride($rep)
    {
        return $this->orders_data->ordersOverride($rep);
    }

    /**
     * @return array
     */
    public function ordersRate($product,$plan)
    {
        return $this->orders_data->ordersRate($product,$plan);
    }

    /**
     * @return array
     */
    public function ordersRecent($accounts_id)
    {
        return $this->orders_data->ordersRecent($accounts_id);
    }

    /**
     * @return array
     */
    public function ordersRep($post)
    {
        return $this->orders_data->ordersRep($post);
    }

    /**
     * @return array
     */
    public function ordersShipping($shipping_id,$quantity)
    {
        return $this->orders_data->ordersShipping($shipping_id,$quantity);
    }

    /**
     * @return array
     */
    public function ordersTaxRate($states_id)
    {
        return $this->orders_data->ordersTaxRate($states_id);
    }

    /**
     * @return array
     */
    public function ordersUpdate($params)
    {
        return $this->orders_data->ordersUpdate($params);
    }

    /**
     * @return array
     */
    public function ordersUpdateState($params)
    {
        return $this->orders_data->ordersUpdateState($params);
    }

    /**
     * @return array
     */
    public function ordersUpdateTerms($params)
    {
        return $this->orders_data->ordersUpdateTerms($params);
    }

    /**
     * @return array
     */
    public function ordersUm($reps_id,$account_id)
    {
        return $this->orders_data->ordersUm($reps_id,$account_id);
    }

    /**
     * @return array
     */
    public function ordersUmAdd($reps_id,$account_id)
    {
        return $this->orders_data->ordersUmAdd($reps_id,$account_id);
    }

}
