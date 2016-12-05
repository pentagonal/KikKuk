<?php
namespace KikKuk\Model\DataRetrieval;

/**
 * Interface DataSingleInterface
 * @package KikKuk\Model
 */
interface DataRetrievalInterface
{
    public function asc();
    public function rand();
    public function desc();

    public function order($by);
    public function group($by);

    public function offset($arg);
    public function limit($arg);
    public function fetch();
    public function fetchAll();

    /**
     * @return string
     */
    public function getQuery();
}
