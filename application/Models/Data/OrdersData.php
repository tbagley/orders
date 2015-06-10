<?php

namespace Models\Data;

use Models\Logic\AddressLogic;
use Models\Logic\BaseLogic;
use Models\Data\BaseData;
use GTC\Component\Utils\Date;
use Models\Data\ContactData;

class OrdersData extends BaseData
{
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        $this->address_logic    = new AddressLogic;
        $this->base_logic       = new BaseLogic;
        $this->contact_data     = new ContactData;

    }

    /**
     * @return array
     */
    public function createNewAccounts($params,$account_id)
    {
        $accounts = array() ;
        $accounts['reps_id'] = $params['reps_id'] ;
        $accounts['account_id'] = $account_id ;
        $accounts['address_billing'] = $params['billing'] ;
        $accounts['address_shipping'] = $params['shipping'] ;
        $accounts['company'] = $params['company'] ;
        $accounts['phone'] = $params['phone'] ;
        $accounts['fax'] = $params['fax'] ;
        $accounts['contact'] = $params['contact'] ;
        $accounts['email'] = $params['email'] ;
        $accounts['e_code'] = $params['e_code'] ;
        if ($this->db_write->insert('marketing.accounts', $accounts)) {
            $sql = "SELECT accounts_id
                    FROM marketing.accounts 
                    WHERE reps_id = ?
                    AND account_id = ?";
            $res = $this->db_read->fetchAll($sql, array($accounts['reps_id'],$accounts['account_id']));
            return $res[0]['accounts_id'] ;
        }
        return false ;
    }

    /**
     * Get report
     *
     * @return array
     */
    public function getReport($sql, $sqlPlaceHolder)
    {
        $report = $this->db_read->fetchAll($sql, $sqlPlaceHolder);
        return $report;
    }

    /**
     * @return array
     */
    public function fulfillmentStatus($post)
    {
        switch($post['params']['status_id']) {

            case                          1  :
            case                         '1' :  $sql = "UPDATE marketing.orders
                                                        SET status_id = ? ,
                                                        approve_id = ? ,
                                                        approvedate = now()
                                                        WHERE orders_id = ?";
                                                $res = $this->db_read->executeQuery($sql, array($post['params']['status_id'],$post['params']['fulfillment_user'],$post['params']['orders_id']));
                                                break;

            case                          2  :
            case                         '2' :  $sql = "UPDATE marketing.orders
                                                        SET status_id = ? ,
                                                        inventory = ?
                                                        WHERE orders_id = ?";
                                                $res = $this->db_read->executeQuery($sql, array($post['params']['status_id'],$post['params']['inventory'],$post['params']['orders_id']));
                                                break;

            case                          3  :
            case                         '3' :  $sql = "UPDATE marketing.orders
                                                        SET status_id = ? ,
                                                        shipping_track = ?
                                                        WHERE orders_id = ?";
                                                $res = $this->db_read->executeQuery($sql, array($post['params']['status_id'],$post['params']['shipping_track'],$post['params']['orders_id']));
                                                break;

            case                          4  :
            case                         '4' :  $sql = "UPDATE marketing.orders
                                                        SET status_id = ?
                                                        WHERE orders_id = ?";
                                                $res = $this->db_read->executeQuery($sql, array($post['params']['status_id'],$post['params']['orders_id']));
                                                break;

            case                          5  :
            case                         '5' :  $sql = "UPDATE marketing.orders
                                                        SET status_id = ? ,
                                                        invoice_number = ? ,
                                                        invoicedate = now()
                                                        WHERE orders_id = ?";
                                                $res = $this->db_read->executeQuery($sql, array($post['params']['status_id'],$post['params']['invoiced'],$post['params']['orders_id']));
                                                break;

            case                          6  :
            case                         '6' :  $sql = "UPDATE marketing.orders
                                                        SET e_code_notes = ? ,
                                                        e_code_activation = now()
                                                        WHERE orders_id = ?";
                                                $res = $this->db_read->executeQuery($sql, array($post['params']['activations'],$post['params']['orders_id']));
                                                break;

        }
        $sql = "SELECT status_id FROM marketing.orders WHERE orders_id = ?";
        $res = $this->db_read->fetchAll($sql, array($post['params']['orders_id']));
        return $res[0]['status_id'] ;
    }

    /**
     * @return array
     */
    public function fulfillmentUser($post)
    {
        $sql = "SELECT * FROM marketing.fulfillment WHERE email = ? AND password = ? LIMIT 1";
        $res = $this->db_read->fetchAll($sql, array($post['params']['login-email'],$post['params']['login-password']));
        return $res[0] ;
    }

    /**
     * @return array
     */
    public function ordersAccessories($accessories_id,$quantity)
    {
        $sql = "SELECT * FROM marketing.accessories WHERE accessories_id = ?";
        $res = $this->db_read->fetchAll($sql, array($accessories_id));
        return $res[0]['cost'] ;
    }

    /**
     * @return array
     */
    public function ordersBackoffice($params)
    {
        $sql = "SELECT a.* ,
                o.e_code order_ecode
                FROM marketing.orders o
                LEFT JOIN marketing.accounts a ON a.accounts_id = o.accounts_id 
                WHERE o.orders_id = ?";
        $res = $this->db_read->fetchAll($sql, array($params['orders_id']));

        $sql = "SELECT * 
                FROM backoffice.contacts
                WHERE request_company LIKE '" . str_replace ( "'" , "\'" , $res[0]['company'] ) . "%'
                OR  request_company LIKE '%" . str_replace ( "'" , "\'" , $res[0]['company'] ) . "%'";
        $result = $this->db_read->fetchAll($sql, array());

        $result['e_code'] = $res[0]['order_ecode'] ;

        return $result ;
    }

    /**
     * @return array
     */
    public function ordersCompanies($rep,$search)
    {
        if($search) {
            $sql = "SELECT ma.*,
                    a.*
                    FROM marketing.accounts ma
                    LEFT JOIN crossbones.account a ON a.account_id = ma.account_id
                    WHERE ma.reps_id = ?
                    AND ( 
                        a.accountname LIKE '" . $search . "%' 
                        OR a.accountname LIKE '%" . $search . "%' 
                        OR ma.company LIKE '" . $search . "%' 
                        OR ma.company LIKE '%" . $search . "%' 
                        OR ma.account_id = ?
                        )
                    ORDER BY a.accountname ASC";
            $res = $this->db_read->fetchAll($sql, array($rep,$search));            
        } else {
            $sql = "SELECT ma.*,
                    a.*
                    FROM marketing.accounts ma
                    LEFT JOIN crossbones.account a ON a.account_id = ma.account_id
                    WHERE ma.reps_id = ?
                    ORDER BY a.accountname ASC";
            $res = $this->db_read->fetchAll($sql, array($rep));            
        }
        return $res ;
    }

    /**
     * @return array
     */
    public function ordersCompany($accounts_id)
    {
        $sql = "SELECT ma.*,
                a.*,
                concat ( u.firstname , ' ' , u.lastname ) as owner_name,
                u.email as email
                FROM marketing.accounts ma
                LEFT JOIN crossbones.account a ON a.account_id = ma.account_id
                LEFT JOIN crossbones.user u ON u.account_id = a.account_id
                WHERE ma.accounts_id = ?
                AND u.roles = ?";
        $res = $this->db_read->fetchAll($sql, array($accounts_id,'ROLE_ACCOUNT_OWNER'));
        return $res[0] ;
    }

    /**
     * @return array
     */
    public function ordersCreateDate($orders_id)
    {
        $i = array('-',':',' ');
        $o = array('','','');
        $sql = "SELECT *
                FROM marketing.orders
                WHERE orders_id = ?";
        $res = $this->db_read->fetchAll($sql, array($orders_id));
        return str_replace ( $i , $o , $res[0]['createdate'] ) ;
    }

    /**
     * @return array
     */
    public function ordersDelete($params)
    {
        $sql = "SELECT *
                FROM marketing.fulfillment
                WHERE fulfillment_id = ?";
        $res = $this->db_read->fetchAll($sql, array($params['fulfillment_user']));

        if($res[0]['read_write']){

            $sql = "UPDATE marketing.orders
                    SET active = ? ,
                    fulfillment_id = ?
                    WHERE orders_id = ?";

            $results = $this->db_read->executeQuery($sql, array('1',$params['fulfillment_user'],$params['orders_id']));

        }

        return $params ;
    }

    /**
     * @return array
     */
    public function ordersDetail($uri)
    {
        $createdate = substr($uri,0,4) . '-' . substr($uri,4,2) . '-' . substr($uri,6,2) . ' ' . substr($uri,8,2) . ':' . substr($uri,10,2) . ':' . substr($uri,12,2);
        $orders_id = substr($uri,14);
        $sql = "SELECT o.*,
                a.company as account_name,
                a.contact as account_contact,
                a.email as account_email,
                a.fax as account_fax,
                a.phone as account_phone,
                ac.cost as accessories_cost,
                ac.nickname as accessories_name,
                f.user as approved_by,
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
                uv.version as version
                FROM marketing.orders o
                LEFT JOIN marketing.accounts a ON a.accounts_id = o.accounts_id
                LEFT JOIN marketing.accessories ac ON ac.accessories_id = o.accessories_id
                LEFT JOIN marketing.fulfillment f ON f.fulfillment_id = o.approve_id
                LEFT JOIN marketing.payment p ON p.payment_id = o.payment_id
                LEFT JOIN marketing.reps r ON r.reps_id = o.reps_id
                LEFT JOIN marketing.shipping ship ON ship.shipping_id = o.shipping_id
                LEFT JOIN marketing.states states ON states.states_id = o.taxes_state
                LEFT JOIN marketing.subscription s ON s.subscription_id = o.subscription_id
                LEFT JOIN unitmanagement.unitversion uv ON uv.unitversion_id = o.unitversion_id
                LEFT JOIN unitmanagement.unitmanufacturer um ON um.unitmanufacturer_id = uv.unitmanufacturer_id
                LEFT JOIN backoffice.m2m_reps m2m ON m2m.m2m_repID = o.m2m_repID
                WHERE o.orders_id = ?
                AND o.createdate = ?";
        $res = $this->db_read->fetchAll($sql, array($orders_id,$createdate));
        return $res[0] ;
    }

    /**
     * @return array
     */
    public function ordersEcode($ecode)
    {
        if($ecode){            
            $sql = "SELECT *
                    FROM backoffice.m2m_reps
                    WHERE e_code = ?";
            $ecode = $this->db_read->fetchAll($sql, array($ecode));
        } else {
            $ecode[0]['e_code'] = '' ;
            $ecode[0]['name'] = '' ;
            $ecode[0]['email'] = '' ;
        }
        return $ecode[0] ;
    }

    /**
     * @return array
     */
    public function ordersEmails($orders_id)
    {
        $sql = "SELECT a.email as customer_email,
                a.contact as customer_name,
                r.email as rep_email,
                r.name as rep_name,
                r.wholesale as wholesale,
                m2m.email as m2m_email,
                m2m.name as m2m_name
                FROM marketing.orders o
                LEFT JOIN marketing.accounts a ON a.accounts_id = o.accounts_id
                LEFT JOIN marketing.reps r ON r.reps_id = o.reps_id
                LEFT JOIN backoffice.m2m_reps m2m ON m2m.m2m_repID = o.m2m_repID
                WHERE orders_id = ?";
        $emails = $this->db_read->fetchAll($sql, array($orders_id));
        return $emails[0] ;
    }

    /**
     * @return array
     */
    public function ordersEmailsAccounts_id($accounts_id)
    {
        $sql = "SELECT a.email as customer_email,
                a.contact as customer_name,
                r.email as rep_email,
                r.name as rep_name
                FROM marketing.accounts a
                LEFT JOIN marketing.reps r ON r.reps_id = a.reps_id
                WHERE a.accounts_id = ?";
        $emails = $this->db_read->fetchAll($sql, array($accounts_id));
        return $emails[0] ;
    }

    /**
     * @return array
     */
    public function ordersHandling($shipping_id,$handling)
    {
        $sql = "SELECT * FROM marketing.shipping WHERE shipping_id = ?";
        $res = $this->db_read->fetchAll($sql, array($shipping_id));
        return $handling + $res[0]['flat'] ;
    }

    /**
     * @return array
     */
    public function ordersOrder($params)
    {

        $confirm = 'Sorry, your order was <b>NOT</b> queued';

        $in = array(',','.');
        $out = array('','');

        if(!($params['m2m_repID'])){
            if($params['e_code']){
                $sql = "SELECT m2m_repID
                        FROM backoffice.m2m_reps
                        WHERE e_code = ?";
                $res = $this->db_read->fetchAll($sql, array($params['e_code']));
                $params['m2m_repID'] = $res[0]['m2m_repID'];
            }
        }
        if(!($params['m2m_repID'])){
            $params['m2m_repID'] = "0";
        }

        $order = array() ;
        $order['accessories_id'] = $params['accessories'] ;
        $order['accounts_id'] = $params['accounts_id'] ;
        $order['address_billing'] = $params['billing'] ;
        $order['address_shipping'] = $params['shipping'] ;
        $order['arate'] = str_replace ( $in , $out , $params['arate'] ) ;
        if($params['cc_onfile_use']=='true'){
            $sql = "SELECT *
                    FROM marketing.accounts
                    WHERE accounts_id = ?
                    AND reps_id = ?";
            $res = $this->db_read->fetchAll($sql, array($params['accounts_id'],$params['reps_id']));
            $params['cc_exp'] = $res[0]['cc_exp'] ;
            $params['cc_num'] = $res[0]['cc_num'] ;
            $params['cc_ver'] = $res[0]['cc_ver'] ;
        }
        $order['cc_exp'] = $params['cc_exp'] ;
        $order['cc_num'] = $params['cc_num'] ;
        $order['cc_ver'] = $params['cc_ver'] ;
        $order['e_code'] = $params['e_code'] ;
        $order['m2m_repID'] = $params['m2m_repID'] ;
        $order['extended_total'] = str_replace ( $in , $out , $params['extended'] ) ;
        $order['grand_total'] = str_replace ( $in , $out , $params['total']  );
        $order['handling_fee'] = str_replace ( $in , $out , $params['handling_fee'] ) ;
        $order['notes'] = $params['notes'] ;
        $order['po'] = $params['po'] ;
        $order['payment_id'] = $params['payment_method'] ;
        $order['quantity'] = str_replace ( $in , $out , $params['quantity'] ) ;
        $order['reps_id'] = $params['reps_id'] ;
        $order['rate'] = str_replace ( $in , $out , $params['rate'] ) ;
        $order['reseller'] = $params['reseller'] ;
        $order['shipping_fee'] = str_replace ( $in , $out , $params['shipping_fee'] ) ;
        $order['shipping_id'] = $params['shipping_method'] ;
        $order['subscription_id'] = $params['plan'] ;
        $order['taxes_amount'] = $params['taxes_amount'] * 100 ;
        $order['taxes_state'] = $params['taxes_state'] ;
        $order['taxes_rate'] = $this->ordersTaxRate($params['taxes_state']) ;
        $order['versions_id'] = $params['product'] ;
        if($order['versions_id']){
            $sql = "SELECT *
                FROM marketing.versions
                WHERE versions_id = ?";
            $res = $this->db_read->fetchAll($sql, array($order['versions_id']));
            $order['unitversion_id'] = $res[0]['unitversion_id'] ;
        }

        $order['override_rate'] = str_replace ( $in , $out , $params['rate_override'] ) ;
        $order['override_arate'] = str_replace ( $in , $out , $params['arate_override'] ) ;
        $order['override_shipping'] = str_replace ( $in , $out , $params['shipping_override'] ) ;
        $order['override_handling'] = str_replace ( $in , $out , $params['handling_override'] ) ;
        $order['override_reason'] = $params['override_reason'] ;
        $order['override_extended'] = str_replace ( $in , $out , $params['extended_override'] ) ;
        $order['override_total'] = str_replace ( $in , $out , $params['total_override']  );

        if ($this->db_write->insert('marketing.orders', $order)) {

            if(!($params['cc_num'])){
                $sql = "SELECT *
                        FROM marketing.accounts
                        WHERE accounts_id = ?
                        AND reps_id = ?";
                $res = $this->db_read->fetchAll($sql, array($params['accounts_id'],$params['reps_id']));
                $params['cc_exp'] = $res[0]['cc_exp'] ;
                $params['cc_num'] = $res[0]['cc_num'] ;
                $params['cc_ver'] = $res[0]['cc_ver'] ;
            }

            if($params['billing']=='undefined') {   $params['billing'] = '' ; }
            if($params['cc_exp']=='undefined') {    $params['cc_exp'] = '' ; }
            if($params['cc_num']=='undefined') {    $params['cc_num'] = '' ; }
            if($params['cc_ver']=='undefined') {    $params['cc_ver'] = '' ; }
            if($params['company']=='undefined') {   $params['company'] = '' ; }
            if($params['contact']=='undefined') {   $params['contact'] = '' ; }
            if($params['e_code']=='undefined') {    $params['e_code'] = '' ; }
            if($params['m2m_repID']=='undefined') { $params['m2m_repID'] = '' ; }
            if($params['email']=='undefined') {     $params['email'] = '' ; }
            if($params['phone']=='undefined') {     $params['phone'] = '' ; }
            if($params['fax']=='undefined') {       $params['fax'] = '' ; }
            if($params['reseller']=='undefined') {  $params['reseller'] = '' ; }
            if($params['shipping']=='undefined') {  $params['shipping'] = '' ; }
            if($params['taxes_state']=='undefined') {  $params['taxes_state'] = '' ; }

            $sql = "UPDATE marketing.accounts
                    SET address_billing = ? ,
                    address_shipping = ? ,
                    cc_exp = ? ,
                    cc_num = ? ,
                    cc_ver = ? ,
                    company = ? ,
                    reseller = ? ,
                    taxes_state = ? ,
                    phone = ? ,
                    fax = ? ,
                    contact = ? ,
                    email = ? ,
                    e_code = ? ,
                    m2m_repID = ?
                    WHERE accounts_id = ?
                    AND reps_id = ?";

            $results = $this->db_read->executeQuery($sql, array($params['billing'],$params['shipping'],$params['cc_exp'],$params['cc_num'],$params['cc_ver'],$params['company'],$params['reseller'],$params['taxes_state'],$params['phone'],$params['fax'],$params['contact'],$params['email'],$params['e_code'],$params['m2m_repID'],$params['accounts_id'],$params['reps_id']));

            $sql = "SELECT orders_id 
                    FROM marketing.orders
                    WHERE accounts_id = ?
                    ORDER BY createdate DESC
                    LIMIT 1";
            $res = $this->db_read->fetchAll($sql, array($params['accounts_id']));
                                    
            $confirm = '<span class="text-bold text-green text-18">SUCCESS</span>: Order #<span class="text-bold text-18">' . $res[0]['orders_id'] . '</span> has been Queued for Fulfillment';

        }

        return $confirm ;

    }

    /**
     * @return array
     */
    public function ordersOptions($value,$ecode)
    {
        $res = array();
        switch ($value) {
            case    'accessories' : $sql = "SELECT * 
                                            FROM marketing.accessories
                                            ORDER BY rank ASC , nickname DESC";
                                    $res = $this->db_read->fetchAll($sql, array());
                                    break;
            case            'dms' : $sql = "SELECT * 
                                            FROM marketing.dms
                                            WHERE dms_id != ?
                                            ORDER BY dms ASC";
                                    $res = $this->db_read->fetchAll($sql, array(1));
                                    break;
           case 'payment_methods' : $sql = "SELECT * 
                                            FROM marketing.payment
                                            WHERE active != 1
                                            ORDER BY payment_method ASC";
                                    $res = $this->db_read->fetchAll($sql, array());
                                    break;
            case          'plans' : $sql = "SELECT * 
                                            FROM marketing.subscription
                                            WHERE active != 1
                                            ORDER BY subscription_id ASC";
                                    $res = $this->db_read->fetchAll($sql, array());
                                    break;
          case 'shipping_methods' : $sql = "SELECT * 
                                            FROM marketing.shipping
                                            WHERE active != 1
                                            ORDER BY cost DESC";
                                    $res = $this->db_read->fetchAll($sql, array());
                                    break;
            case         'states' : $sql = "SELECT * 
                                            FROM marketing.states
                                            ORDER BY statename ASC";
                                    $res = $this->db_read->fetchAll($sql, array());
                                    break;
            case      'timezones' : $sql = "SELECT * 
                                            FROM unitmanagement.timezone
                                            WHERE active = ?
                                            ORDER BY timezone ASC";
                                    $res = $this->db_read->fetchAll($sql, array(1));
                                    break;
            case       'versions' : if($ecode){
                                        $sql = "SELECT umuv.*,
                                                umsp.simprovider,
                                                umum.manufacturer,
                                                mv.nickname,
                                                mv.price5,
                                                mv.price4,
                                                mv.price3,
                                                mv.price2,
                                                mv.price1,
                                                mv.versions_id
                                                FROM unitmanagement.unitversion umuv
                                                LEFT JOIN unitmanagement.unitmanufacturer umum ON umum.unitmanufacturer_id = umuv.unitmanufacturer_id  
                                                LEFT JOIN marketing.versions mv ON mv.unitversion_id = umuv.unitversion_id  
                                                LEFT JOIN unitmanagement.simprovider umsp ON umsp.simprovider_id = mv.simprovider_id  
                                                WHERE mv.unitversion_id IS NOT NULL
                                                AND umsp.simprovider_id = 3";
                                        $res = $this->db_read->fetchAll($sql, array());
                                    } else {
                                        $sql = "SELECT umuv.*,
                                                umsp.simprovider,
                                                umum.manufacturer,
                                                mv.nickname,
                                                mv.price5,
                                                mv.price4,
                                                mv.price3,
                                                mv.price2,
                                                mv.price1,
                                                mv.versions_id
                                                FROM unitmanagement.unitversion umuv
                                                LEFT JOIN unitmanagement.unitmanufacturer umum ON umum.unitmanufacturer_id = umuv.unitmanufacturer_id  
                                                LEFT JOIN marketing.versions mv ON mv.unitversion_id = umuv.unitversion_id  
                                                LEFT JOIN unitmanagement.simprovider umsp ON umsp.simprovider_id = mv.simprovider_id  
                                                WHERE mv.unitversion_id IS NOT NULL";
                                        $res = $this->db_read->fetchAll($sql, array());
                                    }
                                    break;
        }
        return $res ;
    }

    /**
     * @return array
     */
    public function ordersOverride($rep)
    {
        $sql = "SELECT * FROM marketing.reps WHERE reps_id = ?";
        $res = $this->db_read->fetchAll($sql, array($rep));
        return $res[0]['override'] ;
    }

    /**
     * @return array
     */
    public function ordersRecent($accounts_id)
    {
        $sql = "SELECT * 
                FROM marketing.orders 
                WHERE accounts_id = ?
                ORDER BY createdate DESC
                LIMIT 1";
        $res = $this->db_read->fetchAll($sql, array($accounts_id));
        return $res[0] ;
    }

    /**
     * @return array
     */
    public function ordersRate($product,$plan)
    {
        $sql = "SELECT * FROM marketing.versions WHERE versions_id = ?";
        $res = $this->db_read->fetchAll($sql, array($product));
        if(!($plan)){
            $plan=3;
        }
        $buffer = 'price' . $plan;
        return $res[0][$buffer] + 0 ;
    }

    /**
     * @return array
     */
    public function ordersRep($post)
    {
        $sql = "SELECT * FROM marketing.reps WHERE email = ? AND password = ? LIMIT 1";
        $res = $this->db_read->fetchAll($sql, array($post['params']['rep-email'],$post['params']['rep-password']));
        return $res[0] ;
    }

    /**
     * @return array
     */
    public function ordersShipping($shipping_id,$quantity)
    {
        $sql = "SELECT * FROM marketing.shipping WHERE shipping_id = ?";
        $res = $this->db_read->fetchAll($sql, array($shipping_id));
        return $res[0]['cost'] * $quantity ;
    }

    /**
     * @return array
     */
    public function ordersTaxRate($states_id)
    {
        $sql = "SELECT * 
                FROM marketing.states 
                WHERE states_id = ?";

        $res = $this->db_read->fetchAll($sql, array($states_id));

        return $res[0]['taxrate'] ;
    }

    /**
     * @return array
     */
    public function ordersUpdate($params)
    {
        $sql = "SELECT * 
                FROM marketing.orders
                WHERE orders_id = ?";

        $res = $this->db_read->fetchAll($sql, array($params['orders_id']));

        if($res[0]){

            if($params['field'] == 'inventory'){
                $res[0]['inventory'] = $params['value'] ;
            }

            if($params['field'] == 'invoice_number'){
                $res[0]['invoice_number'] = $params['value'] ;
            }

            if($params['field'] == 'shipping_track'){
                $res[0]['shipping_track'] = $params['value'] ;
            }

            $sql = "UPDATE marketing.orders
                    SET inventory = ? ,
                    invoice_number = ? ,
                    shipping_track = ?
                    WHERE orders_id = ?";

            $this->db_read->executeQuery($sql, array($res[0]['inventory'],$res[0]['invoice_number'],$res[0]['shipping_track'],$params['orders_id']));

        }

        $sql = "SELECT * 
                FROM marketing.orders
                WHERE orders_id = ?";

        $res = $this->db_read->fetchAll($sql, array($params['orders_id']));

        $result['read_write'] = $params['field'] . '-' . $params['orders_id'] ;
        $result['value'] = $res[0][$params['field']] ;

        return $result ;

    }

    /**
     * @return array
     */
    public function ordersUpdateState($params)
    {
        $sql = "SELECT * 
                FROM marketing.states
                WHERE states_id = ?";

        $res = $this->db_read->fetchAll($sql, array($params['states_id']));

        if($res[0]){

            $res[0]['taxrate'] = $params['value'] * 100 ;

            $sql = "UPDATE marketing.states
                    SET taxrate = ?,
                    fulfillment_id = ?
                    WHERE states_id = ?";

            $this->db_read->executeQuery($sql, array($res[0]['taxrate'],$params['fulfillment_user'],$params['states_id']));

        }

        $sql = "SELECT * 
                FROM marketing.states
                WHERE states_id = ?";

        $res = $this->db_read->fetchAll($sql, array($params['states_id']));

        $result['states'] = 'states-' . $params['states_id'] ;
        $result['value'] = number_format( $res[0]['taxrate'] / 100 , 2 , '.' , ',' ) ;

        return $result ;

    }

    /**
     * @return array
     */
    public function ordersUpdateTerms($params)
    {
        $sql = "SELECT * 
                FROM marketing.accounts
                WHERE accounts_id = ?";

        $res = $this->db_read->fetchAll($sql, array($params['accounts_id']));

        if($res[0]){

            if($params['field'] == 'credit_check'){
                $res[0]['credit_check'] = $params['checked'] ;
            }

            if($params['field'] == 'credit_cod'){
                $res[0]['credit_cod'] = $params['checked'] ;
            }

            if($params['field'] == 'credit_terms'){
                $res[0]['credit_terms'] = $params['checked'] ;
            }

            $sql = "UPDATE marketing.accounts
                    SET credit_check = ?,
                    credit_cod = ?,
                    credit_terms = ?,
                    fulfillment_id = ?
                    WHERE accounts_id = ?";

            $this->db_read->executeQuery($sql, array($res[0]['credit_check'],$res[0]['credit_cod'],$res[0]['credit_terms'],$params['fulfillment_user'],$params['accounts_id']));

        }

        $sql = "SELECT * 
                FROM marketing.accounts
                WHERE accounts_id = ?";

        $res = $this->db_read->fetchAll($sql, array($params['accounts_id']));

        $result['payment'] = 'payment-' . $params['field'] . '-' . $params['accounts_id'] ;

        $result['checked'] = $res[0][$params['field']] ;

        return $result ;

    }

    /**
     * @return array
     */
    public function ordersUm($reps_id,$account_id)
    {
        $sql = "SELECT ca.accountname,
                ma.account_id,
                ma.company
                FROM marketing.accounts ma 
                LEFT JOIN crossbones.account ca ON ca.account_id = ma.account_id
                WHERE ma.account_id = ?";
        $res = $this->db_read->fetchAll($sql, array($account_id));
        if($res){
            if(!($res[0]['company'])){
                $res[0]['company'] = $res[0]['accountname'] ;
            }
            return '<b>' . $res[0]['company'] . '</b> [' . $res[0]['account_id'] . '] has been claimed' ;
        } else {
            $sql = "SELECT a.account_id,
                    a.accountname
                    FROM marketing.reps r
                    LEFT JOIN crossbones.account a ON a.dealer_id = r.dealer_id
                    WHERE r.reps_id = ?
                    AND ( a.account_id = ? OR a.accountname LIKE '" . $account_id . "%' )";
            $res = $this->db_read->fetchAll($sql, array($reps_id,$account_id));
            if($res[0]['account_id']){
                if(!($res[0]['accountname'])){
                    return '<a class="center orders-um" href="javascript:void(0);" data-value="' . $res[0]['account_id'] . '">Account #' . $res[0]['account_id'] . '</a>' ;
                } else {
                    return '<a class="center orders-um" href="javascript:void(0);" data-value="' . $res[0]['account_id'] . '"><b>' . $res[0]['accountname'] . '</b> [' . $res[0]['account_id'] . ']</a>' ;
                }
            } else {
                return '' ;
            }
        }
    }

    /**
     * @return array
     */
    public function ordersUmAdd($reps_id,$account_id)
    {
        $accounts = array() ;
        $accounts['reps_id'] = $reps_id ;
        $accounts['account_id'] = $account_id ;
        if ($this->db_write->insert('marketing.accounts', $accounts)) {
            $sql = "SELECT account_id
                    FROM marketing.accounts
                    WHERE reps_id = ?
                    AND account_id = ?";
            $res = $this->db_read->fetchAll($sql, array($reps_id,$account_id));
            return $res[0]['account_id'] ;
        }
        return false ;
    }

}
