///<reference path="../../typings/tsd.d.ts" />

module app.stateManager {

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
            // Create state for Default Layout
            $stateProvider
                .state('app.public', {
                    abstract: true,
                    views: {
                        'app@': { // Points to the ui-view in the index.html
                            templateUrl: 'templates/app/_layouts/default.tpl.html'
                        },
                        'navigation@app.public': { // Points to the ui-view="navigation" in default.tpl.html
                            templateUrl: 'templates/app/_partials/navigation.tpl.html',
                            controller: 'stateManager.navigation.controller'
                        }
                    },
                    data: {
                        role: 'public'
                    }
                })
            ;

            // Loop through each sub-module state and register them
            angular.forEach(stateHelperServiceProvider.getStates(), (state:IStateDefinition) => {
                $stateProvider.state(state.name, state.options);
            });
        }

        private static configureRouter($locationProvider, $urlRouterProvider) {
            $locationProvider.html5Mode(true);

            $urlRouterProvider.otherwise(function ($injector, $location) {
                var $state = $injector.get('$state');

                $state.go('app.public.error', {
                    title: "Page not found",
                    message: 'Could not find a state associated with url "' + $location.$$url + '"',
                    url: $location.$$absUrl
                });
            });
        }

    }

    class NavigationController {

        static $inject = ['$scope', 'stateHelperService', '$window'];
        constructor(private $scope, private stateHelperService, private $window) {

            var childStates = stateHelperService.getChildStates('app.public');

            //using the state.data.sortAfter key build a topology and sort it
            var sortMap = _.reduce(childStates, function(t, state:ng.ui.IState){
                t.add(state.name, _.get(state, 'data.sortAfter', []));
                return t;
            }, new $window.Toposort()).sort();

            $scope.navigationStates = _.chain(sortMap)
                .map(function(stateName){
                    return _.find(childStates, {name: stateName}); //find the state by name
                })
                .filter(function(state){
                    return _.get(state, 'data.navigation', false); //only return those that are marked as navigation
                })
                .reverse() //reverse the array
                .value()
            ;

        }

    }

    angular.module('stateManager', [
        'stateHelperServiceProvider',
        'siteModules'
    ])
    .config(StateManagerConfig)
    .controller('stateManager.navigation.controller', NavigationController);

}