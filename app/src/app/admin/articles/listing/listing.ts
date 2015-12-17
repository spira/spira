namespace app.admin.articles.listing {

    export const namespace = 'app.admin.articles.listing';

    /* istanbul ignore next:@todo - skipping controller testing */
    export class ArticlesListingConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
                url: '/listing/{page:int}',
                params: {
                    page: 1
                },
                views: {
                    "main@app.admin": {
                        controller: namespace+'.controller',
                        controllerAs: 'ArticlesListingController',
                        templateUrl: 'templates/app/admin/articles/listing/listing.tpl.html'
                    }
                },
                resolve: /*@ngInject*/{
                    articlesPaginator: (articleService:common.services.article.ArticleService) => {
                        return articleService
                            .getPaginator()
                            .setNested(['thumbnailImage','author'])
                            .setCount(12)
                            .noResultsResolve();
                    },
                    initArticles: (articlesPaginator:common.services.pagination.Paginator, $stateParams:app.admin.ICommonListingStateParams) => {
                        return articlesPaginator.getPage($stateParams.page);
                    }
                },
                data: {
                    title: "Articles Listing",
                    icon: 'library_books',
                    navigation: true,
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    /* istanbul ignore next:@todo - skipping controller testing */
    export class ArticlesListingController extends app.admin.AbstractListingController<common.models.Article> {

        static $inject = ['articlesPaginator', 'initArticles', 'tagService', 'userService', '$stateParams', '$scope'];

    }

    angular.module(namespace, [])
        .config(ArticlesListingConfig)
        .controller(namespace+'.controller', ArticlesListingController);

}