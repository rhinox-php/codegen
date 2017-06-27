<?php
namespace Rhino\Codegen\Cli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Init extends AbstractCommand {
    protected function configure() {
        $this->setName('init')
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Path to initialise codegen files.', '.')
            ->addOption('overwrite', 'o', InputOption::VALUE_NONE, 'Overwrite existing files.')
            ->setDescription('Init codegen files');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $path = $input->getOption('path');
        if (!is_dir($path)) {
            throw new \Exception('Invalid path ' . $path);
        }
        $path = realpath($path);
        $output->writeln('Initialising codegen at ' . $path);

        if (!is_dir($path)) {
            mkdir($path);
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__ . '/../init'), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $item) {
            $outputPath = $path . '/' . $iterator->getSubPathName();
            if ($item->isDir()) {
                if (!is_dir($outputPath)) {
                    $output->writeln('Creating directory ' . $outputPath);
                    mkdir($outputPath);
                }
            } else {
                if (!is_file($outputPath) || $input->getOption('overwrite')) {
                    $output->writeln('Creating ' . $outputPath);
                    copy($item, $outputPath);
                }
            }
        }
    }
}
