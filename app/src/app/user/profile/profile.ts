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

        static $inject = ['userService', 'notificationService', 'countries', 'timezones', 'fullUserInfo', 'genderOptions', 'authService'];

        constructor(
            private userService:common.services.user.UserService,
            private notificationService:common.services.notification.NotificationService,
            private emailConfirmed:boolean,
            public countries:common.services.countries.ICountryDefinition,
            public timezones:common.services.timezones.ITimezoneDefinition,
            public fullUserInfo:common.models.User,
            public genderOptions:common.models.IGenderOption[],
            private authService:common.services.auth.AuthService,
            public providerTypes:string[] = common.models.UserSocialLogin.providerTypes
        ) {
            // Hack to make this work for now
            this.fullUserInfo._userProfile.dob = '1921-01-01';
            this.fullUserInfo.emailConfirmed = '2015-05-05';

            if (emailConfirmed) {
                let updatedUser = userService.getAuthUser();

                this.fullUserInfo.email = updatedUser.email; //if the email has been confirmed, the auth user's email will have updated
            }
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

        /**
         * Register social login function for Profile Controller
         * @param type
         */
        public socialLogin(type:string):void {

            this.authService.socialLogin(type);

        }

        /**
         * Register unlink social login function for Profile Controller
         * @param type
         */
        public unlinkSocialLogin(type:string):void {

            this.authService.unlinkSocialLogin(this.fullUserInfo, type)
                .then(() => {
                    // Typings for lodash must not have this callback shorthand
                    (<any>_).remove(this.fullUserInfo._socialLogins, 'provider', type);

                    this.notificationService.toast('Your ' + _.capitalize(type) + ' has been unlinked from your account').pop();
                });

        }
    }

    angular.module(namespace, [])
        .config(ProfileConfig)
        .controller(namespace+'.controller', ProfileController);
}




