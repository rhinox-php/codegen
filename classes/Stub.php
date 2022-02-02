<?php

namespace Rhino\Codegen;

use Microsoft\PhpParser\Parser;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Microsoft\PhpParser\DiagnosticsProvider;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\PositionUtilities;
use Rhino\Codegen\FormatPhp;

class Stub
{
    private Codegen $codegen;
    private string $file;

    public function __construct(Codegen $codegen, string $file)
    {
        $this->codegen = $codegen;
        $this->file = $file;

    }

    public function generate(): string
    {
        if (!is_file($this->file)) {
            throw new \Exception('Invalid file: ' . $this->file);
        }
        $this->codegen->log('Generating stub for: ' . $this->file);
        $parser = new Parser();
        $root = $parser->parseSourceFile(file_get_contents($this->file));

        ob_start();
        echo "<?php" . PHP_EOL;
        foreach ($root->getChildNodes() as $child) {
            if ($child instanceof Node\Statement\ClassDeclaration) {
                $this->parseClass($root, $child);
            }
            if ($child instanceof Node\Statement\NamespaceUseDeclaration) {
                echo $child->getText($root) . PHP_EOL;
            }
        }
        $stub = ob_get_clean();
        return FormatPhp::formatString($stub);
    }

    protected function parseClass(Node\SourceFileNode $root, Node\Statement\ClassDeclaration $class)
    {
        $className = $class->name->getText($root);
        echo "die('This file should not be included, only analyzed by your IDE');" . PHP_EOL;
        echo "class $className extends \Illuminate\Support\Facades\Facade {" . PHP_EOL;
        foreach ($class->classMembers as $member) {
            $this->parseClassMember($root, $className, $member);
        }
        echo "}" . PHP_EOL;
    }

    protected function parseClassMember(Node\SourceFileNode $root, string $className, $members)
    {
        if (!is_array($members)) {
            $members = [$members];
        }
        $previousMethodName = null;
        $previousPropertyName = null;
        $previousConstName = null;
        foreach ($members as $member) {
            if ($member instanceof Node\MethodDeclaration) {
                if ($member->name->getText($root) === '__construct') {
                    continue;
                }
                foreach ($member->modifiers as $modifier) {
                    if ($modifier->getText($root) !== 'public') {
                        continue 2;
                    }
                    echo $modifier->getText($root) . ' static ';
                }
                echo $member->functionKeyword->getText($root) . ' ';
                echo $member->name->getText($root) . '(';
                if ($member->parameters) {
                    echo $member->parameters->getText($root);
                }
                echo ')';
                if ($member->returnTypeList) {
                    echo ': ' . $member->returnTypeList->getText($root);
                }
                foreach ($member->getChildNodes() as $child) {
                    // var_dump(get_class($child));
                }
                echo ' {}';
                echo PHP_EOL;
                // echo $member->getText() . PHP_EOL;
                // $this->replaceClassMethod($className, $member->name->getText($this->root1), $member->getFullText(), $previousMethodName);
                // $previousMethodName = $member->name->getText($this->root1);
            } elseif ($member instanceof Node\PropertyDeclaration) {
                // $this->replaceClassProperty($className, $this->getPropertyName($member, $this->root1), $member->getFullText(), $previousPropertyName);
                // $previousPropertyName = $this->getPropertyName($member, $this->root1);
            } elseif ($member instanceof Node\ClassConstDeclaration) {
                // $this->replaceClassConst($className, $this->getConstName($member, $this->root1), $member->getFullText(), $previousConstName);
                // $previousConstName = $this->getConstName($member, $this->root1);
            }
        }
    }
}
