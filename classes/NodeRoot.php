<?php
namespace Rhino\Codegen;

class NodeRoot extends Node
{
    public function __construct()
    {
    }

    public function merge(Node $node) {
        $this->children = array_merge($this->children, $node->children);
    }
}
