namespace app.admin.articles.article.media {

    export const namespace = 'app.admin.articles.article.media';

    export class MediaController {

        static $inject = ['article'];
        constructor(public article:common.models.Article) {

        }

    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', MediaController);

}