namespace app.guest.articles.article {

    export const namespace = 'app.guest.articles.article';

    export interface IArticleStateParams extends ng.ui.IStateParamsService
    {
        permalink:string;
    }

    export class ArticleConfig {

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
                    ['header@'+namespace]: {
                        controller: namespace+'.header.controller',
                        controllerAs: 'HeaderController',
                        templateUrl: 'templates/app/guest/articles/article/header/header.tpl.html'
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
                    article: (articleService:common.services.article.ArticleService, $stateParams:IArticleStateParams):common.models.Article | ng.IPromise<common.models.Article> => {
                        return articleService.getModel($stateParams.permalink, ['permalinks', 'metas', 'tags', 'author', 'comments', 'sections']);
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

    export class ArticleController {

        static $inject = ['article', '$state'];
        constructor(
            public article:common.models.Article,
            public $state:ng.ui.IStateService
        ) {
            $state.current.data.meta = article._metas;
        }

    }

    angular.module(namespace, [
        namespace+'.body',
        namespace+'.header',
        namespace+'.comments',
    ])
        .config(ArticleConfig)
        .controller(namespace+'.controller', ArticleController);

}