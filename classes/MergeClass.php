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

    public static function mergeFiles(Codegen $codegen, string $fromFile, string $intoFile): self
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

    public static function mergeStrings(Codegen $codegen, string $fromString, string $intoString): string
    {
        $instance = new static($codegen);
        $instance->setClassSourceFrom($fromString);
        $instance->setClassSourceInto($intoString);
        $instance->parse();

        $output = $instance->getOutput();
        $output = FormatPhp::formatString($output);

        return $output;
    }

    public function parse()
    {
        $this->parser = new Parser();

        $this->codegen->debug('Parsing "from" class source...');
        $this->root1 = $this->parser->parseSourceFile($this->classSourceFrom);
        if (!$this->validate($this->classSourceFrom, $this->root1)) {
            return $this;
        }

        $this->output = $this->classSourceInto;
        $this->codegen->debug('Parsing "into" class source...');
        $this->root2 = $this->parser->parseSourceFile($this->output);

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
                $this->codegen->log($this->prependLineNumbers($content), $error->message, 'line', $lineCharacterPosition->line);
                throw new \Exception('Error parsing PHP source: ' . $error->message . ' line: ' . $lineCharacterPosition->line);
            }
        }
        return true;
    }

    private function prependLineNumbers(string $content)
    {
        $lineNumber = 1;
        $result = [];
        foreach (preg_split('/((\r?\n)|(\r\n?))/', $content) as $line) {
            $result[] = $lineNumber . ': ' . $line;
            $lineNumber++;
        }
        return implode(PHP_EOL, $result);
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
        $previousMethodName = null;
        $previousPropertyName = null;
        $previousConstName = null;
        foreach ($members as $member) {
            if ($member instanceof Node\MethodDeclaration) {
                $this->replaceClassMethod($className, $member->name->getText($this->root1), $member->getFullText(), $previousMethodName);
                $previousMethodName = $member->name->getText($this->root1);
            } elseif ($member instanceof Node\PropertyDeclaration) {
                $this->replaceClassProperty($className, $this->getPropertyName($member, $this->root1), $member->getFullText(), $previousPropertyName);
                $previousPropertyName = $this->getPropertyName($member, $this->root1);
            } elseif ($member instanceof Node\ClassConstDeclaration) {
                $this->replaceClassConst($className, $this->getConstName($member, $this->root1), $member->getFullText(), $previousConstName);
                $previousConstName = $this->getConstName($member, $this->root1);
            }
        }
    }

    protected function replaceClassMethod(string $className1, string $memberName, string $replacement, ?string $previousMethodName)
    {
        $this->codegen->debug('Found class method', $className1, $memberName);
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
            $start = null;
            if ($previousMethodName) {
                $methodDeclaration = $this->getMethodDeclaration($className1, $previousMethodName, $this->root2);
                if ($methodDeclaration) {
                    $start = $methodDeclaration->compoundStatementOrSemicolon->closeBrace->start + 1;
                }
            }
            if (!$start) {
                $classDeclaration = $this->getClassDeclaration($className1, $this->root2);
                if ($classDeclaration) {
                    $start = $classDeclaration->classMembers->closeBrace->start;
                }
            }
            if (!$start) {
                $this->codegen->log('Cannot find replacement start point', $className1, $memberName);
                return;
            }
            $replacement = PHP_EOL . '    ' . trim($replacement) . PHP_EOL;
            $this->setOutput(substr_replace($this->output, $replacement, $start, 0));
        }
        // @todo create class if it doesn't exist
    }

    protected function replaceClassProperty(string $className1, string $memberName, string $replacement, ?string $previousPropertyName)
    {
        $this->codegen->debug('Found class property', $className1, $memberName);
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

            $start = null;
            if ($previousPropertyName) {
                $propertyDeclaration = $this->getPropertyDeclaration($className1, $previousPropertyName, $this->root2);
                if ($propertyDeclaration) {
                    $start = $propertyDeclaration->semicolon->start + 1;
                }
            }
            if (!$start) {
                $classDeclaration = $this->getClassDeclaration($className1, $this->root2);
                if ($classDeclaration) {
                    $start = $classDeclaration->classMembers->closeBrace->start;
                }
            }
            if (!$start) {
                $this->codegen->log('Cannot find replacement start point', $className1, $memberName);
                return;
            }


            $classDeclaration = $this->getClassDeclaration($className1, $this->root2);
            if (!$classDeclaration) {
                return;
            }
            $replacement = PHP_EOL . '    ' . trim($replacement) . PHP_EOL;
            $this->setOutput(substr_replace($this->output, $replacement, $classDeclaration->classMembers->closeBrace->start, 0));
        }
        // @todo create class if it doesn't exist
    }

    protected function replaceClassConst(string $className1, string $memberName, string $replacement, ?string $previousConstName)
    {
        $this->codegen->debug('Found class const', $className1, $memberName);
        $found = false;
        foreach ($this->iterateClassMembers($className1, $this->root2, Node\ClassConstDeclaration::class) as $member) {
            if ($this->getConstName($member, $this->root2) == $memberName) {
                $found = true;
                if ($this->checkDiff($member->getFullText(), $replacement)) {
                    $this->codegen->log('Replacing class const', $className1, $memberName);
                    $this->setOutput(str_replace($member->getFullText(), $replacement, $this->output));
                    $found = true;
                }
            }
        }
        if (!$found) {
            $this->codegen->log('Class const not found, appending', $className1, $memberName);

            $start = null;
            if ($previousConstName) {
                $constDeclaration = $this->getConstDeclaration($className1, $previousConstName, $this->root2);
                if ($constDeclaration) {
                    $start = $constDeclaration->semicolon->start + 1;
                }
            }
            if (!$start) {
                $classDeclaration = $this->getClassDeclaration($className1, $this->root2);
                if ($classDeclaration) {
                    $start = $classDeclaration->classMembers->closeBrace->start;
                }
            }
            if (!$start) {
                $this->codegen->log('Cannot find replacement start point', $className1, $memberName);
                return;
            }


            $classDeclaration = $this->getClassDeclaration($className1, $this->root2);
            if (!$classDeclaration) {
                return;
            }
            $replacement = PHP_EOL . '    ' . trim($replacement) . PHP_EOL;
            $this->setOutput(substr_replace($this->output, $replacement, $classDeclaration->classMembers->closeBrace->start, 0));
        }
    }

    protected function checkDiff($a, $b)
    {
        $a = $this->removeEmptyLines($a);
        $b = $this->removeEmptyLines($b);
        if (preg_replace('/\s+/', '', $a) != preg_replace('/\s+/', '', $b)) {
            $this->logDiff($a, $b);
            return true;
        }
        return false;
    }

    protected function removeEmptyLines(string $text): string
    {
        $text = explode(PHP_EOL, $text);
        $text = array_filter($text, function ($line) {
            return trim($line) != '';
        });
        return implode(PHP_EOL, $text);
    }

    protected function getPropertyName($member, $root)
    {
        $text = $member->propertyElements->getText($root);
        preg_match('/\$([a-z0-9_]+)/i', $text, $matches);
        return $matches[1];
    }

    protected function getConstName($member, $root)
    {
        $text = $member->constElements->getText($root);
        preg_match('/^([a-z0-9_]+)/i', $text, $matches);
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

    protected function getMethodDeclaration($className1, $methodName1, $root)
    {
        foreach ($this->iterateClassMembers($className1, $root, Node\MethodDeclaration::class) as $methodDeclaration) {
            $methodName2 = $methodDeclaration->name->getText($root);
            if ($methodName1 == $methodName2) {
                return $methodDeclaration;
            }
        }
        return null;
    }

    protected function getPropertyDeclaration($className1, $propertyName1, $root)
    {
        foreach ($this->iterateClassMembers($className1, $root, Node\PropertyDeclaration::class) as $propertyDeclaration) {
            $propertyName2 = $this->getPropertyName($propertyDeclaration, $root);
            if ($propertyName1 == $propertyName2) {
                return $propertyDeclaration;
            }
        }
        return null;
    }

    protected function getConstDeclaration($className1, $constName1, $root)
    {
        foreach ($this->iterateClassMembers($className1, $root, Node\ClassConstDeclaration::class) as $constDeclaration) {
            $constName2 = $this->getConstName($constDeclaration, $root);
            if ($constName1 == $constName2) {
                return $constDeclaration;
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
        $tempFile = TempFile::createUnique();
        $tempFile->putContents($output);
        $phpBin = $_SERVER['_'];
        exec($phpBin . ' -l ' . $tempFile->getPath(), $lintOutput, $result);
        if ($result !== 0) {
            $this->logDiff($this->output, $output);
            throw new \Exception('Invalid PHP code generated: ' . implode(PHP_EOL, $lintOutput));
        }
        $this->output = $output;
        $this->root2 = $this->parser->parseSourceFile($this->output);
    }

    protected function logDiff($a, $b)
    {
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
