interface mockEntity {
    id:number;
    body:string
}

(() => {

    let seededChance = new Chance(1);
    let fixtures = {

        getExampleModel(id:number):mockEntity{

            return {
                id:id,
                body: seededChance.string(),
            };
        },

        getExampleCollection(count:number):mockEntity[] {
            return _.range(count).map((id) => fixtures.getExampleModel(id))
        },

        get exampleCollection(): mockEntity[]{
            return fixtures.getExampleCollection(100);
        }

    };

    describe.only('PaginationService', () => {

        let paginationService:common.services.pagination.PaginationService;
        let $httpBackend:ng.IHttpBackendService;
        let ngRestAdapter:NgRestAdapter.NgRestAdapterService;

        beforeEach(()=> {

            module('app');

            inject((_$httpBackend_, _paginationService_, _ngRestAdapter_) => {

                if (!paginationService) { //dont rebind, so each test gets the singleton
                    $httpBackend = _$httpBackend_;
                    paginationService = _paginationService_;
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

                return expect(paginationService).to.be.an('object');
            });

        });

        describe('Default paginator', () => {

            it ('should default to 10 entities retrieved', () => {


                let collection = fixtures.exampleCollection;

                $httpBackend.expectGET('/api/collection', (headers) => {
                    return headers.Range == 'entities=0-9'
                })
                    .respond(206, _.take(collection, 10));

                let paginator = paginationService.getPaginatorInstance('/collection');

                let results = paginator.getNext();

                $httpBackend.flush();

                expect(results).eventually.to.be.instanceof(Array);
                expect(results).eventually.to.have.length(10);
                expect(results).eventually.to.deep.equal(_.take(collection, 10));

            });


        });

    });

})();