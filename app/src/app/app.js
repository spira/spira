angular.module('app', [
    'templates',
    'vendorModules',
    'commonModules',
    'stateManager'
])
    .constant('API_URL', '/api')
    .config(function(){

    })

    .run(function($rootScope) {
        moment.locale('en-gb');
        $rootScope.$on("$stateChangeError", _.bind(console.error, console));
    })

    .controller('AppCtrl', function($scope) {


        $scope.test = 'hello world';

    })

;