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
                        $mdToast:ng.material.IToastService,
                        $location:ng.ILocationService
                    ) => {
                        if(!_.isEmpty($stateParams.emailConfirmationToken)) {
                            userService.confirmEmail(<common.models.User>ngJwtAuthService.getUser(), $stateParams.emailConfirmationToken)
                                .then(() => {
                                    $mdToast.show(
                                        $mdToast.simple()
                                            .hideDelay(2000)
                                            .position('top right')
                                            .content('Your email has successfully been updated')
                                    );

                                    $location.search('emailConfirmationToken', null);

                                }, (err) => {
                                    $mdToast.show(
                                        $mdToast.simple()
                                            .hideDelay(2000)
                                            .position('top right')
                                            .content('Your email has not been updated, please try again')
                                    );
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
                    userProfile:(
                        userService:common.services.user.UserService,
                        ngJwtAuthService:NgJwtAuth.NgJwtAuthService
                    ) => {
                        return userService.getProfile(<common.models.User>ngJwtAuthService.getUser())
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

        static $inject = ['userService', 'user', '$mdToast', 'countries', 'timezones', 'userProfile', 'genderOptions'];
        constructor(
            private userService:common.services.user.UserService,
            public user:common.models.User,
            private $mdToast:ng.material.IToastService,
            public countries:common.services.countries.ICountryDefinition,
            public timezones:common.services.timezones.ITimezoneDefinition,
            public userProfile:common.models.UserProfile,
            public genderOptions:common.models.IGenderOption[]
        ) {

            user._userProfile = userProfile;

        }

        public updateProfile():void {
            this.userService.updateProfile(this.user)
                .then(() => {
                    this.$mdToast.show({
                        hideDelay:2000,
                        position:'top',
                        template:'<md-toast>Profile update was successful.</md-toast>'
                    });
                },
                (err) => {
                    this.$mdToast.show({
                        hideDelay:2000,
                        position:'top',
                        template:'<md-toast>Profile update was unsuccessful, please try again.</md-toast>'
                    });
                })
        }
    }

    angular.module(namespace, [])
        .config(ProfileConfig)
        .controller(namespace+'.controller', ProfileController);
}




