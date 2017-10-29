<?php
namespace Rhino\Codegen\Cli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

class Watch extends AbstractCommand {
    protected function configure() {
        $this->setName('watch')
            ->setDescription('Watch code for changes and trigger generation automatically')
            ->addOption('execute', 'x', InputOption::VALUE_NONE, 'Execute code generation (otherwise dry run).')
            ->addOption('schema', 's', InputOption::VALUE_REQUIRED, 'Codegen schema file to load.', 'codegen.php')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Enable debug output');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $codegen = $this->getCodegen($input->getOption('schema'), !$input->getOption('execute'), $input->getOption('debug'));
        $watcher = new \Rhino\Codegen\Watch\Watcher(function() use($input, $output) {
            $command = $this->getApplication()->find('gen');
            $arguments = array(
                'command' => 'gen',
                '--execute' => $input->getOption('execute'),
                '--schema' => $input->getOption('schema'),
                '--debug' => $input->getOption('debug'),
            );
            $genInput = new ArrayInput($arguments);
            $returnCode = $command->run($genInput, $output);
        });
        $watcher->addDirectory(__DIR__ . '/../../../');
        $watcher->addDirectory('.');
        $watcher->start();
    }
}
