namespace common.services.timezones {

    let seededChance = new Chance(1);
    let fixtures = {

        getTimezones() {

            let timezoneNames = moment.tz.names(),
                mapped = _.chain(timezoneNames)
                    .map((timezoneId:string) => {

                        let timezoneDefinition:ITimezoneDefinition = {
                            timezoneIdentifier: timezoneId,
                            offset:seededChance.integer({min: -720, max: 720}),
                            isDst: seededChance.bool(),
                            displayOffset: seededChance.pick(['+', '-']) + seededChance.natural({min: 0, max: 13})+seededChance.pick([':00', ':30']),
                        };

                        return timezoneDefinition;
                    })
                    .value();

            return mapped;

        }
    };

    describe('TimezonesService', () => {

        let timezonesService:TimezonesService;
        let $httpBackend:ng.IHttpBackendService;
        let ngRestAdapter:NgRestAdapter.NgRestAdapterService;

        beforeEach(()=> {

            module('app');

            inject((_$httpBackend_, _timezonesService_, _ngRestAdapter_) => {

                if (!timezonesService) { //dont rebind, so each test gets the singleton
                    $httpBackend = _$httpBackend_;
                    timezonesService = _timezonesService_;
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

                return expect(timezonesService).to.be.an('object');
            });

        });

        describe('Retrieve timezones', () => {

            beforeEach(() => {

                sinon.spy(ngRestAdapter, 'get');

            });

            afterEach(() => {
                (<any>ngRestAdapter.get).restore();
            });

            let timezones = _.clone(fixtures.getTimezones()); //get a set of countries

            it('should return all timezones', () => {

                $httpBackend.expectGET('/api/timezones').respond(timezones);

                let allTimezonesPromise = timezonesService.getAllTimezones();

                expect(allTimezonesPromise).eventually.to.be.fulfilled;
                expect(allTimezonesPromise).eventually.to.deep.equal(timezones);

                $httpBackend.flush();

            });

            it('should return all timezones from cache', () => {

                let allTimezonesPromise = timezonesService.getAllTimezones();

                expect(allTimezonesPromise).eventually.to.be.fulfilled;
                expect(allTimezonesPromise).eventually.to.deep.equal(timezones);

                expect(ngRestAdapter.get).not.to.have.been.called;

            });

        });

    });

}