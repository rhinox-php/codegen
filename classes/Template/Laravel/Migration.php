<?php
namespace Rhino\Codegen\Template\Laravel;

class Migration extends \Rhino\Codegen\Template\Template
{
    public function generate()
    {
        foreach ($this->codegen->node->children('entity') as $entity) {
            $this->renderTemplate('laravel/database/full', 'app/database/migrations/' . date('Y_m_d_h_i_s') . '_' . $entity->table . '.php', [
                'entity' => $entity,
            ]);
        }
    }

}
