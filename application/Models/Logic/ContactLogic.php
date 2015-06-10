<?php

namespace Models\Logic;

use Models\Logic\BaseLogic;

use Models\Data\ContactData;

use Swift\Transport\Validate;

use GTC\Component\Utils\Arrayhelper;

use Models\Logic\AddressLogic;

use GTC\Component\Form\Validation;

class ContactLogic extends BaseLogic
{
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        $this->contact_data = new ContactData;
        $this->address_logic = new AddressLogic;
        $this->validator = new Validation;
    }

    /**
     * Get the contact groups by account id
     *
     * @params: account_id
     *
     * @return array | bool
     */
    public function getContactGroupsByAccountId($account_id)
    {
        // $this->validator->validate('record_id', $account_id);

        // if ($this->validator->hasError()) {
        //     $this->setErrorMessage($this->validator->getErrorMessage());
        // }

        // if (! $this->hasError()) {
            return $this->contact_data->getContactGroupsByAccountId($account_id);
        // }
        // return false;
    }

    /**
     * Get the contacts by account id
     *
     * @params: account_id
     *
     * @return array | bool
     */
    public function getContactsByAccountId($account_id)
    {
        $this->validator->validate('record_id', $account_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->contact_data->getContactsByAccountId($account_id);
        }
        return false;
    }

    public function getContactById($contact_id)
    {
        $this->validator->validate('record_id', $contact_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            //return $this->contact_data->getContactById($contact_id);
            $contact = $this->contact_data->getContactById($contact_id);
            if ($contact !== false) {
                foreach ($contact as $index => $con) {
                    if (! empty($con['cellnumber'])) {
                        $contact[$index]['formatted_cellnumber'] = $this->address_logic->formatPhoneDisplay($con['cellnumber']);
                    }
                }
                return $contact;
            }
        }
        return false;
    }

    public function getContactGroupById($contactgroup_id, $get_contacts = false)
    {
        $this->validator->validate('record_id', $contactgroup_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            $contactgroup = $this->contact_data->getContactGroupById($contactgroup_id);
            if (($contactgroup !== false) AND is_array($contactgroup) AND (count($contactgroup) == 1)) {
                $contactgroup = array_pop($contactgroup);
                if ($get_contacts === true) {

                    $map_index = array();

                    // get assigned contacts
                    $assigned_contacts = $this->contact_data->getContactByGroupId($contactgroup_id);
                    if ($assigned_contacts === false) {
                        $assigned_contacts = array();
                    }

                    // get available contacts
                    $available_contacts = $this->contact_data->getContactsByAccountId($contactgroup['account_id']);
                    if ($available_contacts === false) {
                        $available_contacts = array();
                    } else if (! empty($available_contacts)) {
                        // create a contact_id - index association of the available contacts for faster access
                        foreach ($available_contacts as $index => $avc) {
                            if (! isset($map_index[$avc['contact_id']])) {
                                $map_index[$avc['contact_id']] = $index;
                            }

                            $available_contacts[$index]['email_enabled'] = (! empty($avc['email'])) ? true : false;
                            $available_contacts[$index]['sms_enabled'] = (! empty($avc['cellnumber']) AND $avc['cellnumber'] !== '0') ? true : false;
                        }
                    }

                    if (! empty($assigned_contacts)) {
                        // iterate through the assigned contact and remove it from the available contacts if method is already set to 'all'
                        foreach ($assigned_contacts as $index => $asc) {
                            if (! empty($asc['method'])) {

                                // defaults sms & email enabled to false
                                $assigned_contacts[$index]['sms_enabled'] = $assigned_contacts[$index]['email_enabled'] = false;

                                if (isset($map_index[$asc['contact_id']])) {
                                    $contact = $available_contacts[$map_index[$asc['contact_id']]];
                                    switch ($asc['method']) {
                                        case 'all':
                                            // if the assigned contact method is 'all', remove the available contact
                                            unset($available_contacts[$map_index[$asc['contact_id']]]);

                                            if (! empty($asc['cellnumber']) AND $asc['cellnumber'] !== '0') {
                                                $assigned_contacts[$index]['sms_enabled'] = true;
                                            }

                                            if (! empty($asc['email'])) {
                                                $assigned_contacts[$index]['email_enabled'] = true;
                                            }
                                            break;
                                        case 'sms':
                                            // if the assigned contact method is 'sms', unset the available contact if it's the contact's only method, else disable 'sms' for the available contact
                                            if (! empty($contact['email'])) {
                                                $available_contacts[$map_index[$asc['contact_id']]]['sms_enabled'] = false;
                                            } else {
                                                unset($available_contacts[$map_index[$asc['contact_id']]]);
                                            }
                                            $assigned_contacts[$index]['sms_enabled'] = true;
                                            break;
                                        case 'email':
                                            // if the assigned contact method is 'email', unset the available contact if it's the contact's only method, else disable 'email' for the available contact
                                            if (! empty($contact['cellnumber']) AND $contact['cellnumber'] !== '0') {
                                                $available_contacts[$map_index[$asc['contact_id']]]['email_enabled'] = false;
                                            } else {
                                                unset($available_contacts[$map_index[$asc['contact_id']]]);
                                            }
                                            $assigned_contacts[$index]['email_enabled'] = true;
                                            break;
                                    }
                                }
                            }
                        }
                    }

                    $contactgroup['assigned_contacts'] = $assigned_contacts;
                    $contactgroup['available_contacts'] = $available_contacts;

                }
            }
            return $contactgroup;

        }
        return false;
    }

    /**
     * Get contact by email
     *
     * @param string    email
     * @param bool      include_user
     * @return array
     */
    public function getContactByEmail($email, $include_user = false)
    {
        if (! empty($email)) {
            if (! \Swift_Validate::email($email)) {
                $this->setErrorMessage('Invalid email');
            }
        } else {
            $this->setErrorMessage('Invalid email');
        }

        if ($include_user !== false AND $include_user !== true) {
            $this->setErrrorMessage('Invalid include user indicator');
        }

        if (! $this->hasError()) {
            return $this->contact_data->getContactByEmail($email, $include_user);
        }
        return false;
    }

    public function getContactByGroupId($contactgroup_id)
    {
        $this->validator->validate('record_id', $contactgroup_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->contact_data->getContactByGroupId($contactgroup_id);
        }
        return false;
    }

    /**
     * Add contact to an account
     *
     * @param array $params
     *
     * @return int|bool
     */
    public function addContact($params)
    {
        $this->validator->validate('record_id', $params['account_id']);
        // $this->validator->validate('first_last_name', $params['firstname']);
        // $this->validator->validate('first_last_name', $params['lastname']);
        if(!($params['firstname'])){
            $this->setErrorMessage('First Name is Missing');
        }
        if(!($params['lastname'])){
            $this->setErrorMessage('Last Name is Missing');
        }

        if (! empty($params['email']) OR (! empty($params['cellnumber']) AND ! empty($params['cellcarrier_id']))) {
            if (! empty($params['email'])) {
                if (! \Swift_Validate::email($params['email'])) {
                    $this->setErrorMessage('err_email');
                } else {
                    // $email = $this->contact_data->getContactByEmail($params['email']);
                    // if (! empty($email) AND empty($params['user_id'])) {    // if contact email is duplicated and we're not associating it to a user, throw error
                    //     $this->setErrorMessage('This email already exist');
                    // }
                }
            }

            if (! empty($params['cellnumber']) AND ! empty($params['cellcarrier_id'])) {

                $params['cellnumber'] = $this->address_logic->formatPhoneForSaving($params['cellnumber']);

                if (! is_numeric($params['cellnumber']) OR strlen($params['cellnumber']) !== 10) {
                    $this->setErrorMessage('err_cellnumber');
                }

                if (! is_numeric($params['cellcarrier_id']) OR $params['cellcarrier_id'] <= 0) {
                    $this->setErrorMessage('err_cellcarrier');
                }
            }
        } else {
            $this->setErrorMessage('err_contact_methods');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            $duplicate = $this->contact_data->getContactByName($params['account_id'], $params['firstname'], $params['lastname']);
            if (empty($duplicate) OR ! empty($params['user_id'])) { // add contact record if: the contactname does not exist or it exist but it's being created for a new user
                return $this->contact_data->addContact($params);
            } else {
                $this->setErrorMessage('err_duplicate_contactname');
            }
        }
        return false;
    }

    /**
     * Add contact to a contact group
     *
     * @param array $params
     *
     * @return bool
     */
    public function addContactToContactGroup($params)
    {
        $this->validator->validate('record_id', $params['contactgroup_id']);
        $this->validator->validate('record_id', $params['contact_id']);

        if (empty($params['method']) OR (! empty($params['method']) AND ! in_array($params['method'], array('all','email','sms')))) {
            $this->setErrorMessage('err_contact_method');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->contact_data->addContactToContactGroup($params);
        }
        return false;
    }

    /**
     * Get the filtered contacts by provided params
     *
     * @params: int $account_id
     * @params: array $params
     *
     * @return array
     */
    public function getFilteredContacts($account_id, $params)
    {
        $total_contacts = array();
        $contacts['iTotalRecords']          = 0;
        $contacts['iTotalDisplayRecords']   = 0;
        $contacts['data']                   = array();

        $this->validator->validate('record_id', $account_id);

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('err_params');
        } else {
            //alphanumeric $params['search_string']
            if (isset($params['string_search']) AND $params['string_search'] != "") {
                $this->validator->validate('alphanumeric', $params['string_search']);
            }
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            switch ($params['filter_type']) {

                case 'string_search':

                    $searchfields = array('firstname', 'lastname');
                    $result = $this->contact_data->getFilteredContactsStringSearch($account_id, $params, $searchfields);
                    if ($result !== false) {
                        $total_contacts = $result;
                    }

                break;

                case 'group_filter':

                    if (isset($params['contactgroup_id']) AND (strtolower($params['contactgroup_id']) == 'all' OR $params['contactgroup_id'] == '')) {
                        $params['contactgroup_id'] = array();
                    } elseif (! is_array($params['contactgroup_id'])) {
                        $params['contactgroup_id'] = array($params['contactgroup_id']);
                    }

                    if (isset($params['contact_type']) AND strtolower($params['contact_type']) == 'all') {
                        $params['contact_type'] = '';
                    }

                    $result = $this->contact_data->getFilteredContacts($account_id, $params);
                    if ($result !== false) {
                        $total_contacts = $result;
                    }

                break;

                default:

                break;
            }

            // for the formatted unit events, process for datatable return results
            if (! empty($total_contacts)) {

                // init total results
                $iTotal                             = count($total_contacts);
                $iFilteredTotal                     = count($total_contacts);
                $contacts['iTotalRecords']          = $iTotal;
                $contacts['iTotalDisplayRecords']   = $iFilteredTotal;
                $aColumns                           = array();        // datatable columns event field/key names

                for ( $i = 0 ; $i < intval( $params['iColumns'] ) ; $i++ ) {
                    $aColumns[] = $params['mDataProp_'.$i];
                }

                $formatted_results = $contact_map_index = array();
                if (! empty($total_contacts)) {
                    foreach ($total_contacts as $contact) {
                        $row = $contact;
                        $row['DT_RowId'] = 'contact-tr-'.$row['contact_id'];       // automatic tr id value for dataTable to set

                        if ($row['contactname'] == '' OR is_null($row['contactname'])){
                            $row['contactname'] = $params['default_value'];
                        }

                        if ($row['contactgroupname'] == '' OR is_null($row['contactgroupname'])){
                            $row['contactgroupname'] = $params['default_value'];
                        }

                        // build contact method and details
                        $row['details'] = $row['contact_method'] = '';
                        switch ($params['contact_type']) {
                            case 'email':
                                if (! empty($row['email'])) {
                                    $row['details'] = $row['email'];
                                    $row['contact_method'] = 'E-Mail';
                                }
                                break;
                            case 'sms':
                                if (isset($row['cellnumber']) AND ! empty($row['cellnumber']) AND $row['cellnumber'] !== '0' AND ! empty($row['gateway'])) {
                                    $row['details'] .= $row['cellnumber'] . '@' . $row['gateway'];
                                    $row['contact_method'] = 'SMS';
                                }
                                break;
                            case 'All':
                            case 'all':
                            default:
                                if (isset($row['cellnumber']) AND ! empty($row['cellnumber']) AND ! empty($row['gateway'])) {
                                    $row['details'] .= $row['cellnumber'] . '@' . $row['gateway'];
                                    $row['contact_method'] = 'SMS';
                                }

                                if (! empty($row['email'])) {
                                    $row['details'] .= ((! empty($row['contact_method'])) ? ' / ' : '') . $row['email'];
                                    $row['contact_method'] = (! empty($row['contact_method'])) ? 'All' : 'E-Mail';
                                }
                                break;
                        }

                        // if contact already exist, update the contact groups for this contact
                        if (! isset($contact_map_index[$row['contact_id']])) {
                            $contact_map_index[$row['contact_id']] = count($formatted_results);
                            $formatted_results[] = $row;
                        } else {
                            $formatted_results[$contact_map_index[$row['contact_id']]]['contactgroupname'] .= ', ' . $row['contactgroupname'];
                        }
                    }

                    // if doing a column sorting
                    if ( isset( $params['iSortCol_0'] ) AND $params[ 'bSortable_'.intval($params['iSortCol_0']) ] == "true")
                    {
                        $formatted_results = $this->filterContactsSort($aColumns[ intval($params['iSortCol_0']) ], ($params['sSortDir_0']==='asc' ? 'asc' : 'desc'), $formatted_results);
                    }

                    // if doing paging, find correct page list
                    if ( isset($params['iDisplayStart']) AND $params['iDisplayLength'] != '-1' )
                    {
                        $formatted_results = array_splice($formatted_results, $params['iDisplayStart'], $params['iDisplayLength']);
                    }
                }

                $contacts['data'] = $formatted_results;
            }
        }

        return $contacts;
    }

    /**
     * Return contacts having sorted by column field by sort order
     *
     * @params: string $column_name
     * @params: string $sort_order
     * @params: array contact
     *
     * @return array $results
     */
    public function filterContactsSort($column_name, $sort_order, $contacts)
    {
        $results = $contacts;
        $sorting_order = '<';       // ascending sort by default
        if ( $sort_order == 'desc') {
            $sorting_order = '>';       // descending sort
        }

        if ( isset($column_name) AND $column_name != "" ) {
            switch($sorting_order) {
                case '<':
                    usort($results, Arrayhelper::usort_compare_asc($column_name));
                break;
                case '>':
                    usort($results, Arrayhelper::usort_compare_desc($column_name));
                break;
            }
        }

        return $results;
    }

    /**
     * Get the filtered contact groups by provided params
     *
     * @params: int $account_id
     * @params: array $params
     *
     * @return array
     */
    public function getFilteredContactGroups($account_id, $params)
    {
        $total_contactgroups = array();
        $contactgroups['iTotalRecords']          = 0;
        $contactgroups['iTotalDisplayRecords']   = 0;
        $contactgroups['data']                   = array();

        $this->validator->validate('record_id', $account_id);

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('err_params');
        } else {
            //alphanumeric $params['search_string']
            if (isset($params['string_search']) AND $params['string_search'] != "") {
                $this->validator->validate('alphanumeric', $params['string_search']);
            }
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            switch ($params['filter_type']) {

                case 'string_search':

                    $searchfields = array('contactgroupname');
                    $result = $this->contact_data->getFilteredContactGroupsStringSearch($account_id, $params, $searchfields);
                    if ($result !== false) {
                        $total_contactgroups = $result;
                    }

                break;

                case 'method_filter':

                    if (isset($params['method_type']) AND strtolower($params['method_type']) == 'all') {
                        $params['method_type'] = '';
                    }

                    $result = $this->contact_data->getContactGroupsByAccountId($account_id);
                    if ($result !== false) {
                        $total_contactgroups = $result;
                    }

                break;

                default:

                break;
            }

            // for the formatted unit events, process for datatable return results
            if (! empty($total_contactgroups)) {

                // init total results
                $iTotal                             = count($total_contactgroups);
                $iFilteredTotal                     = count($total_contactgroups);
                $contactgroups['iTotalRecords']         = $iTotal;
                $contactgroups['iTotalDisplayRecords']  = $iFilteredTotal;
                $aColumns                           = array();        // datatable columns event field/key names

                for ( $i = 0 ; $i < intval( $params['iColumns'] ) ; $i++ ) {
                    $aColumns[] = $params['mDataProp_'.$i];
                }

                $formatted_results = array();
                if (! empty($total_contactgroups)) {
                    foreach ($total_contactgroups as $contactgroup) {
                        $row = $contactgroup;
                        $row['DT_RowId'] = 'contactgroup-tr-'.$row['contactgroup_id'];       // automatic tr id value for dataTable to set

                        // get contacts by contact method for each contact group
                        $row['total_contact'] = 0;
                        $row['contact_method'] = '-';
                        $has_sms = $has_email = false;

                        $contacts = $this->contact_data->getContactByGroupId($row['contactgroup_id'], $params['method_type']);

                        if (! empty($contacts)) {
                            // get the number of total contacts for this contact group
                            $row['total_contact'] = count($contacts);

                            // if the selected contact method is 'All', determine if this contact group has mixed contact methods
                            if (($params['method_type'] == '') OR strtolower($params['method_type']) == 'all') {
                                // iterate through each contact and find to find out if this contact group has mixed contact methods
                                foreach ($contacts as $contact) {
                                    if (! empty($contact['method'])) {
                                        $method = '-';
                                        switch ($contact['method']) {
                                            case 'sms':
                                                $method = 'SMS';
                                                break;
                                            case 'email':
                                                $method = 'E-Mail';
                                                break;
                                            case 'all':
                                                $method = 'All';
                                                break;
                                        }

                                        if ($method === 'All' OR ($row['contact_method'] === 'SMS' AND $method === 'E-Mail') OR ($row['contact_method'] === 'E-Mail' AND $method === 'SMS')) {
                                            $row['contact_method'] = 'All';
                                            break;
                                        } else {
                                            $row['contact_method'] = $method;
                                        }
                                    }
                                }
                            } else { // else if the selected contacted method is not 'All', set it's contact method to the selected method
                                $row['contact_method'] = ($params['method_type'] == 'email') ? 'E-Mail' : 'SMS';
                            }
                        }

                        // save the contact group if method type is 'All' or if it has contacts
                        if (($params['method_type'] == '') OR (strtolower($params['method_type']) == 'all') OR ($row['total_contact'] > 0)) {
                            $formatted_results[] = $row;
                        }
                    }

                    // if doing a column sorting
                    if ( isset( $params['iSortCol_0'] ) AND $params[ 'bSortable_'.intval($params['iSortCol_0']) ] == "true")
                    {
                        $formatted_results = $this->filterContactsSort($aColumns[ intval($params['iSortCol_0']) ], ($params['sSortDir_0']==='asc' ? 'asc' : 'desc'), $formatted_results);
                    }

                    // if doing paging, find correct page list
                    if ( isset($params['iDisplayStart']) AND $params['iDisplayLength'] != '-1' )
                    {
                        $formatted_results = array_splice($formatted_results, $params['iDisplayStart'], $params['iDisplayLength']);
                    }
                }

                $contactgroups['data'] = $formatted_results;
            }
        }

        return $contactgroups;
    }

    /**
     * Get sms carrier options
     *
     * @return array
     */
    public function getSMSCarrierOptions()
    {
        $carriers = $this->contact_data->getSMSCarrierOptions();
        if ($carriers === false) {
            $carriers = array();
        }
        return $carriers;
    }

    /**
     * Update contact info
     *
     * @param int contact_id
     * @oaram array params
     *
     * @return array|bool
     */
    public function updateContactInfo($contact_id, $params)
    {
        $this->validator->validate('record_id', $contact_id);

        if (is_array($params) AND (count($params) > 0)) {
            if (! empty($params['firstname'])) {
                $this->validator->validate('first_last_name', $params['firstname']);
            }

            if (! empty($params['lastname'])) {
                $this->validator->validate('first_last_name', $params['lastname']);
            }

            if (! empty($params['email'])) {
                $this->validator->validate('email', $params['email']);
            }

            if (! empty($params['cellnumber'])) {
                $params['cellnumber'] = $this->address_logic->formatPhoneForSaving($params['cellnumber']);

                if (empty($params['cellnumber']) OR (strlen($params['cellnumber']) !== 10)) {
                    $this->setErrorMessage('Invalid SMS Number');
                } else {
                    // when update sms, carrier must have a value to continue update
                    if (! isset($params['cellcarrier_id']) OR empty($params['cellcarrier_id'])) {
                        $this->setErrorMessage('Must Update SMS Carrier as well');
                    }
                }
            }

            if (! empty($params['cellcarrier_id'])) {
                if (! is_numeric($params['cellcarrier_id']) OR $params['cellcarrier_id'] <= 0) {
                    $this->setErrorMessage('Invalid SMS Carrier');
                } else {
                    // when update carrier, sms number must have a value to continue update
                    if (! isset($params['cellnumber']) OR empty($params['cellnumber'])) {
                        $this->setErrorMessage('Must Update SMS Number as well');
                    }
                }
            }

            if (isset($params['contactstatus']) AND empty($params['contactstatus'])) {
                $this->setErrorMessage('err_contactstatus');
            }
        } else {
            $this->setErrorMessage('err_param');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->contact_data->updateContactInfo($contact_id, $params);
        }

        return false;
    }

    /**
     * Update contact group info
     *
     * @param int contactgroup_id
     * @oaram array params
     *
     * @return array|bool
     */
    public function updateContactGroupInfo($contactgroup_id, $params)
    {
        $this->validator->validate('record_id', $contactgroup_id);

        if (is_array($params) AND (count($params) > 0)) {
            if (isset($params['contactgroupname'])) {
                $this->validator->validate('group_name', $params['contactgroupname']);
                // if group name is valid, check for duplication
                $duplicate = $this->contact_data->getContactGroupByName($params['account_id'], $params['contactgroupname']);
                if (! empty($duplicate)) {
                    $this->setErrorMessage('Duplicated Contact Group Name');
                }
                unset($params['account_id']);
            }
        } else {
            $this->setErrorMessage('err_param');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->contact_data->updateContactGroupInfo($contactgroup_id, $params);
        }
        return false;
    }

    /**
     * Delete contact
     *
     * @param int contact_ids
     *
     * @return bool
     */
    public function deleteContact($contact_id)
    {
        $this->validator->validate('record_id', $contact_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->contact_data->deleteContact($contact_id);
        }
        return false;
    }

    /**
     * Delete contact group
     *
     * @param int contactgroup_id
     *
     * @return bool
     */
    public function deleteContactGroup($contactgroup_id)
    {
        $this->validator->validate('record_id', $contactgroup_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->contact_data->deleteContactGroup($contactgroup_id);
        }
        return false;
    }

    /**
     * Add contact group
     *
     * @param int account_id
     * @param string contactgroup_name
     *
     * @return bool
     */
    public function addContactGroup($account_id, $contactgroup_name)
    {
        $groupname = str_replace("'","",$contactgroup_name);
        $this->validator->validate('record_id', $account_id);
        $this->validator->validate('group_name', $groupname);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            $duplicate = $this->contact_data->getContactGroupByName($account_id, $groupname);
            if (empty($duplicate)) {
                $params = array(
                    'account_id' => $account_id,
                    'contactgroupname' => $contactgroup_name
                );
                return $this->contact_data->addContactGroup($params);
            } else {
                $this->setErrorMessage('err_duplicate_groupname');
            }
        }
        return false;
    }

    /**
     * Delete contact from contact group
     *
     * @param int contact_id
     * @param int contactgroup_id
     *
     * @return bool
     */
    public function deleteContactFromContactGroup($contact_id, $contactgroup_id)
    {
        $this->validator->validate('record_id', $contact_id);
        $this->validator->validate('record_id', $contactgroup_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->contact_data->deleteContactFromContactGroup($contact_id, $contactgroup_id);
        }
        return false;
    }

    /**
     * Get contact from contact group
     *
     * @param int contact_id
     * @param int contactgroup_id
     *
     * @return bool|array
     */
    public function getContactFromContactGroup($contact_id, $contactgroup_id)
    {
        $this->validator->validate('record_id', $contact_id);
        $this->validator->validate('record_id', $contactgroup_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->contact_data->getContactFromContactGroup($contact_id, $contactgroup_id);
        }
        return false;
    }

    /**
     * Update contact method in contactgroup_contact table
     *
     * @param int contact_id
     * @param int contactgroup_id
     *
     * @return bool|array
     */
    public function updateContactGroupContact($contact_id, $contactgroup_id, $params)
    {
        $this->validator->validate('record_id', $contact_id);
        $this->validator->validate('record_id', $contactgroup_id);

        if (! empty($params)) {
            if (! empty($params['method']) AND ! in_array($params['method'], array('all','sms','email'))) {
                $this->setErrorMessage('err_contact_method');
            }
        } else {
            $this->setErrorMessage('err_params');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->contact_data->updateContactGroupContact($contact_id, $contactgroup_id, $params);
        }
        return false;
    }

    /**
     * Get contact by user id
     *
     * @param int    user_id
     * @return array
     */
    public function getContactByUserId($user_id)
    {
        $this->validator->validate('record_id', $user_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->contact_data->getContactByUserId($user_id);
        }
        return false;
    }

    /**
     * Get error messages (calls the parent method)
     *
     * @param string token
     *
     * @return bool|array
     */
    public function getErrorMessage()
    {
        return parent::getErrorMessage();
    }
}
