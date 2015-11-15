var gulp = require('gulp');
var concat = require('gulp-concat');
var flatten = require('gulp-flatten');
var rename = require('gulp-rename');
var sass = require('gulp-sass');
var zip = require('gulp-zip');
var autoprefixer = require('gulp-autoprefixer');

gulp.task('default', ['lib-styles', 'scripts'], function () {
});

gulp.task('site-styles', function () {
    return gulp.src('private/styles/application.scss')
        .pipe(sass({
            outputStyle: 'compressed',
        }).on('error', sass.logError))
        .pipe(rename('application.css'))
        .pipe(gulp.dest('temp'));
});

gulp.task('lib-styles', ['site-styles'], function () {
    return gulp.src([
            'bower_components/bootstrap/dist/css/bootstrap.min.css',
            'temp/application.css',
        ])
        .pipe(autoprefixer({
            browsers: ['last 2 versions'],
            cascade: false,
        }))
        .pipe(concat('application.css'))
        .pipe(gulp.dest('public/styles'));
});

gulp.task('scripts', function () {
    return gulp.src([
            'bower_components/jquery/dist/jquery.min.js',
            'bower_components/bootstrap/dist/js/bootstrap.min.js',
            'private/scripts/application.js',
        ])
        .pipe(concat('application.js'))
        .pipe(gulp.dest('public/scripts'));
});

gulp.task('watch', ['default'], function () {
    return gulp.watch('private/**/*.*', ['default']);
});