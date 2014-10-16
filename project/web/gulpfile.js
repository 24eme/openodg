var gulp = require('gulp');
var path = require('path');

// Plugins Gulp
var plugins =
{
	// Compilateur LESS
	less: require('gulp-less'),

	// Minificateur CSS
	minifyCSS: require('gulp-minify-css'),

	// Prefixeur CSS
	// Maintenir à jour ce plugin ainsi que le package "caniuse-db"
	prefix: require('gulp-autoprefixer'),
	
	// Live reload
	livereload: require('gulp-livereload'),

	// Exclusion de fichiers / dossiers
	ignore: require('gulp-ignore'),

	// Empèche la fin du "watch" si erreur de compilation LESS
	plumber: require('gulp-plumber'),

	// Messages de notification
	// Linux: Installer Notify-send
	// Windows: Installer Growl
	notify: require("gulp-notify")
}; 

// Répertoires
var rep =
{
	img: 		'images/',
	css: 		'css/',
	js: 		'js/',
	less:       'less/'
};


// Fichiers
var fichiers =
{
	img: 			rep.img + 	'**/*',
	css: 			rep.css + 	'**/*.css',
	cssCompile:  	rep.css + 	'compile.css',
	js: 			rep.js +  	'**/*.js',
	less:           rep.less +  '*.less',
	lessCompile:  	rep.less + 	'compile.less'
};

/**
 * Tâche : default
 *******************************************/
gulp.task('default', ['watch'], function()
{
});


/**
 * Tâche : watch
 * 		- Lancement du serveur LiveReload
 *		- Compilation des Fichiers Less
 *		- LiveReload sur JS / CSS
 *******************************************/
gulp.task('watch', function()
{
	// Lancement du serveur LiveReload
	plugins.livereload.listen();
	var serveur = plugins.livereload();

	// Compilation LESS
	gulp.watch(fichiers.less, ['less']);

	// LiveReload sur les CSS, JS et images
	gulp.watch([fichiers.js, fichiers.css, fichiers.img]).on('change', function(e)
	{
		serveur.changed(e.path);
	});
});

/**
 * Tâche : less
 * Fonctions :
 * 		- Compilation LESS
 * 		- Préfixation des propriétés CSS3
 * 		- Compression CSS résultante
 *******************************************/
gulp.task('less', function()
{
	return gulp
		.src(fichiers.lessCompile)
		.pipe
		(
			plugins.plumber(
			{
				errorHandler: plugins.notify.onError("Error: <%= error.message %>")
			})
		)
		.pipe(plugins.less({ paths: [ path.join(__dirname, 'less', 'includes') ] }))
		.pipe(plugins.prefix())
		.pipe(plugins.minifyCSS())
		.pipe(gulp.dest(rep.css));
});

/**
 * Tâche : minCSS
 * Fonctions :
 * 		- Compression de toutes les CSS
 *******************************************/
gulp.task('minCSS', function()
{
	return gulp
		.src(fichiers.css)
		.pipe(plugins.minifyCSS())
		.pipe(gulp.dest(rep.css));
});

