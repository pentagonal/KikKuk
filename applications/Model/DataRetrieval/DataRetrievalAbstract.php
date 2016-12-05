<?php
namespace KikKuk\Model\DataRetrieval;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Schema\Column;
use KikKuk\Database;
use KikKuk\Record\Arrays\Collection;
use KikKuk\Utilities\Sanitation;

/**
 * Class DataRetrievalAbstract
 * @package KikKuk\Model\DataRetrieval
 */
abstract class DataRetrievalAbstract implements \ArrayAccess, DataRetrievalInterface, \IteratorAggregate
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $selector;

    /**
     * @var Collection[]
     */
    protected $data;

    /**
     * @var string
     */
    protected $order = null;

    /**
     * @var string
     */
    protected $sort   = null;

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var Statement
     */
    protected $stmt;

    /**
     * @var Column[]
     */
    protected $columns;

    /**
     * User constructor.
     * @param array $args
     */
    public function __construct(array $args = [])
    {
        $this->data['original'] = new Collection($args);
        $this->database = \KikKuk::get('database');
        $this->queryBuilder = $this
            ->database
            ->createQueryBuilder()
            ->select('*')
            ->from($this->database->quoteIdentifier($this->getTable()));
        $this->getBaseSelector();
    }

    /**
     * Get Table Name prefixed
     *
     * @return string
     */
    public function getTable()
    {
        if (!is_string($this->table) || trim($this->table) == '') {
            throw  new \RuntimeException(
                'Table is not defined by model Data Retrieval',
                E_COMPILE_ERROR
            );
        }

        return $this->database->prefixTables($this->table);
    }

    /**
     * Get main base selector column
     *
     * @return string
     */
    public function getBaseSelector()
    {
        if (!$this->selector) {
            $this->getColumns();
        }

        return $this->selector;
    }

    /**
    /**
     * @param string $columnKey
     * @return bool
     */
    final public function columnExist($columnKey)
    {
        if (! is_string($columnKey)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid table name type. Table name must be as string %s given',
                    gettype($columnKey)
                ),
                E_USER_ERROR
            );
        }

        return array_key_exists($columnKey, (array) $this->getColumns());
    }

    /**
     * Get List Columns
     *
     * @return null|Column[]
     */
    final public function getColumns()
    {
        if (!isset($this->columns)) {
            $this->columns = $this->database->listTableColumns($this->table);
        }
        if (is_array($this->columns) &&
            (!$this->selector
                || ! is_string($this->selector)
                || ! array_key_exists($this->selector, $this->columns)
            )
        ) {
            foreach ($this->columns as $columnKey => $column) {
                if ($column->getAutoincrement()) {
                    $this->selector = $columnKey;
                    break;
                }
            }
        }

        return $this->columns;
    }

    /**
     * Get Data
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->data['original']->all();
    }

    /**
     * Get Attribute
     *
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return $this->data['original']->get($name, $default);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasAttribute($name)
    {
        return $this->data['original']->has($name);
    }

    /**
     * Sanitize Attribute Name for Database
     *
     * @param string $keyName
     * @return string
     */
    public function sanitizeDatabaseWhereAttributeName($keyName)
    {
        if (is_string($keyName)) {
            $keyName = $this->database->quoteIdentifiers(
                trim($keyName, $this->database->getQuoteIdentifier())
            );
        }
        return $keyName;
    }

    /**
     * Sanitize Attribute Name for Database
     *
     * @param string $keyName
     * @return string
     */
    public function sanitizeDatabaseAttributeName($keyName)
    {
        if (is_string($keyName)) {
            $keyName = $this->database->quoteIdentifiers(
                trim($keyName, $this->database->getQuoteIdentifier())
            );
        }
        return $keyName;
    }

    /**
     * Sanitize Values
     *
     * @param mixed $value
     * @return string
     */
    public function sanitizeDatabaseQuoteValue($value)
    {
        return $this->database->quote(Sanitation::maybeSerialize($value));
    }

    /**
     * With Where
     *
     * @param mixed $where
     * @param mixed $with
     * @return DataRetrievalAbstract
     */
    public static function where($where, $with = null)
    {
        $static = new static();
        /**
         * @var QueryBuilder $qb
         * @var Database     $database
         */
        $qb = $static->queryBuilder;
        $static->queryBuilder =& $qb;
        if (func_num_args() < 2) {
            if (!is_array($where) && is_string($where)) {
                throw new \InvalidArgumentException(
                    'Argument 1 must be as array or string to get real statement'
                );
            }
            if (is_array($where)) {
                foreach ($where as $key => $value) {
                    $static
                        ->queryBuilder
                        ->andWhere(
                            $static->sanitizeDatabaseWhereAttributeName($key)
                            . ' = '
                            . $static->sanitizeDatabaseQuoteValue($value)
                        );
                }
            } else {
                $static->queryBuilder->andWhere($where);
            }
            return $static;
        }

        if (! is_string($where)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Argument 1 must be as string to get real statement %s given',
                    gettype($where)
                )
            );
        }
        $static->queryBuilder->andWhere(
            $static->sanitizeDatabaseWhereAttributeName($where)
            . '='
            . $static->sanitizeDatabaseQuoteValue($with)
        );
        return $static;
    }

    /**
     * @param mixed $identifier
     * @return static
     */
    public static function find($identifier)
    {
        $static = new static();
        return $static->where($static->selector, $identifier);
    }

    /**
     * @return Statement|int|null
     */
    public function save()
    {
        if (isset($this->data['current'])) {
            if (isset($this->data['original'][$this->selector])
                || isset($this->data['current'][$this->selector])
            ) {
                $qb = clone $this->queryBuilder;
                $qb->resetQueryParts(['update', 'insert', 'set', 'value', 'select', 'where']);
                $qb = $qb->select('count(*) as count');
                $where = isset($this->data['original'][$this->selector])
                    ? $this->data['original'][$this->selector]
                    : $this->data['current'][$this->selector];

                $qb->where(
                    $this->sanitizeDatabaseWhereAttributeName($this->selector)
                    . '= '
                    . $this->sanitizeDatabaseQuoteValue($where)
                );
                $stmt = $qb->execute();
                if ($stmt) {
                    $stmt->fetch(\PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                    if (isset($stmt['count']) && $stmt['count'] > 0) {
                        $clone = clone $this;
                        foreach ($this->data['current'] as $key => $value) {
                            $clone[$key] = $value;
                        }

                        return $clone->update($this->selector, $where);
                    }
                }
            }

            $this->queryBuilder->insert(
                $this->database->quoteIdentifier($this->getTable())
            );
            foreach ($this->data['current'] as $column => $value) {
                $this
                    ->queryBuilder
                    ->setValue(
                        $this->sanitizeDatabaseAttributeName($column),
                        $this->sanitizeDatabaseQuoteValue($value)
                    );
            }

            return $this->queryBuilder->execute();
        }

        return null;
    }

    public function update($where = null, $with = null)
    {
        if (! isset($this->data['current'])
            || count($this->data['current']) === 0
            || func_num_args() === 0
            && (
                count($this->data['original']) === 0
                || ! $this->data['original']->has($this->selector)
            )
        ) {
            return null;
        }

        $qb =  clone $this->queryBuilder;
        $qb->resetQueryParts(['where', 'set', 'update']);
        $qb->update($this->database->quoteIdentifier($this->getTable()));
        if (func_num_args() < 2) {
            if (func_num_args() > 0) {
                if (!is_array($where) && is_string($where)) {
                    throw new \InvalidArgumentException(
                        'Argument 1 must be as array or string to get real statement'
                    );
                }
            } else {
                $where = $this->sanitizeDatabaseWhereAttributeName($this->selector)
                    . '='
                    . Sanitation::maybeSerialize($this->data['original'][$this->selector]);
            }

            if (is_array($where)) {
                foreach ($where as $key => $value) {
                    $qb->andWhere(
                            $this->sanitizeDatabaseWhereAttributeName($key)
                            . ' = '
                            . $this->sanitizeDatabaseQuoteValue($value)
                        );
                }
            } else {
                $qb->andWhere($where);
            }
        } else {
            $qb->andWhere(
                $this->sanitizeDatabaseWhereAttributeName($where)
                . '='
                . $this->sanitizeDatabaseQuoteValue($with)
            );
        }

        foreach ($this->data['current'] as $column => $with) {
            $qb->set(
                $this->sanitizeDatabaseAttributeName($column),
                $this->sanitizeDatabaseQuoteValue($with)
            );
        }

        return $qb->execute();
    }

    /**
     * Sort Asc
     *
     * @return DataRetrievalAbstract
     */
    public function asc()
    {
        $this->order = 'ASC';
        $this
            ->queryBuilder
            ->orderBy(
                $this->sort,
                $this->order
            );
        return $this;
    }

    /**
     * Sort Desc
     *
     * @return DataRetrievalAbstract
     */
    public function desc()
    {
        $this->order = 'DESC';
        $this
            ->queryBuilder
            ->orderBy(
                $this->sort,
                $this->order
            );
        return $this;
    }

    /**
     * Sort By Random
     *
     * @return DataRetrievalAbstract
     */
    public function rand()
    {
        $this->order = 'RAND()';
        $this->queryBuilder->orderBy(
            $this->order
        );
        return $this;
    }

    /**
     * Add Group
     *
     * @param string $by
     * @return DataRetrievalAbstract
     */
    public function group($by)
    {
        $this->queryBuilder->addGroupBy($by);
        return $this;
    }

    /**
     * @param int $arg
     * @return DataRetrievalAbstract
     */
    public function limit($arg)
    {
        $this->queryBuilder->setMaxResults($arg);
        return $this;
    }

    /**
     * @param int $arg
     * @return DataRetrievalAbstract
     */
    public function offset($arg)
    {
        $this->queryBuilder->setFirstResult($arg);
        return $this;
    }

    /**
     * Get SQL Query
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->queryBuilder->getSQL();
    }

    /**
     * Order By
     *
     * @param string $by
     * @return DataRetrievalAbstract
     */
    public function order($by)
    {
        $this->sort = $by;
        if ($this->order == 'RAND()') {
            $this->order = null;
        }
        $this->queryBuilder->orderBy(
            $this->sort
        );

        return $this;
    }

    /**
     * Get Data
     *
     * @return array
     */
    public function fetchAll()
    {
//        if (strpos($this->getQuery(), 'WHERE ') === false) {
//            return null;
//        }

        $stmt = $this->queryBuilder->execute();
        $array_return = [];
        if ($stmt) {
            while ($rows = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $array_return[] = new static($rows);
            }
        }
        return $array_return;
    }

    /**
     * Fetch Data
     *
     * @return static|null
     */
    public function fetch()
    {
//        if (strpos($this->getQuery(), 'WHERE ') === false) {
//            return null;
//        }

        $stmt = $this->queryBuilder->execute();
        $return = null;
        if ($stmt) {
            $return = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!empty($return) && is_array($return)) {
                return new static($return);
            }
        }

        return $return;
    }

    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function offsetExists($offset)
    {
        return isset($this->data['current']) && $this->data['current']->has($offset);
    }

    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

    public function __set($name, $value)
    {
        if (!isset($this->data['current'])) {
            $this->data['current'] = new Collection();
        }

        $this->data['current'][$name] = $value;
    }

    /**
     * Get Current Data
     *
     * @param string $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if ($this->offsetExists($name)) {
            return $this->data['current']->get($name);
        }
        return null;
    }

    public function __unset($name)
    {
        if (isset($this->data['current'])) {
            unset($this->data['current'][$name]);
        }
    }

    /**
     * Reset Values given
     */
    public function resetValue()
    {
        unset($this->data['current']);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->getAttributes();
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->getAttributes());
    }
}