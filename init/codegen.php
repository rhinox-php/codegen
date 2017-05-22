<?php
$codegen = new \Rhino\Codegen\Codegen();
$codegen->setPath(__DIR__ . '/generated/');
$codegen->setNamespace('App');

(new \Rhino\Codegen\XmlParser($codegen, __DIR__ . '/codegen.xml'))
    ->parse();

$codegen->addTemplate((new \Rhino\Codegen\Template\Generic\Model())
    ->setPath(__DIR__ . '/app/Generated/Models/')
    ->setNamespace('App\Model')
    ->setImplementedNamespace('App\Model'));

return $codegen;
