namespace app {

    export const namespace = 'app';

    class AppConfig {

        static $inject = ['ngHttpProgressProvider', '$provide'];

        constructor(ngHttpProgressProvider:NgHttpProgress.IngHttpProgressServiceProvider, $provide:ng.auto.IProvideService) {

            let httpProgressConfig:NgHttpProgress.INgHttpProgressServiceConfig = {
                color: 'white',
                height: '2px',
            };

            ngHttpProgressProvider.configure(httpProgressConfig);

            //$provide.constant('$MD_THEME_CSS', '/**/'); //disable all angular material style injections
        }

    }

    class AppInit {

        static $inject = ['$rootScope', 'ngRestAdapter'];

        constructor(private $rootScope:ng.IRootScopeService,
                    private ngRestAdapter:NgRestAdapter.NgRestAdapterService) {

            moment.locale('en-gb');
            $rootScope.$on('$stateChangeError', _.bind(console.error, console));

            ngRestAdapter.setSkipInterceptorRoutes([
                /\/api\/auth.*/ //skip the /api/auth* routes as they are handled independently by angular-jwt-auth
            ]);

        }

    }

    export class AppController {

        static $inject = ['$mdSidenav', 'ngJwtAuthService', '$state', 'regionService'];

        constructor(private $mdSidenav:ng.material.ISidenavService,
                    public ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
                    public $state:ng.ui.IStateService,
                    public regionService:common.services.region.RegionService) {
        }

        /**
         * Toggle the admin side navigation
         */
        public toggleNavigationSidenav() {
            this.$mdSidenav('navigation').toggle();
        }

        /**
         * Toggle the registration sidenav
         * @param open
         */
        public toggleRegistrationSidenav(open:boolean = !this.$mdSidenav('registration').isOpen()) {

            if (open) {
                this.$mdSidenav('registration').open();
            } else {
                this.$mdSidenav('registration').close();
            }

        }

        public promptLogin():void {
            this.ngJwtAuthService.promptLogin();
        }

        public logout():void {
            this.ngJwtAuthService.logout();
            let currentState:global.IState = <global.IState>this.$state.current;
            if (currentState.name && currentState.data.loggedIn) {
                this.$state.go('app.guest.home'); //go back to the homepage if we are currently in a logged in state
            }
        }

        /**
         * Redirect the user to their profile
         * @param $event
         * @returns {angular.IPromise<any>|IPromise<any>}
         */
        public goToUserProfile($event:ng.IAngularEvent) {
            return this.$state.go('app.user.profile', $event);
        }

    }

    angular.module(namespace, [
        'templates',
        'config.vendorModules',
        'config.commonModules',
        'config.stateManager',
        'app.root'
    ])
        .config(AppConfig)
        .run(AppInit)
        .controller(namespace + '.controller', AppController);

}