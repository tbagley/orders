<?php

namespace GTC\Framework;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

class User implements UserInterface, EquatableInterface
{
    private $user_id;
    private $accountid;
    private $accountname;
    private $accounttheme;
    private $user_timezone;
    private $account_timezone;
    private $username;
    private $password;
    private $salt;
    private $roles;
    private $permission_data;
    private $access;
    private $first_login;

    public function __construct($user_data, array $roles, array $permission_data, $first_login)
    {
        $this->user_id          = $user_data['user_id'];
        $this->accountid        = $user_data['account_id'];
        $this->accountname      = $user_data['accountname'];
        $this->accounttheme     = $user_data['theme'];
        $this->user_timezone    = $user_data['user_timezone'];
        $this->account_timezone = $user_data['account_timezone'];
        $this->username         = $user_data['username'];
        $this->password         = $user_data['password'];
        //$this->salt             = $salt;
        $this->roles            = $roles;
        $this->permission_data  = $permission_data;

        $this->first_login      = $first_login;

        $this->determineAccess();
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getAccountId()
    {
        return $this->accountid;
    }

    public function getAccountName()
    {
        return $this->accountname;
    }

    public function getAccountTheme()
    {
        return $this->accounttheme;
    }

    public function getUserTimeZone()
    {
        return $this->user_timezone;
    }

    public function getAccountTimeZone()
    {
        return $this->account_timezone;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
    }

    public function getAccess()
    {
        return $this->access;
    }

    public function updateAccess($starter, $reminder)
    {
        $access = $this->access ;

        if (isset($starter)){
            $access['vehicle_starter']['write'] = true ;
        } else {
            $access['vehicle_starter']['write'] = false ;
        }

        if (isset($reminder)){
            $access['vehicle_reminder']['write'] = true ;
        } else {
            $access['vehicle_reminder']['write'] = false ;
        }

        $this->access = $access;
        return $this->access;
    }

    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->getSalt() !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    public function isFirstLogin(){
        return $this->first_login;
    }

    public function determineAccess()
    {
        $access = array();

        $access['owner_user']['write'] = false;

        $roles = $this->getRoles();

        $core = &get_instance();

        // move this somewhere else
        $sql = "SELECT object, action
                FROM permission
                ";

        // fetch all permissions in database
        $all_permissions = $core->db_read->fetchAll($sql);

        // set all permission off by default
        foreach($all_permissions as $p) {
            $access[$p['object']][$p['action']] = false;
        }

        // overwrite individual permissions
        foreach ($this->permission_data as $p) {
            $access[$p['object']][$p['action']] = true;
        }

        $this->access = $access;

        //$access['vehicle_reminder']['write'] = false;

        //print_rb($access);

    }
}
