<?php

namespace Peanut\users;

use Tops\sys\IUser;
use Tops\sys\IUserAccountManager;

class UserFactory implements \Tops\sys\IUserFactory
{

    /**
     * @inheritDoc
     */
    public function createUser()
    {
        $result = new CmsUser();

    }

    /*
     * @var $accountManager AccountManager
     */
    private static $accountManager;
    public static function getAccountManager() {
        if (!isset(self::$accountManager)) {
            self::$accountManager = new AccountManager();
        }
        return self::$accountManager;
    }

    /**
     * @inheritDoc
     */
    public function createAccountManager()
    {
        return self::getAccountManager();
    }
}