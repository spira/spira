namespace app.admin.users.editUser {

    describe('Admin - Edit user', () => {

        let users:common.models.User[],
            $rootScope:global.IRootScope,
            $scope:ng.IScope,
            $stateParams:IEditUserStateParams = <IEditUserStateParams> {
                userId:chance.guid()
            },
            userService:common.services.user.UserService,
            EditUserController:EditUserController,
            $mdDialog:ng.material.IDialogService,
            authService:common.services.auth.AuthService,
            $state:ng.ui.IStateService,
            $q:ng.IQService,
            notificationService:common.services.notification.NotificationService,
            countries:common.services.countries.ICountryDefinition,
            timezones:common.services.timezones.ITimezoneDefinition,
            fullUserInfo:common.models.User = common.models.UserMock.entity(),
            genderOptions:common.models.IGenderOption[] = common.models.UserProfile.genderOptions,
            providerTypes:string[] = common.models.UserSocialLogin.providerTypes,
            roles:common.models.Role[] = common.models.RoleMock.collection()
        ;

        beforeEach(() => {

            module('app');

            inject(($controller, _$rootScope_, _userService_, _$q_, _$mdDialog_, _authService_, _$state_, _notificationService_, _regionService_, _$location_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                userService = _userService_;
                $q = _$q_;
                $mdDialog = _$mdDialog_;
                authService = _authService_;
                $state = _$state_;
                notificationService = _notificationService_;

                EditUserController = $controller(app.admin.users.editUser.namespace + '.controller', {
                    $scope: $scope,
                    userService:userService,
                    notificationService:notificationService,
                    emailConfirmed:$q.when(false),
                    countries: countries,
                    timezones: timezones,
                    fullUserInfo: fullUserInfo,
                    genderOptions: genderOptions,
                    authService: authService,
                    providerTypes: providerTypes,
                    regions: _regionService_.supportedRegions,
                    $location: _$location_,
                    $stateParams: $stateParams,
                    roles: roles,
                });

            });

        });

        it('should be able to prompt an impersonation dialog, then navigate to the root state on confirm', () => {

            let impersonateUser = common.models.UserMock.entity();
            let event = global.MouseEventMock.getMock();

            let dialogShowStub = sinon.stub($mdDialog, 'show');
            dialogShowStub.returns($q.when(true));

            let authImpersonateStub = sinon.stub(authService, 'impersonateUser');
            authImpersonateStub.returns($q.when(true));

            let stateLoadStub = sinon.stub($state, 'go');

            EditUserController.promptImpersonateDialog(event, impersonateUser);

            $rootScope.$apply();

            expect(dialogShowStub).to.have.been.called;
            expect(authImpersonateStub).to.have.been.calledWith(impersonateUser);
            expect(stateLoadStub).to.have.been.calledWith('app.guest.home');

            dialogShowStub.restore();
            authImpersonateStub.restore();
            stateLoadStub.restore();

        });

    });

}