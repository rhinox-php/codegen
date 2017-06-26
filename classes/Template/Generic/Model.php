<?php
namespace Rhino\Codegen\Template\Generic;

class Model extends \Rhino\Codegen\Template\Aggregate {
    public $name = 'generic';

    public function aggregate() {
        yield ModelAbstract::class;
        yield ModelGenerated::class;
        yield ModelInitial::class;
        yield ModelPdo::class;
        yield ModelSerializer::class;
    }

}
