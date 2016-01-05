namespace app.admin {

    export interface IAuthorForm extends ng.IFormController {
        authors: ng.INgModelController;
    }

    export abstract class AbstractMetaController<M extends common.models.IAuthoredModel> {

        public authors:common.models.User[];

        // @todo: Implementation not complete as model has not been implemented yet
        public supportedRegions:global.ISupportedRegion[] = common.services.region.supportedRegions;

        public allRegions:boolean = true;
        // Implementation not complete as model has not been implemented yet

        public overrideAuthor:boolean = false;

        public authorForm:IAuthorForm;

        constructor(public entity:M,
                    protected notificationService:common.services.notification.NotificationService,
                    protected usersPaginator:common.services.pagination.Paginator,
                    protected $scope:ng.IScope) {
            // Initialize the authors array which is used as a model for author contact chip input.
            this.authors = [entity._author];
            if(!_.isEmpty(entity.authorOverride)) {
                this.overrideAuthor = true;
            }

            // ng-change does not work with contact-chips: https://github.com/angular/material/issues/3857
            $scope.$watchCollection(() => this.authors, (newValue, oldValue) => {
                if (!_.isEqual(newValue, oldValue)) {
                    this.validateAndUpdateAuthor();
                }
            });
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
         * Function called when the author contact chip input is updated. As we are only allowed
         * to have one author per article, validate and update if valid.
         */
        public validateAndUpdateAuthor():void {

            this.authorForm.authors.$setValidity('maxlength', this.authors.length < 2);
            this.authorForm.authors.$setValidity('required', this.authors.length > 0);

            if(this.authorForm.$valid) {
                this.entity._author = this.authors[0];
                this.entity.authorId = this.authors[0].userId;
            }

        }

    }

}
