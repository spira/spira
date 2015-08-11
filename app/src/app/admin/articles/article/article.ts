namespace app.admin.articles.article {

    export const namespace = 'app.admin.articles.article';

    export interface IArticleStateParams extends ng.ui.IStateParamsService
    {
        permalink:string;
        newArticle:boolean;
    }

    export class ArticleConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
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
                    }
                },
                resolve: /*@ngInject*/{
                    article: (articleService:common.services.article.ArticleService, $stateParams:IArticleStateParams):common.models.Article | ng.IPromise<common.models.Article> => {

                        if (!$stateParams.permalink || $stateParams.permalink == 'new'){
                            let newArticle = articleService.newArticle();
                            $stateParams.permalink = 'new';
                            $stateParams.newArticle = true;
                            return newArticle;
                        }

                        return articleService.getArticle($stateParams.permalink);
                    }
                },
                data: {
                    title: "Article",
                    icon: 'library_books',
                    navigation: false,
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    export class ArticleController {

        static $inject = ['article', '$stateParams', 'articleService', '$mdToast'];
        constructor(public article:common.models.Article, public $stateParams:IArticleStateParams, private articleService:common.services.article.ArticleService, private $mdToast:ng.material.IToastService) {
        }

        /**
         * Save the article
         * @returns {any}
         */
        public save(){

            return this.articleService.saveArticle(this.article, this.$stateParams.newArticle)
                .then(() => {
                    this.$mdToast.show({
                        hideDelay:2000,
                        position:'top',
                        template:'<md-toast>Article saved</md-toast>'
                    });
                });
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