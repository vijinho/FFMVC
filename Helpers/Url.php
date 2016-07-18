<?php

namespace FFMVC\Helpers;

/**
 * URL Helper Class
 *
 * @package helpers
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Url extends \Prefab
{
    /**
     * Build and return a URL
     * converting 2nd argument $params to querystring if array
     *
     * @param string $url name of the url alias
     * @param mixed $params url params as string or array
     * @param boolean $https force it to be https?
     * @return type
     */
    public static function external($url, $params = null, $https = true)
    {
        $f3 = \Base::instance();
        if (!empty($params)) {
            if (is_array($params)) {
                $params = http_build_query($params);
            }
            $url .= '?' . $params;
        }

        if (!empty($https)) {
            $p = strpos($url, 'http://');
            if ($p !== false) {
                $url = 'https://' . substr($url, 7);
            }
        }
        return $url;
    }

    /**
     * Return an f3 URL route by its alias or the given path
     * converting 2nd argument $params to querystring if array
     *
     * @param string $url name of the url alias
     * @param mixed $params url params as string or array
     * @param boolean $full default true
     * @return type
     */
    public static function internal($url, $params = null, $full = true)
    {
        $f3 = \Base::instance();
        if ('@' == $url[0]) {
            $url = $f3->alias(substr($url,1));
        }
        // missing slash at start of url path
        if ('/' !== substr($url, 0, 1)) {
            $url = '/' . $url;
        }
        if (!empty($params)) {
            if (is_array($params)) {
                $params = http_build_query($params);
            }
            $url .= '?' . $params;
        }
        return empty($full) ? $url : $f3->get('SCHEME') . '://' . $f3->get('HOST') . $url;
    }

}
