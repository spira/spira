namespace app.admin {

    export abstract class AbstractMetaController<M extends common.models.IAuthoredModel> {

        public authors:common.models.User[];

        // @todo: Implementation not complete as model has not been implemented yet
        public supportedRegions:global.ISupportedRegion[] = common.services.region.supportedRegions;

        public allRegions:boolean = true;
        // Implementation not complete as model has not been implemented yet

        public overrideAuthor:boolean = false;

        constructor(public entity:M,
                    protected notificationService:common.services.notification.NotificationService,
                    protected usersPaginator:common.services.pagination.Paginator) {
            // Initialize the authors array which is used as a model for author contact chip input.
            this.authors = [entity._author];
            if(!_.isEmpty(entity.authorOverride)) {
                this.overrideAuthor = true;
            }
        }

        /**
         * Function called when user is searched for in the author contact chip input.
         * @param query
         */
        public searchUsers(query:string):ng.IPromise<common.models.User[]> {
            return this.usersPaginator.query(query);
        }

        /**
         * Function called when there is a change in Author Display Options.
         */
        public authorDisplay():void {
            // If 'Display real author on post' is selected, clear the authorOverride and website
            if(!this.overrideAuthor) {
                this.entity.authorOverride = null;
                this.entity.authorWebsite = null;
            }
        }

        /**
         * Function called when a user is added to the author contact chip input. As we are only allowed
         * to have one author per article, we should replace the author.
         */
        public changeAuthor(newAuthor:common.models.User):void {
            this.authors = [newAuthor];
            this.entity._author = newAuthor;
            this.entity.authorId = newAuthor.userId;
        }

    }

}
