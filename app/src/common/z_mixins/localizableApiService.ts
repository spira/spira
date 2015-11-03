namespace common.mixins {

    export abstract class LocalizableApiService extends common.services.AbstractApiService {

        /**
         * Save localizable entity localizations
         * @returns {any}
         * @param entity
         */
        public saveEntityLocalizations(entity:LocalizableModel):ng.IPromise<common.models.Section<any>[]|boolean> {

            let localizations = entity._localizations;

            if (entity.exists()) {

                let changes:any = (<common.decorators.IChangeAwareDecorator>entity).getChanged(true);
                if (!_.has(changes, '_localizations')) {
                    return this.$q.when(false);
                }
            }

            let localizationRegionPromises = _.map(entity._localizations, (localizationModel:common.models.Localization<common.models.Section<any>>) => {

                return this.ngRestAdapter.put(`${this.apiEndpoint(entity)}/localizations/${localizationModel.regionCode}`, localizationModel.localizations)
                    .then(() => {
                        localizationModel.localizableId = entity.getKey();
                        localizationModel.setExists(true);
                        return localizationModel;
                    });
            });

            return this.$q.all(localizationRegionPromises);
        }

    }

}