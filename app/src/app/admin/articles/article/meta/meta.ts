namespace app.admin.articles.article.meta {

    export const namespace = 'app.admin.articles.article.meta';

    export class MetaController {

        static $inject = ['article'];
        constructor(public article:common.models.Article) {

        }

    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', MetaController);

}