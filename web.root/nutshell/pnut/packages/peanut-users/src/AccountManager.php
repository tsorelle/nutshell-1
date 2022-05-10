<?php

namespace Peanut\users;

use Peanut\users\db\model\entity\Role;
use Peanut\users\db\model\entity\User;
use Peanut\users\db\model\entity\Usersession;
use Peanut\users\db\model\repository\RolesRepository;
use Peanut\users\db\model\repository\UserRolesAssociation;
use Peanut\users\db\model\repository\UsersessionsRepository;
use Peanut\users\db\model\repository\UsersRepository;
use Tops\sys\IUserAccountManager;
use Tops\sys\TAddUserAccountResponse;
use Tops\sys\TConfiguration;

class AccountManager implements IUserAccountManager
{
    /**
     * @var $usersrepository UsersRepository
     */
    private $usersrepository;
    private function getUsersRepository()
    {
        if (!isset($this->usersrepository)) {
            $this->usersrepository = new UsersRepository();
        }
        return $this->usersrepository;
    }

    /**
     * @var $rolesrepository RolesRepository
     */
    private $rolesrepository;
    private function getRolesRepository() {
        if (!isset($this->rolesrepository)) {
            $this->rolesrepository = new RolesRepository();
        }
        return $this->rolesrepository;
    }

    /**
     * @var $userrolesassociation UserRolesAssociation
     */
    private $userrolesassociation;
    private function getUserRolesAssociation() {
        if (!isset($this->userrolesassociation)) {
            $this->userrolesassociation = new UserRolesAssociation();
        }
        return $this->userrolesassociation;
    }

    /**
     * @var $sessionsrepository UsersessionsRepository
     */
    private $sessionsrepository;
    private function getSessionsRepository()
    {
        if (!isset($this->sessionsrepository)) {
            $this->sessionsrepository = new UsersessionsRepository();
        }
        return $this->sessionsrepository;
    }

    function setAdminAccount($password) {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $this->getUsersRepository()->setAdminAccount($password);
    }

    /**
     * @param $username
     * @param $password
     * @param $email (ignored)
     * @param $roles
     * @param $profile (ignored)
     * @param $personId
     * @param $creator
     * @return bool|TAddUserAccountResponse
     *
     * Note, in this implementation, email and profile are maintained in the contact system, ignored here.
     */
    public function addAccount($username, $password, $email=null,$roles=[],$profile=[],
                               $personId = 0, $creator='system')
    {

        $result = new TAddUserAccountResponse();

        $username = trim($username);
        $password = trim($password);
        $validation = $this->validateUserName($username);
        if ($validation !== true) {
            $result->errorCode = $validation;
            return $result;
        }
        $validation = $this->validatePassword($username);
        if ($validation !== true) {
            $result->errorCode = $validation;
            return $result;
        }

        $password = password_hash($password, PASSWORD_DEFAULT);
        $user = new User();
        $user->password = $password;
        $user->username = $username;
        $user->personId = $personId;
        $user->active = 1;
        $user->registrationtime = time();
        $userId = $this->getUsersRepository()->insert($user,$creator);
        $result->invalidRoles = $this->addUserRoles($userId,$roles);

        return true;
    }

    const minUsernameLength = 5;
    private function validateUserName($username)
    {
        if (empty($username)) {
            return 'Username is blank';
        }
        if (strlen($username) < self::minUsernameLength) {
            return sprintf('User name must be at least %d characters long.',self::minUsernameLength);
        }

        $existing = $this->getAccountIdForUsername($username);
        if ($existing !== false) {
            return "Username is in use.";
        }

        return true;
    }

    public function getAccountIdForUsername($userIdentifier)
    {
        if (is_numeric($userIdentifier)) {
            if ($this->getUsersRepository()->getCount('id = '.$userIdentifier)) {
                return $userIdentifier;
            }
            return false;
        }
        return $this->getUsersRepository()->getIdForFieldValue('username',$userIdentifier);
    }

    public function getRoleIdForName($roleIdentifier) {
        if (is_numeric($roleIdentifier)) {
            if ($this->getRolesRepository()->getCount('id = '.$roleIdentifier)) {
                return $roleIdentifier;
            }
            return false;
        }
        return $this->getRolesRepository()->getIdForFieldValue('name',$roleIdentifier);
    }

    const minPasswordLength = 5;
    private function validatePassword($password)
    {
        if (empty($password)) {
            return 'Password is blank';
        }
        if (strlen($password) < self::minPasswordLength) {
            return sprintf('Password must be at least %d characters long.',self::minUsernameLength);
        }
        return true;
    }

    /**
     * @param $usr
     * @return bool|User
     */
    public function getUserData($usr) {
        $repository = $this->getUsersRepository();
        if (is_numeric($usr)) {
            return $repository->get($usr);
        }
        else {
            return $repository->getSingleEntity('username = ?', [$usr]);
        }
    }

    public function changeUserName($usr,$newName,$changedBy='admin') {
        $validation = $this->validateUserName($newName);
        if ($validation !== true) {
            return $validation;
        }
        $user = $this->getUserData($usr);
        $user->username = $newName;
        $this->getUsersRepository()->update($user,$changedBy);
        return true;
    }

    public function changePassword($usr,$newPwd,$changedBy='admin') {
        $validation = $this->validatePassword($newPwd);
        if ($validation !== true) {
            return $validation;
        }
        $user = $this->getUserData($usr);
        $user->password = password_hash($newPwd,PASSWORD_DEFAULT);
        $this->getUsersRepository()->update($user,$changedBy);
        return true;
    }

