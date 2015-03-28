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
    browserSync = require('browser-sync')
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

gulp.task('scripts', ['clean'], function () {
    return gulp.src(paths.src.scripts)
        .pipe(watch(paths.src.scripts))

        .pipe(ngAnnotate())
        .pipe(gulp.dest(paths.dest.base))
    ;
});

gulp.task('styles', ['clean'], function(){
    return gulp.src(paths.src.styles)
        .pipe(watch(paths.src.styles))
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


gulp.task('assets', ['clean'], function(){
    return gulp.src(paths.src.assets)
        .pipe(gulp.dest(paths.dest.assets));
});

gulp.task('index', ['clean'], function(){
    return gulp.src(paths.src.base+'/index.html')
        .pipe(gulp.dest(paths.dest.base));
});

gulp.task('bower', ['clean'], function(cb) {

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

    gulp.src(files)
        //javascript
        .pipe(jsFilter)
        .pipe(sourcemaps.init())
        .pipe(concat('vendor.js'))
        .pipe(sourcemaps.write('./maps'))
        .on('error', onError)
        .pipe(gulp.dest(paths.dest.vendor+'/js'))
        .pipe(jsFilter.restore())
        //css
        .pipe(cssFilter)
        .pipe(sourcemaps.init())
        .pipe(concat('vendor.css'))
        .pipe(sourcemaps.write('./maps'))
        .on('error', onError)
        .pipe(gulp.dest(paths.dest.vendor+'/css'))
        .pipe(cssFilter.restore())

        //else
        .pipe(everythingElseFilter)
        .pipe(gulp.dest(paths.dest.vendor+'/assets'))
        .on('end', cb);


});

// The default task (called when you run `gulp` from cli)
gulp.task('default', ['scripts', 'styles', 'assets', 'bower', 'index']);

gulp.task('browser-sync', function() {
    browserSync({
        proxy: "local.larvae.com"
    });
});