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

    describe('PaginationService', () => {

        let paginationService:common.services.pagination.PaginationService;
        let $httpBackend:ng.IHttpBackendService;
        let ngRestAdapter:NgRestAdapter.NgRestAdapterService;
        let $rootScope:ng.IRootScopeService;

        beforeEach(()=> {

            module('app');

            inject((_$httpBackend_, _paginationService_, _ngRestAdapter_, _$rootScope_) => {

                $httpBackend = _$httpBackend_;
                paginationService = _paginationService_;
                ngRestAdapter = _ngRestAdapter_;
                $rootScope = _$rootScope_;

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

        describe.only('Paginator', () => {

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

            it('should be able to get a subset of results directly', () => {

                let paginator = paginationService.getPaginatorInstance('/collection').setCount(3);

                $httpBackend.expectGET('/api/collection', (headers) => { //second request
                    return headers.Range == 'entities=5-24'
                })
                .respond(206, collection.slice(5, 25));

                let results = paginator.getRange(5, 24);

                $httpBackend.flush();

                expect(results).eventually.to.be.instanceof(Array);
                expect(results).eventually.to.have.length(20);
                expect(results).eventually.to.deep.equal(collection.slice(5, 25));


            });

            it('should redirect to the error handler if a fatal response comes from the api', () => {

                let paginator = paginationService.getPaginatorInstance('/fatal');

                $httpBackend.expectGET('/api/fatal').respond(500);

                let results = paginator.getNext();

                sinon.spy($rootScope, '$broadcast');

                $httpBackend.flush();

                $rootScope.$apply();

                expect($rootScope.$broadcast).to.have.been.calledWith('apiErrorHandler', "Redirecting to error page");

                (<any>$rootScope).$broadcast.restore();

            });

            it('should NOT redirect to the error handler if a recoverable 416 Requested Range Not Satisfiable response comes from the api', () => {

                let paginator = paginationService.getPaginatorInstance('/no-more-results');

                $httpBackend.expectGET('/api/no-more-results').respond(416);

                let results = paginator.getNext();

                sinon.spy($rootScope, '$broadcast');

                $httpBackend.flush();

                $rootScope.$apply();

                expect($rootScope.$broadcast).not.to.have.been.calledWith('apiErrorHandler');
                expect(results).eventually.to.be.rejectedWith(common.services.pagination.PaginatorException);

                (<any>$rootScope).$broadcast.restore();

            });

            it('should not attempt to retrieve from the api when the all items are retrieved', () => {

                let collection = _.clone(fixtures.getExampleCollection(31));
                let responses = _.chunk(collection, 5);

                let paginator = paginationService.getPaginatorInstance('/collection').setCount(5);


                let retrievedResponses = [];

                _.each(responses, (response:{id:number}[]) => {

                    let requestRangeHeader = 'entities=' + _.first(response).id + '-' + _.last(response).id;
                    let responseRangeHeader = requestRangeHeader.replace('=', ' ') + '/' + collection.length;

                    $httpBackend.expectGET('/api/collection', (headers) => { //second request
                        return headers.Range == requestRangeHeader
                    })
                    .respond(206, response, {
                        'Content-Range' : responseRangeHeader
                    });

                    paginator.getNext().then((res) => {
                        retrievedResponses = retrievedResponses.concat(res)
                    });

                    $httpBackend.flush();

                    //$rootScope.$apply();

                });

                _.times(3, () => { //iterate more than required to verify no outstanding requests are generated
                    paginator.getNext();
                });

                expect(retrievedResponses).to.be.instanceof(Array);
                expect(retrievedResponses).to.have.length(31);

            });


        });

    });

})();