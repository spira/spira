namespace common.mixins {

    export abstract class LocalizableApiService extends common.services.AbstractApiService {

        /**
         * Save localizable entity localizations
         * @returns {any}
         * @param entity
         */
        public saveEntityLocalizations(entity:LocalizableModel):ng.IPromise<common.models.Localization<any>[]|boolean> {

            let localizationRequestCollection = this.getNestedCollectionRequestObject(entity, '_localizations', false, true);


            let localizationRegionPromises = _.map(localizationRequestCollection, (localizationModel:common.models.Localization<any>) => {
                return this.ngRestAdapter.put(`${this.apiEndpoint(entity)}/localizations/${localizationModel.regionCode}`, localizationModel.localizations);
            });

            return this.$q.all(localizationRegionPromises).then(() => {
                _.invoke(entity._localizations, 'setExists', true);
                return entity._localizations;
            });
        }

    }

}