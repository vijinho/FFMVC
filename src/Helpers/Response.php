<?php

namespace FFMVC\Helpers;

/**
 * HTTP Response Helper Class.
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Response extends \Prefab
{
    /**
     * Encode the input parameter $data as JSON and output it with appropriate http headers.
     *
     * @param mixed $data   input variable, takes origin, age, methods
     * @param array $params parameters for the http headers: ttl, origin, methods (GET, POST, PUT, DELETE)
     *
     * @return array<string,array|null|string>|null (array headers, string body)
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS
     * @see https://www.w3.org/TR/cors/
     */
    public static function json(array $data, array $params = [])
    {
        $f3 = \Base::instance();

        $body    = json_encode($data, JSON_PRETTY_PRINT);
        $headers = array_key_exists('headers', $params) ? $params['headers'] : [];

        // set ttl
        $ttl = (int) array_key_exists('ttl', $params) ? $params['ttl'] : 0; // cache for $ttl seconds

        $headers = array_merge($headers, [
            'Content-type'                     => 'application/json; charset=utf-8',
            'Expires'                          => Time::http(time() + $ttl),
            'Access-Control-Max-Age'           => $ttl,
            'Access-Control-Expose-Headers'    => array_key_exists('acl_expose_headers', $params) ? $params['acl_expose_headers'] : null,
            'Access-Control-Allow-Methods'     => array_key_exists('acl_http_methods', $params) ? $params['acl_http_methods'] : null,
            'Access-Control-Allow-Origin'      => array_key_exists('acl_origin', $params) ? $params['acl_origin'] : '*',
            'Access-Control-Allow-Credentials' => array_key_exists('acl_credentials', $params) && !empty($params['acl_credentials']) ?  'true' : 'false',
            'ETag'                             => array_key_exists('etag', $params) ? $params['etag'] : md5($body),
            'Content-Length'                   => \UTF::instance()->strlen($body),
        ]);

        // send the headers + data

        if (empty($ttl)) {
            $f3->expire(0);
            $ttl = 0;
        }

        // default status is 200 - OK
        $f3->status(array_key_exists('http_status', $params) ? $params['http_status'] : 200);

        // do not send session cookie
        if (!array_key_exists('cookie', $params)) {
            header_remove('Set-Cookie'); // prevent php session
        }

        ksort($headers);
        foreach ($headers as $header => $value) {
            if (!isset($value)) {
                continue;
            }
            header($header . ': ' . $value);
        }

        // HEAD request should be identical headers to GET request but no body
        if ('HEAD' !== $f3->get('VERB')) {
            echo $body;
        }
    }
}
