<?php

namespace App\Models\Assistant;

use Serializable;

abstract class MessageContent implements Serializable
{
    abstract function render(MessageRole $role = MessageRole::User): string;
}