<?php

namespace GTC\Framework;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 *
 * Based off Symfony's UserProviderInterface implementation
 *
 * Class UserProvider
 *
 */
class UserProvider implements UserProviderInterface
{
    public function loadUserByUsername($username)
    {
        // need to inject database object in a more efficient way, not the Codeigniter method
        $core =& get_instance();

        $sql = "SELECT u.user_id, u.account_id, u.username, u.password, u.roles, u.usertype_id, u.userstatus_id,
                utz.timezone_id AS user_timezone_id, utz.timezone AS user_timezone,
                a.accountname, a.accounttype_id, a.theme,
                atz.timezone_id AS account_timezone_id, atz.timezone AS account_timezone
                FROM user u
                LEFT JOIN account a ON u.account_id = a.account_id
                LEFT JOIN unitmanagement.timezone utz ON u.timezone_id = utz.timezone_id
                LEFT JOIN unitmanagement.timezone atz ON u.timezone_id = atz.timezone_id
                WHERE username = ?
                AND u.userstatus_id > 0 ";

        // look up user in database
        $userData = $core->db_read->fetchAssoc($sql, array($username));

        //print_rb($userData, false);

        if ($userData) {

            $inactive_statuses = array("2", "inactive");

            if (in_array($userData['userstatus_id'], $inactive_statuses)) {
                // block inactive users
                throw new AuthenticationException(sprintf('Username "%s" is inactive, please contact the an administrator.', $username));

            } else {

                // using status, set boolean for whether this is the initial login
                $first_login = ($userData['userstatus_id'] == 1 OR  $userData['userstatus_id'] == 'pending') ? true : false;

                $sql = "SELECT object, action
                        FROM usertype_permission
                        JOIN permission USING (permission_id)
                        WHERE usertype_id = ? ";

                // look up permissions in database
                $permissionData = $core->db_read->fetchAll($sql, array($userData['usertype_id']));

                // build user object and send it back to authentication manager, which checks password, etc
                $userObj = new User($userData, explode(',', $userData['roles']), $permissionData, $first_login);

                return $userObj;

            }
        }

        throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Not sure what this does
     *
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === 'Acme\WebserviceUserBundle\Security\User\WebserviceUser';
    }
}

