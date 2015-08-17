namespace app.user.profile {

    describe('Profile', () => {

        let ProfileController:ProfileController,
            $scope:ng.IScope,
            $rootScope:ng.IRootScopeService,
            countries:common.services.countries.ICountryDefinition,
            timezones:common.services.timezones.ITimezoneDefinition,
            $q:ng.IQService,
            genderOptions:common.models.IGenderOption[] = common.models.UserProfile.genderOptions,
            notificationService:common.services.notification.NotificationService,
            userCredential:global.IUserCredential = <global.IUserCredential>{
                userCredentialId:'007a61cb-3143-3f40-8436-dfab437c1871',
                password:'Password'
            },
            userProfile:common.models.UserProfile = <common.models.UserProfile>{
                dob:'1921-01-01',
                mobile:'04123123',
                phone:'',
                gender:'M',
                about:'Lorem',
                facebook:'',
                twitter:'',
                pinterest:'',
                instagram:'',
                website:''
            },
            fullUserInfo:common.models.User = <common.models.User>{
                userId:'007a61cb-3143-3f40-8436-dfab437c1871',
                email:'john.doe@example.com',
                firstName:'John',
                lastName:'Doe',
                emailConfirmed:'2015-02-02 03:01:06',
                country:'Australia',
                avatarImgUrl:'http://somepicture.com/image',
                timezoneIdentifier:'America/Guyana',
                _userCredential:userCredential,
                _userProfile:userProfile,
                userType:'guest'
            },
            userService = {
                updateUser: (user:common.models.User) => {
                    if (user.email == 'invalid@email.com') {
                        return $q.reject({data: {message: 'this failure message'}});
                    }
                    else {
                        return $q.when(true);
                    }
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

            inject(($controller, _$rootScope_, _$q_, _notificationService_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                $q = _$q_;
                notificationService = _notificationService_;

                ProfileController = $controller(app.user.profile.namespace + '.controller', {
                    $scope: $scope,
                    userService:userService,
                    fullUserInfo: fullUserInfo,
                    notificationService:notificationService,
                    countries: countries,
                    timezones: timezones,
                    userProfile: userProfile,
                    genderOptions: genderOptions,
                    authService: authService
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


            it('should be able to update the profile', () => {

                ProfileController.fullUserInfo.email = 'valid@email.com';

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

                let userLoginDataFacebook:common.models.UserSocialLogin = {
                    userId:ProfileController.fullUserInfo.userId,
                    provider:common.models.UserSocialLogin.facebookType,
                    token:'eyJtZXRob2QiOiJnb29nbGUiLCJzdWIiOiJkODU2ZWI2OS1jYTU4LTQ2M2MtOWNlZS05MTRlMDlkOWZlNWYiLCJfdXNlci'
                };

                ProfileController.fullUserInfo._socialLogins = (<common.models.UserSocialLogin[]>[]);

                ProfileController.fullUserInfo._socialLogins.push(userLoginDataFacebook);

                let socialLoginCount = _.size(ProfileController.fullUserInfo._socialLogins);

                ProfileController.unlinkSocialLogin(common.models.UserSocialLogin.facebookType);

                expect(authService.unlinkSocialLogin).to.have.been.calledWith(ProfileController.fullUserInfo, common.models.UserSocialLogin.facebookType);

                $scope.$apply();

                expect(socialLoginCount).to.be.greaterThan(_.size(ProfileController.fullUserInfo._socialLogins));

                expect(notificationService.toast).to.have.been.calledWith('Your ' + _.capitalize(common.models.UserSocialLogin.facebookType) + ' has been unlinked from your account');

            });

        });

    });

}
