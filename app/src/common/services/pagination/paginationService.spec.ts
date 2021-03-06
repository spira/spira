namespace common.services.pagination {

    interface mockEntity {
        id:number;
        body:string
    }

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

        let paginationService:common.services.pagination.PaginationService,
            $httpBackend:ng.IHttpBackendService,
            ngRestAdapter:NgRestAdapter.NgRestAdapterService,
            $rootScope:ng.IRootScopeService,
            mockShowError = sinon.stub();

        beforeEach(()=> {


            module('app');

            module(($provide:ng.auto.IProvideService) => {
                $provide.factory('errorService', () => {
                    return {
                        showError: mockShowError
                    }
                });
            });

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

                expect(paginationService).to.be.an('object');

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

            it('should be able to get the current count', () => {

                let paginator = paginationService.getPaginatorInstance('/collection').setCount(3);

                expect(paginator.getCount()).to.equal(3);

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

            it('should be able to retrieve results by page', () => {

                let paginator = paginationService.getPaginatorInstance('/collection').setCount(10);

                $httpBackend.expectGET('/api/collection', (headers) => { //second request
                    return headers.Range == 'entities=10-19'
                })
                    .respond(206, collection.slice(10, 20));

                let results = paginator.getPage(2);

                $httpBackend.flush();

                expect(results).eventually.to.be.instanceof(Array);
                expect(results).eventually.to.have.length(10);
                expect(results).eventually.to.deep.equal(collection.slice(10, 20));


            });

            it('should be able to define a custom model factory', () => {

                function Foo(data){
                    _.assign(this, data);
                }

                let modelFactory = (data:any) => new Foo(data);

                let paginator = paginationService.getPaginatorInstance('/collection').setModelFactory(modelFactory);

                $httpBackend.expectGET('/api/collection').respond(206, _.take(collection, 10));

                let results = paginator.getNext();

                results.then((items) => {
                    expect(_.first(items)).to.be.instanceOf(Foo);
                });

                $httpBackend.flush();

                expect(results).eventually.to.be.instanceOf(Array);


            });

            it('should redirect to the error handler if a fatal response comes from the api', () => {

                let paginator = paginationService.getPaginatorInstance('/fatal');

                $httpBackend.expectGET('/api/fatal').respond(500);

                let results = paginator.getNext();

                $httpBackend.flush();

                $rootScope.$apply();

                expect(mockShowError).to.have.been.called;

                mockShowError.reset();

            });

            it('should NOT redirect to the error handler if a recoverable 416 Requested Range Not Satisfiable response comes from the api', () => {

                let paginator = paginationService.getPaginatorInstance('/no-more-results');

                $httpBackend.expectGET('/api/no-more-results').respond(416);

                let results = paginator.getNext();

                $httpBackend.flush();

                expect(mockShowError).not.to.have.been.called;

                return expect(results).eventually.to.be.rejectedWith(common.services.pagination.PaginatorException);

            });

            it('should be able to configure pagination instance to not reject promise when no results are available', () => {

                let paginator = paginationService.getPaginatorInstance('/no-more-results').noResultsResolve();

                $httpBackend.expectGET('/api/no-more-results').respond(416);

                let results = paginator.getNext();

                $httpBackend.flush();
                $rootScope.$apply();

                expect(mockShowError).not.to.have.been.called;

                expect(results).eventually.to.be.instanceOf(Array);
                return expect(results).eventually.to.have.length(0);
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

                return expect(retrievedResponses).to.have.length(31);

            });

            it('should throw exception when an invalid `Content-Range` header is passed', () => {

                let valid = [
                    'entities 10-19/50',
                    'entities 10-19/*',
                ];

                let invalid = [
                    'erghewar',
                    'entities=10/*',
                    'entities 10-19',
                ];

                let validFns = _.map(valid, (headerString:string) => () => common.services.pagination.Paginator.parseContentRangeHeader(headerString));
                let invalidFns = _.map(invalid, (headerString:string) => () => common.services.pagination.Paginator.parseContentRangeHeader(headerString));

                _.each(validFns, (validFn) => {

                    expect(validFn).not.to.throw(common.services.pagination.PaginatorException);
                });

                _.each(invalidFns, (invalidFn) => {

                    expect(invalidFn).to.throw(common.services.pagination.PaginatorException);
                });

            });

            it('should be able to return an array of how many pages of results there are', () => {

                let paginator = paginationService.getPaginatorInstance('/collection').setCount(9);

                $httpBackend.expectGET('/api/collection', (headers) => {
                    return headers.Range == 'entities=9-17'
                })
                    .respond(206, collection.slice(9, 17), {
                        'Content-Range' : 'entities 9-17/' + collection.length
                    });

                let results = paginator.getPage(2);

                $httpBackend.flush();

                let pages = paginator.getPages();

                expect(pages).to.have.length(12);

            });

            it('should be able set nested entities', () => {

                $httpBackend.expectGET('/api/collection', (headers) => {
                    return headers.Range == 'entities=0-9' && headers['With-Nested'] == 'foo, bar';
                })
                    .respond(206, _.take(collection, 10), {
                        'With-Nested' : 'foo, bar'
                    });

                let paginator = paginationService.getPaginatorInstance('/collection').setNested(['foo', 'bar']);

                let results = paginator.getNext();

                $httpBackend.flush();

            });

            describe('Query', () => {

                it('should be able to query the results to filter them', () => {

                    let paginator = paginationService.getPaginatorInstance('/collection').setCount(3);

                    $httpBackend.expectGET('/api/collection?q=' + btoa('foo@bar.com'), (headers) => {
                        return headers.Range == 'entities=0-2'
                    })
                        .respond(206, _.take(collection, 3));

                    let results = paginator.query('foo@bar.com');

                    $httpBackend.flush();

                    expect(results).eventually.to.be.fulfilled;

                });

                it('should be able to complex query the results to filter them', () => {

                    let paginator = paginationService.getPaginatorInstance('/collection').setCount(3);

                    let query = {
                        _all:'foobar',
                        entityId:'foobarId'
                    };

                    $httpBackend.expectGET('/api/collection?q=' + btoa(angular.toJson(query)), (headers) => {
                        return headers.Range == 'entities=0-2'
                    })
                        .respond(206, _.take(collection, 3));

                    let results = paginator.complexQuery(query);

                    $httpBackend.flush();

                    expect(results).eventually.to.be.fulfilled;

                });

                it('should be able to query with an empty string', () => {

                    let paginator = paginationService.getPaginatorInstance('/collection').setCount(3);

                    $httpBackend.expectGET('/api/collection', (headers) => {
                        return headers.Range == 'entities=0-2'
                    })
                        .respond(206, _.take(collection, 3));

                    let results = paginator.query('');

                    $httpBackend.flush();

                    expect(results).eventually.to.be.fulfilled;

                });

                it('should reset the count when there are no search results', () => {

                    let paginator = paginationService.getPaginatorInstance('/collection').setCount(3);

                    $httpBackend.expectGET('/api/collection?q=' + btoa('findnothing'), (headers) => {
                        return headers.Range == 'entities=0-2'
                    })
                        .respond(404);

                    let results = paginator.query('findnothing');

                    $httpBackend.flush();

                    expect(results).eventually.to.be.rejectedWith(common.services.pagination.PaginatorException);

                });

            });

            describe('Similar Entities', () => {

                it('should be able to get similar entities', () => {

                    let entityId = seededChance.guid();

                    let paginator = paginationService.getPaginatorInstance('/collection').setCount(3);

                    $httpBackend.expectGET('/api/collection/' + entityId + '/similar', (headers) => {
                        return headers.Range == 'entities=0-2'
                    })
                        .respond(206, _.take(collection, 3));

                    let results = paginator.getSimilar(entityId);

                    $httpBackend.flush();

                    expect(results).eventually.to.be.fulfilled;

                });

            });

            describe('Caching', () => {

                it('should be able to cache a paginator request', () => {

                    let paginator = paginationService.getPaginatorInstance('/collection').setCount(3).cacheRequests();

                    $httpBackend.expectGET('/api/collection', (headers) => {
                        return headers.Range == 'entities=0-2'
                    })
                        .respond(206, _.take(collection, 3));

                    let results = paginator.query('');

                    $httpBackend.flush();

                    expect(results).eventually.to.be.fulfilled;


                    let results2 = paginator.query('');
                    expect(results2).eventually.to.be.fulfilled;

                });

                it('should be able to cache a paginator request with separate instances of paginator', () => {

                    let paginator = paginationService.getPaginatorInstance('/collection').setCount(3).cacheRequests();

                    $httpBackend.expectGET('/api/collection', (headers) => {
                        return headers.Range == 'entities=0-2'
                    })
                        .respond(206, _.take(collection, 3));

                    let results = paginator.query('');

                    $httpBackend.flush();

                    expect(results).eventually.to.be.fulfilled;

                    let paginator2 = paginationService.getPaginatorInstance('/collection').setCount(3).cacheRequests();
                    let results2 = paginator2.query('');
                    expect(results2).eventually.to.be.fulfilled;

                });

                it('should be able to clear a cached paginator request', () => {

                    let paginator = paginationService.getPaginatorInstance('/collection').setCount(3).cacheRequests();

                    $httpBackend.expectGET('/api/collection', (headers) => {
                        return headers.Range == 'entities=0-2'
                    })
                        .respond(206, _.take(collection, 3));

                    let results = paginator.query('');

                    $httpBackend.flush();

                    expect(results).eventually.to.be.fulfilled;

                    paginator.bustCache();

                    $httpBackend.expectGET('/api/collection', (headers) => {
                        return headers.Range == 'entities=0-2'
                    })
                        .respond(206, _.take(collection, 3));
                    let results2 = paginator.query('');

                    $httpBackend.flush();

                    expect(results2).eventually.to.be.fulfilled;

                });

            });

        });

    });

}