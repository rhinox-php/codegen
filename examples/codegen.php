<?php
// require_once __DIR__ . '/../vendor/autoload.php';

// (new Rhino\Codegen\XmlParser(__DIR__ . '/person.xml'))->parse();
// $cwd = getcwd();
// chdir(__DIR__ . '/example/classes');
// system('php-cs-fixer fix . --level=psr2 -vvv');
// chdir($cwd);

use Rhino\Codegen;
use Rhino\Codegen\Template;

$codegen = new Codegen\Codegen\Web();
$codegen->setPath(__DIR__ . '/output/');
$codegen->setNamespace('Acme\Example');
$codegen->setDatabaseCharset('utf8');
$codegen->setDatabaseCollation('utf8_unicode_ci');

(new Codegen\XmlParser($codegen, __DIR__ . '/codegen.xml'))->parse();

$codegen->setDatabase('mysql:host=127.0.0.1', 'codegen_example', 'root', 'root');

// $codegen->addHook(new Codegen\Hook\PhpCsFixer());

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

$codegen->addTemplate(new Template\Admin\Controller());

$jsonApi = (new Template\JsonApi\JsonApi())
    ->setTitle('Example')
    ->setVersion('1.0')
    ->setEmail('test@example.com')
    ->setHost('example.com')
    ->setBasePath('/api/v1');
$codegen->addTemplate(new Template\JsonApi\Swagger($jsonApi));

$codegen->addTemplate(new Template\Generic\Build());
$codegen->addTemplate(new Template\Generic\Bootstrap());
$codegen->addTemplate(new Template\Generic\Composer());
$codegen->addTemplate(new Template\Generic\ApiTest());
$codegen->addTemplate(new Template\Generic\FastRoute());
$codegen->addTemplate(new Template\Generic\Sql());

$codegen->addTemplate(new Template\Generic\TestPhpUnit());
$codegen->addTemplate(new Template\Generic\TestCoverage());

return $codegen;