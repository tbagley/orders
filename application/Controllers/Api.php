<?php

namespace Controllers;

use GTC\Component\Utils\Date;
use GTC\Component\Utils\Dropdown;
use GTC\Component\Utils\CSV\CSVBuilder;
use GTC\Component\Map\Tiger;
use GTC\Component\Utils\VinDecoder;

use Models\Logic\AddressLogic;

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
 * Class Api
 *
 */
class Api extends BasePage
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

        $this->vehicle_data = new VehicleData;
        $this->vehicle_logic = new VehicleLogic;
        $this->address_logic = new AddressLogic;
        $this->territory_data = new TerritoryLogic;
        $this->territory_logic = new TerritoryLogic;
        // $this->user_data = new UserData;
        $this->user_logic = new UserLogic;
        $this->unitcommand_logic = new UnitCommandLogic;

    }

    /**
     * @route default
     */
    public function api($partner,$subscriber,$script,$p)
    {

        $partner_data = $this->territory_logic->apiPartnerKey($partner);

        if(($partner_data[0]['api_id'])&&($partner_data[0]['partner'])){

            $subscriber_data = $this->territory_logic->apiSubscriberKey($subscriber);

            if(($subscriber_data[0]['account_id'])&&($subscriber_data[0]['user_id'])){

                if ( preg_match ('/&/' , $p ) ) {
                    $params = explode ( '&' , $p ) ;
                    foreach ($params as $key => $parameter) {
                        $pair = explode ( '=' , $parameter ) ;
                        $params[$key] = $pair ;
                    }
                } else {
                    $params = $p ;
                }

                $results = $this->territory_logic->api($subscriber,$script,$params);

                $results['partner'] = $partner_data ;

                foreach ( $results as $k1 => $v1 ) {
                    echo '<hr>' . $k1 . '<hr>' ;
                    foreach ( $v1 as $k2 => $v2 ) {
                        // echo 'RECORD #' . $k2 . '<br>' ;
                        ksort($v2);
                        foreach ( $v2 as $k3 => $v3 ) {
                            echo $k3 . '=' . $v3 . '<br>' ;
                        }
                        echo '<br>' ;
                    }
                    echo '<br>' ;
                }

            }

        }

        exit();

    }

    /**
     * @route default
     */
    public function apiAjax()
    {

        $post = $this->request->request->all();

        foreach ($post as $key => $value) {
            switch($key) {
                case     'partner_key' : $partner = $value ;
                                         break;
                case  'subscriber_key' : $subscriber = $value ;
                                         break;
                case         'command' : $command = $value ;
                                         break;
                case          'metric' : $metric = $value ;
                                         break;
                               default : $pair = array();
                                         $pair[0] = $key ;
                                         $pair[1] = $value ;
                                         $params[] = $pair ;
            }
        }

        $partner_data = $this->territory_logic->apiPartnerKey($partner);

        if(($partner_data[0]['api_id'])&&($partner_data[0]['partner'])&&($command)){

            $subscriber_data = $this->territory_logic->apiSubscriberKey($subscriber);

            if(($subscriber_data[0]['account_id'])&&($subscriber_data[0]['user_id'])&&($command)){

                $results = $this->territory_logic->api($subscriber,$command,$params,$metric);

                echo json_encode($results);

            } else {

                echo json_encode($post);
            
            }

        }

        exit();

    }

    /**
     * @route default
     */
    public function apiJson($partner,$subscriber,$script,$p)
    {

        header("Content-type: text/json");

        $partner_data = $this->territory_logic->apiPartnerKey($partner);

        if(($partner_data[0]['api_id'])&&($partner_data[0]['partner'])){

            $subscriber_data = $this->territory_logic->apiSubscriberKey($subscriber);

            if(($subscriber_data[0]['account_id'])&&($subscriber_data[0]['user_id'])){

                if ( preg_match ('/&/' , $p ) ) {
                    $params = explode ( '&' , $p ) ;
                    foreach ($params as $key => $parameter) {
                        $pair = explode ( '=' , $parameter ) ;
                        $params[$key] = $pair ;
                    }
                } else {
                    $params = $p ;
                }

                $results = $this->territory_logic->api($subscriber,$script,$params,$metric);

                echo json_encode($results);

            } else {

                echo json_encode($post);
            
            }

        }

        exit();

    }

    /**
     * @route default
     */
    public function apiXml($partner,$subscriber,$script,$p)
    {

        header("Content-type: text/xml");

        echo "<?xml version='1.0' encoding='UTF-8'?>\r\n";

        $partner_data = $this->territory_logic->apiPartnerKey($partner);

        if(($partner_data[0]['api_id'])&&($partner_data[0]['partner'])){

            $subscriber_data = $this->territory_logic->apiSubscriberKey($subscriber);

            if(($subscriber_data[0]['account_id'])&&($subscriber_data[0]['user_id'])){

                if ( preg_match ('/&/' , $p ) ) {
                    $params = explode ( '&' , $p ) ;
                    foreach ($params as $key => $parameter) {
                        $pair = explode ( '=' , $parameter ) ;
                        $params[$key] = $pair ;
                    }
                } else {
                    $params = $p ;
                }

                $results = $this->territory_logic->api($subscriber,$script,$params);

                // echo json_encode($results);

                echo "<positionplusgps>\r\n" ;
                foreach ( $results as $k1 => $v1 ) {
                    echo "<" . $k1 . ">\r\n" ;
                    foreach ( $v1 as $k2 => $v2 ) {
                        echo "<record_" . $k2 . ">\r\n" ;
                        foreach ( $v2 as $k3 => $v3 ) {
                            if(!($v3)){
                                $v3 = ' ' ;
                            }
                            echo "<_" . $k3 . ">" . $v3 . "</_" . $k3 . ">\r\n" ;
                        }
                        echo "</record_" . $k2 . ">\r\n" ;
                    }
                    echo "</" . $k1 . ">\r\n" ;
                }
                echo "</positionplusgps>\r\n" ;

            } else {

                // echo json_encode($post);

                echo "<post>\r\n" ;
                foreach ( $post as $k1 => $v1 ) {
                        echo "<" . $k1 . ">" . $v1 . "</" . $k1 . ">\r\n" ;
                }
                echo "</post>\r\n" ;
            
            }

        } else {

            // echo json_encode($post);

            echo "<post>\r\n" ;
            foreach ( $post as $k1 => $v1 ) {
                    echo "<" . $k1 . ">" . $v1 . "</" . $k1 . ">\r\n" ;
            }
            echo "</post>\r\n" ;
        
        }

        exit();

    }

    /**
     * @route default
     */
    public function apiJsClass()
    {

        ?>

                <html>
                    <head>
                        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
                        <script>
                            if (typeof jQuery == 'undefined') {
                                document.write('<script src="/assets/vendor/jquery/jquery-1.10.2.js">\x3C/script>');
                                console.log('jQuery not loaded from cdn');
                            }
                        </script>
                         <script type="text/javascript" src="//api.positionplusgps.com/assets/js/positionplusgps.js"></script>
                         <script type="text/javascript" src="//api.positionplusgps.com/assets/js/positionplusgps_demo.js"></script>
                    </head>
                    <body>
                        <h1>PositionPlusGPS API</h1><p>
                        To observe this functioning pair of JavaScript files, please view the JavaScript Console and Source Code behind this page.<p>
                        To request a Software Development Kit complete with Documentation and API KEYS, please contact PositionPlusGPS at 844-477-7587 x103<p>
                        Thank You
                        <br>
                        <div id="api_out"></div>
                    </body>
                </html>

        <?php 

    }

    /**
     * @route default
     */
    public function apiJsRestrict($partner,$subscriber)
    {

        if($partner){
            $partner_data = $this->territory_logic->apiPartnerKey($partner);
        }

        if( (!($partner)) || (($partner_data[0]['api_id'])&&($partner_data[0]['partner'])) ){

            if($subscriber){
                $subscriber_data = $this->territory_logic->apiSubscriberKey($subscriber);
            }

            if( (!($subscriber)) || (($subscriber_data[0]['account_id'])&&($subscriber_data[0]['user_id'])) ){ ?>

                <html>
                    <head>
                        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
                        <script>
                            if (typeof jQuery == 'undefined') {
                                document.write('<script src="/assets/vendor/jquery/jquery-1.10.2.js">\x3C/script>');
                                console.log('jQuery not loaded from cdn');
                            }
                        </script>
                        <script type="text/javascript" src="//api.positionplusgps.com/assets/js/positionplusgps.js"></script>
                    </head>
                    <body>
                        PositionPlusGPS API<p>
                    </body>
                </html>

            <?php }

        }

    }

}
