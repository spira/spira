namespace app.admin.users {

    describe('Admin - Users', () => {

        let users:common.models.User[],
            $rootScope:global.IRootScope,
            $scope:ng.IScope,
            $stateParams:IUsersListingStateParams = <IUsersListingStateParams> {
                page:1
            },
            userService:common.services.user.UserService,
            usersPaginator:common.services.pagination.Paginator,
            UsersController:UsersController;

        beforeEach(() => {

            module('app');

            inject(($controller, _$rootScope_, _userService_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                userService = _userService_;
                usersPaginator = userService.getUsersPaginator().setCount(10);

                UsersController = $controller(app.admin.users.namespace + '.controller', {
                    usersPaginator: usersPaginator,
                    initUsers: users,
                    $stateParams: $stateParams
                });
            });

        });

        it('should be able to pass this test', () => {

        });

    });

}