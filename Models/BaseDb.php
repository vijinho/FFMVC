<?php

namespace FFMVC\Models;

use \FFMVC\Helpers as Helpers;


/**
 * Base Database Model Class.
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
abstract class BaseDb extends \DB\SQL\Mapper
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

            // create a standard mapper in the f3 hive for the table
            $mapper = new \DB\SQL\Mapper($this->db, $table);
            $this->mapper = $mapper;
            $f3->set($f3->camelcase($table) . 'Mapper', $mapper);

            // also set this class as a mapper itself too with extra methods
            parent::__construct($this->db, $this->table);

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
     * @return object $mapper object
     */
    public function getBy($field, $value)
    {
        return $this->mapper->load(['`' . $field . '` = ?', $value]);
    }


    /**
     * Get a row by ID or UUID
     *
     * @param string $id
     *
     * @return array $data
     *
     * @throws BaseClientException
     * @throws BaseServerException
     */
    public function getById($id)
    {
        $db = \Registry::get('db');

        try {
            $sql = 'SELECT * FROM ' . $this->table . ' WHERE id = :id OR uuid = :id';
            $data = $db->exec($sql, [':id' => $id]);

            return empty($data) ? false : $data[0];
        } catch (\Exception $e) {
            if ($e instanceof BaseClientException) {
                throw new BaseDbServerException($e);
            } else {
                throw new BaseDbServerException($e);
            }
        }
    }


}


class BaseDbClientException extends \Exception
{

}


class BaseDbServerException extends \Exception
{

}
