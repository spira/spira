///<reference path="../../typings/tsd.d.ts" />

namespace config.stateManager {

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

        static $inject = ['$rootScope', 'ngRestAdapter', 'ngJwtAuthService', '$state', 'notificationService', 'authService'];

        constructor(private $rootScope:ng.IRootScopeService,
                    private ngRestAdapter:NgRestAdapter.NgRestAdapterService,
                    private ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
                    private $state:ng.ui.IStateService,
                    private notificationService:common.services.notification.NotificationService,
                    private authService:common.services.auth.AuthService
        ) {

            this.registerStatePermissions();
        }

        /**
         * Register the state change start transition
         * 1) Verify if the user must be logged in to state
         * 2) If so and user is not logged in, stop the transition and prompt the user to log in after waiting for
         *      the auth service to initialise
         * 3) Once the user has authenticated redirect them to the state they were trying to access
         * 4) If they fail to authenticate, return them to the home state.
         */
        private registerStatePermissions = ():void => {

            this.$rootScope.$on('$stateChangeStart', (event, toState:global.IState, toParams, fromState:global.IState, fromParams):ng.IPromise<any> => {

                if (this.userMustBeLoggedIn(toState) && !this.ngJwtAuthService.loggedIn) {

                    event.preventDefault();

                    //defer prompting for login until the auth service has completed all checks
                    return this.authService.initialisedPromise.finally(() => {

                        if(this.ngJwtAuthService.loggedIn){ //user is still not logged in after authentication initialisation
                            return this.$state.go(toState.name, toParams);
                        }

                        return this.showLoginAndRedirectTo(toState, toParams, fromState);

                    });

                }

            });

        };

        /**
         * Show the login to the user then redirect to their intended state or the home state if login fails.
         * @param toState
         * @param toParams
         * @param fromState
         * @returns {IPromise<TResult>}
         */
        private showLoginAndRedirectTo = (toState:global.IState, toParams, fromState:global.IState):ng.IPromise<any> => {

            return this.ngJwtAuthService.requireCredentialsAndAuthenticate()
                .then(() => this.$state.go(toState.name, toParams),
                (err) => {

                    let returnTo = fromState.name ? fromState.name : 'app.guest.home';

                    let attemptedStateName = this.$state.href(toState, toParams);

                    return this.$state.go(returnTo).then(() => {

                        this.notificationService.toast('You are not permitted to access ' + attemptedStateName).options({position:'top right'}).pop();

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
