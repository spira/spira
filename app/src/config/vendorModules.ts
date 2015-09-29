namespace config.vendorModules {

    export const namespace = 'config.vendorModules';

    class CloudinaryConfig {

        constructor() {

            //for some dumb reason cloudinary angular is dependent on jquery. Hopefully they support native angular at some point
            (<any>$).cloudinary.config().cloud_name = 'spira';
        }
    }

    class AuthConfig {

        static $inject = ['ngJwtAuthServiceProvider'];

        constructor(private ngJwtAuthServiceProvider:NgJwtAuth.NgJwtAuthServiceProvider) {

            let config:NgJwtAuth.INgJwtAuthServiceConfig = {
                refreshBeforeSeconds: 60 * 10, //10 mins
                checkExpiryEverySeconds: 60, //1 min
                storageKeyName: 'jwtAuthToken',
                apiEndpoints: {
                    base: '/api/auth/jwt',
                    login: '/login',
                    tokenExchange: '/token',
                    refresh: '/refresh',
                },
                cookie: {
                    enabled: true,
                    name: 'jwtAuthToken',
                    topLevelDomain: true,
                }
            };

            ngJwtAuthServiceProvider.configure(config);

        }

    }

    angular.module(namespace, [
        'ngMessages', //nice validation messages
        'ngMaterial', //angular material
        'ui.router', // Handles state changes and routing of the site
        'ui.route', // Module to check for active urls, nothing to do with ui.router
        'ui.keypress', // Module to check for active urls, nothing to do with ui.router
        'ui.inflector', //Module to Humanise strings (camelCased or pipe-case etc)
        'ui.validate', //Module to add custom validation to inputs
        'ngAnimate', //angular animate
        'ngSanitize', //angular sanitise
        'hljs', //syntax highlighted code blocks - https://github.com/pc035860/angular-highlightjs
        'ngHttpProgress', //http request progress meter - https://github.com/spira/angular-http-progress
        'ngRestAdapter', // api helper methods - https://github.com/spira/angular-rest-adapter
        'ngJwtAuth', // json web token authentication - https://github.com/spira/angular-jwt-auth
        'infinite-scroll', //infinite scrolling - https://github.com/sroze/ngInfiniteScroll
        'ui.validate', // Field validator - https://github.com/angular-ui/ui-validate
        'ngFileUpload', // File uploader - https://github.com/danialfarid/ng-file-upload
        'cloudinary', //directives for displaying cloudinary images (official) - https://github.com/cloudinary/cloudinary_angular
        'hc.marked', //markdown parser - https://github.com/Hypercubed/angular-marked
        'angular-carousel', //content carousel - https://github.com/revolunet/angular-carousel
    ])
    .config(AuthConfig)
    .config(CloudinaryConfig)

}
