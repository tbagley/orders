<?php

namespace Controllers\Ajax;

use GTC\Component\Utils\Date;
use GTC\Component\Utils\Dropdown;
use GTC\Component\Utils\CSV\CSVBuilder;
use GTC\Component\Map\Tiger;
use GTC\Component\Utils\VinDecoder;

use Models\Logic\AddressLogic;

use Models\Data\AccountData;
use Models\Logic\AccountLogic;

use Models\Data\AlertData;
use Models\Logic\AlertLogic;

use Models\Data\ContactData;
use Models\Logic\ContactLogic;

use Models\Data\OrdersData;
use Models\Logic\OrdersLogic;

use Models\Data\TerritoryData;
use Models\Logic\TerritoryLogic;

use Models\Logic\UnitCommandLogic;

use Models\Data\UserData;
// use Models\Logic\UserData;
use Models\Logic\UserLogic;

use Models\Data\UnitData;
use Models\Logic\UnitLogic;

use Models\Data\VehicleData;
use Models\Logic\VehicleLogic;

use Symfony\Component\HttpFoundation\Request;

use GTC\Component\Utils\PDF\TCPDFBuilder;


/**
 * Class Vehicle
 *
 */
class Orders extends BaseAjax
{    
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->account_data = new AccountData;
        $this->account_logic = new AccountLogic;
        $this->address_logic = new AddressLogic;
        $this->alert_data = new AlertData;
        $this->alert_logic = new AlertLogic;
        $this->contact_data = new ContactData;
        $this->contact_logic = new ContactLogic;
        $this->orders_data = new OrdersData;
        $this->orders_logic = new OrdersLogic;
        $this->territory_data = new TerritoryLogic;
        $this->territory_logic = new TerritoryLogic;
        $this->unitcommand_logic = new UnitCommandLogic;
        $this->user_logic = new UserLogic;
        $this->unit_data = new UnitData;
        $this->unit_logic = new UnitLogic;
        $this->vehicle_data = new VehicleData;
        $this->vehicle_logic = new VehicleLogic;

    }

    /**
     * checkEmailFormat
     *
     * @return void
     */
    public function ajax()
    {

        $ajax_data  = array();

        $ajax_data['code'] = '1';

        $post       = $this->request->request->all();

        $ajax_data['params'] = $post['params'];

        switch ($post['pid']) {

            case   'backoffice-check' : $ajax_data['message'] = $post['pid'];
                                        $ajax_data['code'] = '0';
                                        $ajax_data['backoffice'] = $this->orders_logic->ordersBackoffice($post['params']);
                                        break;

            case  'customer-customer' : $ajax_data['message'] = $post['pid'];
                                        if($post['params']['selection']){
                                            $ajax_data['code'] = '0';
                                            $ajax_data['company'] = $this->orders_logic->ordersCompany($post['params']['selection']);
                                            $ajax_data['order_recent'] = $this->orders_logic->ordersRecent($ajax_data['company']['accounts_id']);
                                            if($ajax_data['order_recent']){
                                                $ajax_data['order_recent']['rate'] = number_format( $ajax_data['order_recent']['rate'] / 100 , 2, '.', ',' ) ;
                                                $ajax_data['order_recent']['arate'] = number_format( $ajax_data['order_recent']['arate'] / 100 , 2, '.', ',' ) ;
                                                $ajax_data['order_recent']['shipping_fee'] = number_format( $ajax_data['order_recent']['shipping_fee'] / 100 , 2, '.', ',' ) ;
                                                $ajax_data['order_recent']['handling_fee'] = number_format( $ajax_data['order_recent']['handling_fee'] / 100 , 2, '.', ',' ) ;
                                                $ajax_data['order_recent']['override_rate'] = number_format( $ajax_data['order_recent']['override_rate'] / 100 , 2, '.', ',' ) ;
                                                $ajax_data['order_recent']['override_arate'] = number_format( $ajax_data['order_recent']['override_arate'] / 100 , 2, '.', ',' ) ;
                                                $ajax_data['order_recent']['override_shipping'] = number_format( $ajax_data['order_recent']['override_shipping'] / 100 , 2, '.', ',' ) ;
                                                $ajax_data['order_recent']['override_handling'] = number_format( $ajax_data['order_recent']['override_handling'] / 100 , 2, '.', ',' ) ;
                                            }
                                            $ajax_data['vzw_rep'] = $this->orders_logic->ordersEcode($ajax_data['company']['e_code']);
                                            if($ajax_data['company']['cc_num']){
                                                $ajax_data['company']['cc_onfile'] = '************' . substr($this->orders_logic->ordersDecrypt($ajax_data['company']['cc_num']), -4) ;
                                            }
                                            $ajax_data['company']['cc_num'] = null ;
                                            $ajax_data['company']['cc_ver'] = null ;
                                            $ajax_data['company']['cc_exp'] = null ;
                                            $payment_methods = $this->orders_logic->ordersOptions('payment_methods');
                                            foreach ( $payment_methods as $k => $v ) {
                                                switch($v['payment_id']){
                                                    case             3  :
                                                    case            '3' : if($ajax_data['company']['credit_check']!='f'){
                                                                            $ajax_data['payment_methods'][] = $v ;
                                                                          }
                                                                          break;
                                                    case             4  :
                                                    case            '4' :
                                                    case             8  :
                                                    case            '8' :
                                                    case             9  :
                                                    case            '9' :
                                                    case            10  :
                                                    case           '10' : if($ajax_data['company']['credit_terms']=='t'){
                                                                            $ajax_data['payment_methods'][] = $v ;
                                                                          }
                                                                          break;
                                                    case             6  :
                                                    case            '6' : if($ajax_data['company']['credit_cod']!='t'){
                                                                            $ajax_data['payment_methods'][] = $v ;
                                                                          }
                                                    case             7  :
                                                    case            '7' : if($ajax_data['company']['credit_cod']=='t'){
                                                                            $ajax_data['payment_methods'][] = $v ;
                                                                          }
                                                                          break;
                                                                default : $ajax_data['payment_methods'][] = $v ;
                                                }
                                            }
                                            $ajax_data['versions'] = $this->orders_logic->ordersOptions('versions',$ajax_data['company']['e_code']);
                                            foreach ( $ajax_data['versions'] as $k => $v ) {
                                                $ajax_data['versions'][$k]['price5'] = '$' . number_format( $v['price5'] / 100 , 2, '.', ',' ) ;
                                                $ajax_data['versions'][$k]['price4'] = '$' . number_format( $v['price4'] / 100 , 2, '.', ',' ) ;
                                                $ajax_data['versions'][$k]['price3'] = '$' . number_format( $v['price3'] / 100 , 2, '.', ',' ) ;
                                                $ajax_data['versions'][$k]['price2'] = '$' . number_format( $v['price2'] / 100 , 2, '.', ',' ) ;
                                                $ajax_data['versions'][$k]['price1'] = '$' . number_format( $v['price1'] / 100 , 2, '.', ',' ) ;
                                            }
                                        }
                                        break;

            case       'customer-new' : $ajax_data['message'] = $post['pid'];
                                        $ajax_data['code'] = '0';
                                        $payment_methods = $this->orders_logic->ordersOptions('payment_methods');
                                        foreach ( $payment_methods as $k => $v ) {
                                            switch($v['payment_id']){
                                                case             3  :
                                                case            '3' : if($ajax_data['company']['credit_check']!='f'){
                                                                        $ajax_data['payment_methods'][] = $v ;
                                                                      }
                                                                      break;
                                                case             4  :
                                                case            '4' :
                                                case             8  :
                                                case            '8' :
                                                case             9  :
                                                case            '9' :
                                                case            10  :
                                                case           '10' : if($ajax_data['company']['credit_terms']=='t'){
                                                                        $ajax_data['payment_methods'][] = $v ;
                                                                      }
                                                                      break;
                                                case             6  :
                                                case            '6' : if($ajax_data['company']['credit_cod']!='t'){
                                                                        $ajax_data['payment_methods'][] = $v ;
                                                                      }
                                                case             7  :
                                                case            '7' : if($ajax_data['company']['credit_cod']=='t'){
                                                                        $ajax_data['payment_methods'][] = $v ;
                                                                      }
                                                                      break;
                                                            default : $ajax_data['payment_methods'][] = $v ;
                                            }
                                        }
                                        $ajax_data['versions'] = $this->orders_logic->ordersOptions('versions',$post['params']['e_code']);
                                        foreach ( $ajax_data['versions'] as $k => $v ) {
                                            $ajax_data['versions'][$k]['price5'] = '$' . number_format( $v['price5'] / 100 , 2, '.', ',' ) ;
                                            $ajax_data['versions'][$k]['price4'] = '$' . number_format( $v['price4'] / 100 , 2, '.', ',' ) ;
                                            $ajax_data['versions'][$k]['price3'] = '$' . number_format( $v['price3'] / 100 , 2, '.', ',' ) ;
                                            $ajax_data['versions'][$k]['price2'] = '$' . number_format( $v['price2'] / 100 , 2, '.', ',' ) ;
                                            $ajax_data['versions'][$k]['price1'] = '$' . number_format( $v['price1'] / 100 , 2, '.', ',' ) ;
                                        }
                                        break;

            case  'customer-vzwecode' : $ajax_data['message'] = $post['pid'];
                                        $ajax_data['code'] = '0';
                                        $ajax_data['vzw_rep'] = $this->orders_logic->ordersEcode($post['params']['e_code']);
                                        if ( ! ( $ajax_data['vzw_rep'] ) ) {
                                            $ajax_data['vzw_rep']['e_code'] = '' ;
                                            $ajax_data['vzw_rep']['email'] = '' ;
                                            $ajax_data['vzw_rep']['name'] = '' ;
                                        }
                                        break;

            case              'login' : $ajax_data['message'] = $post['pid'];
                                        $fulfillment = $this->orders_logic->fulfillmentUser($post);
                                        $ajax_data['fulfillment_user'] = $fulfillment['fulfillment_id'] ; 
                                        if($ajax_data['fulfillment_user']){
                                            $ajax_data['fulfillment_accounting']    = $fulfillment['accounting'] ; 
                                            $ajax_data['fulfillment_activations']   = $fulfillment['activations'] ; 
                                            $ajax_data['fulfillment_approved']      = $fulfillment['approved'] ; 
                                            $ajax_data['fulfillment_credit']        = $fulfillment['credit'] ; 
                                            $ajax_data['fulfillment_inventoried']   = $fulfillment['inventoried'] ; 
                                            $ajax_data['fulfillment_invoiced']      = $fulfillment['invoiced'] ; 
                                            $ajax_data['fulfillment_labeled']       = $fulfillment['labeled'] ; 
                                            $ajax_data['fulfillment_pending']       = $fulfillment['pending'] ; 
                                            $ajax_data['fulfillment_read_only']     = $fulfillment['read_only'] ; 
                                            $ajax_data['fulfillment_read_write']    = $fulfillment['read_write'] ; 
                                            $ajax_data['fulfillment_shipped']       = $fulfillment['shipped'] ; 
                                            $ajax_data['code'] = '0';
                                            $ajax_data['message'] .= ', USER ID #' . $ajax_data['fulfillment_user'];
                                        } else {
                                            $ajax_data['message'] = "Sorry, your credentials do not match our records... \r\nPlease try again";
                                        }
                                        break;

            case              'order' : $ajax_data['message'] = $post['pid'];
                                        $ajax_data['code'] = '0';
                                        $ajax_data['params'] = $post;
                                        $result['error'][] = 'no attempt';
                                        if(!($post['params']['accounts_id'])){
                                            $result = $this->createNewAccount($post['params']['account']);
                                            if($result['create']['account_id']){
                                                $post['params']['accounts_id'] = $this->orders_logic->createNewAccounts($post['params'],$result['create']['account_id']);
                                                $this->orders_logic->createNewAccountsNotice($post);
                                            } else {
                                                $results = $result;
                                                if(!($result['error'])){
                                                    // $result['error'][] = '<u>Attempting</u>: ' . implode( ', ' , $result['attempting'] );
                                                    // $result['error'][] = '<hr><u>Parameters</u><br>' ;
                                                    // foreach ($post['params']['account'] as $k => $v) {
                                                    //     $result['error'][] = $k . '=' . $v . '<br>';
                                                    // }
                                                    $result['error'][] = 'REASON: ' . $results['create']['message'] ;
                                                }
                                                $ajax_data['error'] = $result['error'] ;
                                            }
                                        }
                                        if($post['params']['accounts_id']){
                                            $ajax_data['confirm'] = $this->orders_logic->ordersOrder($post['params'],$newAccount);
                                        } else {
                                            $ajax_data['confirm'] = '<a class="navigation" href="javascript:void(0);" id="customer">Your Order Could Not Be Processed... please click here to edit and try again</a>';
                                        }
                                        break;

            case       'order-delete' : $ajax_data['message'] = $post['pid'];
                                        $ajax_data['code'] = '0';
                                        $ajax_data['params'] = $post;
                                        $ajax_data['result'] = $this->orders_logic->ordersDelete($post['params']);
                                        break;

            case            'payment' : $ajax_data['message'] = $post['pid'];
                                        $ajax_data['code'] = '0';
                                        $ajax_data['params'] = $post;
                                        $result = $this->orders_logic->ordersUpdateTerms($post['params']);
                                        $ajax_data['payment'] = $result['payment'];
                                        $ajax_data['checked'] = $result['checked'];
                                        break;

            case              'quote' : $ajax_data['message'] = $post['pid'];
                                        $ajax_data['code'] = '0';

                                        $ajax_data['quote']['override'] = $post['params']['override'];
                                        
                                        switch($post['params']['payment-method']){
                                            case                             '6' :  $handling = 1200 ;
                                                                                    break;
                                                                         default :  $handling = 0 ;
                                        }

                                        if($post['params']['quantity']>0){
                                            $quantity = $post['params']['quantity'];
                                        } else {
                                            $quantity = 10 ;
                                        }

                                        if($post['params']['plan']>0){
                                            $plan = $post['params']['plan'];
                                        } else {
                                            $plan = 3 ;
                                        }

                                        $product = $post['params']['product'];

                                        $rate = $this->orders_logic->ordersRate($product,$plan);
                                        $rate_override                              = $post['params']['rate-override'] * 100;

                                        $arate = $this->orders_logic->ordersAccessories($post['params']['accessories-id'],$quantity);
                                        $arate_override                             = $post['params']['arate-override'] * 100;

                                        $shipping = $this->orders_logic->ordersShipping($post['params']['shipping-id'],1);
                                        $shipping_override                          = $post['params']['shipping-override'] * 100;

                                        $handling = $this->orders_logic->ordersHandling($post['params']['shipping-id'],$handling);
                                        $handling_override                          = $post['params']['handling-override'] * 100;

                                        if($post['params']['reseller']){
                                            $taxrate = 0;
                                            $ajax_data['quote']['extended_taxrate']                 = '0% RESALE';
                                        } else {
                                            $taxrate = $this->orders_logic->ordersTaxRate($post['params']['shipping-state']);
                                            $ajax_data['quote']['extended_taxrate']                 = number_format( $taxrate / 100 , 2, '.', ',' ) . '%';
                                        }
                                        
                                        $extended                                               = ( $rate * $quantity ) + ( $arate * $quantity ) + $handling ;
                                        $extended_taxes                                         = $extended * $taxrate / 100 / 100 ;
                                        $extended_subtotal                                      = $extended + $extended_taxes ;
                                        $extended_shipping                                      = $shipping * $quantity ;
                                        $total                                                  = $extended_subtotal + $extended_shipping ;

                                        $ajax_data['quote']['quantity'] = $quantity ;

                                        $ajax_data['quote']['shipping_fee']         = number_format( $shipping / 100 , 2, '.', ',' ) ;
                                        $ajax_data['quote']['shipping_override']    = number_format( $shipping_override / 100 , 2, '.', ',' ) ;

                                        $ajax_data['quote']['handling_fee']         = number_format( $handling / 100 , 2, '.', ',' ) ;
                                        $ajax_data['quote']['handling_override']    = number_format( $handling_override / 100 , 2, '.', ',' ) ;

                                        $ajax_data['quote']['rate']                 = number_format( $rate / 100 , 2, '.', ',' ) ;
                                        $ajax_data['quote']['arate']                = number_format( ( $arate / 100 ) , 2, '.', ',' ) ;
                                        $ajax_data['quote']['extended']             = number_format( ( $extended ) / 100 , 2, '.', ',' ) ;
                                        $ajax_data['quote']['total']                = number_format( $total / 100 , 2, '.', ',' ) ;
                                        
                                        $ajax_data['quote']['rate_override']        = number_format( $rate_override / 100 , 2, '.', ',' ) ;
                                        $ajax_data['quote']['arate_override']       = number_format( $arate_override / 100 , 2, '.', ',' ) ;

                                        if($post['params']['rate-override']==''){
                                            $rate_override = $rate ;
                                            $ajax_data['quote']['savings_rate']     = null ;
                                        } else {
                                            $ajax_data['quote']['savings_rate']     = number_format( ( $rate - $rate_override ) / 100 , 2, '.', ',' ) ;
                                            $override = 1 ;
                                        }
                                        $ajax_data['quote']['rate_override']    = number_format( $rate_override / 100 , 2, '.', ',' ) ;

                                        if($post['params']['arate-override']==''){
                                            $arate_override = $arate ;
                                            $ajax_data['quote']['savings_arate']    = null ;
                                        } else {
                                            $ajax_data['quote']['savings_arate']    = number_format( ( $arate - $arate_override ) / 100 , 2, '.', ',' ) ;
                                            $override = 1 ;
                                        }
                                        $ajax_data['quote']['arate_override']   = number_format( $arate_override / 100 , 2, '.', ',' ) ;

                                        if($post['params']['shipping-override']==''){
                                            $shipping_override = $shipping ;
                                            $ajax_data['quote']['savings_shipping'] = null ;
                                        } else {
                                            if($shipping_override=='0.00'){
                                                $shipping_override = $shipping ;
                                            }
                                            $ajax_data['quote']['savings_shipping'] = number_format( ( $shipping - $shipping_override ) / 100 , 2, '.', ',' ) ;
                                            $override = 1 ;
                                        }
                                        $ajax_data['quote']['shipping_override'] = number_format( $shipping_override / 100 , 2, '.', ',' ) ;

                                        if($post['params']['handling-override']==''){
                                            $handling_override = $handling ;
                                            $ajax_data['quote']['savings_handling'] = null ;
                                        } else {
                                            // if($handling_override=='0.00'){
                                            //     $handling_override = $handling ;
                                            // }
                                            $ajax_data['quote']['savings_handling'] = number_format( ( $handling - $handling_override ) / 100 , 2, '.', ',' ) ;
                                            $override = 1 ;
                                        }
                                        $ajax_data['quote']['handling_override'] = number_format( $handling_override / 100 , 2, '.', ',' ) ;

                                        $ajax_data['quote']['extended_taxes']                   = number_format( $extended_taxes / 100 , 2, '.', ',' ) ;
                                        $ajax_data['quote']['extended_subtotal']                = number_format( $extended_subtotal / 100 , 2, '.', ',' ) ;
                                        $ajax_data['quote']['extended_shipping']                = number_format( $extended_shipping / 100 , 2, '.', ',' ) ;
                                        
                                        if($override){
                                            $extended_override                                  = ( $rate_override * $quantity ) + ( $arate_override * $quantity ) + $handling_override ;
                                            $ajax_data['quote']['extended_override']            = number_format( $extended_override / 100 , 2, '.', ',' ) ;
                                            $extended_taxes_override                            = ( ( $rate_override * $quantity ) + ( $arate_override * $quantity ) + $handling_override ) * ( $taxrate / 100 / 100 ) ;
                                            $extended_subtotal_override                         = ( $rate_override * $quantity ) + ( $arate_override * $quantity ) + $handling_override + $extended_taxes_override ;
                                            $extended_shipping_override                         = $shipping_override * $quantity ;
                                            $ajax_data['quote']['extended_taxrate_override']    = $ajax_data['quote']['extended_taxrate'] ;
                                        } else {
                                            $extended_override                                  = $extended ;
                                            $extended_taxes_override                            = $extended_taxes ;
                                            $extended_subtotal_override                         = $extended_subtotal ;
                                            $extended_shipping_override                         = $extended_shipping ;
                                            $ajax_data['quote']['extended_taxrate_override']    = null ;
                                            $ajax_data['quote']['diff_total']                   = null ;
                                        }
                                        $ajax_data['quote']['extended_taxes_override']          = number_format( $extended_taxes_override / 100 , 2, '.', ',' ) ;
                                        $ajax_data['quote']['extended_subtotal_override']       = number_format( $extended_subtotal_override / 100 , 2, '.', ',' ) ;
                                        $ajax_data['quote']['extended_shipping_override']       = number_format( $extended_shipping_override / 100 , 2, '.', ',' ) ;
                                        $total_override                                         = $extended_subtotal_override + $extended_shipping_override ;
                                        $ajax_data['quote']['total_override']                   = number_format( ( $total_override ) / 100 , 2, '.', ',' ) ;

                                        $ajax_data['quote']['diff_rate']                        = null ;
                                        $ajax_data['quote']['diff_arate']                       = null ;
                                        $ajax_data['quote']['diff_handling']                    = null ;
                                        $ajax_data['quote']['diff_shipping']                    = null ;
                                        $ajax_data['quote']['diff_extended']                    = null ;
                                        $ajax_data['quote']['diff_subtotal']                    = null ;
                                        $ajax_data['quote']['diff_shipping_extended']           = null ;
                                        $ajax_data['quote']['diff_taxes']                       = null ;
                                        $ajax_data['quote']['diff_total']                       = null ;
                                        if($override){
                                            if($rate != $rate_override){
                                                $ajax_data['quote']['diff_rate']                = number_format( ( ( $rate - $rate_override ) / $rate ) * 100 , 2, '.', ',' ) . '% Discount' ;
                                            }
                                            if($arate != $arate_override){
                                                $ajax_data['quote']['diff_arate']               = number_format( ( ( $arate - $arate_override ) / $arate ) * 100 , 2, '.', ',' ) . '% Discount' ;
                                            }
                                            if($handling != $handling_override){
                                                $ajax_data['quote']['diff_handling']            = number_format( ( ( $handling - $handling_override ) / $handling ) * 100 , 2, '.', ',' ) . '% Discount' ;
                                            }
                                            if($shipping != $shipping_override){
                                                $ajax_data['quote']['diff_shipping']            = number_format( ( ( $shipping - $shipping_override ) / $shipping ) * 100 , 2, '.', ',' ) . '% Discount' ;
                                                $ajax_data['quote']['diff_shipping_extended']   = '$' . number_format( ( $shipping - $shipping_override ) * $quantity / 100 , 2, '.', ',' ) . ' Savings' ;
                                            }
                                            if($extended != $extended_override){
                                                $ajax_data['quote']['diff_extended']            = '$' . number_format( ( $extended - $extended_override ) / 100 , 2, '.', ',' ) . ' Savings' ;
                                            }
                                            if($extended_subtotal != $extended_subtotal_override){
                                                $ajax_data['quote']['diff_subtotal']            = '$' . number_format( ( $extended_subtotal - $extended_subtotal_override ) / 100 , 2, '.', ',' ) . ' Savings' ;
                                            }
                                            if($extended_taxes != $extended_taxes_override){
                                                $ajax_data['quote']['diff_taxes']               = '$' . number_format( ( $extended_taxes - $extended_taxes_override ) / 100 , 2, '.', ',' ) . ' Savings' ;
                                            }
                                            if($total != $total_override){
                                                $ajax_data['quote']['diff_total']               = '$' . number_format( ( $total - $total_override ) / 100 , 2, '.', ',' ) . ' Total Savings' ;
                                            }
                                        }
                                        break;

            case         'read-write' : $ajax_data['message'] = $post['pid'];
                                        $ajax_data['code'] = '0';
                                        $ajax_data['params'] = $post;
                                        $result = $this->orders_logic->ordersUpdate($post['params']);
                                        $ajax_data['read_write'] = $result['read_write'];
                                        $ajax_data['value'] = $result['value'];
                                        break;

            case                'rep' : $ajax_data['message'] = $post['pid'];
                                        $rep = $this->orders_logic->ordersRep($post);
                                        $ajax_data['rep'] = $rep['reps_id'] ; 
                                        if($ajax_data['rep']){
                                            $ajax_data['rep_dealer_id'] = $rep['dealer_id'] ; 
                                            $ajax_data['rep_ecode'] = $rep['ecode'] ; 
                                            $ajax_data['rep_name'] = $rep['name'] ; 
                                            $ajax_data['rep_override'] = $rep['override'] ; 
                                            $ajax_data['code'] = '0';
                                            $ajax_data['companies'] = $this->orders_logic->ordersCompanies($ajax_data['rep']);
                                            $ajax_data['payment_methods'] = $this->orders_logic->ordersOptions('payment_methods');
                                            $ajax_data['shipping_methods'] = $this->orders_logic->ordersOptions('shipping_methods');
                                            foreach( $ajax_data['shipping_methods'] as $key => $value ) {
                                                if ( $value['flat'] < 1 ) {
                                                    $ajax_data['shipping_methods'][$key]['cost'] = number_format ( $value['cost'] / 100 , 2, '.', ',' ) ;
                                                    $ajax_data['shipping_methods'][$key]['flat'] = '' ;
                                                } else {
                                                    $ajax_data['shipping_methods'][$key]['cost'] = number_format ( $value['cost'] / 100 , 2, '.', ',' ) ;
                                                    $ajax_data['shipping_methods'][$key]['flat'] = '&nbsp;Plus&nbsp;$' . number_format ( $value['flat'] / 100 , 2, '.', ',' ) . '&nbsp;Flat&nbsp;Fee' ;
                                                }
                                            }
                                            $ajax_data['versions'] = $this->orders_logic->ordersOptions('versions',$rep['ecode']);
                                            foreach ( $ajax_data['versions'] as $k => $v ) {
                                                $ajax_data['versions'][$k]['price5'] = '$' . number_format( $v['price5'] / 100 , 2, '.', ',' ) ;
                                                $ajax_data['versions'][$k]['price4'] = '$' . number_format( $v['price4'] / 100 , 2, '.', ',' ) ;
                                                $ajax_data['versions'][$k]['price3'] = '$' . number_format( $v['price3'] / 100 , 2, '.', ',' ) ;
                                                $ajax_data['versions'][$k]['price2'] = '$' . number_format( $v['price2'] / 100 , 2, '.', ',' ) ;
                                                $ajax_data['versions'][$k]['price1'] = '$' . number_format( $v['price1'] / 100 , 2, '.', ',' ) ;
                                            }
                                            $ajax_data['message'] .= ', REP ID #' . $ajax_data['rep'];
                                        } else {
                                            $ajax_data['message'] = "Sorry, your credentials do not match our records... \r\nPlease try again";
                                        }
                                        break;

            case             'search' : $ajax_data['message'] = $post['pid'];
                                        $ajax_data['rep'] = $post['params']['rep'];
                                        $ajax_data['um'] = '';
                                        if($ajax_data['rep']){
                                            $ajax_data['code'] = '0';
                                            $ajax_data['companies'] = $this->orders_logic->ordersCompanies($ajax_data['rep'],$post['params']['search']);
                                            if($post['params']['search']){
                                                $ajax_data['um'] = $this->orders_logic->ordersUm($ajax_data['rep'],$post['params']['search']);
                                            }
                                        }
                                        break;

            case             'states' : $ajax_data['message'] = $post['pid'];
                                        $ajax_data['code'] = '0';
                                        $ajax_data['params'] = $post;
                                        $result = $this->orders_logic->ordersUpdateState($post['params']);
                                        $ajax_data['states'] = $result['states'];
                                        $ajax_data['value'] = $result['value'];
                                        break;

            case             'status' : $ajax_data['message'] = $post['pid'];
                                        $ajax_data['status_id'] = $post['status_id'];
                                        $ajax_data['code'] = '0';
                                        $ajax_data['status'] = $this->orders_logic->fulfillmentStatus($post);
                                        switch($ajax_data['status']){
                                            case                 1  :
                                            case                '1' :
                                            case                 4  :
                                            case                '4' :   $this->orders_logic->fulfillmentNotice($post);
                                                                        break;
                                        }
                                        break;

            case                 'um' : $ajax_data['message'] = $post['pid'];
                                        $ajax_data['rep'] = $post['params']['rep'];
                                        $ajax_data['um'] = '';
                                        if($ajax_data['rep']){
                                            $ajax_data['code'] = '0';
                                            $account_id = $this->orders_logic->ordersUmAdd($ajax_data['rep'],$post['params']['um']);
                                            $ajax_data['companies'] = $this->orders_logic->ordersCompanies($ajax_data['rep'],$account_id);
                                            if($account_id){
                                                $ajax_data['um'] = $this->orders_logic->ordersUm($ajax_data['rep'],$account_id);
                                            }
                                        }
                                        break;

                              default : $ajax_data['message'] = $post['pid'];
                                        $ajax_data['params'] = $post;

        }

        $this->ajax_respond($ajax_data);
       
    }

    /**
     *
     */
    public function createNewAccount($params)
    {

        $result = array();

        $emailIsValid     = FALSE;
        $emailIsDuplicate = FALSE;
        $email            = '';

        if (isset($params['account_email'])) {
            $email        = $params['account_email'];
            $emailIsValid = $this->account_logic->emailIsValid($email);
        }

        if ($emailIsValid) {
            $email_in_use = $this->account_logic->emailAddressIsInUse($email);
            if ($email_in_use) {
                $emailIsDuplicate = TRUE;
                $result['error'][] = 'Duplicate Email' ;
            }
        } else {
            $result['error'][] = 'Email Not Valid' ;
        }


        //Check if username exists
        $username = $params['account_username'];
        
        // if posted email already in use, dont update and return error message
        // $username_in_use = $this->account_logic->usernameAddressIsInUse($username);
        if ($username_in_use) {
            $usernameIsDuplicate = TRUE;
            $result['error'][] = 'Duplicate Username' ;
        }

        if ($emailIsValid AND ! $emailIsDuplicate AND !$usernameIsDuplicate AND $params['dealer_id']) {
            $result['attempting'][] = 'CreateAccount Under Dealer Id ' . $params['dealer_id'] ;
            $result['create'] = $this->account_logic->createAccount($params);
        }

        return $result ;

    }

    /**
     *
     */
    public function Reports()
    {

        $ajax_data      = array();

        $post           = $this->request->request->all();

        $params         = $post;
        
        $report         = $this->orders_logic->getReport($params);

        if ($report !== false) {
            $output              = $report;
            $output['records']   = (isset($report['records']) AND ! empty($report['records'])) ? $report['records'] : 0;
            $output['length']    = (isset($report['length'])  AND ! empty($report['length']))  ? $report['length']  : 0;
            $output['data']      = json_encode($report['json']);
            $output['code']      = $report['code'];
            $output['message']   = $report['message'];
        } else {
            $output['code']      = 86 ;
            $output['message']   = 'logout';
        }

        $this->ajax_respond($output);

    }

}
