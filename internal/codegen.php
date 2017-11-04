<?php
use Rhino\Codegen\Template;

$codegen = new \Rhino\Codegen\Codegen\Web();
$codegen->setPath(__DIR__ . '/generated/');
$codegen->setNamespace('Rhino\Codegen');

(new \Rhino\Codegen\XmlParser($codegen, __DIR__ . '/codegen.xml'))
    ->parse();

$codegen->addTemplate(new Template\Generic\BinLint());
$codegen->addTemplate(new Template\Generic\ModelClass());

return $codegen;
