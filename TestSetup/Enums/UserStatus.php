<?php

declare(strict_types=1);

namespace TestSetup\Enums;

enum UserStatus
{
    case Registered;
    case Verified;
    case Banned;
    case Deleted;
}
