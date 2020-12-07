var gulp = require('gulp');
var sass = require('gulp-sass');
var concat = require('gulp-concat');
var minifyCSS = require('gulp-csso');
var sourcemaps = require('gulp-sourcemaps');
var minify = require('gulp-minify');

gulp.task('sass', function() {
	return gulp
		.src([
			'node_modules/bootstrap/scss/bootstrap.scss',
			'node_modules/jquery-ui-dist/jquery-ui.min.css',
			'node_modules/tag-it/css/jquery.tagit.css'
		])
		.pipe(sass())
		.pipe(concat('app.min.css'))
		.pipe(minifyCSS())
		.pipe(gulp.dest('resources/dist'));
});

gulp.task('scripts', function() {
	return gulp
		.src([
			'node_modules/jquery/dist/jquery.js',
			'node_modules/popper.js/dist/umd/popper.js',
			'node_modules/bootstrap/dist/js/bootstrap.js',
			'node_modules/jquery-ui-dist/jquery-ui.min.js',
			'resources/js/tag-it.min.js',
			'resources/js/main.js'
		])
		.pipe(sourcemaps.init())
		.pipe(concat('app.js'))
		.pipe(sourcemaps.write())
		.pipe(gulp.dest('resources/dist'));
});

gulp.task('compress', function() {
	return gulp
		.src('resources/dist/app.js')
		.pipe(
			minify({
				ext: {
					src: '-debug.js',
					min: '.min.js'
				}
			})
		)
		.pipe(gulp.dest('resources/dist'));
});

gulp.task('compileAll', gulp.series('sass', 'scripts', 'compress'));

gulp.task('watch', function() {
	return gulp.watch('resources/js/**/*.js', gulp.series('scripts', 'compress'));
});
