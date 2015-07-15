module config.commonModules {

    export const namespace = 'config.commonModules';

    angular.module(namespace, [
        //service providers
        'common.providers.stateHelperServiceProvider',

        //services
        'common.services.userService',

        //directives
    ]);

}
