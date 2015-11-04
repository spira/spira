namespace common.mixins {

    export abstract class SectionableApiService extends common.services.AbstractApiService {

        /**
         * Save sectionable entity sections
         * @returns {any}
         * @param entity
         */
        public saveEntitySections(entity:SectionableModel):ng.IPromise<common.models.Section<any>[]|boolean> {
/*
            let sections = entity._sections;

            if (!sections || _.isEmpty(sections)){
                return this.$q.when(false);
            }
            console.log('sections', sections);

            if (entity.exists()) {

                let changes:any = (<common.decorators.IChangeAwareDecorator>entity).getChanged(true);
                if (!_.has(changes, '_sections')) {
                    return this.$q.when(false);
                }
            }

            let requestObject = _.chain(sections)
                .filter((section:common.models.Section<any>) => {
                    return !section.exists() || _.size((<common.decorators.IChangeAwareDecorator>section).getChanged()) > 0;
                })
                .map((section:common.models.Section<any>) => {
                    return section.getAttributes();
                })
                .value();
*/

            let requestObject = this.getNestedCollectionRequestObject(entity, '_sections', true);

            if (!requestObject){
                return this.$q.when(false);
            }

            return this.ngRestAdapter.put(this.apiEndpoint(entity) + '/sections', requestObject)
                .then(() => this.saveEntitySectionLocalizations(entity))
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

        public saveEntitySectionLocalizations(entity:SectionableModel):ng.IPromise<any> {

            let sectionLocalizationPromises = _.map(entity._sections, (section:common.models.Section<any>) => {

                if (!section._localizations){
                    return this.$q.when(true);
                }

                let localizationRegionPromises = _.chain(section._localizations)
                    .filter((localizationModel:common.models.Localization<any>) => {
                        return !localizationModel.exists() || _.size((<common.decorators.IChangeAwareDecorator>localizationModel).getChanged()) > 0;
                    })
                    .map((localizationModel:common.models.Localization<any>) => {

                        localizationModel.localizations.type = section.type; //add type so validator can find the correct validation to apply

                        return this.ngRestAdapter.put(`${this.apiEndpoint(entity)}/sections/${section.sectionId}/localizations/${localizationModel.regionCode}`, localizationModel.localizations)
                            .then(() => {
                                localizationModel.localizableId = entity.getKey();
                                localizationModel.setExists(true);
                                return localizationModel;
                            });
                    })
                    .value();

                return this.$q.all(localizationRegionPromises);

            });

            return this.$q.all(sectionLocalizationPromises);

        }
    }

}