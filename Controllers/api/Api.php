<?php

namespace FFMVC\Controllers\API;

use \FFMVC\Helpers as Helpers;
use \FFMVC\Models as Models;

/**
 * Api Controller Class.
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class API
{
    /**
     * version.
     *
     * @var version
     */
    protected $version;

    /**
     * response errors
     * 1xx: Informational - Transfer Protocol Information
     * 2xx: Success - Client's request successfully accepted
     *     - 200 OK, 201 - Created, 202 - Accepted, 204 - No Content (purposefully)
     * 3xx: Redirection - Client needs additional action to complete request
     *     - 301 - new location for resource
     *     - 304 - not modified
     * 4xx: Client Error - Client caused the problem
     *     - 400 - Bad request - nonspecific failure
     *     - 401 - unauthorised
     *     - 403 - forbidden
     *     - 404 - not found
     *     - 405 - method not allowed
     *     - 406 - not acceptable (e.g. not in correct format like json)
     * 5xx: Server Error - The server was responsible.
     *
     * @var errors
     */
    protected $errors = array();

    /**
     * response data.
     *
     * @var data
     */
    protected $data = array();

    /**
     * response params.
     *
     * @var params
     */
    protected $params = array();

    /**
     * response helper.
     *
     * @var response
     */
    protected $response;

    /**
     * db.
     *
     * @var db
     */
    protected $db;

    /**
     * Error format required by RFC6794.
     *
     * @var type
     * @link https://tools.ietf.org/html/rfc6749
     */
    protected $OAuthErrorTypes = array(
        'invalid_request' => array(
            'code' => 'invalid_request',
            'description' => 'The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed.',
            'uri' => '',
            'state' => '',
        ),
        'invalid_credentials' => array(
            'code' => 'invalid_credentials',
            'description' => 'Credentials for authentication were invalid.',
            'uri' => '',
            'state' => '',
        ),
        'invalid_client' => array(
            'code' => 'invalid_client',
            'description' => 'Client authentication failed (e.g., unknown client, no client authentication included, or unsupported authentication method).',
            'uri' => '',
            'state' => '',
        ),
        'invalid_grant' => array(
            'code' => 'invalid_grant',
            'description' => 'The provided authorization grant (e.g., authorization code, resource owner credentials) or refresh token is invalid, expired, revoked, does not match the redirection URI used in the authorization request, or was issued to another client.',
            'uri' => '',
            'state' => '',
        ),
        'unsupported_grant_type' => array(
            'code' => 'unsupported_grant_type',
            'description' => 'The authorization grant type is not supported by the authorization server.',
            'uri' => '',
            'state' => '',
        ),
        'unauthorized_client' => array(
            'code' => 'unauthorized_client',
            'description' => 'The client is not authorized to request an authorization code using this method.',
            'uri' => '',
            'state' => '',
        ),
        'access_denied' => array(
            'code' => 'access_denied',
            'description' => 'The resource owner or authorization server denied the request.',
            'uri' => '',
            'state' => '',
        ),
        'unsupported_response_type' => array(
            'code' => 'unsupported_response_type',
            'description' => 'The authorization server does not support obtaining an authorization code using this method.',
            'uri' => '',
            'state' => '',
        ),
        'invalid_scope' => array(
            'code' => 'invalid_scope',
            'description' => 'The requested scope is invalid, unknown, or malformed.',
            'uri' => '',
            'state' => '',
        ),
        'server_error' => array(
            'code' => 'server_error',
            'description' => 'The authorization server encountered an unexpected condition that prevented it from fulfilling the request.',
            'uri' => '',
            'state' => '',
        ),
        'temporarily_unavailable' => array(
            'code' => 'temporarily_unavailable',
            'description' => 'The authorization server is currently unable to handle the request due to a temporary overloading or maintenance of the server.',
            'uri' => '',
            'state' => '',
        ),
    );

    /**
     * The OAuth Error to return if an OAuthError occurs.
     *
     * @var array OAuthError
     */
    protected $OAuthError = null;

    /**
     * initialize.
     */
    public function __construct()
    {
        $f3 = \Base::instance();
        $this->db = \Registry::get('db');
        $this->response = Helpers\Response::instance();
        $this->version = $f3->get('app.version');
        $params = $f3->get('PARAMS');

        // paging options
        $page = (int) $f3->get('REQUEST.page');

        if (0 >= $page) {
            $page = null;
        }

        $f3->set('REQUEST.page', $page);

        $per_page = (int) $f3->get('REQUEST.per_page');

        if (0 >= $per_page) {
            $per_page = null;
        }

        $f3->set('REQUEST.per_page', $per_page);

        // 1 - reverse, 0 - ascending
        $f3->set('REQUEST.sort_direction', (int) !empty($f3->get('REQUEST.sort_direction')));
    }

    /**
     * compile and send the json response.
     */
    public function afterRoute($f3, $params)
    {
        $version = (int) $f3->get('REQUEST.version');

        if (empty($version)) {
            $version = $this->version;
        }
/*
        if ($version !== $this->version) {
            $this->failure(4999, 'Unknown API version requested.', 400);
        }
*/
        if (!is_array($this->data)) {
            $this->data = array($this->data);
        }

        if (!array_key_exists('href', $this->data) || empty($data['href'])) {
            $data['href'] = $this->href();
        }

        $data = array(
            'service' => 'API',
            'api' => $version,
            'method' => $f3->get('VERB'),
            'time' => time(),
        ) + $this->data;

        // if an OAuthError is set, return that too
        if (!empty($this->OAuthError)) {
            $data['error'] = $this->OAuthError;
        }

        if (count($this->errors)) {
            ksort($this->errors);
            $data['errors'] = $this->errors;
        }

        $return = $f3->get('REQUEST.return');

        switch ($return) {
            case 'xml':
                $this->response->xml($data, $this->params);
                break;

            default:
                case 'json':
                $this->response->json($data, $this->params);
        }
    }

    /**
     * add to the list of errors that occured during this request.
     *
     * @param int    $code        the error code
     * @param string $message     the error message
     * @param int    $http_status the http status code
     */
    public function failure($code, $message, $http_status = null)
    {
        $this->errors[$code] = $message;

        if (!empty($http_status)) {
            $this->params['http_status'] = $http_status;
        }
    }

    /**
     * Get OAuth Error Type.
     *
     * @param type $type
     *
     * @return mixed array error type or boolean false
     */
    protected function getOAuthErrorType($type)
    {
        return array_key_exists($type, $this->OAuthErrorTypes) ? $this->OAuthErrorTypes[$type] : false;
    }

    /**
     * Set the RFC-compliant OAuth Error to return.
     *
     * @param type $code  of error code from RFC
     * @param type $state optional application state
     *
     * @throws Models\ApiServerException
     *
     * @return the OAuth error array
     */
    public function setOAuthError($code, $state = null)
    {
        $error = $this->getOAuthErrorType($code);

        if (empty($error)) {

            throw new Models\ApiServerException('Invalid OAuth error type.', 5100);

        } else {

            if (empty($state)) {
                unset($error['state']);
            } else {
                $error['state'] = $state;
            }

            switch ($code) {

                case 'invalid_client': // as per-spec
                    $this->params['http_status'] = 401;
                    break;

                case 'server_error':
                    $this->params['http_status'] = 500;
                    break;

                case 'invalid_credentials':
                    $this->params['http_status'] = 403;
                    break;

                default:
                    $this->params['http_status'] = 400;
                    break;
            }
            $this->OAuthError = $error;
        }

        return $error;
    }

    /**
     * Basic Authentication for email:password
     * Check that the credentials match the database
     * Cache result for 10 seconds.
     *
     * @return bool success/failure
     */
    public function basicAuthenticateLoginPassword()
    {
        $f3 = \Base::instance();

        $auth = new \Auth(new \DB\SQL\Mapper(\Registry::get('db'), 'users', array('email', 'password'), 10), array(
            'id' => 'email',
            'pw' => 'password',
        ));

        $hash = function ($pw) use ($f3) {
            return Helpers\Str::password($pw);
        };

        return (int) $auth->basic($hash);
    }

    /**
     * Basic Authentication for developer email:token
     * Check that the credentials match the database.
     *
     * @return bool success/failure
     */
    protected function basicAuthenticateLoginToken($f3, $params)
    {
    }

    /**
     * Validate the provided access token or get the bearer token from the incoming http request
     * do $f3->set('access_token') if OK.
     *
     * Or login using app token with HTTP Auth using one of
     *
     * email:password
     * email:access_token
     *
     * Or by URL query string param - ?access_token=$access_token
     *
     * @param string $token the token to validate, or get from the REQUEST
     *
     * @return false or array the token
     */
    protected function validateAccess($token = null)
    {
        $f3 = \Base::instance();
        $model = new Models\Users;

        $token = $f3->get('REQUEST.access_token');

        if (!empty($token)) {
            $access_token = $model->verifyBearerAccessToken($token);
        }

        // check if login via http auth
        $phpAuthUser = $f3->get('REQUEST.PHP_AUTH_USER');

        if (!empty($phpAuthUser)) {

                // try to login as email:password
            if (empty($access_token)) {

                $userLogin = $this->basicAuthenticateLoginPassword();
                if (!empty($userLogin)) {
                    $access_token = $model->getAccessTokenByEmail($phpAuthUser);
                }

            }
        }

         // access token found from basic-auth creds
        if (!empty($access_token)) {
            $f3->set('REQUEST.access_token', $access_token);
        }

        // get token from request
        if (empty($token)) {
            $token = $f3->get('REQUEST.access_token');
        }

        if (empty($token)) {
            // one last try, try if auth is via OAuth Token
            // $ curl -u '<email>:<token>' https://f3-cms.local/api/user
            $user = $f3->get('REQUEST.PHP_AUTH_USER');

            if (!empty($user)) {

                $access_token = $model->getAccessTokenByEmail($user);

                if (!empty($access_token)) {
                    $token = $f3->get('REQUEST.PHP_AUTH_PW');
                    $token = ($token == $access_token) ? $token : null;
                }
            }

            if (empty($token)) {
                $this->setOAuthError('invalid_request');
                $this->failure(4007, 'Missing bearer access token', 400);

                return false;
            }
        }

        // @todo: some user account checks here to make sure is allowed to make requests

        // we now have a valid access token for f3
        if (!empty($access_token)) {
            $f3->set('access_token', $access_token);
        }

        return $access_token;
    }

