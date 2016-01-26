namespace common.services.role {

    describe('RoleService', () => {

        let roleService:RoleService;
        let $httpBackend:ng.IHttpBackendService;

        beforeEach(()=> {

            module('app');

            inject((_$httpBackend_, _roleService_) => {

                if (!roleService) { //dont rebind, so each test gets the singleton
                    $httpBackend = _$httpBackend_;
                    roleService = _roleService_;
                }
            });

        });

        afterEach(() => {
            $httpBackend.verifyNoOutstandingExpectation();
            $httpBackend.verifyNoOutstandingRequest();
        });

        describe('Initialisation', () => {

            it('should be an injectable service', () => {

                return expect(roleService).to.be.an('object');
            });

        });

        describe('Retrieving Roles', () => {

            it('should be able to retrieve all roles', () => {

                let roles = _.clone(common.models.RoleMock.collection(10));

                $httpBackend.expectGET('/api/roles').respond(_.clone(roles));

                let allRolesPromise = roleService.getAllModels();

                expect(allRolesPromise).eventually.to.be.fulfilled;
                expect(allRolesPromise).eventually.to.deep.equal(roles);

                $httpBackend.flush();

            });

        });

    });


}