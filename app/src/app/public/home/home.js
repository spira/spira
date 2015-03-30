angular.module('app.public.home', [])

    .config(function(stateHelperServiceProvider) {
        stateHelperServiceProvider.addState('app.public.home', {
            url: '/',
            views: {
                "main@app.public": { // Points to the ui-view="main" in modal-layout.tpl.html
                    controller: 'app.public.home.controller',
                    templateUrl: 'templates/app/public/home/home.tpl.html'
                }
            },
            resolve: /*@ngInject*/{
                allUsers: function(userService){
                    return userService.getAllUsers();
                }
            },
            data: {
                title: "Home",
                role: 'public'
            }
        });
    })

    .controller('app.public.home.controller', function($scope, allUsers) {


        $scope.users = allUsers;

    })

;