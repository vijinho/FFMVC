<?php

namespace FFMVC\CLI;

use FFMVC\Helpers as Helpers;

/**
 * Base CLI Controller Class.
 *
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
abstract class Base
{
    /**
     * @var object database class
     */
    protected $db;

    /**
     * @var object logging objects
     */
    protected $loggerObject;

    /**
     * @var object user notifications objects
     */
    protected $notificationObject;

    /**
     * Use climate by default
     *
     * @var object cli-handling class
     * @link http://climate.thephpleague.com/
     */
    protected $cli;


    /**
     * initialize.
     */
    public function __construct($params = [])
    {
        if (PHP_SAPI !== 'cli') {
            exit("This controller can only be executed in CLI mode.");
        }

        $f3 = \Base::instance();

        // inject class members based on params
        foreach ($params as $k => $v) {
            $this->$k = $v;
        }

        // if we have none of the following setup, use defaults
        if (empty($this->db)) {
            $this->db = \Registry::get('db');
        }

        if (empty($this->notificationObject)) {
            $this->notificationObject = Helpers\Notifications::instance();
        }

        if (empty($this->loggerObject)) {
            $this->loggerObject = \Registry::get('logger');
        }

        if (empty($this->cli)) {
            $this->cli = new \League\CLImate\CLImate;
            $this->cli->clear();
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


    /**
     * Notify user
     *
     * @param mixed $data multiple messages by 'type' => [messages] OR message string
     * @param string $type type of messages OR null if multiple $data
     * @return boolean success
     */
    public function notify($data, $type = null)
    {
        if (is_array($data)) {
            return $this->notificationObject->addMultiple($data);
        } else {
            return $this->notificationObject->add($data, $type);
        }
    }


    public function beforeRoute($f3, $params)
    {
        $cli = $this->cli;
        $cli->blackBoldUnderline("CLI Script");
    }


    public function afterRoute($f3, $params)
    {
        $cli = $this->cli;
        $cli->shout('Finished.');
        $cli->info('Script executed in ' . round(microtime(true) - $f3->get('TIME'), 3) . ' seconds.');
        $cli->info('Memory used ' . round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB.');
    }
}
