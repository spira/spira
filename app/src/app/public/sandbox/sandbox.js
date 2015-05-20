angular.module('app.public.sandbox', [])

    .config(function(stateHelperServiceProvider) {
        stateHelperServiceProvider.addState('app.public.sandbox', {
            url: '/sandbox',
            views: {
                "main@app.public": { // Points to the ui-view="main" in modal-layout.tpl.html
                    controller: 'app.public.sandbox.controller',
                    templateUrl: 'templates/app/public/sandbox/sandbox.tpl.html'
                }
            },
            resolve: /*@ngInject*/{

            },
            data: {
                title: "Sandbox",
                role: 'public'
            }
        });
    })

    .controller('app.public.sandbox.controller', function($scope) {


    })

;