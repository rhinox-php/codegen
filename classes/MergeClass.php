<?php
namespace Rhino\Codegen;

use Microsoft\PhpParser\{DiagnosticsProvider, Node, Parser, PositionUtilities};

class MergeClass {
    protected $file1;
    protected $file2;
    protected $root1;
    protected $root2;
    protected $output;

    public function __construct(Codegen $codegen, string $file1, string $file2) {
        $this->codegen = $codegen;
        $this->file1 = realpath($file1);
        if (!$this->file1) {
            throw new \Exception('Could not find file 1: ' . $file1);
        }
        $this->file2 = realpath($file2);
        if (!$this->file2) {
            throw new \Exception('Could not find file 2: ' . $file2);
        }
    }

    public static function merge(Codegen $codegen, string $file1, string $file2): self {
        return (new static($codegen, $file1, $file2))->parse();
    }

    public function parse() {

        $this->parser = new Parser();

        $this->codegen->log('Parsing', $this->file1);
        $this->root1 = $this->parser->parseSourceFile(file_get_contents($this->file1));
        if (!$this->validate($this->file1, $this->root1)) {
            return $this;
        }

        $this->output = file_get_contents($this->file2);
        $this->codegen->log('Parsing', $this->file2);
        $this->root2 = $this->parser->parseSourceFile($this->output);

        $errors = DiagnosticsProvider::getDiagnostics($this->root2);
        if (!$this->validate($this->file2, $this->root2)) {
            return $this;
        }

        foreach ($this->root1->getChildNodes() as $child) {
            // var_dump(get_class($child));
            if ($child instanceof Node\Statement\ClassDeclaration) {
                $this->parseClass($child);
            }
        }

        $this->codegen->writeFile($this->file2, $this->output);

        return $this;
    }

    protected function validate($file, $root) {
        $errors = DiagnosticsProvider::getDiagnostics($root);
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $lineCharacterPosition = PositionUtilities::getLineCharacterPositionFromPosition(
                    $error->start,
                    $root->getFileContents()
                );
                $this->codegen->log($file, $error->message, 'line', $lineCharacterPosition->line);
                return false;
            }
        }
        return true;
    }

    protected function parseClass(Node\Statement\ClassDeclaration $class) {
        $className = $class->name->getText($this->root1);
        foreach ($class->classMembers as $member) {
            $this->parseClassMember($className, $member);
        }
    }

    protected function parseClassMember(string $className, $members) {
        if (!is_array($members)) {
            $members = [$members];
        }
        foreach ($members as $member) {
            if ($member instanceof Node\MethodDeclaration) {
                $this->replaceClassMethod($className, $member->name->getText($this->root1), $member->getFullText());
            } elseif ($member instanceof Node\PropertyDeclaration) {
                $this->replaceClassProperty($className, $member->propertyElements->getText($this->root1), $member->getFullText());
            }
        }
    }

    protected function replaceClassMethod(string $className1, string $memberName, string $replacement) {
        $found = false;
        foreach ($this->iterateClassMembers($className1, $this->root2, Node\MethodDeclaration::class) as $member) {
            if ($member->name->getText($this->root2) == $memberName) {
                $found = true;
                if ($member->getFullText() != $replacement) {
                    $this->codegen->log('Replacing class method', $className1, $memberName);
                    $this->setOutput(str_replace($member->getFullText(), $replacement, $this->output));
                }
            }
        }
        if (!$found) {
            $this->codegen->log('Class method not found, appending', $className1, $memberName);
            $classDeclaration = $this->getClassDeclaration($className1, $this->root2);
            $replacement = PHP_EOL . '    ' . trim($replacement) . PHP_EOL;
            $this->setOutput(substr_replace($this->output, $replacement, $classDeclaration->classMembers->closeBrace->start, 0));
        }
        // @todo create class if it doesnt exist
    }

    protected function replaceClassProperty(string $className1, string $memberName, string $replacement) {
        $found = false;
        foreach ($this->iterateClassMembers($className1, $this->root2, Node\PropertyDeclaration::class) as $member) {
            if ($member->propertyElements->getText($this->root2) == $memberName) {
                if ($member->getFullText() != $replacement) {
                    $this->codegen->log('Replacing class property', $className1, $memberName);
                    $this->setOutput(str_replace($member->getFullText(), $replacement, $this->output));
                    $found = true;
                }
            }
        }
    }

    protected function getClassDeclaration($className1, $root) {
        foreach ($root->getChildNodes() as $child) {
            if ($child instanceof Node\Statement\ClassDeclaration) {
                $className2 = $child->name->getText($root);
                if ($className1 == $className2) {
                    return $child;
                }
            }
        }
        return null;
    }

    protected function iterateClassMembers($className1, $root, string $type = null) {
        $classDeclaration = $this->getClassDeclaration($className1, $root);
        if (!$classDeclaration) {
            return;
        }
        foreach ($classDeclaration->classMembers as $members) {
            if (!is_array($members)) {
                $members = [$members];
            }
            foreach ($members as $member) {
                if (!$type || $member instanceof $type) {
                    yield $member;
                }
            }
        }
    }

    protected function setOutput(string $output) {
        $this->output = $output;
        $this->root2 = $this->parser->parseSourceFile($this->output);
    }
}

