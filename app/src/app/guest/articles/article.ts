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
                        templateUrl: 'templates/app/guest/articles/articles.tpl.html'
                    }
                },
                resolve: /*@ngInject*/{

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

    interface IScope extends ng.IScope
    {
    }

    class ArticlesController {

        static $inject = ['$scope'];
        constructor(private $scope : IScope) {

        }

    }

    angular.module(namespace, [
            'app.guest.articles.post'
        ])
        .config(ArticlesConfig)
        .controller(namespace+'.controller', ArticlesController);

}
