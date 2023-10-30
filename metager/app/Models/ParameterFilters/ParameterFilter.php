<?php

namespace App\Models\ParameterFilters;

abstract class ParameterFilter
{
    const VALUE_NO_VALUE = -1;
    public readonly string $name;
    public readonly string $key;

    protected $values;
    protected $enabledValue;

    /**
     * Maps the URL Parameter key used by MetaGer to an implementation of ParameterFilter
     */
    private $parameter_keys = [];

    public function __construct(string $key = null)
    {
        // Add all the available URL Parameters
        $this->addParameterKey("s", Safesearch::class);

        // Add default nofilter value
        $this->values[self::VALUE_NO_VALUE] = new ParameterFilterValue(ParameterFilterValue::VALUE_UNAVAILABLE, function () {
            return "nofilter";
        }, __("metaGer.filter.safesearch.name"));

        // After this point initialization is only done for definitions from the searchengines
        if ($key !== null) {
            return; // Do not initialize Parametername if a key is supplied
        }
        $classname = get_class($this);

        if (!array_key_exists($classname, $this->parameter_keys)) {
            throw new \Exception("Cannot find URL Parameter for class `$classname`");
        }
        $this->key = $this->parameter_keys[$classname];
        if (!isset($this->name)) {
            throw new \Exception("`" . $classname . "::name` was not initialized by implementation.");
        }

        // Check if this filter is enabled
    }

    public function getValue(int $key): ParameterFilterValue
    {
        if (!array_key_exists($key, $this->values)) {
            throw new \Exception("Requested Parameterfilter value does not exist");
        }
        return $this->values[$key];
    }

    public function isAvailable(): bool
    {
        /**
         * @param ParameterFilterValue $filterValue
         */
        foreach ($this->values as $key => $filterValue) {
            if (in_array($filterValue->status, [ParameterFilterValue::VALUE_AVAILABLE, ParameterFilterValue::VALUE_DISABLED])) {
                return true;
            }
        }
        return false;
    }

    public function enable(int $value): bool
    {
        foreach ($this->values as $key => $filterValue) {
            if ($key === $value) {
                $this->enabledValue = $value;
                return true;
            }
        }
        return false;
    }

    public function isEnabled(): bool
    {
        if ($this->enabledValue !== null) {
            return true;
        } else {
            return false;
        }
    }
    private function addParameterKey(string $new_key, string $class)
    {
        foreach ($this->parameter_keys as $classname => $key) {
            if ($key === $new_key) {
                // Do not allow duplicate URL Parameter definitions
                throw new \Exception("The URL Parameter `$new_key` does already exist for the class `$classname`.");
            }
        }
        $this->parameter_keys[$class] = $new_key;
    }

}