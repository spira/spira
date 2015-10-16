namespace app.admin.articles.article {

    export const namespace = 'app.admin.articles.article';

    export interface IArticleStateParams extends ng.ui.IStateParamsService
    {
        permalink:string;
        newArticle:boolean;
    }

    export class ArticleConfig {

        public static state:global.IState;

        static $inject = ['stateHelperServiceProvider'];

        constructor(private stateHelperServiceProvider){

            ArticleConfig.state = {
                url: '/article/{permalink}',
                params: {
                    newArticle: false,
                },
                views: {
                    "main@app.admin": {
                        controller: namespace+'.controller',
                        controllerAs: 'ArticleController',
                        templateUrl: 'templates/app/admin/articles/article/article.tpl.html'
                    },
                    ['post@'+namespace] : {
                        controller: namespace+'.post.controller',
                        controllerAs: 'PostController',
                        templateUrl: 'templates/app/admin/articles/article/post/post.tpl.html'
                    },
                    ['media@'+namespace] : {
                        controller: namespace+'.media.controller',
                        controllerAs: 'MediaController',
                        templateUrl: 'templates/app/admin/articles/article/media/media.tpl.html'
                    },
                    ['meta@'+namespace] : {
                        controller: namespace+'.meta.controller',
                        controllerAs: 'MetaController',
                        templateUrl: 'templates/app/admin/articles/article/meta/meta.tpl.html'
                    },
                    ['stats@'+namespace] : {
                        controller: namespace+'.stats.controller',
                        controllerAs: 'StatsController',
                        templateUrl: 'templates/app/admin/articles/article/stats/stats.tpl.html'
                    },
                    ['history@'+namespace] : {
                        controller: namespace+'.history.controller',
                        controllerAs: 'HistoryController',
                        templateUrl: 'templates/app/admin/articles/article/history/history.tpl.html'
                    },
                    ['preview@'+namespace] : {
                        controller: app.guest.articles.article.namespace+'.controller',
                        controllerAs: 'ArticleController',
                        templateUrl: 'templates/app/guest/articles/article/article.tpl.html'
                    },
                    ['header@'+namespace]: {
                        controller: app.guest.articles.article.namespace+'.header.controller',
                        controllerAs: 'HeaderController',
                        templateUrl: 'templates/app/guest/articles/article/header/header.tpl.html'
                    },
                    ['body@'+namespace]: {
                        controller: app.guest.articles.article.namespace+'.body.controller',
                        controllerAs: 'BodyController',
                        templateUrl: 'templates/app/guest/articles/article/body/body.tpl.html'
                    },
                    ['footer@'+namespace]: {
                        controller: app.guest.articles.article.namespace+'.footer.controller',
                        controllerAs: 'FooterController',
                        templateUrl: 'templates/app/guest/articles/article/header/header.tpl.html'
                    },
                },
                resolve: /*@ngInject*/{
                    article: (articleService:common.services.article.ArticleService, $stateParams:IArticleStateParams, userService:common.services.user.UserService):common.models.Article | ng.IPromise<common.models.Article> => {

                        if (!$stateParams.permalink || $stateParams.permalink == 'new'){
                            let newArticle = articleService.newArticle(userService.getAuthUser());
                            $stateParams.permalink = 'new';
                            $stateParams.newArticle = true;
                            return newArticle;
                        }

                        return articleService.getArticle($stateParams.permalink, [
                            'articlePermalinks', 'articleMetas', 'tags', 'author', 'sections'
                        ]);
                    },
                    usersPaginator: (userService:common.services.user.UserService) => {
                        return userService.getUsersPaginator().setCount(10);
                    }
                },
                onExit: ['articleService', (articleService:common.services.article.ArticleService) => {
                    articleService.dumpQueueSaveFunctions();
                }],
                data: {
                    title: "Article",
                    icon: 'library_books',
                    navigation: false,
                }
            };

            stateHelperServiceProvider.addState(namespace, ArticleConfig.state);

        }

    }

    export class ArticleController {



        public showPreview:boolean = false;

        static $inject = ['article', '$stateParams', 'articleService', 'notificationService'];
        constructor(
            public article:common.models.Article,
            public $stateParams:IArticleStateParams,
            private articleService:common.services.article.ArticleService,
            private notificationService:common.services.notification.NotificationService
        ) {
        }

        /**
         * Save the article
         * @returns {any}
         */
        public save(){

            return this.articleService.saveArticleWithRelated(this.article)
                .then(() => {
                    this.notificationService.toast('Article saved').pop();
                });
        }


        public togglePreview(){
            this.showPreview = !this.showPreview;
        }

    }

    angular.module(namespace, [
        namespace+'.post',
        namespace+'.media',
        namespace+'.meta',
        namespace+'.stats',
        namespace+'.history',
    ])
        .config(ArticleConfig)
        .controller(namespace+'.controller', ArticleController);

}