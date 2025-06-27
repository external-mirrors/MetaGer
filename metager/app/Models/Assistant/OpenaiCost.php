<?php

namespace App\Models\Assistant;

class OpenaiCost extends AssistantCost
{
    private float $per_input_token;
    private float|null $per_cached_input_token;
    private float $per_output_token;

    public function __construct(float $per_input_token, float|null $per_cached_input_token, float $per_output_token)
    {
        $this->per_input_token = $per_input_token;
        $this->per_cached_input_token = $per_cached_input_token;
        $this->per_output_token = $per_output_token;
    }
}