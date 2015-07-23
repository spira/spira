module common.services.countries {

    export const namespace = 'common.services.countries';

    export class CountriesService {

        static $inject:string[] = ['ngRestAdapter', '$q'];
        constructor(
            private ngRestAdapter: NgRestAdapter.INgRestAdapterService,
            private $q:ng.IQService) {

        }

        /**
         * Get all countries from the API
         * @returns {any}
         */
        public getAllCountries(){

            return this.ngRestAdapter.get('/users')
                .then((res) => {
                    return res.data;
                })
            ;

        }

    }

    angular.module(namespace, [])
        .service('countriesService', CountriesService);

}



