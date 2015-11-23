console.log("Loading core plugins...");
console.time("Core plugins loaded");

var gulpCore = require('gulp'),
    gulpLoadPlugins = require('gulp-load-plugins'),
    plugins = gulpLoadPlugins({
        pattern: [
            'gulp-*',
            'gulp.*',
            'karma-*',
            'del',
            'globby',
            'lodash',
            'main-bower-files',
            'minimatch',
            'run-sequence',
            'json5',
            'merge2'
        ],
        rename: {
            'gulp-angular-templatecache': 'templateCache'
        }
    }),
    gulp = plugins.help(gulpCore),
    _ = require('lodash'),
    browserSync = require('browser-sync').create(),
    path = require('path'),
    bowerJson = require('./app/bower.json'),
    packageJson = require('./package.json')
;

console.timeEnd("Core plugins loaded");

console.log('browserSync', _.functions(browserSync));


var paths = {
    src: {
        tsd: 'app/typings/**/*.d.ts',
        base: 'app/src',
        get scripts(){
            return [
                //@todo relax this to app/bower_components/**/*.d.ts and negate the typings files or even better allow resolution of duplicate typings files
                'app/bower_components/**/dist/*.d.ts', //only read in the .d.ts files from bower distribution
                'app/typings/**/*.d.ts', //get the local typings files
                this.base + '/**/*.ts', this.base + '/**/*.js', //match all javascript and typescript files
                '!'+this.base + '/**/*.spec.js', '!'+this.base + '/**/*.spec.ts' //ignore all spec files
            ]
        },
        get templates(){
            return this.base + '/**/*.tpl.html'
        },
        get styles(){
            return this.base + '/**/*.less'
        },
        get materialThemeFiles(){
            return 'app/bower_components/angular-material/modules/js/**/*-theme.css'
        },
        get assets(){
            return this.base + '/assets/images/**/*'
        },
        get tests(){
            return [
                //@todo relax this to app/bower_components/**/*.d.ts and negate the typings files or even better allow resolution of duplicate typings files
                'app/bower_components/**/dist/*.d.ts', //only read in the .d.ts files from bower distribution
                'app/typings/**/*.d.ts', //get the local typings files
                this.base + '/**/*.d.ts', //get the source definitions
                paths.dest.base + '/**/*.d.ts', //get the built definintions
                this.base + '/**/*.spec.ts' //get all test specifications
            ]
        }
    },
    dest: {
        base: 'app/build',
        get scripts(){
            return this.base+ '/js'
        },
        get tests(){
            return this.base+ '/tests'
        },
        get appStyles(){
            return this.base + '/css/**/*.css'
        },
        get css(){
            return this.base + '/css'
        },
        get vendor(){
            return this.base + '/vendor'
        },
        get assets(){
            return this.base + '/assets'
        },
        get coverage(){
            return 'reports/**/lcov.info'
        }
    }
};

gulp.task('bower:install', 'installs bower dependencies', [],  function() {

    return plugins.bower({ cwd: './app', cmd: 'install'}, ['--allow-root']);

});

gulp.task('clean', 'deletes all build files', [], function(cb) {
    plugins.del([paths.dest.base], cb);
});

