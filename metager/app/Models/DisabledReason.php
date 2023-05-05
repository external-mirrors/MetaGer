<?php

namespace App\Models;

enum DisabledReason
{
    case USER_CONFIGURATION;
    case PAYMENT_REQUIRED;
    case SERVES_ADVERTISEMENTS;
    case INCOMPATIBLE_FILTER;
    case INCOMPATIBLE_FOKUS;
    case INCOMPATIBLE_LOCALE;
    case SUMAS_CONFIGURATION;
}