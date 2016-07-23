<?php

namespace FFMVC\Models;


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
     * @var object logging class
     */
    protected $loggerObject;

    /**
     * initialize with array of params, 'logger' can be injected
     */
    public function __construct($params = [])
    {
        $f3 = \Base::instance();

        if (!array_key_exists('loggerObject', $params)) {
            $this->loggerObject = \Registry::get('logger');
        }

        foreach ($params as $k => $v) {
            $this->$k = $v;
        }
    }


    /**
     * Write to log
     *
     * @param mixed $data
     * @return bool true on success
     */
    public function log($data)
    {
        if (empty($this->loggerObject) || empty($data)) {
            return false;
        }
        if (is_string($data)) {
            $data = [$data];
        } elseif (is_object($data)) {
            $data = print_r($data, 1);
        } elseif (is_array($data)) {
            foreach ($data as $line) {
                $this->loggerObject->write($line);
            }
        }
        return true;
    }

}


class BaseException extends \Exception
{

}


class BaseClientException extends \Exception
{

}


class BaseServerException extends \Exception
{

}
