namespace common.services.article {

    describe('Article Service', () => {

        let articleService:ArticleService;
        let $httpBackend:ng.IHttpBackendService;
        let ngRestAdapter:NgRestAdapter.NgRestAdapterService;
        let $rootScope:ng.IRootScopeService;
        let $q:ng.IQService;

        beforeEach(()=> {

            module('app');

            inject((_$httpBackend_, _articleService_, _ngRestAdapter_, _$rootScope_, _$q_) => {

                if (!articleService) { //dont rebind, so each test gets the singleton
                    $httpBackend = _$httpBackend_;
                    articleService = _articleService_;
                    ngRestAdapter = _ngRestAdapter_;
                    $rootScope = _$rootScope_;
                    $q = _$q_;
                }
            });

        });

        afterEach(() => {
            $httpBackend.verifyNoOutstandingExpectation();
            $httpBackend.verifyNoOutstandingRequest();
        });

        describe('Initialisation', () => {

            it('should be an injectable service', () => {

                return expect(articleService).to.be.an('object');
            });

        });

        describe('Retrieve an article paginator', () => {

            beforeEach(() => {

                sinon.spy(ngRestAdapter, 'get');

            });

            afterEach(() => {
                (<any>ngRestAdapter.get).restore();
            });

            let articles = common.models.ArticleMock.collection(30); //get a set of articles

            it('should return the first set of articles', () => {

                $httpBackend.expectGET('/api/articles').respond(_.take(articles, 10));

                let articlePaginator = articleService.getPaginator();

                let firstSet = articlePaginator.getNext(10);

                expect(firstSet).eventually.to.be.fulfilled;
                expect(firstSet).eventually.to.deep.equal(_.take(articles, 10));

                $httpBackend.flush();

            });


        });

        describe('Get article', () => {

            let mockArticle  = common.models.ArticleMock.entity();

            it('should be able to retrieve an article by permalink', () => {

                $httpBackend.expectGET('/api/articles/'+mockArticle.permalink, (headers) => {
                    return headers['With-Nested'] == 'articlePermalinks, articleMetas, tags, author'
                }).respond(mockArticle);

                let article = articleService.getArticle(mockArticle.permalink, ['articlePermalinks', 'articleMetas', 'tags', 'author']);

                expect(article).eventually.to.be.fulfilled;
                expect(article).eventually.to.deep.equal(mockArticle);

                $httpBackend.flush();

            });

        });

        describe('New Article', () => {

            it('should be able to get a new article with a UUID', () => {

                let author = common.models.UserMock.entity();

                let article = articleService.newArticle(author);

                expect(article.articleId).to.be.ok;

                expect(article.authorId).to.equal(author.userId);

                expect(article._author).to.deep.equal(author);

            });

        });

        describe('New Comment', () => {

            it('should be able to save a new comment', () => {

                let article = common.models.ArticleMock.entity();

                let comment = common.models.ArticleCommentMock.entity();

                $httpBackend.expectPOST('/api/articles/' + article.articleId + '/comments').respond(201);

                let savePromise = articleService.saveComment(article, comment);

                expect(savePromise).eventually.to.be.fulfilled;

                expect(savePromise).eventually.to.deep.equal(comment);

                $httpBackend.flush();

            });

        });

        describe('Save Article', () => {

            it('should save a new article and all related entities', () => {

                let article = common.models.ArticleMock.entity();
                article.setExists(false);
                article._tags.push(common.models.TagMock.entity());
                article._tags.push(common.models.TagMock.entity());
                article._articleMetas = article._articleMetas.concat(common.models.ArticleMetaMock.collection(2, {articleId:article.articleId}));
                article._sections = common.models.SectionMock.collection(2, {}, false);

                $httpBackend.expectPUT('/api/articles/'+article.articleId, article.getAttributes()).respond(201);
                $httpBackend.expectPUT('/api/articles/'+article.articleId+'/sections', _.clone(article._sections, true)).respond(201);
                $httpBackend.expectPUT('/api/articles/'+article.articleId+'/tags', _.clone(article._tags, true)).respond(201);
                $httpBackend.expectPUT('/api/articles/'+article.articleId+'/meta', _.filter(_.clone(article._articleMetas, true), (item) => {
                    return !_.isEmpty(item.metaContent)
                })).respond(201);

                let savePromise = articleService.saveArticleWithRelated(article);

                expect(savePromise).eventually.to.be.fulfilled;
                expect(savePromise).eventually.to.deep.equal(article);

                $httpBackend.flush();

            });

            it('should save an existing article with a patch request', () => {

                let article = common.models.ArticleMock.entity();
                article.setExists(true);

                article.title = "This title has been updated";

                article._tags = [common.models.TagMock.entity()];

                $httpBackend.expectPATCH('/api/articles/'+article.articleId, (<common.decorators.IChangeAwareDecorator>article).getChanged()).respond(201);
                $httpBackend.expectPUT('/api/articles/'+article.articleId+'/tags', _.clone(article._tags, true)).respond(201);

                let savePromise = articleService.saveArticleWithRelated(article);

                expect(savePromise).eventually.to.be.fulfilled;
                expect(savePromise).eventually.to.deep.equal(article);

                $httpBackend.flush();

            });

            it('should not make an api call if nothing has changed', () => {

                let article = common.models.ArticleMock.entity();
                article.setExists(true);

                let savePromise = articleService.saveArticleWithRelated(article);

                expect(savePromise).eventually.to.equal(article);

            });

            describe('queued save functions', () => {

                it('should be able to queue a function (delete section) to be run when save is called', () => {

                    let article = common.models.ArticleMock.entity({
                        _sections: common.models.SectionMock.collection(2, {}, true),
                    }, true);

                    let queuedSaveFunction = () => {
                        return articleService.deleteSection(article, article._sections[0]);
                    };

                    articleService.addQueuedSaveProcessFunction(queuedSaveFunction);

                    $httpBackend.expectDELETE('/api/articles/'+article.articleId+'/sections/'+article._sections[0].sectionId).respond(201);

                    let savePromise = articleService.saveArticleWithRelated(article);

                    expect(savePromise).eventually.to.equal(article);


                    $httpBackend.flush();

                });

                it('should be able to clear the queued save functions', () => {

                    let queuedSaveFunction = ():ng.IPromise<boolean> => {
                        return $q.when(true);
                    };

                    articleService.addQueuedSaveProcessFunction(queuedSaveFunction);

                    expect((<any>articleService).queuedSaveProcessFunctions).to.have.length(1);

                    articleService.dumpQueueSaveFunctions();

                    expect((<any>articleService).queuedSaveProcessFunctions).to.be.empty;
                });

            });

        });

    });

}