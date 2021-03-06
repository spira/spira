namespace app.guest {

    export const namespace = 'app.guest';

    interface IGuestStateParams {
        region: string;
    }

    class GuestConfig {

        static $inject = ['stateHelperServiceProvider', 'supportedRegions'];
        constructor(private stateHelperServiceProvider, supportedRegions:global.ISupportedRegion[]){

            let regionString = _.pluck(supportedRegions, 'code').join('|');

            let state:global.IState = {
                abstract: true,
                url: `/{region:${regionString}}`,
                params: {
                    region: {
                        value: null,
                        squash: true,
                    }
                },
                views: {
                    'app@': { // Points to the ui-view in the index.html
                        templateUrl: 'templates/app/_layouts/default.tpl.html',
                        controller: app.namespace + '.controller',
                        controllerAs: 'AppController',
                    },
                    'navigation@app.guest': { // Points to the ui-view="navigation" in default.tpl.html
                        templateUrl: 'templates/app/guest/navigation/navigation.tpl.html',
                        controller: app.guest.navigation.namespace+'.controller',
                        controllerAs: 'NavigationController',
                    },
                    'registration@app.guest': { // Points to the ui-view="registration" in default.tpl.html
                        templateUrl: 'templates/app/guest/registration/registration.tpl.html',
                        controller: app.guest.registration.namespace+'.controller',
                        controllerAs: 'RegistrationController',
                    }
                },
                resolve: /*@ngInject*/{
                    region: ($stateParams:IGuestStateParams, regionService:common.services.region.RegionService) => {

                        //if the region service has a different region set, change it to the url one as that will be the link preference
                        if (!!$stateParams.region && (!regionService.currentRegion || regionService.currentRegion.code !== $stateParams.region)){
                            regionService.currentRegion = regionService.getRegionByCode($stateParams.region);
                        }

                        return $stateParams.region;
                    }
                },
                data: {
                    loggedIn: false,
                    role: 'guest',
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    angular.module('app.guest', [
        'app.guest.home',
        'app.guest.forum',
        'app.guest.login',
        'app.guest.sandbox',
        'app.guest.articles',
        'app.guest.navigation',
        'app.guest.registration',
        'app.guest.resetPassword',
    ])
    .config(GuestConfig);

}