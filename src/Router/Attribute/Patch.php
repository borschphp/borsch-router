<?php

namespace Borsch\Router\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class Patch extends Route
{

    public function __construct(
        string $path = '',
        ?string $name = null,
        int $priority = 0
    ) {
        parent::__construct(['PATCH'], $path, $name, $priority);
    }
}
