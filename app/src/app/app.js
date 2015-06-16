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
            '50': 'ffebee',
            '100': 'ffcdd2',
            '200': 'ef9a9a',
            '300': 'rgb(152, 185, 186)',
            '400': 'rgb(152, 185, 186)',
            '500': 'rgb(225, 60, 30)',
            '600': 'e53935',
            '700': 'd32f2f',
            '800': 'rgb(152, 185, 186)',
            '900': 'b71c1c',
            'A100': 'ff8a80',
            'A200': 'ff5252',
            'A400': 'ff1744',
            'A700': 'd50000',
            'contrastDefaultColor': 'light',    // whether, by default, text (contrast)
                                                // on this palette should be dark or light
            'contrastDarkColors': ['50', '100', //hues which contrast should be 'dark' by default
                '200', '300', '400', 'A100'],
            'contrastLightColors': undefined    // could also specify this if default was 'dark'
        });
        $mdThemingProvider.theme('default')
            .primaryPalette('amazingPaletteName')

    })

    .run(function($rootScope) {
        moment.locale('en-gb');
        $rootScope.$on("$stateChangeError", _.bind(console.error, console));
    })

    .controller('AppCtrl', function($scope, $mdSidenav) {

        $scope.toggleLeftNav = function(){
            $mdSidenav('left').toggle();
        }

    })

;