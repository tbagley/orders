<?php

namespace Models;

class Base
{

    /**
 * Container for error messages
 *
 * @var array
 */
    private $errors = array();

    public function __construct()
    {
        //parent::__construct();
    }

    /**
     * Gets and clears any existing error messages
     *
     * @return array|bool returns array of error messages or false if no errors
     */
    protected function getErrorMessage()
    {
        if (count($this->errors) > 0) {
            $tmp = $this->errors;
            $this->errors = array();
            return $tmp;
        }
        return false;
    }

    /**
     * Set/Append an error message to the internal error array
     *
     * @param string $message
     * @return void
     */
    protected function setErrorMessage($message)
    {
        if (! is_array($message)) {
            if ($message != '') {
                $this->errors[] = $message;
            }
        } else {
            $this->errors = array_merge($this->errors, $message);
        }
    }

    /**
     * Checks for the existence of error messages
     *
     * @return bool
     */
    protected function hasError()
    {
        if (count($this->errors) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Clear all previous errors
     *
     * @return void
     */
    protected function clearError()
    {
        $this->errors = array();
    }
                                                                                                                        
    /**
     * PERMISSION Checkbox: Checkbox that looks like a cool switch
     *
     * echo HTML
     */
    public function permissionCheckbox($pid,$utid,$label,$checked,$disabled)
    {
        $html = '<div id="' . $pid . '" class="row permission';
        if($disabled){
            $html .= ' permission-canned';
        }
        $html .= '"><div class="permission-checkbox pull-right';
        if($disabled){
            $html .= ' permission-disabled'; 
        }
        $html .= '"><input type="checkbox" id="permission-' . $utid . '-' . $pid . '" value="1"';
        if($checked){
            $html .= ' checked'; 
        }
        if($disabled){
            $html .= ' disabled'; 
        }
        $html .= '/><label for="permission-checkbox"></label></div><div class="permission-label">' . str_replace(' ','&nbsp;',$label) . '</div></div>'; 

        if($disabled){
            $html = '<div id="' . $pid . '" class="row permission"><div class="permission-label">' . str_replace(' ','&nbsp;',$label) . '</div></div>'; 
        }

        return $html;
    }

    /**
     * WIZARD Link to Map All Addresses in Report
     *
     * return HTML
     */
    public function wizardMapAllLink()
    {

        // return '&nbsp;&nbsp;<a href="javascript:void(0);" class="map-addresses-all">Map&nbsp;Addresses&nbsp;&nbsp;<img src="/assets/media/icons/markers/map-marker-icon-blue.png" style="height:16px;""></a>&nbsp;&nbsp;';
        return '<button class="btn btn-default btn-icon btn-small map-addresses-all pull-right" title="View All Addresses on Map"><img src="/assets/media/icons/markers/map-marker-icon-blue.png" style="height:16px;"">&nbsp;Map</button>';
                                                                
    }

    /**
     * WIZARD Adjust Timestamp to Timezone
     *
     * return Timestamp
     */
    public function wizardTzAdj($format,$dt,$to,$from)
    {

        if ( ! ( $format ) ) {
            $format = 'Y-m-d H:i:s' ;
        }

        switch ( $to ) {
          case                  '' :  $to = 'America/Los_Angeles';
                                      break;
          case         'US/Alaska' :  $to = 'America/Anchorage' ;
                                      break;
          case       'US/Aleutian' :  $to = 'America/Adak' ;
                                      break;
          case         'US/Hawaii' :  $to = 'Pacific/Honolulu' ;
                                      break;
          case        'US/Arizona' :  $to = 'America/Phoenix' ;
                                      break;
          case        'US/Central' :
          case   'US/East-Indiana' : 
          case 'US/Indiana-Starke' : 
          case       'US/Michigan' :  $to = 'America/Chicago' ;
                                      break;
                                      break;
          case        'US/Eastern' :  $to = 'America/New_York' ;
                                      break;
          case       'US/Mountain' :  $to = 'America/Denver' ;
                                      break;
          case        'US/Pacific' : 
          case    'US/Pacific-New' :  $to = 'America/Los_Angeles';
                                      break;
        }

        if($from){
            // $to = 'UTC';
        }

        return date ( $format , strtotime($dt . ' ' . str_replace('US/','America/',$to) ));

    }
                                                                
    /**
     * WIZARD link: Delete "X" Link
     *
     * echo HTML
     */
    public function wizardDow($dow)
    {

      switch($dow){

        case  '1' :
        case   1  : $dow = 'Monday' ;
                    break;

        case  '2' :
        case   2  : $dow = 'Tuesday' ;
                    break;

        case  '3' :
        case   3  : $dow = 'Wednesday' ;
                    break;

        case  '4' :
        case   4  : $dow = 'Thursday' ;
                    break;

        case  '5' :
        case   5  : $dow = 'Friday' ;
                    break;

        case  '6' :
        case   6  : $dow = 'Saturday' ;
                    break;

        case  '7' :
        case   7  : $dow = 'Sunday' ;
                    break;

      }

      return $dow ;

    }
                                                                
    /**
     * WIZARD link: Delete "X" Link
     *
     * echo HTML
     */
    public function wizardDelete($val,$pid,$uniq,$dbid,$ck1,$ck2,$ck3)
    {
        if(!($val)){
            $val = '<span class="wizard-nodata" title="please click to enter data...">&oplus;</span>';
        }

        $onClick = "Core.Wizard.Delete(this.id,'" . $dbid . "','" . $ck1 . "','" . $ck2 . "');";

        return $val . '&nbsp;&nbsp;<a class="wizard-editable text-grey-8" id="' . str_replace('"', '', $pid . '-' . $dbid . '-' . $uniq) . '" href="javascript:void(0);" onClick="' . $onClick . '" ><img src="\assets\media\icons\delete.gif" style="height:12px;width:12px;" alt="X" title="Delete record?"></a>&nbsp;' ;

    }
                                                                
    /**
     * WIZARD link: Delete "X" Link
     *
     * echo HTML
     */
    public function wizardDeleteRecord($type,$pid,$uniq,$uid)
    {
        if(!($val)){
            $val = '<span class="wizard-nodata" title="please click to enter data...">&oplus;</span>';
        }

        $onClick = "Core.Wizard.DeleteRecord(this.id,'" . $uid . "','delete-" . $type . "');";

        return '&nbsp;&nbsp;<a class="wizard-editable text-red-12" id="' . str_replace('"', '', $pid . '-delete-' . $type . '-' . $uniq) . '" href="javascript:void(0);" onClick="' . $onClick . '" ><img src="\assets\media\icons\delete.gif" style="height:12px;width:12px;" alt="X" title="Delete record?"></a>&nbsp;' ;

    }
                                                                
    /**
     * WIZARD link: Keystroke Editable Fields
     *
     * echo HTML
     */
    public function wizardInput($pid,$uniq,$dbid,$key,$val,$refreshReportOnChange,$class)
    {
        if($refreshReportOnChange){
            $refreshReportOnChange=",'1'";
        }
        if(!($val)){
            // $val = '<span class="wizard-nodata" title="please click to enter data...">&oplus;</span>';
        }

        $onClick = "Core.Wizard.Link2Input(this.id,this.text,'" . $dbid . "','" . $key . "'" . $refreshReportOnChange . ");";

        return '<div class="wizard-div wizard-edit ' . $class . '"><table class="wizard-join ' . $class . '"><tr><td class="' . $class . '"><a class="wizard-clickable wizard-editable" id="' . str_replace('"', '', $pid . '-' . $dbid . '-' . $uniq) . '" href="javascript:void(0);" onClick="' . $onClick . '"  onFocus="' . $onClick . '">' . $val . '</a></td></tr></table></div>' ;

    }

    /**
     * WIZARD link: Select Dropdown Lists
     *
     * echo HTML
     */
    public function wizardSelect($pid,$uniq,$dbid,$key,$val,$refreshReportOnChange)
    {
        $class = array_pop( explode( '-', $dbid ) ) ;

        $eid = str_replace('"', '', $pid . '-' . $dbid . '-' . $uniq . '-' . $key);

        if($refreshReportOnChange){
            $refreshReportOnChange=",'1'";
            $roc=1;
        }
        if(!($val)){
            // $val = '<span class="wizard-nodata" title="please click to enter data...">&oplus;</span>';
        }

        if(!($options[$val])){
            $options[$val] .= '<option value="' . $val . '">' . $val . '</option>'; 
        }

        return '<select id="' . $eid . '">' . $options[$val] . '</select>';

        // 2014-10-29 $onClick = "dontDeSelect='ul-" . $eid . "';Core.Wizard.Link2Select('" . $eid . "','" . $dbid . "','" . $key . "'" . $refreshReportOnChange . ");setTimeout('dontDeSelect=\'\';',1);";
        // 2014-10-29 $onLeave = "dontDeSelect='';setTimeout('Core.Wizard.DeSelect(\'ul-" . $eid . "\',1)',5000);";
        // 2014-10-29 return '<div class="wizard-div wizard-select" onClick="' . $onClick . '"><table class="wizard-join"><tr><td><a class="wizard-clickable ' . $class . ' ' . $dbid . ' wizard-editable" id="' . $eid . '" href="javascript:void(0);" onfocus="' . $onClick . '">' . $val . '</a></td><th><a class="wizard-caret pull-right" href="javascript:void(0);"><img src="/assets/media/icons/caret.png"></a></th></tr></table></div><ul id="ul-' . $eid . '" class="dropdown-menu wizard-select-options" onBlur="' . $onLeave . '" onMouseOut="' . $onLeave . '" onMouseOver="dontDeSelect=\'ul-' . $eid . '\';" role="menu"></ul>';
        // 2014-10-27 return '<div id="' . $eid . '" class="wizard-selectable wizard-' . $class . '"><select class="form-control ' . $class . ' ' . $dbid . ' wizard-editable" data-dbid="' . $dbid . '" data-key="' . $key . '" data-roc="' . $roc . '" id="select-' . $eid . '"><option value="' . $key . '" selected>' . $val . '</option><option></option><option></option><option></option><option></option></select></div>';
        // return '<div class="row"><div class="wizard-div wizard-select"><a class="' . $class . ' ' . $dbid . ' wizard-editable" id="' . $eid . '" href="javascript:void(0);" onClick="' . $onClick . '" onfocus="' . $onClick . '">' . $val . '</a>&nbsp;&nbsp;<a class="wizard-caret pull-right" href="javascript:void(0);" onClick="' . $onClick . '">&equiv;</a><ul id="ul-' . $eid . '" class="dropdown-menu" onblur="Core.Wizard.DeSelect(this.id);" onmouseleave="Core.Wizard.DeSelect(this.id);" role="menu"></ul></div></div>';
    }

    /**
     * timezoneDelta
     *
     * return timestamp
     */
    public function tzUtc2Local ( $timezone , $dts )
    {
      $dtz = date_default_timezone_get( ) ;
      date_default_timezone_set( 'UTC' ) ;
      $buffer = strtotime($dts);
      date_default_timezone_set( $timezone ) ;
      $dts = date('Y-m-d H:i:s',$buffer);
      date_default_timezone_set( $dtz ) ;
      return $dts;
    }

    /**
     * timezoneDelta
     *
     * return timestamp
     */
    public function timezoneDelta ( $timezone , $dts , $read , $utc )
    {
    
        if ( $timezone ) {
    
          if(!($utc)){
            $gmtDistServer = $this->timezone ( 'UTC' ) ;
          } else {
            $gmtDistServer = date_default_timezone_get ( ) ;
          }
          $gmtDistZone = $this->timezone ( $timezone ) ;
    
          $delta = $gmtDistServer - $gmtDistZone ;
    
          if ( $delta > 0 ) {
            if ( $read ) {
              $result = date ( 'Y-m-d H:i:s' , strtotime ( $dts . ' - ' . $delta . 'hours' ) ) ;
            } else {
              $result = date ( 'Y-m-d H:i:s' , strtotime ( $dts . ' + ' . $delta . 'hours' ) ) ;
            }
          } else if ( $delta < 0 ) {
            if ( $read ) {
              $result = date ( 'Y-m-d H:i:s' , strtotime ( $dts . ' + ' . str_replace ( '-' , '' , $delta ) . 'hours' ) ) ;
            } else {
              $result = date ( 'Y-m-d H:i:s' , strtotime ( $dts . ' ' . $delta . 'hours' ) ) ;
            }
          } else {
            $result = date ( 'Y-m-d H:i:s' , strtotime ( $dts ) ) ;
          }
    
        } else {
    
          $result = $dts ;
    
        }

        return $result ;
    
    }


    /**
     * timezoneDelta
     *
     * return timestamp
     */
    public function timezone ( $z ) 
    {

        switch ( $z ) {
          case "Africa/Abidjan" : $s = 0 ; $d = 0 ; break ;
          case "Africa/Accra" : $s = 0 ; $d = 0 ; break ;
          case "Africa/Addis_Ababa" : $s = 3 ; $d = 3 ; break ;
          case "Africa/Algiers" : $s = 1 ; $d = 1 ; break ;
          case "Africa/Asmara" : $s = 3 ; $d = 3 ; break ;
          case "Africa/Asmera" : $s = 3 ; $d = 3 ; break ;
          case "Africa/Bamako" : $s = 0 ; $d = 0 ; break ;
          case "Africa/Bangui" : $s = 1 ; $d = 1 ; break ;
          case "Africa/Banjul" : $s = 0 ; $d = 0 ; break ;
          case "Africa/Bissau" : $s = 0 ; $d = 0 ; break ;
          case "Africa/Blantyre" : $s = 2 ; $d = 2 ; break ;
          case "Africa/Brazzaville" : $s = 1 ; $d = 1 ; break ;
          case "Africa/Bujumbura" : $s = 2 ; $d = 2 ; break ;
          case "Africa/Cairo" : $s = 2 ; $d = 2 ; break ;
          case "Africa/Casablanca" : $s = 0 ; $d = 1 ; break ;
          case "Africa/Ceuta" : $s = 1 ; $d = 2 ; break ;
          case "Africa/Conakry" : $s = 0 ; $d = 0 ; break ;
          case "Africa/Dakar" : $s = 0 ; $d = 0 ; break ;
          case "Africa/Dar_es_Salaam" : $s = 3 ; $d = 3 ; break ;
          case "Africa/Djibouti" : $s = 3 ; $d = 3 ; break ;
          case "Africa/Douala" : $s = 1 ; $d = 1 ; break ;
          case "Africa/El_Aaiun" : $s = 0 ; $d = 0 ; break ;
          case "Africa/Freetown" : $s = 0 ; $d = 0 ; break ;
          case "Africa/Gaborone" : $s = 2 ; $d = 2 ; break ;
          case "Africa/Harare" : $s = 2 ; $d = 2 ; break ;
          case "Africa/Johannesburg" : $s = 2 ; $d = 2 ; break ;
          case "Africa/Juba" : $s = 3 ; $d = 3 ; break ;
          case "Africa/Kampala" : $s = 3 ; $d = 3 ; break ;
          case "Africa/Khartoum" : $s = 3 ; $d = 3 ; break ;
          case "Africa/Kigali" : $s = 2 ; $d = 2 ; break ;
          case "Africa/Kinshasa" : $s = 1 ; $d = 1 ; break ;
          case "Africa/Lagos" : $s = 1 ; $d = 1 ; break ;
          case "Africa/Libreville" : $s = 1 ; $d = 1 ; break ;
          case "Africa/Lome" : $s = 0 ; $d = 0 ; break ;
          case "Africa/Luanda" : $s = 1 ; $d = 1 ; break ;
          case "Africa/Lubumbashi" : $s = 2 ; $d = 2 ; break ;
          case "Africa/Lusaka" : $s = 2 ; $d = 2 ; break ;
          case "Africa/Malabo" : $s = 1 ; $d = 1 ; break ;
          case "Africa/Maputo" : $s = 2 ; $d = 2 ; break ;
          case "Africa/Maseru" : $s = 2 ; $d = 2 ; break ;
          case "Africa/Mbabane" : $s = 2 ; $d = 2 ; break ;
          case "Africa/Mogadishu" : $s = 3 ; $d = 3 ; break ;
          case "Africa/Monrovia" : $s = 0 ; $d = 0 ; break ;
          case "Africa/Nairobi" : $s = 3 ; $d = 3 ; break ;
          case "Africa/Ndjamena" : $s = 1 ; $d = 1 ; break ;
          case "Africa/Niamey" : $s = 1 ; $d = 1 ; break ;
          case "Africa/Nouakchott" : $s = 0 ; $d = 0 ; break ;
          case "Africa/Ouagadougou" : $s = 0 ; $d = 0 ; break ;
          case "Africa/Porto-Novo" : $s = 1 ; $d = 1 ; break ;
          case "Africa/Sao_Tome" : $s = 0 ; $d = 0 ; break ;
          case "Africa/Timbuktu" : $s = 0 ; $d = 0 ; break ;
          case "Africa/Tripoli" : $s = 1 ; $d = 2 ; break ;
          case "Africa/Tunis" : $s = 1 ; $d = 1 ; break ;
          case "Africa/Windhoek" : $s = 1 ; $d = 2 ; break ;
          case "AKST9AKDT" : $s = -9 ; $d = -8 ; break ;
          case "America/Adak" : $s = -10 ; $d = -9 ; break ;
          case "America/Anchorage" : $s = -9 ; $d = -8 ; break ;
          case "America/Anguilla" : $s = -4 ; $d = -4 ; break ;
          case "America/Antigua" : $s = -4 ; $d = -4 ; break ;
          case "America/Araguaina" : $s = -3 ; $d = -3 ; break ;
          case "America/Argentina/Buenos_Aires" : $s = -3 ; $d = -3 ; break ;
          case "America/Argentina/Catamarca" : $s = -3 ; $d = -3 ; break ;
          case "America/Argentina/ComodRivadavia" : $s = -3 ; $d = -3 ; break ;
          case "America/Argentina/Cordoba" : $s = -3 ; $d = -3 ; break ;
          case "America/Argentina/Jujuy" : $s = -3 ; $d = -3 ; break ;
          case "America/Argentina/La_Rioja" : $s = -3 ; $d = -3 ; break ;
          case "America/Argentina/Mendoza" : $s = -3 ; $d = -3 ; break ;
          case "America/Argentina/Rio_Gallegos" : $s = -3 ; $d = -3 ; break ;
          case "America/Argentina/Salta" : $s = -3 ; $d = -3 ; break ;
          case "America/Argentina/San_Juan" : $s = -3 ; $d = -3 ; break ;
          case "America/Argentina/San_Luis" : $s = -3 ; $d = -3 ; break ;
          case "America/Argentina/Tucuman" : $s = -3 ; $d = -3 ; break ;
          case "America/Argentina/Ushuaia" : $s = -3 ; $d = -3 ; break ;
          case "America/Aruba" : $s = -4 ; $d = -4 ; break ;
          case "America/Asuncion" : $s = -4 ; $d = -3 ; break ;
          case "America/Atikokan" : $s = -5 ; $d = -5 ; break ;
          case "America/Atka" : $s = -10 ; $d = -9 ; break ;
          case "America/Bahia" : $s = -3 ; $d = -3 ; break ;
          case "America/Bahia_Banderas" : $s = -6 ; $d = -5 ; break ;
          case "America/Barbados" : $s = -4 ; $d = -4 ; break ;
          case "America/Belem" : $s = -3 ; $d = -3 ; break ;
          case "America/Belize" : $s = -6 ; $d = -6 ; break ;
          case "America/Blanc-Sablon" : $s = -4 ; $d = -4 ; break ;
          case "America/Boa_Vista" : $s = -4 ; $d = -4 ; break ;
          case "America/Bogota" : $s = -5 ; $d = -5 ; break ;
          case "America/Boise" : $s = -7 ; $d = -6 ; break ;
          case "America/Buenos_Aires" : $s = -3 ; $d = -3 ; break ;
          case "America/Cambridge_Bay" : $s = -7 ; $d = -6 ; break ;
          case "America/Campo_Grande" : $s = -4 ; $d = -3 ; break ;
          case "America/Cancun" : $s = -6 ; $d = -5 ; break ;
          case "America/Caracas" : $s = -4.5 ; $d = -4.5 ; break ;
          case "America/Catamarca" : $s = -3 ; $d = -3 ; break ;
          case "America/Cayenne" : $s = -3 ; $d = -3 ; break ;
          case "America/Cayman" : $s = -5 ; $d = -5 ; break ;
          case "America/Chicago" : $s = -6 ; $d = -5 ; break ;
          case "America/Chihuahua" : $s = -7 ; $d = -6 ; break ;
          case "America/Coral_Harbour" : $s = -5 ; $d = -5 ; break ;
          case "America/Cordoba" : $s = -3 ; $d = -3 ; break ;
          case "America/Costa_Rica" : $s = -6 ; $d = -6 ; break ;
          case "America/Creston" : $s = -7 ; $d = -7 ; break ;
          case "America/Cuiaba" : $s = -4 ; $d = -3 ; break ;
          case "America/Curacao" : $s = -4 ; $d = -4 ; break ;
          case "America/Danmarkshavn" : $s = 0 ; $d = 0 ; break ;
          case "America/Dawson" : $s = -8 ; $d = -7 ; break ;
          case "America/Dawson_Creek" : $s = -7 ; $d = -7 ; break ;
          case "America/Denver" : $s = -7 ; $d = -6 ; break ;
          case "America/Detroit" : $s = -5 ; $d = -4 ; break ;
          case "America/Dominica" : $s = -4 ; $d = -4 ; break ;
          case "America/Edmonton" : $s = -7 ; $d = -6 ; break ;
          case "America/Eirunepe" : $s = -4 ; $d = -4 ; break ;
          case "America/El_Salvador" : $s = -6 ; $d = -6 ; break ;
          case "America/Ensenada" : $s = -8 ; $d = -7 ; break ;
          case "America/Fort_Wayne" : $s = -5 ; $d = -4 ; break ;
          case "America/Fortaleza" : $s = -3 ; $d = -3 ; break ;
          case "America/Glace_Bay" : $s = -4 ; $d = -3 ; break ;
          case "America/Godthab" : $s = -3 ; $d = -2 ; break ;
          case "America/Goose_Bay" : $s = -4 ; $d = -3 ; break ;
          case "America/Grand_Turk" : $s = -5 ; $d = -4 ; break ;
          case "America/Grenada" : $s = -4 ; $d = -4 ; break ;
          case "America/Guadeloupe" : $s = -4 ; $d = -4 ; break ;
          case "America/Guatemala" : $s = -6 ; $d = -6 ; break ;
          case "America/Guayaquil" : $s = -5 ; $d = -5 ; break ;
          case "America/Guyana" : $s = -4 ; $d = -4 ; break ;
          case "America/Halifax" : $s = -4 ; $d = -3 ; break ;
          case "America/Havana" : $s = -5 ; $d = -4 ; break ;
          case "America/Hermosillo" : $s = -7 ; $d = -7 ; break ;
          case "America/Indiana/Indianapolis" : $s = -5 ; $d = -4 ; break ;
          case "America/Indiana/Knox" : $s = -6 ; $d = -5 ; break ;
          case "America/Indiana/Marengo" : $s = -5 ; $d = -4 ; break ;
          case "America/Indiana/Petersburg" : $s = -5 ; $d = -4 ; break ;
          case "America/Indiana/Tell_City" : $s = -6 ; $d = -5 ; break ;
          case "America/Indiana/Vevay" : $s = -5 ; $d = -4 ; break ;
          case "America/Indiana/Vincennes" : $s = -5 ; $d = -4 ; break ;
          case "America/Indiana/Winamac" : $s = -5 ; $d = -4 ; break ;
          case "America/Indianapolis" : $s = -5 ; $d = -4 ; break ;
          case "America/Inuvik" : $s = -7 ; $d = -6 ; break ;
          case "America/Iqaluit" : $s = -5 ; $d = -4 ; break ;
          case "America/Jamaica" : $s = -5 ; $d = -5 ; break ;
          case "America/Jujuy" : $s = -3 ; $d = -3 ; break ;
          case "America/Juneau" : $s = -9 ; $d = -8 ; break ;
          case "America/Kentucky/Louisville" : $s = -5 ; $d = -4 ; break ;
          case "America/Kentucky/Monticello" : $s = -5 ; $d = -4 ; break ;
          case "America/Knox_IN" : $s = -6 ; $d = -5 ; break ;
          case "America/Kralendijk" : $s = -4 ; $d = -4 ; break ;
          case "America/La_Paz" : $s = -4 ; $d = -4 ; break ;
          case "America/Lima" : $s = -5 ; $d = -5 ; break ;
          case "America/Los_Angeles" : $s = -8 ; $d = -7 ; break ;
          case "America/Louisville" : $s = -5 ; $d = -4 ; break ;
          case "America/Lower_Princes" : $s = -4 ; $d = -4 ; break ;
          case "America/Maceio" : $s = -3 ; $d = -3 ; break ;
          case "America/Managua" : $s = -6 ; $d = -6 ; break ;
          case "America/Manaus" : $s = -4 ; $d = -4 ; break ;
          case "America/Marigot" : $s = -4 ; $d = -4 ; break ;
          case "America/Martinique" : $s = -4 ; $d = -4 ; break ;
          case "America/Matamoros" : $s = -6 ; $d = -5 ; break ;
          case "America/Mazatlan" : $s = -7 ; $d = -6 ; break ;
          case "America/Mendoza" : $s = -3 ; $d = -3 ; break ;
          case "America/Menominee" : $s = -6 ; $d = -5 ; break ;
          case "America/Merida" : $s = -6 ; $d = -5 ; break ;
          case "America/Metlakatla" : $s = -8 ; $d = -8 ; break ;
          case "America/Mexico_City" : $s = -6 ; $d = -5 ; break ;
          case "America/Miquelon" : $s = -3 ; $d = -2 ; break ;
          case "America/Moncton" : $s = -4 ; $d = -3 ; break ;
          case "America/Monterrey" : $s = -6 ; $d = -5 ; break ;
          case "America/Montevideo" : $s = -3 ; $d = -2 ; break ;
          case "America/Montreal" : $s = -5 ; $d = -4 ; break ;
          case "America/Montserrat" : $s = -4 ; $d = -4 ; break ;
          case "America/Nassau" : $s = -5 ; $d = -4 ; break ;
          case "America/New_York" : $s = -5 ; $d = -4 ; break ;
          case "America/Nipigon" : $s = -5 ; $d = -4 ; break ;
          case "America/Nome" : $s = -9 ; $d = -8 ; break ;
          case "America/Noronha" : $s = -2 ; $d = -2 ; break ;
          case "America/North_Dakota/Beulah" : $s = -6 ; $d = -5 ; break ;
          case "America/North_Dakota/Center" : $s = -6 ; $d = -5 ; break ;
          case "America/North_Dakota/New_Salem" : $s = -6 ; $d = -5 ; break ;
          case "America/Ojinaga" : $s = -7 ; $d = -6 ; break ;
          case "America/Panama" : $s = -5 ; $d = -5 ; break ;
          case "America/Pangnirtung" : $s = -5 ; $d = -4 ; break ;
          case "America/Paramaribo" : $s = -3 ; $d = -3 ; break ;
          case "America/Phoenix" : $s = -7 ; $d = -7 ; break ;
          case "America/Port_of_Spain" : $s = -4 ; $d = -4 ; break ;
          case "America/Port-au-Prince" : $s = -5 ; $d = -4 ; break ;
          case "America/Porto_Acre" : $s = -4 ; $d = -4 ; break ;
          case "America/Porto_Velho" : $s = -4 ; $d = -4 ; break ;
          case "America/Puerto_Rico" : $s = -4 ; $d = -4 ; break ;
          case "America/Rainy_River" : $s = -6 ; $d = -5 ; break ;
          case "America/Rankin_Inlet" : $s = -6 ; $d = -5 ; break ;
          case "America/Recife" : $s = -3 ; $d = -3 ; break ;
          case "America/Regina" : $s = -6 ; $d = -6 ; break ;
          case "America/Resolute" : $s = -6 ; $d = -5 ; break ;
          case "America/Rio_Branco" : $s = -4 ; $d = -4 ; break ;
          case "America/Rosario" : $s = -3 ; $d = -3 ; break ;
          case "America/Santa_Isabel" : $s = -8 ; $d = -7 ; break ;
          case "America/Santarem" : $s = -3 ; $d = -3 ; break ;
          case "America/Santiago" : $s = -4 ; $d = -3 ; break ;
          case "America/Santo_Domingo" : $s = -4 ; $d = -4 ; break ;
          case "America/Sao_Paulo" : $s = -3 ; $d = -2 ; break ;
          case "America/Scoresbysund" : $s = -1 ; $d = 0 ; break ;
          case "America/Shiprock" : $s = -7 ; $d = -6 ; break ;
          case "America/Sitka" : $s = -9 ; $d = -8 ; break ;
          case "America/St_Barthelemy" : $s = -4 ; $d = -4 ; break ;
          case "America/St_Johns" : $s = -3.5 ; $d = -2.5 ; break ;
          case "America/St_Kitts" : $s = -4 ; $d = -4 ; break ;
          case "America/St_Lucia" : $s = -4 ; $d = -4 ; break ;
          case "America/St_Thomas" : $s = -4 ; $d = -4 ; break ;
          case "America/St_Vincent" : $s = -4 ; $d = -4 ; break ;
          case "America/Swift_Current" : $s = -6 ; $d = -6 ; break ;
          case "America/Tegucigalpa" : $s = -6 ; $d = -6 ; break ;
          case "America/Thule" : $s = -4 ; $d = -3 ; break ;
          case "America/Thunder_Bay" : $s = -5 ; $d = -4 ; break ;
          case "America/Tijuana" : $s = -8 ; $d = -7 ; break ;
          case "America/Toronto" : $s = -5 ; $d = -4 ; break ;
          case "America/Tortola" : $s = -4 ; $d = -4 ; break ;
          case "America/Vancouver" : $s = -8 ; $d = -7 ; break ;
          case "America/Virgin" : $s = -4 ; $d = -4 ; break ;
          case "America/Whitehorse" : $s = -8 ; $d = -7 ; break ;
          case "America/Winnipeg" : $s = -6 ; $d = -5 ; break ;
          case "America/Yakutat" : $s = -9 ; $d = -8 ; break ;
          case "America/Yellowknife" : $s = -7 ; $d = -6 ; break ;
          case "Antarctica/Casey" : $s = 11 ; $d = 8 ; break ;
          case "Antarctica/Davis" : $s = 5 ; $d = 7 ; break ;
          case "Antarctica/DumontDUrville" : $s = 10 ; $d = 10 ; break ;
          case "Antarctica/Macquarie" : $s = 11 ; $d = 11 ; break ;
          case "Antarctica/Mawson" : $s = 5 ; $d = 5 ; break ;
          case "Antarctica/McMurdo" : $s = 12 ; $d = 13 ; break ;
          case "Antarctica/Palmer" : $s = -4 ; $d = -3 ; break ;
          case "Antarctica/Rothera" : $s = -3 ; $d = -3 ; break ;
          case "Antarctica/South_Pole" : $s = 12 ; $d = 13 ; break ;
          case "Antarctica/Syowa" : $s = 3 ; $d = 3 ; break ;
          case "Antarctica/Vostok" : $s = 6 ; $d = 6 ; break ;
          case "Arctic/Longyearbyen" : $s = 1 ; $d = 2 ; break ;
          case "Asia/Aden" : $s = 3 ; $d = 3 ; break ;
          case "Asia/Almaty" : $s = 6 ; $d = 6 ; break ;
          case "Asia/Amman" : $s = 3 ; $d = 3 ; break ;
          case "Asia/Anadyr" : $s = 12 ; $d = 12 ; break ;
          case "Asia/Aqtau" : $s = 5 ; $d = 5 ; break ;
          case "Asia/Aqtobe" : $s = 5 ; $d = 5 ; break ;
          case "Asia/Ashgabat" : $s = 5 ; $d = 5 ; break ;
          case "Asia/Ashkhabad" : $s = 5 ; $d = 5 ; break ;
          case "Asia/Baghdad" : $s = 3 ; $d = 3 ; break ;
          case "Asia/Bahrain" : $s = 3 ; $d = 3 ; break ;
          case "Asia/Baku" : $s = 4 ; $d = 5 ; break ;
          case "Asia/Bangkok" : $s = 7 ; $d = 7 ; break ;
          case "Asia/Beirut" : $s = 2 ; $d = 3 ; break ;
          case "Asia/Bishkek" : $s = 6 ; $d = 6 ; break ;
          case "Asia/Brunei" : $s = 8 ; $d = 8 ; break ;
          case "Asia/Calcutta" : $s = 5.5 ; $d = 5.5 ; break ;
          case "Asia/Choibalsan" : $s = 8 ; $d = 8 ; break ;
          case "Asia/Chongqing" : $s = 8 ; $d = 8 ; break ;
          case "Asia/Chungking" : $s = 8 ; $d = 8 ; break ;
          case "Asia/Colombo" : $s = 5.5 ; $d = 5.5 ; break ;
          case "Asia/Dacca" : $s = 6 ; $d = 6 ; break ;
          case "Asia/Damascus" : $s = 2 ; $d = 3 ; break ;
          case "Asia/Dhaka" : $s = 6 ; $d = 6 ; break ;
          case "Asia/Dili" : $s = 9 ; $d = 9 ; break ;
          case "Asia/Dubai" : $s = 4 ; $d = 4 ; break ;
          case "Asia/Dushanbe" : $s = 5 ; $d = 5 ; break ;
          case "Asia/Gaza" : $s = 2 ; $d = 3 ; break ;
          case "Asia/Harbin" : $s = 8 ; $d = 8 ; break ;
          case "Asia/Hebron" : $s = 2 ; $d = 3 ; break ;
          case "Asia/Ho_Chi_Minh" : $s = 7 ; $d = 7 ; break ;
          case "Asia/Hong_Kong" : $s = 8 ; $d = 8 ; break ;
          case "Asia/Hovd" : $s = 7 ; $d = 7 ; break ;
          case "Asia/Irkutsk" : $s = 9 ; $d = 9 ; break ;
          case "Asia/Istanbul" : $s = 2 ; $d = 3 ; break ;
          case "Asia/Jakarta" : $s = 7 ; $d = 7 ; break ;
          case "Asia/Jayapura" : $s = 9 ; $d = 9 ; break ;
          case "Asia/Jerusalem" : $s = 2 ; $d = 3 ; break ;
          case "Asia/Kabul" : $s = 4.5 ; $d = 4.5 ; break ;
          case "Asia/Kamchatka" : $s = 12 ; $d = 12 ; break ;
          case "Asia/Karachi" : $s = 5 ; $d = 5 ; break ;
          case "Asia/Kashgar" : $s = 8 ; $d = 8 ; break ;
          case "Asia/Kathmandu" : $s = 5.75 ; $d = 5.75 ; break ;
          case "Asia/Katmandu" : $s = 5.75 ; $d = 5.75 ; break ;
          case "Asia/Kolkata" : $s = 5.5 ; $d = 5.5 ; break ;
          case "Asia/Krasnoyarsk" : $s = 8 ; $d = 8 ; break ;
          case "Asia/Kuala_Lumpur" : $s = 8 ; $d = 8 ; break ;
          case "Asia/Kuching" : $s = 8 ; $d = 8 ; break ;
          case "Asia/Kuwait" : $s = 3 ; $d = 3 ; break ;
          case "Asia/Macao" : $s = 8 ; $d = 8 ; break ;
          case "Asia/Macau" : $s = 8 ; $d = 8 ; break ;
          case "Asia/Magadan" : $s = 12 ; $d = 12 ; break ;
          case "Asia/Makassar" : $s = 8 ; $d = 8 ; break ;
          case "Asia/Manila" : $s = 8 ; $d = 8 ; break ;
          case "Asia/Muscat" : $s = 4 ; $d = 4 ; break ;
          case "Asia/Nicosia" : $s = 2 ; $d = 3 ; break ;
          case "Asia/Novokuznetsk" : $s = 7 ; $d = 7 ; break ;
          case "Asia/Novosibirsk" : $s = 7 ; $d = 7 ; break ;
          case "Asia/Omsk" : $s = 7 ; $d = 7 ; break ;
          case "Asia/Oral" : $s = 5 ; $d = 5 ; break ;
          case "Asia/Phnom_Penh" : $s = 7 ; $d = 7 ; break ;
          case "Asia/Pontianak" : $s = 7 ; $d = 7 ; break ;
          case "Asia/Pyongyang" : $s = 9 ; $d = 9 ; break ;
          case "Asia/Qatar" : $s = 3 ; $d = 3 ; break ;
          case "Asia/Qyzylorda" : $s = 6 ; $d = 6 ; break ;
          case "Asia/Rangoon" : $s = 6.5 ; $d = 6.5 ; break ;
          case "Asia/Riyadh" : $s = 3 ; $d = 3 ; break ;
          case "Asia/Saigon" : $s = 7 ; $d = 7 ; break ;
          case "Asia/Sakhalin" : $s = 11 ; $d = 11 ; break ;
          case "Asia/Samarkand" : $s = 5 ; $d = 5 ; break ;
          case "Asia/Seoul" : $s = 9 ; $d = 9 ; break ;
          case "Asia/Shanghai" : $s = 8 ; $d = 8 ; break ;
          case "Asia/Singapore" : $s = 8 ; $d = 8 ; break ;
          case "Asia/Taipei" : $s = 8 ; $d = 8 ; break ;
          case "Asia/Tashkent" : $s = 5 ; $d = 5 ; break ;
          case "Asia/Tbilisi" : $s = 4 ; $d = 4 ; break ;
          case "Asia/Tehran" : $s = 3.5 ; $d = 4.5 ; break ;
          case "Asia/Tel_Aviv" : $s = 2 ; $d = 3 ; break ;
          case "Asia/Thimbu" : $s = 6 ; $d = 6 ; break ;
          case "Asia/Thimphu" : $s = 6 ; $d = 6 ; break ;
          case "Asia/Tokyo" : $s = 9 ; $d = 9 ; break ;
          case "Asia/Ujung_Pandang" : $s = 8 ; $d = 8 ; break ;
          case "Asia/Ulaanbaatar" : $s = 8 ; $d = 8 ; break ;
          case "Asia/Ulan_Bator" : $s = 8 ; $d = 8 ; break ;
          case "Asia/Urumqi" : $s = 8 ; $d = 8 ; break ;
          case "Asia/Vientiane" : $s = 7 ; $d = 7 ; break ;
          case "Asia/Vladivostok" : $s = 11 ; $d = 11 ; break ;
          case "Asia/Yakutsk" : $s = 10 ; $d = 10 ; break ;
          case "Asia/Yekaterinburg" : $s = 6 ; $d = 6 ; break ;
          case "Asia/Yerevan" : $s = 4 ; $d = 4 ; break ;
          case "Atlantic/Azores" : $s = -1 ; $d = 0 ; break ;
          case "Atlantic/Bermuda" : $s = -4 ; $d = -3 ; break ;
          case "Atlantic/Canary" : $s = 0 ; $d = 1 ; break ;
          case "Atlantic/Cape_Verde" : $s = -1 ; $d = -1 ; break ;
          case "Atlantic/Faeroe" : $s = 0 ; $d = 1 ; break ;
          case "Atlantic/Faroe" : $s = 0 ; $d = 1 ; break ;
          case "Atlantic/Jan_Mayen" : $s = 1 ; $d = 2 ; break ;
          case "Atlantic/Madeira" : $s = 0 ; $d = 1 ; break ;
          case "Atlantic/Reykjavik" : $s = 0 ; $d = 0 ; break ;
          case "Atlantic/South_Georgia" : $s = -2 ; $d = -2 ; break ;
          case "Atlantic/St_Helena" : $s = 0 ; $d = 0 ; break ;
          case "Atlantic/Stanley" : $s = -3 ; $d = -3 ; break ;
          case "Australia/ACT" : $s = 10 ; $d = 11 ; break ;
          case "Australia/Adelaide" : $s = 9.5 ; $d = 10.5 ; break ;
          case "Australia/Brisbane" : $s = 10 ; $d = 10 ; break ;
          case "Australia/Broken_Hill" : $s = 9.5 ; $d = 10.5 ; break ;
          case "Australia/Canberra" : $s = 10 ; $d = 11 ; break ;
          case "Australia/Currie" : $s = 10 ; $d = 11 ; break ;
          case "Australia/Darwin" : $s = 9.5 ; $d = 9.5 ; break ;
          case "Australia/Eucla" : $s = 8.75 ; $d = 8.75 ; break ;
          case "Australia/Hobart" : $s = 10 ; $d = 11 ; break ;
          case "Australia/LHI" : $s = 10.5 ; $d = 11 ; break ;
          case "Australia/Lindeman" : $s = 10 ; $d = 10 ; break ;
          case "Australia/Lord_Howe" : $s = 10.5 ; $d = 11 ; break ;
          case "Australia/Melbourne" : $s = 10 ; $d = 11 ; break ;
          case "Australia/North" : $s = 9.5 ; $d = 9.5 ; break ;
          case "Australia/NSW" : $s = 10 ; $d = 11 ; break ;
          case "Australia/Perth" : $s = 8 ; $d = 8 ; break ;
          case "Australia/Queensland" : $s = 10 ; $d = 10 ; break ;
          case "Australia/South" : $s = 9.5 ; $d = 10.5 ; break ;
          case "Australia/Sydney" : $s = 10 ; $d = 11 ; break ;
          case "Australia/Tasmania" : $s = 10 ; $d = 11 ; break ;
          case "Australia/Victoria" : $s = 10 ; $d = 11 ; break ;
          case "Australia/West" : $s = 8 ; $d = 8 ; break ;
          case "Australia/Yancowinna" : $s = 9.5 ; $d = 10.5 ; break ;
          case "Brazil/Acre" : $s = -4 ; $d = -4 ; break ;
          case "Brazil/DeNoronha" : $s = -2 ; $d = -2 ; break ;
          case "Brazil/East" : $s = -3 ; $d = -2 ; break ;
          case "Brazil/West" : $s = -4 ; $d = -4 ; break ;
          case "Canada/Atlantic" : $s = -4 ; $d = -3 ; break ;
          case "Canada/Central" : $s = -6 ; $d = -5 ; break ;
          case "Canada/Eastern" : $s = -5 ; $d = -4 ; break ;
          case "Canada/East-Saskatchewan" : $s = -6 ; $d = -6 ; break ;
          case "Canada/Mountain" : $s = -7 ; $d = -6 ; break ;
          case "Canada/Newfoundland" : $s = -3.5 ; $d = -2.5 ; break ;
          case "Canada/Pacific" : $s = -8 ; $d = -7 ; break ;
          case "Canada/Saskatchewan" : $s = -6 ; $d = -6 ; break ;
          case "Canada/Yukon" : $s = -8 ; $d = -7 ; break ;
          case "CET" : $s = 1 ; $d = 2 ; break ;
          case "Chile/Continental" : $s = -4 ; $d = -3 ; break ;
          case "Chile/EasterIsland" : $s = -6 ; $d = -5 ; break ;
          case "CST6CDT" : $s = -6 ; $d = -5 ; break ;
          case "Cuba" : $s = -5 ; $d = -4 ; break ;
          case "EET" : $s = 2 ; $d = 3 ; break ;
          case "Egypt" : $s = 2 ; $d = 2 ; break ;
          case "Eire" : $s = 0 ; $d = 1 ; break ;
          case "EST" : $s = -5 ; $d = -5 ; break ;
          case "EST5EDT" : $s = -5 ; $d = -4 ; break ;
          case "Etc./GMT" : $s = 0 ; $d = 0 ; break ;
          case "Etc./GMT" : $s = 0 ; $d = 0 ; break ;
          case "Etc./UCT" : $s = 0 ; $d = 0 ; break ;
          case "Etc./Universal" : $s = 0 ; $d = 0 ; break ;
          case "Etc./UTC" : $s = 0 ; $d = 0 ; break ;
          case "Etc./Zulu" : $s = 0 ; $d = 0 ; break ;
          case "Europe/Amsterdam" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Andorra" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Athens" : $s = 2 ; $d = 3 ; break ;
          case "Europe/Belfast" : $s = 0 ; $d = 1 ; break ;
          case "Europe/Belgrade" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Berlin" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Bratislava" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Brussels" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Bucharest" : $s = 2 ; $d = 3 ; break ;
          case "Europe/Budapest" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Chisinau" : $s = 2 ; $d = 3 ; break ;
          case "Europe/Copenhagen" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Dublin" : $s = 0 ; $d = 1 ; break ;
          case "Europe/Gibraltar" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Guernsey" : $s = 0 ; $d = 1 ; break ;
          case "Europe/Helsinki" : $s = 2 ; $d = 3 ; break ;
          case "Europe/Isle_of_Man" : $s = 0 ; $d = 1 ; break ;
          case "Europe/Istanbul" : $s = 2 ; $d = 3 ; break ;
          case "Europe/Jersey" : $s = 0 ; $d = 1 ; break ;
          case "Europe/Kaliningrad" : $s = 3 ; $d = 3 ; break ;
          case "Europe/Kiev" : $s = 2 ; $d = 3 ; break ;
          case "Europe/Lisbon" : $s = 0 ; $d = 1 ; break ;
          case "Europe/Ljubljana" : $s = 1 ; $d = 2 ; break ;
          case "Europe/London" : $s = 0 ; $d = 1 ; break ;
          case "Europe/Luxembourg" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Madrid" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Malta" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Mariehamn" : $s = 2 ; $d = 3 ; break ;
          case "Europe/Minsk" : $s = 3 ; $d = 3 ; break ;
          case "Europe/Monaco" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Moscow" : $s = 4 ; $d = 4 ; break ;
          case "Europe/Nicosia" : $s = 2 ; $d = 3 ; break ;
          case "Europe/Oslo" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Paris" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Podgorica" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Prague" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Riga" : $s = 2 ; $d = 3 ; break ;
          case "Europe/Rome" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Samara" : $s = 4 ; $d = 4 ; break ;
          case "Europe/San_Marino" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Sarajevo" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Simferopol" : $s = 2 ; $d = 3 ; break ;
          case "Europe/Skopje" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Sofia" : $s = 2 ; $d = 3 ; break ;
          case "Europe/Stockholm" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Tallinn" : $s = 2 ; $d = 3 ; break ;
          case "Europe/Tirane" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Tiraspol" : $s = 2 ; $d = 3 ; break ;
          case "Europe/Uzhgorod" : $s = 2 ; $d = 3 ; break ;
          case "Europe/Vaduz" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Vatican" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Vienna" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Vilnius" : $s = 2 ; $d = 3 ; break ;
          case "Europe/Volgograd" : $s = 4 ; $d = 4 ; break ;
          case "Europe/Warsaw" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Zagreb" : $s = 1 ; $d = 2 ; break ;
          case "Europe/Zaporozhye" : $s = 2 ; $d = 3 ; break ;
          case "Europe/Zurich" : $s = 1 ; $d = 2 ; break ;
          case "GB" : $s = 0 ; $d = 1 ; break ;
          case "GB-Eire" : $s = 0 ; $d = 1 ; break ;
          case "GMT" : $s = 0 ; $d = 0 ; break ;
          case "GMT" : $s = 0 ; $d = 0 ; break ;
          case "GMT0" : $s = 0 ; $d = 0 ; break ;
          case "GMT-" : $s = 0 ; $d = 0 ; break ;
          case "Greenwich" : $s = 0 ; $d = 0 ; break ;
          case "Hong Kong" : $s = 8 ; $d = 8 ; break ;
          case "HST" : $s = -10 ; $d = -10 ; break ;
          case "Iceland" : $s = 0 ; $d = 0 ; break ;
          case "Indian/Antananarivo" : $s = 3 ; $d = 3 ; break ;
          case "Indian/Chagos" : $s = 6 ; $d = 6 ; break ;
          case "Indian/Christmas" : $s = 7 ; $d = 7 ; break ;
          case "Indian/Cocos" : $s = 6.5 ; $d = 6.5 ; break ;
          case "Indian/Comoro" : $s = 3 ; $d = 3 ; break ;
          case "Indian/Kerguelen" : $s = 5 ; $d = 5 ; break ;
          case "Indian/Mahe" : $s = 4 ; $d = 4 ; break ;
          case "Indian/Maldives" : $s = 5 ; $d = 5 ; break ;
          case "Indian/Mauritius" : $s = 4 ; $d = 4 ; break ;
          case "Indian/Mayotte" : $s = 3 ; $d = 3 ; break ;
          case "Indian/Reunion" : $s = 4 ; $d = 4 ; break ;
          case "Iran" : $s = 3.5 ; $d = 4.5 ; break ;
          case "Israel" : $s = 2 ; $d = 3 ; break ;
          case "Jamaica" : $s = -5 ; $d = -5 ; break ;
          case "Japan" : $s = 9 ; $d = 9 ; break ;
          case "JST-9" : $s = 9 ; $d = 9 ; break ;
          case "Kwajalein" : $s = 12 ; $d = 12 ; break ;
          case "Libya" : $s = 2 ; $d = 2 ; break ;
          case "MET" : $s = 1 ; $d = 2 ; break ;
          case "Mexico/BajaNorte" : $s = -8 ; $d = -7 ; break ;
          case "Mexico/BajaSur" : $s = -7 ; $d = -6 ; break ;
          case "Mexico/General" : $s = -6 ; $d = -5 ; break ;
          case "MST" : $s = -7 ; $d = -7 ; break ;
          case "MST7MDT" : $s = -7 ; $d = -6 ; break ;
          case "Navajo" : $s = -7 ; $d = -6 ; break ;
          case "NZ" : $s = 12 ; $d = 13 ; break ;
          case "NZ-CHAT" : $s = 12.75 ; $d = 13.75 ; break ;
          case "Pacific/Apia" : $s = 13 ; $d = 14 ; break ;
          case "Pacific/Auckland" : $s = 12 ; $d = 13 ; break ;
          case "Pacific/Chatham" : $s = 12.75 ; $d = 13.75 ; break ;
          case "Pacific/Chuuk" : $s = 10 ; $d = 10 ; break ;
          case "Pacific/Easter" : $s = -6 ; $d = -5 ; break ;
          case "Pacific/Efate" : $s = 11 ; $d = 11 ; break ;
          case "Pacific/Enderbury" : $s = 13 ; $d = 13 ; break ;
          case "Pacific/Fakaofo" : $s = 13 ; $d = 13 ; break ;
          case "Pacific/Fiji" : $s = 12 ; $d = 13 ; break ;
          case "Pacific/Funafuti" : $s = 12 ; $d = 12 ; break ;
          case "Pacific/Galapagos" : $s = -6 ; $d = -6 ; break ;
          case "Pacific/Gambier" : $s = -9 ; $d = -9 ; break ;
          case "Pacific/Guadalcanal" : $s = 11 ; $d = 11 ; break ;
          case "Pacific/Guam" : $s = 10 ; $d = 10 ; break ;
          case "Pacific/Honolulu" : $s = -10 ; $d = -10 ; break ;
          case "Pacific/Johnston" : $s = -10 ; $d = -10 ; break ;
          case "Pacific/Kiritimati" : $s = 14 ; $d = 14 ; break ;
          case "Pacific/Kosrae" : $s = 11 ; $d = 11 ; break ;
          case "Pacific/Kwajalein" : $s = 12 ; $d = 12 ; break ;
          case "Pacific/Majuro" : $s = 12 ; $d = 12 ; break ;
          case "Pacific/Marquesas" : $s = -9.5 ; $d = -9.5 ; break ;
          case "Pacific/Midway" : $s = -11 ; $d = -11 ; break ;
          case "Pacific/Nauru" : $s = 12 ; $d = 12 ; break ;
          case "Pacific/Niue" : $s = -11 ; $d = -11 ; break ;
          case "Pacific/Norfolk" : $s = 11.5 ; $d = 11.5 ; break ;
          case "Pacific/Noumea" : $s = 11 ; $d = 11 ; break ;
          case "Pacific/Pago_Pago" : $s = -11 ; $d = -11 ; break ;
          case "Pacific/Palau" : $s = 9 ; $d = 9 ; break ;
          case "Pacific/Pitcairn" : $s = -8 ; $d = -8 ; break ;
          case "Pacific/Pohnpei" : $s = 11 ; $d = 11 ; break ;
          case "Pacific/Ponape" : $s = 11 ; $d = 11 ; break ;
          case "Pacific/Port_Moresby" : $s = 10 ; $d = 10 ; break ;
          case "Pacific/Rarotonga" : $s = -10 ; $d = -10 ; break ;
          case "Pacific/Saipan" : $s = 10 ; $d = 10 ; break ;
          case "Pacific/Samoa" : $s = -11 ; $d = -11 ; break ;
          case "Pacific/Tahiti" : $s = -10 ; $d = -10 ; break ;
          case "Pacific/Tarawa" : $s = 12 ; $d = 12 ; break ;
          case "Pacific/Tongatapu" : $s = 13 ; $d = 13 ; break ;
          case "Pacific/Truk" : $s = 10 ; $d = 10 ; break ;
          case "Pacific/Wake" : $s = 12 ; $d = 12 ; break ;
          case "Pacific/Wallis" : $s = 12 ; $d = 12 ; break ;
          case "Pacific/Yap" : $s = 10 ; $d = 10 ; break ;
          case "Poland" : $s = 1 ; $d = 2 ; break ;
          case "Portugal" : $s = 0 ; $d = 1 ; break ;
          case "PRC" : $s = 8 ; $d = 8 ; break ;
          case "PST8PDT" : $s = -8 ; $d = -7 ; break ;
          case "ROC" : $s = 8 ; $d = 8 ; break ;
          case "ROK" : $s = 9 ; $d = 9 ; break ;
          case "Singapore" : $s = 8 ; $d = 8 ; break ;
          case "Turkey" : $s = 2 ; $d = 3 ; break ;
          case "UCT" : $s = 0 ; $d = 0 ; break ;
          case "Universal" : $s = 0 ; $d = 0 ; break ;
          case "US/Alaska" : $s = -9 ; $d = -8 ; break ;
          case "US/Aleutian" : $s = -10 ; $d = -9 ; break ;
          case "US/Arizona" : $s = -7 ; $d = -7 ; break ;
          case "US/Central" : $s = -6 ; $d = -5 ; break ;
          case "US/Eastern" : $s = -5 ; $d = -4 ; break ;
          case "US/East-Indiana" : $s = -5 ; $d = -4 ; break ;
          case "US/Hawaii" : $s = -10 ; $d = -10 ; break ;
          case "US/Indiana-Starke" : $s = -6 ; $d = -5 ; break ;
          case "US/Michigan" : $s = -5 ; $d = -4 ; break ;
          case "US/Mountain" : $s = -7 ; $d = -6 ; break ;
          case "US/Pacific" : $s = -8 ; $d = -7 ; break ;
          case "US/Pacific-New" : $s = -8 ; $d = -7 ; break ;
          case "US/Samoa" : $s = -11 ; $d = -11 ; break ;
          case "UTC" : $s = 0 ; $d = 0 ; break ;
          case "WET" : $s = 0 ; $d = 1 ; break ;
          case "W-SU" : $s = 4 ; $d = 4 ; break ;
          case "Zulu" : $s = 0 ; $d = 0 ; break ;
        }

        if ( date ( 'I' ) ) {
          return $d ;
        } else {
          return $s ;
        }

    }

    /**
     * Returns an encrypted & utf8-encoded
     */
    function encryptCc($decrypted_string) {
      $i = array('0','1','2','3','4','5','6','7','8','9');
      $o = array('a','#','X','q','~','!','W','@','%','h');
      $encrypted_string = str_replace( $i , $o , preg_replace( "/[^0-9]/" , "", $decrypted_string ) ) ;
      return $encrypted_string;
    }

    /**
     * Returns decrypted original string
     */
    function decryptCc($encrypted_string) {
      $i = array('a','#','X','q','~','!','W','@','%','h');
      $o = array('0','1','2','3','4','5','6','7','8','9');
      if ($_SERVER['HTTPS']) {
        $decrypted_string = str_replace( $i , $o , $encrypted_string ) ;
      } else {
        if(strlen($encrypted_string)>4){
          $decrypted_string = '****' . substr( str_replace( $i , $o , $encrypted_string ) ,-4) ;        
        } else {
          $decrypted_string = '****' ;        
        }
      }
      return $decrypted_string;
    }

    /**
     * timezoneDelta
     *
     * return timestamp
     */
    public function zActual ( $row )
    {
      if($row['override_reason']){
        $rate_product = $row['override_rate'] ;
        $rate_accessessories = $row['override_arate'] ;
        $rate_shipping = $row['override_shipping'] ;
        $rate_handling = $row['override_handling'] ;
      } else {
        $rate_product = $row['rate'] ;
        $rate_accessessories = $row['arate'] ;
        $rate_shipping = $row['shipping_fee'] ;
        $rate_handling = $row['handling_fee'] ;
      }
      $actual = ( $rate_product + $rate_accessessories + $rate_shipping ) * $row['quantity'] + $rate_handling ;
      return $actual ;
    }

    /**
     * timezoneDelta
     *
     * return timestamp
     */
    public function zOrder ( $row , $btn_class , $btn_label )
    {

      if( $btn_label ){
        $btn_label = '<button class="btn btn-warning pull-right status status-' . $btn_class . '" href="javascript:void(0);" id="' . $row['orders_id'] . '">' . $btn_label . ' #' . $row['orders_id'] . '</button>' ;
      } else if ( ( $row['invoicedate'] ) && ( $row['invoicedate'] != '0000-00-00 00:00:00' ) ) {
        $btn_label = 'Invoice #&nbsp;' . $row['invoice_number'] . '<p>was processed on &nbsp;' . $row['invoicedate'] ;
      } else {
        // $btn_label = 'Invoice #&nbsp;' . $row['invoice_number'] . '<p>was processed on &nbsp;' . $row['invoicedate'] ;
      }      
      if ( $row['approved_by'] ) {
        $label_approved_by = 'Approved By:' ;
      }
      if ( ( $row['approvedate'] ) && ( $row['approvedate'] != '0000-00-00 00:00:00' ) ) {
        $label_approvedate = 'Approved On:' ;
      } else {
        $row['approvedate'] = null ;
      }
      if ( $row['payment'] == 'Credit Card' ) {
        $label_credit_card = 'Credit Card:' ;
        if($row['cc_num']){
          $credit_card = 'CC#=' . $row['cc_num'] . ', CCV=' . $row['cc_ver'] . ', EXP=' . $row['cc_exp'] ;
        } else {
          $credit_card = '********************';
        }
      }
      if ( $row['inventory'] ) {
        $label_inventory = 'Inventory:' ;
      }
      if ( $row['m2m_e_code'] ) {
        $label_m2m_e_code = 'VZW Rep:' ;        
        $label_m2m_email = 'Email:' ;        
        $label_m2m_phone = 'Phone:' ;        
      }
      if ( $row['shipping_track'] ) {
        $label_shipping_track = 'Tracking Number:' ;
      }

      if($row['override_reason']){

        $discount_reason_label = 'Discount Reason:';
        $discount_reason = $row['override_reason'];
        if($row['rate'] != $row['override_rate']){
          $discount_rate = '&nbsp;&nbsp;<span class="text-green">less $' . number_format( ( $row['rate'] - $row['override_rate'] ) / 100 , 2 , '.' , ',' ) . ' per unit</span>' ;
        }
        $rate_product = $row['override_rate'] ;
        //
        $discount_reason_label = 'Discount Reason:';
        $discount_reason = $row['override_reason'];
        if($row['arate'] != $row['override_arate']){
          $discount_arate = '&nbsp;&nbsp;<span class="text-green">less $' . number_format( ( $row['arate'] - $row['override_arate'] ) / 100 , 2 , '.' , ',' ) . ' per unit</span>' ;
        }
        $rate_accessessories = $row['override_arate'] ;
        //
        if(($row['rate'] != $row['override_rate'])||($row['arate'] != $row['override_arate'])){
          $discount_order = '&nbsp;&nbsp;<span class="text-red"> savings of $' . number_format( ( ( $row['rate'] + $row['arate'] - $row['override_rate'] - $row['override_arate'] ) * $row['quantity'] ) / 100 , 2 , '.' , ',' ) . '</span>' ;
        }
        //
        $discount_reason_label = 'Discount Reason:';
        $discount_reason = $row['override_reason'];
        if($row['shipping_fee'] != $row['override_shipping']){
          $discount_shipping = '&nbsp;&nbsp;<span class="text-red"> savings of $' . number_format( ( ( $row['shipping_fee'] - $row['override_shipping'] ) * $row['quantity'] ) / 100 , 2 , '.' , ',' ) . '</span>' ;
        }
        $rate_shipping = $row['override_shipping'] ;
        //
        $discount_reason_label = 'Discount Reason:';
        $discount_reason = $row['override_reason'];
        if($row['handling_fee'] != $row['override_handling']){
          $discount_handling = '&nbsp;&nbsp;<span class="text-red"> savings of $' . number_format( ( $row['handling_fee'] - $row['override_handling'] ) / 100 , 2 , '.' , ',' ) . '</span>' ;
        }
        $rate_handling = $row['override_handling'] ;

      } else {
        $rate_product = $row['rate'] ;
        $rate_accessessories = $row['arate'] ;
        $rate_shipping = $row['shipping_fee'] ;
        $rate_handling = $row['handling_fee'] ;
      }

      if ( ( str_replace('/' , '' , strtolower($row['reseller']) ) != 'na' ) && ( $row['reseller'] ) ) {
        $resale_label = 'Resale Number:' ;
        $reseller = $row['reseller'] ;
      } else {
        $resale_label = 'State:' ;
        $reseller = $row['taxes_state_name'] ;
        $resale_state_label = 'Tax Rate:' ;
        $resale_rate = number_format( ( $row['taxes_rate'] ) / 100 , 2 , '.' , ',' ) . '%' ;
        $resale_amount_label = 'Taxes:' ;
        $resale_amount = '$' . number_format( ( $row['taxes_amount'] ) / 100 , 2 , '.' , ',' ) ;
      }

      $total_extended = $row['quantity'] * ( $rate_product + $rate_accessessories ) + $rate_handling ;

      $total_shipping = $row['quantity'] * $rate_shipping ;

      $total_grand = ( $rate_product + $rate_accessessories + $rate_shipping ) * $row['quantity'] + $rate_handling ;

      $out = '<div class="background-green" id="details-' . $btn_class . '_' . $row['orders_id'] . '" style="display: none;"><table class="background-green width-100"><tr>' 
              . '<th><span class="pull-right">Account:</span></th><td><b>' . $row['account_name'] . '</b></td>'
              . '<th><span class="pull-right">Billing Contact:</span></th><td>' . $row['account_contact'] . '</td>'
              . '<th><span class="pull-right">Position Plus Rep:</span></th><td>' . $row['rep_name'] . '</td>'
              . '<th><span class="pull-right">' . $label_m2m_e_code . '</span></th><td>' . $row['m2m_name'] . '&nbsp;&nbsp; ' . $row['m2m_e_code'] . '</td>'
              . '</tr><tr>'
              . '<th><span class="pull-right">Sales Order #</span></th><td>' . $row['orders_id'] . '</td>'
              . '<th><span class="pull-right">Email:</span></th><td>' . $row['account_email'] . '</td>'
              . '<th><span class="pull-right">Email:</span></th><td>' . $row['rep_email'] . '</td>'
              . '<th><span class="pull-right">' . $label_m2m_email . '</span></th><td>' . $row['m2m_email'] . '</td>'
              . '</tr><tr>'
              . '<th><span class="pull-right">Purchase Order #</span></th><td>' . $row['po'] . '</td>'
              . '<th><span class="pull-right">Phone:</span></th><td>' . $row['account_phone'] . '</td>'
              . '<th><span class="pull-right">Phone:</span></th><td>' . $row['rep_phone'] . '</td>'
              . '<th><span class="pull-right">' . $label_m2m_phone . '</span></th><td>' . $row['m2m_phone'] . '</td>'
              . '</tr><tr>'
              . '<td colspan="8"><div class="background-hr">&nbsp;</div></td>'
              . '</tr><tr>'
              . '<th><span class="pull-right">Billing Address:</span></th><td colspan="3">' . $row['address_billing'] . '</td>'
              . '<th><span class="pull-right">Shipping Address:</span></th><td colspan="3">' . $row['address_shipping'] . '</td>'
              . '</tr><tr>'

              // . '<td colspan="2"></td>'
              // . '<th><span class="pull-right">Fax:</span></th><td>' . $row['account_fax'] . '</td>'
              // . '<th><span class="pull-right">Fax:</span></th><td>' . $row['rep_fax'] . '</td>'
              // . '<th><span class="pull-right">Fax:</span></th><td>' . $row['m2m_fax'] . '</td>'
              // . '</tr><tr>'

              // . '<td colspan="6">'
              // . $total_extended
              // . ' = '
              // . $row['quantity']
              // . ' * '
              // . '(' . $rate_product . '+' . $rate_accessessories . ')'
              // . '</td>'
              // . '</tr><tr>'
              // . '<td colspan="6">'
              // . $total_grand
              // . ' = '
              // . '(' . $rate_product . '+' . $rate_accessessories . '+' . $rate_shipping . ')'
              // . ' * '
              // . $row['quantity'] . ' + ' . $rate_handling
              // . '</td>'

              // . '</tr><tr>'
      
              . '<td colspan="8"><div class="background-hr">&nbsp;</div></td>'
              . '</tr><tr>'
              . '<th><span class="pull-right">Quantity:</span></th><td>' . $row['quantity'] . '</td>'
              . '<th><span class="pull-right">Product Price:</span></th><td>&nbsp;$' . number_format( $row['rate'] / 100 , 2 , '.' , ',' ) . $discount_rate . '</td>'
              . '<th><span class="pull-right">Product:</span></th><td><span class="text-green">' . $row['product_name'] . '</span>&nbsp;' . $row['manufacturer'] . ' ' . $row['version'] . '</td>'
              . '<th><span class="pull-right">Notes:</span></th><td class="text-red valign-top" rowspan="3"><textarea class="background-green width-100" rows="5" readonly>' . $row['notes'] . '</textarea></td>'
              . '</tr><tr>'
              . '<th><span class="pull-right text-grey">' . $resale_label . '</span></th><td class="text-grey">' . $reseller . '</td>'
              . '<th><span class="pull-right">Accessory Price:</span></th><td>&nbsp;$' . number_format( $row['accessories_cost'] / 100 , 2 , '.' , ',' ) . $discount_arate . '</td>'
              . '<th><span class="pull-right">Accessory:</span></th><td>' . $row['accessories_name'] . '</td>'
              . '</tr><tr>'
              . '<th><span class="pull-right text-grey">' . $resale_state_label . '</span></th><td class="text-grey">' . $resale_state . ' ' . $resale_rate . '</td>'
              . '<th><span class="pull-right">Handling:</span></th><td>&nbsp;$' . number_format( $row['handling_fee'] / 100 , 2 , '.' , ',' ) . $discount_handling . '</td>'
              . '<th><span class="pull-right">Plan:</span></th><td>' . $row['plan'] . '</td>'
              . '</tr><tr>'
              . '<th><span class="pull-right text-grey">' . $resale_amount_label . '</span></th><td class="text-grey">' . $resale_amount . '</td>'
              // . '<th><span class="pull-right">Shipping Fees:</span></th><td>$' . number_format( ( $row['shipping_fee'] * $row['quantity'] ) / 100 , 2 , '.' , ',' ) . $discount_shipping . '</td>'
              . '<th><span class="pull-right">Extended:</span></th><td>&nbsp;$' . number_format( $total_extended / 100 , 2 , '.' , ',' ) . $discount_order . '</td>'
              . '<th><span class="pull-right">Shipping Method:</span></th><td>' . $row['shipping'] . '</td>'
              . '<th><span class="pull-right">' . $label_shipping_track . '</span></th><td><a href="https://www.fedex.com/apps/fedextrack/?tracknumbers=' . $row['shipping_track'] . '&cntry_code=us" target="_fedex">' . $row['shipping_track'] . '</a></td>'
              . '</tr><tr>'
              . '<td colspan="2"></td>'
              . '<th><span class="pull-right">Taxes:</span></th><td>&nbsp;$' . number_format( $row['taxes_amount'] / 100 , 2 , '.' , ',' ) . '</td>'
              . '</tr><tr>'
              . '<td colspan="2"></td>'
              . '<th><span class="pull-right">Sub-Total:</span></th><td>&nbsp;$' . number_format( $sub_total / 100 , 2 , '.' , ',' ) . '</td>'
              . '</tr><tr>'
              . '<td colspan="2"></td>'
              . '<th><span class="pull-right">Shipping:</span></th><td>&nbsp;$' . number_format( $total_shipping / 100 , 2 , '.' , ',' ) . $discount_shipping . '</td>'
              . '</tr><tr>'
              . '<td colspan="2" rowspan="2"></td>'
              . '<th rowspan="2"><h4 class="pull-right">Grand Total:</h4></th><td rowspan="2"><h4>$' . number_format( $total_grand / 100 , 2 , '.' , ',' ) . '</h4></td>'
              . '<th><span class="pull-right text-red">' . $discount_reason_label . '</span></th><td class="text-red">' . $discount_reason . '</td>'
              . '</tr><tr>'
              . '<th><span class="pull-right">Payment Method:</span></th><td>' . $row['payment'] . '</td>'
              . '<th><span class="pull-right">' . $label_credit_card . '</span></th><td>' . $credit_card . '</td>'
              . '</tr><tr>'
              . '<td colspan="8"><div class="background-hr">&nbsp;</div></td>'
              . '</tr><tr>'
              . '<th><span class="pull-right">Updated:</span></th><td>' . $row['updated'] . '</td>'
              . '<th><span class="pull-right">' . $label_approved_by . '</span></th><td>' . $row['approved_by'] . '</td>'
              . '<th><span class="pull-right">' . $label_inventory . '</span></th><td rowspan="2"><textarea class="background-green width-100" rows="5" readonly>' . $row['inventory'] . '</textarea></td>'
              . '<td colspan="2" rowspan="2">' . $btn_label . '</td>'
              . '</tr><tr>'
              . '<th><span class="pull-right">Created:</span></th><td>' . $row['createdate'] . '</td>'
              . '<th><span class="pull-right">' . $label_approvedate . '</span></th><td>' . $row['approvedate'] . '</td>'
              . '</tr></table>'
              . '</div>' ;
      return $out;
    }

}
