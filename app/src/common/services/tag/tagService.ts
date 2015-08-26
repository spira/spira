namespace common.services.tag {

    export const namespace = 'common.services.tag';

    export class TagService {

        static $inject:string[] = ['ngRestAdapter'];

        constructor(private ngRestAdapter:NgRestAdapter.INgRestAdapterService) {
        }

        /**
         * Get an instance of the Tag given data
         * @param data
         * @returns {common.models.Tag}
         */
        public static tagFactory(data:any):common.models.Tag {
            return new common.models.Tag(data);
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

    }

    angular.module(namespace, [])
        .service('tagService', TagService);

}



