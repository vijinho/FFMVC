<?php

namespace FFMVC\Models;

use FFMVC\Traits as Traits;

/**
 * Base Model Class.
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
abstract class Base extends \Prefab
{
    use Traits\Logger;
    use Traits\Validation;

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
