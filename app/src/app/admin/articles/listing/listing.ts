module app.admin.articles.listing {

    export const namespace = 'app.admin.articles.listing';

    export class ArticlesListingConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
                url: '/listing',
                views: {
                    "main@app.admin": {
                        controller: namespace+'.controller',
                        controllerAs: 'ArticlesListingController',
                        templateUrl: 'templates/app/admin/articles/listing/listing.tpl.html'
                    }
                },
                resolve: /*@ngInject*/{
                    articlesPaginator: (articleService:common.services.article.ArticleService) => {
                        return articleService.getArticlesPaginator();
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

        public allArticles:common.models.Article[] = [];
        static $inject = ['articlesPaginator'];
        constructor(private articlesPaginator:common.services.pagination.Paginator) {

            articlesPaginator.getNext().then((articles) => {
                this.allArticles = articles;
            });

        }
    }

    angular.module(namespace, [])
        .config(ArticlesListingConfig)
        .controller(namespace+'.controller', ArticlesListingController);

}