gulp.task('scripts:app', 'processes javascript & typescript files', [], function () {

    var tsFilter = plugins.filter('**/*.ts');
    var jsFilter = plugins.filter('**/*.js');
    var tsdFilter = plugins.filter('**/*.d.ts');

    var tsResult = gulp.src(paths.src.scripts)
        //remove the typings references from tsd files @todo remove when tsd recursive resolution is complete https://github.com/DefinitelyTyped/tsd/issues/150
        .pipe(tsdFilter)
        .pipe(plugins.replace('/// <reference path="../typings/tsd.d.ts" />', ''))
        .pipe(tsdFilter.restore())

        .pipe(plugins.sourcemaps.init())
        .pipe(jsFilter)
        .pipe(plugins.ngAnnotate())
        .pipe(jsFilter.restore())

        .pipe(tsFilter)
        .pipe(plugins.typescript({
            target: "ES5",
            noExternalResolve: true,
            typescript: require('typescript'),
            declarationFiles: true,
            sortOutput: true,
            experimentalDecorators: true
        }, undefined, plugins.typescript.reporter.longReporter()));

    return plugins.merge2([
        tsResult.dts
            //.pipe(plugins.replace('<reference path="typings', '<reference path="../typings'))
            .pipe(plugins.concat('declarations.d.ts'))
            .pipe(gulp.dest(paths.dest.scripts)),

        tsResult.js
            .pipe(tsFilter.restore())
            .pipe(plugins.sort())
            .pipe(plugins.concat(packageJson.name + '.js'))
            .pipe(plugins.sourcemaps.write('./', {includeContent: false, sourceRoot: __dirname+'/app/src/'}))
            .pipe(gulp.dest(paths.dest.scripts))
    ]);
});

gulp.task('scripts:test', 'processes javascript & typescript tests', [], function () {

    var tsdFilter = plugins.filter('**/*.d.ts');

    var tsResult = gulp.src(paths.src.tests)
        //remove the typings references from tsd files @todo remove when tsd recursive resolution is complete https://github.com/DefinitelyTyped/tsd/issues/150
        .pipe(tsdFilter)
        .pipe(plugins.replace('/// <reference path="../typings/tsd.d.ts" />', ''))
        .pipe(tsdFilter.restore())

        .pipe(plugins.sourcemaps.init())

        .pipe(plugins.typescript({
            target: "ES5",
            noExternalResolve: true,
            typescript: require('typescript'),
            declarationFiles: false,
            experimentalDecorators: true
        }, undefined, plugins.typescript.reporter.longReporter()))
    ;

    return tsResult.js
        .pipe(plugins.sort())
        .pipe(plugins.concat(packageJson.name + '.spec.js'))
        .pipe(plugins.sourcemaps.write('./', {includeContent: false, sourceRoot: __dirname+'/app/src/'}))
        .pipe(gulp.dest(paths.dest.tests))
    ;
});

gulp.task('templates-watch', 'watches template files for changes [not working]', ['templates'], browserSync.reload);
gulp.task('templates', 'builds template files', [], function(){
    return gulp.src(paths.src.templates)
        .pipe(plugins.templateCache({
            root: "templates/",
            standalone: true
        }))
        .pipe(plugins.concat('templates.js'))
        .pipe(gulp.dest(paths.dest.scripts))
    ;
});

gulp.task('styles', 'compiles stylesheets', [], function(){
    return gulp.src(paths.src.styles)
        //.pipe(watch(paths.src.styles))
        .pipe(plugins.sourcemaps.init())
        .pipe(plugins.less())
        //.pipe(concatCss('app.css'))
        .pipe(plugins.sourcemaps.write('./maps'))
        .pipe(gulp.dest(paths.dest.css))
        //.on('end', function(){
        //    browserSync.reload();
        //})
    ;
});

