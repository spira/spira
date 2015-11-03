namespace common.mixins {

    export abstract class LocalizableApiService extends common.services.AbstractApiService {

        /**
         * Save localizable entity localizations
         * @returns {any}
         * @param entity
         */
        public saveEntityLocalizations(entity:LocalizableModel):ng.IPromise<common.models.Localization<any>[]|boolean> {

            let localizations = entity._localizations;

            if (entity.exists()) {

                let changes:any = (<common.decorators.IChangeAwareDecorator>entity).getChanged(true);
                if (!_.has(changes, '_localizations')) {
                    return this.$q.when(false);
                }
            }

            let localizationRegionPromises = _.chain(entity._localizations)
                .filter((localizationModel:common.models.Localization<any>) => {
                    return !localizationModel.exists() || _.size((<common.decorators.IChangeAwareDecorator>localizationModel).getChanged()) > 0;
                })
                .map((localizationModel:common.models.Localization<any>) => {

                    return this.ngRestAdapter.put(`${this.apiEndpoint(entity)}/localizations/${localizationModel.regionCode}`, localizationModel.localizations)
                        .then(() => {
                            localizationModel.localizableId = entity.getKey();
                            localizationModel.setExists(true);
                            return localizationModel;
                        });
                })
                .value();

            return this.$q.all(localizationRegionPromises);
        }

    }

}