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
     * initialize.
     */
    public function __construct($params = [])
    {
        $f3 = \Base::instance();

        // inject class members
        foreach ($params as $k => $v) {
            $this->$k = $v;
        }
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
        $csrf = $f3->get('csrf');
        if ($csrf === false) {
            $url = Helpers\Url::instance()->internal($url, $params);
            $f3->reroute($url);
            return;
        } else {
            $csrf = Helpers\Str::salted(Helpers\Str::random(16), Helpers\Str::random(16), Helpers\Str::random(16));
            $f3->set('csrf', $csrf);
            $f3->set('SESSION.csrf', $csrf);
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        }
        return true;
    }


}
