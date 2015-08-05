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

        describe('Paginator', () => {

            let collection = fixtures.exampleCollection;

            it ('should default to 10 entities retrieved', () => {

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

            it('should return the second set of results when getNext() is called again', () => {


                let paginator = paginationService.getPaginatorInstance('/collection');

                // Get first page
                $httpBackend.expectGET('/api/collection', (headers) => {
                    return headers.Range == 'entities=0-9'
                })
                .respond(206, _.take(collection, 10));

                paginator.getNext();

                $httpBackend.flush();

                //Get second page
                $httpBackend.expectGET('/api/collection', (headers) => { //second request
                    return headers.Range == 'entities=10-19'
                })
                .respond(206, collection.slice(10, 20));

                let results = paginator.getNext();

                $httpBackend.flush();

                expect(results).eventually.to.be.instanceof(Array);
                expect(results).eventually.to.have.length(10);
                expect(results).eventually.to.deep.equal(collection.slice(10, 20));

            });

            it('should return the same set of results after reset() is called', () => {


                let paginator = paginationService.getPaginatorInstance('/collection');

                // Get first page
                $httpBackend.expectGET('/api/collection', (headers) => {
                    return headers.Range == 'entities=0-9'
                })
                .respond(206, _.take(collection, 10));

                let results1 = paginator.getNext();

                paginator.reset(); //reset the counter

                $httpBackend.flush();

                // Get first page again
                $httpBackend.expectGET('/api/collection', (headers) => {
                    return headers.Range == 'entities=0-9'
                })
                    .respond(206, _.take(collection, 10));

                let results2 = paginator.getNext();

                $httpBackend.flush();

                expect(results1).eventually.to.be.instanceof(Array);
                expect(results1).eventually.to.have.length(10);
                expect(results2).eventually.to.have.length(10);
                expect(results1).eventually.to.deep.equal(_.take(collection, 10));
                expect(results2).eventually.to.deep.equal(_.take(collection, 10));

            });

            it('should be able to configure the page size', () => {

                let paginator = paginationService.getPaginatorInstance('/collection').setCount(3);

                $httpBackend.expectGET('/api/collection', (headers) => { //second request
                    return headers.Range == 'entities=0-2'
                })
                    .respond(206, _.take(collection, 3));

                let results = paginator.getNext();

                $httpBackend.flush();

                expect(results).eventually.to.be.instanceof(Array);
                expect(results).eventually.to.have.length(3);
                expect(results).eventually.to.deep.equal(_.take(collection, 3));

            });

            it.skip('should be able to get a subset of results directly', () => {

                let paginator = paginationService.getPaginatorInstance('/collection').setCount(3);

                $httpBackend.expectGET('/api/collection', (headers) => { //second request
                    return headers.Range == 'entities=12-36'
                })
                .respond(206, collection.slice(12, 37));

                let results = paginator.getRange(12, 36);

                $httpBackend.flush();

                expect(results).eventually.to.be.instanceof(Array);
                expect(results).eventually.to.have.length(24);
                expect(results).eventually.to.deep.equal(collection.slice(12, 37));


            });


        });

    });

})();