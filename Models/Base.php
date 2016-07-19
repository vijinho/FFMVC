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
        if (empty($this->logger)) {
            $this->logger = &$f3->ref('logger');
        }
    }


}


class BaseClientException extends \Exception
{

}


class BaseServerException extends \Exception
{

}
