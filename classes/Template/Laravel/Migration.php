<?php
namespace Rhino\Codegen\Template\Laravel;

class Migration extends \Rhino\Codegen\Template\Template
{
    protected $name = 'laravel';
    protected $path = null;

    public function generate()
    {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('database/full', $this->path . $entity->getClassName() . '.php', [
                'entity' => $entity,
            ]);
        }
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }
}
