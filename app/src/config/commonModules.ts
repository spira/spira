namespace config.commonModules {

    export const namespace = 'config.commonModules';

    angular.module(namespace, [
        'common.providers.stateHelperServiceProvider',
        'common.services',
        'common.directives',
        'common.filters',
    ]);

}
