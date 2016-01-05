namespace common.services.tag {

    export const namespace = 'common.services.tag';

    export class TagService extends AbstractApiService {

        static $inject:string[] = ['ngRestAdapter', 'paginationService', '$q'];

        /**
         * Get the api endpoint for the model
         * @returns {string}
         */
        public apiEndpoint():string {
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
        public newTag(overrides:any = {}):common.models.Tag {

            return this.modelFactory(_.merge({
                tagId: this.ngRestAdapter.uuid(),
            }, overrides));

        }

        /**
         * Get the first result, if there is no result create a new tag
         * @param query
         * @returns {IPromise<common.models.Tag>}
         */
        public firstOrNew(query:string):ng.IPromise<common.models.Tag> {

            return this.getPaginator().query(query)
                .then((results):common.models.Tag => {
                    if(!_.find(results, {tag:query})) {
                        return this.newTag({tag:query});
                    }

                    return results[0];
                })
                .catch(():common.models.Tag => {
                    return this.newTag({tag:query});
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
                    (<common.decorators.changeAware.IChangeAwareDecorator>tag).resetChanged(); //reset so next save only saves the changed ones
                    return tag;
                });

        }

        /**
         * Get top level group tags for a particular group (e.g. articles).
         *
         * @returns {IPromise<common.models.Tag[]>}
         * @param service
         */
        public getTagCategories(service:common.services.AbstractApiService):ng.IPromise<common.models.CategoryTag[]> {

            if(!service.cachedCategoryTagPromise) {
                service.cachedCategoryTagPromise = this.getAllModels<common.models.CategoryTag>(['childTags'], service.apiEndpoint() + '/tag-categories')
            }

            return service.cachedCategoryTagPromise;
        }

        /**
         * Categorize an entity's tags for ease of use.
         *
         * @param entity
         * @param service
         * @returns {IPromise<TResult>}
         */
        public categorizeTags<T extends common.mixins.TaggableModel, S extends common.services.AbstractApiService>(entity:T, service:S):ng.IPromise<common.models.ICategorizedTags>|common.models.ICategorizedTags {

            return this.getTagCategories(service)
                .then((categoryTags:common.models.CategoryTag[]) => {

                    // Get the keys of the return object, strip out any spaces
                    let keys = _.map(categoryTags, (categoryTag:common.models.CategoryTag) => {
                        return categoryTag.tag.replace(' ', '');
                    });

                    let tagTagIds = _.zipObject(
                        keys,
                        categoryTags
                    );

                    return _.mapValues(tagTagIds, (categoryTag:common.models.CategoryTag) => {
                        (<common.models.CategoryTagWithChildren>categoryTag)._tagsInCategory = _.filter(entity._tags, (tag:common.models.LinkingTag) => {
                            return tag._pivot.tagGroupId == categoryTag.tagId;
                        });

                        return categoryTag;
                    });

                });

        }

    }

    angular.module(namespace, [])
        .service('tagService', TagService);

}