gulp.task('theme', 'compiles angular material theme', [], function(){

    //var hueRegex = new RegExp('(\'|")?{{\\s*(' + colorType + ')-(color|contrast)-?(\\d\\.?\\d*)?\\s*}}(\"|\')?','g');
    var simpleVariableRegex = /'?"?\{\{\s*([a-zA-Z]+)-(A?\d+|hue\-[0-3]|shadow)-?(\d\.?\d*)?(contrast)?\s*\}\}'?"?/g;
    var variableRegex = /'?"?\{\{\s*([a-zA-Z0-9\.-]+)*\s*\}\}'?"?/g;

    var variablesFound = [];

    return gulp.src(paths.src.materialThemeFiles)
        .pipe(plugins.concat('admin-material-theme.less'))
        .pipe(plugins.replace('.md-THEME_NAME-theme', ''))
        .pipe(plugins.replace(variableRegex, function(match){

            var variable = '@' + match.replace(/[ {}']/g, '');
            var replacement = variable;

            if ((variable.match(/-/g) || []).length > 1){
                var alpha = variable.substr(_.lastIndexOf(variable, "-")+1);
                variable = variable.substr(0, _.lastIndexOf(variable, "-"));
                replacement = 'fade('+ variable + ',' + alpha + ')';
            }

            if (variablesFound.indexOf(variable) < 0){

                variablesFound.push(variable);
                console.log(variable);
            }

            return replacement;
        }))
        .pipe(plugins.insert.prepend('#admin-theme {\n'))
        .pipe(plugins.insert.append('}\n'))
        .pipe(plugins.insert.prepend('@import (reference) "admin-variables.less";\n'))
        .pipe(gulp.dest(paths.src.base + '/styles'))
    ;

});


gulp.task('assets', 'copies asset files', [], function(){
    return gulp.src(paths.src.assets)
        .pipe(gulp.dest(paths.dest.assets));
});

gulp.task('bower:build', 'compiles frontend vendor files', [], function(cb) {

    var files = plugins.mainBowerFiles({
            includeDev: true,
            paths: {
                bowerDirectory: 'app/bower_components',
                bowerJson: 'app/bower.json'
            }
        }),
        jsFilter = plugins.filter('**/*.js'),
        cssFilter = plugins.filter(['**/*.css', '**/*.css.map']),
        everythingElseFilter = plugins.filter(['**/*', '!**/*.css', '!**/*.js', '!**/*.map', '!**/*.less']),
        onError = function(cb){
            console.error(cb);
        };


    if (!files.length) {
        return cb();
    }

    gulp.src(files, {base: 'app/bower_components'})
        //javascript
        .pipe(jsFilter)
        .on('error', onError)
        .pipe(plugins.sourcemaps.init())
        .pipe(plugins.concat(packageJson.name + '.vendor.js'))
        .pipe(plugins.sourcemaps.write('./', {includeContent: false, sourceRoot: __dirname+'/app/bower_components/'}))
        .pipe(gulp.dest(paths.dest.vendor+'/js'))
        .pipe(jsFilter.restore())
        //css
        .pipe(cssFilter)

        .pipe(plugins.replace('../fonts/fontawesome', '/vendor/assets/font-awesome/fonts/fontawesome'))
        .pipe(plugins.replace('font/fontello', '/vendor/assets/pen/src/font/fontello'))
        .on('error', onError)
        .pipe(gulp.dest(paths.dest.vendor+'/css'))
        .pipe(cssFilter.restore())

        //else
        .pipe(everythingElseFilter)
        .pipe(gulp.dest(paths.dest.vendor+'/assets'))
        .on('end', cb);

});

var getIndexFiles = function(conf){

    conf = conf || {};

    var config = _.defaults(conf, {
        devDeps: false //developer dependencies
    });

    var vendorFiles = plugins.mainBowerFiles({
        includeDev: config.devDeps,
        paths: {
            bowerDirectory: 'app/bower_components',
            bowerJson: 'app/bower.json'
        }
    });

    vendorFiles = vendorFiles.map(function(path){
        return path.replace(/\\/g, "\/").replace(/^.+bower_components\//i, '');
    });

    var files = {
        scripts: {
            app: plugins.globby.sync(paths.dest.scripts+'/**/*.js').map(function(path){
                return path.replace('app/build/', '');
            }),
            vendor: plugins.globby.sync(paths.dest.vendor+'/**/*.js').map(function(path){
                return path.replace('app/build/', '');
            })
        },
        styles: {
            app: plugins.globby.sync(paths.dest.appStyles).map(function(path){
                return path.replace('app/build/', '');
            }),
            vendor: vendorFiles.filter(plugins.minimatch.filter("*.css", {matchBase: true})).map(function(path){
                return 'vendor/css/'+path;
            })
        }
    };

    return files;

};

gulp.task('index', 'processes index.html file', [], function(){

    var files = getIndexFiles();


    return gulp.src(paths.src.base+'/index.html')
        .pipe(plugins.template(files))
        .pipe(gulp.dest(paths.dest.base))
    ;

});

// The default task (called when you run `gulp` from cli)
gulp.task('default', 'default task', ['build']);

gulp.task('build', 'runs build sequence for frontend', function (cb){
    plugins.runSequence('clean',
        //'bower:install',
        ['scripts:app', 'templates', 'styles', 'assets', 'bower:build'],
        'index',
        cb);
});

gulp.task('watch', 'starts up browsersync server and runs task watchers', [], function() {

    /**
     * @todo resolve why this file watcher is required for browsersync to function
     * It should be possible to just add a watch task that fires reload when task runs
     * See `templates-watch` method above which does not work
     */

    browserSync.watch(paths.dest.base+'/**', function (event, file) {
        if (event === "change") {
            browserSync.reload();
        }
    });

    browserSync.init({
        proxy: "http://local.app.spira.io"
    });

    gulp.watch(paths.src.templates, ['templates']);
    gulp.watch(paths.src.scripts, ['scripts:app']);
    gulp.watch(paths.src.styles, ['styles']);
    gulp.watch(paths.src.assets, ['assets']);
    gulp.watch(paths.src.base+'/index.html', ['index']);

});


gulp.task('test:app',  'unit test & report frontend coverage', [], function(cb){
    plugins.runSequence('build', 'scripts:test', 'test:karma', cb);
});

gulp.task('test:karma',  'unit test the frontend', [], function(done){

    var files = getIndexFiles({
        devDeps: true
    });

    var testFiles = files.scripts.vendor
        .concat(files.scripts.app)
        .map(function(path){
            return 'app/build/'+path;
        })
        .concat(plugins.globby.sync(paths.dest.tests+'/**/*.js'))
    ;

    var karmaConfig = {
        configFile: __dirname + '/karma.conf.js',
        singleRun: true,
        files: testFiles
    };


    var KarmaServer = require('karma').Server;

    new KarmaServer(karmaConfig, done).start();
});

gulp.task('test:api', 'unit tests the api', [], function(){

    return gulp.src('api/phpunit.xml')
        .pipe(plugins.phpunit('./api/vendor/bin/phpunit', {
            notify: true,
            coverageClover: './reports/coverage/api/clover.xml'
        }))
        .on('error', function(err){
            plugins.notify.onError(testNotification('fail', 'phpunit'));
            throw err;
        })
        .pipe(plugins.notify(testNotification('pass', 'phpunit')))
    ;

});

gulp.task('test:forum', 'tests the forum', [], function(){

    return gulp.src('forum/phpunit.xml')
        .pipe(plugins.phpunit('./forum/vendor/bin/phpunit', {
            notify: true
        }))
        .on('error', function(err){
            plugins.notify.onError(testNotification('fail', 'phpunit'));
            throw err;
        })
        .pipe(plugins.notify(testNotification('pass', 'phpunit')))
    ;

});

gulp.task('test', 'executes all unit and integration tests', ['test:app', 'test:api', 'test:forum']);

gulp.task('coveralls', 'generates code coverage for the frontend', [], function(){
    gulp.src(paths.dest.coverage)
        .pipe(plugins.coveralls());
});


function testNotification(status, pluginName, override) {
    var options = {
        title:   ( status == 'pass' ) ? 'Tests Passed' : 'Tests Failed',
        message: ( status == 'pass' ) ? '\n\nAll tests have passed!\n\n' : '\n\nOne or more tests failed...\n\n',
        icon:    __dirname + '/node_modules/gulp-' + pluginName +'/assets/test-' + status + '.png'
    };
    options = _.merge(options, override);
    return options;
}
