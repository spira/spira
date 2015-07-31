
///<reference path="../../../src/global.d.ts" />

module app.admin {

    export const namespace = 'app.admin';

    class AdminConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
                abstract: true,
                url: '/admin',
                views: {
                    'app@': { // Points to the ui-view in the index.html
                        templateUrl: 'templates/app/_layouts/admin.tpl.html',
                        controller: app.namespace + '.controller',
                        controllerAs: 'AppController',
                    },
                    'navigation@app.admin': { // Points to the ui-view="navigation" in admin.tpl.html
                        templateUrl: 'templates/app/admin/navigation/navigation.tpl.html',
                        controller: app.admin.navigation.namespace+'.controller',
                        controllerAs: 'AdminNavigationController',
                    }
                },
                data: {
                    loggedIn: true,
                    role: 'admin',
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    angular.module('app.admin', [
        'app.admin.dashboard',
        'app.admin.articles',
        'app.admin.media',
        'app.admin.users',
        'app.admin.navigation',
    ])
    .config(AdminConfig);

}