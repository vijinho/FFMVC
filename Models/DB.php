<?php

namespace FFMVC\Models;

use FFMVC\Traits as Traits;
use FFMVC\Models\Mappers as Mappers;

/**
 * Base Database Class
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 * @see https://fatfreeframework.com/sql
 */
abstract class DB extends Base
{
    use Traits\Validation;

    /**
     * @var object database class
     */
    protected $db;

    /**
     * @var table in the db
     */
    protected $table;

    /**
     * @var mapper class name
     */
    protected $mapperClass;

    /**
     * @var mapper for class
     */
    protected $mapper;

    /**
     * initialize with array of params, 'db' and 'logger' can be injected
     */
    public function __construct($params = [])
    {
        $f3 = \Base::instance();

        if (!array_key_exists('logger', $params)) {
            $this->logger = \Registry::get('logger');
            unset($params['logger']);
        }

        if (!array_key_exists('db', $params)) {
            $this->db = \Registry::get('db');
            unset($params['db']);
        }

            // guess the table name
        $class = \UTF::instance()->substr(get_class($this), 13);
        $table = $f3->snakecase($class);
        $this->table = $table;

            // guess the mapper class
        $mapperClass = __NAMESPACE__ . "\\Mappers\\" . $class;
        if (class_exists($mapperClass)) {
            $this->mapper = new $mapperClass;
            $this->mapperClass = $mapperClass;
        }

        foreach ($params as $k => $v) {
            $this->$k = $v;
        }
    }

    /**
     * Get the associated mapper for the table
     * @return type
     */
    public function &getMapper()
    {
        return $this->mapper;
    }
}


class BaseDbException extends \Exception
{

}


class BaseDbClientException extends \Exception
{

}


class BaseDbServerException extends \Exception
{

}
