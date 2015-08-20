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
            UsersController:UsersController,
            $q:ng.IQService;

        beforeEach(() => {

            module('app');

            inject(($controller, _$rootScope_, _userService_, _$q_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                userService = _userService_;
                $q = _$q_;

                // Setup usersPaginator before injection
                usersPaginator = userService.getUsersPaginator().setCount(10);
                usersPaginator.query = (searchTerm:string) => {
                    return $q.when(['foo', 'bar', 'foobar']);
                };

                UsersController = $controller(app.admin.users.namespace + '.controller', {
                    usersPaginator: usersPaginator,
                    initUsers: users,
                    $stateParams: $stateParams
                });
            });

            sinon.spy(usersPaginator, 'query');
            sinon.spy(usersPaginator, 'getPages');

        });

        afterEach(() => {

            (<any>usersPaginator).query.restore();
            (<any>usersPaginator).getPages.restore();

        });

        it('should be able to search for users', () => {

            UsersController.search('foobar');

            $scope.$apply();

            expect(usersPaginator.query).to.have.been.calledWith('foobar');

            expect(UsersController.users).to.be.deep.equal(['foo', 'bar', 'foobar']);

            expect(usersPaginator.getPages).to.have.been.called;
        });

    });

}