angular.module('vendorModules', [
    'ngMessages', //nice validation messages
    'ui.bootstrap', //angular implementations of twitter bootstrap elements
    'ui.router', // Handles state changes and routing of the site
    'ui.route', // Module to check for active urls, nothing to do with ui.router
    'ui.keypress', // Module to check for active urls, nothing to do with ui.router
    'ui.inflector', //Module to Humanise strings (camelCased or pipe-case etc)
    'ui.bootstrap.showErrors', //handle the has-error etc classes correctly
    'ui.validate', //Module to add custom validation to inputs
    'ngAnimate', //angular animate
    'ngSanitize' //angular sanitise
]);