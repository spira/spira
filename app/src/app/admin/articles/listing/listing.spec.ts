namespace app.admin.articles.listing {

    describe('Admin - Articles Listing', () => {

        let articles:common.models.Article[],
            $rootScope:global.IRootScope,
            $scope:ng.IScope,
            $stateParams:IArticlesListingStateParams = {
                page:1
            },
            articleService:common.services.article.ArticleService,
            articlesPaginator:common.services.pagination.Paginator,
            ArticlesListingController:ArticlesListingController;

        beforeEach(() => {

            module('app');

            inject(($controller, _$rootScope_, _articleService_) => {
                $rootScope = _$rootScope_;
                $scope = $rootScope.$new();
                articleService = _articleService_;

                // Setup articlesPaginator before injection
                articlesPaginator = articleService.getArticlesPaginator().setCount(10);

                ArticlesListingController = $controller(app.admin.articles.listing.namespace + '.controller', {
                    articlesPaginator: articlesPaginator,
                    initArticles: articles,
                    $stateParams: $stateParams
                });
            });

        });

        // Temporary until the controller is fleshed out more
        it('should perform one test and pass it', () => {

            expect(true).to.be.true;

        });

    });

}