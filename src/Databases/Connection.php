<?php

namespace Tethys\Databases;

use Tethys\Core\Component;
use Tethys\Core\Exception;

abstract class Connection extends Component implements RecordQueriesInterface
{

    const FETCH_ALL = 'all';
    const FETCH_COLUMN = 'column';
    const FETCH_ONE = 'one';
    const FETCH_ROW = 'row';
    const FETCH_PAIRS = 'pairs';

    /**
     * @var string
     */
    public $db;

    /**
     * @var string
     */
    public $user;

    /**
     * @var string
     */
    public $password;

    /**
     * @var \PDO
     */
    protected $dbh;

    /**
     * @var string[]
     */
    public $prepare;

    /**
     * @return \PDO
     * @throws Exception
     */
    public function dbh()
    {
        if (null === $this->dbh) {

            try {

                $this->dbh = new \PDO($this->getConnectionString(), $this->user, $this->password, $this->getConnectionOptions());
                $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            } catch (\PDOException $e) {

                throw new DatabaseErrorException($e->getMessage(), $e->getCode(), $e);

            }

            try {

                if ($this->prepare && is_array($this->prepare)) {
                    foreach ($this->prepare as $sql) $this->dbh->query($sql);
                }


            } catch (\PDOException $e) {

                throw new QueryErrorException($e->getMessage(), $e->getCode(), $e);

            }

        }

        return $this->dbh;

    }

    /**
     * @param string $sql
     * @return mixed
     * @throws QueryErrorException
     */
    public function fetchOne($sql)
    {
        try {
            return $this->dbh()->query($sql)->fetchColumn();
        } catch (\PDOException $e) {
            throw new QueryErrorException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $sql
     * @param bool $assoc
     * @return array
     */
    public function fetchRow($sql, $assoc = true)
    {
        return $this->dbh()->query($sql, $assoc ? \PDO::FETCH_ASSOC : \PDO::FETCH_NUM)->fetch();
    }

    /**
     * @param string $sql
     * @param bool $value_as_key
     * @return array
     */
    public function fetchCol($sql, $value_as_key = false)
    {
        $query = $this->dbh()->query($sql, \PDO::FETCH_COLUMN, 0);

        if (false === $value_as_key) return $query->fetchAll();

        $result = [];
        foreach ($query->fetchAll() as $value) $result[$value] = $value;
        return $result;
    }

    /**
     * @param string $sql
     * @return array
     */
    public function fetchPairs($sql)
    {
        return $this->dbh()->query($sql, \PDO::FETCH_KEY_PAIR)->fetchAll();
    }

    /**
     * @param string $sql
     * @param null $key_field
     * @param bool $clear_field
     * @return mixed
     * @throws DatabaseErrorException
     */
    public function fetchAll($sql, $key_field = null, $clear_field = false)
    {
        $result = $this->dbh()->query($sql, \PDO::FETCH_ASSOC)->fetchAll();
        if (!$key_field) return $result;
        $ret = [];
        foreach ($result as $item) {
            if (!isset($item[$key_field])) throw new DatabaseErrorException('Key field not found');
            $key_field_value = $item[$key_field];
            if ($clear_field) unset($item[$key_field]);
            $ret[$key_field_value] = $item;
        }
        return $ret;
    }

    /**
     * @param string $queries
     * @param array $types
     * @return array
     */
    public function fetchMulti($queries = '', $types = [])
    {
        $ret = [];
        $query = $this->dbh()->query($queries);
        do {

            switch ($types ? array_shift($types) : self::FETCH_ALL) {

                case(self::FETCH_PAIRS):
                    $ret[] = $query->fetchAll(\PDO::FETCH_KEY_PAIR) ?: [];
                    break;

                case(self::FETCH_ROW):
                    $ret[] = $query->fetch(\PDO::FETCH_ASSOC) ?: null;
                    break;

                case(self::FETCH_COLUMN):
                    $ret[] = $query->fetchAll(\PDO::FETCH_COLUMN);
                    break;

                case(self::FETCH_ONE):
                    $ret[] = $query->fetchColumn();
                    break;

                default:
                    $ret[] = $query->fetchAll(\PDO::FETCH_ASSOC) ?: [];
                    break;

            }

        } while ($query->nextRowset());
        return $ret;
    }

    /**
     * @param string $sql
     * @return bool|\PDOStatement
     */
    public function query($sql)
    {
        return $this->dbh()->query($sql);
    }

    /**
     * @param string $sql
     * @param string $name
     * @return int
     * @throws InsertQueryErrorException
     */
    public function insert($sql, $name = null)
    {
        try {
            $this->dbh()->query($sql, \PDO::ERRMODE_EXCEPTION);
            $new_id = (int)$this->dbh()->lastInsertId($name);
            if (!$new_id) throw new InsertQueryErrorException('Last insert id error');
            return $new_id;
        } catch (\PDOException $e) {
            throw new InsertQueryErrorException($e->getMessage());
        }
    }

    /**
     * @param string $string
     * @return string
     */
    public function escape($string)
    {
        return $this->dbh()->quote($string);
    }

    /**
     * @param \Closure $func
     * @throws \Exception
     */
    public function transaction($func)
    {

        if (!$this->dbh()->beginTransaction()) throw new \Exception('Error begin transaction');

        try {

            call_user_func($func, $this);
            $this->dbh()->commit();

        } catch (\Exception $e) {

            $this->dbh()->rollBack();
            throw $e;

        }

    }

    /**
     * @param string|Record $class
     * @param array $filterParams
     * @param string|null $orderBy
     * @return RecordFilter
     * @throws Exception
     */
    public function findObject($class, array $filterParams = [], $orderBy = null)
    {

        if (!$class) throw new Exception('1'); // todo: make empty record class
        if (!class_exists($class)) throw new Exception('2'); // todo: make no record class

        $reflection = new \ReflectionClass($class);
        if (!$reflection->isSubclassOf('Tethys\Databases\Record')) throw new Exception('3 '.$class); // todo: make bad record class

        $filterClass = static::getFilterClass();
        if (!$filterClass) throw new Exception('4'); // todo: make empty filter class
        if (!class_exists($filterClass)) throw new Exception('5'); // todo: make no filter class

        $reflection = new \ReflectionClass($filterClass);
        if (!$reflection->isSubclassOf('Tethys\Databases\RecordFilter')) throw new Exception('6 '.$filterClass); // todo: make bad record class

        return $filterClass::make([
            'connection' => $this,
            'objectClass' => $class,
            'filterParams' => $filterParams,
            'orderBy' => $orderBy,
        ]);
    }

    /**
     * @return string
     */
    abstract public function getConnectionString();

    /**
     * @return string|RecordFilter
     */
    abstract public function getFilterClass();

    /**
     * @return array
     */
    public function getConnectionOptions()
    {
        return [];
    }

}