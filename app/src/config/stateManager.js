angular.module('stateManager', [
    'stateHelperServiceProvider',
    'siteModules'
])
    .config(function($stateProvider, $locationProvider, $urlRouterProvider, $compileProvider, stateHelperServiceProvider) {
        $locationProvider.html5Mode(true);

        $urlRouterProvider.otherwise(function ($injector, $location) {
            var $state = $injector.get('$state');

            $state.go('app.public.error', {
                title: "Page not found",
                message: 'Could not find a state associated with url "'+$location.$$url+'"',
                url: $location.$$absUrl
            });
        });

        $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|chrome-extension):/);

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
        angular.forEach(stateHelperServiceProvider.getStates(), function(state) {
            $stateProvider.state(state.name, state.options);
        });

    })

    .run(function($rootScope){

    })

    .controller('stateManager.navigation.controller', function($scope, stateHelperService, $window) {

        var childStates = stateHelperService.getChildStates('app.public');

        //using the state.data.sortAfter key build a topology and sort it
        var sortMap = _.reduce(childStates, function(t, state){
            t.add(state.name, _.get(state, 'data.sortAfter', []));
            return t;
        }, new Toposort()).sort();

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

    })
;