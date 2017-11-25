<?php
namespace Rhino\Codegen\Codegen\Web;

class Gulp
{
    protected $tasks;
    protected $codegen;
    protected $js = [];

    public function __construct(\Rhino\Codegen\Codegen $codegen)
    {
        $this->codegen = $codegen;
        $this->codegen->npm->addDevDependency('gulp', '^3.9');
        $this->codegen->npm->addDevDependency('gulp-expect-file', '^0.0.7');
        $this->codegen->npm->addDevDependency('gulp-scss', '^1.4');
    }

    public function generate()
    {
        $start = [];
        foreach ($this->tasks as $name => $js) {
            $start[] = "gulp.start('$name');";
        }
        $js = $this->append("
            const gulp = require('gulp');

            const expectFile = require('gulp-expect-file');
            const scss = require('gulp-scss');

            gulp.task('watch', ['default'], () => {
                gulp.watch('src/**', ['default']);
            });

            gulp.task('default', () => {
                " . trim($this->codegen->unindent(implode("\n", $start), 4 * 4)) . "
            });
        ");
        foreach ($this->tasks as $name => $js) {
            $this->append("
                gulp.task('$name', () => {
                    " . trim($this->codegen->unindent($js, 4 * 5)) . "
                });
            ");
        }
        $file = $this->codegen->getFile('gulpfile.js');
        $this->codegen->writeFile($file, implode("\n\n", $this->js) . "\n");
    }

    protected function append(string $js, int $indentAmount = 0): self {
        $this->js[] = $this->codegen->unindent($js, $indentAmount);
        return $this;
    }

    public function addTask($name, $code) {
        $this->tasks[$name] = $code;
        return $this;
    }
}
