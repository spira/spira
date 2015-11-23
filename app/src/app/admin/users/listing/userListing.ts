namespace app.admin.users.listing {

    export const namespace = 'app.admin.users.listing';

    export interface IUsersListingStateParams extends ng.ui.IStateParamsService
    {
        page:number;
    }

    export class UsersListingConfig {

        static $inject = ['stateHelperServiceProvider'];

        constructor(private stateHelperServiceProvider) {

            let listingState:global.IState = {
                url: '/{page:int}',
                params: {
                    page: 1
                },
                views: {
                    "main@app.admin": {
                        controller: namespace + '.controller',
                        controllerAs: 'UsersListingController',
                        templateUrl: 'templates/app/admin/users/listing/userListing.tpl.html'
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
                    title: "Find Users",
                    icon: 'group',
                    navigation: true,
                }
            };

            stateHelperServiceProvider.addState(namespace, listingState);

        }

    }

    export class UsersListingController {

        public pages:number[] = [];

        public currentPageIndex:number;

        static $inject = ['usersPaginator', 'initUsers', '$stateParams', 'notificationService', '$mdDialog', 'authService', '$state'];

        constructor(
            private usersPaginator:common.services.pagination.Paginator,
            public users:common.models.User[],
            public $stateParams:IUsersListingStateParams,
            private notificationService:common.services.notification.NotificationService,
            private $mdDialog:ng.material.IDialogService,
            private authService:common.services.auth.AuthService,
            private $state:ng.ui.IStateService
        ) {

            this.pages = usersPaginator.getPages();

            this.currentPageIndex = this.$stateParams.page - 1;

        }

        /**
         * Search for users and update the user set.
         */
        public search(query:string):void {

            this.usersPaginator.query(query)
                .then((users) => {
                    this.users = users;
                }).finally(() => { //@todo handle case where search returns no results
                    this.pages = this.usersPaginator.getPages();
                });

        }

        public promptImpersonateDialog($event:MouseEvent, user:common.models.User) {

            var confirm = this.$mdDialog.confirm()
                .parent("#admin-theme")
                .targetEvent($event)
                .title("Are you sure you want to impersonate this user?")
                .content(`
                    Any action you take as that user will appear to be done by that user.
                    <blockquote cite="Uncle Ben">With great power comes great responsibility <br><small>- Uncle Ben</small></blockquote>
                `)
                .ariaLabel("Confirm impersonate")
                .ok(`Impersonate ${user.fullName()}!`)
                .cancel("Nope! I don't want to do that");

            return this.$mdDialog.show(confirm).then(() => {
                return this.authService.impersonateUser(user);
            })
            .then(() => {
                this.$state.go('app.guest.home');
            });

        }

    }

    angular.module(namespace, [])
        .config(UsersListingConfig)
        .controller(namespace + '.controller', UsersListingController);

}
