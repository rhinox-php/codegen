<?php
namespace Rhino\Codegen\Template;

interface AggregateInterface {
    public function aggregate();
    public function generate();
    public function iterateTemplates();
}
