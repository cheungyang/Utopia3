<?php
namespace Utopia\Components\DataModel;

use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;
//use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;

class MigrationDb extends ComponentRoot
{
    private $_conn;
    private $_config;

    static public function isSingleton(){
        return true;
    }

    public function initialize($mixed=false){
        $this->_config = ConfigurationBundle::summon()
            ->setMasterFile(dirname(__FILE__).'/'.ComponentRoot::MASTER_CONF)
            ->{'dataaccess.mysql.config'};

        //$config = new Configuration();
        $this->_config['driver'] = 'pdo_mysql';
        $this->_conn = DriverManager::getConnection($this->_config);
    }

    public function __call($name, $arguments) {
        return call_user_func_array(array($this->_conn, $name), $arguments);
    }

    public function getMigrationQuery(\Utopia\Components\DataModel\DataSchema $schema, $delete=false) {
        $sm = $this->_conn->getSchemaManager();
        $fromSchema = $sm->createSchema();
        $toschema = $this->getDBAMLSchemaObj($schema);
        $comparator = new Comparator();
        $schemaDiff = $comparator->compare($fromSchema, $toschema);

        $platform = $this->_conn->getDatabasePlatform();
        return !$delete?
            $schemaDiff->toSaveSql($platform): //no table drop/deletion
            $schemaDiff->toSql($platform); // queries to get from one to another schema.
    }

    public function migrateSchema(Schema $schema, $delete=false) {
        $queries = $this->getMigrationQuery($schema, $delete);
        foreach ($queries as $sql) {
            $this->_conn->exec($sql);
        }
    }


    /**
     * get schema object subsist of all tables
     *
     * @return Schema
     */
    public function getDBAMLSchemaObj(\Utopia\Components\DataModel\DataSchema $schema) {
        $Schema = new Schema();
        $tables = array();
        $entities = $schema->getEntityNames();

        //FIELDS
        foreach($entities as $entity) {
            if (in_array($entity, array('types', 'options'))) {
                continue;
            }

            $rec = $schema->getSchema($entity);
            $tables[$entity] = $Schema->createTable($schema->getTableName($entity));

            //FIXME: not working: add table options
            foreach ($schema->getSchemaOptions() as $key => $val) {
                $tables[$entity]->addOption($key, $val);
            }

            $primary = array();
            $index = array();
            $uniqueindex = array();

            foreach ($rec['fields'] as $name => $spec) {
                preg_match_all('/(\w*)(\((\d*)\))?/', $spec['type'], $arr, PREG_PATTERN_ORDER);
                $type = $arr[1][0];

                //column
                $opts = isset($spec['opts'])? $spec['opts']: array();
                //length
                if (isset($arr[3][0]) && !empty($arr[3][0])) {
                    $opts['length'] = $arr[3][0];
                }
                //null
                if (isset($spec['req']) && $spec['req']==true) {
                    $opts['notnull'] = true;
                } else {
                    $opts['notnull'] = false;
                }
                //unsigned
                if (isset($spec['unsigned'])) {
                    $opts['unsigned'] = $spec['unsigned'];
                }
                //fixed
                if (isset($spec['fixed'])) {
                    $opts['fixed'] = $spec['fixed'];
                }
                //def
                if (isset($spec['def'])) {
                    $opts['default'] = $spec['def'];
                }
                //autoincrement (not working)
                if (isset($spec['autoincrement']) && $spec['autoincrement']==true) {
                    $opts['autoincrement'] = true;
                    $tables[$entity]->setIdGeneratorType(\Doctrine\DBAL\Schema\Table::ID_IDENTITY);
                }

                $tables[$entity]->addColumn($name, $type, $opts);

                //primary
                if (isset($spec['primary']) && $spec['primary']==true) {
                    $primary[] = $name;
                }

                //uniqueindex
                if (isset($spec['uniqueindex']) && $spec['uniqueindex']==true) {
                    $uniqueindex[] = $name;
                }

                //index
                if (isset($spec['index']) && $spec['index']==true) {
                    $index[] = $name;
                }
            }

            if (!empty($primary)) {
                $tables[$entity]->setPrimaryKey($primary);
            }
            if (!empty($uniqueindex)) {
                $tables[$entity]->addUniqueIndex($uniqueindex);
            }
            if (!empty($index)) {
                $tables[$entity]->addIndex($index);
            }
        }

        //RELATIONSHIPS
        foreach($entities as $entity) {
            if (in_array($entity, array('types', 'options'))) {
                continue;
            }

            $rec = $schema->getSchema($entity);
            foreach ($rec['relationships'] as $field => $spec) {
                $tables[$entity]->addForeignKeyConstraint($tables[$spec['foreigntable']], array($field), array($spec['foreignfield']), array("onUpdate" => "CASCADE"));
            }
        }
        return $Schema;
    }
}
