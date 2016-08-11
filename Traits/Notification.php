<?php

namespace FFMVC\Traits;

/**
 * Add user notifications messages, typically for user feedback from web pages.
 * The class constructor must set a member $notificationObject which contains
 * two methods:
 *     - add which adds notification of $type (e.g warning or into)
 *     - addMultiple which adds multiple messages in $type => $messages[] format
 */
trait Notification
{

    /**
     * @var object user notifications object
     */
    protected $notificationObject;


    /**
     * Notify user
     *
     * @param mixed $data multiple messages by 'type' => [messages] OR message string
     * @param string $type type of messages (success, error, warning, info) OR null if multiple $data
     * @return boolean success
     */
    public function notify($data, $type = null)
    {
        if (is_array($data)) {
            return $this->notificationObject->addMultiple($data);
        } else {
            return $this->notificationObject->add($data, $type);
        }
    }

}
