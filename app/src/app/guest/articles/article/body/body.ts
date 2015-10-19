namespace app.guest.articles.article.body {

    export const namespace = 'app.guest.articles.article.body';

    /* istanbul ignore next:@todo - skipping controller testing */
    export class BodyController {

        static $inject = ['article'];

        constructor(
            public article:common.models.Article
        ) {
        }

    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', BodyController);

}