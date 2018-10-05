<?php

namespace Tethys\Databases;

use Tethys\Core\Component;
use Tethys\Core\Exception;

/**
 * Class Connection
 * @package Tethys\Databases
 */
abstract class Connection extends Component implements RecordQueriesInterface
{
    /**
     * Типы запросов для fetchMulti
     */
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
     * @throws DatabaseErrorException
     */
    public function dbh()
    {
        if (null === $this->dbh) {
            try {

                $this->dbh = new \PDO($this->getConnectionString(), $this->user, $this->password, $this->getConnectionOptions());
                $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                if ($this->prepare && is_array($this->prepare)) {
                    foreach ($this->prepare as $sql) $this->dbh->query($sql);
                }

            } catch (\Throwable $t) {

                throw new DatabaseErrorException($t->getMessage(), $t->getCode(), $t);

            }
        }
        return $this->dbh;
    }

    /**
     * @param string $sql
     * @return mixed
     * @throws DatabaseErrorException
     */
    public function fetchOne($sql)
    {
        try {
            return $this->dbh()->query($sql)->fetchColumn();
        } catch (DatabaseErrorException $t) {
            throw $t;
        } catch (\Throwable $t) {
            throw new QueryErrorException($t->getMessage(), $t->getCode(), $t);
        }
    }

    /**
     * @param string $sql
     * @param bool $assoc
     * @return array|null
     * @throws DatabaseErrorException
     */
    public function fetchRow($sql, $assoc = true)
    {
        try {
            return $this->dbh()->query($sql, $assoc ? \PDO::FETCH_ASSOC : \PDO::FETCH_NUM)->fetch();
        } catch (DatabaseErrorException $t) {
            throw $t;
        } catch (\Throwable $t) {
            throw new QueryErrorException($t->getMessage(), $t->getCode(), $t);
        }
    }

    /**
     * @param string $sql
     * @param bool $value_as_key
     * @return array
     * @throws DatabaseErrorException
     */
    public function fetchCol($sql, $value_as_key = false)
    {
        try {

            $query = $this->dbh()->query($sql, \PDO::FETCH_COLUMN, 0);

            if (false === $value_as_key) return $query->fetchAll();

            $result = [];
            foreach ($query->fetchAll() as $value) $result[$value] = $value;
            return $result;

        } catch (DatabaseErrorException $t) {
            throw $t;
        } catch (\Throwable $t) {
            throw new QueryErrorException($t->getMessage(), $t->getCode(), $t);
        }
    }

    /**
     * @param string $sql
     * @return array
     * @throws DatabaseErrorException
     */
    public function fetchPairs($sql)
    {
        try {

            return $this->dbh()->query($sql, \PDO::FETCH_KEY_PAIR)->fetchAll();

        } catch (DatabaseErrorException $t) {
            throw $t;
        } catch (\Throwable $t) {
            throw new QueryErrorException($t->getMessage(), $t->getCode(), $t);
        }
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
        try {

            $result = $this->dbh()->query($sql, \PDO::FETCH_ASSOC)->fetchAll();
            if (!$key_field) return $result;

            $ret = [];
            foreach ($result as $item) {
                if (!isset($item[$key_field])) throw new Exception('Key field not found');
                $key_field_value = $item[$key_field];
                if ($clear_field) unset($item[$key_field]);
                $ret[$key_field_value] = $item;
            }
            return $ret;

        } catch (DatabaseErrorException $t) {
            throw $t;
        } catch (\Throwable $t) {
            throw new QueryErrorException($t->getMessage(), $t->getCode(), $t);
        }
    }

    /**
     * @param string $queries
     * @param array $types
     * @return array
     * @throws DatabaseErrorException
     */
    public function fetchMulti($queries = '', $types = [])
    {
        try {

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

        } catch (DatabaseErrorException $t) {
            throw $t;
        } catch (\Throwable $t) {
            throw new QueryErrorException($t->getMessage(), $t->getCode(), $t);
        }
    }

    /**
     * @param string $sql
     * @return bool|\PDOStatement
     * @throws DatabaseErrorException
     */
    public function query($sql)
    {
        try {

            return $this->dbh()->query($sql);

        } catch (DatabaseErrorException $t) {
            throw $t;
        } catch (\Throwable $t) {
            throw new QueryErrorException($t->getMessage(), $t->getCode(), $t);
        }
    }

    /**
     * @param string $sql
     * @param string $name
     * @return int
     * @throws DatabaseErrorException
     */
    public function insert($sql, $name = null)
    {
        try {

            $this->dbh()->query($sql, \PDO::ERRMODE_EXCEPTION);
            $new_id = (int)$this->dbh()->lastInsertId($name);
            if (!$new_id) throw new Exception('Last insert id error');
            return $new_id;

        } catch (DatabaseErrorException $t) {
            throw $t;
        } catch (\Throwable $t) {
            throw new InsertQueryErrorException($t->getMessage(), $t->getCode(), $t);
        }
    }

    /**
     * @param string $string
     * @return string
     * @throws DatabaseErrorException
     * @throws QueryErrorException
     */
    public function escape($string)
    {
        try {

            return $this->dbh()->quote($string);

        } catch (DatabaseErrorException $t) {
            throw $t;
        } catch (\Throwable $t) {
            throw new InsertQueryErrorException($t->getMessage(), $t->getCode(), $t);
        }
    }

    /**
     * @param callable $func
     * @throws DatabaseErrorException
     */
    public function transaction($func)
    {
        try {

            if (!$this->dbh()->beginTransaction()) throw new Exception('Error begin transaction');

            call_user_func($func, $this);
            $this->dbh()->commit();

        } catch (DatabaseErrorException $e) {
            $this->dbh()->rollBack();
            throw $e;
        } catch (\Throwable $t) {
            $this->dbh()->rollBack();
            throw new TransactionErrorException($t->getMessage(), $t->getCode(), $t);
        }
    }

    /**
     * @param string|Record $class
     * @param array $filterParams
     * @param string|null $orderBy
     * @return RecordFilter|null
     */
    public function findObject($class, array $filterParams = [], $orderBy = null)
    {
        try {

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

        } catch (\Throwable $t) {

            return null;

        }
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