<?php

namespace FFMVC\Helpers;

/**
 * String Helper Class
 *
 * @package helpers
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Str extends \Prefab
{
    /**
     * generate random string
     *
     * @param int $length of password
     * @param null|string $chars characters to use for random string
     * @return string password
     */
    public static function random(int $length = 10, string $chars = null): string
    {
        if (empty($chars)) {
            // ignore characters which can be consued, i, l, 1, o, O, 0 etc
            $chars = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWYZ';
        }

        $chars = str_shuffle($chars); // shuffle base character string
        $x = \UTF::instance()->strlen($chars) - 1;
        $str = '';

        for ($i = 0; $i < $length; $i++) {
            $str .= \UTF::instance()->substr($chars, rand(0, $x), 1);
        }

        return (string) $str;
    }


    /**
     * Generates a hash for a given string
     *
     * @param string $string to salt
     * @param string $pepper string pepper to add to the salted string for extra security
     * @return string $encoded
     * @link http://php.net/manual/en/function.hash-hmac.php
     * @link http://fatfreeframework.com/base#hash
     */
    public static function salted(string $string, string $pepper = ''): string
    {
        $f3 = \Base::instance();
        $salt = $f3->get('security.salt');
        $hash = $f3->get('security.hash');

        return base64_encode(hash_hmac($hash, $string, $salt . $pepper, true));
    }

    /**
     * Generates a hashed password a given string
     *
     * @param string $string to salt
     * @param string $pepper string pepper to add to the salted string for extra security
     * @return string $encoded
     */
    public static function password(string $string, string $pepper = ''): string
    {
        return \Base::instance()->hash(self::salted($string, $pepper));
    }


    /**
     * Compares a hashed password with the hashed value of a given string
     *
     * @param string $hashed_password a hashed password
     * @param string $string to salt
     * @param string $pepper string pepper to add to the salted string for extra security
     * @return string success on match
     */
    public static function passwordVerify(string $hashed_password, string $string, string $pepper = ''): string
    {
        return ($hashed_password === \Base::instance()->hash(self::salted($string, $pepper)));
    }


    /**
     * Generate name based md5 UUID (version 3).
     * @example '7e57d004-2b97-0e7a-b45f-5387367791cd'
     * @copyright Copyright (c) 2011 François Zaninotto and others
     * @url https://github.com/fzaninotto/Faker
     * @return string $uuid
     */
    public static function uuid(): string
    {
        // fix for compatibility with 32bit architecture; seed range restricted to 62bit
        $seed = mt_rand(0, 2147483647) . '#' . mt_rand(0, 2147483647);

        // Hash the seed and convert to a byte array
        $val = md5($seed, true);
        $byte = array_values(unpack('C16', $val));

        // extract fields from byte array
        $tLo = ($byte[0] << 24) | ($byte[1] << 16) | ($byte[2] << 8) | $byte[3];
        $tMi = ($byte[4] << 8) | $byte[5];
        $tHi = ($byte[6] << 8) | $byte[7];
        $csLo = $byte[9];
        $csHi = $byte[8] & 0x3f | (1 << 7);

        // correct byte order for big edian architecture
        if (pack('L', 0x6162797A) == pack('N', 0x6162797A)) {
            $tLo = (($tLo & 0x000000ff) << 24) | (($tLo & 0x0000ff00) << 8)
                | (($tLo & 0x00ff0000) >> 8) | (($tLo & 0xff000000) >> 24);
            $tMi = (($tMi & 0x00ff) << 8) | (($tMi & 0xff00) >> 8);
            $tHi = (($tHi & 0x00ff) << 8) | (($tHi & 0xff00) >> 8);
        }

        // apply version number
        $tHi &= 0x0fff;
        $tHi |= (3 << 12);

        // cast to string
        $uuid = sprintf(
            '%08x-%04x-%04x-%02x%02x-%02x%02x%02x%02x%02x%02x',
            $tLo,
            $tMi,
            $tHi,
            $csHi,
            $csLo,
            $byte[10],
            $byte[11],
            $byte[12],
            $byte[13],
            $byte[14],
            $byte[15]
        );

        return $uuid;
    }

    /**
     * Deserialize a value as an object or array if serialized
     *
     * @param mixed $value
     */
    public static function deserialize($value)
    {
        // first try to unserialize php object
        $v = @unserialize($value); // object if success

            // next try to json_decode - results in array
        if (empty($v) || !is_object($v)) {
            $v = json_decode($value, true);
        }

        // update value to unserialized object/array if necessary
        if (is_object($v) || is_array($v)) {
            return $v;
        }

        return $value;
    }
}
