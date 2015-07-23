module common.services.countries {

    export const namespace = 'common.services.countries';

    export class CountriesService {

        static $inject:string[] = ['ngRestAdapter', '$q'];

        constructor(private ngRestAdapter:NgRestAdapter.INgRestAdapterService,
                    private $q:ng.IQService) {

        }

        private countriesCachePromise:ng.IPromise<any> = null;

        /**
         * Get all countries from the API
         * @returns {any}
         */
        public getAllCountries() {

            //store the promise in cache, so next time it is called the countries are resolved immediately.
            if (!this.countriesCachePromise){
                this.countriesCachePromise = this.ngRestAdapter.get('/countries')
                    .then((res) => {
                        return res.data;
                    })
                ;
            }

            return this.countriesCachePromise;
        }

    }

    angular.module(namespace, [])
        .service('countriesService', CountriesService);

}



