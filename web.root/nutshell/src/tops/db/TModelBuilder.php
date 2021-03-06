<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 7/3/2017
 * Time: 12:51 PM
 */

namespace Tops\db;

/*****
 * Example build script: \tools\create-model.php
 */

use Tops\sys\TConfiguration;
use Tops\sys\TPath;
use \PDO;

class TModelBuilder
{
    /**
     * @var \PDO
     */
    private static $dbh;
    private static $modelsPath;
    private static $dbPath;
    private static $appNamespace;
    private static $tempNamespace = 'Peanut\ORM';
    private static $prefix;
    private static $overwrite;

    private static function buildSource($tableName,$params,$databaseId=null)
    {
        $dbh = self::$dbh;
        $dbPath = self::$dbPath;
        $modelsPath = self::$modelsPath;
        $databaseId = empty($databaseId) ? 'null' : "'$databaseId'";
        $date = new \DateTime();
        if (empty($params)) {
            $params = array();
        }

        print "\nBuilding $tableName...";

        $q = $dbh->prepare("DESCRIBE $tableName");
        $q->execute();
        $fields = $q->fetchAll(PDO::FETCH_OBJ);

        $dtoTypes = [];

        $repository = @$params['repository'];

        if (empty($repository)) {
            $repository = self::entityNameFromTableName($tableName);
        }

        $entityName = @$params['entity'];
        if (empty($entityName)) {
            $len = strlen($repository);
            if (substr($repository, $len - 2) == 'es') {
                $entityName = substr($repository, 0, $len - 2);
            } else if (substr($repository, $len - 1) == 's') {
                $entityName = substr($repository, 0, $len - 1);
            }
        }

        $buildEntity = strpos($entityName, '\\') === false;
        $lookupField = @$params['lookupField'];

        $entityProperties = array();
        $fieldDefs = array();
        $isTimestamped = false;
        $namedEntityFields = array(
            'id','name','description','code'
        );
        foreach ($fields as $field) {
            $fieldName = $field->Field;
            switch ($field->Field) {
                case 'createdby' :
                    $isTimestamped = true;
                    $fieldDefs[] = "'$fieldName'=>PDO::PARAM_STR";
                    break;
                case 'createdon' :
                    $isTimestamped = true;
                    $fieldDefs[] = "'$fieldName'=>PDO::PARAM_STR";
                    break;
                case 'changedby' :
                    $isTimestamped = true;
                    $fieldDefs[] = "'$fieldName'=>PDO::PARAM_STR";
                    break;
                case 'changedon' :
                    $isTimestamped = true;
                    $fieldDefs[] = "'$fieldName'=>PDO::PARAM_STR";
                    break;
                default:
                    switch ($field->Type) {
                        case 'datetime':
                            $dtoTypes[$fieldName] = "DateTime";
                            break;
                        case 'date' :
                            $dtoTypes[$fieldName] = "Date";
                            break;
                        case 'tinyint(1)' :
                            if ($fieldName != 'active') {
                                $dtoTypes[$fieldName] = "Flag";
                            }
                            break;
                        case 'time' :
                            // todo: supported later
                            break;
                    }
                    $entityProperties[$field->Field] = '    public $' . $field->Field . ";";
                    $type = explode('(', $field->Type)[0];
                    $type = $type == 'int' ? 'INT' : 'STR';
                    $fieldDefs[] = "'$fieldName'=>PDO::PARAM_$type";
                    break;
            }
        }


        $repositorySuperclass = '\Tops\db\TEntityRepository';
        $propertyCount = sizeof($entityProperties);
        $isNamedEntity = false;

        if ($isTimestamped) {
            if (array_key_exists('id',$entityProperties) &&
                array_key_exists('name',$entityProperties) &&
                array_key_exists('code',$entityProperties) &&
                array_key_exists('description',$entityProperties) ) {
                $superclass = 'NamedEntity';
                $isNamedEntity = true;
                $lookupField = null;
                unset($entityProperties['id']);
                unset($entityProperties['name']);
                unset($entityProperties['code']);
                unset($entityProperties['description']);
                unset($entityProperties['active']);
                $repositorySuperclass = '\Tops\db\TNamedEntitiesRepository';
                if (sizeof($entityProperties) == 0) {
                    // this is a generic lookup table, use NamedEntity
                    $propertyCount = 0;
                    $buildEntity = false;
                    $fullClassName = '\Tops\db\NamedEntity';
                    $lookupField = null;
                }
            }
            else {
                $superclass = 'TimeStampedEntity';
            }
        }
        else {
            $superclass = 'TAbstractEntity';
        }

        if ($buildEntity) {
            $superclass =  isset($superclass) ?  ' extends \Tops\db\\'.$superclass : '';
            $dto ='$dto';
            $entity =
                "<?php \n" .
                "/** \n" .
                " * Created by /tools/create-model.php \n" .
                " * Time:  " . $date->format('Y-m-d H:i:s') . "\n" .
                " */ \n\n" .
                "// Deployment namespace: "."namespace " . self::$appNamespace . "\\entity;" . "\n\n" .
                "namespace Peanut\\ORM\\entity;" . "\n\n" .
                "class $entityName $superclass \n" .
                "{ \n" .
                join("\n", array_values($entityProperties))."\n\n";

            if (!empty($dtoTypes)) {
                $entity .= "    public function getDtoDataTypes()\n    {\n        ".'$'."types = parent::getDtoDataTypes();\n";
                foreach ($dtoTypes as $propertyName => $dtoType) {
                    $entity .= '        $'.sprintf("types['%s'] = \Tops\sys\TDataTransfer::dataType%s;\n",$propertyName,$dtoType);
            }
                $entity .= '        return $types;'."\n    }\n";
            }

            $entity .= "}\n";

            $fullClassName = self::$appNamespace."\\entity\\" . $entityName;
        }
        else if (!isset($fullClassName)){
            $fullClassName =   $entityName;
        }

        $code = array(
            "<?php ",
            "/** ",
            " * Created by /tools/create-model.php ",
            " * Time:  " . $date->format('Y-m-d H:i:s'),
            " */ \n" .
            " // Deployment NS: "."namespace ".self::$appNamespace."\\repository;\n",
            "namespace Peanut\\ORM\\repository;\n",
            '',
            'use \PDO;',
            'use PDOStatement;',
            'use Tops\db\TDatabase;',
            "use $repositorySuperclass;",
            '',
            "class $repository" . "Repository extends $repositorySuperclass",
            "{",
            "    protected function getTableName() {",
            "        return '$tableName';",
            "    }",
            "",
            "    protected function getDatabaseId() {",
            "        return $databaseId;",
            "    }",
            "");


        if ($propertyCount > 0) {
            $code[] = "    protected function getClassName() {";
            $code[] ="       // return '$fullClassName';";
            $code[] ="        return null; // delete and uncomment above for deployment";
            $code[] ="    }";
            $code[] ="";
            $code[] ="    protected function getFieldDefinitionList()";
            $code[] ="    {";
            $code[] ="        return array(";

            $last = sizeof($fieldDefs);
            $count = 0;
            foreach ($fieldDefs as $def) {
                $count++;
                $code[] = "        $def".($count == $last? ');' : ',');
            }
            $code[] = '    }';

            if (!empty($lookupField)) {
                $code[] =  "    protected function getLookupField() {";
                $code[] =  "        return '$lookupField';";
                $code[] =  "    }";
            }
        }



        $code[] = '}';

        $repos = join("\n",$code);

        if ($buildEntity) {
            self::writeFile($modelsPath,$entityName.'.php',$entity);
        }
        self::writeFile($dbPath,$repository.'Repository.php',$repos);
        print("\n");
    }

