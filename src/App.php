<?php

namespace FFMVC;

/**
 * Main App Class
 *
 * This should be included and run by every app upon initialisation and termination
 * It sets up the base environment for the app - should not create objects
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class App extends \Prefab
{
    /**
     * setup the base application environment.
     *
     * @param \Base $f3
     * @return void
     */
    public static function start()
    {
        $f3 = \Base::instance();

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
        $f3->set('HOMEDIR', realpath($f3->get('SERVER.DOCUMENT_ROOT') . '/../'));

        // make sure directories are full, not relative path
        foreach (['LOGS', 'TEMP', 'UPLOADS'] as $key) {
            $dir = $f3->get($key);
            if (!empty($dir)) {
                $dir = realpath($dir);
                $f3->set($key, $dir . '/');
            }
        }


        // these take multiple paths
        $language = $f3->get('LANGUAGE');
        foreach (['LOCALES', 'UI'] as $key) {
            $paths = $f3->get($key);
                // remove any invalid dirs
            if (!empty($paths)) {

                $dirs = $f3->split($paths);
                foreach ($dirs as $k => $dir) {
                    if (empty($dir)) {
                        unset($dirs[$k]);
                        continue;
                    }
                    // switch for different language templates
                    $langDir = '';
                    if ('UI' == $key) {
                        $langDir = 'templates/' . $language . '/' .  $dir;
                        $dir = 'templates/en/' .  $dir;
                        if (!file_exists($langDir)) {
                            unset($langDir);
                        }
                    }
                    if (!empty($langDir)) {
                        $dirs[$k] = realpath($langDir) . '/' . ';' . realpath($dir) . '/';
                    } else {
                        $dirs[$k] = realpath($dir) . '/';
                    }
                }
                $f3->set($key, join(';', $dirs));
            }
        }

        $debug = $f3->get('debug');

        // default cacheable data time in seconds from config
        $ttl = $f3->get('app.ttl');

            // enable full logging if not production
       $logfile = $f3->get('app.logfile');
        if (!empty($logfile)) {
            if ('production' !== $f3->get('app.env')) {
                ini_set('log_errors', 'On');
                ini_set('error_log', $logfile);
                ini_set('error_reporting', -1);
            }
        }

        // parse params for http-style dsn
        // setup database connection params
        // @see http://fatfreeframework.com/databases
        $httpDSN = $f3->get('db.http_dsn');
        if (!empty($httpDSN)) {
            $params = \FFMVC\Helpers\DB::instance()->parseHttpDsn($httpDSN);
            $f3->mset([
                'db.driver' => $params['driver'],
                'db.host'   => $params['host'],
                'db.port'   => $params['port'],
                'db.name'   => $params['name'],
                'db.user'   => $params['user'],
                'db.pass'   => $params['pass'],
            ]);
            $f3->set('db.dsn', \FFMVC\Helpers\DB::instance()->createDbDsn($params), $ttl);
        }

        // setup outgoing email server for php mail command
        ini_set('SMTP', $f3->get('email.host'));
        ini_set('sendmail_from', $f3->get('email.from'));
        ini_set('smtp_port', $f3->get('email.port'));
        ini_set('user', $f3->get('email.user'));
        ini_set('password', $f3->get('email.pass'));

        if (PHP_SAPI !== 'cli') {
            return;
        }

        // set default error handler output for CLI mode
        $f3->set('ONERROR', function ($f3) {
            $e = $f3->get('ERROR');

                // detailed error notifications because it's not public
            $errorMessage = sprintf("Trace: %s\n\nException %d: %s\n%s\n",
                $e['trace'], $e['code'], $e['status'], $e['text']
            );

            echo $errorMessage;

            $logger = \Registry::get($logger);
            if (is_object($logger)) {
                $logger->write($errorMessage, $f3->get('app.logdate'));
            }
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

    /**
     * tasks for the application once run.
     *
     * @return void
     */
    public static function finish()
    {
        // log script execution time if debugging
        $f3 = \Base::instance();
        $debug = $f3->get('DEBUG');
        $logger = \Registry::get('logger');

        if ($logger && $debug || 'production' !== $f3->get('app.env')) {

            // log database transactions if level 3
            $db = \Registry::get('db');

            if (3 <= $debug &&
                method_exists($logger, 'write') &&
                method_exists($db, 'log')) {
                $logger->write($db->log(), $f3->get('app.logdate'));
            }

            $execution_time = round(microtime(true) - $f3->get('TIME'), 3);
            $params = $f3->get('PARAMS');

            $logger->write('Script '.$params[0].' executed in '.$execution_time.' seconds using '.
                round(memory_get_usage() / 1024 / 1024, 2).'/'.
                round(memory_get_peak_usage() / 1024 / 1024, 2).' MB memory/peak', $f3->get('app.logdate'));
        }

        // http://php.net/manual/en/function.ob-end-flush.php
        while (ob_get_level()) {
            @ob_end_flush();
            @flush();
        }
    }
}
