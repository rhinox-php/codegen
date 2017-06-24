<?php
namespace Rhino\Codegen\Cli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeMigration extends AbstractCommand {
    protected function configure() {
        $this->setName('make:migration')
            ->setDescription('Create a migration file')
            ->addArgument('migrationName', InputArgument::REQUIRED, 'Name of migration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $migrationName = $input->getArgument('migrationName');
        $migrationName = strtolower($migrationName);
        $migrationName = preg_replace('/[^a-z]+/', '_', $migrationName);
        $migrationName = trim($migrationName, '_');
        $file = './sql/up/' . date('Y_m_d_His_') . $migrationName . '.sql';
        if (!file_exists($file)) {
            $output->writeln($file);
            file_put_contents($file, '');
        }
    }
}
