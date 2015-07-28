
///<reference path="../../../src/global.d.ts" />

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
                        templateUrl: 'templates/app/_partials/navigation/navigation.tpl.html',
                        controller: app.partials.navigation.namespace+'.controller'
                    },
                    'registration@app.guest': { // Points to the ui-view="registration" in default.tpl.html
                        templateUrl: 'templates/app/_partials/registration/registration.tpl.html',
                        controller: app.partials.registration.namespace+'.controller'
                    }
                },
                data: {
                    loggedIn: false,
                    role: 'guest',
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    angular.module('app.guest', [
        'app.guest.home',
        'app.guest.articles',
        'app.guest.sandbox',
        'app.guest.error',
        'app.guest.login',
        'app.guest.resetPassword'
    ])
    .config(GuestConfig);

}