<?php

namespace App\Models\Assistant;

enum MessageRole
{
    case Agent;
    case User;
}