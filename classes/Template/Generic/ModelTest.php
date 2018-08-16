<?php
namespace Rhino\Codegen\Template\Generic;

class ModelTest extends \Rhino\Codegen\Template\Generic
{
    protected $testBaseClass = '\PHPUnit\Framework\TestCase';

    public function generate()
    {
        foreach ($this->codegen->node->children('entity') as $entity) {
            $this->renderTemplate('generic/tests/model', 'tests/Model/' . $entity->class . 'Test.php', [
                'entity' => $entity,
            ]);
        }
    }

    public function generateTestAttribute(\Rhino\Codegen\Attribute $attribute) {
        if ($attribute->is('string')) {
            return 'bin2hex(openssl_random_pseudo_bytes(127))';
        } elseif ($attribute->is('text')) {
            return 'bin2hex(openssl_random_pseudo_bytes(1024))';
        } elseif ($attribute->is('int')) {
            return 'mt_rand()';
        } elseif ($attribute->is('decimal')) {
            return 'mt_rand() / mt_getrandmax()';
        } elseif ($attribute->is('bool')) {
            return 'mt_rand() < 0.5';
        } elseif ($attribute->is('date', 'date-time')) {
            return 'new \DateTimeImmutable()';
        } elseif ($attribute->is('object')) {
            return 'new \\' . $attribute->getClass();
        } elseif ($attribute->is('array')) {
            return '[]';
        }
        assert(false, new \Exception('Unknown type for test attribute generation: ' . get_class($attribute)));
    }

    public function setTestBaseClass(string $testBaseClass): self {
        $this->testBaseClass = $testBaseClass;
        return $this;
    }

    public function getTestBaseClass(): string {
        return $this->testBaseClass;
    }
}
