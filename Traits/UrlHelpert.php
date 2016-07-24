<?php

namespace FFMVC\Traits;


trait UrlHelper
{

    /**
     * @var object url helper objects
     */
    protected $urlHelperObject;


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
}
