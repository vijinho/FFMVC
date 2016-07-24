<?php

namespace FFMVC\Traits;


/**
 * The class constructor should initialise a member loggerObject which contains
 * the method 'write' to write a line to the log entry - default file -  backend.
 * If an array is passed, each value is written as a new line.
 * Passing in an object dumps it to the log.
 */
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
            if (is_a($data, '\Exception') && $f3->get('DEBUG') > 2) {
                $data = print_r($data, 1);
            } else {
                $data = print_r($data, 1);
            }
        }
        if (is_array($data)) {
            foreach ($data as $line) {
                $this->loggerObject->write($line);
            }
        }
        return true;
    }

}
