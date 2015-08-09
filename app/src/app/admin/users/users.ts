namespace app.admin.users {

    export const namespace = 'app.admin.users';

    export class UsersConfig {

        static $inject = ['stateHelperServiceProvider'];

        constructor(private stateHelperServiceProvider) {

            let state:global.IState = {
                url: '/users',
                views: {
                    "main@app.admin": {
                        controller: namespace + '.controller',
                        controllerAs: 'UsersController',
                        templateUrl: 'templates/app/admin/users/users.tpl.html'
                    }
                },
                resolve: /*@ngInject*/{
                    allUsers: (userService:common.services.user.UserService) => {
                        return userService.getAllUsers();
                    }
                },
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

    export class UsersController {

        static $inject = ['allUsers'];

        constructor(public allUsers:common.models.User[]) {

            this.allUsers = allUsers.map((user:common.models.User) => {
                (<any>user).fullname = user.fullName();
                return user;
            })

        }

    }

    angular.module(namespace, [])
        .config(UsersConfig)
        .controller(namespace + '.controller', UsersController);

}