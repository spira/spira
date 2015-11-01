namespace common.mixins {

    export abstract class TaggableApiService extends common.services.AbstractApiService {


        public saveEntityTags(entity:TaggableModel):ng.IPromise<common.models.LinkingTag[]|boolean> {

            let tagData = _.clone(entity._tags);

            if (entity.exists()) {

                let changes:any = (<common.decorators.IChangeAwareDecorator>entity).getChanged(true);
                if (!_.has(changes, '_tags')) {
                    return this.$q.when(false);
                }
            }

            return this.ngRestAdapter.put(this.apiEndpoint(entity) + '/tags', tagData)
                .then(() => {
                    return entity._tags;
                });

        }


    }

}

