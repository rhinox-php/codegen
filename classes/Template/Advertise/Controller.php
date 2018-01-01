<?php
namespace Rhino\Codegen\Template\Advertise;

class Controller extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        $this->renderTemplate('advertise/controller', 'src/classes/Controller/HomeController.php', [
        ]);
        $this->renderTemplate('advertise/views/home.twig', 'src/views/home.twig', [
        ]);
        $this->renderTemplate('advertise/views/layout.twig', 'src/views/layout.twig', [
        ]);
        $this->copy('advertise/images/*', 'public/images');

        $this->codegen->gulp->addTask('advertise-css', "
            const files = [
                'src/assets/scss/advertise.scss',
            ];
            return gulp.src(files)
                .pipe(expectFile(files))
                .pipe(sass())
                .pipe(gulp.dest('public/assets/build/'));
        ");
        $this->copy('advertise/assets/*.scss', 'src/assets/scss');
    }

    public function css(...$args) {
        $libraries = [
            'bootstrap' => 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css',
        ];
        foreach ($args as $name) {
            echo '<link rel="stylesheet" href="' . $libraries[$name] . '" />' . PHP_EOL;
        }
    }

    public function js(...$args) {
        $libraries = [
            'jquery' => 'https://code.jquery.com/jquery-2.2.4.min.js',
            'bootstrap' => 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js',
        ];
        foreach ($args as $name) {
            echo '<script src="' . $libraries[$name] . '"></script>' . PHP_EOL;
        }
    }

    public function iterateRoutes()
    {
        yield ['get', '/', $this->getNamespace('controller-implemented') . '\\HomeController', 'home'];
    }
}
