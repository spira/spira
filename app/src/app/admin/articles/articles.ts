module app.admin.articles {

    export const namespace = 'app.admin.articles';

    export class ArticlesConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
                url: '/articles',
                views: {
                    "main@app.admin": {
                        controller: namespace+'.controller',
                        controllerAs: 'ArticlesController',
                        templateUrl: 'templates/app/admin/articles/articles.tpl.html'
                    }
                },
                resolve: /*@ngInject*/{
                    allArticles: (articleService:common.services.article.ArticleService) => {
                        return articleService.getAllArticles();
                    }
                },
                data: {
                    title: "Articles",
                    icon: 'description',
                    navigation: true,
                    sortAfter: app.admin.dashboard.namespace,
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    export class ArticlesController {

        static $inject = ['allArticles'];
        constructor(public allArticles:common.services.article.IArticle[]) {

        }

    }

    angular.module(namespace, [])
        .config(ArticlesConfig)
        .controller(namespace+'.controller', ArticlesController);

}