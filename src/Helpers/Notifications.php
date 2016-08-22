<?php

namespace FFMVC\Helpers;

use FFMVC\{Helpers, Models};

/**
 * Notifications Helper Class.
 *
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2006-2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Notifications extends \Prefab
{

    public static $types = [
        'success',
        'error',
        'warning',
        'info',
        'debug'
    ];

    /**
     * initialise notifications
     *
     * @param bool $saveState
     * @param array $types
     */
    public static function init(bool $saveState = true, array $types = [])
    {
        $f3 = \Base::instance();
        $cli = (PHP_SAPI == 'cli');
        $notifications = $f3->get('notifications');

        if (empty($notifications)) {
            $notifications = [];
        }

        if (!empty($types)) {
            static::$types = $types;
        }

        foreach (static::$types as $type) {
            if (!array_key_exists($type, $notifications)) {
                $notifications[$type] = [];
            }
        }

        $f3->set('notifications', $notifications);

        if (!$cli) {
            $f3->set('notifications_save_state', $saveState); // save notifications in session?
        }
    }


    /**
     * Clear all notifications
     */
    public static function clear()
    {
        $f3 = \Base::instance();
        $notifications = [];

        foreach (static::$types as $type) {
            $notifications[$type] = [];
        }

        $f3->set('notifications', $notifications);
    }


    /**
     * Keep state between requests?
     *
     * @param bool $boolean
     * @return boolean
     */
    public static function saveState(bool $boolean = true): bool
    {
        if (PHP_SAPI !== 'cli') {
            $f3 = \Base::instance();
            $f3->set('notifications_save_state', $boolean);

            return true;
        } else {

            return false;
        }
    }

    public function __destruct()
    {
        if (PHP_SAPI !== 'cli') {
            $f3 = \Base::instance();

            // save persistent notifications
            $notifications = empty($f3->get('notifications_save_state')) ? null : $f3->get('notifications');
            $f3->set('SESSION.notifications', $notifications);

        }
    }


    /**
     * add a notification, default type is notification
     *
     * @param string $notification
     * @param string $type
     */
    public static function add(string $notification, string $type = null)
    {
        $f3 = \Base::instance();
        $notifications = $f3->get('notifications');
        $type = (empty($type) || !in_array($type, static::$types)) ? 'info' : $type;

        // don't repeat notifications!
        if (!in_array($notification, $notifications[$type]) && is_string($notification)) {
            $notifications[$type][] = $notification;
        }
        $f3->set('notifications', $notifications);
    }


    /**
     * add multiple notifications by type
     *
     * @param array $notificationsList
     */
    public function addMultiple(array $notificationsList)
    {
        $f3 = \Base::instance();
        $notifications = $f3->get('notifications');

        foreach ($notificationsList as $type => $list) {
            foreach ($list as $notification) {
                $notifications[$type][] = $notification;
            }
        }

        $f3->set('notifications', $notifications);
    }


    /**
     * return notifications of given type or all types, return false if none, clearing stack
     *
     * @param string $type
     * @param bool $clear
     * @return boolean|array
     */
    public static function get(string $type = null, bool $clear = true)
    {
        $f3 = \Base::instance();
        $notifications = $f3->get('notifications');

        if (!empty($type)) {
            if (in_array($type, static::$types)) {

                $i = count($notifications[$type]);
                if (0 < $i) {
                    return $notifications[$type];
                } else {
                    return false;
                }

            } else {
                return false;
            }
        }

        // return false if there actually are no notifications in the session
        $i = 0;
        foreach (static::$types as $type) {
            $i += count($notifications[$type]);
        }

        if (0 == $i) {
            return false;
        }

        // order return by order of type array above
        // i.e. success, error, warning and then informational notifications last
        $return = [];
        foreach (static::$types as $type) {
            $return[$type] = $notifications[$type];
        }

        // clear all notifications
        if (!empty($clear)) {
            static::clear();
        }

        return $return;
    }


}
