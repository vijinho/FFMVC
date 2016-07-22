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

    // example to test if already running
    // run cli.php '/index/running' in two different terminals
    final public function running($f3, $params)
    {
        $cli = $this->cli;
        $cli->shoutBold(__METHOD__);

        // use process id for log notifications
        $mypid = getmypid();
        $pid = $mypid['PID'];
        $log = \Registry::get('logger');
        $msg = $pid . ': Starting...';
        $cli->shout($msg);
        $log->write($msg);

        // check if already running, quit if so
        exec("ps auxww | grep -i index/running | grep -v grep", $ps);
        if (1 < count($ps)) {
            $msg = $pid . ': Already running! Quitting.';
            $cli->shout($msg);
            $log->write($ps[0]);
            $log->write($msg);
            return false;
        }
        sleep(10);
    }

}
