namespace app.guest.articles.article.comments {

    export const namespace = 'app.guest.articles.article.comments';

    export class CommentsController {

        static $inject = ['article'];

        constructor(
            public article:common.models.Article
        ) {
        }

    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', CommentsController);

}