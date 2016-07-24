<?php

namespace FFMVC\Traits;


trait Notification
{

    /**
     * @var object user notifications objects
     */
    protected $notificationObject;


    /**
     * Notify user
     *
     * @param mixed $data multiple messages by 'type' => [messages] OR message string
     * @param string $type type of messages (success, danger, warning, info) OR null if multiple $data
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
