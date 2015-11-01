namespace common.services.tag {

    describe('Tag Service', () => {

        let tagService:TagService;
        let $httpBackend:ng.IHttpBackendService;
        let ngRestAdapter:NgRestAdapter.NgRestAdapterService;

        beforeEach(()=> {

            module('app');

            inject((_$httpBackend_, _tagService_, _ngRestAdapter_) => {

                if (!tagService) { //dont rebind, so each test gets the singleton
                    $httpBackend = _$httpBackend_;
                    tagService = _tagService_;
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

                return expect(tagService).to.be.an('object');
            });

        });


        describe('Tag CRUD', () => {

            it('should be able to get a new tag with a UUID', () => {

                let tag = tagService.newTag();

                expect(tag.tagId).to.be.ok;

            });

            it('should be able to save a tag', () => {

                let tag = common.models.TagMock.entity();

                $httpBackend.expectPUT('/api/tags/'+tag.tagId, _.clone(tag)).respond(201);

                let savePromise = tagService.saveTag(tag);


                expect(savePromise).eventually.to.be.fulfilled;
                expect(savePromise).eventually.to.deep.equal(tag);

                $httpBackend.flush();

            });

            it('should be able to get a collection of group tags', () => {

                sinon.spy(ngRestAdapter, 'get');

                let tags = common.models.TagMock.collection(3);

                $httpBackend.expectGET('/api/entity/tag-categories').respond(tags);

                let mockApiService = {
                    apiEndpoint: sinon.mock().returns('/entity'),
                };

                let groupTags = tagService.getTagCategories(<any>mockApiService);

                expect(groupTags).eventually.to.be.fulfilled;

                expect(groupTags).eventually.to.deep.equal(tags);

                $httpBackend.flush();

                (<any>ngRestAdapter.get).restore();

            });

        });

        describe('Tag Paginator', () => {

            it('should return the first set of tags', () => {

                sinon.spy(ngRestAdapter, 'get');

                let tags = common.models.TagMock.collection(20);

                $httpBackend.expectGET('/api/tags').respond(_.take(tags, 10));

                let tagPaginator = tagService.getPaginator();

                let firstSet = tagPaginator.getNext(10);

                expect(firstSet).eventually.to.be.fulfilled;
                expect(firstSet).eventually.to.deep.equal(_.take(tags, 10));

                $httpBackend.flush();

                (<any>ngRestAdapter.get).restore();

            });


        });

    });

}