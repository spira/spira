var gulp = require('gulp'),
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
    minimatch = require('minimatch')
;

var paths = {
    src: {
        base: 'app/src',
        get scripts(){
            return this.base + '/**/*.js'
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
        get appScripts(){
            return this.base + '/app/**/*.js'
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
        }
    }
};

gulp.task('clean', function(cb) {
    del([paths.dest.base], cb);
});

gulp.task('scripts', [], function () {
    return gulp.src(paths.src.scripts)
        //.pipe(watch(paths.src.scripts))
        .pipe(ngAnnotate())
        .pipe(gulp.dest(paths.dest.base))
    ;
});

gulp.task('styles', [], function(){
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


gulp.task('assets', [], function(){
    return gulp.src(paths.src.assets)
        .pipe(gulp.dest(paths.dest.assets));
});

gulp.task('bower', [], function(cb) {

    var files = mainBowerFiles({
            paths: {
                bowerDirectory: 'app/bower_components',
                bowerJson: 'app/bower.json'
            }
        }),
        jsFilter = filter('**/*.js'),
        cssFilter = filter('**/*.css'),
        everythingElseFilter = filter([ '**/*.!{js,css}' ]),
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
        .on('error', onError)
        .pipe(gulp.dest(paths.dest.vendor+'/css'))
        .pipe(cssFilter.restore())

        //else
        .pipe(everythingElseFilter)
        .pipe(gulp.dest(paths.dest.vendor+'/assets'))
        .on('end', cb);


});

gulp.task('index', function(){

    var vendorFiles = mainBowerFiles({
        paths: {
            bowerDirectory: 'app/bower_components',
            bowerJson: 'app/bower.json'
        }
    });

    vendorFiles = vendorFiles.map(function(path){
       return path.replace(__dirname+'/app/bower_components/', '');
    });

    var files = {
        scripts: {
            app: globby.sync(paths.dest.appScripts).map(function(path){
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

    console.log('paths.dest.appScripts', paths.dest.appScripts);

    console.log('files', files);

    return gulp.src(paths.src.base+'/index.html')
        .pipe(template(files))
        .pipe(gulp.dest(paths.dest.base))
    ;

});

// The default task (called when you run `gulp` from cli)
gulp.task('default', ['scripts', 'styles', 'assets', 'bower', 'index']);

gulp.task('build', function (cb){
    runSequence('clean',
        ['scripts', 'styles', 'assets', 'bower', 'index'],
        'index',
        cb);
});

gulp.task('browser-sync', function() {
    browserSync({
        proxy: "local.larvae.com"
    });
});