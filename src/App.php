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

        // hive vars we are setting
        $hive = [];

        // set home directory for project
        $hive['HOMEDIR'] = realpath($f3->get('SERVER.DOCUMENT_ROOT') . '/../');

        // make sure directories are full, not relative path
        foreach (['LOGS', 'TEMP', 'UPLOADS'] as $key) {
            $dir = $f3->get($key);
            if (!empty($dir)) {
                $hive[$key] = realpath($dir) . '/';
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
                        if (!file_exists($langDir)) {
                            unset($langDir);
                        }
                        $dir     = 'templates/en/' . $dir;
                    }
                    $dirs[$k] = empty($langDir) ? realpath($dir) . '/'
                        : realpath($langDir) . '/' . ';' . realpath($dir) . '/';
                }
                $hive[$key] = join(';', $dirs);
            }
        }

            // enable full logging if not production
        $ini     = [];
        $logfile = $f3->get('log.file');
        if (empty($logfile)) {
            $hive['log']['file'] = '/dev/null';
        } elseif ('production' !== $f3->get('app.env')) {
            $hive['log']['file'] = $logfile;
            $ini                 = array_merge($ini, [
                'log_errors'             => 'On',
                'error_log'              => $logfile,
                'error_reporting'        => -1,
                'ignore_repeated_errors' => 'On',
                'ignore_repeated_source' => 'On',
            ]);
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
            $hive['db']    = $dbParams;
        }
        // setup outgoing email server for php mail command
        $email = $f3->get('email'); // email settings
        $ini   = array_merge($ini, [
            'SMTP'          => $email['host'],
            'sendmail_from' => $email['from'],
            'smtp_port'     => $email['port'],
            'user'          => $email['user'],
            'password'      => $email['pass'],
        ]);

        // multiple-set of $hive array to $f3 hive
        $f3->mset($hive);

        // CLI mode ends here
        if (empty($f3->get('CLI'))) {
            // set ini settings
            foreach ($ini as $value => $setting) {
                ini_set($value, $setting);
            }
            return;
        }

        // set ini settings
        // log errors if run on command line
        foreach (array_merge($ini, [
            'display_errors' => 'On',
            'error_log'      => 'On',
            'html_errors'    => 'Off',
        ]) as $value => $setting) {
            ini_set($value, $setting);
        }

        // set default error handler output for CLI mode
        $f3->set('ONERROR', function ($f3) {
            $error = $f3->get('ERROR');

                // detailed error notifications because it's not public
            $errorMessage = sprintf("Trace: %s\n\nException %d: %s\n%s\n",
                print_r($f3->trace(null, false), 1), $error['code'], $error['status'], $error['text']
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
        $f3     = \Base::instance();
        $debug  = $f3->get('DEBUG');
        $logger = \Registry::exists('logger') ? \Registry::get('logger') : null;

        if ('production' !== $f3->get('app.env') || !empty($logger) && $debug) {

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
            ob_end_flush();
            flush();
        }
    }
}
