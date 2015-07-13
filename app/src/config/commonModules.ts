module config.commonModules {

    export const namespace = 'config.commonModules';

    angular.module('commonModules', [
        //service providers
        'stateHelperServiceProvider',

        //services
        'userService',

        //directives
        'highlightDirective'
    ]);

}
