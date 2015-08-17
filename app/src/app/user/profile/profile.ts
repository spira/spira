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
                    emailConfirmationToken:(
                        userService:common.services.user.UserService,
                        $stateParams:IStateParams,
                        ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
                        notificationService:common.services.notification.NotificationService,
                        $location:ng.ILocationService
                    ) => {
                        if(!_.isEmpty($stateParams.emailConfirmationToken)) {
                            userService.confirmEmail(<common.models.User>ngJwtAuthService.getUser(), $stateParams.emailConfirmationToken)
                                .then(() => {
                                    notificationService.toast('Your email has successfully been updated').pop();

                                    $location.search('emailConfirmationToken', null);

                                }, (err) => {
                                    notificationService.toast('Your email has not been updated, please try again').pop();
                                });
                        }
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
                        ngJwtAuthService:NgJwtAuth.NgJwtAuthService
                    ) => {
                        return userService.getUser(<common.models.User>ngJwtAuthService.getUser())
                    },
                    genderOptions:() => {
                        return common.models.UserProfile.genderOptions;
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

    export class ProfileController {

        static $inject = ['userService', 'user', 'notificationService', 'countries', 'timezones', 'userProfile', 'genderOptions'];
        constructor(
            private userService:common.services.user.UserService,
            public user:common.models.User,
            private notificationService:common.services.notification.NotificationService,
            public countries:common.services.countries.ICountryDefinition,
            public timezones:common.services.timezones.ITimezoneDefinition,
            public fullUserInfo:common.models.User,
            public genderOptions:common.models.IGenderOption[]
        ) {
            // Hack to make this work for now
            this.fullUserInfo._userProfile.dob = '1921-01-01';
            this.fullUserInfo.emailConfirmed = '2015-05-05';
        }

        public updateUser():void {

            if(_.isEmpty(this.fullUserInfo._userCredential)) {
                delete this.fullUserInfo._userCredential;
            }

            this.userService.updateUser(this.fullUserInfo)
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




