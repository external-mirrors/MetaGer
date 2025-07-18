<?php

namespace App\Authentication;

enum KeyState: string
{
    case FULL = 'full';
    case LOW = 'low';
    case EMPTY = 'empty';
    case NO_KEY = 'no_key';
}