<?php

namespace FFMVC\Traits;


trait CSRF
{
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
