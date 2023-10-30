<?php

namespace App\Models\ParameterFilters;

class ParameterFilterValue
{
    const VALUE_AVAILABLE   = 0;
    const VALUE_UNAVAILABLE = 1;
    const VALUE_DISABLED    = 2;

    public readonly int $status;
    /** Holds a function to generate the value  */
    private \Closure $value;
    public readonly string $label;

    public function __construct(int $status, \Closure $value, string $label)
    {
        if (!in_array($status, [self::VALUE_AVAILABLE, self::VALUE_UNAVAILABLE, self::VALUE_DISABLED])) {
            throw new \InvalidArgumentException("Parameter `status` must be one of [ParameterFilterValue::VALUE_AVAILABLE, ParameterFilterValue::VALUE_UNAVAILABLE, ParameterFilterValue::VALUE_DISABLED]");
        }
        $this->status = $status;
        $this->value  = $value;
        $this->label  = $label;
    }

    public function getValue(): string
    {
        return call_user_func($this->value);
    }
}