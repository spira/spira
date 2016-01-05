namespace common.mixins {

    export abstract class TaggableApiService extends common.services.AbstractApiService {


        public saveEntityTags(entity:TaggableModel):ng.IPromise<common.models.LinkingTag[]|boolean> {

            if(!_.has((<common.decorators.changeAware.IChangeAwareDecorator>entity).getChanged(true), '_tags')) {
                return this.$q.when(false);
            }

            let requestObject = this.getNestedCollectionRequestObject(entity, '_tags', false, false);

            return this.ngRestAdapter.put(this.apiEndpoint(entity) + '/tags', requestObject)
                .then(() => {
                    _.invoke(entity._tags, 'setExists', true);
                    return entity._tags;
                });

        }


    }

}

