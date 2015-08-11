namespace app.admin.articles.article.history {

    export const namespace = 'app.admin.articles.article.history';

    export class HistoryController {

        static $inject = ['article'];
        constructor(public article:common.models.Article) {

        }

    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', HistoryController);

}