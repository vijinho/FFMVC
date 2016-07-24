<?php

namespace FFMVC\Traits;


trait Logger
{

    /**
     * @var object logging objects
     */
    protected $loggerObject;

    /**
     * Write to log
     *
     * @param mixed $data
     * @return bool true on success
     */
    public function log($data)
    {
        if (empty($this->loggerObject) || empty($data)) {
            return false;
        }
        if (is_string($data)) {
            $data = [$data];
        } elseif (is_object($data)) {
            $data = print_r($data, 1);
        } elseif (is_array($data)) {
            foreach ($data as $line) {
                $this->loggerObject->write($line);
            }
        }
        return true;
    }

}
