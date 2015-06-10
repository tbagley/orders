<?php

namespace Models\Data;

use Models\Data\BaseData;

class ContactData extends BaseData
{
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
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
        $sql = "SELECT
                    *
                FROM
                    crossbones.contactgroup
                WHERE
                    account_id = ?
                AND
                    active != ?
                ORDER BY
                    contactgroupname ASC";

        $contact_groups = $this->db_read->fetchAll($sql, array($account_id,1));

        return $contact_groups;
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
        $sql = "SELECT
                    *,
                    CONCAT(firstname, ' ', lastname) as contactname
                FROM
                    crossbones.contact
                WHERE
                    account_id = ?
                AND
                    active != ?
                ORDER BY
                    firstname, lastname ASC";

        $contacts = $this->db_read->fetchAll($sql, array($account_id,1));

        return $contacts;
    }

    /**
     * Get the contact by contact_id
     *
     * @param int $contact_id
     *
     * @return bool
     */
    public function getContactById($contact_id)
    {
        $data = array();

        $sql = "SELECT
                    contact.*,
                    CONCAT(contact.cellnumber, '@', cellcarrier.gateway) as smsnumber,
                    cellcarrier.gateway as gateway
                FROM contact
                LEFT JOIN cellcarrier ON cellcarrier.cellcarrier_id = contact.cellcarrier_id
                WHERE contact_id = ?
                LIMIT 1";

        $data = $this->db_read->fetchAll($sql, array($contact_id));

        return $data;
    }

    /**
     * Get the contact by email
     *
     * @param string email
     *
     * @return bool
     */
    public function getContactByEmail($email, $include_user = false)
    {
        $join_user = $select_user = "";

        if ($include_user === true) {
            $select_user = ", CONCAT(user.firstname, ' ', user.lastname) AS userfullname";
            $join_user = "LEFT JOIN user ON user.user_id = contact.user_id";
        }

        $sql = "SELECT
                    contact.*{$select_user}
                FROM contact
                LEFT JOIN cellcarrier ON cellcarrier.cellcarrier_id = contact.cellcarrier_id
                {$join_user}
                WHERE contact.email = ?
                LIMIT 1";

        return $this->db_read->fetchAll($sql, array($email));
    }

    /**
     * Get the contact by the contact group id
     *
     * @param int $contactgroup_id
     *
     * @return bool
     */
    public function getContactByGroupId($contactgroup_id, $contact_method = '')
    {
        $sql_params = array($contactgroup_id);

        $where_contact_method = '';

        if (! empty($contact_method)) {
            switch ($contact_method) {
                case 'email':
                    $where_contact_method = "AND c.email != ? ";
                    $sql_params[] = '';
                    break;
                case 'sms':
                    $where_contact_method = "AND c.cellnumber != ? ";
                    $sql_params[] = '0';
                    break;
            }
        }

        $sql = "SELECT
                    cgc.*,
                    c.*,
                    cc.*,
                    CONCAT(c.firstname, ' ', c.lastname) as contactname,
                    CONCAT(c.cellnumber, '@', cc.gateway) as smsnumber
                FROM contactgroup_contact AS cgc
                INNER JOIN contact AS c ON cgc.contact_id = c.contact_id
                LEFT JOIN cellcarrier AS cc ON cc.cellcarrier_id = c.cellcarrier_id
                WHERE cgc.contactgroup_id = ? {$where_contact_method}";

        $data = $this->db_read->fetchAll($sql, $sql_params);
        return $data;
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
        $sql = $columns = $values = '';
        $sql_params = array();

        $columns = '`' . implode('`,`', array_keys($params)) . '`'; // column names
        $values = substr(str_repeat('?,', count($params)), 0, -1);  // placeholders
        $sql_params = array_values($params);                        // array of values
               
        $sql = 'INSERT INTO crossbones.contact (' . $columns . ') VALUES (' . $values . ')';
        
        // return $sql . ' : ' . implode(',',$sql_params);
        if ($this->db_read->executeQuery($sql, $sql_params)) {
            return $this->db_read->lastInsertId();
        } else {
            $this->setErrorMessage('err_database');
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
        if ($this->db_read->insert('crossbones.contactgroup_contact', $params)) {
            return true;
        }

        return false;
    }

    /**
     * Get the filtered contacts by params (string search)
     *
     * @params int account_id
     * @params array $params
     * @params array $searchfields
     *
     * @return array | bool
     */
    public function getFilteredContactsStringSearch($account_id, $params, $searchfields)
    {
        $sql_params = array($account_id);

        $where_search_string = "";
        if (isset($params['search_string']) AND $params['search_string'] != '') {

            $search_string = $params['search_string'];
            if (! empty($searchfields) AND is_array($searchfields)) {
                $where_search_string = "AND (";

                foreach ($searchfields as $key => $fieldname) {
                    $where_search_string .= "`".$fieldname."` LIKE ? OR ";
                    $sql_params[] = '%'.str_replace("_", "\_", $search_string).'%';
                }

                $where_search_string = substr($where_search_string, 0, -4);
        		$where_search_string .= ")";
            }
        }

        $sql = "SELECT
                    contact.*,
                    CONCAT(contact.firstname, ' ', contact.lastname) as contactname,
                    contactgroup.*,
                    contactgroup_contact.method as method,
                    cellcarrier.*
                FROM contact
                LEFT JOIN contactgroup_contact ON contactgroup_contact.contact_id = contact.contact_id
                LEFT JOIN contactgroup ON contactgroup.contactgroup_id = contactgroup_contact.contactgroup_id
                LEFT JOIN cellcarrier ON cellcarrier.cellcarrier_id = contact.cellcarrier_id
                WHERE contact.account_id = ? {$where_search_string}
                ORDER BY contactname ASC";

        $contacts = $this->db_read->fetchAll($sql, $sql_params);
        return $contacts;
    }

    /**
     * Get the filtered contacts by $params
     *
     * @params: int account_id
     * @params: array $params
     *
     * @return array | bool
     */
    public function getFilteredContacts($account_id, $params)
    {
        $sql_params = array($account_id);

        $where_contact_groups = "";
        if (isset($params['contactgroup_id']) AND ! empty($params['contactgroup_id'])) {
            $where_contact_groups = " AND contactgroup.contactgroup_id IN (" . substr(str_repeat('?,', count($params['contactgroup_id'])), 0, -1) . ") ";
            $sql_params = array_merge($sql_params, array_values($params['contactgroup_id']));
        }

        $where_contact_type = "";
        if (isset($params['contact_type']) AND $params['contact_type'] != '') {
            switch ($params['contact_type']) {
                case 'email':
                    $where_contact_type = " AND contact.email != ? ";
                    $sql_params[] = '';
                    break;
                case 'sms':
                    $where_contact_type = " AND contact.cellnumber != ? ";
                    $sql_params[] = '0';
                    break;
            }
        }

        $sql = "SELECT
                    contact.*,
                    CONCAT(contact.firstname, ' ', contact.lastname) as contactname,
                    contactgroup.*,
                    contactgroup_contact.method as method,
                    cellcarrier.*
                FROM contact
                LEFT JOIN contactgroup_contact ON contactgroup_contact.contact_id = contact.contact_id
                LEFT JOIN contactgroup ON contactgroup.contactgroup_id = contactgroup_contact.contactgroup_id
                LEFT JOIN cellcarrier ON cellcarrier.cellcarrier_id = contact.cellcarrier_id
                WHERE contact.account_id = ? {$where_contact_groups}{$where_contact_type}
                ORDER BY contactname ASC";

        $contacts = $this->db_read->fetchAll($sql, $sql_params);
        return $contacts;
    }

    /**
     * Get the filtered contact groups by params (string search)
     *
     * @params int account_id
     * @params array $params
     * @params array $searchfields
     *
     * @return array | bool
     */
    public function getFilteredContactGroupsStringSearch($account_id, $params, $searchfields)
    {
        $sql_params = array($account_id);

        $where_search_string = "";
        if (isset($params['search_string']) AND $params['search_string'] != '') {

            $search_string = $params['search_string'];
            if (! empty($searchfields) AND is_array($searchfields)) {
                $where_search_string = "AND (";

                foreach ($searchfields as $key => $fieldname) {
                    $where_search_string .= "`".$fieldname."` LIKE ? OR ";
                    $sql_params[] = '%'.str_replace("_", "\_", $search_string).'%';
                }

                $where_search_string = substr($where_search_string, 0, -4);
        		$where_search_string .= ")";
            }
        }

        $sql = "SELECT
                    contactgroup.*
                FROM contactgroup
                WHERE contactgroup.account_id = ? {$where_search_string}
                ORDER BY contactgroupname ASC";

        $contactgroups = $this->db_read->fetchAll($sql, $sql_params);
        return $contactgroups;
    }

    /**
     * Get sms carrier options
     *
     * @return array
     */
    public function getSMSCarrierOptions()
    {
        $sql = "SELECT *
                FROM cellcarrier";

        $carriers = $this->db_read->fetchAll($sql);
        return $carriers;
    }

    /**
     * Update contact info
     *
     * @param int contact_id
     * @oaram array params
     *
     * @return bool
     */
    public function updateContactInfo($contact_id, $params)
    {
        if ($this->db_read->update('crossbones.contact', $params, array('contact_id' => $contact_id))) {
            return true;
        }
        return false;
    }

    /**
     * Update contact group info
     *
     * @param int contactgroup_id
     * @oaram array params
     *
     * @return bool
     */
    public function updateContactGroupInfo($contactgroup_id, $params)
    {
        if ($this->db_read->update('crossbones.contactgroup', $params, array('contactgroup_id' => $contactgroup_id))) {
            return true;
        }
        return false;
    }

    /**
     * Delete contact
     *
     * @param int contact_id
     *
     * @return bool
     */
    public function deleteContact($contact_id)
    {
        if ($this->db_read->delete('crossbones.contact', array('contact_id' => $contact_id))) {
            $this->db_read->delete('crossbones.contactgroup_contact', array('contact_id' => $contact_id));
            $this->db_read->delete('crossbones.alert_contact', array('contact_id' => $contact_id));
            return true;
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
        if ($this->db_read->delete('crossbones.contactgroup', array('contactgroup_id' => $contactgroup_id))) {
            $this->db_read->delete('crossbones.contactgroup_contact', array('contactgroup_id' => $contactgroup_id));
            $this->db_read->delete('crossbones.alert_contact', array('contactgroup_id' => $contactgroup_id));
            return true;
        }
        return false;
    }

    /**
     * Get contact group by name
     *
     * @param int account_id
     * @param string groupname
     *
     * @return array|bool
     */
    public function getContactGroupByName($account_id, $groupname)
    {
        $sql = "SELECT *
                FROM crossbones.contactgroup
                WHERE account_id = ?
                AND contactgroupname = ?
                AND active = ?
                LIMIT 1";

        $group = $this->db_read->fetchAll($sql, array($account_id,$groupname,0));

        return ($group ? $group : false);
    }

    /**
     * Get the contactgroup by contactgroup_id
     *
     * @param int $contactgroup_id
     *
     * @return array|bool
     */
    public function getContactGroupById($contactgroup_id)
    {
        $sql = "SELECT *
                FROM contactgroup
                WHERE contactgroup_id = ?
                LIMIT 1";

        $data = $this->db_read->fetchAll($sql, array($contactgroup_id));

        return $data;
    }

    /**
     * Add contact group to account
     *
     * @param array params
     *
     * @return array|bool
     */
    public function addContactGroup($params)
    {
        // if ($this->db_read->insert('crossbones.contactgroup', $params)) {
        //     return $this->db_read->lastInsertId();
        // }

        $sql = $columns = $values = '';
        $sql_params = array();

        $columns = '`' . implode('`,`', array_keys($params)) . '`'; // column names
        $values = substr(str_repeat('?,', count($params)), 0, -1);  // placeholders
        $sql_params = array_values($params);  // array of values
               
        $sql = 'INSERT INTO crossbones.contactgroup (' . $columns . ') VALUES (' . $values . ')';
        
        // return $sql . ' : ' . implode(',',$sql_params);
        if ($this->db_read->executeQuery($sql, $sql_params)) {
            return $this->db_read->lastInsertId();
        } else {
            $this->setErrorMessage('err_database');
        }
                    
        return false;
    }

    /**
     * Get contact by name
     *
     * @param int account_id
     * @param string firstname
     * @param string lastname
     *
     * @return array|bool
     */
    public function getContactByName($account_id, $firstname, $lastname)
    {
        $sql = "SELECT *
                FROM crossbones.contact
                WHERE account_id = ? AND firstname = ? AND lastname = ?
                LIMIT 1";

        $group = $this->db_read->fetchAll($sql, array($account_id, $firstname, $lastname));

        return ($group ? $group : false);
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
        $delete = $this->db_read->delete('crossbones.contactgroup_contact', array('contact_id' => $contact_id, 'contactgroup_id' => $contactgroup_id));
        if ($delete !== false) {
            return true;
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
        $sql = "SELECT cgc.*,
                       c.*,
                       cc.*
                FROM crossbones.contactgroup_contact AS cgc
                LEFT JOIN crossbones.contact AS c ON c.contact_id = cgc.contact_id
                LEFT JOIN crossbones.cellcarrier AS cc ON cc.cellcarrier_id = c.cellcarrier_id
                WHERE cgc.contact_id = ? AND cgc.contactgroup_id = ?
                LIMIT 1";

        return $this->db_read->fetchAll($sql, array($contact_id, $contactgroup_id));
    }

    /**
     * Update contact method in contactgroup_contact table
     *
     * @param int contact_id
     * @param int contactgroup_id
     *
     * @return bool|int
     */
    public function updateContactGroupContact($contact_id, $contactgroup_id, $params)
    {
        return $this->db_write->update('crossbones.contactgroup_contact', $params, array('contact_id' => $contact_id, 'contactgroup_id' => $contactgroup_id));
    }

    /**
     * Get contact by user id
     *
     * @param int    user_id
     * @return array
     */
    public function getContactByUserId($user_id)
    {
        $sql = "SELECT *
                FROM contact
                WHERE user_id = ?
                LIMIT 1";

        return $this->db_read->fetchAll($sql, array($user_id));
    }
}
