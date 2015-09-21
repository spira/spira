namespace app.guest.articles.article {

    export const namespace = 'app.guest.articles.article';

    class ArticleConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
                url: '/article/{permalink}',
                views: {
                    'main@app.guest': {
                        controller: namespace+'.controller',
                        templateUrl: 'templates/app/guest/articles/article/article.tpl.html'
                    },
                    'content@app.guest.blog.post': {
                        templateUrl: 'templates/app/guest/articles/article/article-stub.tpl.html'
                    }
                },
                resolve: /*@ngInject*/{

                },
                data: {
                    title: "Article",
                    role: 'public'
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    interface IScope extends ng.IScope
    {
    }

    class ArticleController {

        static $inject = ['$scope'];
        constructor(private $scope : IScope) {

        }

    }

    angular.module(namespace, [
        'app.guest.articles.article'
    ])
        .config(ArticleConfig)
        .controller(namespace+'.controller', ArticleController);

}