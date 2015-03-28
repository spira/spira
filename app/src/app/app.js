angular.module('app', [
    'vendorModules'
])
    .config(function(){

    })

    .run(function() {
        moment.locale('en-gb');
    })

    .controller('app.controller', function($scope) {


        $scope.test = 'hello world';

    })

;