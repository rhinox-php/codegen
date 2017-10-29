<?php
namespace Rhino\Codegen\Template\Generic;

class Model extends \Rhino\Codegen\Template\Generic implements \Rhino\Codegen\Template\AggregateInterface {
    use \Rhino\Codegen\Template\Aggregate;

    public function aggregate() {
        yield ModelAbstract::class;
        yield ModelGenerated::class;
        yield ModelInitial::class;
        yield ModelPdo::class;
        yield ModelSerializer::class;
        yield ModelTest::class;
    }

}
