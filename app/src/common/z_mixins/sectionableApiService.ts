namespace common.mixins {

    export abstract class SectionableApiService extends common.services.AbstractApiService {

        /**
         * Save sectionable entity sections
         * @returns {any}
         * @param entity
         */
        public saveEntitySections(entity:SectionableModel):ng.IPromise<common.models.Section<any>[]|boolean> {

            let sections = entity._sections;

            if (entity.exists()) {

                let changes:any = (<common.decorators.IChangeAwareDecorator>entity).getChanged(true);
                if (!_.has(changes, '_sections')) {
                    return this.$q.when(false);
                }
            }

            sections = _.filter((sections), (section:common.models.Section<any>) => {
                return !section.exists() || _.size((<common.decorators.IChangeAwareDecorator>section).getChanged()) > 0;
            });

            return this.ngRestAdapter.put(this.apiEndpoint(entity) + '/sections', _.clone(sections))
                .then(() => {
                    return entity._sections;
                });
        }


        public deleteSection(entity:SectionableModel, section:common.models.Section<any>):ng.IPromise<boolean> {
            return this.ngRestAdapter.remove(this.apiEndpoint(entity) + '/sections/' + section.sectionId)
                .then(() => {
                    return true;
                });
        }

    }

}