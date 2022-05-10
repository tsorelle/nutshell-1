<?php

namespace Peanut\users;

use Peanut\users\db\model\entity\User;

class CmsUser extends \Tops\sys\TAbstractUser
{
    /**
     * @var User
     */
    private $userData;

    /**
     * @var
     */
    private $roles;

    /**
     * @var AccountManager
     */
    private $accountManager;

    public function __construct()
    {
        $this->accountManager = UserFactory::getAccountManager();
    }

    /**
     * @inheritDoc
     */
    public function loadById($id)
    {
        $this->userData = $this->accountManager->getUserData($id);
    }

    /**
     * @inheritDoc
     */
    public function loadByEmail($email)
    {
        $id = $this->accountManager->getCmsUserIdByEmail($email);
        $this->userData =  $this->accountManager->getUserData($id);
    }

    /**
     * @inheritDoc
     */
    public function loadByUserName($userName)
    {
        $this->userData = $this->accountManager->getUserData($userName);
    }

    /**
     * @inheritDoc
     */
    public function loadCurrentUser()
    {
        $this->userData = $this->accountManager->getCurrentSignedInUser();
        if (!$this->userData) {
            $this->userData = new User();
            $this->userData->username='guest';
            $this->userData->id = false;
        }
    }

    /**
     * @inheritDoc
     */
    public function isAdmin()
    {
        if (isset($this->userData)) {
            return ($this->userData->id == 1 || $this->isMemberOf('administrators'));
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        if (!isset($this->userData)) {
            return [];
        }
        if (!isset($this->roles)) {
            $this->roles = $this->accountManager->getUserRoles($this->userData->id);
        }
        return $this->roles;
    }

    public function signIn($username, $password = null)
    {
        return $this->accountManager->signIn($username,$password);
    }

    /**
     * @inheritDoc
     */
    public function isAuthenticated()
    {
        return (isset($this->userData) && $this->userData->username !== 'guest');
    }

    protected function loadProfile()
    {
        if (isset($this->userData) && $this->userData->id) {

            $this->profile = $this->accountManager->getProfileValues($this->userData->id);
        }
        else {
            $this->profile = [];
        }
    }
}