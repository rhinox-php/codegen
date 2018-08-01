<?php
namespace Rhino\Codegen\Template\Generic;

class ModelTest extends \Rhino\Codegen\Template\Generic
{
    protected $testBaseClass = '\PHPUnit\Framework\TestCase';

    public function generate()
    {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('generic/tests/model', 'tests/Model/' . $entity->getClassName() . 'Test.php', [
                'entity' => $entity,
            ]);
        }
    }

    public function generateTestAttribute(\Rhino\Codegen\Attribute $attribute) {
        if ($attribute->isType(['String'])) {
            return 'bin2hex(openssl_random_pseudo_bytes(127))';
        } elseif ($attribute->isType(['Text'])) {
            return 'bin2hex(openssl_random_pseudo_bytes(1024))';
        } elseif ($attribute->isType(['Int'])) {
            return 'mt_rand()';
        } elseif ($attribute->isType(['Decimal'])) {
            return 'mt_rand() / mt_getrandmax()';
        } elseif ($attribute->isType(['Bool'])) {
            return 'mt_rand() < 0.5';
        } elseif ($attribute->isType(['Date', 'DateTime'])) {
            return 'new \DateTimeImmutable()';
        } elseif ($attribute->isType(['Object'])) {
            return 'new \\' . $attribute->getClass();
        } elseif ($attribute->isType(['Array'])) {
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
