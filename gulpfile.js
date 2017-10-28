var
	gulp = require( 'gulp' ),
	browserSync = require( 'browser-sync' ),
	pump = require( 'pump' ),
	$ = require( 'gulp-load-plugins' )( {lazy: true} );

gulp.task( 'styles', function () {
	pump( [
		gulp.src( './badgeup/!(*vendor)/**/*.scss' ),
		$.sass().on( 'error', $.sass.logError ),
		$.autoprefixer( { browsers: ['last 2 version'] } ),
		gulp.dest( './badgeup' )
	] );
} );

gulp.task( 'scripts', function () {
	pump( [
		gulp.src( './badgeup/!(*vendor)/**/!(*.min).js' ),
		$.sourcemaps.init(),
		$.uglify(),
		$.rename( {suffix: '.min'} ),
		$.sourcemaps.write( '.' ),
		gulp.dest( './badgeup' )
	] );
} );

gulp.task( 'watch', function () {
	// Watch .sass files
	gulp.watch( './badgeup/!(*vendor)/**/*.scss', ['styles'] );
	// Watch .js files
	gulp.watch( './badgeup/!(*vendor)/**/!(*.min).js', ['scripts'] );
} );

// build everything
gulp.task( 'build', function () {
	gulp.start(
		'styles',
		'scripts'
	);
} );

// build everything and watch for changes
gulp.task( 'default', function () {
	gulp.start(
		'styles',
		'scripts',
		'watch'
	);
} );
