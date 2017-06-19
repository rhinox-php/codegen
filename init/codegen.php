<?php
use Rhino\Codegen;
use Rhino\Codegen\Template;

$codegen = new Codegen\Codegen\Web();
$codegen->setPath(__DIR__ . '/../');
$codegen->setNamespace('App');

(new Codegen\XmlParser($codegen, __DIR__ . '/codegen.xml'))
    ->parse();

$codegen->setDatabase('mysql:host=127.0.0.1', 'app', 'root', 'root');

$codegen->addTemplate(new Template\Generic\Bin());

$codegen->addTemplate(new Template\Generic\Model());
$codegen->addTemplate(new Template\Generic\ModelInitial());
$codegen->addTemplate(new Template\Generic\ModelPdo());
$codegen->addTemplate(new Template\Generic\ModelSerializer());

$codegen->addTemplate(new Template\Generic\Controller());
$codegen->addTemplate(new Template\Generic\ControllerInitial());
$codegen->addTemplate(new Template\Generic\ControllerHome());
$codegen->addTemplate(new Template\Generic\ControllerApi());
$codegen->addTemplate(new Template\Generic\ControllerApiInitial());

$codegen->addTemplate(new Template\JsonApi\Swagger());
$codegen->addTemplate(new Template\Generic\Build());
$codegen->addTemplate(new Template\Generic\Bootstrap());
$codegen->addTemplate(new Template\Generic\Composer());
$codegen->addTemplate(new Template\Generic\ApiTest());
$codegen->addTemplate(new Template\Generic\FastRoute());
$codegen->addTemplate(new Template\Generic\Sql());

return $codegen;
