<?php

namespace Borsch\Router\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::IS_REPEATABLE)]
class Controller
{

    public function __construct(
        public string $base_path = '',
        public int $priority = 0
    ) {}
}
