var _ = require('lodash'),
    gulpCore = require('gulp'),
    gulp = require('gulp-help')(gulpCore),
    bower = require('gulp-bower'),
    watch = require('gulp-watch'),
    notify = require('gulp-notify'),
    del = require('del'),
    concat = require('gulp-concat'),
    concatCss = require('gulp-concat-css'),
    uglify = require('gulp-uglify'),
    ngAnnotate = require('gulp-ng-annotate'),
    less = require('gulp-less'),
    sourcemaps = require('gulp-sourcemaps'),
    mainBowerFiles = require('main-bower-files'),
    filter = require('gulp-filter'),
    browserSync = require('browser-sync'),
    template = require('gulp-template'),
    globby = require('globby'),
    runSequence = require('run-sequence'),
    minimatch = require('minimatch'),
    templateCache = require('gulp-angular-templatecache'),
    karma = require('gulp-karma'),
    addSrc = require('gulp-add-src'),
    coveralls = require('gulp-coveralls'),
    phpunit = require('gulp-phpunit'),
    newman = require('newman'),
    fs = require('fs'),
    JSON5 = require('json5'),
    replace = require('gulp-replace')
;

var paths = {
    src: {
        base: 'app/src',
        get scripts(){
            return [this.base + '/**/*.js', '!'+this.base + '/**/*.spec.js']
        },
        get templates(){
            return this.base + '/**/*.tpl.html'
        },
        get styles(){
            return this.base + '/styles/**/*.less'
        },
        get assets(){
            return this.base + '/assets/images/**/*'
        },
        get tests(){
            return this.base + '/**/*.spec.js'
        }
    },
    dest: {
        base: 'app/build',
        get scripts(){
            return this.base+ '/js'
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

gulp.task('bower:install', 'installs bower dependencies', function() {

    return bower({ cwd: './app', cmd: 'install'}, ['--allow-root']);

});

gulp.task('clean', 'deletes all build files', function(cb) {
    del([paths.dest.base], cb);
});

gulp.task('scripts', 'processes javascript files', [], function () {
    return gulp.src(paths.src.scripts)
        //.pipe(watch(paths.src.scripts))
        .pipe(ngAnnotate())
        .pipe(gulp.dest(paths.dest.scripts))
    ;
});

gulp.task('templates', 'builds template files', [], function(){
    return gulp.src(paths.src.templates)
        .pipe(templateCache({
            root: "templates/",
            standalone: true
        }))
        .pipe(concat('templates.js'))
        .pipe(gulp.dest(paths.dest.scripts))
    ;
});

gulp.task('styles', 'compiles stylesheets', [], function(){
    return gulp.src(paths.src.styles)
        //.pipe(watch(paths.src.styles))
        .pipe(sourcemaps.init())
        .pipe(less())
        //.pipe(concatCss('app.css'))
        .pipe(sourcemaps.write('./maps'))
        .pipe(gulp.dest(paths.dest.css))
        //.on('end', function(){
        //    browserSync.reload();
        //})
    ;
});


gulp.task('assets', 'copies asset files', [], function(){
    return gulp.src(paths.src.assets)
        .pipe(gulp.dest(paths.dest.assets));
});

gulp.task('bower:build', 'compiles frontend vendor files', [], function(cb) {

    var files = mainBowerFiles({
            includeDev: true,
            paths: {
                bowerDirectory: 'app/bower_components',
                bowerJson: 'app/bower.json'
            }
        }),
        jsFilter = filter('**/*.js'),
        cssFilter = filter(['**/*.css', '**/*.css.map']),
        everythingElseFilter = filter(['**/*', '!**/*.css', '!**/*.js', '!**/*.map', '!**/*.less']),
        onError = function(cb){
            console.error(cb);
        };


    if (!files.length) {
        return cb();
    }

    gulp.src(files, {base: 'app/bower_components'})
        //javascript
        .pipe(jsFilter)
        //.pipe(sourcemaps.init())
        //.pipe(concat('vendor.js'))
        //.pipe(sourcemaps.write('./maps'))
        .on('error', onError)
        .pipe(gulp.dest(paths.dest.vendor+'/js'))
        .pipe(jsFilter.restore())
        //css
        .pipe(cssFilter)
        //.pipe(sourcemaps.init())
        //.pipe(concat('vendor.css'))
        //.pipe(sourcemaps.write('./maps'))

        .pipe(replace('../fonts/fontawesome', '/vendor/assets/font-awesome/fonts/fontawesome'))
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

    var vendorFiles = mainBowerFiles({
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
            app: globby.sync(paths.dest.scripts+'/**/*.js').map(function(path){
                return path.replace('app/build/', '');
            }),
            vendor: vendorFiles.filter(minimatch.filter("*.js", {matchBase: true})).map(function(path){
                return 'vendor/js/'+path;
            })
        },
        styles: {
            app: globby.sync(paths.dest.appStyles).map(function(path){
                return path.replace('app/build/', '');
            }),
            vendor: vendorFiles.filter(minimatch.filter("*.css", {matchBase: true})).map(function(path){
                return 'vendor/css/'+path;
            })
        }
    };

    return files;

};

gulp.task('index', 'processes index.html file', function(){

    var files = getIndexFiles();


    return gulp.src(paths.src.base+'/index.html')
        .pipe(template(files))
        .pipe(gulp.dest(paths.dest.base))
    ;

});

// The default task (called when you run `gulp` from cli)
gulp.task('default', 'default task', ['build']);

gulp.task('build', 'runs build sequence for frontend', function (cb){
    runSequence('clean',
        'bower:install',
        ['scripts', 'templates', 'styles', 'assets', 'bower:build'],
        'index',
        cb);
});

gulp.task('browser-sync', 'triggers browsersync reload [not yet working]', function() {
    browserSync({
        proxy: "local.app.nglume.io"
    });
});

gulp.task('test:app',  'unit tests the frontend', function(){

    var files = getIndexFiles({
        devDeps: true
    });
    var testFiles = files.scripts.vendor
        .map(function(path){
            return 'app/build/'+path;
        })
        .concat(globby.sync(paths.src.scripts))
        .concat(globby.sync(paths.src.tests))
    ;

    testFiles.push('app/build/js/templates.js');

    return gulp.src(testFiles)
        .pipe(karma({
            configFile: 'karma.conf.js',
            action: 'run'
        }))
        .on('error', function(err) {
            // Make sure failed tests cause gulp to exit non-zero
            throw err;
        });

});

gulp.task('test:api', 'unit tests the api', function(){

    return gulp.src('api/phpunit.xml')
        .pipe(phpunit('./api/vendor/bin/phpunit', {
            notify: true,
            coverageClover: './reports/coverage/api/clover.xml'
        }))
        .on('error', function(err){
            notify.onError(testNotification('fail', 'phpunit'));
            throw err;
        })
        .pipe(notify(testNotification('pass', 'phpunit')))
    ;

});

gulp.task('test:postman', 'integration tests the api', function(callback){ //@todo fix postman not connecting with travis ci, or not returning exitcodes


    var collectionJson = JSON5.parse(fs.readFileSync("./api/postman/nglume.json.postman_collection", 'utf8'));

    var newmanOptions = {
        envJson: JSON5.parse(fs.readFileSync("./api/postman/nglume-travisci.postman_environment", "utf-8")), // environment file (in parsed json format)
        asLibrary: true,
        stopOnError: false
    };


    newman.execute(collectionJson, newmanOptions, function(exitCode){

        if (exitCode !== 0){
            throw new Error("Postman tests failed!");
        }

        callback();
    });

});

gulp.task('test', 'executes all unit and integration tests', ['test:app', 'test:api', 'test:postman']);

gulp.task('coveralls', 'generates code coverage for the frontend', function(){
    gulp.src(paths.dest.coverage)
        .pipe(coveralls());
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
