///<reference path="../../typings/tsd.d.ts" />

module config.stateManager {

    export const namespace = 'config.stateManager';

    class StateManagerConfig {

        static $inject = ['$stateProvider', '$locationProvider', '$urlRouterProvider', '$compileProvider', 'stateHelperServiceProvider'];
        constructor(private $stateProvider, private $locationProvider, private $urlRouterProvider, private $compileProvider, private stateHelperServiceProvider){

            StateManagerConfig.configureRouter($locationProvider, $urlRouterProvider);
            $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|chrome-extension):/);

            StateManagerConfig.registerStates($stateProvider, stateHelperServiceProvider);
        }

        private static registerStates($stateProvider, stateHelperServiceProvider) {

            //add base state
            $stateProvider
                .state('app', {
                    abstract: true
                })
            ;

            // Loop through each sub-module state and register them
            angular.forEach(stateHelperServiceProvider.getStates(), (state:global.IStateDefinition) => {
                $stateProvider.state(state.name, state.options);
            });
        }

        private static configureRouter($locationProvider, $urlRouterProvider) {
            $locationProvider.html5Mode(true);

            $urlRouterProvider.otherwise(function ($injector, $location) {
                var $state = $injector.get('$state');

                $state.go(app.guest.error.namespace, {
                    title: "Page not found",
                    message: 'Could not find a state associated with url "' + $location.$$url + '"',
                    url: $location.$$absUrl
                });
            });
        }

    }

    angular.module(namespace, [
        'config.siteModules' //include the site modules after stateManager has been configured so all states can be loaded
    ])
    .config(StateManagerConfig);

}