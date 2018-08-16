<?php
namespace Rhino\Codegen\Template\Generic;

class ViewModelIndex extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        foreach ($this->codegen->node->children('entity') as $entity) {
            $this->renderTemplate('generic/views/model/index', 'src/views/' . $entity->getFileName() . '/index.php', [
                'entity' => $entity,
            ]);
        }
    }
}
