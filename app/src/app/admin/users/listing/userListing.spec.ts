namespace app.admin.users.listing {

    describe('Admin - Users', () => {

        let users:common.models.User[],
            $rootScope:global.IRootScope,
            $scope:ng.IScope,
            $stateParams:IUsersListingStateParams = <IUsersListingStateParams> {
                page:1
            },
            userService:common.services.user.UserService,
            usersPaginator:common.services.pagination.Paginator,
            UsersListingController:UsersListingController,
            $mdDialog:ng.material.IDialogService,
            authService:common.services.auth.AuthService,
            $state:ng.ui.IStateService,
            $q:ng.IQService;

        beforeEach(() => {

            module('app');

            inject(($controller, _$rootScope_, _userService_, _$q_, _$mdDialog_, _authService_, _$state_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                userService = _userService_;
                $q = _$q_;
                $mdDialog = _$mdDialog_;
                authService = _authService_;
                $state = _$state_;

                // Setup usersPaginator before injection
                usersPaginator = userService.getUsersPaginator().setCount(10);
                usersPaginator.query = (searchTerm:string) => {
                    return $q.when(['foo', 'bar', 'foobar']);
                };

                UsersListingController = $controller(app.admin.users.listing.namespace + '.controller', {
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

            UsersListingController.search('foobar');

            $scope.$apply();

            expect(usersPaginator.query).to.have.been.calledWith('foobar');

            expect(UsersListingController.users).to.be.deep.equal(['foo', 'bar', 'foobar']);

            expect(usersPaginator.getPages).to.have.been.called;
        });

    });

}