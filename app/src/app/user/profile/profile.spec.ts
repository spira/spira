namespace app.user.profile {

    describe('Profile', () => {

        let ProfileController:ProfileController,
            $scope:ng.IScope,
            $rootScope:ng.IRootScopeService,
            $mdToast:ng.material.IToastService,
            countries:common.services.countries.ICountryDefinition,
            timezones:common.services.timezones.ITimezoneDefinition,
            $q:ng.IQService,
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

            inject(($controller, _$rootScope_, _$mdToast_, _$q_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                $mdToast = _$mdToast_;
                $q = _$q_;

                ProfileController = $controller(app.user.profile.namespace + '.controller', {
                    $scope: $scope,
                    userService:userService,
                    user:user,
                    $mdToast: $mdToast,
                    countries: countries,
                    timezones: timezones,
                    userProfile: userProfile
                });
            })

        });

        beforeEach(() => {

            sinon.spy($mdToast, 'show');

        });

        describe('Internal Functions', () => {

            it('should return a list of gender options', () => {

                let genderOptions = ProfileController.genderOptions();

                expect(genderOptions).to.deep.equal(common.models.UserProfile.genderOptions);

            });

        });


        describe('User Interactions', () => {

            it('should be able to update the profile', () => {

                ProfileController.user.email = 'valid@email.com';

                ProfileController.updateProfile();

                $scope.$apply();

                expect($mdToast.show).to.have.been.calledWith(sinon.match.has("template", sinon.match(/Profile update was successful./)));

            });

            it('should display an error message on profile update failure', () => {

                ProfileController.user.email = 'invalid@email.com';

                ProfileController.updateProfile();

                $scope.$apply();

                expect($mdToast.show).to.have.been.calledWith(sinon.match.has("template", sinon.match(/Profile update was unsuccessful, please try again./)));

            });

        });

    });

}