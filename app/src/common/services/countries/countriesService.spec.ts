namespace common.services.countries {

    let seededChance = new Chance(1);
    let fixtures = {

        getCountries() {

            let countries = (<any>seededChance).countries(),
                mapped = _.chain(countries)
                    .map((country:{abbreviation:string, name:string}) => {
                        return {
                            countryCode: country.abbreviation,
                            countryName: country.name,
                        };
                    })
                    .value();

            return mapped;

        }
    };

    describe('CountriesService', () => {

        let countriesService:CountriesService;
        let $httpBackend:ng.IHttpBackendService;
        let ngRestAdapter:NgRestAdapter.NgRestAdapterService;

        beforeEach(()=> {

            module('app');

            inject((_$httpBackend_, _countriesService_, _ngRestAdapter_) => {

                if (!countriesService) { //dont rebind, so each test gets the singleton
                    $httpBackend = _$httpBackend_;
                    countriesService = _countriesService_;
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

                return expect(countriesService).to.be.an('object');
            });

        });

        describe('Retrieve countries', () => {

            beforeEach(() => {

                sinon.spy(ngRestAdapter, 'get');

            });

            afterEach(() => {
                (<any>ngRestAdapter.get).restore();
            });

            let countries = _.clone(fixtures.getCountries()); //get a set of countries

            it('should return all countries', () => {

                $httpBackend.expectGET('/api/countries').respond(countries);

                let allCountriesPromise = countriesService.getAllCountries();

                expect(allCountriesPromise).eventually.to.be.fulfilled;
                expect(allCountriesPromise).eventually.to.deep.equal(countries);

                $httpBackend.flush();

            });

            it('should return all countries from cache', () => {

                let allCountriesPromise = countriesService.getAllCountries();

                expect(allCountriesPromise).eventually.to.be.fulfilled;
                expect(allCountriesPromise).eventually.to.deep.equal(countries);

                expect(ngRestAdapter.get).not.to.have.been.called;

            });

        });

    });

}