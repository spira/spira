namespace app.admin.articles.article.post {

    export const namespace = 'app.admin.articles.article.post';

    export class PostController {

        static $inject = ['article'];
        constructor(public article:common.models.Article) {

        }

    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', PostController);

}