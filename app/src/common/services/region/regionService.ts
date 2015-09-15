namespace common.services.region {

    export const namespace = 'common.services.region';

    export const supportedRegions:global.ISupportedRegion[] = [
        {
            code: 'au',
            name: 'Australia',
            icon: '&#x1F1E6;&#x1F1FA;',
            //emoji: '🇦🇺',
        },
        {
            code: 'uk',
            name: 'United Kingdom',
            icon: '&#x1F1EC;&#x1F1E7;',
            //emoji: '🇬🇧',
        },
        {
            code: 'us',
            name: 'United States',
            icon: '&#x1F1FA;&#x1F1F8;',
            //emoji : '🇺🇸',
        }
    ];

    export class RegionService {

        public supportedRegions:global.ISupportedRegion[];
        public currentRegion:global.ISupportedRegion = null;

        static $inject:string[] = ['$state'];
        constructor(private $state:ng.ui.IStateService) {
            this.supportedRegions = supportedRegions;
        }

        /**
         * Set the region and reload the current state
         * @param region
         */
        public setRegion(region:global.ISupportedRegion) {

            this.currentRegion = region;

            this.$state.go('.', {
                region: region.code
            });
        }

        /**
         * Get the region with a supplied code
         * @param regionCode
         * @returns {global.ISupportedRegion|T}
         */
        public getRegionByCode(regionCode:String):global.ISupportedRegion {
            return _.find(this.supportedRegions, {code: regionCode})
        }


    }

    angular.module(namespace, [])
        .constant('supportedRegions', supportedRegions)
        .service('regionService', RegionService)
        .service('regionInterceptor', RegionInterceptor)
        .config(['$httpProvider', '$injector', ($httpProvider:ng.IHttpProvider) => {
            $httpProvider.interceptors.push('regionInterceptor');
        }])
    ;
}



