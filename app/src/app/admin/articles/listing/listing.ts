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
                    allArticles: (articleService:common.services.article.ArticleService) => {
                        return articleService.getAllArticles();
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

        static $inject = ['allArticles'];
        constructor(public allArticles:common.services.article.IArticle[]) {

        }

    }

    angular.module(namespace, [])
        .config(ArticlesListingConfig)
        .controller(namespace+'.controller', ArticlesListingController);

}