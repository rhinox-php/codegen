<?php

namespace Rhino\Codegen\Cli\Command;

use Rhino\Codegen\Template\Template;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RenderTemplateFile extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('render:template-file')
            ->setDescription('Generate code')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force regenerating all files')
            ->addOption('overwrite', 'o', InputOption::VALUE_NONE, 'Overwrite local changes to files')
            ->addOption('filter', 'i', InputOption::VALUE_REQUIRED, 'Filter entities')
            ->addOption('template', null, InputOption::VALUE_REQUIRED, 'Template class')
            ->addOption('template-file', null, InputOption::VALUE_REQUIRED, 'Template file to render')
            ->addOption('output-file', null, InputOption::VALUE_REQUIRED, 'Output file')
            ->addOption('data-file', null, InputOption::VALUE_REQUIRED, 'Data file to use');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $codegen = $this->getCodegen(
            $input->getOption('schema'),
            !$input->getOption('execute'),
            $input->getOption('debug'),
            $input->getOption('force'),
            $input->getOption('overwrite'),
            $input->getOption('filter'),
        );
        $templateClass = $input->getOption('template');
        $data = unserialize(file_get_contents($input->getOption('data-file')));
        /** @var Template */
        $template = new $templateClass();
        $template->setCodegen($codegen);
        $template->renderTemplateFile($input->getOption('template-file'), $input->getOption('output-file'), $data);
        unlink($input->getOption('data-file'));
        return 0;
    }
}
