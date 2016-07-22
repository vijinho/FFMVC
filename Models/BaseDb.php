<?php

namespace FFMVC\Models;


/**
 * Base Database Class extends f3's DB\SQL\Mapper
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 * @see https://fatfreeframework.com/sql
 */
abstract class BaseDb extends \DB\SQL
{

    /**
     * @var object database class
     */
    protected $db;

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
            $this->logger = \Registry::get('logger');
            unset($params['logger']);
        }

        if (!array_key_exists('db', $params)) {
            $this->db = \Registry::get('db');
            unset($params['db']);
        }

        foreach ($params as $k => $v) {
            $this->$k = $v;
        }
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
