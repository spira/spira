namespace app.admin.utilities {

    export const namespace = 'app.admin.utilities';

    export class UtilitiesConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
                url: '/utilities',
                abstract: true,
                data: {
                    title: "Utilities",
                    icon: 'settings_applications',
                    navigation: true,
                    navigationGroup: 'admin',
                    sortAfter: app.admin.users.namespace,
                }
            };

            stateHelperServiceProvider.addState(namespace, state);
        }

    }

    angular.module(namespace, [
        namespace + '.systemInformation',
    ])
        .config(UtilitiesConfig);

}