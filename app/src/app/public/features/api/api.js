angular.module('app.public.features.api', [])

    .config(function(stateHelperServiceProvider) {
        stateHelperServiceProvider.addState('app.public.features.api', {
            url: '/api',
            views: {
                "featuresContent@app.public.features": {
                    controller: 'app.public.features.api.controller',
                    templateUrl: 'templates/app/public/features/api/api.tpl.html'
                }
            },
            resolve: /*@ngInject*/{
                allUsers: function(userService){
                    return userService.getAllUsers();
                }
            },
            data: {
                title: "Working with the REST API",
                role: 'public',
                icon: 'cloud'
            }
        });
    })

    .controller('app.public.features.api.controller', function($scope, allUsers, $state) {

        $scope.users = allUsers;

        $scope.state = $state.current;

    })

;