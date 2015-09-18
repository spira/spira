namespace common.services.region {

    type IRequestConfig = ng.IRequestConfig & NgRestAdapter.INgRestAdapterRequestConfig;

    export class RegionInterceptor {

        private regionService:RegionService;

        /**
         * Construct the service with dependencies injected
         */
        static $inject = ['$injector'];

        constructor(private $injector:ng.auto.IInjectorService) {
        }

        /**
         * Get an instance of the region service from the injector
         * @returns {RegionService}
         */
        private getRegionService = ():RegionService=> {
            if (this.regionService == null) {
                this.regionService = this.$injector.get('regionService');
            }
            return this.regionService;
        };

        /**
         * Add the Accept-Region header to the request when a region is set
         * @param config
         * @returns {IRequestConfig}
         */
        public request = (config:IRequestConfig):IRequestConfig => {

            if (!config.isBaseUrl){
                return config;
            }

            let regionService = this.getRegionService();

            if (regionService.currentRegion){
                config.headers['Accept-Region'] = regionService.currentRegion.code;
            }

            return config;
        };

        /**
         * Intercept and process the Content-Region header when the api returns it
         * @param response
         * @returns {ng.IHttpPromiseCallbackArg<any>}
         */
        public response = (response:ng.IHttpPromiseCallbackArg<any>):ng.IHttpPromiseCallbackArg<any> => {

            let regionHeader = response.headers('Content-Region');

            if (regionHeader) {

                let regionService = this.getRegionService();

                if (!regionService.currentRegion) { //only trigger a region set when the current region has not been set
                    regionService.setRegion(regionService.getRegionByCode(regionHeader));
                }

            }

            return response;
        };

    }

}