// unknown catch-all api method
    public function unknown($f3, $params)
    {
        $this->failure(4998, 'Unknown API Request', 400);
    }

// set relative URL
    protected function rel($path)
    {
        $f3 = \Base::instance();
        $this->data['rel'] = Helpers\Url::internal($path);
        return;
    }

// set canonical URL
    protected function href($path = null)
    {
        $f3 = \Base::instance();

        if (empty($path)) {
            $this->data['href'] = $f3->get('REALM');
        } else {
            $this->data['href'] = Helpers\Url::internal($path);
        }

        return;
    }

    /**
     * page the results
     *
     * @param type $data
     * @return type
     */
    protected function page_results($data)
    {

        $f3 = \Base::instance();

        $page = $f3->get('REQUEST.page');
        if (empty($page)) {
            return $data;
        }

        // minimum 5 results per page
        $per_page = $f3->get('REQUEST.per_page');
        if (empty($per_page) || 5 > $per_page) {
            $per_page = 5;
        }

        if ($per_page > count($data)) {
            $per_page = count($data);
        }

        $sort_direction = $f3->get('REQUEST.sort_direction');
        if (!empty($sort_direction)) {
            $data = array_reverse($data);
        }

        // calculate which section of results to return
        $pages = ceil(count($data) / $per_page);

        if ($page > $pages) {
            $page = $pages;
        } else if (1 >= $pages) {
            $per_page = count($data);
        }

        $to = ($page * $per_page);
        $from = ($to - $per_page) + 1;

        $page_first = $page_last = $page_next = \Helpers\Url::instance()->internal($f3->get('PATH')) . '?';

        $url_params = array(
            'per_page' => $per_page,
            'sort_direction' => $sort_direction
        );

        $page_first .= http_build_query($url_params + array('page' => 1));
        $page_last .= http_build_query($url_params + array('page' => $pages));

        $n = $page + 1;

        if ($n < $pages) {
            $page_next .= http_build_query($url_params + array('page' => $n));
        } else {
            $page_next = '';
        }

        $p = $page - 1;

        if (0 < $p) {
            $page_previous .= http_build_query($url_params + array('page' => $p));
        } else {
            $page_previous = '';
        }

        $this->data['paging'] = array(
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => $pages,
            'sort_direction' => $sort_direction,
            'total_results' => count($data),
            'results_from' => $from,
            'results_to' => $to,
            'page_next' => $page_next,
            'page_previous' => $page_previous,
            'page_first' => $page_first,
            'page_last' => $page_last
        );

        return array_slice($data, $from, $per_page);
    }

    // route /api
    public function api($f3, $params)
    {
        $this->params['http_methods'] = 'GET,HEAD';
        $this->rel('/api');
    }

    public function user($f3, $params)
    {
        $this->validateAccess();

        $this->params['http_methods'] = 'GET,HEAD';
        $this->data = [
            'access_token' => $f3->get('access_token')
        ];
    }

}
