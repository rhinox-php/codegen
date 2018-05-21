<?php
namespace Rhino\Codegen;

use Microsoft\PhpParser\DiagnosticsProvider;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\PositionUtilities;

class MergeClass
{
    protected $classSourceFrom;
    protected $classSourceInto;
    protected $root1;
    protected $root2;
    protected $output;

    public function __construct(Codegen $codegen)
    {
        $this->codegen = $codegen;
    }

    public function setClassSourceFrom(string $classSourceFrom): self
    {
        $this->classSourceFrom = $classSourceFrom;
        return $this;
    }

    public function setClassSourceInto(string $classSourceInto): self
    {
        $this->classSourceInto = $classSourceInto;
        return $this;
    }

    public static function merge(Codegen $codegen, string $fromFile, string $intoFile): self
    {
        $realFromFile = realpath($fromFile);
        if (!$realFromFile) {
            throw new \Exception('Could not find file 1: ' . $fromFile);
        }
        $realIntoFile = realpath($intoFile);
        if (!$realIntoFile) {
            throw new \Exception('Could not find file 2: ' . $intoFile);
        }

        $instance = new static($codegen);
        $codegen->log('Loading ' . $realFromFile);
        $instance->setClassSourceFrom(file_get_contents($realFromFile));
        $codegen->log('Loading ' . $realIntoFile);
        $instance->setClassSourceInto(file_get_contents($realIntoFile));
        $instance->parse();

        $codegen->writeFile($realIntoFile, $instance->getOutput());
        FormatPhp::formatFile($realIntoFile);

        return $instance;
    }

    public function parse()
    {
        $this->parser = new Parser();

        $this->codegen->log('Parsing "from" class source...');
        $this->root1 = $this->parser->parseSourceFile($this->classSourceFrom);
        if (!$this->validate($this->classSourceFrom, $this->root1)) {
            return $this;
        }

        $this->output = $this->classSourceInto;
        $this->codegen->log('Parsing "into" class source...');
        $this->root2 = $this->parser->parseSourceFile($this->output);

        $errors = DiagnosticsProvider::getDiagnostics($this->root2);
        if (!$this->validate($this->classSourceInto, $this->root2)) {
            return $this;
        }

        foreach ($this->root1->getChildNodes() as $child) {
            if ($child instanceof Node\Statement\ClassDeclaration) {
                $this->parseClass($child);
            }
        }

        return $this;
    }

    public function getOutput()
    {
        return $this->output;
    }

