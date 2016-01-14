namespace app.admin.users.editUser.roles {

    describe('User Role admin', () => {

        let RolesController:RolesController,
            $scope:ng.IScope,
            $rootScope:ng.IRootScopeService,
            $q:ng.IQService,
            fullUserInfo:common.models.User = common.models.UserMock.entity({});

        beforeEach(() => {

            module('app');

            inject(($controller, _$rootScope_, _$q_, _notificationService_, _$location_, _regionService_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                $q = _$q_;

                RolesController = $controller(app.admin.users.editUser.roles.namespace + '.controller', {
                    $scope: $scope,
                    fullUserInfo: fullUserInfo,
                    regions: _regionService_.supportedRegions,
                });
            });

        });

        describe('User Roles admin', () => {


            it('should do something', () => {

            });

        });

    });

}
