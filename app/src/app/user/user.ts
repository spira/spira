
///<reference path="../../../src/global.d.ts" />

module app.user {

    export const namespace = 'app.user';

    class UserConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
                abstract: true,
                views: {
                    'app@': { // Points to the ui-view in the index.html
                        templateUrl: 'templates/app/_layouts/user.tpl.html'
                    },
                    'navigation@app.user': { // Points to the ui-view="navigation" in default.tpl.html
                        templateUrl: 'templates/app/_partials/navigation/navigation.tpl.html',
                        controller: app.partials.navigation.namespace+'.controller'
                    }
                },
                data: {
                    role: 'user'
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    angular.module('app.user', [
        'app.user.profile',
    ])
        .config(UserConfig);

}