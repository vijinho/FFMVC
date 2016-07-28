<?php

namespace FFMVC\Traits;

use FFMVC\Helpers as Helpers;

/**
 * Handle validation (via GUMP or GUMP-inheriting validation class)
 *
 * The constructor should optional initialise default validation and filtering rules.
 * The class can then call the default method validate() which return true/false if validation passed
 * or validate(false) which returns an array of the validation errors which can be made
 * more presentable passed into getValidationErrors()
 *
 * @see \FFMVC\Helpers\Validator
 * @url https://github.com/Wixel/GUMP
 */
trait Validation
{
    /**
     * Validation rules (GUMP rules array)
     *
     * @var array
     */
    protected $validationRules = [];

    /**
     * Initial validation rules (automatically copied from $validationRules when instantiated)
     *
     * @var array
     */
    protected $validationRulesDefault = [];

    /**
     * Filter rules  (GUMP filters array)
     *
     * @var array
     */
    protected $filterRules = [];

    /**
     * Initial filter rules  (automatically copied from $filterRules when instantiated)
     *
     * @var array
     */
    protected $filterRulesDefault = [];

    /**
     * Boolean flag from validation run results if object is valid
     *
     * @var array
     */
    protected $valid = [];

    /**
     * Errors from last validation run
     *
     * @var array
     */
    protected $validationErrors = [];


    /**
     * initialize with array of params, 'db' and 'logger' can be injected
     */
    public function __construct($params = [])
    {
        // save default validation rules and filter rules in-case we add rules
        $this->validationRulesDefault = $this->validationRules;
        $this->filterRulesDefault = $this->filterRules;
    }


    /**
     * Set filter rules from array
     *
     * @param array $rules
     * @return array
     */
    public function setFilterRules(array $rules = [])
    {
        $this->filterRules = $rules;

        return $this->filterRules;
    }


    /**
     * Set validation rules from array
     *
     * @param array $rules
     * @return array
     */
    public function setValidationRules(array $rules = [])
    {
        $this->validationRules = $rules;

        return $this->validationRules;
    }


    /**
     * Reset filter rules to default
     *
     * @return array
     */
    public function resetFilterRules()
    {
        $this->filterRules = $this->filterRulesDefault;

        return $this->filterRules;
    }


    /**
     * Reset validation rules to default
     *
     * @return array
     */
    public function resetValidationRules()
    {
        $this->validationRules = $this->validationRulesDefault;

        return $this->validationRules;
    }


    /**
     * Add extra filter rules from array
     *
     * @param array $rules
     * @return array
     */
    public function addFilterRules(array $rules = [])
    {
        $this->filterRules = array_merge($this->filterRules, $rules);

        return $this->filterRules;
    }


    /**
     * Add extra validation rules from array
     *
     * @param array $rules
     * @return array
     */
    public function addValidationRules(array $rules = [])
    {
        $this->validationRules = array_merge($this->validationRules, $rules);

        return $this->validationRules;
    }


    /**
     * Enforce validation required validation check on given fields
     * or all fields if no array passed in
     *
     * @param array optional $fields
     * @return array $validationRules
     */
    public function requiredValidation(array $fields = [])
    {
        $rules = $this->validationRules;

        // force all fields to required if empty
        if (empty($fields)) {
            $fields = array_keys($rules);
        }

        // appened 'required' to validation rules for fields
        foreach ($rules as $k => $v) {

            if (in_array($k, $fields)) {
                $rules[$k] = 'required|' . $v;
            } elseif (false !== \UTF::instance()->stristr($v, 'exact_len')) {
                // special case, exact_len means required otherwise!
                unset($rules[$k]);
            }

        }

        $this->validationRules = $rules;

        return $this->validationRules;
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
        $validator = Helpers\Validator::instance();
        $validator->filter_rules($this->filterRules);
        return $validator->filter($data);
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
            }

            return $this->valid;
        }
    }


    /**
     * Process errors of results from (return array of $this->validate(true)) $validator->run($data) into friendlier notifications
     *
     * @param mixed $errors errors from $validator->run($data) or get last errors
     * @param array $notifications
     */
    public function getValidationErrors($errors = [])
    {
        if (empty($errors)) {
            $errors = $this->validationErrors;
            if (empty($errors)) {
                return [];
            }
        }

        $notifications = [];

        if (is_array($errors)) {

            foreach ($errors as $e) {

                $fieldname = ucwords(str_replace('_', ' ', $e['field']));

                switch ($e['rule']) {

                    case 'validate_exact_len':
                        $msg = sprintf('%s must be exactly %d characters in length.',
                            $fieldname, $e['param']);
                        break;

                    case 'validate_min_len':
                        $msg = sprintf('%s must be at least %d characters.',
                            $fieldname, $e['param']);
                        break;

                    case 'validate_max_len':
                        $msg = sprintf('%s must at most %d characters.',
                            $fieldname, $e['param']);
                        break;

                    case 'validate_valid_url':
                        $msg = sprintf('URLs must be valid if set.', $fieldname);
                        break;

                    case 'validate_valid_email':
                        $msg = sprintf('%s must be a valid email address.',
                            $fieldname);
                        break;

                    case 'validate_required':
                        $msg = sprintf('%s must be entered.', $fieldname);
                        break;

                    default:
                        $msg = sprintf('Exception: %s %s %s', $fieldname,
                            $e['rule'], $e['param']);
                        break;
                }
                $notifications[] = $msg;
            }
        }
        return $notifications;
    }

}


