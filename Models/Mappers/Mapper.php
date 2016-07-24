<?php

namespace FFMVC\Models\Mappers;

use FFMVC\Traits as Traits;
use FFMVC\Helpers as Helpers;

/**
 * Base Database Mapper Class extends f3's DB\SQL\Mapper
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 * @link https://fatfreeframework.com/sql-mapper
 * @link https://github.com/Wixel/GUMP
 */
abstract class Mapper extends \DB\SQL\Mapper
{
    use Traits\Logger;
    use Traits\Validation;

    /**
     * @var object database class
     */
    protected $db;

    /**
     * @var table for the mapper
     */
    protected $table;

    /**
     * @var object logging class
     */
    protected $loggerObject;

    /**
     * Initial validation rules (automatically copied from $validationRules when instantiated)
     *
     * @var array
     */
    protected $validationRulesDefault = [];

    /**
     * Initial filter rules  (automatically copied from $filterRules when instantiated)
     *
     * @var array
     */
    protected $filterRulesDefault = [];


    /**
     * initialize with array of params, 'db' and 'logger' can be injected
     */
    public function __construct($params = [])
    {
        $f3 = \Base::instance();

        if (!array_key_exists('logger', $params)) {
            $this->loggerObject = \Registry::get('logger');
            unset($params['logger']);
        }

        if (!array_key_exists('db', $params)) {
            $this->db = \Registry::get('db');
            unset($params['db']);
        }

        // guess the table name from the class name if not specified as a class member
        parent::__construct($this->db,
            strtolower(empty($this->table) ? $f3->snakecase(\UTF::instance()->substr(get_class($this), 21)) : $this->table));

        foreach ($params as $k => $v) {
            $this->$k = $v;
        }

        // save default validation rules and filter rules in-case we add rules
        $this->validationRulesDefault = $this->validationRules;
        $this->filterRulesDefault = $this->filterRules;
    }


    /**
     * Enhanced mapper save only saves if validation passes and also generates a uuid if needed
     *
     * @return boolean true/false
     */
    public function vSave()
    {
        // generate UUID
        if (in_array('uuid', $this->fields()) && (null == $this->uuid)) {
            $tmp = clone $this;
            $uuid = Helpers\Str::uuid();
            while ($tmp->load(['uuid = ?', $uuid])) {
                $uuid = Helpers\Str::uuid();
            }
            unset($tmp);
            $this->uuid = $uuid;
        }

        return $this->validate() ? $this->save() : false;
    }


    /**
     * Filter and validate
     *
     * @param bool $run GUMP - call 'run' (return true/false) otherwise call 'validate' (return array)
     * @param array $data optional data array if different values to check outside of this mapper object fields
     * @return mixed array of validated data if 'run' otherwise array of errors or boolean if passed 'validate'
     * @link https://github.com/Wixel/GUMP
     */
    public function validate($run = true, array $data = [])
    {
        if (!is_array($data) || empty($data)) {
            $data = $this->cast();
        }

        $validator = Helpers\Validator::instance();
        $validator->validation_rules($this->validationRules);
//        $validator->filter_rules($this->filterRules);

        if (empty($run)) {

            $this->validationErrors = $validator->validate($data);
            $this->valid = !is_array($this->validationErrors);

            return $this->valid ? true : $this->validationErrors;

        } else {

            $this->valid = false;
            $data = $validator->run($data);

            if (is_array($data)) {
                $this->valid = true;
                $this->copyfrom($data); // update filtered/validated data
            }

            return $this->valid;
        }
    }


}


class MapperException extends \Exception {}
