<?php
namespace Rhino\Codegen\Template\Generic;

class ModelTest extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('generic/tests/model', 'tests/Model/' . $entity->getClassName() . 'Test.php', [
                'entity' => $entity,
            ]);
        }
    }

    public function generateTestAttribute(\Rhino\Codegen\Attribute $attribute) {
        if ($attribute->is(['String'])) {
            return 'bin2hex(openssl_random_pseudo_bytes(127))';
        } elseif ($attribute->is(['Text'])) {
            return 'bin2hex(openssl_random_pseudo_bytes(1024))';
        } elseif ($attribute->is(['Int'])) {
            return 'mt_rand()';
        } elseif ($attribute->is(['Decimal'])) {
            return 'mt_rand() / mt_getrandmax()';
        } elseif ($attribute->is(['Bool'])) {
            return 'mt_rand() < 0.5';
        } elseif ($attribute->is(['Date', 'DateTime'])) {
            return 'new \DateTimeImmutable()';
        }
        assert(false, new \Exception('Unknown type for test attribute generation'));
    }
}
