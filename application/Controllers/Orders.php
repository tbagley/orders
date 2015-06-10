<?php

namespace Controllers;

use GTC\Component\Utils\Date;
use GTC\Component\Utils\Dropdown;
use GTC\Component\Utils\CSV\CSVBuilder;
use GTC\Component\Map\Tiger;
use GTC\Component\Utils\VinDecoder;

use Models\Logic\AddressLogic;

use Models\Data\OrdersData;
use Models\Logic\OrdersLogic;

use Models\Data\VehicleData;
use Models\Logic\VehicleLogic;

use Models\Data\TerritoryData;
use Models\Logic\TerritoryLogic;

use Models\Logic\UserData;
use Models\Logic\UserLogic;

use Models\Logic\UnitCommandLogic;

use Symfony\Component\HttpFoundation\Request;

use GTC\Component\Utils\PDF\TCPDFBuilder;



/**
 * Class Orders
 *
 */
class Orders extends BasePage
{    
    /**
     *
     */
    public function __construct()
    {

        parent::__construct();

        // start database
        $this->load_db('master');
        $this->load_db('slave');

        $this->address_logic = new AddressLogic;
        $this->orders_data = new OrdersData;
        $this->orders_logic = new OrdersLogic;
        $this->territory_data = new TerritoryLogic;
        $this->territory_logic = new TerritoryLogic;
        // $this->user_data = new UserData;
        $this->user_logic = new UserLogic;
        $this->unitcommand_logic = new UnitCommandLogic;
        $this->vehicle_data = new VehicleData;
        $this->vehicle_logic = new VehicleLogic;

    }

    /**
     * @route fulfillment
     */
    public function fulfillment()
    {

        $view_data = array();

        $view_data['environment']       = md5(ENVIRONMENT);
        $view_data['context']           = $this->route_data['route'];
        $view_data['map_api']           = $this->map_api;
        $view_data['decarta_api_key']   = $this->decarta_api_key;
        $view_data['message']           = null;

        $this->render("page/orders/fulfillment.html.twig", $view_data);

    }

    /**
     * @route fulfillment
     */
    public function warehouse()
    {

        $view_data = array();

        $view_data['environment']       = md5(ENVIRONMENT);
        $view_data['context']           = $this->route_data['route'];
        $view_data['map_api']           = $this->map_api;
        $view_data['decarta_api_key']   = $this->decarta_api_key;
        $view_data['message']           = null;

        $this->render("page/orders/warehouse.html.twig", $view_data);

    }

    /**
     * @route default
     */
    public function orders()
    {

        $view_data = array();

        $view_data['environment']       = md5(ENVIRONMENT);
        $view_data['context']           = $this->route_data['route'];
        $view_data['map_api']           = $this->map_api;
        $view_data['decarta_api_key']   = $this->decarta_api_key;
        $view_data['message']           = null;

        $view_data['accessories']       = $this->orders_logic->ordersOptions('accessories');
        foreach( $view_data['accessories'] as $key => $value ) {
            $view_data['accessories'][$key]['cost'] = number_format ( $value['cost'] / 100 , 2, '.', ',' ) ;
        }
        $view_data['dms']               = $this->orders_logic->ordersOptions('dms');
        $view_data['payment_methods']   = $this->orders_logic->ordersOptions('payment_methods');
        $view_data['plans']             = $this->orders_logic->ordersOptions('plans');
        // $view_data['password']          = 'pass' . date('Hs') ;
        $view_data['password']          = 'pass1234' ;
        $view_data['shipping_methods']  = $this->orders_logic->ordersOptions('shipping_methods');
        foreach( $view_data['shipping_methods'] as $key => $value ) {
            $view_data['shipping_methods'][$key]['cost'] = number_format ( $value['cost'] / 100 , 2, '.', ',' ) ;
            $view_data['shipping_methods'][$key]['flat'] = number_format ( $value['flat'] / 100 , 2, '.', ',' ) ;
        }
        $view_data['usa_states']        = $this->orders_logic->ordersOptions('states');
        foreach( $view_data['usa_states'] as $key => $value ) {
            $view_data['usa_states'][$key]['taxrate'] = number_format ( $value['taxrate'] / 100 , 2, '.', ',' ) ;
        }
        $view_data['timezones']         = $this->orders_logic->ordersOptions('timezones');
        $view_data['versions']          = array() ; // $this->orders_logic->ordersOptions('versions');

        $this->render("page/orders/orders.html.twig", $view_data);

    }

