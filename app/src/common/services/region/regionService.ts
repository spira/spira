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


    export class RegionInit {

        static $inject:string[] = ['regionService', 'ngJwtAuthService'];

        constructor(private regionService:RegionService,
                    private ngJwtAuthService:NgJwtAuth.NgJwtAuthService) {

            this.ngJwtAuthService.registerLoginListener((user:common.models.User) => regionService.handleLoggedInUser(user));

        }

    }

    export class RegionService {

        public supportedRegions:global.ISupportedRegion[];
        public currentRegion:global.ISupportedRegion = null;
        public userRegion:global.ISupportedRegion = null;

        static $inject:string[] = ['$state', '$timeout', 'ngJwtAuthService'];

        constructor(private $state:ng.ui.IStateService,
                    private $timeout:ng.ITimeoutService,
                    private ngJwtAuthService:NgJwtAuth.NgJwtAuthService) {

            this.supportedRegions = supportedRegions;

        }

        /**
         * Set the region and reload the current state
         * @param region
         */
        public setRegion(region:global.ISupportedRegion) {

            this.currentRegion = region;

            this.$timeout(() => {
                this.$state.go('.', {
                    region: region.code
                });
            });

        }

        public handleLoggedInUser(user:common.models.User):void {

            this.userRegion = this.getRegionByCode(user.regionCode);

            if (!this.currentRegion){
                this.currentRegion = this.userRegion;
            }

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
        .run(RegionInit)
        .service('regionService', RegionService)
        .service('regionInterceptor', RegionInterceptor)
        .config(['$httpProvider', '$injector', ($httpProvider:ng.IHttpProvider) => {
            $httpProvider.interceptors.push('regionInterceptor');
        }])
    ;
}



