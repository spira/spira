angular.module('app.public.blog.post', [])

    .config(function(stateHelperServiceProvider) {
        stateHelperServiceProvider.addState('app.public.blog.post', {
            url: '/{permalink}',
            views: {
                'main@app.public': {
                    controller: 'app.public.blog.post.controller',
                    templateUrl: 'templates/app/public/blog/post/post.tpl.html'
                },
                'content@app.public.blog.post': {
                    templateUrl: 'templates/app/public/blog/post/post-stub.tpl.html'
                }
            },
            resolve: /*@ngInject*/{

            },
            data: {
                title: "Blog Post",
                role: 'public'
            }
        });
    })

    .controller('app.public.blog.post.controller', function($scope) {


    })

;