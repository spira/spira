module app.guest.articles.post {

    export const namespace = 'app.guest.articles.post';

    class PostConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
                url: '/{stub}',
                views: {
                    'main@app.guest': {
                        controller: namespace+'.controller',
                        templateUrl: 'templates/app/guest/blog/post/post.tpl.html'
                    },
                    'content@app.guest.blog.post': {
                        templateUrl: 'templates/app/guest/blog/post/post-stub.tpl.html'
                    }
                },
                resolve: /*@ngInject*/{

                },
                data: {
                    title: "Blog Post",
                    role: 'public'
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    interface IScope extends ng.IScope
    {
    }

    class PostController {

        static $inject = ['$scope'];
        constructor(private $scope : IScope) {

        }

    }

    angular.module(namespace, [
        'app.guest.articles.post'
    ])
        .config(PostConfig)
        .controller(namespace+'.controller', PostController);

}