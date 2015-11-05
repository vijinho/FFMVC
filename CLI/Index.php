<?php

namespace FFMVC\CLI;

/**
 * Index CLI Class.
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Index extends Base
{
    final public function index($f3, $params)
    {
        $cli = $this->cli;
        $cli->shoutBold(__METHOD__);
        $cli->shout("Hello World!");
    }
}
