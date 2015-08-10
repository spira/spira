namespace app.admin.articles.article.stats {

    export const namespace = 'app.admin.articles.article.stats';

    export class StatsController {

        static $inject = ['article'];
        constructor(public article:common.models.Article) {

        }

    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', StatsController);

}