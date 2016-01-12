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
                    systemInformation: (systemInformationService:common.services.systemInformation.SystemInformationService):ng.IPromise<common.services.systemInformation.ISystemInformationSources> => {
                        return systemInformationService.getSystemInformation();
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

        public appMatchesApi:boolean;

        static $inject = [
            'systemInformation',
        ];
        constructor(
            public systemInformation:common.services.systemInformation.ISystemInformationSources
        ) {

            this.appMatchesApi = systemInformation.app.latestCommit.commit === systemInformation.api.latestCommit.commit;
        }

    }

    angular.module(namespace, [])
        .config(SystemInformationConfig)
        .controller(namespace+'.controller', SystemInformationController);
}




