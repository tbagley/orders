<?php

namespace Models\Data;

use Models\Data\BaseData;
use GTC\Component\Utils\Date;

class UserData extends BaseData
{
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     */
    public function getLegalId($user_id)
    {
        $results = array();

        $sql = "SELECT a.account_id,
                a.legal_id,
                a.legal_agreement,
                a.accountname as account_name,
                u.firstname as user_first,
                u.lastname as user_last,
                u.username as user_name,
                u.email as user_email,
                u.roles as user_roles,
                ut.usertype as usertype,
                ut.usertype_id as usertype_id
                FROM crossbones.account a 
                LEFT JOIN crossbones.user u ON u.account_id = a.account_id
                LEFT JOIN crossbones.usertype ut ON ut.usertype_id = u.usertype_id
                WHERE u.user_id = ?";

        $results['account'] = $this->db_read->fetchAll($sql, array($user_id));

        $sql = "SELECT *
                FROM crossbones.legal
                WHERE active != '1'
                ORDER BY legal_id DESC
                LIMIT 1";

        $results['legal'] = $this->db_read->fetchAll($sql, array());

        return $results;
    }

    /**
     */
    public function setLegalId($user_id,$legal_id)
    {
        $sql = "SELECT a.account_id,
                a.legal_id,
                a.legal_agreement,
                u.usertype_id,
                u.roles
                FROM crossbones.account a 
                LEFT JOIN crossbones.user u ON u.account_id = a.account_id
                WHERE u.user_id = ?";

        $res = $this->db_read->fetchAll($sql, array($user_id));

        $res[0]['user_id'] = $user_id ;

        if(($legal_id)&&($res[0]['account_id'])&&(($res[0]['roles']=='ROLE_ACCOUNT_OWNER')||($res[0]['usertype_id']==1))){

            $sql = "UPDATE crossbones.account
                    SET legal_id = ? ,
                    legal_user = ? ,
                    legal_agreement = now()
                    WHERE account_id = ? ";

            if ($this->db_read->executeQuery($sql, array( $legal_id , $user_id , $res[0]['account_id'] ))){

                $sql = "SELECT a.account_id,
                        a.legal_id,
                        a.legal_agreement,
                        u.usertype_id,
                        u.roles
                        FROM crossbones.account a 
                        LEFT JOIN crossbones.user u ON u.account_id = a.account_id
                        WHERE u.user_id = ?";

                $res = $this->db_read->fetchAll($sql, array($user_id));

            }
            
        }

        return $res;
    }

    /**
     */
    public function umCheckAccountNameExist($account_name)
    {
        $results = array();

        $sql = "SELECT *
                FROM crossbones.account
                WHERE accountname = ?";

        $results = $this->db_read->fetchAll($sql, array($account_name));

        return $results;
    }

    /**
     * @param $user_id
     * @return mixed
     */
    public function umUserDetail($user_id)
    {
        $results = array();

        $sql = "
            SELECT
                user.*,
                userstatus.userstatusname
            FROM
                unitmanagement.user AS user
                LEFT JOIN unitmanagement.userstatus AS userstatus ON userstatus.userstatus_id = user.userstatus_id
            WHERE
                `user_id` = ?
        ";

        if (isset($this->db_read)) {
            $results = $this->db_read->fetchAssoc($sql, array($user_id));
        }

        return $results;
    }

    /**
     */
    public function umUserNameTaken($account_username)
    {
        $results = array();

        $sql = "SELECT *
                FROM crossbones.user
                WHERE username = ?";

        $results = $this->db_read->fetchAll($sql, array($account_username));

        return $results;
    }

    /**
     * Support Ajax User Create
     */
    public function ajaxEmailCheck($email)
    {
        if($email){
            $sql = "SELECT * FROM crossbones.user WHERE email = ? AND userstatus_id > 0 AND userstatus_id < 4" ;
            $result = $this->db_read->fetchAll($sql, array($email));
        } else {
            $result['error'] = 'Email Address is Missing' ;
        }
        return $result;
    }

    /**
     * Support Ajax User Create
     */
    public function ajaxUserCheck($username)
    {
        if($username){
            $sql = "SELECT * 
                    FROM crossbones.user 
                    WHERE username = ?
                    AND userstatus_id > 0
                    AND userstatus_id < 4" ;
            $results = $this->db_read->fetchAll($sql, array($username));
            if($results[0]['username']){
                $result['username'] = $results[0]['username'] ;
                $result['user_id'] = $results[0]['user_id'] ;
            }
        } else {
            $result['error'] = 'User Name is Missing' ;
        }
        return $result;
    }

