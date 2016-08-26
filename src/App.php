<?php

namespace FFMVC;

/**
 * Main App Class
 *
 * This should be included and run by every app upon initialisation and termination
 * It sets up the base environment for the app - should not create objects
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2016 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class App extends \Prefab
{
    /**
     * setup the base application environment.
     *
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
                        $langDir = 'templates/' . $language . '/' . $dir;
                        $dir     = 'templates/en/' . $dir;
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

            // enable full logging if not production
        $logfile = $f3->get('log.file');
        if (empty($logfile)) {
            $f3->set('log.file', '/dev/null');
        } elseif ('production' !== $f3->get('app.env')) {
            ini_set('log_errors', 'On');
            $logfile = $f3->get('LOGS') . $logfile;
            $f3->set('logfile', $logfile);
            ini_set('error_log', $logfile);
            ini_set('error_reporting', -1);
            ini_set('ignore_repeated_errors', 'On');
            ini_set('ignore_repeated_source', 'On');
        }

        // parse params for http-style dsn
        // setup database connection params
        // @see http://fatfreeframework.com/databases
        $httpDSN = $f3->get('db.dsn_http');
        if (!empty($httpDSN)) {
            $dbParams      = $f3->get('db');
            $params        = \FFMVC\Helpers\DB::instance()->parseHttpDsn($httpDSN);
            $params['dsn'] = \FFMVC\Helpers\DB::instance()->createDbDsn($params);
            $dbParams      = array_merge($dbParams, $params);
            $f3->set('db', $dbParams);
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

        // log errors if run on command line
        ini_set('display_errors', 'On');
        ini_set('error_log', 'On');
        ini_set('html_errors', 'Off');

        // set default error handler output for CLI mode
        $f3->set('ONERROR', function ($f3) {
            $e = $f3->get('ERROR');

                // detailed error notifications because it's not public
            $errorMessage = sprintf("Trace: %s\n\nException %d: %s\n%s\n",
                print_r($f3->trace(null, false), 1), $e['code'], $e['status'], $e['text']
            );

            echo $errorMessage;

            if (\Registry::exists('logger')) {
                $logger = \Registry::get('logger');
                if (is_object($logger)) {
                    $logger->write($errorMessage, $f3->get('log.date'));
                }
            }
        });
    }

    /**
     * tasks for the application once run.
     *
     * @return void
     */
    public static function finish()
    {
        // log script execution time if debugging
        $f3    = \Base::instance();
        $debug = $f3->get('DEBUG');

        if (\Registry::exists('logger')) {
            $logger = \Registry::get('logger');
        } else {
            $logger = null;
        }

        if (!empty($logger) && is_object($logger) && $debug || 'production' !== $f3->get('app.env')) {

            // log database transactions if level 3
            $db = \Registry::get('db');

            if (3 <= $debug &&
                method_exists($logger, 'write') &&
                method_exists($db, 'log')) {
                $logger->write($db->log(), $f3->get('log.date'));
            }

            $execution_time = round(microtime(true) - $f3->get('TIME'), 3);
            $params         = $f3->get('PARAMS');
            $params         = is_array($params) && !empty($params[0]) ? $params[0] : '';
            $logger->write('Script ' . $params . ' executed in ' . $execution_time . ' seconds using ' .
                round(memory_get_usage() / 1024 / 1024, 2) . '/' .
                round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB memory/peak', $f3->get('log.date'));
        }

        // http://php.net/manual/en/function.ob-end-flush.php
        while (ob_get_level()) {
            @ob_end_flush();
            @flush();
        }
    }
}
