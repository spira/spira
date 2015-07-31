///<reference path="../../typings/tsd.d.ts" />

module config.stateManager {

    export const namespace = 'config.stateManager';

    class StateManagerConfig {

        static $inject = ['$stateProvider', '$locationProvider', '$urlRouterProvider', '$compileProvider', 'stateHelperServiceProvider'];

        constructor(private $stateProvider, private $locationProvider, private $urlRouterProvider, private $compileProvider, private stateHelperServiceProvider) {

            StateManagerConfig.configureRouter($locationProvider, $urlRouterProvider);
            $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|chrome-extension):/);

            StateManagerConfig.registerStates($stateProvider, stateHelperServiceProvider);
        }

        private static registerStates($stateProvider, stateHelperServiceProvider) {

            //add base state
            $stateProvider
                .state('app', {
                    abstract: true,
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

    class StateManagerInit {

        static $inject = ['$rootScope', 'ngRestAdapter', 'ngJwtAuthService', '$state', '$mdToast'];

        constructor(private $rootScope:ng.IRootScopeService,
                    private ngRestAdapter:NgRestAdapter.NgRestAdapterService,
                    private ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
                    private $state:ng.ui.IStateService,
                    private $mdToast:ng.material.IToastService) {

            this.registerStatePermissions();

        }

        private registerStatePermissions = () => {

            this.$rootScope.$on('$stateChangeStart', (event, toState:global.IState, toParams, fromState:global.IState, fromParams) => {

                if (this.userMustBeLoggedIn(toState) && !this.ngJwtAuthService.loggedIn) {

                    event.preventDefault();

                    //check to see if a password reset token is provided and use that if so
                    if(!_.isEmpty(toParams['passwordResetToken'])) {
                        this.ngJwtAuthService.exchangeToken(toParams['passwordResetToken'])
                            .then(() => {
                                this.$state.go(toState.name, toParams);
                            }, (err) => {
                                this.$mdToast.show(
                                    this.$mdToast.simple()
                                        .hideDelay(2000)
                                        .position('top right')
                                        .content("Sorry, you have already tried to reset your password using this link")
                                );
                                this.showLoginAndRedirectTo(toState, toParams, fromState);
                            });
                    }
                    else {
                        this.showLoginAndRedirectTo(toState, toParams, fromState);
                    }
                }

            })

        };

        private showLoginAndRedirectTo = (toState:global.IState, toParams, fromState:global.IState) => {
            this.ngJwtAuthService.requireCredentialsAndAuthenticate()
                .then(() => {
                    this.$state.go(toState.name, toParams);
                }, (err) => {

                    let returnTo = fromState.name ? fromState.name : 'app.guest.home';

                    let attemptedStateName = this.$state.href(toState, toParams);

                    this.$state.go(returnTo).then(() => {

                        this.$mdToast.show(
                            this.$mdToast.simple()
                                .hideDelay(2000)
                                .position('top right')
                                .content("You are not permitted to access " + attemptedStateName)
                        );

                    }); //go back home
                })
        };

        private userMustBeLoggedIn = (state:global.IState)  => {

            return !!state.data.loggedIn;

        }

    }

    angular.module(namespace, [
        'config.siteModules' //include the site modules after stateManager has been configured so all states can be loaded
    ])
        .config(StateManagerConfig)
        .run(StateManagerInit);

}