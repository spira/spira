namespace app.admin.users {

    export const namespace = 'app.admin.users';

    export interface IUsersListingStateParams extends ng.ui.IStateParamsService
    {
        page:number;
    }

    export class UsersConfig {

        static $inject = ['stateHelperServiceProvider'];

        constructor(private stateHelperServiceProvider) {

            let state:global.IState = {
                url: '/users/{page:int}',
                params: {
                    page: 1
                },
                views: {
                    "main@app.admin": {
                        controller: namespace + '.controller',
                        controllerAs: 'UsersController',
                        templateUrl: 'templates/app/admin/users/users.tpl.html'
                    }
                },
                resolve: /*@ngInject*/{
                    usersPaginator: (userService:common.services.user.UserService) => {
                        return userService.getUsersPaginator().setCount(12);
                    },
                    initUsers: (usersPaginator:common.services.pagination.Paginator, $stateParams:IUsersListingStateParams) => {
                        return usersPaginator.getPage($stateParams.page);
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

        public users:common.models.User[] = [];

        public pages:number[] = [];

        public currentPageIndex:number;

        static $inject = ['usersPaginator', 'initUsers', '$stateParams', 'notificationService'];

        constructor(
            private usersPaginator:common.services.pagination.Paginator,
            users,
            public $stateParams:IUsersListingStateParams,
            private notificationService:common.services.notification.NotificationService
        ) {

            this.users = users;

            this.pages = usersPaginator.getPages();

            this.currentPageIndex = this.$stateParams.page - 1;

        }

        /**
         * Search for users and update the user set.
         */
        public search(searchTerm:string):void {

            this.usersPaginator.query(searchTerm)
                .then((users) => {
                    this.users = users;
                    this.pages = this.usersPaginator.getPages();
                })

        }

    }

    angular.module(namespace, [])
        .config(UsersConfig)
        .controller(namespace + '.controller', UsersController);

}