    private static function writeFile($filePath,$classFile, $data)
    {
        print "\nWriting '$classFile'...";

        if (self::$overwrite || !file_exists($filePath.$classFile)) {
            file_put_contents($filePath.$classFile,$data);
        }
        else {
            print "\nFile '$classFile' exists. Skipping...";
        }
    }


    private static function makeDirectory($dirname)
    {
        if (!file_exists($dirname)) {
            mkdir($dirname, 0777);
        }
    }

    public static function Build($config=array(),$toolsRoot) {
        $databaseKey =  @$config['settings']['databaseKey'];
        $srcRoot=@$config['settings']['sourcePath'];
        $appNamespace = @$config['settings']['namespace'];
        self::$prefix=empty($config['settings']['prefix']) ? '' : $config['settings']['prefix'];
        self::$overwrite=empty($config['settings']['overwrite']) ? false : true;

        $include=$config['tables'];

        if ($srcRoot == null) {
            $appSrc = TConfiguration::getValue('application','locations');
            $srcRoot = TPath::getFileRoot().$appSrc.'/';
        }
        else {
            // $fileroot = TPath::getFileRoot();
            $srcRoot = "$toolsRoot/$srcRoot";
            if (substr($srcRoot,-1) !== '/') {
                $srcRoot .= '/';
            }
            //$srcRoot = TPath::normalize($srcPath);

        }
        if (!file_exists($srcRoot)) {
            throw new \Exception("Application directory '$srcRoot' does not exist");
        }

        self::$appNamespace = $appNamespace == null ?
            TConfiguration::getValue('applicationNamespace','services') :
            $appNamespace;


        self::$modelsPath = $srcRoot.'entity/';
        self::$dbPath = $srcRoot.'repository/';
        self::makeDirectory(self::$modelsPath);
        self::makeDirectory(self::$dbPath);
        // self::$dbPath = $srcRoot.'db/';
        self::$dbh = TDatabase::getConnection($databaseKey);
        if (substr(self::$appNamespace,0,1) == '\\') {
            self::$appNamespace = substr(self::$appNamespace,1);
        }
        print("Building model\n");
        print ("Entity path: ".self::$modelsPath."\n");
        print("Repository path: ".self::$dbPath."\n");

        $q  = self::$dbh->prepare("SHOW TABLES");
        $q->execute();
        $tables = $q->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            if (array_key_exists($table,$include)) {
                $tableInfo = @$config[$table];
                if (!is_array($tableInfo)) {
                    $tableInfo = [];
                }
                self::buildSource($table,$tableInfo,$databaseKey);
            }
        }

        // var_dump($tables);

        print("\n\nBuild complete.\n");
    }

    /**
     * @param $tableName
     * @return bool|string
     */
    private static function entityNameFromTableName($tableName)
    {
        $className = '';
        $parts = explode('_', $tableName);
        if (sizeof($parts) > 1 && $parts[0] == self::$prefix) {
            array_shift($parts);
        }
        foreach ($parts as $part) {
            $className .= strtoupper(substr($part, 0, 1)) . substr($part, 1);
        }
        /*
                $plural = substr($className, strlen($className) - 1);
                if ($plural == 's') {
                    $className = substr($className, 0, strlen($className) - 1);
                }
        */
        return $className;
    }

}