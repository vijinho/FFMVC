<?php

namespace FFMVC\Models;

use \FFMVC\Helpers as Helpers;


/**
 * Base Model Class.
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
abstract class Base extends \Prefab
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
     * @var fat free database mapper
     * @url http://fatfreeframework.com/databases#CRUD(ButWithaLotofStyle)
     */
    protected $mapper = null;

    /**
     * @var object logging class
     */
    protected $logger;

    /**
     * initialize.
     */
    public function __construct($params = [])
    {
        $f3 = \Base::instance();
        foreach ($params as $k => $v) {
            $this->$k = $v;
        }
        if (empty($this->db)) {
            $this->db = \Registry::get('db');

            // guess the table name from the class name if not specified as a class member
            $table = strtolower(empty($this->table) ? $f3->snakecase(substr(get_class($this),
                            13)) : $this->table);
            $this->table = $table;

            // create a mapper in the f3 hive for the table
            $mapper = new \DB\SQL\Mapper($this->db, $table);
            $this->mapper = $mapper;
            $f3->set($f3->camelcase($table) . 'Mapper', $mapper);
        }
        if (empty($this->logger)) {
            $this->logger = &$f3->ref('logger');
        }
    }


    /**
     * Get row for field by given value
     *
     * @param string $field field to match
     * @param string $value value to match
     *
     * @return object
     */
    final public function getBy($field, $value)
    {
        return $this->mapper->load(['`' . $field . '` = ?', $value]);
    }
}


class BaseClientException extends \Exception
{

}


class BaseServerException extends \Exception
{

}
