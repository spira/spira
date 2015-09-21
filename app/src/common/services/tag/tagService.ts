namespace common.services.tag {

    export const namespace = 'common.services.tag';

    export class TagService extends AbstractApiService {

        static $inject:string[] = ['ngRestAdapter', 'paginationService', '$q'];

        /**
         * Get the api endpoint for the model
         * @returns {string}
         */
        protected apiEndpoint():string {
            return '/tags';
        }

        /**
         * Get an instance of the Tag given data
         * @param data
         * @returns {common.models.Tag}
         * @param exists
         */
        public modelFactory(data:any, exists:boolean = false):common.models.Tag {
            return new common.models.Tag(data, exists);
        }

        /**
         * Get a new tag with no values and a set uuid
         * @returns {common.models.Tag}
         */
        public newTag():common.models.Tag {

            return this.modelFactory({
                tagId: this.ngRestAdapter.uuid(),
            });

        }

        /**
         * Save a tag
         * @param tag
         * @returns ng.IPromise<common.models.Tag>
         */
        public saveTag(tag:common.models.Tag):ng.IPromise<common.models.Tag> {

            return this.ngRestAdapter.put(this.apiEndpoint() + '/' + tag.tagId, _.clone(tag))
                .then(() => {
                    (<common.decorators.IChangeAwareDecorator>tag).resetChanged(); //reset so next save only saves the changed ones
                    return tag;
                });

        }

    }

    angular.module(namespace, [])
        .service('tagService', TagService);

}



