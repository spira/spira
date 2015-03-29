module.exports = function(config) {
    config.set({

        basePath: './',

        frameworks: ['jasmine'],
        plugins: ['karma-jasmine', 'karma-phantomjs-launcher', 'karma-coverage'],

        preprocessors: {
            'app/build/js/**/*.js': ['coverage']
        },

        reporters: ['progress', 'coverage'],

        port: 9018,
        runnerPort: 9100,
        urlRoot: '/',

        autoWatch: false,
        browsers: [
            'PhantomJS'
        ],

        logLevel: config.LOG_INFO,

        coverageReporter: {
            // specify a common output directory
            dir: 'app/build/reports/coverage',
            reporters: [
                // reporters not supporting the `file` property
                {type: 'html', subdir: 'report-html'},
                {type: 'lcov', subdir: 'report-lcov'}
            ]
        }
    });
};