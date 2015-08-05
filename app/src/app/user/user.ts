
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
                        templateUrl: 'templates/app/_layouts/default.tpl.html',
                        controller: app.namespace + '.controller',
                        controllerAs: 'AppController',
                    },
                    'navigation@app.user': { // Points to the ui-view="navigation" in user.tpl.html. This is the primary (top) navigation
                        templateUrl: 'templates/app/guest/navigation/navigation.tpl.html',
                        controller: app.guest.navigation.namespace+'.controller',
                        controllerAs: 'NavigationController',
                    }
                },
                resolve: {
                    user:(ngJwtAuthService:NgJwtAuth.NgJwtAuthService) => {
                        return <common.models.User>ngJwtAuthService.getUser()
                    }
                },
                data: {
                    loggedIn: true,
                    role: 'user',
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