    /**
     * Support Ajax User Create
     */
    public function ajaxUserCreate($user,$post)
    {

        $save_params = array(
            'account_id'        => $user['account_id'],
            'userstatus_id'     => 1,
            'usertype_id'       => $post['usertype_id'],
            'timezone_id'       => $user['timezone_id'],
            'roles'             => 'ROLE_ACCOUNT_USER',
            'firstname'         => $post['firstname'],
            'lastname'          => $post['lastname'],
            'email'             => $post['email'],
            'username'          => $post['username'],
            'password'          => $post['password']
        );
        
        $columns = '`' . implode('`,`', array_keys($save_params)) . '`';

        $values = substr(str_repeat('?,', count($save_params)), 0, -1);
        
        $sqlPlaceHolder = array_values($save_params);
        
        $sql = "INSERT INTO `crossbones`.`user` ({$columns}) VALUES ({$values})";

        if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) {

            $sql = "SELECT * FROM crossbones.user WHERE username = ? AND userstatus_id > 0 AND userstatus_id < 4" ;
            $result = $this->db_read->fetchAll($sql, array($post['username']));

            if ($result[0]['user_id']) {

                $ymd = date ( 'Y-m-d' ) ;

                $save_params2 = array(
                    'account_id'        => $user['account_id'],
                    'user_id'           => $result[0]['user_id'],
                    'cellcarrier_id'    => $post['cellcarrier_id'],
                    'firstname'         => $post['firstname'],
                    'lastname'          => $post['lastname'],
                    'email'             => $post['email'],
                    'cellnumber'        => $post['cellnumber'],
                    'createdate'        => $ymd,
                    'contactstatus'     => 'active'
                );
                
                $columns = '`' . implode('`,`', array_keys($save_params2)) . '`';

                $values = substr(str_repeat('?,', count($save_params2)), 0, -1);
                
                $sqlPlaceHolder = array_values($save_params2);
                
                $sql = "INSERT INTO `crossbones`.`contact` ({$columns}) VALUES ({$values})";

                if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) {

                    return $sql ;
        
                }

            }

