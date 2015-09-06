namespace common.services.tag {

    export const namespace = 'common.services.tag';

    export class TagService {

        private cachedPaginator:common.services.pagination.Paginator;

        static $inject:string[] = ['ngRestAdapter', 'paginationService'];

        constructor(private ngRestAdapter:NgRestAdapter.INgRestAdapterService,
                    private paginationService:common.services.pagination.PaginationService) {
        }

        /**
         * Get an instance of the Tag given data
         * @param data
         * @returns {common.models.Tag}
         * @param exists
         */
        public static tagFactory(data:any, exists:boolean = false):common.models.Tag {
            return new common.models.Tag(data, exists);
        }

        /**
         * Get a new tag with no values and a set uuid
         * @returns {common.models.Tag}
         */
        public newTag():common.models.Tag {

            return TagService.tagFactory({
                tagId: this.ngRestAdapter.uuid(),
            });

        }

        /**
         * Get the tag paginator
         * @returns {Paginator}
         */
        public getTagsPaginator():common.services.pagination.Paginator {

            //cache the paginator so subsequent requests can be collection length-aware
            if (!this.cachedPaginator) {
                this.cachedPaginator = this.paginationService
                    .getPaginatorInstance('/tags')
                    .setModelFactory(TagService.tagFactory);
            }

            return this.cachedPaginator;
        }

        /**
         * Save a tag
         * @param tag
         * @returns ng.IPromise<common.models.Tag>
         */
        public saveTag(tag:common.models.Tag):ng.IPromise<common.models.Tag> {

            return this.ngRestAdapter.put('/tags/' + tag.tagId, _.clone(tag))
                .then(() => {
                    (<common.decorators.IChangeAwareDecorator>tag).resetChangedProperties(); //reset so next save only saves the changed ones
                    return tag;
                });

        }

    }

    angular.module(namespace, [])
        .service('tagService', TagService);

}



