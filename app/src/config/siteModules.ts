module config.siteModules {

    export const namespace = 'config.siteModules';

    angular.module('siteModules', [
        // Top level site modules
        'app.guest',
    ]);

}