namespace common.mixins {

    export abstract class TaggableApiService extends common.services.AbstractApiService {


        public saveEntityTags(entity:TaggableModel):ng.IPromise<common.models.LinkingTag[]|boolean> {

            let requestObject = this.getNestedCollectionRequestObject(entity, '_tags', false, false);

            if (!requestObject){
                return this.$q.when(false);
            }

            return this.ngRestAdapter.put(this.apiEndpoint(entity) + '/tags', requestObject)
                .then(() => {
                    _.invoke(entity._tags, 'setExists', true);
                    return entity._tags;
                });

        }


    }

}

