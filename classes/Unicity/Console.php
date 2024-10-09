<?php

declare(strict_types=1);

namespace Unicity;

final class Console
{
    public static function log($message)
    {
        fwrite(fopen('php://stdout', 'w'), strval($message));
    }

}
