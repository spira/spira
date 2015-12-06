namespace app.admin.users.editUser {

    export const namespace = 'app.admin.users.editUser';

    class EditUserConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(
            private stateHelperServiceProvider
        ){

            let state:global.IState = {
                url: '/{userId}/edit',
                views: {
                    "main@app.admin": {
                        controller: namespace+'.controller',
                        controllerAs: 'EditUserController',
                        templateUrl: 'templates/app/admin/users/editUser/editUser.tpl.html',
                    },
                    ['profile@'+namespace]: {
                        controller: namespace+'.controller',
                        controllerAs: 'ProfileController',
                        templateUrl: 'templates/app/user/profile/profile.tpl.html',
                    }
                },
                resolve: /*@ngInject*/{
                    countries:(countriesService:common.services.countries.CountriesService) => {
                        return countriesService.getAllCountries()
                    },
                    timezones:(timezonesService:common.services.timezones.TimezonesService) => {
                        return timezonesService.getAllTimezones();
                    },
                    fullUserInfo:(userService:common.services.user.UserService, $stateParams:IStateParams) => {
                        console.log($stateParams.userId);
                        return userService.getModel($stateParams.userId, ['userCredential', 'userProfile', 'socialLogins', 'uploadedAvatar']);
                    },
                    genderOptions:() => {
                        return common.models.UserProfile.genderOptions;
                    },
                    providerTypes:() => {
                        return common.models.UserSocialLogin.providerTypes;
                    },
                    regions:(regionService:common.services.region.RegionService) => {
                        return regionService.supportedRegions;
                    }
                },
                data: {
                    title: "Edit User Profile",
                    icon: 'extension',
                    navigation: false
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    interface IStateParams extends ng.ui.IStateParamsService
    {
        userId:string;
    }

    export class EditUserController extends app.abstract.profile.AbstractProfileController {

        static $inject = [
            //from abstract
            'userService',
            'notificationService',
            'authService',
            'countries',
            'timezones',
            'genderOptions',
            'regions',
            'providerTypes',
            'fullUserInfo',

            //EditUserController
        ];

        constructor(
            userService:common.services.user.UserService,
            notificationService:common.services.notification.NotificationService,
            authService:common.services.auth.AuthService,
            countries:common.services.countries.ICountryDefinition,
            timezones:common.services.timezones.ITimezoneDefinition,
            genderOptions:common.models.IGenderOption[],
            regions:global.ISupportedRegion[],
            providerTypes:string[],
            fullUserInfo:common.models.User
        ) {

            super(userService, notificationService, authService, countries, timezones, genderOptions, regions, providerTypes, fullUserInfo)
        }

    }

    angular.module(namespace, [])
        .config(EditUserConfig)
        .controller(namespace+'.controller', EditUserController);
}




