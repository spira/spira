module app.admin.articles.listing {

    export const namespace = 'app.admin.articles.listing';

    export interface IArticlesListingStateParams extends ng.ui.IStateParamsService
    {
        page:number;
    }

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
                        return articleService.getArticlesPaginator().setCount(12);
                    },
                    initArticles: (articlesPaginator:common.services.pagination.Paginator, $stateParams:IArticlesListingStateParams) => {
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

    export class ArticlesListingController {

        public articles:common.models.Article[] = [];
        static $inject = ['articlesPaginator', 'initArticles', '$stateParams'];

        public pages:number[] = [];

        constructor(private articlesPaginator:common.services.pagination.Paginator, articles, public $stateParams:IArticlesListingStateParams) {

            this.articles = articles;

            let perPage = articlesPaginator.getCount();

            this.pages = _.range(1, Math.ceil(articlesPaginator.entityCountTotal/perPage) + 1);

        }
    }

    angular.module(namespace, [])
        .config(ArticlesListingConfig)
        .controller(namespace+'.controller', ArticlesListingController);

}