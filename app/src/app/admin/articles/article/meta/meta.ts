namespace app.admin.articles.article.meta {

    export const namespace = 'app.admin.articles.article.meta';

    export class MetaController {

        static $inject = ['article', 'notificationService', 'usersPaginator'];

        public authors:common.models.User[];

        constructor(
            public article:common.models.Article,
            private notificationService:common.services.notification.NotificationService,
            private usersPaginator:common.services.pagination.Paginator
        ) {
            this.authors = [article._author];
        }

        /**
         * Function called when author is searched for in the author contact chip input.
         * @param query
         */
        public searchAuthors(query:string):ng.IPromise<common.models.User[]> {
            return this.usersPaginator.query(query);
        }

        /**
         * Function called when an author is added.
         */
        public authorAdded(newAuthor:common.models.User):void {
            this.authors = [newAuthor];
            this.article._author = newAuthor;
        }

    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', MetaController);

}