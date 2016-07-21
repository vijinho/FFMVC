<?php

namespace FFMVC\Controllers;

use FFMVC\Helpers as Helpers;


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
     * @var object database class
     */
    protected $db;

    /**
     * @var object user notificationsHelper class
     */
    protected $notificationsHelper;

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

        // inject class members
        foreach ($params as $k => $v) {
            $this->$k = $v;
        }

        // use defaults if missing
        if (empty($this->db)) {
            $this->db = \Registry::get('db');
        }
        if (empty($this->notificationsHelper)) {
            $this->notificationsHelper = Helpers\Notifications::instance();
        }
        if (empty($this->logger)) {
            $this->logger = &$f3->ref('logger');
        }
    }

    /**
     * Check for CSRF token, reroute if failed, otherwise generate new csrf token
     * Call this method from a controller method class to check and then set a new csrf token
     * then include $f3-get('csrf') as a hidden type in your form to be submitted
     *
     * @param type $url
     * @param type $params
     */
    final public function checkCSRF($url = '@home', $params = [])
    {
        $f3 = \Base::instance();
        $m = Helpers\Notifications::instance();
        $m->saveState();
        $csrf = $f3->get('csrf');
        if ($csrf === false) {
            $m->add("CSRF check failed.", 'danger');
            $url = Helpers\Url::instance()->internal($url, $params);
            $f3->reroute($url);
        } else {
            $csrf = Helpers\Str::salted(Helpers\Str::random(16), Helpers\Str::random(16), Helpers\Str::random(16));
            $f3->set('csrf', $csrf);
            $f3->set('SESSION.csrf', $csrf);
        }
    }

}
