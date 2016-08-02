<?php

namespace FFMVC\Helpers;
use FFMVC\Models as Models;

/**
 * Notifications Helper Class.
 *
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2006-2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Notifications extends \Prefab
{

    public static $TYPES = [
        'success',
        'danger',
        'warning',
        'info'
    ];

    public static function init($saveState = true)
    {
        $f3 = \Base::instance();
        $cli = (PHP_SAPI == 'cli');
        $notifications = $f3->get('notifications');

        if (empty($notifications)) {
            $notifications = [];
        }

        foreach (self::$TYPES as $type) {
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

        foreach (self::$TYPES as $type) {
            $notifications[$type] = [];
        }

        $f3->set('notifications', $notifications);
    }


    public static function saveState($boolean = true)
    {
        if (PHP_SAPI !== 'cli') {
            $f3 = \Base::instance();
            $f3->set('notifications_save_state', $boolean);

            return true;
        } else {

            return false;
        }
    }

    /**
     * Log notifications
     */
    public static function log()
    {
        $f3 = \Base::instance();
        $debug = $f3->get('DEBUG');
        $logger = \Registry::get('logger');
        $notifications = $f3->get('notifications');

        if ($notifications && 3 <= $debug && $logger && method_exists($logger, 'write')) {
            $uuid = $f3->get('uuid');
            $debugInfo = [
                'IP' => $f3->get('IP'),
                'URI' => $f3->get('REALM'),
                'REQUEST' => $f3->get('REQUEST'),
            ];
            foreach ($notifications as $type => $messages) {
                foreach ($messages as $m) {
                    $msg = 'Notification: ' . trim($uuid . ' ' . $type . ' ' . $m);
                    $logger->write($msg);
                }
            }
        }
    }

    public function __destruct()
    {
        if (PHP_SAPI !== 'cli') {
            $f3 = \Base::instance();

            self::log();
            // save persistent notifications
            $notifications = empty($f3->get('notifications_save_state')) ? null : $f3->get('notifications');
            $f3->set('SESSION.notifications', $notifications);

        }
    }


    // add a notification, default type is notification
    public static function add($notification, $type = null)
    {
        $f3 = \Base::instance();
        $notifications = $f3->get('notifications');
        $type = (empty($type) || !in_array($type, self::$TYPES)) ? 'info' : $type;

        // don't repeat notifications!
        if (!in_array($notification, $notifications[$type]) && is_string($notification)) {
            $notifications[$type][] = $notification;
        }
        $f3->set('notifications', $notifications);
    }


    /**
     * add multiple notifications by type
     *
     * @param type $notificationsList
     */
    public function addMultiple($notificationsList)
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


    // return notifications of given type or all TYPES, return false if none
    public static function sum($type = null)
    {
        $f3 = \Base::instance();
        $notifications = $f3->get('notifications');

        if (!empty($type)) {
            if (in_array($type, self::$TYPES)) {
                $i = count($notifications[$type]);
                return $i;
            } else {
                return false;
            }
        }

        $i = 0;
        foreach (self::$TYPES as $type) {
            $i += count($notifications[$type]);
        }

        return $i;
    }


    // return notifications of given type or all TYPES, return false if none, clearing stack
    public static function get($type = null, $clear = true)
    {
        $f3 = \Base::instance();
        $notifications = $f3->get('notifications');

        if (!empty($type)) {
            if (in_array($type, self::$TYPES)) {

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
        foreach (self::$TYPES as $type) {
            $i += count($notifications[$type]);
        }

        if (0 == $i) {
            return false;
        }

        // order return by order of type array above
        // i.e. success, error, warning and then informational notifications last
        foreach (self::$TYPES as $type) {
            $return[$type] = $notifications[$type];
        }

        // clear all notifications
        if (!empty($clear)) {
            self::log();
            self::clear();
        }

        return $return;
    }


}
