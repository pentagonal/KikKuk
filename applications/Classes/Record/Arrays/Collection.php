<?php
namespace KikKuk\Record\Arrays;

use KikKuk\Interfaces\Record\ArrayInterface;

/**
 * Class Collection
 * @package KikKuk\Record\Arrays
 */
class Collection implements ArrayInterface
{
    /**
     * Collection Data
     *
     * @var array
     */
    protected $storedData = [];

    /**
     * Collector constructor.
     * @param array $args
     */
    public function __construct(array $args = [])
    {
        $this->replace($args);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->storedData[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function replace(array $items)
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return array_key_exists($key, $this->storedData);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        return isset($this->storedData[$key]) ? $this->storedData[$key] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->storedData;
    }

    /**
     * @return array
     */
    public function keys()
    {
        return array_keys($this->storedData);
    }

    /**
     * {@inheritdoc}
     */
    public function first()
    {
        return reset($this->storedData);
    }

    /**
     * {@inheritdoc}
     */
    public function last()
    {
        return end($this->storedData);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->storedData);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->storedData);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        return next($this->storedData);
    }

    /**
     * {@inheritdoc}
     */
    public function prev()
    {
        return prev($this->storedData);
    }

    /**
     * {@inheritdoc}
     */
    public function push($keyName, $value = null)
    {
        if (func_num_args() > 1) {
            $this->remove($keyName);
            $this->set($keyName, $value);
        } else {
            array_push($this->storedData, $keyName);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        array_pop($this->storedData);
    }

    /**
     * {@inheritdoc}
     */
    public function shift()
    {
        array_shift($this->storedData);
    }

    /**
     * {@inheritdoc}
     */
    public function unShift($keyName, $value = null)
    {
        if (func_num_args() > 1) {
            $this->remove($keyName);
            $this->storedData = [$keyName => $value] + $this->storedData;
        } else {
            array_unshift($this->storedData, $keyName);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function filter($values)
    {
        $returnValue = [];
        foreach ($this->storedData as $key => $value) {
            if ($value === $value) {
                $returnValue[$key] = $value;
            }
        }

        return $returnValue;
    }

    /**
     * {@inheritdoc}
     */
    public function contain($values)
    {
        foreach ($this->storedData as $key => $value) {
            if ($value === $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function indexOf($values)
    {
        return array_search($values, $this->storedData, true);
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return empty($this->storedData);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        unset($this->storedData[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->storedData = [];
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->storedData);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * Returning @uses \Traversable instance
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->storedData);
    }
}
