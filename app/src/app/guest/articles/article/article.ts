namespace app.guest.articles.article {

    export const namespace = 'app.guest.articles.article';

    export interface IArticleStateParams extends ng.ui.IStateParamsService
    {
        permalink:string;
    }

    class ArticleConfig {

        public static state:global.IState;

        static $inject = ['stateHelperServiceProvider'];

        constructor(private stateHelperServiceProvider){

            ArticleConfig.state = {
                url: '/article/{permalink}',
                views: {
                    'main@app.guest': {
                        controller: namespace+'.controller',
                        templateUrl: 'templates/app/guest/articles/article/article.tpl.html'
                    },
                    ['body@'+namespace]: {
                        controller: namespace+'.body.controller',
                        controllerAs: 'BodyController',
                        templateUrl: 'templates/app/guest/articles/article/body/body.tpl.html'
                    },
                    ['comments@'+namespace]: {
                        controller: namespace+'.comments.controller',
                        controllerAs: 'CommentsController',
                        templateUrl: 'templates/app/guest/articles/article/comments/comments.tpl.html'
                    }
                },
                resolve: /*@ngInject*/{
                    article: ($stateParams:IArticleStateParams, articleService:common.services.article.ArticleService):common.models.Article | ng.IPromise<common.models.Article> => {

                        return articleService.getArticle($stateParams.permalink, ['articlePermalinks', 'articleMetas', 'tags', 'author', 'comments']);
                    },
                    user: (ngJwtAuthService:NgJwtAuth.NgJwtAuthService):common.models.User => {
                        return <common.models.User>ngJwtAuthService.getUser()
                    }
                },
                data: {
                    title: "Article",
                    role: 'public'
                }
            };

            stateHelperServiceProvider.addState(namespace, ArticleConfig.state);

        }

    }

    class ArticleController {

        static $inject = ['article'];
        constructor(
            public article:common.models.Article
        ) {
        }

    }

    angular.module(namespace, [
        namespace+'.body',
        namespace+'.comments',
    ])
        .config(ArticleConfig)
        .controller(namespace+'.controller', ArticleController);

}