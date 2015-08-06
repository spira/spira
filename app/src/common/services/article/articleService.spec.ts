(() => {

    let seededChance = new Chance(1);
    let fixtures = {

        getArticle():common.services.article.IArticle {

            let title = seededChance.sentence();

            return {
                title: title,
                body: seededChance.paragraph(),
                permalink: title.replace(' ', ''),
            };

        },
        getArticles() {

            return chance.unique(fixtures.getArticle, 30);

        }
    };

    describe('Article Service', () => {

        let articleService:common.services.article.ArticleService;
        let $httpBackend:ng.IHttpBackendService;
        let ngRestAdapter:NgRestAdapter.NgRestAdapterService;

        beforeEach(()=> {

            module('app');

            inject((_$httpBackend_, _articleService_, _ngRestAdapter_) => {

                if (!articleService) { //dont rebind, so each test gets the singleton
                    $httpBackend = _$httpBackend_;
                    articleService = _articleService_;
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

                return expect(articleService).to.be.an('object');
            });

        });

        describe('Retrieve all articles', () => {

            beforeEach(() => {

                sinon.spy(ngRestAdapter, 'get');

            });

            afterEach(() => {
                (<any>ngRestAdapter.get).restore();
            });

            let articles = _.clone(fixtures.getArticles()); //get a set of articles

            it('should return all articles', () => {

                $httpBackend.expectGET('/api/articles').respond(articles);

                let allArticlePromise = articleService.getAllArticles();

                expect(allArticlePromise).eventually.to.be.fulfilled;
                expect(allArticlePromise).eventually.to.deep.equal(articles);

                $httpBackend.flush();

            });


        });

    });

})();