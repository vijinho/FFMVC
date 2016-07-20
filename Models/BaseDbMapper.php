<?php

namespace FFMVC\Models;


/**
 * Base Database Mapper Class extends f3's DB\SQL\Mapper
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 * @url https://fatfreeframework.com/sql-mapper
 */
abstract class BaseDbMapper extends \DB\SQL\Mapper
{

    /**
     * @var object database class
     */
    protected $db;

    /**
     * @var table for the model
     */
    protected $table;

    /**
     * @var object logging class
     */
    protected $logger;

    /**
     * initialize with array of params, 'db' and 'logger' can be injected
     */
    public function __construct($params = [])
    {
        $f3 = \Base::instance();

        if (!array_key_exists('logger', $params)) {
            $this->logger = &$f3->ref('logger');
            unset($params['logger']);
        }

        if (!array_key_exists('db', $params)) {
            $this->db = \Registry::get('db');
            unset($params['db']);
        }

        // guess the table name from the class name if not specified as a class member
        parent::__construct($this->db,
            strtolower(empty($this->table) ? $f3->snakecase(substr(get_class($this),
                            13)) : $this->table));

        foreach ($params as $k => $v) {
            $this->$k = $v;
        }
    }


}


class BaseDbMapperException extends \Exception
{

}


class BaseDbMapperClientException extends \Exception
{

}


class BaseDbMapperServerException extends \Exception
{

}
