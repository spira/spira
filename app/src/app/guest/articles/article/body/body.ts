namespace app.guest.articles.article.body {

    export const namespace = 'app.guest.articles.article.body';

    export class BodyController {

        static $inject = ['article'];

        constructor(
            public article:common.models.Article
        ) {
            console.log('article', article);
        }

    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', BodyController);

}