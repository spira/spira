module app {

    export const namespace = 'app';

    class AppConfig {

        static $inject = ['$mdThemingProvider', '$mdIconProvider', 'ngHttpProgressProvider'];

        constructor($mdThemingProvider:ng.material.IThemingProvider, $mdIconProvider:ng.material.IIconProvider, ngHttpProgressProvider:NgHttpProgress.IngHttpProgressServiceProvider) {

            $mdThemingProvider.theme('defaultTheme')
                .primaryPalette('green')
                .accentPalette('grey')
            ;

            $mdThemingProvider.theme('adminTheme')
                .primaryPalette('blue-grey')
                .accentPalette('deep-orange')
            ;

            $mdThemingProvider.setDefaultTheme('defaultTheme');

            let httpProgressConfig:NgHttpProgress.INgHttpProgressServiceConfig = {
                color: 'green',
                height: '2px',
            };

            ngHttpProgressProvider.configure(httpProgressConfig);

            //$mdIconProvider.defaultFontSet('fontawesome');

            /*
             $mdThemingProvider.definePalette('amazingPaletteName', {
             '50': 'rgb(100, 100, 5)',
             '100': 'rgb(100, 100, 10)',
             '200': 'rgb(100, 100, 20)',
             '300': 'rgb(100, 100, 30)',
             '400': 'rgb(100, 100, 40)',
             '500': 'rgb(100, 100, 50)',
             '600': 'rgb(100, 100, 60)',
             '700': 'rgb(100, 100, 70)',
             '800': 'rgb(100, 100, 80)',
             '900': 'rgb(100, 100, 90)',
             'A100': 'rgb(100, 100, 110)',
             'A200': 'rgb(100, 100, 120)',
             'A400': 'rgb(100, 100, 140)',
             'A700': 'rgb(100, 100, 170)',
             'contrastDefaultColor': 'light',    // whether, by default, text (contrast)
             // on this palette should be dark or light
             'contrastDarkColors': ['50', '100', //hues which contrast should be 'dark' by default
             '200', '300', '400', 'A100'],
             'contrastLightColors': undefined    // could also specify this if default was 'dark'
             });
             $mdThemingProvider.theme('default')
             .primaryPalette('amazingPaletteName');

             */
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

        static $inject = ['$mdSidenav', 'ngJwtAuthService', '$state'];

        constructor(private $mdSidenav:ng.material.ISidenavService, public authService:NgJwtAuth.NgJwtAuthService, private $state:ng.ui.IStateService) {
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
        public toggleRegistrationSidenav(open:boolean) {
            if (_.isUndefined(open)) {
                this.$mdSidenav('registration').toggle();
                return;
            }

            open ? this.$mdSidenav('registration').open() : this.$mdSidenav('registration').close();

        }

        /**
         * Redirect the user to their profile
         * @param $event
         * @returns {angular.IPromise<any>|IPromise<any>}
         */
        public goToUserProfile($event:ng.IAngularEvent){
            return this.$state.go('app.user.profile', $event);
        }

    }

    angular.module(namespace, [
        'templates',
        'config.vendorModules',
        'config.commonModules',
        'config.stateManager',
    ])
        .config(AppConfig)
        .run(AppInit)
        .controller(namespace + '.controller', AppController);

}