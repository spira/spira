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
         * Function called when user is searched for in the author contact chip input.
         * @param query
         */
        public searchUsers(query:string):ng.IPromise<common.models.User[]> {
            return this.usersPaginator.query(query);
        }

        /**
         * Function called when a user is added to the author contact chip input. As we are only allowed
         * to have one author per article, we should replace the author.
         */
        public changeAuthor(newAuthor:common.models.User):void {
            this.authors = [newAuthor];
            this.article._author = newAuthor;
            this.article.authorId = newAuthor.userId;
        }

    }

    angular.module(namespace, [])
        .controller(namespace+'.controller', MetaController);

}