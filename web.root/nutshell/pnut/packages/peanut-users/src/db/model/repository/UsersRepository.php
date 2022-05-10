<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2022-05-09 12:38:20
 */ 

namespace Peanut\users\db\model\repository;

use \PDO;
use PDOStatement;
use Peanut\users\db\model\entity\User;
use Tops\db\TDatabase;
use \Tops\db\TEntityRepository;
use Tops\sys\TConfiguration;

class UsersRepository extends \Tops\db\TEntityRepository
{
    public function setAdminAccount($password) {
        /**
         * @var $current User
         */
        $current = $this->get(1);
        if ($current) {
            $current->password = $password;
            $this->update($current);
        }
        else {
            $sql =
                // "INSERT INTO pnut_users (`id`,`username`,`password`,`active`,`createdby`,createdon`,`changedby`,`changedon`) ".
                "INSERT INTO pnut_users (`id`,`username`,`password`,`active`,`createdby`,`changedby`) ".
                " VALUES (1,'admin',?,1,'system','system')";

            $this->executeStatement($sql,[$password]);
        }
    }

    public function getUserIdByEmail($email,$emailTableName)
    {
        $sql = sprintf(
            'SELECT u.id FROM pnut_users u '.
            'JOIN %s e ON u.id = p.accountId '.
            'WHERE u.active = 1 and p.active = 1 and e.email = ?',$emailTableName);

        return $this->getValue($sql,$email);
    }

    public function getContactInfo($id, $fields)
    {
        $sqlFormat = 'SELECT'.
            ' u.username,'.
            ' $s AS email,'.
            ' %s AS `full-name`,'.
            ' %s AS `short-name,'.
            ' %s AS display-name,'.
            ' %s AS timezone,'.
            ' %s  AS `language`'.
            ' FROM `pnut_users` u'.
            ' JOIN %s c ON c.`accountId` = u.id'.
            ' WHERE u.id = ?';

        $email = $fields['email'] ?? null;
        $fullname = $fields['fullname'] ?? null;
        $fullname = $fullname ? "c.$fullname" : 'u.username';
        $shortname = $fields['fullname'] ?? null;
        $shortname = $shortname ? "c.$shortname" : $fullname;
        $displayname = $fields['displayname'] ?? null;
        $displayname = $displayname ? "c.$displayname" : $fullname;
        $timezone =  $fields['timesone'] ?? null;
        $timezone = $timezone ? "c.$timezone" : "'UCT'";
        $language =  $fields['language'] ?? null;
        $language = $language ? "c.$language" : "'en'";

        $sql = sprintf($sqlFormat,$email,$fullname,$shortname,$displayname,$language);

        $stmt = $this->executeStatement($sql,$id);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    protected function getTableName() {
        return 'pnut_users';
    }

    protected function getDatabaseId() {
        return null;
    }

    protected function getClassName() {
       return 'Peanut\users\db\model\entity\User';
    }

    protected function getFieldDefinitionList()
    {
        return array(
        'id'=>PDO::PARAM_INT,
        'username'=>PDO::PARAM_STR,
        'personId'=>PDO::PARAM_INT,
        'password'=>PDO::PARAM_STR,
        'registrationtime'=>PDO::PARAM_STR,
        'active'=>PDO::PARAM_STR,
        'createdby'=>PDO::PARAM_STR,
        'createdon'=>PDO::PARAM_STR,
        'changedby'=>PDO::PARAM_STR,
        'changedon'=>PDO::PARAM_STR);
    }

    /**
     * @param $username
     * @return bool|User
     */
    public function getUserByUsername($username) {
        return $this->getSingleEntity('username = ?',$username);
    }
}