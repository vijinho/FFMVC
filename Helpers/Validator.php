<?php

namespace FFMVC\Helpers;


/**
 * Validation Helper Class
 *
 * @package helpers
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 * @link https://github.com/Wixel/GUMP
 */
class Validator extends \GUMP
{

    /**
     *  A custom filter named "lower".
     *
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


    /**
     *  A custom filter named "upper".
     *
     *  The callback function receives two arguments:
     *  The value to filter, and any parameters used in the filter rule. It should returned the filtered value.
     *
     * @param type $value
     * @param type $param
     *
     * @return type
     */
    public function filter_upper($value, $param = null)
    {
        return strtoupper($value);
    }

    /**
     * Strip whitespaces from the beginning of a string
     *
     * The callback function receives two arguments:
     * The value to filter, and any parameters used in the filter rule. It should returned the filtered value.
     *
     * @param type $value
     * @param type $param
     *
     * @return type
     * @link https://fatfreeframework.com/utf-unicode-string-manager#ltrim
     */
    public function ltrim($value, $param = null)
    {
        return \UTF::instance()->ltrim($value);
    }

    /**
     * Strip whitespaces from the end of a string
     *
     * The callback function receives two arguments:
     * The value to filter, and any parameters used in the filter rule. It should returned the filtered value.
     *
     * @param type $value
     * @param type $param
     *
     * @return type
     * @link https://fatfreeframework.com/utf-unicode-string-manager#rtrim
     */
    public function rtrim($value, $param = null)
    {
        return \UTF::instance()->rtrim($value);
    }

    /**
     * Strip whitespaces from the beginning and end of a string
     *
     * The callback function receives two arguments:
     * The value to filter, and any parameters used in the filter rule. It should returned the filtered value.
     *
     * @param type $value
     * @param type $param
     *
     * @return type
     * @link https://fatfreeframework.com/utf-unicode-string-manager#trim
     */
    public function trim($value, $param = null)
    {
        return \UTF::instance()->trim($value);
    }

    /**
     * Convert code points to Unicode symbols
     *
     * The callback function receives two arguments:
     * The value to filter, and any parameters used in the filter rule. It should returned the filtered value.
     *
     * @param type $value
     * @param type $param
     *
     * @return type
     * @link https://fatfreeframework.com/utf-unicode-string-manager#translate
     */
    public function translate($value, $param = null)
    {
        return \UTF::instance()->translate($value);
    }

    /**
     * Translate emoji tokens to Unicode font-supported symbols
     *
     * The callback function receives two arguments:
     * The value to filter, and any parameters used in the filter rule. It should returned the filtered value.
     *
     * @param type $value
     * @param type $param
     *
     * @return type
     * @link https://fatfreeframework.com/utf-unicode-string-manager#emojify
     */
    public function emojify($value, $param = null)
    {
        return \UTF::instance()->emojify($value);
    }

}
