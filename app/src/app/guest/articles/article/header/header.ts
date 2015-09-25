namespace app.guest.articles.article.header {

    export const namespace = 'app.guest.articles.article.header';

    export class HeaderController {

        static $inject = ['article'];

        constructor(
            public article:common.models.Article
        ) {
        }

    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', HeaderController);

}