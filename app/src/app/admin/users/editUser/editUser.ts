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
                    fullUserInfo:(userService:common.services.user.UserService, $stateParams:IEditUserStateParams) => {
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

    export interface IEditUserStateParams extends ng.ui.IStateParamsService
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
            '$mdDialog',
            '$state'
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

            private $mdDialog:ng.material.IDialogService,
            private $state:ng.ui.IStateService
        ) {

            super(userService, notificationService, authService, countries, timezones, genderOptions, regions, providerTypes, fullUserInfo)
        }

        public promptImpersonateDialog($event:MouseEvent, user:common.models.User) {

            var confirm = this.$mdDialog.confirm()
                .parent("#admin-container")
                .targetEvent($event)
                .title("Are you sure you want to impersonate this user?")
                .htmlContent(`
                    Any action you take as that user will appear to be done by that user.
                    <blockquote cite="Uncle Ben">With great power comes great responsibility <br><small>- Uncle Ben</small></blockquote>
                `)
                .ariaLabel("Confirm impersonate")
                .ok(`Impersonate ${user.fullName}!`)
                .cancel("Nope! I don't want to do that");

            return this.$mdDialog.show(confirm).then(() => {
                return this.authService.impersonateUser(user);
            })
                .then(() => {
                    this.$state.go('app.guest.home');
                });

        }

        public resetUserPassword($event:MouseEvent, user:common.models.User) {
            var confirm = this.$mdDialog.confirm()
                .parent("#admin-container")
                .targetEvent($event)
                .title("Reset User password?")
                .textContent(`@todo`)
                .ariaLabel("Reset Password")
                .ok(`Reset password for ${user.fullName}? @todo`)
                .cancel("Cancel");

            return this.$mdDialog.show(confirm).then(() => {
                //@todo
            })
        }

        public toggleBan(user:common.models.User) {
            //@todo
        }

    }

    angular.module(namespace, [])
        .config(EditUserConfig)
        .controller(namespace+'.controller', EditUserController);
}




