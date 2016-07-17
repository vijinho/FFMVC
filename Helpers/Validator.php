<?php

namespace FFMVC\Helpers;

/**
 * Validation Helper Class
 *
 * @package helpers
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Validator extends \GUMP
{
    /**
     *  A custom filter named "lower".
     *  The callback function receives two arguments:
     *  The value to filter, and any parameters used in the filter rule. It should returned the filtered value.
     *
     * @param type $value
     * @param type $param
     *
     * @return type
     */
    public function filter_lower($value, $param = null)
    {
        return strtolower($value);
    }
}
