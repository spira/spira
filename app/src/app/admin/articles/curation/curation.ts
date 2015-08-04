module app.admin.articles.curation {

    export const namespace = 'app.admin.articles.curation';

    export class ArticlesCurationConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
                url: '/curation',
                views: {
                    "main@app.admin": {
                        controller: namespace+'.controller',
                        controllerAs: 'CurationController',
                        templateUrl: 'templates/app/admin/articles/curation/curation.tpl.html'
                    }
                },
                resolve: /*@ngInject*/{
                },
                data: {
                    title: "Article Curation",
                    icon: 'account_balance',
                    navigation: true,
                    sortAfter: app.admin.articles.listing.namespace,
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    export class ArticlesCurationController {

        static $inject = [];
        constructor() {

        }

    }

    angular.module(namespace, [])
        .config(ArticlesCurationConfig)
        .controller(namespace+'.controller', ArticlesCurationController);

}