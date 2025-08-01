<?php

namespace Borsch\Router\Exception;

use Exception;
use Psr\Http\Server\RequestHandlerInterface;

class RuntimeException extends Exception
{

    /**
     * @param array<string, string> $substitutions
     */
    public static function unableToGenerateUri(string $path, array $substitutions): self
    {
        return new self(sprintf(
            'Unable to generate URI "%s", missing substitutions, only received : %s...',
            $path,
            implode(', ', $substitutions)
        ));
    }

    public static function substitutionDoesNotMatchRouteConstraint(string $name, string $substitution, string $constraint): self
    {
        return new self(sprintf(
            'Given substitution for "%s" (= %s) does not match the route constraint "%s"...',
            $name,
            $substitution,
            $constraint
        ));
    }

    public static function notARequestHandler(string $id): self
    {
        return new self(sprintf(
            'Handler with ID "%s" must implement %s...',
            $id,
            RequestHandlerInterface::class
        ));
    }
}
