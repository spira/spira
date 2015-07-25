///<reference path="../../../build/js/declarations.d.ts" />

(() => {

    let seededChance = new Chance(1);
    let fixtures = {

        buildUser: (overrides = {}) => {

            let userId = seededChance.guid();
            let defaultUser:global.IUser = {
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
        get user():global.IUser {
            return fixtures.buildUser();
        },
        get users() {
            return _.range(10).map(() => fixtures.user);
        }
    };

    const seededEmail = 'john.smith@example.com'; //the email that was seeded by the API

    describe('UserService', () => {

        let userService:common.services.user.UserService;
        let $httpBackend:ng.IHttpBackendService;
        let authService:NgJwtAuth.NgJwtAuthService;
        let ngRestAdapter:NgRestAdapter.NgRestAdapterService;

        beforeEach(()=> {

            module('app');

            inject((_$httpBackend_, _userService_, _ngJwtAuthService_, _ngRestAdapter_) => {

                if (!userService) { //dont rebind, so each test gets the singleton
                    $httpBackend = _$httpBackend_;
                    userService = _userService_;
                    authService = _ngJwtAuthService_;
                    ngRestAdapter = _ngRestAdapter_;
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

        describe('All users', () => {

            it ('should return all users', () => {

                let users = _.clone(fixtures.users); //get a new user copy

                $httpBackend.expectGET('/api/users').respond(users);

                let allUsersPromise = userService.getAllUsers();

                expect(allUsersPromise).eventually.to.be.fulfilled;
                expect(allUsersPromise).eventually.to.deep.equal(users);

                $httpBackend.flush();

            });

            it('should reject the promise getting users fails', () => {

                $httpBackend.expectGET('/api/users').respond(500);

                let allUsersPromise = userService.getAllUsers();

                expect(allUsersPromise).eventually.to.be.rejected;

                $httpBackend.flush();

            });

        });

        describe('User Registration', () => {


            before(() => authService.logout()); //make sure we are logged out

            describe('Username/Password (non social)', () => {


                it('should be able to create a new user and attempt login immediately',  () => {

                    let user = _.clone(fixtures.user);
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


    });


})();