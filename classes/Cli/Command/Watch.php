<?php
namespace Rhino\Codegen\Cli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Watch extends AbstractCommand {
    protected function configure() {
        $this->setName('watch')
            ->setDescription('Watch code for changes and trigger generation automatically')
            ->addOption('schema', 's', InputOption::VALUE_REQUIRED, 'Codegen schema file to load.', 'codegen.php')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Enable debug output');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $codegen = getCodegen($input->getOption('schema'), true, $input->getOption('debug'));
        $watcher = new \Rhino\Codegen\Watch\Watcher(function() {
            passthru('php ' . __FILE__ . ' gen -x');
        });
        $watcher->addDirectory(__DIR__ . '/../');
        $watcher->addDirectory('.');
        $watcher->start();
    }
}
