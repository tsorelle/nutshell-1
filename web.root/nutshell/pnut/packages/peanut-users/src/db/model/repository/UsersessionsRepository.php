<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2022-05-09 12:39:20
 */ 

namespace  Peanut\users\db\model\repository;

use \PDO;
use PDOStatement;
use Peanut\users\db\model\entity\Usersession;
use Tops\db\TDatabase;
use \Tops\db\TEntityRepository;

class UsersessionsRepository extends \Tops\db\TEntityRepository
{
    /**
     * @param $sessionId
     * @return mixed|Usersession
     */
    public function getActiveSession($sessionId)
    {
        return $this->getSingleEntity('signedin >= (NOW() - INTERVAL 7 DAY)) AND  sessionid=?',[$sessionId]);
    }

    public function getSessionBySessionId($sessionId) {
        return $this->getSingleEntity('sessionid=?',[$sessionId]);
    }

    protected function getTableName() {
        return 'pnut_usersessions';
    }

    protected function getDatabaseId() {
        return null;
    }

    protected function getClassName() {
        return 'Peanut\users\db\model\entity\Usersession';
    }

    protected function getFieldDefinitionList()
    {
        return array(
            'id'=>PDO::PARAM_INT,
            'sessionid'=>PDO::PARAM_STR,
            'userId'=>PDO::PARAM_INT,
            'signedin'=>PDO::PARAM_STR);
    }}