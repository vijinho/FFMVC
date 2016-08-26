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
     * @param bool  $output send the output headers and body? or return them?
     *
     * @return array<string,array|null|string>|null (array headers, string body)
     *
     * @see http://www.w3.org/TR/2008/WD-access-control-20080912/
     */
    public static function json(array $data, array $params = [], bool $output = true)
    {
        $f3 = \Base::instance();

        $headers = array_key_exists('headers', $params) ? $params['headers'] : [];
        $headers['Content-type'] = 'application/json; charset=utf-8';

        $ttl = array_key_exists('ttl', $params) ? $params['ttl'] : 0; // cache for $ttl seconds
        if (empty($ttl)) {
            $f3->expire(0);
            //$headers['Cache-Control'] = 'no-store, no-cache, must-revalidate, max-age=0';
            $ttl = 0;
        }

        $headers['Expires'] = Time::http(array_key_exists('expires', $params) ? $params['expires'] : time() + $ttl);
        $headers['Access-Control-Max-Age'] = $ttl;
        $headers['Access-Control-Allow-Methods'] = array_key_exists('http_methods', $params) ? $params['http_methods'] : ''; //'OPTIONS, HEAD, GET, POST, PUT, PATCH, DELETE';
        $headers['Access-Control-Allow-Origin'] = array_key_exists('acl_origin', $params) ? $params['acl_origin'] : '*';
        $headers['Access-Control-Allow-Credentials'] = array_key_exists('acl_credentials', $params) ? $params['credentials'] : 'false';

        $body = json_encode($data, JSON_PRETTY_PRINT);
        $headers['ETag'] = array_key_exists('etag', $params) ? $params['etag'] : md5($body);
        $headers['Content-Length'] = \UTF::instance()->strlen($body);

        // if returning output data or sending a HEAD request...
        if (empty($output)) {
            if ('HEAD' == $f3->get('VERB')) {
                $body = null;
            }
            return ['headers' => $headers, 'body' => $body];
        }

        // do not send session cookie
        if (!array_key_exists('cookie', $params)) {
            header_remove('Set-Cookie'); // prevent php session
        }

        // default status is 200 - OK
        $f3->status(array_key_exists('http_status', $params) ? $params['http_status'] : 200);

        // send the headers + data
        foreach ($headers as $header => $value) {
            header($header.': '.$value);
        }

        // HEAD request should be identical headers to GET request but no body
        if ('HEAD' !== $f3->get('VERB')) {
            echo $body;
        }
    }
}
