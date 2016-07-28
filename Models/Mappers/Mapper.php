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
     * Fields and their visibility to clients, boolean or string of visible field name
     *
     * @var array $fieldsVisible
     */
    protected $fieldsVisible = [];

    /**
     * Fields that are editable to clients, boolean or string of visible field name
     *
     * @var array $fieldsEditable
     */
    protected $fieldsEditable = [];

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
     * Cast the mapper data to an array and modify (for external clients typically)
     * using the visible fields and names for export, converting dates to unixtime
     *
     * @param boolean $unmodified should the data be mapper original or modified?
     * @return array $data
     */
    public function &exportArray($unmodified = false)
    {
        $data = $this->cast();
        if (!empty($unmodified)) {
            return $data;
        }
        foreach ($data as $k => $v) {
            if (empty($this->fieldsVisible[$k])) {
                unset($data[$k]);
                continue;
            } elseif (true !== $this->fieldsVisible[$k]) {
                unset($data[$k]);
                $data[$this->fieldsVisible[$k]] = $v;
            }
            // convert date to unix timestamp
            if ('updated' == $k || 'created' == $k || (
                strlen($v) == 19 && preg_match("/^[\d]{4}-[\d]{2}-[\d]{2}[\s]+[\d]{2}:[\d]{2}:[\d]{2}/", $v, $m))) {
                $data[$k] = strtotime($v);
            }
        }
        return $data;
    }


    /**
     * Convert the mapper object to format suitable for JSON
     *
     * @param boolean $unmodified should the data be mapper original or modified?
     * @param boolean $public cast as public (visible) data or raw db data?
     * @return json
     */
    public function &exportJson($unmodified = false)
    {
        return json_encode(empty($public) ? $this->cast() : $this->exportArray($unmodified), JSON_PRETTY_PRINT);
    }


    /**
     * Set a field (default named uuid) to a UUID value if one is not present.
     *
     * @param string $field the name of the field to check and set
     * @return string $uuid the new uuid generated
     */
    public function setUUID($field = 'uuid')
    {
        // a proper uuid is 36 characters
        if (in_array($field, $this->fields()) && (null == $this->$field || strlen($this->$field !== 36))) {
            $tmp = clone $this;
            $uuid = Helpers\Str::uuid();
            while ($tmp->load(["$field = ?", $uuid])) {
                $uuid = Helpers\Str::uuid();
            }
            unset($tmp);
            $this->$field = $uuid;
            return $uuid;
        }
        return empty($this->$field) ? null : $this->$field;
    }


    /**
     * Enhanced mapper save only saves if validation passes and also generates a uuid if needed
     *
     * @param string $id uuid key field
     * @return boolean true/false
     */
    public function smartSave($id = 'uuid')
    {
        // set UUID vield value if not set
        $this->setUUID($id);

        // set date created field if not set
        if (in_array('created', $this->fields()) && empty($this->created)) {
            $this->created = Helpers\Time::database();
        }

        return $this->validate() ? $this->save() : false;
    }


    /**
     * Apply filter rules to data
     *
     * @param array $data
     * @param array $rules
     * @return $data
     */
    public function filter(array $data = [], array $rules = [])
    {
        if (!is_array($data) || empty($data)) {
            $data = $this->cast();
        }

        $validator = Helpers\Validator::instance();
        $validator->filter_rules($this->filterRules);
        $data = $validator->filter($data);
        $this->copyfrom($data); // update filtered/validated data
        return $data;

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
        $validator->filter_rules($this->filterRules);
        $data = $validator->filter($data);

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
