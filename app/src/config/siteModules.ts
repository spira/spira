module config.siteModules {

    export const namespace = 'config.siteModules';

    angular.module(namespace, [
        // Top level site modules
        'app.guest',
        'app.user',
    ]);

}