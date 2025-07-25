<?php

namespace Borsch\Router\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class Post extends Route
{

    public function __construct(
        string $path = '',
        ?string $name = null,
        int $priority = 0
    ) {
        parent::__construct(['POST'], $path, $name, $priority);
    }
}
