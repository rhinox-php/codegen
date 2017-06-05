<?php
if (!class_exists('Rhino\Codegen\Codegen')) {
    if (is_file(__DIR__ . '/../vendor/autoload.php')) {
        require __DIR__ . '/../vendor/autoload.php';
    } elseif (is_file(__DIR__ . '/../../../autoload.php')) {
        require __DIR__ . '/../../../autoload.php';
    } else {
        throw new Exception('Cannot file autoloader, tried ' . __DIR__ . '/../vendor/autoload.php and ' . __DIR__ . '/../../../autoload.php');
    }
}

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

function getCodegen(InputInterface $input, OutputInterface $output) {
    $schema = $input->getOption('schema');
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
    $codegen->setDryRun(!$input->getOption('execute'));
    $codegen->setDebug($input->getOption('debug') ? true : false);
    return $codegen;
}

$application = new Application();

$application->add(new class() extends Command {
    protected function configure() {
        $this->setName('gen')
            ->setDescription('Generate code')
            ->addOption('execute', 'x', InputOption::VALUE_NONE, 'Execute code generation (otherwise dry run).')
            ->addOption('schema', 's', InputOption::VALUE_REQUIRED, 'Codegen schema file to load.', 'codegen.php')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Enable debug output');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        getCodegen($input, $output)->generate();
    }
});

$application->add(new class() extends Command {
    protected function configure() {
        $this->setName('desc')
            ->setDescription('Describe entities')
            ->addOption('execute', 'x', InputOption::VALUE_NONE, 'Execute code generation (otherwise dry run).')
            ->addOption('schema', 's', InputOption::VALUE_REQUIRED, 'Codegen schema file to load.', 'codegen.php');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        getCodegen($input, $output)->describe();
    }
});

$application->add(new class() extends Command {
    protected function configure() {
        $this->setName('migrate')
            ->setDescription('Generate migrations')
            ->addOption('execute', 'x', InputOption::VALUE_NONE, 'Execute code generation (otherwise dry run).')
            ->addOption('schema', 's', InputOption::VALUE_REQUIRED, 'Codegen schema file to load.', 'codegen.php')
            ->addArgument('outputPath', InputArgument::REQUIRED, 'Path to output generated files to.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $schema = $input->getArgument('schema');
        $xmlParser = new \Rhino\Codegen\XmlParser($schema);
        $codegen = $xmlParser->parse();
        $codegen->setDryRun(!$input->getOption('execute'));
        $codegen->migrate($input->getArgument('outputPath'));
    }
});

$application->add(new class() extends Command {
    protected function configure() {
        $this->setName('db:reset')
            ->setDescription('Reset table')
            ->addOption('execute', 'x', InputOption::VALUE_NONE, 'Execute code generation (otherwise dry run).')
            ->addOption('schema', 's', InputOption::VALUE_REQUIRED, 'Codegen schema file to load.', 'codegen.php')
            ->addArgument('entity', InputArgument::OPTIONAL, 'Entity type to reset.')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Enable debug output');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        getCodegen($input, $output)->dbReset();
    }
});

$application->add(new class() extends Command {
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
});

$application->add(new class() extends Command {
    protected function configure() {
        $this->setName('init')
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Path to initialise codegen files.', '.')
            ->setDescription('Init codegen files');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $path = $input->getOption('path');
        if (!is_dir($path)) {
            throw new \Exception('Invalid path ' . $path);
        }
        $path = realpath($path);
        $output->writeln('Initialising codegen at ' . $path);

        $source = "dir/dir/dir";

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
                if (!is_file($outputPath)) {
                    $output->writeln('Creating ' . $outputPath);
                    copy($item, $outputPath);
                }
            }
        }
    }
});

$application->run();
