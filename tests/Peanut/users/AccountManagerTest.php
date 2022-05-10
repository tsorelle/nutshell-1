<?php

namespace Peanut\users;

use PHPUnit\Framework\TestCase;

class AccountManagerTest extends TestCase
{

    public function testGetRoleIdForName()
    {

    }

    public function testSetAdminAccount()
    {

    }

    public function testGetAccountIdForUsername()
    {

    }

    public function testAuthenticateUser()
    {

    }

    public function testAddUserRole()
    {

    }

    public function testRemoveUserRole()
    {

    }

    public function testAddRole()
    {
        $manager = new AccountManager();
        $manager->addRole('administrators','System Admins','test');
        $roleId = $manager->getRoleIdForName('administrators');
        $this->assertNotEmpty($roleId);

    }

    public function testGetCurrentSignedInUser()
    {

    }

    public function testRemoveRole()
    {

    }

    public function testSignOut()
    {

    }

    public function testAddAccount()
    {

    }

    public function testGetUserRoleNames()
    {

    }

    public function testGetUsersInRole()
    {

    }

    public function testGetUserRoles()
    {

    }

    public function testAddUserRoles()
    {

    }

    public function testRemoveAccount()
    {

    }

    public function testAddRoleForUserId()
    {

    }

    public function testChangePassword()
    {

    }

    public function testSignIn()
    {

    }

    public function testGetProfileValues()
    {

    }

    public function testChangeUserName()
    {

    }

    public function testGetUserData()
    {

    }
}
