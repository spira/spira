///<reference path="../../../../src/global.d.ts" />

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

                            $location.search('emailConfirmationToken', null);

                            return userService.confirmEmail(<common.models.User>ngJwtAuthService.getUser(), emailToken)
                                .then(() => {

                                    _.delay(() => notificationService.toast('Your email has successfully been updated').pop(), 1000);

                                    return true;

                                }, (err) => {
                                    _.delay(() => notificationService.toast('Your email has not been updated, please try again').pop(), 1000);

                                    return false;
                                });

                        }

                        return $q.when(false);
                    },
                    countries:(countriesService:common.services.countries.CountriesService) => countriesService.getAllCountries(),
                    timezones:(timezonesService:common.services.timezones.TimezonesService) => timezonesService.getAllTimezones(),
                    userProfile:(userService:common.services.user.UserService) => userService.getProfile(userService.getAuthUser()),
                    genderOptions:() =>  common.models.UserProfile.genderOptions,
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

    export class ProfileController {

        static $inject = ['userService', 'user', 'notificationService', 'emailConfirmed', 'countries', 'timezones', 'userProfile', 'genderOptions'];
        constructor(
            private userService:common.services.user.UserService,
            public user:common.models.User,
            private notificationService:common.services.notification.NotificationService,
            private emailConfirmed:boolean,
            public countries:common.services.countries.ICountryDefinition,
            public timezones:common.services.timezones.ITimezoneDefinition,
            public userProfile:common.models.UserProfile,
            public genderOptions:common.models.IGenderOption[]
        ) {

            if (emailConfirmed){
                this.user = userService.getAuthUser(); //if the email has been confirmed, the auth user's email will have updated
            }

            user._userProfile = userProfile;

        }

        public updateProfile():void {
            this.userService.updateProfile(this.user)
                .then(() => {
                    this.notificationService.toast('Profile update was successful').pop();
                },
                (err) => {
                    this.notificationService.toast('Profile update was unsuccessful, please try again').pop();
                })
        }
    }

    angular.module(namespace, [])
        .config(ProfileConfig)
        .controller(namespace+'.controller', ProfileController);
}




