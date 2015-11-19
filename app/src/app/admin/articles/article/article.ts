namespace app.admin.articles.article {

    export const namespace = 'app.admin.articles.article';

    export class ArticleConfig {

        public static state:global.IState;

        static $inject = ['stateHelperServiceProvider'];

        constructor(private stateHelperServiceProvider){

            ArticleConfig.state = {
                url: '/article/{id}',
                params: {
                    newEntity: false,
                },
                views: {
                    "main@app.admin": {
                        controller: namespace+'.controller',
                        controllerAs: 'ArticleController',
                        templateUrl: 'templates/app/admin/articles/article/article.tpl.html'
                    },
                    ['content@'+namespace] : {
                        controller: namespace+'.content.controller',
                        controllerAs: 'ContentController',
                        templateUrl: 'templates/app/admin/articles/article/content/content.tpl.html'
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
                    article: (articleService:common.services.article.ArticleService, $stateParams:app.admin.ICommonStateParams, userService:common.services.user.UserService):common.models.Article | ng.IPromise<common.models.Article> => {

                        if (!$stateParams.id || $stateParams.id == 'new'){
                            let newArticle = articleService.newArticle(userService.getAuthUser());
                            $stateParams.id = 'new';
                            $stateParams.newEntity = true;
                            return newArticle;
                        }

                        return articleService.getModel($stateParams.id, [
                            'articlePermalinks', 'metas', 'tags', 'author', 'sections.localizations', 'localizations', 'thumbnailImage'
                        ]);
                    },
                    usersPaginator: (userService:common.services.user.UserService) => {
                        return userService.getUsersPaginator().setCount(10);
                    },
                    groupTags: (tagService:common.services.tag.TagService, articleService:common.services.article.ArticleService) => {
                        return tagService.getTagCategories(articleService);
                    }
                },
                onExit: ['articleService', (articleService:common.services.article.ArticleService) => {
                    articleService.dumpQueueSaveFunctions();
                }],
                data: {
                    title: "Compose Article",
                    icon: 'library_books',
                    navigation: false,
                }
            };

            stateHelperServiceProvider.addState(namespace, ArticleConfig.state);

        }

    }

    export class ArticleController extends app.admin.AbstractEntitiesController<common.models.Article, common.services.article.ArticleService> {

        static $inject = ['article', 'articleService', '$stateParams', 'notificationService', 'groupTags'];


        public showPreview:boolean = false;


        public togglePreview(){
            this.showPreview = !this.showPreview;
        }

    }

    angular.module(namespace, [
        namespace+'.content',
        namespace+'.meta',
        namespace+'.stats',
        namespace+'.history',
    ])
        .config(ArticleConfig)
        .controller(namespace+'.controller', ArticleController);

}