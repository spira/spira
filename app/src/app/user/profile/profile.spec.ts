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
            user:common.models.User = <common.models.User>{
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
                updateProfile: (user:common.models.User) => {
                    if (user.email == 'invalid@email.com') {
                        return $q.reject({data: {message: 'this failure message'}});
                    }
                    else {
                        return $q.when(true);
                    }
                }
            };

        beforeEach(() => {

            module('app');

        });

        beforeEach(() => {

            inject(($controller, _$rootScope_, _$q_, _notificationService_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                $q = _$q_;
                notificationService = _notificationService_;

                ProfileController = $controller(app.user.profile.namespace + '.controller', {
                    $scope: $scope,
                    userService:userService,
                    user:user,
                    notificationService:notificationService,
                    countries: countries,
                    timezones: timezones,
                    userProfile: userProfile,
                    genderOptions: genderOptions
                });
            })

        });

        beforeEach(() => {

            sinon.spy(notificationService, 'toast');

        });

        afterEach(() => {

            (<any>notificationService).toast.restore();

        });

        describe('User Interactions', () => {


            it('should be able to update the profile', () => {

                ProfileController.user.email = 'valid@email.com';

                ProfileController.updateProfile();

                $scope.$apply();

                expect(notificationService.toast).to.have.been.calledWith('Profile update was successful');

            });

            it('should display an error message on profile update failure', () => {

                ProfileController.user.email = 'invalid@email.com';

                ProfileController.updateProfile();

                $scope.$apply();

                expect(notificationService.toast).to.have.been.calledWith('Profile update was unsuccessful, please try again');

            });

        });

    });

}