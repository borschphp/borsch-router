<?php

namespace Borsch\Router\Exception;

use Exception;

class InvalidArgumentException extends Exception
{

    public static function routeNameAlreadyExists(string $name): self
    {
        return new self(sprintf(
            'A similar route name (%s) has already been provided.',
            $name
        ));
    }

    public static function routeNameIsUnknown(string $name): self
    {
        return new self(sprintf(
            'The route named %s is unknown...',
            $name
        ));
    }

    public static function invalidRoutePath(string $path): self
    {
        return new self(sprintf(
            'Invalid route path "%s", must be a string.',
            $path
        ));
    }
}
