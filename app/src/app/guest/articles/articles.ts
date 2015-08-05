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
                        controllerAs: 'ArticlesController',
                        templateUrl: 'templates/app/guest/articles/articles.tpl.html'
                    }
                },
                resolve: /*@ngInject*/{
                    articlesPaginator: (articleService:common.services.article.ArticleService) => {
                        return articleService.getArticlesPaginator();
                    },
                    initialArticles: (articlesPaginator:common.services.pagination.Paginator) => {
                        return articlesPaginator.getNext();
                    }
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

    export class ArticlesController {

        public allArticles:common.models.Article[] = [];
        static $inject = ['articlesPaginator', 'initialArticles'];
        constructor(private articlesPaginator:common.services.pagination.Paginator, initialArticles:common.models.Article[]) {

            this.allArticles = initialArticles;

        }


        /**
         * Get more articles
         */
        public showMore():void {

            this.articlesPaginator.getNext().then((moreArticles:common.models.Article[]) => {

                this.allArticles = this.allArticles.concat(moreArticles)
            });

        }


    }

    angular.module(namespace, [
            'app.guest.articles.post'
        ])
        .config(ArticlesConfig)
        .controller(namespace+'.controller', ArticlesController);

}
