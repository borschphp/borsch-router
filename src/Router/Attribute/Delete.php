<?php

namespace Borsch\Router\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class Delete extends Route
{

    public function __construct(
        string $path = '',
        ?string $name = null,
        int $priority = 0
    ) {
        parent::__construct(['DELETE'], $path, $name, $priority);
    }
}
