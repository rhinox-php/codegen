<?php
require_once './classes/CodeGen.php';
require_once './classes/XmlParser.php';
require_once './classes/Entity.php';
require_once './classes/Attribute/StringAttribute.php';

(new Rhino\Codegen\XmlParser(__DIR__ . '/example/person.xml'))->parse();
