<?php
namespace Rhino\Codegen\Cli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Merge extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('merge')
            ->setDescription('Merge generated classes with existing classes')
            ->addOption('overwrite', 'o', InputOption::VALUE_NONE, 'Overwrite existing files instead of copying them.')
            ->addOption('filter', 'f', InputOption::VALUE_REQUIRED, 'Filter files to merge.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filter = $input->getOption('filter');
        $codegen = $this->getCodegen($input->getOption('schema'), !$input->getOption('execute'), $input->getOption('debug'));
        $codegen->generate();
        $mapper = $codegen->getMergeFileMapper();
        if (!$mapper) {
            throw new \Exception('File mapper not set in Codegen config.');
        }
        $path = $codegen->getPath();
        $codegen->createDirectory($path . '/merged/');
        foreach ($codegen->getManifest()->getFiles() as $generatedFile => $hash) {
            $generatedFile = $path . $generatedFile;
            $existingFile = $mapper(realpath($generatedFile));
            if (is_file($existingFile) && is_file($generatedFile)) {
                if ($filter && !preg_match('/' . $filter . '/', $existingFile)) {
                    continue;
                }
                if ($input->getOption('overwrite')) {
                    $writeFile = $existingFile;
                } else {
                    $writeFile = $path . '/merged/' . basename($existingFile);
                    $codegen->copyFile($existingFile, $writeFile);
                }
                \Rhino\Codegen\MergeClass::mergeFiles($codegen, $generatedFile, $writeFile);
            }
        }
    }
}
