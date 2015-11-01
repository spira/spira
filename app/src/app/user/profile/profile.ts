namespace app.user.profile {

    export const namespace = 'app.user.profile';

    class ProfileConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(
            private stateHelperServiceProvider
        ){

            let state:global.IState = {
                url: '/profile?emailConfirmationToken',
                views: {
                    "main@app.user": {
                        controller: namespace+'.controller',
                        controllerAs: 'ProfileController',
                        templateUrl: 'templates/app/user/profile/profile.tpl.html'
                    }
                },
                reloadOnSearch: false, // Do not reload state when we remove the emailConfirmationToken or loginToken
                params: <IStateParams> {
                    onBoard: false
                },
                resolve: /*@ngInject*/{
                    emailConfirmed:(
                        userService:common.services.user.UserService,
                        $stateParams:IStateParams,
                        ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
                        notificationService:common.services.notification.NotificationService,
                        $location:ng.ILocationService,
                        $q:ng.IQService
                    ) => {
                        if(!_.isEmpty($stateParams.emailConfirmationToken)) {

                            let emailToken = $stateParams.emailConfirmationToken;

                            return userService.confirmEmail(<common.models.User>ngJwtAuthService.getUser(), emailToken)
                                .then(() => {

                                    notificationService.toast('Your email has successfully been updated').delay(1000).pop();

                                    return true;

                                }, (err) => {

                                    notificationService.toast('Your email has not been updated, please try again').delay(1000).pop();

                                    return false;
                                });

                        }

                        return $q.when(false);
                    },
                    countries:(
                        countriesService:common.services.countries.CountriesService
                    ) => {
                        return countriesService.getAllCountries()
                    },
                    timezones:(
                        timezonesService:common.services.timezones.TimezonesService
                    ) => {
                        return timezonesService.getAllTimezones();
                    },
                    fullUserInfo:(
                        userService:common.services.user.UserService,
                        user:common.models.User //inherited from parent state
                    ) => {
                        return userService.getModel(user.userId, ['userCredential', 'userProfile', 'socialLogins', 'uploadedAvatar']);
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
                    title: "User Profile",
                        icon: 'extension',
                        navigation: true,
                }
            };

            stateHelperServiceProvider.addState(namespace, state);

        }

    }

    interface IStateParams extends ng.ui.IStateParamsService
    {
        onBoard?:boolean;
        emailConfirmationToken?:string;
    }

    export class ProfileController extends app.abstract.profile.AbstractProfileController {

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

            //ProfileController
            'emailConfirmed',
            '$location',
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
            fullUserInfo:common.models.User,

            private emailConfirmed:boolean,
            private $location:ng.ILocationService
        ) {
            
            super(userService, notificationService, authService, countries, timezones, genderOptions, regions, providerTypes, fullUserInfo)

            if (this.emailConfirmed) {
                let updatedUser = userService.getAuthUser();

                // If the email has been confirmed, the auth user's email will have updated
                this.fullUserInfo.email = updatedUser.email;
            }

            /**
             * Remove loginToken/emailConfirmationToken if present in the URL params. We need to do this so they are
             * not reused. Removing these will not reload state because 'reloadOnSearch' is set to false for this
             * state. Unfortunately these can not be removed in the resolves. loginToken can not be removed in
             * authService.ts:processLoginToken() for reasons undetermined.
             */
            this.$location.search('loginToken', null);

            this.$location.search('emailConfirmationToken', null);

        }

    }

    angular.module(namespace, [])
        .config(ProfileConfig)
        .controller(namespace+'.controller', ProfileController);
}




