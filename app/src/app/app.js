angular.module('app', [

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