    public function removeAccount($usr) {
        $user = $this->getUserData($usr);
        if ($user === false) {
            return false;
        }
        // todo: clear contact reference
        $this->getUserRolesAssociation()->removeAllRight($user->id);
        return $this->getUsersRepository()->delete($user->id);
    }

    /**
     * @return false|Usersession
     */
    public function getCurrentSignedInUser() {
        $repository = $this->getSessionsRepository();
        $sessionId = $this->getCurrentSessionId();
        if (!empty($sessionId)) {
            $session = $repository->getActiveSession($sessionId);
            if ($session) {
                return $this->getUsersRepository()->get($session->userId);
            }
        }
        return false;
    }

    public function authenticateUser($username,$pwd) {
        $username = trim($username);
        $user = $this->getUserData($username);
        if (!$user) {
            return false;
        }
        if (password_verify($pwd,$user->password)) {
            return $user;
        }
        return false;
    }

    public function signIn($username,$pwd) {
        $sessionsRepository = $this->getSessionsRepository();
        $user = $this->authenticateUser($username,$pwd);
        if ($user === false) {
            return "User authentication failed.";

        }
        $sessionId = $this->getCurrentSessionId();
        if (!$sessionId) {
            return 'Session not initialized';
        }
        $sessionsRepository->newSession($sessionId,$user->id);
        return $user;
    }

    private function getCurrentSessionId() {
        return (session_status() == PHP_SESSION_ACTIVE) ? session_id() : 0;
    }

    public function signOut() {
        $sessionId = $this->getCurrentSessionId();
        if ($sessionId) {
            $repository = $this->getSessionsRepository();
            $session = $repository->getSessionBySessionId($sessionId);
            if ($session) {
                $repository->delete($session->id);
            }
        }
    }

    public function getRoleByName($name) {
        return $this->getRolesRepository()->getSingleEntity('name = ?',[$name]);
    }

    public function getRoles() {
        return $this->getRolesRepository()->getAll();
    }

    public function getUserRoleNames($usr) {
        $id = $this->getAccountIdForUsername($usr);
        if (!$id) {
            return false;
        }
        return $this->getUserRolesAssociation()->getRightValues($id,'name');
    }

    public function getUserRoles($usr) {
        $id = $this->getAccountIdForUsername($usr);
        if (!$id) {
            return false;
        }
        return $this->getUserRolesAssociation()->getRightObjects($id);
    }

    public function addUserRole($usr,$roleName) {
        $userId = $this->getAccountIdForUsername($usr);
        if (!$usr) {
            return 'User not found';
        }
        return $this->addRoleForUserId($userId,$roleName);
    }

    public function addRoleForUserId($userId,$roleName) {
        $roleId = $this->getRoleIdForName($roleName);
        if (!$roleId) {
            return 'Role not found';
        }
        // $this->getUserRolesAssociation()->addAssociationLeft($userId,$roleId);
        $this->getUserRolesAssociation()->addAssociationRight($userId,$roleId);
        return true;
    }

    public function addUserRoles($userId,array $roles) {
        $invalid = [];
        if (!empty($roles)) {
            foreach ($roles as $roleName) {
                $added = $this->addRoleForUserId($userId,$roleName);
                if ($added != true) {
                    $invalid[] = $roleName;
                }
            }
        }
        return $invalid;
    }

    public function removeUserRole($usr,$roleName) {
        $userId = $this->getAccountIdForUsername($usr);
        if (!$userId) {
            return 'User not found';
        }
        $roleId = $this->getRoleIdForName($roleName);
        if (!$roleId) {
            return 'Role not found';
        }
        // $this->getUserRolesAssociation()->removeAssociationLeft($userId,$roleId);
        $this->getUserRolesAssociation()->removeAssociationRight($userId,$roleId);
        return true;
    }

    public function addRole($roleName,$description,$createdBy='admin') {
        $repository = new RolesRepository();
        $roleId = $this->getRoleIdForName($roleName);
        if ($roleId) {
            return false; // already exists
        }
        $role = new Role();
        $role->name = $roleName;
        $role->description = $description;
        $role->active = 1;
        return $repository->insert($role,$createdBy);

    }

    public function getUsersInRole($roleIdentifier) {
        $repository = $this->getRolesRepository();
        $id = $this->getRoleIdForName($roleIdentifier);
        if (!$id) {
            return [];
        }
        $asso = $this->getUserRolesAssociation();
        return $this->getUserRolesAssociation()->getLeftObjects($id);
    }

    public function removeRole($roleIdentifier) {
        $id = $this->getRoleIdForName($roleIdentifier);
        if ($id) {
            $users = $this->getUsersInRole($id);
            if (count($users) === 0) {
                $this->getRolesRepository()->delete($id);
                return true;
            }
        }
        return false;
    }

    public function getCmsUserId($username)
    {
        return $this->getAccountIdForUsername($username);
    }

    public function getCmsUserIdByEmail($email)
    {
        $emailTableName = TConfiguration::getValue('emailtable','mail','pnut_contacts');
        return $this->getUsersRepository()->getUserIdByEmail($email,$emailTableName);
    }

    public function getPasswordResetUrl()
    {
        return "/user/forgot-password";
    }

    public function getLoginUrl()
    {
        return "/signin";
    }

    public function getProfileValues($userIdentifier) {
        $id = $this->getAccountIdForUsername($userIdentifier);
        if (!$id) {
            return [];
        }
        $fieldConfig = TConfiguration::getIniSection('contact-fields');
        return $this->getUsersRepository()->getContactInfo($id,$fieldConfig);
    }
}