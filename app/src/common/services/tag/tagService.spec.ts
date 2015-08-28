(() => {

    let seededChance = new Chance(1);
    let fixtures = {

        getTag():common.models.Tag {

            return new common.models.Tag({
                tagId: seededChance.guid(),
                tag: seededChance.word(),
            });

        },
        getTags() {

            return chance.unique(fixtures.getTag, 30);
        }
    };

    describe('Tag Service', () => {

        let tagService:common.services.tag.TagService;
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


        describe('New Tag', () => {

            it('should be able to get a new tag with a UUID', () => {

                let tag = tagService.newTag();

                expect(tag.tagId).to.be.ok;

            });

        });

        describe('Retrieve an tag paginator', () => {

            beforeEach(() => {

                sinon.spy(ngRestAdapter, 'get');

            });

            afterEach(() => {
                (<any>ngRestAdapter.get).restore();
            });

            let tags = _.clone(fixtures.getTags()); //get a set of tags

            it('should return the first set of tags', () => {

                $httpBackend.expectGET('/api/tags').respond(_.take(tags, 10));

                let tagPaginator = tagService.getTagsPaginator();

                let firstSet = tagPaginator.getNext(10);

                expect(firstSet).eventually.to.be.fulfilled;
                expect(firstSet).eventually.to.deep.equal(_.take(tags, 10));

                $httpBackend.flush();

            });


        });

        describe('Save tag', () => {

            it('should save a tag', () => {

                let tag = fixtures.getTag();

                $httpBackend.expectPUT('/api/tags/'+tag.tagId, _.clone(tag)).respond(201);

                let savePromise = tagService.saveTag(tag);


                expect(savePromise).eventually.to.be.fulfilled;
                expect(savePromise).eventually.to.deep.equal(tag);

                $httpBackend.flush();

            });


        });


    });

})();