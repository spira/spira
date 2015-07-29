module app.admin.dashboard {

    export const namespace = 'app.admin.dashboard';

    class DashboardConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
                url: '/',
                views: {
                    "main@app.admin": {
                        controller: namespace+'.controller',
                        templateUrl: 'templates/app/admin/dashboard/dashboard.tpl.html'
                    }
                },
                resolve: /*@ngInject*/{
                },
                data: {
                    title: "Admin Dashboard",
                    icon: 'home',
                    navigation: true
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    interface IScope extends ng.IScope
    {
    }

    class DashboardController {

        static $inject = ['$scope'];
        constructor(private $scope : IScope) {

        }

    }

    angular.module(namespace, [])
        .config(DashboardConfig)
        .controller(namespace+'.controller', DashboardController);

}