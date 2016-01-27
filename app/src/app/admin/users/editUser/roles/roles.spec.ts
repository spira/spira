namespace app.admin.users.editUser.roles {

    describe('User Role admin', () => {

        let RolesController:RolesController,
            $scope:ng.IScope,
            $rootScope:ng.IRootScopeService,
            $q:ng.IQService,
            roles:common.models.Role[] = common.models.RoleMock.collection(10),
            permissions:common.models.role.Permission[] = common.models.role.PermissionMock.collection(5),
            fullUserInfo:common.models.User = common.models.UserMock.entity({
                _roles: _.take(roles, 3)
            })
        ;

        //nest permissions
        roles = _.map(roles, (role:common.models.Role) => {
            role._permissions = _.sample(permissions, 2);
            return new common.models.Role(role); //re-hydrate so the permissions are hydrated with the circular reference
        });

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
                    roles: roles,
                });
            });

        });

        describe('User Roles admin', () => {

            it('should initialise the roles set with nested permissions and their associated roles that grant the permission', () => {

                let firstRole = _.first(RolesController.roles);

                expect(firstRole).to.have.property('_permissions');

                let firstPermission = _.first(firstRole._permissions);

                expect(firstPermission).to.have.property('__grantedByAll');
                expect(firstPermission).to.have.property('__grantedByRole');

                expect(firstPermission.__grantedByRole).to.contain(firstRole);

            });

            it('should be able to test if the current user has a given role', () => {

                let expectedRole = _.first(fullUserInfo._roles);

                let testResult = RolesController.userHasRole(expectedRole);

                expect(testResult).to.be.true;

            });

            it('should be able to toggle the ownership of a role for a user', () => {

                let toggleRole = _.first(fullUserInfo._roles);

                RolesController.toggleRole(toggleRole);

                expect(RolesController.userHasRole(toggleRole)).to.be.false;

                RolesController.toggleRole(toggleRole);

                expect(RolesController.userHasRole(toggleRole)).to.be.true;


            });

        });

    });

}
