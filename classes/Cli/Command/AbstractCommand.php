<?php
namespace Rhino\Codegen\Cli\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends \Symfony\Component\Console\Command\Command {
    protected function getCodegen(string $schema, bool $dryRun, bool $debug): \Rhino\Codegen\Codegen {
        switch (pathinfo($schema, PATHINFO_EXTENSION)) {
            case 'php': {
                if (!is_file($schema)) {
                    throw new \Exception('Could not find codegen schema file: ' . $schema);
                }
                $codegen = require $schema;
                break;
            }
            case 'xml': {
                $xmlParser = new \Rhino\Codegen\XmlParser($schema);
                $codegen = $xmlParser->parse();
                break;
            }
        }
        $codegen->setDryRun($dryRun);
        $codegen->setDebug($debug);
        return $codegen;
    }
}
