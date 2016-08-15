<?php

namespace FFMVC\Helpers;

/**
 * Db Helper Class
 *
 * @package helpers
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class DB extends \Prefab
{
    /**
     * Parse a http-style dsn and return parts as an array
     *
     * @param string $httpDSN http-style dsn, e.g mysql://root:root@127.0.0.1:3306/development_db
     * @return array string of http dsn parameters, e.g.
     * {
     *      ["driver"]=> string(5) "mysql"
     *      ["scheme"] => string(5) "mysql"
     *      ["host"]=> string(9) "127.0.0.1"
     *      ["port"]=> int(3306)
     *      ["user"]=> string(4) "root"
     *      ["pass"]=> string(4) "root"
     *      ["name"]=> string(14) "development_db"
     *      ["path"]=> string(14) "development_db"
     * }
     * @see http://php.net/parse_url
     */
    public static function parseHttpDsn(string $httpDSN): array
    {
        $m = parse_url($httpDSN);
        if (false == $m) {
            throw new \FFMVC\InvalidArgumentException('Invalid DSN setting');
        }
        $m['path'] = substr($m['path'], 1);
        $m['name'] = $m['path'];
        $m['port'] = empty($m['port']) ? 3306 : (int) $m['port'];
        $m['driver'] = $m['scheme'];
        return $m;
    }


    /**
     * Return an f3 (pdo) dsn based on config params OR existing dsn param if set
     *
     * @param array $config e.g.
     * {
     *      ["driver"]=> string(5) "mysql"
     *      ["host"]=> string(9) "127.0.0.1"
     *      ["port"]=> int(3306)
     *      ["name"]=> string(14) "development_db"
     * }
     * @return string $dsn e.g. mysql:host=127.0.0.1;port=3306;dbname=development_db
     * @see http://php.net/manual/en/class.pdo.php
     */
    public static function createDbDsn(array $config): string
    {
        if (array_key_exists('dsn', $config)) {
            return $config['dsn'];
        } else {
            return sprintf('%s:host=%s;port=%d;dbname=%s',
                $config['driver'], $config['host'], $config['port'], $config['name']
            );
        }
    }


    /**
     * Return a new \DB\SQL object from the dsn parameters
     *
     * @param string $dsn e.g. mysql:host=127.0.0.1;port=3306;dbname=development_db
     * @param string $user
     * @param string $pass
     * @param array $options options to PDO
     * @link https://fatfreeframework.com/sql
     * @see http://php.net/manual/en/class.pdo.php
     */
    public static function &newDsnDb(string $dsn, string $user, string $pass, array $options = []): \DB\SQL
    {
        $db = new \DB\SQL(
            $dsn,
            $user,
            $pass,
            array_key_exists('options', $options) ? [] : [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
        return $db;
    }


    /**
     * Return a new \DB\SQL object from the given parameters
     *
     * @param string|array http dsn string or arary params params for connection
     * Requires:
     * {
     *      ["driver"]=> string(5) "mysql"
     *      ["host"]=> string(9) "127.0.0.1"
     *      ["port"]=> int(3306)
     *      ["user"]=> string(4) "root"
     *      ["pass"]=> string(4) "root"
     *      ["name"]=> string(14) "development_db"
     * }
     * OR
     * PDO dsn and and username, password
     * @link https://fatfreeframework.com/sql
     * @see http://php.net/manual/en/class.pdo.php
     */
    public static function &newDb($params): \DB\SQL
    {
        if (is_string($params)) {
            $params = self::parseHttpDsn($params);
        }

        if (array_key_exists('dsn', $params)) {
            $dsn = $params['dsn'];
        } else {
            $dsn = self::createDbDsn($params);
        }

        return self::newDsnDb($dsn, $params['user'], $params['pass']);
    }


}
