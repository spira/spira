///<reference path="../../../build/js/declarations.d.ts" />

let seededChance = new Chance(1);
let fixtures = {
    get user() {

        let userId = seededChance.guid();
        return {
            _self: '/users/'+userId,
            userId: userId,
            email: seededChance.email(),
            firstName: seededChance.first(),
            lastName: seededChance.last(),
            phone: seededChance.phone()
        }
    },
    get users() {

        return _.range(10).map(() => fixtures.user);

    }
};

describe('UserService', () => {

    let userService:common.services.IUserService;
    let $httpBackend:ng.IHttpBackendService;

    beforeEach(()=> {

        module('app');

        inject((_$httpBackend_, _userService_) => {

            if (!userService) { //dont rebind, so each test gets the singleton
                $httpBackend = _$httpBackend_;
                userService = _userService_; //register injected of service provider
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

        it.skip('should reject the promise getting users fails', () => {

            $httpBackend.expectGET('/api/users').respond(500);

            let allUsersPromise = userService.getAllUsers();

            expect(allUsersPromise).eventually.to.be.rejected;

            $httpBackend.flush();

        });

    });


});
