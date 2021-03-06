namespace app.user.profile {

    describe('Profile', () => {

        let ProfileController:ProfileController,
            $scope:ng.IScope,
            $rootScope:ng.IRootScopeService,
            countries:common.services.countries.ICountryDefinition,
            timezones:common.services.timezones.ITimezoneDefinition,
            $q:ng.IQService,
            genderOptions:common.models.IGenderOption[] = common.models.UserProfile.genderOptions,
            providerTypes:string[] = common.models.UserSocialLogin.providerTypes,
            notificationService:common.services.notification.NotificationService,
            $location:ng.ILocationService,
            userCredential:global.IUserCredential = <global.IUserCredential>{
                userCredentialId:'007a61cb-3143-3f40-8436-dfab437c1871',
                password:'Password'
            },
            fullUserInfo:common.models.User = common.models.UserMock.entity({
                _userCredential:userCredential,
                _userProfile:common.models.UserProfileMock.entity(),
            }),
            userService = {
                saveUserWithRelated: (user:common.models.User) => {
                    if (user.email == 'invalid@email.com') {
                        return $q.reject({data: {message: 'this failure message'}});
                    }
                    else {
                        return $q.when(true);
                    }
                },
                getAuthUser: () => {
                    return fullUserInfo;
                }
            },
            authService = {
                socialLogin:(type:string, redirectState:string = '', redirectStateParams:Object = {}) => {
                    return true;
                },
                unlinkSocialLogin:(user:common.models.User, provider:string) => {
                    return $q.when(true);
                },
            };

        beforeEach(() => {

            module('app');

            inject(($controller, _$rootScope_, _$q_, _notificationService_, _$location_, _regionService_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                $q = _$q_;
                notificationService = _notificationService_;
                $location = _$location_;

                ProfileController = $controller(app.user.profile.namespace + '.controller', {
                    $scope: $scope,
                    userService:userService,
                    notificationService:notificationService,
                    emailConfirmed:$q.when(false), //@todo mock this properly for tests
                    countries: countries,
                    timezones: timezones,
                    fullUserInfo: fullUserInfo,
                    genderOptions: genderOptions,
                    authService: authService,
                    providerTypes: providerTypes,
                    regions: _regionService_.supportedRegions,
                    $location: $location
                });
            });

            sinon.spy(notificationService, 'toast');

            sinon.spy(authService, 'socialLogin');

            sinon.spy(authService, 'unlinkSocialLogin');

        });

        afterEach(() => {

            (<any>notificationService).toast.restore();

            (<any>authService).socialLogin.restore();

            (<any>authService).unlinkSocialLogin.restore();

        });

        describe('User Interactions', () => {


            it('should be able to update the user', () => {

                ProfileController.fullUserInfo.email = 'valid@email.com';

                ProfileController.updateUser();

                $scope.$apply();

                expect(notificationService.toast).to.have.been.calledWith('Profile update was successful');

            });

            it('should be able to update the user with an empty profile', () => {

                ProfileController.fullUserInfo.email = 'valid@email.com';

                ProfileController.fullUserInfo._userProfile = null;

                ProfileController.updateUser();

                $scope.$apply();

                expect(notificationService.toast).to.have.been.calledWith('Profile update was successful');

            });

            it('should display an error message on profile update failure', () => {

                ProfileController.fullUserInfo.email = 'invalid@email.com';

                ProfileController.updateUser();

                $scope.$apply();

                expect(notificationService.toast).to.have.been.calledWith('Profile update was unsuccessful, please try again');

            });

            it('should be able to add a social network login method', () => {

                ProfileController.socialLogin(common.models.UserSocialLogin.facebookType);

                expect(authService.socialLogin).to.have.been.calledWith(common.models.UserSocialLogin.facebookType);

            });

            it('should be able to unlink a social network login method', () => {

                let userLoginDataFacebook = new common.models.UserSocialLogin({
                    userId:ProfileController.fullUserInfo.userId,
                    provider:common.models.UserSocialLogin.facebookType,
                    token:'eyJtZXRob2QiOiJnb29nbGUiLCJzdWIiOiJkODU2ZWI2OS1jYTU4LTQ2M2MtOWNlZS05MTRlMDlkOWZlNWYiLCJfdXNlci'
                });

                ProfileController.fullUserInfo._socialLogins = [ userLoginDataFacebook ];

                ProfileController.unlinkSocialLogin(common.models.UserSocialLogin.facebookType);

                expect(authService.unlinkSocialLogin).to.have.been.calledWith(ProfileController.fullUserInfo, common.models.UserSocialLogin.facebookType);

                $scope.$apply();

                expect(ProfileController.fullUserInfo._socialLogins).to.be.empty;

                expect(notificationService.toast).to.have.been.calledWith('Your ' + _.capitalize(common.models.UserSocialLogin.facebookType) + ' has been unlinked from your account');

            });

        });

    });

}
