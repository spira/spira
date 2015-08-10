namespace app.admin.articles.article {

    export const namespace = 'app.admin.articles.article';

    interface IStateParams extends ng.ui.IStateParamsService
    {
        permalink:string;
    }

    export class ArticleConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
                url: '/article/{permalink}',
                views: {
                    "main@app.admin": {
                        controller: namespace+'.controller',
                        controllerAs: 'ArticleController',
                        templateUrl: 'templates/app/admin/articles/article/article.tpl.html'
                    }
                },
                resolve: /*@ngInject*/{
                    article: (articleService:common.services.article.ArticleService, $stateParams:IStateParams) => {

                        if (!$stateParams.permalink){
                            let newArticle = articleService.newArticle();
                            $stateParams.permalink = newArticle.getIdentifier();
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

        static $inject = ['article'];
        constructor(public article:common.models.Article) {

        }
    }

    angular.module(namespace, [])
        .config(ArticleConfig)
        .controller(namespace+'.controller', ArticleController);

}