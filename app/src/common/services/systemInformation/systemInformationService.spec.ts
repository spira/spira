namespace common.services.systemInformation {

    describe('SystemInformationService', () => {

        let systemInformationService:SystemInformationService;
        let $httpBackend:ng.IHttpBackendService;
        let ngRestAdapter:NgRestAdapter.NgRestAdapterService;

        beforeEach(()=> {

            module('app');

            inject((_$httpBackend_, _systemInformationService_, _ngRestAdapter_) => {

                if (!systemInformationService) { //dont rebind, so each test gets the singleton
                    $httpBackend = _$httpBackend_;
                    systemInformationService = _systemInformationService_;
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

                return expect(systemInformationService).to.be.an('object');
            });

        });

        describe('Retrieve system information', () => {

            let sysInfoAppMock = common.models.SystemInformationMock.entity();
            let sysInfoApiMock = common.models.SystemInformationMock.entity();

            it('should get system information files from both app and api', (done) => {

                $httpBackend.expectGET('/system-information.json').respond(sysInfoAppMock);
                $httpBackend.expectGET('/api/utility/system-information').respond(sysInfoApiMock);

                let systemInformationPromise = systemInformationService.getSystemInformation();
                $httpBackend.flush();

                expect(systemInformationPromise).eventually.to.be.fulfilled;

                systemInformationPromise.then((res) => {
                    expect(res['app']).to.deep.equal(sysInfoAppMock);
                    expect(res['api']).to.deep.equal(sysInfoApiMock);
                    done();
                });

            });

        });

    });

}
