<?php

namespace Controllers\Ajax;

use Models\Logic\AddressLogic;

use Models\Data\VehicleData;
use Models\Logic\VehicleLogic;

use Models\Data\TerritoryData;
use Models\Logic\TerritoryLogic;

//use Zend\Permissions\Acl\Role\RoleInterface;
use GTC\Component\Utils\Measurement;
use GTC\Component\Utils\CSV\CSVBuilder;
use GTC\Component\Map\Tiger;

use GTC\Component\Utils\PDF\TCPDFBuilder;

/**
 * Class Landmark
 *
 */
class Landmark extends BaseAjax
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->vehicle_data = new VehicleData;
        $this->vehicle_logic = new VehicleLogic;
        $this->address_logic = new AddressLogic;

        $this->territory_data = new TerritoryData;
        $this->territory_logic = new TerritoryLogic;

    }

    /**
     *	Get landmarks by landmark group ids (called via ajax)
     *
     *	POST params: landmark_groups (array of landmark ids)
     *
     *	@return void
     */      
    public function getLandmarksByGroupIds()
    {
        $ajax_data  = array();
        $user_id    = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();
        
        $landmark_groups = array();

        $landmarks = $this->territory_logic->getTerritoryByGroupIds($account_id, $landmark_groups);
        if ($landmarks !== false) {
            $ajax_data['code'] = 0;
            $ajax_data['data']['landmarks'] = $landmarks;
            $ajax_data['message'] = 'Success';
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Error';
        }

        $this->ajax_respond($ajax_data);
    }
    
    /**
     * Get the landmark info by filtered paramaters (called via ajax)
     *
     * POST params: filter_type
     * POST params: territorygroup_id
     * POST params: territorytype
     * POST params: search_string
     *
     * @return array
     */
    public function getFilteredLandmarks()
    {
        $ajax_data  = array();
        $user_id    = $this->user_session->getUserId();
        $post       = $this->request->request->all();

        $this->territory_logic->setTerritoryType('landmark');

        $search_type = (isset($post['filter_type']) AND !empty($post['filter_type'])) ? $post['filter_type'] : 'group_filter';
        $territorygroup_id = (isset($post['territorygroup_id']) AND !empty($post['territorygroup_id'])) ? $post['territorygroup_id'] : '';
        $territorytype = (isset($post['territorytype']) AND !empty($post['territorytype'])) ? $post['territorytype'] : '';
        $territorycategory_id = (isset($post['territorycategory_id']) AND !empty($post['territorycategory_id'])) ? $post['territorycategory_id'] : '';
        $territory_id = (isset($post['territory_id']) AND !empty($post['territory_id'])) ? $post['territory_id'] : 0;

        $params = $post;
        $params['default_value'] = '-';
        
        if ($search_type != '') {
            $landmark = $this->territory_logic->getFilteredTerritory($user_id, $params);
            if ($landmark !== false) {
                if(isset($landmark['landmarks']) AND ! empty($landmark['landmarks'])) {
                    $ajax_data['code'] = 0;
                    $ajax_data['data'] = $landmark;
                    $ajax_data['message'] = 'Success';
                } else {
                    $ajax_data['code'] = 0;
                    $ajax_data['data'] = $landmark;
                    $ajax_data['message'] = 'No Match Found';
                }
            } else {
                $errors = $this->territory_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',', $errors);
                } else {
                    $errors = 'Failed due to database error';
                }
                
                $ajax_data['code'] = 0;
                $ajax_data['data']['landmarks'] = array();
                $ajax_data['message'] = $errors; 
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Error';
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Get the landmark info by filtered paramaters (called via datatable ajax)
     *
     * POST params: filter_type
     * POST params: landmarkgroup_id
     * POST params: landmark_type
     * POST params: search_string
     * POST params: datatable params
     *
     * @return array
     */
    public function getFilteredLandmarksList()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
        $user_id    = $this->user_session->getUserId();

        $sEcho = 0;
        if (isset($post['sEcho']) AND $post['sEcho'] != '') {
            $sEcho = $post['sEcho'];
        }

        $output = array(
            "sEcho" => intval($sEcho),
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "data" => array()
        );

        $search_type                = (isset($post['filter_type']) AND !empty($post['filter_type'])) ? $post['filter_type'] : 'group_filter';
        $params                     = $post;
        $params['default_value']    = '-';
        
        if ($search_type != '') {
            $landmarks = $this->territory_logic->getFilteredTerritoryList($user_id, $params);
            if ($landmarks !== false) {
                
                $output['iTotalRecords']        = (isset($landmarks['iTotalRecords']) AND ! empty($landmarks['iTotalRecords'])) ? $landmarks['iTotalRecords'] : 0;
                $output['iTotalDisplayRecords'] = (isset($landmarks['iTotalDisplayRecords']) AND ! empty($landmarks['iTotalDisplayRecords'])) ? $landmarks['iTotalDisplayRecords'] : 0;
                $output['data']                 = (isset($landmarks['data']) AND ! empty($landmarks['data'])) ? $landmarks['data'] : array();
            }
        }

        echo json_encode( $output );
        exit;
    }

    /**
     * Get the incomplete landmarks datatable (called via datatable ajax)
     *
     * @return array
     */
    public function getIncompleteLandmarksList()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
        $user_id    = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();

        $sEcho = 0;
        if (isset($post['sEcho']) AND $post['sEcho'] != '') {
            $sEcho = $post['sEcho'];
        }

        $output = array(
            "sEcho" => intval($sEcho),
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "data" => array()
        );

        $search_type                = (isset($post['filter_type']) AND !empty($post['filter_type'])) ? $post['filter_type'] : 'group_filter';
        $params                     = $post;
        $params['default_value']    = '-';
        
        if ($search_type != '') {
            $landmarks = $this->territory_logic->getIncompleteTerritoryList($account_id, $params);
            if ($landmarks !== false) {
                
                $output['iTotalRecords']        = (isset($landmarks['iTotalRecords']) AND ! empty($landmarks['iTotalRecords'])) ? $landmarks['iTotalRecords'] : 0;
                $output['iTotalDisplayRecords'] = (isset($landmarks['iTotalDisplayRecords']) AND ! empty($landmarks['iTotalDisplayRecords'])) ? $landmarks['iTotalDisplayRecords'] : 0;
                $output['data']                 = (isset($landmarks['data']) AND ! empty($landmarks['data'])) ? $landmarks['data'] : array();
            }
        }

        echo json_encode( $output );
        exit;
    }

    /**
     * Get the incomplete landmarks datatable (called via datatable ajax)
     *
     * @return array
     */
    public function getFilteredVerificationList()
    {
        $ajax_data      = array();
        $post           = $this->request->request->all();
        $user_id        = $this->user_session->getUserId();
        $account_id     = $this->user_session->getAccountId();
        $user_timezone  = $this->user_session->getUserTimeZone();

        $sEcho = 0;
        if (isset($post['sEcho']) AND $post['sEcho'] != '') {
            $sEcho = $post['sEcho'];
        }

        $output = array(
            "sEcho" => intval($sEcho),
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "data" => array()
        );

        $this->territory_logic->setTerritoryType('reference');

        $search_type                = (isset($post['filter_type']) AND !empty($post['filter_type'])) ? $post['filter_type'] : 'group_filter';
        $params                     = $post;
        $params['user_timezone']    = $user_timezone;
        $params['default_value']    = '-';
        
        if ($search_type != '') {
            $vaddress = $this->territory_logic->getVerificationTerritoryList($user_id, $params);
            if ($vaddress !== false) {
                
                $output['iTotalRecords']        = (isset($vaddress['iTotalRecords']) AND ! empty($vaddress['iTotalRecords'])) ? $vaddress['iTotalRecords'] : 0;
                $output['iTotalDisplayRecords'] = (isset($vaddress['iTotalDisplayRecords']) AND ! empty($vaddress['iTotalDisplayRecords'])) ? $vaddress['iTotalDisplayRecords'] : 0;
                $output['data']                 = (isset($vaddress['data']) AND ! empty($vaddress['data'])) ? $vaddress['data'] : array();
            }
        }

        echo json_encode( $output );
        exit;
    }

    /**
     * Get verification address by provided territory_id
     *
     * @param int territory_id
     *
     * @return bool|array
     */    
    public function getVerifacationAddressByIds()
    {
        $ajax_data      = array();
        $user_id        = $this->user_session->getUserId();
        $post           = $this->request->request->all();
        $territory_id   = (isset($post['territory_id']) AND ! empty($post['territory_id'])) ? $post['territory_id'] : '';

        $territory = $this->territory_logic->getTerritoryByIds($territory_id);

        if ($territory !== false) {
            $ajax_data['code'] = 0;

            if (count($territory) == 1) {
                $territory = array_pop($territory);
            }

            $ajax_data['data'] = $territory;
            $ajax_data['message'] = 'Success';
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Error';
        }

        $this->ajax_respond($ajax_data);
    }


    /**
     * Get the landmark group datatable (called via datatable ajax)
     *
     * @return array
     */
    public function getLandmarkGroupList()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
        $user_id    = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();

        $sEcho = 0;
        if (isset($post['sEcho']) AND $post['sEcho'] != '') {
            $sEcho = $post['sEcho'];
        }

        $output = array(
            "sEcho" => intval($sEcho),
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "data" => array()
        );

        $this->territory_logic->setTerritoryType('landmark');

        $search_type                = (isset($post['filter_type']) AND !empty($post['filter_type'])) ? $post['filter_type'] : 'group_filter';
        $params                     = $post;
        $params['default_value']    = '-';
        
        if ($search_type != '') {
            $vaddress = $this->territory_logic->getTerritoryGroupList($user_id, $params);
            if ($vaddress !== false) {
                
                $output['iTotalRecords']        = (isset($vaddress['iTotalRecords']) AND ! empty($vaddress['iTotalRecords'])) ? $vaddress['iTotalRecords'] : 0;
                $output['iTotalDisplayRecords'] = (isset($vaddress['iTotalDisplayRecords']) AND ! empty($vaddress['iTotalDisplayRecords'])) ? $vaddress['iTotalDisplayRecords'] : 0;
                $output['data']                 = (isset($vaddress['data']) AND ! empty($vaddress['data'])) ? $vaddress['data'] : array();
            }
        }

        $this->territory_logic->resetTerritoryType();

        echo json_encode( $output );
        exit;
    }
    
    /**
     *	Get a landmark's info by its landmark id (called via ajax) 
     *
     *	POST params: lid
     *
     *	@return void
     */    
    public function getLandmarkByIds()
    {
        $ajax_data      = array();
        $user_id        = $this->user_session->getUserId();
        $post           = $this->request->request->all();
        $territory_id   = (isset($post['territory_id']) AND ! empty($post['territory_id'])) ? $post['territory_id'] : '';

        $territory = $this->territory_logic->getTerritoryByIds($territory_id);
        if ($territory !== false) {
            $ajax_data['code'] = 0;

            if (count($territory) == 1) {
                $territory = array_pop($territory);
            }

            $ajax_data['data'] = $territory;
            $ajax_data['message'] = 'Success';
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Error';
        }

        $this->ajax_respond($ajax_data);	    
    }
    
    /**
     *	Get an unfound landmark's info by landmarkupload id (called via ajax) 
     *
     *	POST params: landmarkupload_id
     *
     *	@return void
     */    
    public function getIncompleteLandmarkByIds()
    {
        $ajax_data      = array();
        $user_id        = $this->user_session->getUserId();
        $post           = $this->request->request->all();
        $landmark_id    = (isset($post['territoryupload_id']) AND ! empty($post['territoryupload_id'])) ? $post['territoryupload_id'] : '';

        $landmarks = $this->territory_logic->getTerritoryUploadByIds($landmark_id);
        if ($landmarks !== false) {
            $ajax_data['code'] = 0;

            if (count($landmarks) == 1) {
                $landmarks = array_pop($landmarks);

                // validate the incomplete landmark info
                $error = $this->territory_logic->validateIncompleteTerritoryInfo('all', $landmarks);
                if ($error !== true) {
                    $ajax_data['validation_error'] = $error;
                }
            }

            $ajax_data['data'] = $landmarks;
            $ajax_data['message'] = 'Success';
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Error';
        }

        $this->ajax_respond($ajax_data);	    
    }
 
    /**
     *	Save a landmark information (called via ajax) 
     *
     *	POST params: landmark_group, landmark_title, landmark_address, landmark_lat, landmark_long, landmark_radius, unfound
     *
     *	@return void
     */    
    public function saveLandmark()
    {
        $ajax_data  = array();
        $account_id = $this->user_session->getAccountId();
        $post       = $this->request->request->all();
        
        $type = $shape = $title = $street_address = $city = $state = $zip = $country = $error = '';
        $group = $radius = $latitude = $longitude = $category_id = 0;
        
        if (isset($post['type'])) {
            $category_id = $post['type'];        
        } else {
            $error = 'Invalid landmark category';
        }
        
        if (! empty($post['shape'])) {
            $shape = $post['shape'];        
        } else {
            $error = 'Invalid landmark shape';
        }            
        
        if (! empty($post['title'])) {
            $title = $post['title'];        
        } else {
            $error = 'Invalid landmark title';
        }
        
        if (! empty($post['street_address'])) {
            $street_address = $post['street_address'];
        }
        
        if (! empty($post['city'])) {
            $city = $post['city'];
        }
        
        if (! empty($post['state'])) {
            $state = $post['state'];
        }
        
        if (! empty($post['zip'])) {
            $zip = $post['zip'];
        }
        
        if (! empty($post['country'])) {
            $country = $post['country'];
        }
        
        if (! empty($post['group'])) {
            $group = $post['group'];        
        } else {
            $group = 1; // if there is was no landmark group selected, use the default group (1 for now)
        }           

        if (! empty($post['radius'])) {
            $radius = $post['radius'];        
        }
        
        if (! empty($post['landmarktype'])) {
            $landmarktype = $post['landmarktype'];        
        } else {
            $landmarktype = 9;        
        }
        
        if (! empty($post['landmarktypetext'])) {
            $landmarktypetext = $post['landmarktypetext'];        
        } else {
            $landmarktypetext = 'n/a';        
        }
        
        if (! empty($post['latitude']) AND ! empty($post['longitude'])) {
            $latitude = $post['latitude'];
            $longitude = $post['longitude'];
        } else {
            $error = 'Invalid coordinates';
        }

        if (! empty($post['coordinates'])) {
            $coordinates = array();
            $buffer = explode(', ',$post['coordinates']);
            foreach($buffer as $key => $val){
                $buf = explode(' ',$val);
                if(($buf[0])&&($buf[1])){
                    $coordinates[] = array('latitude'=>$buf[0],'longitude'=>$buf[1]);
                }
            }
        } else if ($shape!='circle'){
            $error = 'Missing coordinates';
        }

        if ($error == '') {
            $territory_id = $this->territory_logic->saveTerritory($account_id, $latitude, $longitude, $title, $radius, $street_address, $city, $state, $zip, $country, $shape, 'landmark', $group, $coordinates, $category_id, false , $landmarktype , $landmarktypetext);
            if ($territory_id !== false) {
                $ajax_data['code'] = 0;
                $ajax_data['data']['territory_id'] = $territory_id;
                $ajax_data['message'] = 'Successfully saved landmark';   
                $ajax_data['action'] = 'landmark-add';     
            } else {
                $errors = $this->territory_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',', $errors);    
                }
                $ajax_data['code'] = 1;
                $ajax_data['validation_error'][] = $ajax_data['message'] = $errors;
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = $error;
        }

        $this->ajax_respond($ajax_data);
    }	

    /**
     *	Remove a landmark information (called via ajax) 
     *
     *	POST params: landmark_id
     *
     *	@return void
     */    
    public function deleteLandmark()
    {
        $ajax_data      = array();
        $account_id     = $this->user_session->getAccountId();
        $post           = $this->request->request->all();
        $landmark_id    = (isset($post['territory_id']) AND ! empty($post['territory_id'])) ? $post['territory_id'] : '';
        $reference      = (! empty($post['reference']) AND ($post['reference'] == 1)) ? true : false;
        
        $landmarks = $this->territory_logic->deleteTerritory($landmark_id, $account_id, $reference);
        if ($landmarks !== false) {
            $ajax_data['code'] = 0;
            $ajax_data['data'] = $landmarks;
            $ajax_data['message'] = 'Success';
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Error';
        }

        $this->ajax_respond($ajax_data);
 
    }	

    /**
     *	Remove an unfound landmark information (called via ajax) 
     *
     *	POST params: landmark_id (is actually a landmarkupload id)
     *
     *	@return void
     */    
    public function deleteLandmarkUpload()
    {
        $ajax_data          = array();
        $user_id            = $this->user_session->getUserId();
        $post               = $this->request->request->all();
        $landmarkupload_id  = (isset($post['territory_id']) AND ! empty($post['territory_id'])) ? $post['territory_id'] : '';

        $landmarkupload = $this->territory_logic->deleteTerritoryUpload($landmarkupload_id, $user_id);
        if ($landmarkupload !== false) {
            $ajax_data['code'] = 0;
            $ajax_data['data'] = $landmarkupload;
            $ajax_data['message'] = 'Success';
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Error';
        }

        $this->ajax_respond($ajax_data);
 
    }
    
    /**
     *	Get a landmarkgroup info by its landmarkgroup id (called via ajax) 
     *
     *	POST params: group_id
     *
     *	@return void
     */    
    public function getLandmarkGroupInfo()
    {
        $ajax_data  = array();
        $user_id    = $this->user_session->getUserId();
        $post       = $this->request->request->all();
        
        if (! empty($post['group_id'])) {
            $this->territory_logic->setTerritoryType(array('landmark'));
            $territorygroup = $this->territory_logic->getTerritoryGroupsById($user_id, $post['group_id'], true); // the third paramter is a bool indicating if we should include the territories
            $this->territory_logic->resetTerritoryType();
            if ($territorygroup !== false) {
                // get the current territory groups that user have access to for building dropdowns
                $temp_groups = $this->territory_logic->getTerritoryGroupsByUserId($user_id);
                if (! empty($temp_groups)) {
                    foreach($temp_groups as $index => $group) {
                        // remove the selected vehicle groups from the dropdown 
                        if ($group['territorygroup_id'] == $post['group_id']) {
                            unset($temp_groups[$index]);
                            break;
                        }
                    }
                    // get the array values (in order to get numeric index array)
                    $territory_groups = array_values($temp_groups);    
                }
                
                $ajax_data['code'] = 0;
                $ajax_data['data']['territory_groups'] = $territory_groups;
                $ajax_data['data']['landmarkgroup_data'] = array_pop($territorygroup['groups']);
                $ajax_data['data']['defaultgroup_id'] = $territorygroup['defaultgroup_id'];
                $ajax_data['message'] = 'Successfully retrieved territory group info';    
            } else {
                $errors = $this->territory_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',', $errors);    
                }
                $ajax_data['code'] = 1;
                $ajax_data['message'] = $errors;                    
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid territory group id';                
        }
        
        $this->ajax_respond($ajax_data);	    
    }

    public function fetchLandmarkCSVImportTemplate()
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=import_landmark_template.csv');
        exit('"Name","Street","City","State","Zipcode","Country","Latitude","Longitude","Radius (miles)","Landmark Group Name"');//,"Landmark Type"');

    }

    /**
     * Upload landmarks
     *
     * @return void
     */    
    public function uploadLandmarks()
    {
        // simulate AJAX params
        $view_data = $this->ajax_data;
        $response['code'] = 1; // set response code to 'fail' by default
        $message = 'ERROR'; // set response message as a fail message by default
        $files = $this->request->files;
        
		if (! empty($files)) {
		    $file_path = '';
		    foreach ($files as $f) {
                $file_path = $f->getPathname();
                break;  // only need to get one file path per upload
		    }  
		    
		    $user_id    = $this->user_session->getUserId();
		    $account_id = $this->user_session->getAccountId();

    		if (! empty($account_id)) {
        		if (file_exists($file_path)) {
                    $result = $this->territory_logic->uploadTerritory($account_id, $user_id, '', $file_path, 'landmark');
                    $attempts_landmark_counter = $result['attempts'] ;
                    $complete_landmark_counter = $result['complete'] ;
                    $incomplete_landmark_counter = $result['incomplete'] ;
                    if($result['headererror']){
                        $upload_message = '<span class="text-warning">ERROR<br>&nbsp;<br>One or more column headers is not as expected.<br>&nbsp;<br>Please use template.<br>&nbsp;<br></span>';
                    } else if ($incomplete_landmark_counter !== false) {
                		$response['code'] = 0; // upload success
                		$message = 'Landmarks Uploaded';
                		
                        $pluralComplete = ' has';
                        if($complete_landmark_counter>1){
                            $pluralComplete = 'es have';
                        }
                		
                        $pluralIncomplete = ' was';
                        if($incomplete_landmark_counter>1){
                            $pluralIncomplete = 'es were';
                        }
                        
                        if ($complete_landmark_counter > 0) {
                            // $upload_message .= '<p><span class="text-success text-18"><b>SUCCESS!</b></span><br>' . $complete_landmark_counter . ' address' . $pluralComplete . ' been uploaded and processing will begin shortly. Once processed, partially processed entries may be found and updated using the <a href="/landmark/incomplete" class="text-18">Pending Landmarks</a> report.</p>';    
                            $upload_message .= '<p><span class="text-success text-18"><b>SUCCESS</b></span><br>' . $complete_landmark_counter . ' address' . $pluralComplete . ' been added to the <a href="/landmark/list" style="font-size:120%;font-weight:700;">Landmark List</a> report.</p>';
                        }
                        if ($incomplete_landmark_counter > 0) {
                           	$upload_message .= '<p><span class="text-warning text-18"><b>PENDING ITEMS</b></span><br>' . $incomplete_landmark_counter . ' address' . $pluralIncomplete . ' not processed due to missing, incomplete, and/or invalid components.</p>';
                            $upload_message .= '<p>Please use the <a href="/landmark/incomplete" style="font-size:120%;font-weight:700;">Pending Landmarks</a> report to make any necessary corrections. <span class="text-grey">As a courtesy, please be advised an automated second attempt to process pending landmarks has been scheduled to run within the next ten (10) minutes. However, it is likely critical data is missing which will need to be supplied by you, our valued user. Thank you for your understanding and participation.</span></p>';
                		}
            		} else {
                		$upload_message .= '<span class="text-warning">ERROR: An invalid template was used</span>';
            		}
                    unlink($file_path);                		         	
        		} else {
            		$upload_message .= '<span class="text-warning">ERROR: Cannot find uploaded file or file is empty</span>';	
        		}
    		} else {
        		$upload_message .= '<span class="text-warning">ERROR: This account id is not valid</span>';
    		}
		} else {
			$upload_message .= '<span class="text-warning">ERROR: Failed to upload the csv file</span>';
		}
        $upload_message .= '<p><span class="text-grey text-10">To start over, please select a new file...</span></p>';

// $upload_message .= ' (' . $incomplete_landmark_counter . ')' ; 
// $upload_message .= ' ( errors:"' . $result['params']['error'] . '" attempts:"' . $attempts_landmark_counter . '"  yyy:"' . $result['params']['yyy'] . '"  xxx:"' . $result['params']['xxx'] . '" lat:"' . $result['params']['latitude'] . '" long:"' . $result['params']['longitude'] . '" street:"' . $result['params']['streetaddress'] . '"  city:"' . $result['params']['city'] . '"  state:"' . $result['params']['state'] . '"  zip:"' . $result['params']['zipcode'] . '"  country:"' . $result['params']['country'] . '"  hello:"' . $result['params']['hello'] . '" )' ; 
// $upload_message .= ' ( ' . $result['params']['error'] . ' )' ; 

		$response['message'] = $message;
		$response['upload_message'] = $upload_message;
        $view_data['data']['response'] = json_encode($response);

        $this->ajax_render('partial/iframe-upload-response.html.twig', $view_data);
    }

    /**
     * Export Filter Landmark List by search_string/group for an account
     *
     * GET params: string $format Specify type of export: pdf or csv
     * GET params: string $filterType (string_search or group_filter)
     * GET params: $filterValue1 (search_string or a group_id depending on type)
     * GET params: $filterValue2 (all or state status filter)
     *
     * @return array
     */    
    public function exportFilteredLandmarkList($format, $filterType, $filterValue1, $filterValue2)
    {
        $landmark_groups            = array();
        $user_id                    = $this->user_session->getUserId();
        $results                    = array();

        $params['sEcho']            = 0;
        $params['bSearchable_0']    = true;
        $params['bSearchable_1']    = true;
        $params['bSearchable_2']    = true;
        $params['bSearchable_3']    = true;
        $params['bSearchable_4']    = true;
        $params['bSortable_0']      = true;
        $params['bSortable_1']      = true;
        $params['bSortable_2']      = true;
        $params['bSortable_3']      = true;
        $params['bSortable_4']      = true;
        $params['iSortCol_0']       = 0;
        $params['iSortingCols']     = 1;
        $params['iColumns']         = 5;
        $params['mDataProp_0']      = 'territoryname';
        $params['mDataProp_1']      = 'territorygroupname';
        $params['mDataProp_2']      = 'territorytype';
        $params['mDataProp_3']      = 'radius';
        $params['mDataProp_4']      = 'formatted_address';
        $params['sSortDir_0']       = 'asc';

        $params['filter_type']      = $filterType;
        $params['default_value']    = '';

        // if filterType is a string search, set params according
        if ($filterType == 'string_search') {
            $params['search_string']    = $filterValue1;
            $params['territorygroup_id'] = 'ALL';
            $params['territorytype']    = 'ALL';
        } else {
            // if filterType is group_filter, set params according for the group
            if ($filterValue1 != '' AND $filterValue1 != 'ALL' AND $filterValue1 != 'All') {
                $landmark_groups = $filterValue1;
            }
            $params['territorygroup_id'] = $filterValue1;
            $params['territorytype']    = $filterValue2;
        }
        
        $landmarks = $this->territory_logic->getFilteredTerritoryList($user_id, $params);
        if ($landmarks !== false) {
            $results = (isset($landmarks['data']) AND ! empty($landmarks['data'])) ? $landmarks['data'] : array();
        }

        $filename   = str_replace(' ', '_', $this->user_session->getAccountName().'_FilterLandmarkListExport_'.$filterType.'-'.$filterValue1.'-'.$filterValue2);
        $fields     = array('territoryname' => 'Landmark','territorygroupname' => 'Group', 'territorytype' => 'Type', 'radius_in_miles' => 'Radius', 'formatted_address' => 'Location');

        if($format == 'pdf') {
            $pdf_builder = new TCPDFBuilder('L');
            $pdf_builder->createTitle('Landmarks');
            $pdf_builder->createTable($results, $fields);
            $pdf_builder->Output($filename, 'D');
        } else {
            $csv_builder = new CSVBuilder();
            $csv_builder->setSeparator(',');
            $csv_builder->setClosure('"');
            $csv_builder->setFields($fields);
            $csv_builder->format($results)->export($filename);
        }
        
        exit();
    }

    /**
     * Export Landmark by landmark_id
     *
     * GET params: string format, int landmark_id
     *
     * @return array
     */    
    public function exportLandmark($format, $landmark_id)
    {
        $results    = array();
        $user_id    = $this->user_session->getUserId();

        $landmarks = $this->territory_logic->getTerritoryByIds($landmark_id);
        if ($landmarks !== false) {
            $results = $landmarks;
        }

        $filename   = str_replace(' ', '_', $this->user_session->getAccountName().'_ExportLandmark-'.$results[0]['territoryname']);
        $fields     = array('territoryname' => 'Landmark','territorygroupname' => 'Group', 'territorytype' => 'Type', 'radius_in_miles' => 'Radius', 'formatted_address' => 'Location');

        if($format == 'pdf') {
            $pdf_builder = new TCPDFBuilder('L');
            $pdf_builder->createTitle("Landmark's Detail");
            $pdf_builder->createTable($results, $fields);
            $pdf_builder->Output($filename, 'D');
        } else {
            $csv_builder = new CSVBuilder();
            $csv_builder->setSeparator(',');
            $csv_builder->setClosure('"');
            $csv_builder->setFields($fields);
            $csv_builder->format($results)->export($filename);
        }
        
        exit();
    }

    /**
     * Update a landmark's info by post params (called via datatable ajax)
     *
     * POST params: id
     * POST params: value
     *
     * @return array
     */
    public function updateLandmarkInfo() 
    {
        $ajax_data  = array();
        $user_id    = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();
        $post       = $this->request->request->all();

    	$landmark_id = (isset($post['primary_keys']['landmarkPk'])) ? $post['primary_keys']['landmarkPk'] : 0;
    	$page = (isset($post['primary_keys']['landmarkPage'])) ? $post['primary_keys']['landmarkPage'] : '';
    	$radius = (isset($post['primary_keys']['landmarkRadius'])) ? $post['primary_keys']['landmarkRadius'] : '660';
        $params = array();
        $table = '';

        if (isset($post['id'])) {
         switch ($post['id']) {
            case 'landmark-name':
                $params['territoryname'] = $post['value'];
                $table = 'territory';
                break;
            case 'landmark-group':
                $params['territorygroup_id'] = $post['value'];
                $table = 'territorygroup_territory';
                break;
            case 'landmark-unit':
                $params['unit_id'] = $post['value'];
                $table = 'unit_territory';
                break;
            case 'landmark-type':
                //$params['reference'] = $post['value'];
                $params['territorytype'] = $post['value'];
                $table = 'territory';
                break;
            case 'landmark-shape':
                $params['shape'] = $post['value'];
                $params['radius'] = $radius;
                if (isset($page) AND $page != 'incomplete') {
                    $params['update_shape'] = 1;
                }

                $table = 'territory';
                break;
            case 'landmark-radius':
                $params['radius'] = $post['value'];
                $table = 'territory';
                break;
            case 'landmark-address':
                $params['address'] = $post['value'];
                
                //$params['streetaddress'] = '';
                //$params['city'] = '';
                //$params['state'] = '';
                //$params['zipcode'] = '';
                //$params['country'] = '';
                
                $table = 'territory';
                break;
            case 'landmark-map-click':
                $params['lat_long'] = $post['value'];
                
                //$params['latitude'] = '';
                //$params['longitude'] = '';
                
                $table = 'territory';
                break;
            case 'landmark-category':
                $params['territorycategory_id'] = ($post['value'] == 0 ? null : $post['value']);
                $table = 'territory'; 
            default:
                break;
            }
        }
        
        // updating shape/location
        if (! empty($post['shape'])) {
            $params['shape'] = $post['shape'];
            $table = 'territory';
        }
        
        if (! empty($post['latitude']) AND ! empty($post['longitude'])) {
            $params['latitude'] = $post['latitude'];
            $params['longitude'] = $post['longitude'];
            $table = 'territory';
        }
        
        if (! empty($post['coordinates']) AND $page == '') {
            $params['coordinates'] = $post['coordinates'];
            $table = 'territory';
        }
        
        if (isset($post['street_address'])) {
            $params['streetaddress'] = $post['street_address'];
            $table = 'territory';
        }
        
        if (isset($post['city'])) {
            $params['city'] = $post['city'];
            $table = 'territory';
        }

        if (isset($post['state'])) {
            $params['state'] = $post['state'];
            $table = 'territory';
        }

        if (isset($post['zip'])) {
            $params['zipcode'] = (! empty($post['zip'])) ? $post['zip'] : '';
            $table = 'territory';
        }
        
        if (isset($post['country'])) {
            $params['country'] = (! empty($post['country'])) ? $post['country'] : '';
            $table = 'territory';
        }
        
        if (isset($post['process'])) {
            $params['process'] = $post['process'];
        }

        if (! empty($params) AND ! empty($landmark_id)) {
            // if from incomplete detail page, update landmarkupload table
            if (isset($page) AND $page == 'incomplete') {

                // update first
                $update_landmark = $this->territory_logic->updateIncompleteTerritory($landmark_id, $params, 'territoryupload');
                
                if ($update_landmark !== false) {
                    // get this incomplete landmark info $landmark_id
                    $landmarkupload = $this->territory_data->getTerritoryUploadByIds(array($landmark_id));
    
                    if ( isset($landmarkupload[0]) AND ! empty($landmarkupload[0])) {
                        $row = $landmarkupload[0];
                        // calculate bounding box
                        $boundingbox = $this->territory_logic->getBoundingBoxValue('circle', array($row['latitude'] . ' ' . $row['longitude']), $row['radius']);
                        
                        // create landmark params 
                        $landmark_created = false;
                        $params = array(
                            'account_id'            => $account_id,
                            'territorycategory_id'  => $row['territorycategory_id'],
                            'unit_id'               => (isset($row['unit_id']) ? trim($row['unit_id']) : 0),
                            'territoryname'         => (isset($row['territoryname']) ? trim($row['territoryname']) : ''),
                            'streetaddress'         => (isset($row['streetaddress']) ? trim($row['streetaddress']) : ''),
                            'city'                  => (isset($row['city']) ? trim($row['city']) : ''),
                            'state'                 => (isset($row['state']) ? trim($row['state']) : ''),
                            'zipcode'               => (isset($row['zipcode']) ? trim($row['zipcode']) : 0),
                            'country'               => (isset($row['country']) ? trim($row['country']) : ''),
                            'latitude'              => ((is_numeric($row['latitude']) AND ! empty($row['latitude'])) ? trim($row['latitude']) : 0),
                            'longitude'             => ((is_numeric($row['longitude']) AND ! empty($row['longitude'])) ? trim($row['longitude']) : 0),
                            'radius'                => ((is_numeric($row['radius']) AND ! empty($row['radius'])) ? trim($row['radius']) : 0),
                            'territorygroupname'    => (isset($row['territorygroupname'])) ? trim($row['territorygroupname']) : '',
                            'territorytype'         => (isset($row['territorytype'])) ? trim($row['territorytype']) : 'landmark',
                            'shape'                 => 'circle',
                            'boundingbox'           => ((isset($boundingbox) AND $boundingbox != '') ? trim($boundingbox) : ''),
                            'active'                => 1,
                            'process'               => $row['process']
                        );
    
                        // process validation for inline field
                        $validated = $this->territory_logic->validateIncompleteTerritoryInfo($post['id'], $params);
                        if ($validated === true) {
                            
                            // if no error then validation passes so process transfer
                            $validated_transfer = $this->territory_logic->saveIncompleteToTerritory($account_id, $user_id, $landmark_id, $params);
                            if ($validated_transfer === true) {
    
            	                $ajax_data['data'] = $post;
            	                $ajax_data['code'] = 0;
            	                $ajax_data['message'] = 'Updated and Transfer Successful';
            	                
                            } else {
                                // errors while transfering
            	                $ajax_data['data'] = $post;
            	                $ajax_data['code'] = 0;
            	                $ajax_data['new_error'] = $validated_transfer;
            	                $ajax_data['message'] = 'Transfer Not Successful';
                            }
                        } else {
                            // has validation error for this field
        	                $ajax_data['data'] = $post;
        	                $ajax_data['code'] = 1;
        	                $ajax_data['validation_error'] = $validated;
        	                $ajax_data['new_error'] = $validated;
        	                $ajax_data['message'] = 'Transfer Not Successful';
                        }
                        
                        $row['formatted_address'] = $this->address_logic->validateAddress($row['streetaddress'], $row['city'], $row['state'], $row['zipcode'], $row['country']);
                        $row['radius_in_miles'] = Measurement::radiusFeetToFractionConverter($row['radius']);
                        $ajax_data['data']['landmark_data'] = $row;
                    } else {
                        //incomplete landmark not found
    	                $ajax_data['data'] = $post;
    	                $ajax_data['code'] = 0;
    	                $ajax_data['message'] = 'This Incomplete Landmark Not Found';
                    }
                } else {
                    $error = $this->territory_logic->getErrorMessage();
                    $ajax_data['validation_error'] = $error;
	                $ajax_data['data'] = $post;
	                $ajax_data['code'] = 1; 
	                $ajax_data['message'] = 'Failed to update the Incomplete Landmark';                   
                }
            } else {
                // Not incomplete landmark updates
	            if ($this->territory_logic->updateTerritoryInfo($landmark_id, $params, $table) !== false) {
	                $ajax_data['data'] = $post;

	                $landmark_info = $this->territory_logic->getTerritoryByIds($landmark_id);
	                $ajax_data['data']['landmark_data'] = array_pop($landmark_info);
	                $ajax_data['code'] = 0;
	                $ajax_data['message'] = 'Updated Landmark Information';
	            } else {
                    $errors = $this->territory_logic->getErrorMessage();
                    if (! empty($errors) AND is_array($errors)) {
                        $errors = implode(',', $errors);    
                    }
                    $ajax_data['code'] = 1;
                    $ajax_data['validation_error'][] = $ajax_data['message'] = $errors;
	            }
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid landmark ID or nothing to update';
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Get landmark groups for x-editable dropdown
     *
     * @return void
     *
     */
    public function getLandmarkGroupOptions()
    {
        $user_id = $this->user_session->getUserId();
        $output  = '[';
        
        // set territory type to 'landmark'
        $this->territory_logic->setTerritoryType('landmark');
        
        $landmark_groups = $this->territory_logic->getTerritoryGroupsByUserId($user_id);
        if ($landmark_groups !== false) {
            $last_index = count($landmark_groups) - 1;
            foreach ($landmark_groups as $index => $group) {
                $separator = ',';

                if ($index == $last_index) {
                    $separator = '';
                }

                $output .= '{"value": "' . $group['territorygroup_id'] . '", "text": "' . $group['territorygroupname'] . '"}' . $separator;
            }
        }

        // reset territory type
        $this->territory_logic->resetTerritoryType();

        $output .= ']';
        die($output);

    }

    /**
     * Get verification landmark units for x-editable dropdown
     *
     * @return void
     *
     */
    public function getLandmarkUnitOptions()
    {
        $account_id = $this->user_session->getUserId();
        $output     = '[';

        $units = $this->territory_logic->getUnitTerritoryByUserId($account_id);
        if ($units !== false) {
            $last_index = count($units) - 1;
            foreach ($units as $index => $unit) {
                $separator = ',';

                if ($index == $last_index) {
                    $separator = '';
                }

                $output .= '{"value": "' . $unit['unit_id'] . '", "text": "' . $unit['unitname'] . '"}' . $separator;
            }
        }

        $output .= ']';
        
        die($output);

    }

    public function getLandmarkTypeOptions($placeholder = null, $value = null)
    {
        $output = '[';

        if ($placeholder !== null) {  // used when setting up alert triggers
            $output .= '
                {
                    "value": "'.$value.'",
                    "text":  "'.$placeholder.'"
                },
            ';
        }

        $output .= '
            {
                "value": "landmark",
                "text": "Landmark"
            },
            {
                "value": "reference",
                "text":  "Reference"
            }
        ]
        ';

        die($output);
    }

    public function getLandmarkRadiusOptions()
    {
        $output = '
        [
            {
                "value": "'. (5280*0.0625) .'",
                "text":  "1/16 Mile"
            },
            {
                "value": "'. (5280*0.125) .'",
                "text":  "1/8 Mile"
            },
            {
                "value": "'. (5280*0.25) .'",
                "text": "1/4 Mile"
            },
            {
                "value": "'. (5280*0.5) .'",
                "text": "1/2 Mile"
            },
            {
                "value": "'. (5280) .'",
                "text": "1 Mile"
            },
            {
                "value": "'. (5280*3) .'",
                "text": "3 Miles"
            },
            {
                "value": "'. (5280*5) .'",
                "text": "5 Miles"
            }
        ]
        ';

        die($output);
    }

    public function getLandmarkMethodOptions()
    {
        $output = '
        [
            {
                "value": "landmark-manual",
                "text":  "Manual Entry"
            },
            {
                "value": "landmark-map-click",
                "text": "Map Click"
            }
        ]
        ';

        die($output);
    }

    public function getLandmarkTriggerOptions()
    {
        $output = '
        [
            {
                "value": "enter",
                "text":  "On Enter"
            },
            {
                "value": "exit",
                "text": "On Exit"
            },
            {
                "value": "enter-exit",
                "text": "On Enter or Exit"
            }
        ]
        ';

        die($output);
    }

    public function getLandmarkOptions($placeholder = null, $value = '')
    {
        $output = '[';

        if ($placeholder !== null) {  // used when setting up alert triggers
            $value = ($value === null) ? '' : $value;
            $output .= '
                {
                    "value": "'.$value.'",
                    "text":  "'.$placeholder.'"
                },
            ';
        }

        // set territory type to be 'landmark'
        $this->territory_logic->setTerritoryType(array('landmark'));
        
        $territory_groups   = $groups = array();
        $user_id            = $this->user_session->getUserId();
        $account_id         = $this->user_session->getAccountId();
        $territory_groups   = $this->territory_logic->getTerritoryGroupsByUserId($user_id);
        if (! empty($territory_groups)) {
            foreach ($territory_groups as $territory_group) {
                $groups[] = $territory_group['territorygroup_id'];
            }
        }

        $landmarks = $this->territory_logic->getTerritoryByGroupIds($user_id, $groups);
        if (! empty($landmarks)) {
            $last_index = count($landmarks) - 1;
            foreach ($landmarks as $index => $landmark) {
                $separator = ',';

                if ($index == $last_index) {
                    $separator = '';
                }

                $output .= '{"value": "' . $landmark['territory_id'] . '", "text": "' . $landmark['territoryname'] . '"}' . $separator;
            }
        }

        $output .= ']';
        
        // reset territory type array back to default (i.e. all territory types)
        $this->territory_logic->resetTerritoryType();
        
        die($output);
    }
    
    /**
     * Get landmark category for x-editable dropdown
     *
     * @return void
     *
     */
    public function getLandmarkCategoryOptions()
    {
        $user_id = $this->user_session->getUserId();
        $output  = '[';

        $landmark_categories = $this->territory_logic->getAllTerritoryCategories();

        if ($landmark_categories !== false) {
            $last_index = count($landmark_categories) - 1;
            $output .= '{"value": "0", "text": "None"},';
            foreach ($landmark_categories as $index => $category) {
                $separator = ',';

                if ($index == $last_index) {
                    $separator = '';
                }

                $output .= '{"value": "' . $category['territorycategory_id'] . '", "text": "' . $category['territorycategoryname'] . '"}' . $separator;
            }
        }

        $output .= ']';
        die($output);

    }

    /**
     * Get reference option for x-editable dropdown for report section
     *
     * @return void
     *
     */
    public function getReferenceOptions($placeholder = null, $value = null)
    {
        $output = '[';

        if ($placeholder !== null) {  // used when setting up alert triggers
            $output .= '
                {
                    "value": "'.$value.'",
                    "text":  "'.$placeholder.'"
                },
            ';
        }

        $output .= '
            {
                "value": "Full",
                "text": "Fully Verified"
            },
            {
                "value": "Partial",
                "text":  "Partial Verification"
            },
            {
                "value": "None",
                "text":  "No Verification"
            }
        ]
        ';

        die($output);
    }
    
    /**
     * Reverse Geocode using Tiger
     *
     * POST params: latitude, longitude
     *
     * @return void
     */
    public function reverseGeocode() 
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
        $latitude   = '';
        $longitude  = '';
        
        if (! empty($post['latitude'])) {
            $latitude = $post['latitude'];
        }
        
        if (! empty($post['longitude'])) {
            $longitude = $post['longitude'];
        }
        
        if ($latitude != '' AND is_numeric($latitude) AND $longitude != '' AND is_numeric($longitude)) {
            $geocoder = new Tiger();

            $result = $geocoder->reverseGeocode($latitude, $longitude);
            $ajax_data['code'] = 0;
            $ajax_data['data'] = $result;
            $ajax_data['message'] = 'Successfully retrieved data';

        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid coordinates';
        }
        
        $this->ajax_respond($ajax_data);        
    }

    /**
     * Add landmark group to user (called via ajax)
     *
     * POST params: landmarkgroup_id, user_id
     *
     * @return void
     */
    public function addLandmarkGroupToUser()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();

        if (! empty($post['user_id'])) {
            if (! empty($post['landmarkgroups']) AND is_array($post['landmarkgroups'])) {
                $user_id = $post['user_id'];
                $landmarkgroups = $post['landmarkgroups'];
                $ajax_data['message'] = '';
                foreach ($landmarkgroups as $landmarkgroup) {
                    if ($this->territory_logic->addTerritoryGroupToUser($landmarkgroup['id'], $user_id) !== false) {
                        if (! isset($ajax_data['code'])) {
                            $ajax_data['code'] = 0;
                            $ajax_data['message'] = 'Assigned landmark group to user';
                        }
                        $ajax_data['data']['added_groups'][] = $landmarkgroup;      
                    } else {
                        $ajax_data['code'] = 1;
                        $errors = $this->territory_logic->getErrorMessage();
                        if (! empty($errors) AND is_array($errors)) {
                            $errors = implode(',', $errors);
                        } else {
                            $errors = 'Failed due to database error';
                        }
                        $ajax_data['message'] .= $landmarkgroup['name'] . ' - ' . $errors . ' | ';
                        $ajax_data['data']['failed_groups'][] = $landmarkgroup;
                    }
                }
                
                // if one or more landmark groups can not be assign to this user, build a list of these vehicle groups and their associating error message
                if (($ajax_data['code'] === 1) AND ! empty($ajax_data['message']) AND ! empty($ajax_data['data']['failed_groups'])) {
                    $ajax_data['message'] = 'The following landmark group(s) were not able to be assign to this user: ' . $ajax_data['message'];
                }
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Invalid landmark group id';                    
            }    
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid user id';
        }
        
        $this->ajax_respond($ajax_data);
    }

    /**
     * Remove landmark group from user (called via ajax)
     *
     * POST params: landmarkgroup_id, user_id
     *
     * @return void
     */
    public function removeLandmarkGroupFromUser()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();

        if (! empty($post['user_id'])) {
            if (! empty($post['landmarkgroups']) AND is_array($post['landmarkgroups'])) {
                $user_id = $post['user_id'];
                $landmarkgroups = $post['landmarkgroups'];
                $ajax_data['message'] = '';
                foreach ($landmarkgroups as $landmarkgroup) {
                    if ($this->territory_logic->removeTerritoryGroupFromUser($landmarkgroup['id'], $user_id) !== false) {
                        if (! isset($ajax_data['code'])) {
                            $ajax_data['code'] = 0;
                            $ajax_data['message'] = 'Removed landmark group from user';
                        }      
                    } else {
                        $ajax_data['code'] = 1;
                        $errors = $this->territory_logic->getErrorMessage();
                        if (! empty($errors) AND is_array($errors)) {
                            $errors = implode(',', $errors);
                        } else {
                            $errors = 'Failed due to database error';
                        }
                        $ajax_data['message'] .= $landmarkgroup['name'] . ' - ' . $errors . ' | ';
                        $ajax_data['data']['failed_groups'][] = $landmarkgroup;
                    }
                }
                
                // if one or more landmark groups can not be remove from this user, build a list of these vehicle groups and their associating error message
                if (($ajax_data['code'] === 1) AND ! empty($ajax_data['message']) AND ! empty($ajax_data['data']['failed_groups'])) {
                    $ajax_data['message'] = 'The following landmark group(s) were not able to be remove from this user: ' . $ajax_data['message'];
                }
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Invalid landmark group id';                    
            }    
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid user id';
        }
               
        $this->ajax_respond($ajax_data);
    }

    /**
     * Add vehicles to vehicle groups (called via ajax)
     *
     * POST params: landmarks, landmarkgroup_id
     *
     * @return void
     */
    public function addLandmarksToGroup()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();

        if (! empty($post['landmarkgroup_id'])) {
            if (! empty($post['landmarks']) AND is_array($post['landmarks'])) {
                $landmarks = $post['landmarks'];
                $landmarkgroup_id = $post['landmarkgroup_id'];
                
                $ajax_data['message'] = '';
                foreach ($landmarks as $landmark) {
                    if ($this->territory_logic->updateTerritoryInfo($landmark['id'], array('territorygroup_id' => $landmarkgroup_id), 'territory') !== false) {
                        if (! isset($ajax_data['code'])) {
                            $ajax_data['code'] = 0;
                            $ajax_data['message'] = 'Assigned landmarks to landmark group';
                        }
                        $ajax_data['data']['added_groups'][] = $landmark;      
                    } else {
                        $ajax_data['code'] = 1;
                        $errors = $this->territory_logic->getErrorMessage();
                        if (! empty($errors) AND is_array($errors)) {
                            $errors = implode(',', $errors);
                        } else {
                            $errors = 'Failed due to database error';
                        }
                        $ajax_data['message'] .= $territory['name'] . ' - ' . $errors . ' | ';
                        $ajax_data['data']['failed_groups'][] = $territory;
                    }
                }
                
                // if one or more vehicles can not be assign to this user, build a list of these vehicles and their associating error message
                if (($ajax_data['code'] === 1) AND ! empty($ajax_data['message']) AND ! empty($ajax_data['data']['failed_groups'])) {
                    $ajax_data['message'] = 'The following landmark(s) were not able to be assign to this group: ' . $ajax_data['message'];
                } 
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Invalid landmarks';                    
            }    
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid landmarkgroup id';
        }
       
        $this->ajax_respond($ajax_data);
    }

    /**
     * Remove vehicle group from user (called via ajax)
     *
     * POST params: vehicles, vehiclegroup_id
     *
     * @return void
     */
    public function removeLandmarksFromGroup()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();

        if (! empty($post['landmarkgroup_id'])) {
            if (! empty($post['landmarks']) AND is_array($post['landmarks'])) {
                $landmarks = $post['landmarks'];
                $ajax_data['message'] = '';
                foreach ($landmarks as $landmark) {
                    if ($this->territory_logic->updateTerritoryInfo($landmark['id'], array('territorygroup_id' => $post['landmarkgroup_id']), 'territory') !== false) {
                        if (! isset($ajax_data['code'])) {
                            $ajax_data['code'] = 0;
                            $ajax_data['message'] = 'Removed landmarks from landmark group';      
                        }
                    } else {
                        $ajax_data['code'] = 1;
                        $errors = $this->territory_logic->getErrorMessage();
                        if (! empty($errors) AND is_array($errors)) {
                            $errors = implode(',', $errors);
                        } else {
                            $errors = 'Failed due to database error';
                        }
                        $ajax_data['message'] .= $landmark['name'] . ' - ' . $errors . ' | ';
                        $ajax_data['data']['failed_groups'][] = $landmark;
                    }
                }
                
                // if one or more vehicle groups can not be remove from this user, build a list of these vehicle groups and their associating error message
                if (($ajax_data['code'] === 1) AND ! empty($ajax_data['message']) AND ! empty($ajax_data['data']['failed_groups'])) {
                    $ajax_data['message'] = 'The following landmark(s) were not able to be remove from this group: ' . $ajax_data['message'];
                }
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Invalid landmarks';                    
            }    
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid landmark group id';
        }
               
        $this->ajax_respond($ajax_data);
    }

    /**
     * Save landmark group (called via ajax)
     *
     * POST params: vehiclegroupname
     *
     * @return void
     */
    public function addLandmarkGroup()
    {
        $ajax_data  = array();
        $user_id    = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();
        $post       = $this->request->request->all();
        
        if (! empty($post['landmarkgroupname'])) {
            $landmarkgroup_id = $this->territory_logic->addTerritoryGroup($account_id, $post['landmarkgroupname'], 'landmark');
            if ($landmarkgroup_id !== false) {
                $add_to_user = $this->territory_logic->addTerritoryGroupToUser($landmarkgroup_id, $user_id);
                if ($add_to_user !== false) {
                    $ajax_data['code'] = 0;
                    $ajax_data['message'] = 'Added Landmark Group';   
                    $ajax_data['action'] = 'landmarkgroup-add';   
                } else {
                    $errors = $this->territory_logic->getErrorMessage();
                    if (! empty($errors) AND is_array($errors)) {
                        $errors = implode(',', $errors);
                    } else {
                        $errors = 'Failed due to database error';
                    }
                    
                    $ajax_data['code'] = 1;
                    $ajax_data['message'] = $errors;                        
                }    
            } else {
                $errors = $this->territory_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',', $errors);
                } else {
                    $errors = 'Failed due to database error';
                }
                
                $ajax_data['code'] = 1;
                $ajax_data['message'] = $errors;                   
            }    
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Territory Group Name cannot be blank';                
        }
               
        $this->ajax_respond($ajax_data);            
    }
    
    /**
     * Delete landmark group (called via ajax)
     *
     * POST params: unitgroup_id
     *
     * @return array
     */
    public function deleteLandmarkGroup()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();

        if (! empty($post['landmarkgroup_id'])) {
            if ($this->territory_logic->updateTerritoryGroupInfo($post['landmarkgroup_id'], array('active' => 0)) !== false) {
                $ajax_data['code'] = 0;
                $ajax_data['message'] = 'Deleted landmark group';
            } else {
                $errors = $this->territory_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',', $errors);
                } else {
                    $errors = 'Failed due to database error';
                }
                
                $ajax_data['code'] = 1;
                $ajax_data['message'] = $errors;
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid territory group id';
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Update the landmark group info by landmark group id (called via ajax)
     *
     * POST params: landmarkgroup_id
     *
     * @return array
     */
    public function updateLandmarkGroupInfo()
    {
        $ajax_data          = array();
        $post               = $this->request->request->all();
    	$landmarkgroup_id   = $post['primary_keys']['landmarkGroupPk'];
        $params             = array();

        if (! empty($post['id'])) {
            if ($post['id'] == 'landmark-group-name') {
                $params['territorygroupname'] = $post['value'];
                $params['account_id'] = $this->user_session->getAccountId();
            }
        }

        if (! empty($params) AND ! empty($landmarkgroup_id)) {
            if ($this->territory_logic->updateTerritoryGroupInfo($landmarkgroup_id, $params) !== false) {
                $ajax_data['data'] = $post;
                $ajax_data['code'] = 0;
                $ajax_data['message'] = 'Updated Landmark Group Information';
            } else {
                $errors = $this->territory_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',', $errors);    
                }
                $ajax_data['code'] = 1;
                $ajax_data['validation_error'][] = $ajax_data['message'] = $errors;
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Error';
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Get the landmark info by filtered paramaters (called via ajax)
     *
     * POST params: search_string, landmarkgroup_id
     *
     * @return array
     */
    public function getFilteredAvailableLandmarks()
    {
        $ajax_data  = array();
        $account_id = $this->user_session->getAccountId();
        $user_id    = $this->user_session->getUserId();
        $post       = $this->request->request->all();

        $this->territory_logic->setTerritoryType('landmark');
        
        $params['search_string'] = isset($post['search_string']) ? $post['search_string'] : '';
        $params['territorygroup_id'] = isset($post['landmarkgroup_id']) ? $post['landmarkgroup_id'] : 0;
        
        $landmarks = $this->territory_logic->getFilteredAvailableTerritories($account_id, $params);
        if ($landmarks !== false) {
            $ajax_data['code'] = 0;
            $ajax_data['data']['landmarks'] = $landmarks;
            $ajax_data['message'] = 'Successfully retrieved available landmarks';
        } else {
            $errors = $this->territory_logic->getErrorMessage();
            if (! empty($errors) AND is_array($errors)) {
                $errors = implode(',', $errors);
            } else {
                $errors = 'Failed due to database error';
            }
            
            $ajax_data['code'] = 1;
            $ajax_data['message'] = $errors; 
        }

        $this->ajax_respond($ajax_data);
    } 
}