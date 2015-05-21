angular.module('app.public.home', [])

    .config(function(stateHelperServiceProvider) {
        stateHelperServiceProvider.addState('app.public.home', {
            url: '/',
            views: {
                "main@app.public": {
                    controller: 'app.public.home.controller',
                    templateUrl: 'templates/app/public/home/home.tpl.html'
                }
            },
            resolve: /*@ngInject*/{
            },
            data: {
                title: "Home",
                role: 'public',
                icon: 'home'
            }
        });
    })

    .controller('app.public.home.controller', function($scope) {


    })

;