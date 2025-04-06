<?php

namespace Borsch\Router\Node;

use Borsch\Router\Contract\RouteInterface;

class RouteNode
{

    public string $segment;
    public ?RouteInterface $route = null;
    /** @var RouteNode[] */
    public array $children = [];

    public bool $is_dynamic = false;
    public ?string $variable_name = null;
    public ?string $regex_pattern = null;

    public function __construct(string $segment, ?RouteInterface $route = null)
    {
        $this->segment = $segment;
        $this->route = $route;
    }

    public function addChild(string $segment): self
    {
        foreach ($this->children as $child) {
            if ($child->segment === $segment) {
                return $child;
            }
        }

        $new_child = new self($segment);
        $this->children[] = $new_child;

        return $new_child;
    }

    public function addDynamicChild(string $variableName, string $pattern): RouteNode
    {
        foreach ($this->children as $child) {
            if ($child->is_dynamic && $child->variable_name === $variableName) {
                return $child;
            }
        }

        $node = new RouteNode('{' . $variableName . '}');
        $node->is_dynamic = true;
        $node->variable_name = $variableName;
        $node->regex_pattern = $pattern;

        $this->children[] = $node;

        return $node;
    }
}
