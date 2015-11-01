namespace app.admin.users {

    export const namespace = 'app.admin.users';

    export class UsersConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
                url: '/users',
                abstract: true,
                data: {
                    title: "Users",
                    icon: 'group',
                    navigation: true,
                    navigationGroup: 'admin',
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    angular.module(namespace, [
        namespace + '.listing',
        namespace + '.editUser',
    ])
        .config(UsersConfig);

}