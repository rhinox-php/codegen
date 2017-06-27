<?php
namespace Rhino\Codegen\Cli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DbMigrate extends AbstractCommand {
    protected function configure() {
        $this->setName('db:migrate')
            ->setDescription('Generate migrations')
            ->addOption('execute', 'x', InputOption::VALUE_NONE, 'Execute code generation (otherwise dry run).')
            ->addOption('schema', 's', InputOption::VALUE_REQUIRED, 'Codegen schema file to load.', 'codegen.php')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Enable debug output');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getCodegen($input->getOption('schema'), !$input->getOption('execute'), $input->getOption('debug'))
            ->dbMigrate();
    }
}
