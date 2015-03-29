module.exports = function(config) {
    config.set({

        basePath: './',

        frameworks: ['jasmine'],
        plugins: ['karma-jasmine', 'karma-phantomjs-launcher'],
        preprocessors: {},

        reporters: 'dots',

        port: 9018,
        runnerPort: 9100,
        urlRoot: '/',

        autoWatch: false,
        browsers: [
            'PhantomJS'
        ],

        logLevel: config.LOG_INFO
    });
};