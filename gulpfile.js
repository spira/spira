var gulp = require('gulp'),
    watch = require('gulp-watch'),
    notify = require('gulp-notify'),
    del = require('del'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    ngAnnotate = require('gulp-ng-annotate'),
    less = require('gulp-less'),
    sourcemaps = require('gulp-sourcemaps')
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
        get images(){
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
        .pipe(notify("scripts task complete."))
    ;
});

gulp.task('styles', ['clean'], function(){
    return gulp.src(paths.src.styles)
        .pipe(watch(paths.src.styles))
        .pipe(sourcemaps.init())
        .pipe(less())
        .pipe(sourcemaps.write('./maps'))
        .pipe(gulp.dest(paths.dest.css))
        .pipe(notify("styles task complete."))
    ;
});

gulp.task('images', ['clean'], function(){
    return gulp.src(paths.src.images)
        .pipe(gulp.dest(paths.dest.base));
});


// Rerun the task when a file changes
gulp.task('watch', function() {
    gulp.watch(paths.src.scripts, ['scripts'], function(event){
        console.log('File ' + event.path + ' was ' + event.type + ', running tasks...');

    });
    gulp.watch(paths.src.images, ['images']);
});

// The default task (called when you run `gulp` from cli)
gulp.task('default', ['watch', 'scripts', 'images']);