namespace app.admin {

    export interface ICommonListingStateParams extends ng.ui.IStateParamsService
    {
        page:number;
    }

    export interface IQuery
    {
        _all?:[string];
        authorId?:[string];
        _tags?:Object;
    }

    export abstract class AbstractListingController<M extends common.models.AbstractModel> {

        public entities:M[] = [];
        public pages:number[] = [];
        public currentPageIndex:number;
        public tagsToFilter:common.models.Tag[] = [];
        public usersToFilter:common.models.User[] = [];
        public queryString:string;

        private tagsPaginator:common.services.pagination.Paginator;
        private usersPaginator:common.services.pagination.Paginator;

        constructor(
            protected entitiesPaginator:common.services.pagination.Paginator,
            entities,
            protected tagService:common.services.tag.TagService,
            protected userService:common.services.user.UserService,
            public $stateParams:ICommonListingStateParams,
            protected $scope:ng.IScope
        ) {
            this.entities = entities;

            this.tagsPaginator = tagService.getPaginator().setCount(10).noResultsResolve();

            this.usersPaginator = userService.getPaginator().setCount(10).noResultsResolve();

            this.pages = entitiesPaginator.getPages();

            this.currentPageIndex = this.$stateParams.page - 1;

            this.$scope.$watchCollection(() => this.tagsToFilter, (newValue, oldValue) => {
                if (!_.isEqual(newValue, oldValue)) {
                    this.search();
                }
            });

            this.$scope.$watchCollection(() => this.usersToFilter, (newValue, oldValue) => {
                if (!_.isEqual(newValue, oldValue)) {
                    this.search();
                }
            });
        }

        /**
         * Function called when an entity is searched for.
         * @returns {ng.IPromise<any[]>}
         */
        public search():ng.IPromise<any> {

            let query:IQuery = {};

            if(this.queryString) {
                query._all = [this.queryString];
            }

            if(this.usersToFilter.length > 0) {
                query.authorId = (<[string]>_.pluck(this.usersToFilter, 'userId'));
            }

            if(this.tagsToFilter.length > 0) {
                query._tags = {tagId:_.pluck(this.tagsToFilter, 'tagId')};
            }

            if(_.isEmpty(query)) {
                return this.entitiesPaginator.reset().getPage(1)
                    .then((entities) => {
                        this.entities = entities;
                        this.pages = this.entitiesPaginator.getPages();
                    })
            }

            return this.entitiesPaginator.complexQuery(query)
                .then((entities) => {
                    this.entities = entities;
                })
                .catch(() => {
                    this.entities = [];
                })
                .finally(() => {
                    this.pages = this.entitiesPaginator.getPages();
                });
        }

        /**
         * Function used in auto-complete to search for tags.
         * @param query
         * @returns {ng.IPromise<any[]>}
         */
        public searchTags(query:string):ng.IPromise<any> {
            return this.tagsPaginator.complexQuery({
                tag: [query]
            });
        }

        /**
         * Function used in auto-complete to search for users.
         * @param query
         * @returns {ng.IPromise<any[]>}
         */
        public searchUsers(query:string):ng.IPromise<any> {
            return this.usersPaginator.query(query);
        }

    }

}