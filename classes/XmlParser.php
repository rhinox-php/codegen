<?php
namespace Rhino\Codegen;

class XmlParser {
    
    protected $file;
    protected $codegen;
    
    public function __construct($file) {
        assert(is_file($file), 'Expected codegen XML file to be valid: ' . $file);
        
        $this->file = $file;
        $this->codegen = new Codegen($this);
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
                $this->codegen->setProjectName((string) $node['project-name']);
                $this->codegen->setTemplatePath(dirname($this->getFile()) . '/' . $node['template-path']);
                $this->codegen->setUrlPrefix((string) $node['url-prefix']);
                $this->codegen->setViewPathPrefix((string) $node['view-path-prefix']);
                $this->codegen->setClassPathPrefix((string) $node['class-path-prefix']);
                $this->codegen->setDatabaseName((string) $node['database-name']);
                $this->codegen->setPort((int) $node['port']);
                break;
            }
        }
    }
    
    protected function parseEntity($node) {
        $entity = new Entity();
        $entity->setName((string) $node['name']);
        foreach ($node->children() as $child) {
            switch ($child->getName()) {
                case 'authentication': {
                    $entity->setAuthentication(true);

                    $attribute = new Attribute\StringAttribute();
                    $attribute->setName('Password Hash');
                    $entity->addAttribute($attribute);
                    break;
                }
                case 'string-attribute': {
                    $attribute = new Attribute\StringAttribute();
                    $attribute->setName((string) $child['name']);
                    $entity->addAttribute($attribute);
                    break;
                }
                case 'int-attribute': {
                    $attribute = new Attribute\IntAttribute();
                    $attribute->setName((string) $child['name']);
                    $entity->addAttribute($attribute);
                    break;
                }
                case 'date-attribute': {
                    $attribute = new Attribute\DateAttribute();
                    $attribute->setName((string) $child['name']);
                    $entity->addAttribute($attribute);
                    break;
                }
                case 'text-attribute': {
                    $attribute = new Attribute\TextAttribute();
                    $attribute->setName((string) $child['name']);
                    $entity->addAttribute($attribute);
                    break;
                }
                case 'bool-attribute': {
                    $attribute = new Attribute\BoolAttribute();
                    $attribute->setName((string) $child['name']);
                    $entity->addAttribute($attribute);
                    break;
                }
                case 'one-to-one-relationship': {
                    $to = $this->codegen->findEntity((string) $child['to']);

                    $attribute = new Attribute\IntAttribute();
                    $attribute->setName($entity->getName() . ' ID');
                    $to->addAttribute($attribute);

                    $relationship = new Relationship\OneToOne();
                    $relationship->setFrom($entity);
                    $relationship->setTo($to);
                    $entity->addRelationship($relationship);
                    $to->addRelationship($relationship);
                    break;
                }
                case 'one-to-many-relationship': {
                    $to = $this->codegen->findEntity((string) $child['to']);

                    $attribute = new Attribute\IntAttribute();
                    $attribute->setName($entity->getName() . ' ID');
                    $to->addAttribute($attribute);

                    $relationship = new Relationship\OneToMany();
                    $relationship->setFrom($entity);
                    $relationship->setTo($to);
                    $entity->addRelationship($relationship);
                    $to->addRelationship($relationship);
                    break;
                }
//                case 'to-many-relationship': {
//                    $to = $this->codegen->findEntity((string) $child['entity']);
//                    $relationship = new Relationship\ToMany();
//                    $relationship->setFrom($entity);
//                    $relationship->setTo($to);
//                    $entity->addRelationship($relationship);
//                    $to->addRelationship($relationship);
//                    break;
//                }
            }
        }
        $this->codegen->addEntity($entity);
    }
    
    public function getFile() {
        return $this->file;
    }
    
}
