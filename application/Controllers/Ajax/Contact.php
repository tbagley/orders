<?php

namespace Controllers\Ajax;

use Models\Data\ContactData;
use Models\Logic\ContactLogic;

/**
 * Class Contact
 *
 * Thin Controller for Contact CRUD
 *
 */
class Contact extends BaseAjax
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->contact_data = new ContactData;
        $this->contact_logic = new ContactLogic;

    }

    public function updateContactInfo()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();

        if (! empty($post['id'])) {
            $params = array();
            $contact_id = $post['primary_keys']['contactPk'];
            $contact_sms = (isset($post['primary_keys']['contactSms']) AND $post['primary_keys']['contactSms'] != '') ? $post['primary_keys']['contactSms'] : '';
            $contact_carrier = (isset($post['primary_keys']['contactCarrier']) AND $post['primary_keys']['contactCarrier'] != '') ? $post['primary_keys']['contactCarrier'] : '';

            switch ($post['id']) {
                case 'contact-first-name':
                    $params['firstname'] = $post['value'];
                    break;
                case 'contact-last-name':
                    $params['lastname'] = $post['value'];
                    break;
                case 'contact-email':
                    $params['email'] = $post['value'];
                    break;
                case 'contact-sms':
                    $params['cellnumber'] = $post['value'];

                    if ($post['value'] == '') {
                        $params['cellcarrier_id'] = 0;
                    } else {
                        if (! empty($contact_carrier)) {
                            $params['cellcarrier_id'] = $contact_carrier;
                        }
                    }
                    break;
                case 'contact-carrier':
                    $params['cellcarrier_id'] = $post['value'];

                    if (! empty($contact_sms)) {
                        $params['cellnumber'] = $contact_sms;
                    }
                    break;
            }

            if (! empty($params)) {
                $update = $this->contact_logic->updateContactInfo($contact_id, $params);
                if ($update !== false) {
                    $ajax_data['code'] = 0;
                    $ajax_data['data'] = $post;
                    $ajax_data['message'] = 'Updated Contact Information';
                } else {
                    $errors = $this->contact_logic->getErrorMessage();
                    if (! empty($errors) AND is_array($errors)) {
                        $errors = implode(',', $errors);
                    } else {
                        $errors = 'Action failed due to a database issue';
                    }

                    $ajax_data['code']      = (in_array($post['id'], array('contact-sms','contact-carrier'))) ? 0 : 1;
                    $ajax_data['data']      = $post;
                    $ajax_data['message']   = $errors;
                    $ajax_data['validation_error'][] = $errors; // need to trigger sms field highligting
                }
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'No contact info to update';
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid paramter';
        }

        $this->ajax_respond($ajax_data);
    }

    public function updateContactGroupInfo()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
        $account_id = $this->user_session->getAccountId();

        if (! empty($post['id'])) {
            $params = array();
            $contactgroup_id = $post['primary_keys']['contactGroupPk'];

            switch ($post['id']) {
                case 'contact-group-name':
                    $params['contactgroupname'] = $post['value'];
                    $params['account_id'] = $account_id;
                    break;
            }

            if (! empty($params)) {
                $update = $this->contact_logic->updateContactGroupInfo($contactgroup_id, $params);
                if ($update !== false) {
                    $ajax_data['code'] = 0;
                    $ajax_data['data'] = $post;
                    $ajax_data['message'] = 'Updated Contact Group Information';
                } else {
                    // if sms or carrier requirement error
                    $errors = $this->contact_logic->getErrorMessage();
                    if (! empty($errors) AND is_array($errors)) {
                        $errors = implode(',', $errors);
                    } else {
                        $errors = 'Action failed due to a database issue';
                    }

                    $ajax_data['code']      = 1;
                    $ajax_data['data']      = $post;
                    $ajax_data['message']   = $errors;
                    $ajax_data['validation_error'][] = $errors;
                }
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'No contact group info to update';
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid paramter';
        }

        $this->ajax_respond($ajax_data);
    }

    public function getContactOptions($placeholder = null, $value = '')
    {
        $output = '[';

        if ($placeholder !== null) {
            $output .= '
                {
                    "value": "'.$value.'",
                    "text":  "'.$placeholder.'"
                },
            ';
        }

        $account_id = $this->user_session->getAccountId();
        $contacts = $this->contact_logic->getContactsByAccountId($account_id);

        if ($contacts !== false) {
            $last_index = count($contacts) - 1;
            foreach ($contacts as $index => $contact) {
                $separator = ',';

                if ($index == $last_index) {
                    $separator = '';
                }

                $output .= '{"value": "' . $contact['contact_id'] . '", "text": "' . $contact['contactname'] . '"}' . $separator;
            }
        }

        $output .= ']';

        die($output);
    }

    public function getContactGroupOptions($placeholder = null, $value = '')
    {
        $output = '[';

        if ($placeholder !== null) {
            $output .= '
                {
                    "value": "'.$value.'",
                    "text":  "'.$placeholder.'"
                },
            ';
        }

        $account_id = $this->user_session->getAccountId();
        $contact_groups = $this->contact_logic->getContactGroupsByAccountId($account_id);

        if ($contact_groups !== false) {
            $last_index = count($contact_groups) - 1;
            foreach ($contact_groups as $index => $group) {
                $separator = ',';

                if ($index == $last_index) {
                    $separator = '';
                }

                $output .= '{"value": "' . $group['contactgroup_id'] . '", "text": "' . $group['contactgroupname'] . '"}' . $separator;
            }
        }

        $output .= ']';

        die($output);
    }

    public function getSMSCarrierOptions()
    {
        $output = '[';
        $sms_carriers = $this->contact_logic->getSMSCarrierOptions();

        if ($sms_carriers !== false) {
            $last_index = count($sms_carriers) - 1;
            foreach ($sms_carriers as $index => $carrier) {
                $separator = ',';

                if ($index == $last_index) {
                    $separator = '';
                }

                $output .= '{"value": "' . $carrier['cellcarrier_id'] . '", "text": "' . $carrier['cellcarrier'] . '"}' . $separator;
            }
        }

        $output .= ']';

        die($output);
    }

    public function getContactMethodOptions()
    {
        $output =
            '[
                {
                    "value": "email",
                    "text": "E-Mail"
                },
                {
                    "value": "sms",
                    "text": "SMS"
                },
                {
                    "value": "all",
                    "text": "All"
                }
            ]
            ';

            die($output);
    }

    /**
     * Add Contact
     *
     * Post Params: contact_firstname, contact_lastname, contact_email, contact_sms, contact_sms_carrier, contactgroup_id, contact_method
     *
     * @return void
     */
    public function addContact()
    {
        $ajax_data  = $contact_params = $contactgroup_params = array();
        $account_id = $this->user_session->getAccountId();
        $post       = $this->request->request->all();
        $error      = '';

        if (! empty($post['contact_firstname'])) {
            $contact_params['firstname'] = $post['contact_firstname'];
        } else {
            $error = 'Invalid Contact First Name';
        }

        if (! empty($post['contact_lastname'])) {
            $contact_params['lastname'] = $post['contact_lastname'];
        } else {
            $error = 'Invalid Contact Last Name';
        }

        if (! empty($post['contact_email'])) {
            $contact_params['email'] = $post['contact_email'];
        }

        if (! empty($post['contact_sms']) AND ! empty($post['contact_sms_carrier'])) {
            $contact_params['cellnumber'] = $post['contact_sms'];
            $contact_params['cellcarrier_id'] = $post['contact_sms_carrier'];
        } else if (empty($post['contact_email'])) {
            $error = 'Either an E-Mail or an SMS Number is required';
        }

        if (! empty($post['contactgroup_id'])) {
            $contactgroup_params['contactgroup_id'] = $post['contactgroup_id'];
        }

        if (! empty($post['contact_method']) AND in_array($post['contact_method'], array('all','email','sms'))) {
            $contactgroup_params['method'] = $post['contact_method'];
        } else if (! empty($contact_params['contactgroup_id'])) {
            $error = 'Invalid contact method';
        }

        if ($error == '') {
            if (! empty($contact_params)) {
                $contact_params['account_id'] = $account_id;
                $contact_id = $this->contact_logic->addContact($contact_params);
                if ($contact_id !== false) {
                    $ajax_data['code'] = 0;
                    $ajax_data['message'] = 'Saved contact';

                    // save contact to contactgroup if needed
                    if (! empty($contactgroup_params)) {
                        $contactgroup_params['contact_id'] = $contact_id;
                        $success = $this->contact_logic->addContactToContactGroup($contactgroup_params);
                        if ($success !== false) {
                            // success message already set
                        } else {
                            $errors = $this->contact_logic->getErrorMessage();
                            if (! empty($errors) AND is_array($errors)) {
                                $errors = implode(',', $errors);
                            } else {
                                $errors = 'Action failed due to a database issue';
                            }

                            $ajax_data['code'] = 1;
                            $ajax_data['message'] = $errors;
                        }
                    }
                } else {
                    $errors = $this->contact_logic->getErrorMessage();
                    if (! empty($errors) AND is_array($errors)) {
                        $errors = implode(',', $errors);
                    } else {
                        $errors = 'Action failed due to a database issue';
                    }

                    $ajax_data['code'] = 1;
                    $ajax_data['message'] = $errors;
                }
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'No contact info to be added';
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = $error;
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Get the contacts by filtered paramaters (called via ajax)
     *
     * POST params: filter_type, contact_id, contactgroup_id, search_string
     *
     * @return array
     */
    public function getFilteredContacts()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
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
            $contacts = $this->contact_logic->getFilteredContacts($account_id, $params);
            if ($contacts !== false) {

                $output['iTotalRecords']        = (isset($contacts['iTotalRecords']) AND ! empty($contacts['iTotalRecords'])) ? $contacts['iTotalRecords'] : 0;
                $output['iTotalDisplayRecords'] = (isset($contacts['iTotalDisplayRecords']) AND ! empty($contacts['iTotalDisplayRecords'])) ? $contacts['iTotalDisplayRecords'] : 0;
                $output['data']                 = (isset($contacts['data']) AND ! empty($contacts['data'])) ? $contacts['data'] : array();
            }
        }

        echo json_encode( $output );
        exit;
    }

    /**
     * Get the contact groups by filtered paramaters (called via ajax)
     *
     * POST params: filter_type, method_type, search_string
     *
     * @return array
     */
    public function getFilteredContactGroups()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
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

        $search_type                = (isset($post['filter_type']) AND !empty($post['filter_type'])) ? $post['filter_type'] : 'method_filter';
        $params                     = $post;
        $params['default_value']    = '-';

        if ($search_type != '') {
            $contacts = $this->contact_logic->getFilteredContactGroups($account_id, $params);
            if ($contacts !== false) {

                $output['iTotalRecords']        = (isset($contacts['iTotalRecords']) AND ! empty($contacts['iTotalRecords'])) ? $contacts['iTotalRecords'] : 0;
                $output['iTotalDisplayRecords'] = (isset($contacts['iTotalDisplayRecords']) AND ! empty($contacts['iTotalDisplayRecords'])) ? $contacts['iTotalDisplayRecords'] : 0;
                $output['data']                 = (isset($contacts['data']) AND ! empty($contacts['data'])) ? $contacts['data'] : array();
            }
        }

        echo json_encode( $output );
        exit;
    }

    /**
     * Get the contact by contact id (called via ajax)
     *
     * POST params: contact_id
     *
     * @return array
     */
    public function getContactById()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();

        if (! empty($post['contact_id'])) {
            $contact_id = $post['contact_id'];
            $contact = $this->contact_logic->getContactById($contact_id);
            if ($contact !== false) {
                if (! empty($contact) AND is_array($contact)) {
                    $contact = array_pop($contact);
                    $ajax_data['code'] = 0;
                    $ajax_data['data']['contact'] = $contact;
                    $ajax_data['message'] = 'Successfully retrieved contact info';
                }
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Failed to get contact info';
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid contact id';
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Get the contact group by contactgroup_id (called via ajax)
     *
     * POST params: contactgroup_id
     *
     * @return array
     */
    public function getContactGroupById()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();

        if (! empty($post['contactgroup_id'])) {
            $contactgroup_id = $post['contactgroup_id'];
            $contactgroup = $this->contact_logic->getContactGroupById($contactgroup_id, true);
            if ($contactgroup !== false) {
                $ajax_data['code'] = 0;
                $ajax_data['data']['contactgroup'] = $contactgroup;
                $ajax_data['message'] = 'Successfully retrieved contact group info';
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Failed to get contact group info';
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid contact group id';
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Delete the contact by contact id (called via ajax)
     *
     * POST params: contact_id
     *
     * @return array
     */
    public function deleteContact()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();

        if (! empty($post['contact_id'])) {
            $contact_id = $post['contact_id'];
            $contact = $this->contact_logic->deleteContact($contact_id);
            if ($contact !== false) {
                $ajax_data['code'] = 0;
                $ajax_data['message'] = 'Successfully deleted contact';
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Failed to delete contact';
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid contact id';
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Delete the contact group by contactgroup id (called via ajax)
     *
     * POST params: contactgroup_id
     *
     * @return array
     */
    public function deleteContactGroup()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();

        if (! empty($post['contactgroup_id'])) {
            $contactgroup_id = $post['contactgroup_id'];
            $contactgroup = $this->contact_logic->deleteContactGroup($contactgroup_id);
            if ($contactgroup !== false) {
                $ajax_data['code'] = 0;
                $ajax_data['message'] = 'Successfully deleted contact group';
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Failed to delete contact group';
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid contact group id';
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Update Contacts in Contact Group (called via ajax)
     *
     * POST param: contactgroup_id
     * POST param: contact_id
     * POST parma: contact (array)
     *
     * @return array
     */
    public function updateContactGroupContact()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();

        if (! empty($post['contactgroup_id'])) {
            //print_rb($post['contact']);
            $contactgroup_id = $post['contactgroup_id'];
            if (! empty($post['contact_id'])) {
                $contact_id = $post['contact_id'];
                if (empty($post['contact'])) { // if the contact array is empty (i.e. the assigned contact was removed), delete the contactgroup - contact association
                    $delete = $this->contact_logic->deleteContactFromContactGroup($contact_id, $contactgroup_id);
                    if ($delete !== false) {
                        $ajax_data['code'] = 0;
                        $ajax_data['message'] = 'Removed contact from contact group';
                    } else {
                        $ajax_data['code'] = 1;
                        $ajax_data['message'] = 'Failed to remove contact from contact group';
                    }
                } else {                        // else determine if the contact needs to be added or updated
                    // check if the contact exist in this group
                    $contact = $this->contact_logic->getContactFromContactGroup($contact_id, $contactgroup_id);
                    // set parameters to be added/updated in contactgroup_contact table
                    $params = array();
                    $contact_info = $post['contact'];
                    if ((isset($contact_info['sms']) AND ($contact_info['sms'] === 'true')) AND (isset($contact_info['email']) AND ($contact_info['email'] === 'true'))) {
                        $params['method'] = 'all';
                    } else if (isset($contact_info['sms']) AND ($contact_info['sms'] === 'true')) {
                        $params['method'] = 'sms';
                    } else if (isset($contact_info['email']) AND ($contact_info['email'] === 'true')) {
                        $params['method'] = 'email';
                    }

                    if (empty($contact)) {  // if the contact doesn't exist in this group yet, it needs to be added
                        $params['contact_id'] = $contact_id;
                        $params['contactgroup_id'] = $contactgroup_id;
                        $add = $this->contact_logic->addContactToContactGroup($params);
                        if ($add !== false) {
                            $ajax_data['code'] = 0;
                            $ajax_data['message'] = 'Added contact to contact group';
                        } else {
                            $ajax_data['code'] = 1;
                            $ajax_data['message'] = 'Failed to add contact to contact group';
                        }
                    } else {                // else if it does exist, update its contact method
                        $update = $this->contact_logic->updateContactGroupContact($contact_id, $contactgroup_id, $params);
                        if ($update !== false) {
                            $ajax_data['code'] = 0;
                            $ajax_data['message'] = 'Updated contact in contact group';
                        } else {
                            $ajax_data['code'] = 1;
                            $ajax_data['message'] = 'Failed to update contact in contact group';
                        }
                    }
                }
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Invalid contact id';
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid contact group id';
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Add contact group (called via ajax)
     *
     * POST params: contact_id
     *
     * @return array
     */
    public function addContactGroup()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();

        if (! empty($post['contactgroup_name'])) {
            $contactgroup_name = $post['contactgroup_name'];
            $account_id = $this->user_session->getAccountId();
            $saved = $this->contact_logic->addContactGroup($account_id, $contactgroup_name);
            if ($saved !== false) {
                $ajax_data['code'] = 0;
                $ajax_data['message'] = 'Successfully added contact group';
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Failed to add contact group';
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid contact group name';
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Get the contact group by account id (called via ajax)
     *
     * @return void
     */
    public function getContactGroupsByAccountId()
    {
        $ajax_data          = array();

        $account_id = $this->user_session->getAccountId();
        $contactgroups = $this->contact_logic->getContactGroupsByAccountId($account_id);
        if ($contactgroups !== false) {
            $ajax_data['code'] = 0;
            $ajax_data['data']['contactgroups'] = $contactgroups;
            $ajax_data['message'] = 'Successfully retrieved contact groups';
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Failed to retrieve contact groups';
        }

        $this->ajax_respond($ajax_data);
    }
}
