
///<reference path="../../global.d.ts" />

module app.guest {

    export const namespace = 'app.guest';

    class GuestConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
                abstract: true,
                views: {
                    'app@': { // Points to the ui-view in the index.html
                        templateUrl: 'templates/app/_layouts/default.tpl.html'
                    },
                    'navigation@app.guest': { // Points to the ui-view="navigation" in default.tpl.html
                        templateUrl: 'templates/app/_partials/navigation.tpl.html',
                        controller: app.partials.navigation.namespace+'.controller'
                    }
                },
                data: {
                    role: 'public'
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    angular.module('app.guest', [
        'app.guest.home',
        'app.guest.articles',
        'app.guest.sandbox',
        'app.guest.error'
    ])
    .config(GuestConfig);

}