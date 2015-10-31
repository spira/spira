namespace app.admin.dashboard {

    export const namespace = 'app.admin.dashboard';

    class DashboardConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
                url: '',
                views: {
                    "main@app.admin": {
                        controller: namespace+'.controller',
                        templateUrl: 'templates/app/admin/dashboard/dashboard.tpl.html'
                    }
                },
                resolve: /*@ngInject*/{
                },
                data: {
                    title: "Dashboard",
                    icon: 'dashboard',
                    navigation: true
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    export class DashboardController {

        static $inject = [];
        constructor() {

        }

    }

    angular.module(namespace, [])
        .config(DashboardConfig)
        .controller(namespace+'.controller', DashboardController);

}