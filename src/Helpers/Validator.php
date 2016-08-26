<?php

namespace FFMVC\Helpers;

/**
 * Validation Helper Class
 *
 * Add to composer.json:
 *    "wixel/gump": "dev-master"
 *
 * @package helpers
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2016 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 * @url https://github.com/Wixel/GUMP
 */
class Validator extends \GUMP
{
    /**
     * Function to create and return previously created instance
     * Renamed from get_instance() to follow $f3 design pattern
     * as calling $this->get_instance() will ignore this class
     * and get a GUMP instance instead if this method did not exist
     *
     * @return Validator
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Perform data filtering against the provided ruleset.
     *
     * @param mixed $input
     * @param array optinal $ruleset ot use class ruleset
     * @return bool|array
     */
    public function filter(array $input, array $ruleset = [])
    {
        return empty($ruleset) ? parent::filter($input, $this->filter_rules) : parent::filter($input, $ruleset);
    }

    /**
     * Perform data validation against the provided ruleset.
     *
     * @param array $input
     * @param array optinal $ruleset ot use class ruleset
     * @return bool|array
     */
    public function validate(array $input, array $ruleset = [])
    {
        return empty($ruleset) ? parent::validate($input, $this->validation_rules) : parent::validate($input, $ruleset);
    }

    /**
     *  A custom filter named "lower".
     *
     *  The callback function receives two arguments:
     *  The value to filter, and any parameters used in the filter rule. It should returned the filtered value.
     *
     * @param mixed $value
     * @param mixed $param
     * @return string
     */
    public function filter_lower($value, $param = null): string
    {
        return strtolower($value);
    }

    /**
     *  A custom filter named "upper".
     *
     *  The callback function receives two arguments:
     *  The value to filter, and any parameters used in the filter rule. It should returned the filtered value.
     *
     * @param mixed $value
     * @param mixed $param
     * @return string
     */
    public function filter_upper($value, $param = null): string
    {
        return strtoupper($value);
    }

    /**
     * Strip whitespaces from the beginning of a string
     *
     * The callback function receives two arguments:
     * The value to filter, and any parameters used in the filter rule. It should returned the filtered value.
     *
     * @param mixed $value
     * @param mixed $param
     * @return string
     * @link https://fatfreeframework.com/utf-unicode-string-manager#ltrim
     */
    public function filter_ltrim($value, $param = null): string
    {
        return \UTF::instance()->ltrim($value);
    }

    /**
     * Strip whitespaces from the end of a string
     *
     * The callback function receives two arguments:
     * The value to filter, and any parameters used in the filter rule. It should returned the filtered value.
     *
     * @param mixed $value
     * @param mixed $param
     * @return string
     * @link https://fatfreeframework.com/utf-unicode-string-manager#rtrim
     */
    public function filter_rtrim($value, $param = null): string
    {
        return \UTF::instance()->rtrim($value);
    }

    /**
     * Strip whitespaces from the beginning and end of a string
     *
     * The callback function receives two arguments:
     * The value to filter, and any parameters used in the filter rule. It should returned the filtered value.
     *
     * @param mixed $value
     * @param mixed $param
     * @return string
     * @link https://fatfreeframework.com/utf-unicode-string-manager#trim
     */
    public function filter_trim($value, $param = null): string
    {
        return \UTF::instance()->trim($value);
    }

    /**
     * Convert code points to Unicode symbols
     *
     * The callback function receives two arguments:
     * The value to filter, and any parameters used in the filter rule. It should returned the filtered value.
     *
     * @param mixed $value
     * @param mixed $param
     * @return string
     * @link https://fatfreeframework.com/utf-unicode-string-manager#translate
     */
    public function filter_translate($value, $param = null): string
    {
        return \UTF::instance()->translate($value);
    }

    /**
     * Translate emoji tokens to Unicode font-supported symbols
     *
     * The callback function receives two arguments:
     * The value to filter, and any parameters used in the filter rule. It should returned the filtered value.
     *
     * @param mixed $value
     * @param mixed $param
     * @return string
     * @link https://fatfreeframework.com/utf-unicode-string-manager#emojify
     */
    public function filter_emojify($value, $param = null): string
    {
        return \UTF::instance()->emojify($value);
    }

    /**
     * Convert input to a slug
     *
     * The callback function receives two arguments:
     * The value to filter, and any parameters used in the filter rule. It should returned the filtered value.
     *
     * @param mixed $value
     * @param mixed $param
     * @return string
     * @link https://fatfreeframework.com/utf-unicode-string-manager#emojify
     */
    public function filter_slug($value, $param = null): string
    {
        return \Web::instance()->slug($value);
    }

    /**
     * Check whether the IP Address is Public
     *
     * Usage: '<index>' => 'valid_ip_public'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     *
     * @return mixed
     */
    public function validate_valid_ip_public(string $field, array $input, $param = null)
    {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }
        if (!\Audit::instance()->ispublic($input[$field])) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Check whether the IP Address is NOT Public
     *
     * Usage: '<index>' => 'valid_ip_not_public'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     * @return mixed
     */
    public function validate_valid_ip_not_public(string $field, array $input, $param = null)
    {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }
        if (\Audit::instance()->ispublic($input[$field])) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Check whether the IP Address is Reserved
     *
     * Usage: '<index>' => 'valid_ip_reserved'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     * @return mixed
     */
    public function validate_valid_ip_reserved(string $field, array $input, $param = null)
    {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }
        if (!\Audit::instance()->isreserved($input[$field])) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Check whether the IP Address is Private or Reserved
     *
     * Usage: '<index>' => 'valid_ip_private'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     * @return mixed
     */
    public function validate_valid_ip_private(string $field, array $input, $param = null)
    {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }
        if (!\Audit::instance()->isprivate($input[$field])) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

}
