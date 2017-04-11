<?php
namespace Rhino\Codegen;

class XmlParser {
    
    protected $file;
    protected $codegen;
    protected $parsers = [];
    
    public function __construct(Codegen $codegen, $file) {
        assert(is_file($file), 'Expected codegen XML file to be valid: ' . $file);
        
        $this->file = $file;
        $this->codegen = $codegen;
        
        $this->addParser('entity', new XmlParser\EntityParser());
    }
    
    public function parse(): Codegen {
        $errorMode = libxml_use_internal_errors(true);
        try {
            $file = $this->getFile();
            $xml = simplexml_load_file($file);
            if (!$xml) {
                throw new \Exception('Could not read XML.');
            }
            foreach ($xml as $child) {
                $this->preparseNode($child);
            }
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
    
    protected function preparseNode($node) {
        if (!isset($this->parsers[$node->getName()])) {
            throw new \Exception('Could not find node parser for ' . $node->getName());
        }
        $this->parsers[$node->getName()]->setCodegen($this->codegen)->preparse($node);
    }
    
    protected function parseNode(\SimpleXMLElement $node) {
        if (!isset($this->parsers[$node->getName()])) {
            throw new \Exception('Could not find node parser for ' . $node->getName());
        }
        $this->parsers[$node->getName()]->setCodegen($this->codegen)->parse($node);
    }
    
    protected function parseEntity($node) {
        // $this->codegen->debug($node->asXml());
        $entity = $this->codegen->findEntity((string) $node['name']);
        
        foreach ($node->children() as $child) {
            switch ($child->getName()) {
                case 'authentication': {
                    $entity->setAuthentication(true);

                    $attribute = new Attribute\StringAttribute();
                    $attribute->setName('Password Hash');
                    $entity->addAttribute($attribute);
                    break;
                }
                case 'one-to-one-relationship': {
                    $to = $this->codegen->findEntity((string) $child['to']);

                    $attribute = new Attribute\IntAttribute();
                    $attribute->setName(((string) $child['name'] ?: $to->getName()) . ' Id');
                    $entity->addAttribute($attribute);

                    $relationship = new Relationship\OneToOne();
                    $relationship->setFrom($entity);
                    $relationship->setTo($to);
                    $relationship->setName((string) $child['name'] ?: (string) $child['to']);
                    $entity->addRelationship($relationship);
                    $to->addRelationship($relationship);
                    break;
                }
                case 'belongs-to': {
                    $to = $this->codegen->findEntity((string) $child['entity']);

                    $attribute = new Attribute\IntAttribute();
                    $attribute->setName($to->getName() . ' Id');
                    $entity->addAttribute($attribute);

                    $relationship = new Relationship\BelongsTo();
                    $relationship->setFrom($entity);
                    $relationship->setTo($to);
                    $relationship->setName((string) $child['name'] ?: $to->getName());
                    $entity->addRelationship($relationship);
                    $to->addRelationship($relationship);
                    break;
                }
                case 'one-to-many-relationship': {
                    $to = $this->codegen->findEntity((string) $child['to']);

                    $attribute = new Attribute\IntAttribute();
                    $attribute->setName($entity->getName() . ' Id');
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
                case 'has-one': {
                    $to = $this->codegen->findEntity((string) $child['entity']);

                    $attribute = new Attribute\IntAttribute();
                    $attribute->setName(((string) $child['name'] ?: $to->getName()) . ' Id');
                    $entity->addAttribute($attribute);

                    $relationship = new Relationship\HasOne();
                    $relationship->setFrom($entity);
                    $relationship->setTo($to);
                    $relationship->setName((string) $child['name'] ?: $to->getName());
                    $entity->addRelationship($relationship);
                    $to->addRelationship($relationship);
                    break;
                }
                case 'has-many': {
                    $to = $this->codegen->findEntity((string) $child['entity']);

//                    $attribute = new Attribute\IntAttribute();
//                    $attribute->setName($entity->getName() . ' Id');
//                    $to->addAttribute($attribute);

                    $relationship = new Relationship\HasMany();
                    $relationship->setFrom($entity);
                    $relationship->setTo($to);
                    $relationship->setName((string) $child['name'] ?: $to->getName());
                    $entity->addRelationship($relationship);
                    $to->addRelationship($relationship);
                    break;
                }
            }
        }
    }
    
    public function getFile() {
        return $this->file;
    }
    
    public function addParser($nodeName, $parser) {
        $this->parsers[$nodeName] = $parser;
        return $this;
    }
    
}
