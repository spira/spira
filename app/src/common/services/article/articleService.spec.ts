(() => {

    let seededChance = new Chance(1);
    let fixtures = {

        getArticle():common.models.Article {

            let title = seededChance.sentence();

            return new common.models.Article({
                articleId: seededChance.guid(),
                title: title,
                body: seededChance.paragraph(),
                permalink: title.replace(' ', '-'),
                _tags: [
                    {
                        tagId: seededChance.guid(),
                        tag: seededChance.word,
                    },
                    {
                        tagId: seededChance.guid(),
                        tag: seededChance.word,
                    }
                ]
            });

        },
        getArticles() {

            return chance.unique(fixtures.getArticle, 30);
        }
    };

    describe('Article Service', () => {

        let articleService:common.services.article.ArticleService;
        let $httpBackend:ng.IHttpBackendService;
        let ngRestAdapter:NgRestAdapter.NgRestAdapterService;
        let $rootScope:ng.IRootScopeService;

        beforeEach(()=> {

            module('app');

            inject((_$httpBackend_, _articleService_, _ngRestAdapter_, _$rootScope_) => {

                if (!articleService) { //dont rebind, so each test gets the singleton
                    $httpBackend = _$httpBackend_;
                    articleService = _articleService_;
                    ngRestAdapter = _ngRestAdapter_;
                    $rootScope = _$rootScope_;
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

            let articles = _.clone(fixtures.getArticles()); //get a set of articles

            it('should return the first set of articles', () => {

                $httpBackend.expectGET('/api/articles').respond(_.take(articles, 10));

                let articlePaginator = articleService.getArticlesPaginator();

                let firstSet = articlePaginator.getNext(10);

                expect(firstSet).eventually.to.be.fulfilled;
                expect(firstSet).eventually.to.deep.equal(_.take(articles, 10));

                $httpBackend.flush();

            });


        });

        describe('Get article', () => {

            let mockArticle  = fixtures.getArticle();

            it('should be able to retrieve an article by permalink', () => {

                $httpBackend.expectGET('/api/articles/'+mockArticle.permalink).respond(mockArticle);

                let article = articleService.getArticle(mockArticle.permalink);

                expect(article).eventually.to.be.fulfilled;
                expect(article).eventually.to.deep.equal(mockArticle);

                $httpBackend.flush();

            });

        });

        describe('New Article', () => {

            it('should be able to get a new article with a UUID', () => {

                let article = articleService.newArticle();

                expect(article.articleId).to.be.ok;

            });

        });

        describe('Save Article', () => {


            it('should save a new article and all related entities', () => {

                let article = fixtures.getArticle();

                $httpBackend.expectPUT('/api/articles/'+article.articleId, article.getAttributes()).respond(201);
                $httpBackend.expectPUT('/api/articles/'+article.articleId+'/tags', _.clone(article._tags, true)).respond(201);

                let savePromise = articleService.saveArticleWithRelated(article);

                expect(savePromise).eventually.to.be.fulfilled;
                expect(savePromise).eventually.to.deep.equal(article);

                $httpBackend.flush();

            });

        });


    });

})();