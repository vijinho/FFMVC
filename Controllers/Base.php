<?php

namespace FFMVC\Controllers;

use FFMVC\Helpers as Helpers;
use FFMVC\Models as Models;

/**
 * Base Controller Class.
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
abstract class Base
{
    /**
     * @var object logging objects
     */
    protected $loggerObject;

    /**
     * @var object user notifications objects
     */
    protected $notificationObject;

    /**
     * @var object url helper objects
     */
    protected $urlHelperObject;


    /**
     * initialize.
     */
    public function __construct($params = [])
    {
        $f3 = \Base::instance();

        if (!array_key_exists('loggerObject', $params)) {
            $this->loggerObject = \Registry::get('logger');
        }

        if (!array_key_exists('notificationObject', $params)) {
            $this->notificationObject = Helpers\Notifications::instance();
        }

        if (!array_key_exists('urlHelperObject', $params)) {
            $this->urlHelperObject = Helpers\Url::instance();
        }

        // inject class members
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


    /**
     * Notify user
     *
     * @param mixed $data multiple messages by 'type' => [messages] OR message string
     * @param string $type type of messages (success, danger, warning, info) OR null if multiple $data
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


    /**
     * Create an internal URL
     *
     * @param type $url
     * @param array $params
     */
    public function url($url, array $params = [])
    {
        return $this->urlHelperObject->internal($url, $params);
    }


    /**
     * Create an external URL
     *
     * @param type $url
     * @param array $params
     */
    public function xurl($url, array $params = [], $https = true)
    {
        return $this->urlHelperObject->external($url, $params, $https);
    }



    /**
     * Check for CSRF token, reroute if failed, otherwise generate new csrf token
     * Call this method from a controller method class to check and then set a new csrf token
     * then include $f3-get('csrf') as a hidden type in your form to be submitted
     *
     * @param string $url if csrf check fails
     * @param array $params for querystring
     * @return boolean true/false if csrf enabled
     */
    public function checkCSRF($url = '@index', $params = [])
    {
        $f3 = \Base::instance();
        if (empty($f3->get('app.csrf_enabled'))) {
            return false;
        }
        // redirect user if it's not a POST request
        if ('POST' !== $f3->get('VERB')) {
            $f3->reroute($url);
        }
        $csrf = $f3->get('csrf');
        if ($csrf === false) {
            $url = $this->url($url, $params);
            $f3->reroute($url);
            return;
        } else {
            $csrf = Helpers\Str::salted(Helpers\Str::random(16), Helpers\Str::random(16), Helpers\Str::random(16));
            $f3->set('csrf', $csrf);
            $f3->set('SESSION.csrf', $csrf);
            $f3->expire(0);
        }
        return true;
    }


}
