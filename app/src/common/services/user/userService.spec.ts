(() => {

    let seededChance = new Chance(1);
    let fixtures = {

        buildUser: (overrides = {}) => {

            let userId = seededChance.guid();
            let defaultUser:global.IUserData = {
                _self: '/users/'+userId,
                userId: userId,
                email: seededChance.email(),
                firstName: seededChance.first(),
                lastName: seededChance.last(),
                _userCredential: {
                    userCredentialId: seededChance.guid(),
                    password: seededChance.string(),
                }
            };

            return _.merge(defaultUser, overrides);
        },
        get user():common.models.User {
            return new common.models.User(fixtures.buildUser());
        },
        get users() {
            return _.range(10).map(() => fixtures.user);
        }
    };
    let userProfile:common.models.UserProfile = <common.models.UserProfile>{
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
        };

    const seededEmail = 'john.smith@example.com'; //the email that was seeded by the API

    describe('UserService', () => {

        let userService:common.services.user.UserService;
        let $httpBackend:ng.IHttpBackendService;
        let authService:NgJwtAuth.NgJwtAuthService;
        let ngRestAdapter:NgRestAdapter.NgRestAdapterService;
        let $mdDialog:ng.material.IDialogService;
        let $timeout:ng.ITimeoutService;

        beforeEach(()=> {

            module('app');

            inject((_$httpBackend_, _userService_, _ngJwtAuthService_, _ngRestAdapter_, _$mdDialog_, _$timeout_) => {

                if (!userService) { //dont rebind, so each test gets the singleton
                    $httpBackend = _$httpBackend_;
                    userService = _userService_;
                    authService = _ngJwtAuthService_;
                    ngRestAdapter = _ngRestAdapter_;
                    $mdDialog = _$mdDialog_;
                    $timeout = _$timeout_;
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

                let user = _.clone(fixtures.user);

                $httpBackend.expectGET('/api/users/' + user.userId).respond(200);

                let userDetailsPromise = userService.getUser(user);

                expect(userDetailsPromise).eventually.to.be.fulfilled;

                $httpBackend.flush();

            });

            it('should return a new user created from user data', () => {

                let userData = _.clone(fixtures.buildUser());

                let user = common.services.user.UserService.userFactory(userData);

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

                let users = _.clone(fixtures.users); // Get a set of users

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

                    let user = _.compactObject(fixtures.user);
                    delete user._self;

                    $httpBackend.expectPUT(/\/api\/users\/.+/, (requestObj) => {
                        return _.isEqual(_.keys(user), _.keys(JSON.parse(requestObj))); //as we are not aware of what the userId or userCredentialId is we cannot test full equality
                    }).respond(204);
                    $httpBackend.expectGET('/api/auth/jwt/login', (headers) => /Basic .*/.test(headers['Authorization'])).respond(200);
                    //note the above auth request does not return a valid token so the login will not be successful so we can't test for that

                    userService.registerAndLogin(user.email, user._userCredential.password, user.firstName, user.lastName);

                    $httpBackend.flush();

                });

            });

            describe('Email checking', () => {


                it('should be able to poll the api to check if an email has been registered', () => {

                    let notExistingEmail = 'not-registered@example.com';

                    $httpBackend.expectHEAD('/api/users/email/'+notExistingEmail).respond(404);
                    $httpBackend.expectHEAD('/api/users/email/'+seededEmail).respond(200);

                    let notExistingCheck = userService.isEmailRegistered(notExistingEmail);
                    let existingCheck = userService.isEmailRegistered(seededEmail);

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

                let user = _.clone(fixtures.user);

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

                let user = _.clone(fixtures.user);
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

                let user = _.clone(fixtures.user);

                let profile = _.clone(userProfile);

                profile.dob = '1995-01-01';
                profile.about = 'Ipsum';
                user.firstName = 'FooBar';

                user._userProfile = profile;

                $httpBackend.expectPATCH('/api/users/' + user.userId,
                    (jsonData:string) => {
                        let data:common.models.User = JSON.parse(jsonData);
                        return data.firstName == 'FooBar' && data._userProfile.dob == '1995-01-01' && data._userProfile.about == 'Ipsum';
                    }).respond(204);

                let profileUpdatePromise = userService.updateUser(user);

                expect(profileUpdatePromise).eventually.to.be.fulfilled;

                $httpBackend.flush();
            });

        });

    });


})();