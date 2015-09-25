namespace common.services.countries {

    export const namespace = 'common.services.countries';

    export interface ICountryDefinition {
        countryName:string;
        countryCode:string;
    }

    export class CountriesService {

        static $inject:string[] = ['ngRestAdapter'];

        constructor(private ngRestAdapter:NgRestAdapter.INgRestAdapterService) {

        }

        private countriesCachePromise:ng.IPromise<ICountryDefinition[]> = null;

        /**
         * Get all countries from the API
         * @returns {any}
         */
        public getAllCountries():ng.IPromise<ICountryDefinition[]> {

            //store the promise in cache, so next time it is called the countries are resolved immediately.
            if (!this.countriesCachePromise) {
                this.countriesCachePromise = this.ngRestAdapter.get('/countries')
                    .then((res) => res.data)
                ;
            }

            return this.countriesCachePromise;
        }

    }

    angular.module(namespace, [])
        .service('countriesService', CountriesService);

}



