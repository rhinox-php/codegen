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
        if (!$file2) {
            throw new \Exception('Could not find file 2: ' . $file2);
        }
    }

    public static function merge(Codegen $codegen, string $file1, string $file2): self {
        return (new static($codegen, $file1, $file2))->parse();
    }

    public function parse() {
        $this->codegen->log('Parsing', $this->file1);

        $parser = new Parser();

        $this->root1 = $parser->parseSourceFile(file_get_contents($this->file1));

        $this->output = file_get_contents($this->file2);
        $this->root2 = $parser->parseSourceFile($this->output);

        $errors = DiagnosticsProvider::getDiagnostics($this->root1);
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->codegen->log($error);
            }
            return;
        }

        $errors = DiagnosticsProvider::getDiagnostics($this->root2);
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->codegen->log($error);
            }
            return;
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

    public function parseClass(Node\Statement\ClassDeclaration $class) {
        $className = $class->name->getText($this->root1);
        foreach ($class->classMembers as $member) {
            $this->parseClassMember($className, $member);
        }
    }

    public function parseClassMember(string $className, $members) {
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

    public function replaceClassMethod(string $className1, string $memberName, string $replacement) {
        foreach ($this->iterateClassMembers($this->root2) as $className2 => $member) {
            if ($className1 != $className2) {
                continue;
            }
            if ($member instanceof Node\MethodDeclaration) {
                if ($member->name->getText($this->root2) == $memberName) {
                    if ($member->getFullText() != $replacement) {
                        $this->codegen->log('Replacing class method', $className1, $memberName);
                        $this->output = str_replace($member->getFullText(), $replacement, $this->output);
                    }
                }
            }
        }
    }

    public function replaceClassProperty(string $className1, string $memberName, string $replacement) {
        foreach ($this->iterateClassMembers($this->root2) as $className2 => $member) {
            if ($className1 != $className2) {
                continue;
            }
            if ($member instanceof Node\PropertyDeclaration) {
                if ($member->propertyElements->getText($this->root2) == $memberName) {
                    if ($member->getFullText() != $replacement) {
                        $this->codegen->log('Replacing class property', $className1, $memberName);
                        $this->output = str_replace($member->getFullText(), $replacement, $this->output);
                    }
                }
            }
        }
    }

    public function iterateClassMembers($root) {
        foreach ($this->root2->getChildNodes() as $child) {
            if ($child instanceof Node\Statement\ClassDeclaration) {
                $className = $child->name->getText($this->root2);
                foreach ($child->classMembers as $members) {
                    if (!is_array($members)) {
                        $members = [$members];
                    }
                    foreach ($members as $member) {
                        yield $className => $member;
                    }
                }
            }
        }
    }
}

