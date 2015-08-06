module app.guest.articles {

    export const namespace = 'app.guest.articles';

    class ArticlesConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
                url: '/articles',
                views: {
                    "main@app.guest": {
                        controller: namespace+'.controller',
                        controllerAs: 'ArticlesController',
                        templateUrl: 'templates/app/guest/articles/articles.tpl.html'
                    }
                },
                resolve: /*@ngInject*/{
                    allArticles: (articleService:common.services.article.ArticleService) => {
                        return articleService.getAllArticles();
                    }
                },
                data: {
                    title: "Articles",
                    role: 'guest',
                    icon: 'content_paste',
                    navigation: true,
                    sortAfter: app.guest.home.namespace,
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

    angular.module(namespace, [
            'app.guest.articles.post'
        ])
        .config(ArticlesConfig)
        .controller(namespace+'.controller', ArticlesController);

}
