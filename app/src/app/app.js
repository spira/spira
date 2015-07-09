angular.module('app', [
    'templates',
    'vendorModules',
    'commonModules',
    'stateManager'
])
    .constant('API_URL', '/api')
    .config(function($mdIconProvider, $mdThemingProvider){
        $mdIconProvider.fontSet('fa', 'fontawesome');

        //$mdThemingProvider.theme('default')
        //    .primaryPalette('green')
        //    .accentPalette('grey')
        //;

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

    })

    .run(function($rootScope) {
        moment.locale('en-gb');
        $rootScope.$on("$stateChangeError", _.bind(console.error, console));
    })

    .controller('AppCtrl', function($scope, $mdSidenav) {

        $scope.toggleLeftNav = function(){
            $mdSidenav('left').toggle();
        };

    })

;