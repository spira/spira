namespace app.admin.articles.article.meta {

    export const namespace = 'app.admin.articles.article.meta';

    export class MetaController {

        static $inject = ['article', 'notificationService'];

        public authors:common.models.ContactChip[] = [];

        constructor(
            public article:common.models.Article,
            private notificationService:common.services.notification.NotificationService
        ) {
            this.authors.push(article._author.contactChip());
        }

        /**
         * Function called when author is searched for in the author contact chip input
         * @param query
         */
        public searchAuthors(query:string) {
        }

    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', MetaController);

}