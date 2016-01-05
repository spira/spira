namespace app.admin {

    export abstract class AbstractContentController<M extends common.models.AbstractModel, S extends common.services.IExtendedApiService> {

        public publicUrl:string;
        protected tagsPaginator:common.services.pagination.Paginator;

        constructor(
            public entity:M,
            protected tagService:common.services.tag.TagService,
            protected $scope:ng.IScope,
            public modelService:S
        ) {
            this.publicUrl = this.modelService.getPublicUrl(this.entity);

            this.tagsPaginator = tagService.getPaginator().setCount(10).noResultsResolve();
        }

        /**
         * Called when title field is modified
         */
        public updatePublicUrl():void {

            this.publicUrl = this.modelService.getPublicUrl(this.entity);

        }

    }

}