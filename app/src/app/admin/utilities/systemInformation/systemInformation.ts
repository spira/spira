namespace app.admin.utilities.systemInformation {

    export const namespace = 'app.admin.utilities.systemInformation';

    export class SystemInformationConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
                url: '/system-information',
                views: {
                    "main@app.admin": {
                        controller: namespace+'.controller',
                        controllerAs: 'SystemInformationController',
                        templateUrl: 'templates/app/admin/utilities/systemInformation/systemInformation.tpl.html',
                    }
                },
                resolve: /*@ngInject*/{
                    appSystemInformation: (ngRestAdapter:NgRestAdapter.NgRestAdapterService) => {
                        return ngRestAdapter.api('/').get('build-info.json').then((res) => new common.models.SystemInformation(res.data));
                    },
                },
                data: {
                    title: "System Information",
                    icon: 'developer_mode',
                    navigation: true,
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }


    export class SystemInformationController {

        static $inject = [
            'appSystemInformation',
        ];
        constructor(
            public appSystemInformation:common.models.SystemInformation
        ) {
        }

    }

    angular.module(namespace, [])
        .config(SystemInformationConfig)
        .controller(namespace+'.controller', SystemInformationController);
}




