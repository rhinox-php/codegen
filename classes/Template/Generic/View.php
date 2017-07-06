<?php
namespace Rhino\Codegen\Template\Generic;

class View extends \Rhino\Codegen\Template\Generic implements \Rhino\Codegen\Template\AggregateInterface {
    use \Rhino\Codegen\Template\Aggregate;

    public function aggregate() {
        yield ViewModelForm::class;
        yield ViewModelIndex::class;
    }

}
