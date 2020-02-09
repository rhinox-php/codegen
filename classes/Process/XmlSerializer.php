<?php
namespace Rhino\Codegen\Process;

use Rhino\Codegen\Codegen;
use Rhino\Codegen\Relationship;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Helper\Table;

class XmlSerializer
{
    protected $entities;

    public function __construct(array $entities) {
        $this->entities = $entities;
    }

    public function serialize()
    {
        $xml = new \SimpleXMLElement('<codegen/>');
        foreach ($this->entities as $entity) {
            $nodeEntity = $xml->addChild('entity');
            $nodeEntity['name'] = $entity->getName();

            foreach ($entity->getAttributes() as $attribute) {
                // if ($attribute->isForeignKey()) {
                //     continue;
                // }
                switch ($attribute->getType()) {
                    case 'string': {
                        $nodeAttribute = $nodeEntity->addChild('string-attribute');
                        break;
                    }
                    case 'int': {
                        $nodeAttribute = $nodeEntity->addChild('int-attribute');
                        break;
                    }
                    case 'datetime': {
                        $nodeAttribute = $nodeEntity->addChild('date-time-attribute');
                        break;
                    }
                    case 'decimal': {
                        $nodeAttribute = $nodeEntity->addChild('decimal-attribute');
                        break;
                    }
                }
                $nodeAttribute['name'] = $attribute->getName();
            }

            // foreach ($entity->getRelationships() as $relationship) {
            //     if ($relationship->getFrom() == $entity) {
            //         if ($relationship instanceof Relationship\BelongsTo) {
            //             $nodeAttribute = $nodeEntity->addChild('belongs-to');
            //         } elseif ($relationship instanceof Relationship\HasMany) {
            //             $nodeAttribute = $nodeEntity->addChild('has-many');
            //         } elseif ($relationship instanceof Relationship\HasOne) {
            //             $nodeAttribute = $nodeEntity->addChild('has-one');
            //         }
            //         $nodeAttribute['entity'] = $relationship->getName();
            //     }
            // }
        }

        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        die($dom->saveXML());
    }
}
