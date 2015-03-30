angular.module('app', [
    'templates',
    'vendorModules',
    'commonModules',
    'stateManager'
])
    .constant('API_URL', '/api')
    .config(function(){

    })

    .run(function() {
        moment.locale('en-gb');
    })

    .controller('AppCtrl', function($scope) {


        $scope.test = 'hello world';

    })

;