    protected function validate($content, $root)
    {
        assert(is_string($content), new \InvalidArgumentException('Expected source to be a string.'));
        assert(strlen($content) > 0, new \InvalidArgumentException('Expected source string to not be empty.'));

        $errors = DiagnosticsProvider::getDiagnostics($root);
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $lineCharacterPosition = PositionUtilities::getLineCharacterPositionFromPosition(
                    $error->start,
                    $root->getFileContents()
                );
                $this->codegen->log($content, $error->message, 'line', $lineCharacterPosition->line);
                return false;
            }
        }
        return true;
    }

    protected function parseClass(Node\Statement\ClassDeclaration $class)
    {
        $className = $class->name->getText($this->root1);
        foreach ($class->classMembers as $member) {
            $this->parseClassMember($className, $member);
        }
    }

    protected function parseClassMember(string $className, $members)
    {
        if (!is_array($members)) {
            $members = [$members];
        }
        foreach ($members as $member) {
            if ($member instanceof Node\MethodDeclaration) {
                $this->replaceClassMethod($className, $member->name->getText($this->root1), $member->getFullText());
            } elseif ($member instanceof Node\PropertyDeclaration) {
                $this->replaceClassProperty($className, $this->getPropertyName($member, $this->root1), $member->getFullText());
            }
        }
    }

    protected function replaceClassMethod(string $className1, string $memberName, string $replacement)
    {
        $this->codegen->log('Found class method', $className1, $memberName);
        $found = false;
        foreach ($this->iterateClassMembers($className1, $this->root2, Node\MethodDeclaration::class) as $member) {
            if ($member->name->getText($this->root2) == $memberName) {
                $found = true;
                if ($this->checkDiff($member->getFullText(), $replacement)) {
                    $this->codegen->log('Replacing class method', $className1, $memberName);
                    $this->setOutput(str_replace($member->getFullText(), $replacement, $this->output));
                }
            }
        }
        if (!$found) {
            $this->codegen->log('Class method not found, appending', $className1, $memberName);
            $classDeclaration = $this->getClassDeclaration($className1, $this->root2);
            if (!$classDeclaration) {
                return;
            }
            $replacement = PHP_EOL . '    ' . trim($replacement) . PHP_EOL;
            $this->setOutput(substr_replace($this->output, $replacement, $classDeclaration->classMembers->closeBrace->start, 0));
        }
        // @todo create class if it doesn't exist
    }

    protected function replaceClassProperty(string $className1, string $memberName, string $replacement)
    {
        $this->codegen->log('Found class property', $className1, $memberName);
        $found = false;
        foreach ($this->iterateClassMembers($className1, $this->root2, Node\PropertyDeclaration::class) as $member) {
            if ($this->getPropertyName($member, $this->root2) == $memberName) {
                $found = true;
                if ($this->checkDiff($member->getFullText(), $replacement)) {
                    $this->codegen->log('Replacing class property', $className1, $memberName);
                    $this->setOutput(str_replace($member->getFullText(), $replacement, $this->output));
                    $found = true;
                }
            }
        }
        if (!$found) {
            $this->codegen->log('Class property not found, appending', $className1, $memberName);
            $classDeclaration = $this->getClassDeclaration($className1, $this->root2);
            if (!$classDeclaration) {
                return;
            }
            $replacement = PHP_EOL . '    ' . trim($replacement) . PHP_EOL;
            $this->setOutput(substr_replace($this->output, $replacement, $classDeclaration->classMembers->closeBrace->start, 0));
        }
        // @todo create class if it doesn't exist
    }

    protected function checkDiff($a, $b) {
        $a = $this->removeEmptyLines($a);
        $b = $this->removeEmptyLines($b);
        if (preg_replace('/\s+/', '', $a) != preg_replace('/\s+/', '', $b)) {
            $this->logDiff($a, $b);
            return true;
        }
        return false;
    }

    protected function removeEmptyLines(string $text): string {
        $text = explode(PHP_EOL, $text);
        $text = array_filter($text, function($line) {
            return trim($line) != '';
        });
        return implode(PHP_EOL, $text);
    }

    protected function getPropertyName($member, $root) {
        $text = $member->propertyElements->getText($root);
        preg_match('/\$([a-z0-9_]+)/i', $text, $matches);
        return $matches[1];
    }

    protected function getClassDeclaration($className1, $root)
    {
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

    protected function iterateClassMembers($className1, $root, string $type = null)
    {
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

    protected function setOutput(string $output)
    {
        $this->output = $output;
        $this->root2 = $this->parser->parseSourceFile($this->output);
    }

    protected function logDiff($a, $b) {
        $a = explode(PHP_EOL, $a);
        $b = explode(PHP_EOL, $b);
        $cols = exec('tput cols');
        $cols = $cols / 2 - 4;
        echo str_repeat('-', $cols * 2 + 4) . PHP_EOL;
        for ($i = 0; $i < count($a) || $i < count($b); $i++) {
            $a1 = substr(str_pad($a[$i] ?? '', $cols, ' ', STR_PAD_RIGHT), 0, $cols);
            $b1 = substr(str_pad($b[$i] ?? '', $cols, ' ', STR_PAD_RIGHT), 0, $cols);
            echo $a1 . ' | ' . $b1 . PHP_EOL;
        }
        echo str_repeat('-', $cols * 2 + 4) . PHP_EOL;
    }
}
