<?php
namespace Rhino\Codegen;

class XmlParser {
    
    protected $file;
    protected $codegen;
    
    public function __construct(string $file) {
        assert(is_file($file), 'Expected codegen XML file to be valid: ' . $file);
        
        $this->file = $file;
        $this->codegen = new Codegen();
    }
    
    public function parse(): Codegen {
        $errorMode = libxml_use_internal_errors(true);
        try {
            $file = $this->getFile();
            $xml = simplexml_load_file($file);
            foreach ($xml as $child) {
                $this->parseNode($child);
            }
        } catch (\Exception $exception) {
            throw new \Exception('Error parsing XML in ' . $file, 1, $exception);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($errorMode);
        }

        return $this->codegen;
    }
    
    protected function parseNode($node) {
        switch ($node->getName()) {
            case 'entity': {
                $this->parseEntity($node);
                break;
            }
            case 'code': {
                $this->codegen->setNamespace((string) $node['namespace']);
                break;
            }
        }
    }
    
    protected function parseEntity($node) {
        $entity = new Entity();
        $entity->setName((string) $node['name']);
        foreach ($node->children() as $child) {
            switch ($child->getName()) {
                case 'attribute': {
                    $attribute = new Attribute\StringAttribute();
                    $attribute->setName((string) $child['name']);
                    $entity->addAttribute($attribute);
                    break;
                }
                case 'relationship': {
                    if ($child['to-many']) {
                        $to = $this->codegen->findEntity((string) $child['to-many']);
                        $relationship = new Relationship\ToMany();
                        $relationship->setFrom($entity);
                        $relationship->setTo($to);
                        $entity->addRelationship($relationship);
                        $to->addRelationship($relationship);
                    } elseif ($child['to-one']) {

                    }
                    break;
                }
            }
        }
        $this->codegen->addEntity($entity);
    }
    
    protected function getFile() {
        return $this->file;
    }
    
}
