module app.admin.articles {

    export const namespace = 'app.admin.articles';

    export class ArticlesConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
                url: '/articles',
                abstract: true,
                data: {
                    title: "Articles",
                    icon: 'description',
                    navigation: true,
                    navigationGroup: 'cms',
                    sortAfter: app.admin.dashboard.namespace,
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    angular.module(namespace, [
        'app.admin.articles.listing',
        'app.admin.articles.article',
        'app.admin.articles.curation',
    ])
        .config(ArticlesConfig);

}