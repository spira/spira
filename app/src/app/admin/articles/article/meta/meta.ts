namespace app.admin.articles.article.meta {

    export const namespace = 'app.admin.articles.article.meta';

    export class MetaController {

        static $inject = ['article', 'notificationService', 'usersPaginator'];

        public authors:common.models.User[];

        // @todo: Implementation not complete as model has not been implemented yet
        public supportedRegions:global.ISupportedRegion[] = common.services.region.supportedRegions;

        public allRegions:boolean = true;
        // Implementation not complete as model has not been implemented yet

        constructor(
            public article:common.models.Article,
            private notificationService:common.services.notification.NotificationService,
            private usersPaginator:common.services.pagination.Paginator
        ) {
            // Initialize the authors array which is used as a model for author contact chip input.
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