    /**
     * @route default
     */
    public function orderstatus($uri)
    {

        $view_data = array();

        $view_data['environment']       = md5(ENVIRONMENT);
        $view_data['context']           = $this->route_data['route'];
        $view_data['map_api']           = $this->map_api;
        $view_data['decarta_api_key']   = $this->decarta_api_key;
        $view_data['message']           = null;

        $view_data['order'] = $this->orders_logic->ordersDetail($uri);

        $quantity                       = $view_data['order']['quantity'] ;
        $view_data['order']['discount_reason_label'] = '';
        $view_data['order']['discount_reason'] = '';
        $view_data['order']['discount_rate'] = '';
        $view_data['order']['discount_arate'] = '';
        $view_data['order']['discount_shipping'] = '';
        $view_data['order']['discount_handling'] = '';
        $view_data['order']['discount_sandh'] = '';
        $view_data['order']['discount_extended'] = '';
        $view_data['order']['discount_subtotal'] = '';
        $view_data['order']['discount_total'] = '';

        if ( ( str_replace('/' , '' , strtolower($view_data['order']['reseller']) ) != 'na' ) && ($view_data['order']['reseller']) ) {
            $view_data['order']['taxes_description'] = 'Resale #' . $view_data['order']['reseller'] ;
        } else {
            $view_data['order']['taxes_description'] = number_format( $view_data['order']['taxes_rate'] / 100 , 2 , '.' , ',' ) . '%'  . ' ' . $view_data['order']['taxes_state_name']  ;
        }

        $rate            = $view_data['order']['rate'] ;
        $arate           = $view_data['order']['arate'] ;
        $shipping        = $view_data['order']['shipping_fee'] ;
        $handling        = $view_data['order']['handling_fee'] ;
        $view_data['order']['shipping_handling']    = ( $view_data['order']['shipping_fee'] * $quantity ) + $view_data['order']['handling_fee'] ;

        if(($view_data['order']['override_reason'])&&($view_data['order']['override_rate']>0)){
            //
            $view_data['order']['discount_reason_label'] = 'Discount Reason:';
            $view_data['order']['discount_reason'] = $view_data['order']['override_reason'];
            //
            if($rate > $view_data['order']['override_rate']){
              $view_data['order']['discount_rate'] = 'less $' . number_format( ( $rate - $view_data['order']['override_rate'] ) / 100 , 2 , '.' , ',' ) . ' per unit' ;
              $rate = $view_data['order']['override_rate'] ;
            }
            //
            if($arate > $view_data['order']['override_arate']){
              $view_data['order']['discount_arate'] = 'less $' . number_format( ( $arate - $view_data['order']['override_arate'] ) / 100 , 2 , '.' , ',' ) . ' per unit' ;
              $arate = $view_data['order']['override_arate'] ;
            }
            //
            if(($rate != $view_data['order']['override_rate'])||($arate != $view_data['order']['override_arate'])){
              $view_data['order']['discount_order'] = '&nbsp;&nbsp;<span class="text-red"> savings of $' . number_format( ( ( $rate + $arate - $view_data['order']['override_rate'] - $view_data['order']['override_arate'] ) * $quantity ) / 100 , 2 , '.' , ',' ) . '</span>' ;
            }
            //
            if($view_data['order']['shipping_fee'] > $view_data['order']['override_shipping']){
              $view_data['order']['discount_shipping'] = ' less $' . number_format( ( $view_data['order']['shipping_fee'] - $view_data['order']['override_shipping'] ) / 100 , 2 , '.' , ',' ) . ' per unit' ;
                $shipping = $view_data['order']['override_shipping'] ;
            }
            //
            if($view_data['order']['handling_fee'] > $view_data['order']['override_handling']){
              $view_data['order']['discount_handling'] = ' less $' . number_format( ( $view_data['order']['handling_fee'] - $view_data['order']['override_handling'] ) / 100 , 2 , '.' , ',' ) ;
                $handling = $view_data['order']['override_handling'] ;
            }
            //
            // $view_data['order']['shipping_handling'] = (  $view_data['order']['shipping_fee'] * $quantity ) + $view_data['order']['handling_fee'] ;
            // $view_data['order']['override_shipping_handling'] = ( $shipping * $quantity ) + $handling ;
            // if($view_data['order']['shipping_handling'] > $view_data['order']['override_shipping_handling']){
            //   $view_data['order']['discount_sandh'] = ' savings of $' . number_format( ( $view_data['order']['shipping_handling'] - $view_data['order']['override_shipping_handling'] ) / 100 , 2 , '.' , ',' ) ;
            // }
            //
        }

        $total_extended     = $quantity * ( $rate + $arate ) ;
        $discount_extended  = $quantity * ( $view_data['order']['rate'] - $rate + $view_data['order']['arate'] - $arate ) ;
        $total_sandh        = ( $quantity * $shipping ) + $handling ;
        $discount_sandh     = ( $quantity * ( $view_data['order']['shipping_fee'] - $shipping ) ) + $view_data['order']['handling_fee'] - $handling ;
        $taxes              = $view_data['order']['taxes_amount'] ;
        $subtotal           = $total_extended + $handling + $taxes ;
        $total_grand        = $total_extended + $total_sandh;

        if(($view_data['order']['override_reason'])&&($view_data['order']['override_rate']>0)){
            if($discount_extended){
                $view_data['order']['discount_extended'] = ' savings of $' . number_format ( $discount_extended / 100 , 2, '.', ',' ) ;
            }
            if($discount_sandh){
                $view_data['order']['discount_sandh'] = ' savings of $' . number_format ( $discount_sandh / 100 , 2, '.', ',' ) ;
            }
            if(($discount_extended)||($discount_sandh)){
                $view_data['order']['discount_total'] = ' savings of $' . number_format ( ( $discount_extended + $discount_sandh ) / 100 , 2, '.', ',' ) ;
            }
        }

        $view_data['order']['quantity']         = number_format ( $quantity , 0, '.', ',' ) ;
        switch($view_data['order']['subscription_id']){
            case                                   1  :
            case                                  '1' : $view_data['order']['subscription'] = '1 Year' ;
                                                        break;

                                              default : $view_data['order']['subscription'] = $view_data['order']['subscription_id'] . ' Years' ;
                                                        break;
        }
        $view_data['order']['rate']             = '$' . number_format ( $view_data['order']['rate'] / 100 , 2, '.', ',' ) ;
        $view_data['order']['accessories_cost'] = '$' . number_format ( $view_data['order']['accessories_cost'] / 100 , 2, '.', ',' ) ;
        $view_data['order']['rate_shipping']    = '$' . number_format ( $shipping / 100 , 2, '.', ',' ) ;
        $view_data['order']['rate_handling']    = '$' . number_format ( $handling / 100 , 2, '.', ',' ) ;
        $view_data['order']['extended_ship']    = '$' . number_format ( $quantity * $shipping / 100 , 2, '.', ',' ) ;
        $view_data['order']['extended_total']   = '$' . number_format ( $total_extended / 100 , 2, '.', ',' ) ;
        $view_data['order']['sandh_total']      = '$' . number_format ( $total_sandh / 100 , 2, '.', ',' ) ;
        $view_data['order']['taxes']            = '$' . number_format ( $taxes / 100 , 2, '.', ',' ) ;
        $view_data['order']['subtotal']         = '$' . number_format ( $subtotal / 100 , 2, '.', ',' ) ;
        $view_data['order']['grand_total']      = '$' . number_format ( $total_grand / 100 , 2, '.', ',' ) ;

        $this->render("page/orders/orderstatus.html.twig", $view_data);

    }

}
