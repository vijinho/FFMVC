<?php

namespace FFMVC\App;

/**
 * Main App Class
 *
 * This should be included and run by every app upon initialisation and termination
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Main extends \Prefab
{
    /**
     * setup the base application environment.
     *
     * @param object $logger inject your own logger
     */
    public static function start(&$f3, $logger = null)
    {
        // http://php.net/manual/en/function.ob-start.php
        ob_start();

        // read config and overrides
        // @see http://fatfreeframework.com/framework-variables#configuration-files
        $f3->config('config/default.ini');

        // by default this file does not exist, you must create it and include your local settings
        if (file_exists('config/config.ini')) {
            $f3->config('config/config.ini');
        }

        // set home directory for project
        $f3->set('HOME', realpath($f3->get('SERVER.DOCUMENT_ROOT') . '/../'));

        // make sure directories are full, not relative path
        foreach (['LOGS', 'TEMP', 'UPLOADS'] as $key) {
            $dir = $f3->get($key);
            if (!empty($dir)) {
                $dir = realpath($dir);
                $f3->set($key, $dir . '/');
            }
        }

        // these take multiple paths
        foreach (['LOCALES', 'UI'] as $key) {

            $paths = $f3->get($key);

                // remove any invalid dirs
            if (!empty($paths)) {

                $dirs = $f3->split($paths);

                if (count($dirs)) {
                    foreach ($dirs as $k => $dir) {
                        if (empty($dir)) {
                            unset($dirs[$k]);
                            continue;
                        }
                        $dirs[$k] = realpath($dir) . '/';
                    }
                    $f3->set($key, join(';', $dirs));
                }
            }
        }

        $debug = $f3->get('debug');

        // if logger wasn't injected use f3's logger
        if (empty($logger)) {
            // no logfile defined means no logging!
            $logfile = $f3->get('app.logfile');

            if (!empty($logfile)) {

                $logger = new \Log($logfile);

                    // enable full logging if not production
                if ('production' !== $f3->get('app.env')) {
                    ini_set('log_errors', true);
                    ini_set('error_log', $logfile);
                    ini_set('error_reporting', -1);
                }

                \Registry::set('logger', $logger);

            }
        }

        // setup outgoing email server for php mail command
        ini_set('SMTP', $f3->get('email.host'));
        ini_set('sendmail_from', $f3->get('email.from'));
        ini_set('smtp_port', $f3->get('email.port'));
        ini_set('user', $f3->get('email.user'));
        ini_set('password', $f3->get('email.pass'));

        // set default error handler output for CLI mode
        if (PHP_SAPI == 'cli') {

            $f3->set('ONERROR', function ($f3) {
                $e = $f3->get('ERROR');
                    // detailed error notifications because it's not public
                $errorMessage = sprintf("Exception %d: %s\n%s\n\n%s\n",
                    $e['code'], $e['status'], $e['text'], $e['trace']
                );

                $logger->write($errorMessage);
            });

            // fix for f3 not populating $_GET when run on the command line
            $uri = $f3->get('SERVER.REQUEST_URI');
            $querystring = preg_split("/&/", \UTF::instance()->substr($uri, 1 + \UTF::instance()->strpos($uri . '&', '?')));

            if (!empty($querystring) && count($querystring)) {

                foreach ($querystring as $pair) {

                    if (0 == count($pair)) {
                        continue;
                    }

                    $val = preg_split("/=/", $pair);

                    if (!empty($val) && count($val) == 2) {
                        $k = $val[0];
                        $v = $val[1];
                        if (!empty($k) && !empty($v)) {
                            $_GET[$k] = $v;
                        }
                    }
                }
                $f3->set('GET', $_GET);
            }
        }

        return $f3;
    }

    /**
     * tasks for the application once run.
     */
    public static function finish(&$f3)
    {
        // log script execution time if debugging
        $debug = $f3->get('DEBUG');
        $logger = \Registry::get('logger');

        if ($logger && $debug || 'production' !== $f3->get('app.env')) {

            // log database transactions if level 3
            $db = \Registry::get('db');

            if (3 <= $debug &&
                method_exists($logger, 'write') &&
                method_exists($db, 'log')) {
                $logger->write($db->log());
            }

            $execution_time = round(microtime(true) - $f3->get('TIME'), 3);
            $params = $f3->get('PARAMS');

            $logger->write('Script '.$params[0].' executed in '.$execution_time.' seconds using '.
                round(memory_get_usage() / 1024 / 1024, 2).'/'.
                round(memory_get_peak_usage() / 1024 / 1024, 2).' MB memory/peak');
        }

        // http://php.net/manual/en/function.ob-end-flush.php
        while (ob_get_level()) {
            @ob_end_flush();
            @flush();
        }
    }
}
