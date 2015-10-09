namespace app.guest.articles.article {

    describe('Article (public)', () => {

        let article:common.models.Article = common.models.ArticleMock.entity(),
            $rootScope:global.IRootScope,
            $scope:ng.IScope,
            articleService:common.services.article.ArticleService,
            ArticleController:app.guest.articles.article.ArticleController,
            ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
            user:common.models.User = common.models.UserMock.entity(),
            $stateParams:IArticleStateParams = <IArticleStateParams> {
                permalink: 'foobar'
            };

        beforeEach(() => {

            module('app');

            inject(($controller, _$rootScope_, _articleService_, _ngJwtAuthService_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                articleService = _articleService_;
                ngJwtAuthService = _ngJwtAuthService_;

                articleService.getArticle = sinon.stub().returns(article);

                ngJwtAuthService.getUser = sinon.stub().returns(user);

                ArticleController = $controller(app.guest.articles.article.namespace + '.controller', {
                    $stateParams: $stateParams,
                    article: article,
                });
            });

        });

        it('should have an injected article', () => {

            expect(ArticleController.article).to.be.an.instanceOf(common.models.Article);

        });

        it('should be able to resolve the article', () => {

            let article = (<any>ArticleConfig.state.resolve).article(articleService, $stateParams);

            expect(articleService.getArticle).to.have.been.calledWith('foobar', ['articlePermalinks', 'articleMetas', 'tags', 'author', 'comments', 'sections']);

            expect(article).to.deep.equal(article);

        });

        it('should be able to resolve the user', () => {

            let retrievedUser = (<any>ArticleConfig.state.resolve).user(ngJwtAuthService);

            expect(ngJwtAuthService.getUser).to.have.been.called;

            expect(retrievedUser).to.deep.equal(user);

        });

    });

}