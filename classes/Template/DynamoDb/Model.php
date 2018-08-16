<?php
namespace Rhino\Codegen\Template\DynamoDb;

use Rhino\Codegen\Attribute;

class Model extends \Rhino\Codegen\Template\Template
{
    protected $name = 'dynamo-db';
    protected $path = null;
    protected $namespace = null;
    protected $tableNamePrefix = null;

    public function generate()
    {
        $this->renderTemplate('classes/abstract-model', $this->path . 'AbstractModel.php');
        foreach ($this->codegen->node->children('entity') as $entity) {
            $this->renderTemplate('classes/generated-model', $this->path . $entity->class . '.php', [
                'entity' => $entity,
            ]);
        }
    }

    public function getAttributeType(Attribute $attribute)
    {
        if ($attribute->is('string', 'text', 'date', 'date-time')) {
            return 'S';
        }
        if ($attribute->is('int', 'decimal', 'bool')) {
            return 'N';
        }
        throw new \Exception('Unknown DynamoDB attribute type.');
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

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function getTableNamePrefix()
    {
        return $this->tableNamePrefix;
    }

    public function setTableNamePrefix($tableNamePrefix)
    {
        $this->tableNamePrefix = $tableNamePrefix;
        return $this;
    }
}
