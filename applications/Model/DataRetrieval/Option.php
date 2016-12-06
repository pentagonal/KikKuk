<?php
namespace KikKuk\Model\DataRetrieval;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Driver\Statement;
use KikKuk\Record\Arrays\Collection;
use KikKuk\Utilities\Sanitation;

/**
 * Class Option
 * @package KikKuk\Model\DataRetrieval
 */
class Option implements \ArrayAccess
{
    /**
     * @var Collection
     */
    protected static $options;

    /**
     * @var string
     */
    protected static $table;

    /**
     * Option constructor.
     */
    public function __construct()
    {
        self::initOption();
    }

    /**
     * Init
     */
    protected static function initOption()
    {
        if (!isset(self::$options)) {
            self::$options = new Collection();
            self::dbInit();
        }
    }

    /**
     * @return QueryBuilder
     */
    protected static function getQb()
    {
        $database = \KikKuk::get('database');
        if (!isset(self::$table)) {
            self::$table = $database->prefixTables('options');
        }

        return $database->createQueryBuilder();
    }

    /**
     * Database Init
     */
    protected static function dbInit()
    {
        $qb = self::getQb();
        $stmt = $qb->select('*')
            ->from(self::$table)
            ->where('LOWER(options_autoload)=:autoload')
            ->setParameter(':autoload', 'yes')
            ->execute();
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $value) {
            $value['options_value'] = Sanitation::maybeUnSerialize($value['options_value']);
            self::$options->set($value['options_name'], $value);
        }
        if ($stmt instanceof Statement) {
            $stmt->closeCursor();
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function has($name)
    {
        if (!is_string($name)) {
            return false;
        }
        return self::getDetail($name, true) !== true;
    }

    /**
     * @param string $name
     * @param mixed  $default
     * @param bool $autoload
     * @return mixed
     */
    public static function getOrUpdate($name, $default, $autoload = false)
    {
        if (self::has($name)) {
            return self::get($name);
        }
        self::update($name, $default, $autoload);
        return $default;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function get($name, $default = null)
    {
        if (self::has($name)) {
            return self::getDetail($name)['options_value'];
        }

        return $default;
    }

    /**
     * @param string $name
     * @param mixed  $default
     * @return mixed|array
     */
    public static function getDetail($name, $default = null)
    {
        if (!is_string($name)) {
            return null;
        }

        self::initOption();
        if (self::$options->has($name)) {
            if (! is_array($value = self::$options->get($name))) {
                return $default;
            }

            return $value;
        }

        $retVal = self::getQb()
            ->select('*')
            ->from(self::$table)
            ->where('options_name = :paramname')
            ->setParameter(':paramname', $name)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);
        if ($retVal) {
            $retVal['options_value'] = Sanitation::maybeUnSerialize($retVal['options_value']);
            self::$options->set($name, $retVal);
            return $retVal;
        }

        self::$options->set($name, true);
        return $default;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param null $autoload
     * @return bool|int|null
     */
    public static function update($name, $value, $autoload = null)
    {
        if (!is_string($name)) {
            return null;
        }

        self::initOption();
        $exists = self::$options->has($name);
        if ($exists) {
            $exists = is_array(self::$options->get($name));
        }

        if ($exists) {
            $qb = self::getQb()
                ->update(self::$table)
                ->set('options_value', ':paramvalue')
                ->setParameter(':paramvalue', Sanitation::maybeSerialize($value));
            if (is_bool($autoload)) {
                $qb->set('options_autoload', ':paramautoload');
                $qb->setParameter(':paramautoload', ($autoload ? 'yes' : 'no'));
            }
            $stmt = $qb->where('options_name = :paramname')
                ->setParameter(':paramname', $name)
                ->execute();
            $opt = self::$options[$name];
            $opt['options_value'] = $value;
            $opt['autoload']     = $autoload;
            self::$options[$name] = $opt;
            unset($opt);
            if ($stmt instanceof Statement) {
                $count = $stmt->rowCount();
                $stmt->closeCursor();
                $stmt = $count;
            }
            return $stmt;
        }

        $autoload = $autoload === true ? 'yes' : 'no';
        $qb = self::getQb()
            ->insert(self::$table)
            ->values([
                'options_name' => ':paramname',
                'options_value' => ':paramvalue',
                'options_autoload' => ':paramautoload'
            ])->setParameters(
                [
                    ':paramname' => $name,
                    ':paramvalue' => Sanitation::maybeSerialize($value),
                    ':paramautoload' => $autoload
                ]
            );
        $stmt = $qb->execute();
        self::getDetail($name);
        if ($stmt instanceof Statement) {
            $count = $stmt->rowCount();
            $stmt->closeCursor();
            $stmt = $count;
        }
        return $stmt;
    }

    /**
     * @param array $args
     * @return bool|int
     */
    public static function updates(array $args)
    {
        if (empty($args)) {
            return false;
        }
        $success = 0;
        foreach ($args as $key => $value) {
            if (self::update($key, $value)) {
                $success++;
            }
        }

        return $success;
    }

    /**
     * Delete options
     *
     * @param string $name
     */
    public function delete($name)
    {
        if (self::has($name)) {
            $stmt = self::getQb()
                ->delete(self::$table)
                ->where('options_name=:paramname')
                ->setParameter(':paramname', $name)
                ->execute();
            if ($stmt instanceof Statement) {
                $stmt->closeCursor();
            }
            self::$options->set($name, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return self::has($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return self::getDetail($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        self::update($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        self::delete($offset);
    }

    /**
     * Magic Method Destruct
     */
    public function __destruct()
    {
        self::initOption();
        self::$options->clear();
    }
}
