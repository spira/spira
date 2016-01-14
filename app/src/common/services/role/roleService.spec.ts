namespace common.services.user {

    describe('UserService', () => {

        let userService:UserService;
        let $httpBackend:ng.IHttpBackendService;
        let authService:NgJwtAuth.NgJwtAuthService;
        let ngRestAdapter:NgRestAdapter.NgRestAdapterService;
        let $mdDialog:ng.material.IDialogService;
        let $timeout:ng.ITimeoutService;
        let $rootScope:ng.IRootScopeService;

        beforeEach(()=> {

            module('app');

            inject((_$httpBackend_, _userService_, _ngJwtAuthService_, _ngRestAdapter_, _$mdDialog_, _$timeout_, _$rootScope_) => {

                if (!userService) { //dont rebind, so each test gets the singleton
                    $httpBackend = _$httpBackend_;
                    userService = _userService_;
                    authService = _ngJwtAuthService_;
                    ngRestAdapter = _ngRestAdapter_;
                    $mdDialog = _$mdDialog_;
                    $timeout = _$timeout_;
                    $rootScope = _$rootScope_;
                }
            });

        });

        afterEach(() => {
            $httpBackend.verifyNoOutstandingExpectation();
            $httpBackend.verifyNoOutstandingRequest();
        });

        describe('Initialisation', () => {

            it('should be an injectable service', () => {

                return expect(userService).to.be.an('object');
            });

        });

        describe('Retrieving User/Users', () => {

            it('should be able to retrieve full info for one user', () => {

                let user = _.clone(common.models.UserMock.entity());

                $httpBackend.expectGET('/api/users/' + user.userId,
                    (headers) => /userCredential, userProfile, socialLogins/.test(headers['With-Nested'])
                ).respond(200);

                let userDetailsPromise = userService.getModel(user.userId, ['userCredential', 'userProfile', 'socialLogins']);

                expect(userDetailsPromise).eventually.to.be.fulfilled;

                $httpBackend.flush();

            });

            it('should not request `With-Nested` when nested entities are not requested', () => {

                let user = common.models.UserMock.entity();

                $httpBackend.expectGET('/api/users/' + user.userId,
                    (headers) => !_.has(headers, 'With-Nested')
                ).respond(200);

                let userDetailsPromise = userService.getModel(user.userId);

                expect(userDetailsPromise).eventually.to.be.fulfilled;

                $httpBackend.flush();
            });

            it('should return a new user created from user data', () => {

                let userData = _.clone(common.models.UserMock.entity());

                let user = userService.modelFactory(userData);

                expect(user).to.be.instanceOf(common.models.User);

                expect(userData.email).to.equal(user.email);

                expect(userData.userId).to.equal(user.userId);

            });

            it('should be able to get a user from auth', () => {

                sinon.spy(authService, 'getUser');

                userService.getAuthUser();

                expect(authService.getUser).to.have.been.called;

                (<any>authService).getUser.restore();

            });

            describe('Retrieve a user paginator', () => {

                beforeEach(() => {

                    sinon.spy(ngRestAdapter, 'get');

                });

                afterEach(() => {
                    (<any>ngRestAdapter.get).restore();
                });

                let users = _.clone(common.models.UserMock.collection()); // Get a set of users

                it('should return the first set of users', () => {

                    $httpBackend.expectGET('/api/users').respond(_.take(users, 10));

                    let usersPaginator = userService.getUsersPaginator();

                    let firstSet = usersPaginator.getNext(10);

                    expect(firstSet).eventually.to.be.fulfilled;
                    expect(firstSet).eventually.to.deep.equal(_.take(users, 10));

                    $httpBackend.flush();

                });

            });

        });

        describe('User Registration', () => {


            before(() => authService.logout()); //make sure we are logged out

            describe('Username/Password (non social)', () => {

                it('should be able to create a new user and attempt login immediately',  () => {

                    let user = common.models.UserMock.entity();
                    user._userCredential = new common.models.UserCredential({
                        password: 'hunter2',
                    });

                    $httpBackend.expectPUT(/\/api\/users\/.+/, (requestObj) => {
                        return _.every(['userId', 'email', 'username', 'firstName', 'lastName'], _.hasOwnProperty, JSON.parse(requestObj));
                    }).respond(204);

                    $httpBackend.expectPUT(/\/api\/users\/.+\/credentials/, (requestObj) => {
                        return _.every(['userId', 'userCredentialId','password'], _.hasOwnProperty, JSON.parse(requestObj));
                    }).respond(204);


                    $httpBackend.expectGET('/api/auth/jwt/login', (headers) => /Basic .*/.test(headers['Authorization'])).respond(200);
                    //note the above auth request does not return a valid token so the login will not be successful so we can't test for that

                    userService.registerAndLogin(user.email, user.username, user._userCredential.password, user.firstName, user.lastName);

                    $httpBackend.flush();

                });

            });

            describe('Email checking', () => {


                it('should be able to poll the api to check if an email has been registered', () => {

                    let notExistingEmail = 'not-registered@example.com';
                    let existingEmail = 'registered@example.com';

                    $httpBackend.expectHEAD('/api/users/email/'+notExistingEmail).respond(404);
                    $httpBackend.expectHEAD('/api/users/email/'+existingEmail).respond(200);

                    let notExistingCheck = userService.isEmailRegistered(notExistingEmail);
                    let existingCheck = userService.isEmailRegistered(existingEmail);

                    $httpBackend.flush();

                    expect(notExistingCheck).eventually.to.become(false);
                    expect(existingCheck).eventually.to.become(true);

                });

            });

        });

        describe('Password Reset', () => {

            it('should open the password reset dialog', () => {

                sinon.spy($mdDialog, 'show');

                userService.promptResetPassword();

                $timeout.flush();

                expect($mdDialog.show).to.have.been.called;

                (<any>$mdDialog).show.restore();

            });

            it('should be able to send a reset password email', () => {

                let email = 'test@email.com';

                $httpBackend.expectDELETE('/api/users/' + email + '/password').respond(202);

                let resetPasswordPromise = userService.resetPassword(email);

                expect(resetPasswordPromise).eventually.to.be.fulfilled;

                $httpBackend.flush();

            });

        });

        describe('Change Email', () => {

            it('should be able to send a patch request to confirm email change', () => {

                let user = _.clone(common.models.UserMock.entity());

                const emailToken = 'cf8a43a2646fd46c2081960ff1150a6b48d5ed062da3d59559af5030eea21548';

                $httpBackend.expectPATCH('/api/users/' + user.userId,
                    (jsonData:string) => {
                        let data:{emailConfirmed:string} = JSON.parse(jsonData);
                        // TSD only has definitions for moment 2.8.0 which does not contain function isBetween
                        return data.emailConfirmed && (<any>moment)(data.emailConfirmed).isBetween(moment().subtract(1, 'second'), moment());
                    },
                    (headers) => {
                        return headers['email-confirm-token'] == emailToken;
                    }).respond(202);

                let emailConfirmationPromise = userService.confirmEmail(user, emailToken);

                expect(emailConfirmationPromise).eventually.to.be.fulfilled;

                $httpBackend.flush();
            });

            it('should reject the promise if a bogus user id is passed through', () => {

                let user = _.clone(common.models.UserMock.entity());
                user.userId = 'bogus-user-id';

                const emailToken = 'cf8a43a2646fd46c2081960ff1150a6b48d5ed062da3d59559af5030eea21548';

                $httpBackend.expectPATCH('/api/users/' + user.userId).respond(422);

                let emailConfirmationPromise = userService.confirmEmail(user, emailToken);

                expect(emailConfirmationPromise).eventually.to.be.rejected;

                $httpBackend.flush();
            });

        });

        describe('Update Details', () => {

            it('should be able to send a patch request to update the user details (including profile)', () => {

                let user = common.models.UserMock.entity();

                user.firstName = 'Joe';
                user._userProfile = common.models.UserProfileMock.entity();
                user._userProfile.dob = moment('1995-01-01');
                user._userProfile.about = 'Ipsum';

                $httpBackend.expectPATCH('/api/users/' + user.userId, (jsonData:string) => {
                    let data:common.models.User = JSON.parse(jsonData);
                    return data.firstName == user.firstName;
                }).respond(204);

                $httpBackend.expectPATCH('/api/users/' + user.userId + '/profile', (jsonData:string) => {
                    let data:any = JSON.parse(jsonData);
                    return data.dob == user._userProfile.dob.toISOString() && data.about == user._userProfile.about;
                }).respond(204);

                let profileUpdatePromise = userService.saveUserWithRelated(user);

                $rootScope.$apply();

                expect(profileUpdatePromise).eventually.to.be.fulfilled;

                $httpBackend.flush();
            });


            it('should not make an api call if nothing has changed', () => {

                let user = common.models.UserMock.entity({
                    _userProfile: common.models.UserProfileMock.entity(null, true),
                }, true);


                let savePromise = userService.saveUserWithRelated(user);

                expect(savePromise).eventually.to.equal(user);

            });


            it('should update the region setting when the user updates their profile', () => {

                let user = common.models.UserMock.entity({
                    regionCode: 'us',
                    _userProfile: common.models.UserProfileMock.entity(null, true),
                }, true);

                $httpBackend.expectPATCH('/api/users/' + user.userId, {
                    regionCode: 'au',
                }).respond(204);

                user.regionCode = 'au';

                let savePromise = userService.saveUserWithRelated(user);

                $httpBackend.flush();

                expect(savePromise).eventually.to.equal(user);

            });

        });

    });


}