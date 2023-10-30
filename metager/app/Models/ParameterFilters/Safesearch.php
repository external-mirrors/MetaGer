<?php

namespace App\Models\ParameterFilters;

class Safesearch extends ParameterFilter
{
    const VALUE_SAFE     = 0;
    const VALUE_MODERATE = 1;
    const VALUE_OFF      = 2;
    public readonly string $name;

    public function __construct(string $key = null)
    {
        $this->name = __("metaGer.filter.safesearch.name");
        parent::__construct($key);

        $this->values[self::VALUE_SAFE]     = new ParameterFilterValue(ParameterFilterValue::VALUE_UNAVAILABLE, function () {
            return "s";
        }, __('metaGer.filter.safesearch.strict'));
        $this->values[self::VALUE_MODERATE] = new ParameterFilterValue(ParameterFilterValue::VALUE_UNAVAILABLE, function () {
            return "m";
        }, __('metaGer.filter.safesearch.moderate'));
        $this->values[self::VALUE_OFF]      = new ParameterFilterValue(ParameterFilterValue::VALUE_UNAVAILABLE, function () {
            return "o";
        }, __('metaGer.filter.safesearch.off'));
    }

}