            return $sql ;

        }
       
        return false;

    }

    /**
     * Support Ajax User Create
     */
    public function ajaxUserTypeCheck($usertype)
    {
        if($usertype){
            $sql = "SELECT * FROM crossbones.usertype WHERE usertype = ? AND active IS NOT NULL" ;
            $result = $this->db_read->fetchAll($sql, array($usertype));
        } else {
            $result['error'] = 'User Type Name is Missing' ;
        }
        return $result;
    }

    /**
     * Support Ajax User Type Create
     */
    public function ajaxUserTypeCreate($user,$post)
    {

        $params = array(
            'account_id'        => $user['account_id'],
            'usertype'          => $post['usertype'],
            'active'            => '1',
            'canned'            => '0'
        );
        
        $columns = '`' . implode('`,`', array_keys($params)) . '`';

        $values = substr(str_repeat('?,', count($params)), 0, -1);
        
        $sqlPlaceHolder = array_values($params);
        
        $sql = "INSERT INTO `crossbones`.`usertype` ({$columns}) VALUES ({$values})";

        if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) {

            $sql = "SELECT * FROM crossbones.usertype WHERE account_id = ? AND active = '1' ORDER BY createdate DESC LIMIT 1" ;

            $res = $this->db_read->fetchAll($sql, array($user['account_id']));

            $usertype_id = $res[0]['usertype_id'] ;

            if ($usertype_id) {

                $sql = 'SUCCESS: ' . $usertype_id ;

                $params = array(
                    'usertype_id'          => $usertype_id,
                    'createdate'            => date('Y-m-d H:i:s')
                );

                if($post['p1']=='true'){$params['permission_id'] = 1; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p2']=='true'){$params['permission_id'] = 2; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p3']=='true'){$params['permission_id'] = 3; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p4']=='true'){$params['permission_id'] = 4; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p5']=='true'){$params['permission_id'] = 5; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p6']=='true'){$params['permission_id'] = 6; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p7']=='true'){$params['permission_id'] = 7; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p8']=='true'){$params['permission_id'] = 8; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p9']=='true'){$params['permission_id'] = 9; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p10']=='true'){$params['permission_id'] = 10; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p11']=='true'){$params['permission_id'] = 11; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p12']=='true'){$params['permission_id'] = 12; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p13']=='true'){$params['permission_id'] = 13; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p14']=='true'){$params['permission_id'] = 14; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p15']=='true'){$params['permission_id'] = 15; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p16']=='true'){$params['permission_id'] = 16; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p17']=='true'){$params['permission_id'] = 17; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p18']=='true'){$params['permission_id'] = 18; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p19']=='true'){$params['permission_id'] = 19; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p20']=='true'){$params['permission_id'] = 20; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p21']=='true'){$params['permission_id'] = 21; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p22']=='true'){$params['permission_id'] = 22; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }
                if($post['p23']=='true'){$params['permission_id'] = 23; $columns = '`' . implode('`,`', array_keys($params)) . '`'; $values = substr(str_repeat('?,', count($params)), 0, -1); $sqlPlaceHolder = array_values($params); $sql = "INSERT INTO `crossbones`.`usertype_permission` ({$columns}) VALUES ({$values})"; if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) { $sql = 'SWITCH' ; } }

            }

            return $sql . ' : ' . implode ( ', ' , $params ) . ' : $usertype_id="' . $usertype_id . '", $post[p1]="' . $post['p1'] . '"' ;

        }
       
        return false;

    }

    /**
     * Support Ajax User Type Update
     */
    public function ajaxUserTypeUpdate($user,$post)
    {

        $sql = "UPDATE crossbones.usertype SET usertype = ? WHERE usertype_id = ? AND account_id = ? ";

        if ($this->db_read->executeQuery($sql, array( $post['usertype'] , $post['usertype_id'] , $user['account_id'] ))){

            return $sql;

        }
       
        return false;

    }

    /**
     * Support Ajax Calls
     */
    public function ajaxUpdate($user,$unit_id,$field,$value)
    {

        switch ($field) {

            case            'vehicle-vin' : $sql = "SELECT
                                                        u.*,
                                                        ua.unitattribute_id AS unitattribute_id,
                                                        ua.vin AS vin,
                                                        ua.make AS make,
                                                        ua.model AS model,
                                                        ua.year AS year,
                                                        ua.color AS color,
                                                        ua.licenseplatenumber AS licenseplatenumber,
                                                        ua.loannumber AS loannumber,
                                                        ua.plan AS plan,
                                                        ua.purchasedate AS purchasedate,
                                                        ua.renewaldate AS renewaldate,
                                                        ua.lastrenewaldate AS lastrenewaldate
                                                    FROM crossbones.unit u
                                                    LEFT JOIN crossbones.unitattribute ua ON u.unit_id = ua.unit_id
                                                    WHERE u.unit_id = '" . $unit_id . "'";

                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = '1735'" ;
                                            $results = $this->db_read->fetchAll($sql, array());
                                            
                                            $result = '#sql=' . $sql . '#vin=' . $results['vin'] . '#unit_id='  . $unit_id . '#account_id='  . $user['account_id'] . '#' ;
                                            break;

                                  default : $result = 'ajaxUpdate:no action:' . $field . '="' . $value . '"' ;
        }

        return $result;

    }

    /**
     * Get all user groups/types
     *
     *
     * @return array
     */
    public function getCreateDate($user_id)
    {

        $sql = "SELECT a.createdate as createdate_account,
                u.createdate as createdate_user
                FROM crossbones.user u
                LEFT JOIN crossbones.account a ON a.account_id = u.account_id
                WHERE u.user_id = ?
                AND a.accountstatus_id = '1'
                ORDER BY a.createdate ASC
                LIMIT 1";

        $createdate = $this->db_read->fetchAll($sql, array($user_id));

        return $createdate[0]; 

    }

    /**
     * Get all user groups/types
     *
     *
     * @return array
     */
    public function getUserTypesByAccountId($account_id)
    {
        $usertypes = array();

        $sql = "SELECT *
                FROM usertype
                WHERE account_id = ? OR canned = 1
                ORDER BY canned DESC, usertype ASC";

        $usertypes = $this->db_read->fetchAll($sql, array($account_id));
        return $usertypes; 
    }

    /**
     * Get all users
     *
     *
     * @return array
     */
    public function getUsersByAccountId($account_id)
    {
        $users = array();

        $sql = "SELECT u.*,
                CONCAT(u.firstname, ' ', u.lastname) as user_fullname
                FROM user u
                WHERE u.account_id = ?
                AND u.userstatus_id < 4
                AND u.userstatus_id > 0
                ORDER BY u.firstname ASC, u.lastname ASC";

        $users = $this->db_read->fetchAll($sql, array($account_id));
        return $users; 
    }

    /**
     * Get all permission category
     *
     * @return array|bool
     */
    public function getPermissionCategory()
    {
        $permissions_category = array();

        $sql = "SELECT *
                FROM permissioncategory
                ORDER BY sortorder, permissioncategoryname";

        return $this->db_read->fetchAll($sql);
    }

    /**
     * Get all permissions by category id
     *
     * @param int $permissioncategory_id
     *
     * @return array|bool
     */
    public function getPermissionByCategoryId($permissioncategory_id)
    {
        $permissions = array();

        $sql = "SELECT *
                FROM permission
                WHERE permissioncategory_id = ? 
                ORDER BY sortorder, object, label";

        $permissions = $this->db_read->fetchAll($sql, array($permissioncategory_id));
        return $permissions; 
    }

    /**
     * Get all permissions for a user
     *
     * @return array
     */
    public function getPermissions($account_id,$user_id)
    {
        $permissions = array();

        if (($account_id)&&($user_id)){
            $sql = "SELECT p.label,
                    p.object,
                    p.action,
                    u.roles
                    FROM crossbones.user u 
                    LEFT JOIN crossbones.usertype ut ON ut.usertype_id = u.usertype_id
                    LEFT JOIN crossbones.usertype_permission utp ON utp.usertype_id = u.usertype_id
                    LEFT JOIN crossbones.permission p ON p.permission_id = utp.permission_id
                    WHERE u.account_id = ?
                    AND u.user_id = ?
                    AND ut.active = ? 
                    ORDER BY p.sortorder, p.object, p.label";
            $permissions = $this->db_read->fetchAll($sql, array($account_id,$user_id,1));
        } else {
            $sql = "SELECT * FROM crossbones.permission WHERE permission_id IS NOT NULL";
            $permissions = $this->db_read->fetchAll($sql, array());
        }

        return $permissions ;

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
    public function getFilteredUsersStringSearch($account_id, $params, $searchfields)
    {
        $sqlPlaceHolder = array($account_id);

        $where_search_string = "";
        if (isset($params['search_string']) AND $params['search_string'] != '') {

            $search_string = $params['search_string'];
            if (! empty($searchfields) AND is_array($searchfields)) {
                $where_search_string = "AND (";

                foreach ($searchfields as $key => $fieldname) {
                    $where_search_string .= $fieldname." LIKE ? OR ";
                    $sqlPlaceHolder[] = '%'.str_replace("_", "\_", $search_string).'%';
                }

                $where_search_string = substr($where_search_string, 0, -4);
        		$where_search_string .= ")";
            }
        }

        $sql = "SELECT 
                    user.*,
                    CONCAT(user.firstname, ' ', user.lastname) as user_fullname,
                    us.*,
                    ut.*,
                    c.cellnumber AS cellnumber
                FROM user
                INNER JOIN userstatus AS us ON us.userstatus_id = user.userstatus_id
                LEFT JOIN usertype ut ON user.usertype_id = ut.usertype_id
                LEFT JOIN contact c ON user.user_id = c.user_id
                WHERE user.account_id = ? AND user.roles != 'ROLE_ACCOUNT_OWNER' AND user.userstatus_id > 0 AND user.userstatus_id < 4 {$where_search_string}
                ORDER BY user.firstname, user.lastname";

        $users = $this->db_read->fetchAll($sql, $sqlPlaceHolder);
        return $users;        
    }
    
    /**
     * Get the filtered contacts by $params
     *
     * @params: int account_id
     * @params: array $params
     *
     * @return array | bool
     */    
    public function getFilteredUsers($account_id, $params)
    {
        $sqlPlaceHolder = array($account_id);

        $where_type_in = "";
        if (isset($params['usertype_id']) AND ! empty($params['usertype_id'])) {
            $where_type_in = "AND user.usertype_id IN (" . substr(str_repeat('?,', count($params['usertype_id'])), 0, -1) . ") ";
            $sqlPlaceHolder = array_merge($sqlPlaceHolder, array_values($params['usertype_id']));
        }

        $where_status_in = "";
        if (isset($params['userstatus_id']) AND ! empty($params['userstatus_id'])) {
            $where_status_in = "AND user.userstatus_id IN (" . substr(str_repeat('?,', count($params['userstatus_id'])), 0, -1) . ") ";
            $sqlPlaceHolder = array_merge($sqlPlaceHolder, array_values($params['userstatus_id']));
        }

        $sql = "SELECT 
                    user.*,
                    CONCAT(user.firstname, ' ', user.lastname) AS user_fullname,
                    us.*,
                    ut.*,
                    c.cellnumber AS cellnumber
                FROM user
                INNER JOIN userstatus AS us ON us.userstatus_id = user.userstatus_id
                LEFT JOIN usertype ut ON user.usertype_id = ut.usertype_id
                LEFT JOIN contact c ON user.user_id = c.user_id
                WHERE user.account_id = ? AND user.roles != 'ROLE_ACCOUNT_OWNER' AND user.userstatus_id > 0 AND user.userstatus_id < 4 {$where_type_in} {$where_status_in}
                ORDER BY user.firstname, user.lastname";

        $users = $this->db_read->fetchAll($sql, $sqlPlaceHolder);
        return $users;        
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
    public function getFilteredUserTypeStringSearch($account_id, $params, $searchfields)
    {
        $sqlPlaceHolder = array($account_id);

        $where_search_string = "";
        if (isset($params['search_string']) AND $params['search_string'] != '') {

            $search_string = $params['search_string'];
            if (! empty($searchfields) AND is_array($searchfields)) {
                $where_search_string = "AND (";
        		
                foreach ($searchfields as $key => $fieldname) {
                    $where_search_string .= "`".$fieldname."` LIKE ? OR ";
                    $sqlPlaceHolder[] = '%'.str_replace("_", "\_", $search_string).'%';
                }

                $where_search_string = substr($where_search_string, 0, -4);
        		$where_search_string .= ")";
            }
        }

        // $sql = "SELECT 
        //             usertype.*, count(u.user_id) as total_count,
        //             IF(usertype.canned = '0', 'yes', 'no') as editable
        //         FROM usertype
        //         LEFT JOIN `user` u ON usertype.usertype_id = u.usertype_id AND u.userstatus_id != 0
        //         WHERE (u.account_id=$account_id) {$where_search_string}
        //         GROUP BY usertype.usertype_id
        //         ORDER BY usertype.usertype, usertype.canned DESC";
                
        $sql = "SELECT 
                    usertype.*, count(u.user_id) as total_count,
                    IF(usertype.canned = '0', 'yes', 'no') as editable
                FROM usertype
                LEFT JOIN `user` u ON usertype.usertype_id = u.usertype_id AND u.userstatus_id > 0 AND u.userstatus_id < 4
                WHERE (usertype.account_id=$account_id) {$where_search_string}
                GROUP BY usertype.usertype_id
                ORDER BY usertype.usertype, usertype.canned DESC";
                
        $types = $this->db_read->fetchAll($sql, $sqlPlaceHolder);
        return $types;        
    }
    
    /**
     * Assign user to vehicle group
     *
     * @param array params
     */
    public function addUserToVehicleGroup($user_id,$vg)
    {
        return $this->db_write->insert('crossbones.user_unitgroup', array('user_id' => $user_id, 'unitgroup_id' => $vg));    
    }


    /**
     * Add user to the account
     *
     * @param array params
     *
     * @return bool|int
     */
    public function addUser($params) 
    {
        if ($this->db_write->insert('crossbones.user', $params) !== false) {
            return $this->db_write->lastInsertId();
        }
        return false;
    }

    /**
     * Update user info
     *
     * @param int   user_id
     * @param array params
     *
     * @return bool
     */
    public function updateUserInfo($user_id, $params) 
    {
        if ($this->db_write->update('crossbones.user', $params, array('user_id' => $user_id)) !== false) {
            return true;
        }
        return false;
    }

    /**
     * Delete user
     *
     * @param int   user_id
     *
     * @return bool
     */
    public function deleteUser($user_id) 
    {
        if ($this->db_write->update('crossbones.user', array('userstatus_id' => 0), array('user_id' => $user_id)) !== false) {
            // maybe deactivate user's unit/landmark groups too (groups are currently associated to user, not account)
            return true;
        }
        return false;
    }

    /**
     * Get the user by user_id
     *
     * @param int user_id
     *
     * @return bool|array
     */
    public function getUserById($user_id)
    {
        $sql = "SELECT 
                    user.*,
                    CONCAT(user.firstname, ' ', user.lastname) AS fullname,
                    userstatus.*,
                    usertype.usertype AS usertype,
                    usertype.canned AS canned,
                    c.contact_id,
                    c.cellnumber,
                    c.cellcarrier_id
                FROM user
                LEFT JOIN userstatus ON userstatus.userstatus_id = user.userstatus_id
                LEFT JOIN usertype ON usertype.usertype_id = user.usertype_id
                LEFT JOIN contact c ON c.user_id = user.user_id
                WHERE user.user_id = ?
                LIMIT 1";

        return $this->db_read->fetchAll($sql, array($user_id));        
    }

    /**
     * Get the usertype by usertype_id
     *
     * @param int usertype_id
     *
     * @return bool|array
     */
    public function getUserTypeById($usertype_id)
    {
        $sql = "SELECT 
                    *
                FROM usertype
                WHERE usertype_id = ?
                LIMIT 1";

        return $this->db_read->fetchAll($sql, array($usertype_id));        
    }


    /**
     * Get the permissions for user_id (should be usertype_id)
     *
     * @param int usertype_id
     *
     * @return bool|array
     */
    public function getUserTypePermissionsById($user_id)
    {
        $sql = "SELECT 
                    p.*,
                    pc.*
                FROM `usertype_permission` utp
                LEFT JOIN `permission` p ON utp.permission_id = p.permission_id
                LEFT JOIN `permissioncategory` pc ON p.permissioncategory_id = pc.permissioncategory_id
                WHERE utp.usertype_id = ?
                ORDER BY pc.permissioncategoryname ASC";

        return $this->db_read->fetchAll($sql, array($user_id));
    }

    /**
     * Add usertype to the account
     *
     * @param array params
     *
     * @return bool|int
     */
    public function addUserType($params) 
    {
        if ($this->db_write->insert('crossbones.usertype', $params) !== false) {
            return $this->db_write->lastInsertId();
        }
        return false;
    }

    /**
     * Delete usertype
     *
     * @param int   account_id
     * @param int   usertype_id
     *
     * @return bool
     */
    public function deleteUserType($account_id, $usertype_id) 
    {
        return $this->db_write->delete('crossbones.usertype', array('account_id' => $account_id,'usertype_id' => $usertype_id));
    }

    /**
     * Update usertype info
     *
     * @param int   usertype_id
     * @param array params
     *
     * @return bool
     */
    public function updateUserTypeInfo($usertype_id, $params) 
    {
        if ($this->db_write->update('crossbones.usertype', $params, array('usertype_id' => $usertype_id)) !== false) {
            return true;
        }
        return false;
    }

    /**
     * Check if usertype exist
     *
     * @param string $usertype
     * @param int $account_id
     *
     * @return bool|array
     */    
    public function checkUserTypeExist($account_id, $usertype)
    {
        $sql = "SELECT 
                    *
                FROM usertype
                WHERE account_id = ? AND usertype = ?";

        $types = $this->db_read->fetchAll($sql, array($account_id, $usertype));
        return $types;        
    }

    /**
     * Check if usertype is associated to an active user
     *
     * @param int $usertype_id
     *
     * @return bool
     */    
    public function checkUserTypeUserAssociationExist($usertype_id)
    {
        $sql = "SELECT 
                    *
                FROM user
                WHERE usertype_id = ? 
                AND userstatus_id > 0 
                AND userstatus_id < 4
                LIMIT 1";

        $result = $this->db_read->fetchAll($sql, array($usertype_id));
        if ($result !== false AND empty($result)) {
            // association not found, return false
            return false;
        }

        // association exist, return true
        return true;
    }

    /*
     * Get usertype options for account
     *
     * @param int $account_id
     *
     * @return void
     */
    public function getUserTypeOptions($account_id)
    {
        $sql = "SELECT 
                    *
                FROM usertype
                WHERE account_id = ? OR canned = 1";

        $types = $this->db_read->fetchAll($sql, array($account_id));
        return $types; 
    }

    /**
     * Add usertype permission to the usertype
     *
     * @param array params
     *
     * @return bool|int
     */
    public function addUserTypePermission($params) 
    {
        if ($this->db_write->insert('crossbones.usertype_permission', $params) !== false) {
            return $this->db_write->lastInsertId();
        }
        return false;
    }

    /**
     * Insert/Update on Duplicate usertype_permission relation
     *
     * @params array params
     *
     * @return bool
     */
    function insertUserTypePermission($params)
    {
        $columns = $update = $values = '';
        $values_arr = array();

        foreach ($params as $col => $value) {
            $columns .= $col.',';
            $values .= '?,';
            $update .= $col . ' = ?,';
            $values_arr[] = $value;
        }

        $columns = rtrim($columns, ',');
        $values = rtrim($values, ',');
        $update = rtrim($update, ',');

        $sql_params = array_merge($values_arr, $values_arr);

        $sql = "INSERT INTO crossbones.usertype_permission ({$columns}) 
                VALUES ({$values}) 
                ON DUPLICATE KEY UPDATE {$update}";

        if ($this->db_read->executeQuery($sql, $sql_params)) {
            return true;
        }

        return false;
    } 

    /**
     * Get locked permission
     *
     * @return bool|array
     */
    public function getLockedPermissions() 
    {
        $sql = "SELECT 
                    *
                FROM permission
                WHERE locked = ?";

        return $this->db_read->fetchAll($sql, array(1));
    }

    /**
     * Get Usertype permission
     *
     * @param int   $usertype_id, int $permission_id
     *
     * @return bool|array
     */
    public function getUserTypePermission($params) 
    {
        $sql = "SELECT 
                    *
                FROM usertype_permission
                WHERE usertype_id = ? AND permission_id = ?";

        return $this->db_read->fetchAll($sql, array($params['usertype_id'], $params['permission_id']));
    }

    /**
     * Remove usertype_permission for usertype_id
     *
     * @param int usertype_id
     *
     * @return bool|int
     */
    public function deleteUserTypePermission($usertype_id) 
    {
        return $this->db_write->delete('crossbones.usertype_permission', array('usertype_id' => $usertype_id));
    }

    /**
     * Remove usertype to the account
     *
     * @param array params
     *
     * @return bool|int
     */
    public function removeUserTypePermission($params) 
    {
        return $this->db_write->delete('crossbones.usertype_permission', array('usertype_id' => $params['usertype_id'], 'permission_id' => $params['permission_id']));
    }
        
    /**
     * Get account by id
     *
     * @param int account_id
     *
     * @return bool|array
     */
    public function getAccountInfo($account_id)
    {
        $sql = "SELECT 
                    *
                FROM account
                WHERE account_id = ?
                LIMIT 1";

        return $this->db_read->fetchAll($sql, array($account_id));        
    }

    /**
     * Get user by email
     *
     * @param string email
     *
     * @return bool|array
     */
    public function getUserByEmail($email)
    {
        $sql = "SELECT 
                    *
                FROM user
                WHERE email = ?
                LIMIT 1";

        return $this->db_read->fetchAll($sql, array($email));        
    }
    
    /**
     * Add user invitation
     *
     * @param int user_id
     * @param string token
     * @param datetime expiration_date
     *
     * @return bool|int
     */
    public function addUserInvitation($user_id, $token, $expiration_date)
    {
        return $this->db_write->insert('crossbones.userinvitation', array('user_id' => $user_id, 'token' => $token, 'expiredate' => $expiration_date));    
    }
    
    /**
     * Get user by invitation token
     *
     * @param string token
     *
     * @return bool|int
     */
    public function getUserByInvitationToken($token)
    {
        $sql = "SELECT ui.*,
                       u.*,
                       us.userstatusname as userstatusname,
                       tz.*,
                       ut.usertype AS usertype,
                       ut.canned AS canned
                FROM crossbones.userinvitation AS ui
                LEFT JOIN crossbones.user AS u ON u.user_id = ui.user_id
                LEFT JOIN crossbones.userstatus AS us ON us.userstatus_id = u.userstatus_id
                LEFT JOIN unitmanagement.timezone AS tz on tz.timezone_id = u.timezone_id
                LEFT JOIN crossbones.usertype AS ut ON ut.usertype_id = u.usertype_id
                WHERE ui.token = ?
                LIMIT 1";
        
        return $this->db_read->fetchAll($sql, array($token));    
    }

    /**
     * Get timezones
     *
     * @return bool|array
     */
    public function getTimezones()
    {
        $sql = "SELECT *
                FROM unitmanagement.timezone";
        
        return $this->db_read->fetchAll($sql);    
    }

    /**
     * Get user by username
     *
     * @return bool|array
     */
    public function getUserByUsername($username)
    {
        $sql = "SELECT 
                    *
                FROM crossbones.user
                WHERE username = ?
                AND userstatus_id > 0
                AND userstatus_id < 4
                LIMIT 1";
            
        return $this->db_read->fetchAll($sql, array($username));        
    }

    /**
     * Delete user invitation by user id
     *
     * @param int user_id
     *
     * @return bool
     */
    public function deleteUserInvitation($user_id)
    {
        return $this->db_write->delete('crossbones.userinvitation', array('user_id' => $user_id));        
    }

    /**
     * Get users where NOT IN
     *
     * @param int account_id
     * @param array params
     *
     * @return bool|array
     */
    public function getUserWhereNotIn($account_id, $params)
    {
        $where_not_in = '';
        $sqlPlaceHolder = array($account_id);

        if (! empty($params) AND is_array($params)) {
            $where_not_in .= "AND ";
            foreach ($params as $column => $value) {
                if (! is_array($value)) {
                    $value = array($value);
                }
                
                $val = substr(str_repeat('?,', count($value)), 0, -1);
                $sqlPlaceHolder = array_merge($sqlPlaceHolder, array_values($value));
                                    
                $where_not_in .= $column . " NOT IN " . "(" . $val . ")";    
            }    
        }
        
        $sql = "SELECT *, CONCAT(firstname, ' ', lastname) AS fullname
                FROM user
                WHERE account_id = ? {$where_not_in} AND userstatus_id = 3";

        return $this->db_read->fetchAll($sql, $sqlPlaceHolder);        
    }

    /**
     * Get all accounts
     *
     * @param array account_status
     *
     * @return bool|array
     */
    public function getAccounts($account_status)
    {
        $sqlPlaceHolder = array();
        $where_in = "";
        if (! empty($account_status) AND is_array($account_status)) {
            $where_in = " AND ac.accountstatus_id IN (" . substr(str_repeat('?,', count($account_status)), 0, -1) . ")";
            $sqlPlaceHolder = array_values($account_status);
        }

        $sql = "SELECT 
                    *
                FROM account ac
                WHERE 1{$where_in}
                ORDER BY ac.account_id";

        return $this->db_read->fetchAll($sql, $sqlPlaceHolder);        
    }

    /**
     * Get user by account id
     *
     * @return bool|array
     */
    public function getUserByAccountId($account_id)
    {
        $sql = "SELECT 
                    *, CONCAT(firstname, ' ', lastname) AS fullname
                FROM user
                WHERE account_id = ? AND userstatus_id = 3"; // 3 = active

        return $this->db_read->fetchAll($sql, array($account_id));        
    }
    
    /**
     * Get user command history
     *
     * @param $account_id, $params
     * @return array|bool
     */
    public function getUserCommandHistory($account_id, $filter_params) 
    {
        $sqlPlaceHolder = array();
        $where_filter = $limit = "";

        if (isset($filter_params['starttime']) AND isset($filter_params['endtime'])) {
            $where_filter .= " AND messagedate >= ? AND messagedate < ?";
            $sqlPlaceHolder[] = $filter_params['starttime'];
            $sqlPlaceHolder[] = $filter_params['endtime'];   
        }

        if (isset($filter_params['user_id']) && $filter_params['user_id']!='0') {
            $sqlPlaceHolder[] = $filter_params['user_id'];
            $sqlPlaceHolder[] = $filter_params['user_id'];

            $sql = "SELECT (select username from crossbones.user where account_id=? limit 1) as username, unit.unitname as fullname, messagedate as sentdate, commandname from sms.out 
                    left join unitmanagement.unitcommand on out.message=unitcommand.command 
                    left join crossbones.unit on unit.unit_id=out.unit_id
                    where commandname is not null and messagestatus='Sent' 
                    and unit.account_id=? {$where_filter}
                    order by messagedate desc";
                    // echo "if";
        } else {

            $sql = "SELECT '' as username, unit.unitname as fullname, messagedate as sentdate, commandname from sms.out 
                    left join unitmanagement.unitcommand on out.message=unitcommand.command 
                    left join crossbones.unit on unit.unit_id=out.unit_id
                    where commandname is not null and messagestatus='Sent' 
                     {$where_filter}
                    order by messagedate desc";
        }

        // echo $sql;

        //Get Disabled History
        //SELECT * from sms.out where message REGEXP (select GROUP_CONCAT(REPLACE(REPLACE(command, '!', ''), '+', '') SEPARATOR '|') as DisableString from unitmanagement.unitcommand where commandname='Starter Disable') AND messagestatus='Sent' AND unit_id=1 order by messagedate desc limit 1       
        //select unit_id, messagedate, commandname from sms.out left join unitmanagement.unitcommand on out.message=unitcommand.command where commandname is not null and messagestatus='Sent' and unit_id=1735 order by messagedate desc                
        $query=$this->db_read->fetchAll($sql, $sqlPlaceHolder);

        return $query;
    }
    
    /**
     * getUserStarterDisabledUnit
     *
     * @param $account_id, $params
     * @return array|bool
     */
    public function getUserStarterDisabledUnit($account_id, $filter_params) 
    {

        $sql = "SELECT db as db
                FROM crossbones.unit
                WHERE unit_id = ?";

        $data = $this->db_read->fetchAssoc($sql, array($filter_params['unit_id']));
        $udb = $data['db'];

        $sqlPlaceHolder = array($account_id);

        //Get Disabled History
        //$sql="select (SELECT username FROM crossbones.user where account_id=".$account_id." limit 1) AS username, (select concat(streetaddress, ', ', city, ' ', state, ', ', zipcode) from unitevent1.unit".$filter_params['unit_id']." order by servertime desc limit 1) as receiveaddress, smshist.messagedate from (SELECT * from sms.out where unit_id=".$filter_params['unit_id']." AND messagestatus='Sent' and message REGEXP (select GROUP_CONCAT(REPLACE(REPLACE(command, '!', ''), '+', '') SEPARATOR '|') as DisableString from unitmanagement.unitcommand) order by messagedate desc limit 1) smshist LEFT JOIN unitmanagement.unitcommand on smshist.message=unitcommand.command where commandname='Starter Disable'";
        $sql="select (SELECT username FROM crossbones.user where account_id=".$account_id." limit 1) AS username, (select concat(streetaddress, ', ', city, ' ', state, ', ', zipcode) from ".$udb.".unit".$filter_params['unit_id']." order by servertime desc limit 1) as receiveaddress, smshist.messagedate, smshist.commandname from (select commandname, messagedate from (select * from unitmanagement.unitcommand  left join sms.out on out.message=unitcommand.command WHERE unit_id=".$filter_params['unit_id']." and messagestatus='Sent' order by messagedate desc limit 1) smsinsidehist) smshist";

        $query=$this->db_read->fetchAll($sql, $sqlPlaceHolder);

        return $query;
    }


    /**
     * Add a temporary token
     *
     * @param $user_id,$token,$expires
     * @return array|bool
     */
    public function addTempToken($user_id,$token,$expires)
    {
        $sql = "INSERT INTO crossbones.temporary_tokens (`user_id`,`token`,`expires`,`active`) 
                VALUES ('$user_id','$token','$expires',1)";

        if ($this->db_write->executeQuery($sql)) {
            return true;
        }

        return false;
    }
    
    /**
     * Get temporary token
     *
     * @param $token
     * @return array|bool
     */
    public function getTempToken($token)
    {
        $sql = "SELECT * FROM crossbones.temporary_tokens WHERE token='$token' AND active=1 AND expires >= NOW()";
		
		$token=$this->db_write->fetchAll($sql);
		
        return $token;
    }
    
    /**
     * Disable temporary token
     *
     * @param $token
     * @return array|bool
     */
    public function disableTempToken($token)
    {
        $sql = "UPDATE crossbones.temporary_tokens SET active=2 WHERE token='$token'";

        if ($this->db_write->executeQuery($sql)) {
            return true;
        }

        return false;
    }

    public function getUserBySudo($sudo)
    {
        return $this->db_read->fetchAll("
            SELECT *
            FROM user
            WHERE sudo = ? AND userstatus_id = 3
        ", array($sudo));
    }
    
    /**
     * Get URL
     *
     * @param $url
     * @return array
     */
    public function getURL($url)
    {
        $sql = "SELECT * FROM crossbones.urls WHERE `url`='$url' LIMIT 1";
		
		$urldata=$this->db_read->fetchAssoc($sql);
		
        return $urldata;
    }
}
