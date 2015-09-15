namespace common.services.region {

    describe('Region Service', () => {

        let regionService:common.services.region.RegionService;
        let $httpBackend:ng.IHttpBackendService;
        let ngRestAdapter:NgRestAdapter.NgRestAdapterService;
        let $rootScope:ng.IRootScopeService;

        beforeEach(()=> {

            module('app');

            inject((_$httpBackend_, _regionService_, _ngRestAdapter_, _$rootScope_) => {
                $httpBackend = _$httpBackend_;
                regionService = _regionService_;
                ngRestAdapter = _ngRestAdapter_;
                $rootScope = _$rootScope_;
            });

            sinon.stub((<any>regionService).$state, 'go');

        });

        afterEach(() => {
            $httpBackend.verifyNoOutstandingExpectation();
            $httpBackend.verifyNoOutstandingRequest();

            (<any>regionService).$state.go.restore();

        });

        describe('Initialisation', () => {

            it('should be an injectable service', () => {

                return expect(regionService).to.be.an('object');
            });

        });

        describe('Utility functions', () => {

            it('should be able to return a region when given a code', () => {

                let region = regionService.getRegionByCode('au');

                expect(region).to.be.instanceOf(Object);
                return expect(region.code).to.equal('au');
            });

        });

        describe('Set a region', () => {

            it('should not have a region initially', () => {

                return expect(regionService.currentRegion).to.be.null;

            });

            it('should be able to set a user selected region', () => {

                let chosenRegion = regionService.getRegionByCode('au');

                regionService.setRegion(chosenRegion);

                expect(regionService.currentRegion).to.deep.equal(chosenRegion);

            });

            it('should update the $state when the region is changed', () => {

                let newRegion = regionService.getRegionByCode('us');

                regionService.setRegion(newRegion);

                expect((<any>regionService).$state.go).to.have.been.calledWith('.', {region: newRegion.code});

            });

        });

        describe('Set and intercept Region headers', () => {

            it('should not send a region header when there is no chosen region', () => {

                regionService.currentRegion = null; //clear the current setting

                $httpBackend.expectGET('/api/any', (headers:Object) => {
                    return !_.has(headers, 'Accept-Region');
                }).respond(200);

                let responsePromise = ngRestAdapter.get('/any');

                $httpBackend.flush();

                expect(responsePromise).eventually.to.be.fulfilled;

            });

            it('should send an Accept-Region header when a region has been chosen', () => {

                regionService.setRegion(regionService.getRegionByCode('au'));

                $httpBackend.expectGET('/api/any', (headers:Object) => {
                    return _.has(headers, 'Accept-Region') && headers['Accept-Region'] === 'au';
                }).respond(200);

                let responsePromise = ngRestAdapter.get('/any');

                $httpBackend.flush();

                expect(responsePromise).eventually.to.be.fulfilled;

            });

            it('should set the region when an api call responds with a Content-Region header', () => {

                regionService.currentRegion = null; //clear the current setting

                $httpBackend.expectGET('/api/any').respond(200, '', {
                    'Content-Region': 'us'
                });

                ngRestAdapter.get('/any');

                $httpBackend.flush();

                expect(regionService.currentRegion).not.to.be.null;
                expect(regionService.currentRegion.code).to.equal('us');

            });

        });

    });

}