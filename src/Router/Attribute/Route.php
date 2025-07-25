<?php

namespace Borsch\Router\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class Route
{

    public function __construct(
        /** @var string[] GET|POST|PUT|PATCH|DELETE|HEAD|OPTION|PURGE */
        public array $methods,
        public string $path = '',
        public ?string $name = null,
        public int $priority = 0
    ) {}
}
