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


        public setRegion(region:global.ISupportedRegion) {

            let currentStateParams = this.$state.current.params;

            this.currentRegion = region;

            this.$state.go('.', {
                region: region.code
            });
        }


    }

    angular.module(namespace, [])
        .constant('supportedRegions', supportedRegions)
        .service('regionService', RegionService);

}



