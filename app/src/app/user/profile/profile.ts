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
                        $mdToast:ng.material.IToastService
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
                        // We don't need anything but the timezone identifier here
                        return timezonesService.getAllTimezones()
                            .then((timezones:common.services.timezones.ITimezoneDefinition[]) => {
                                let result:string[] = _.pluck(timezones, 'timezoneIdentifier');
                                return result;
                            });
                    },
                    userProfile:(
                        userService:common.services.user.UserService,
                        ngJwtAuthService:NgJwtAuth.NgJwtAuthService
                    ) => {
                        return userService.getProfile(<common.models.User>ngJwtAuthService.getUser())
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

class ProfileController {

    static $inject = ['userService', '$stateParams', 'user', '$mdToast', 'countries', 'timezones', 'userProfile', '$filter'];
        constructor(
            private userService:common.services.user.UserService,
            private $stateParams:IStateParams,
            public user:common.models.User,
            private $mdToast:ng.material.IToastService,
            public countries:common.services.countries.ICountryDefinition,
            public timezones:string[],
            public userProfile:common.models.UserProfile,
            private $filter:ng.IFilterService
        ) {

            user._userProfile = userProfile;

        }

        public autocompleteSearchText:string;

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

        public searchTimezones(search:string):string[] {
            return _.filter(this.timezones, (s) => {
                let regEx = new RegExp(search);
                return regEx.test(s);
            });
        }

        public itemSelected():void {
            this.autocompleteSearchText = this.user.timezoneIdentifier;
        }

        public initAutocomplete():void {
            this.autocompleteSearchText = this.user.timezoneIdentifier;
            console.log("setting" + this.user.timezoneIdentifier);
        }

    }

    angular.module(namespace, [])
        .config(ProfileConfig)
        .controller(namespace+'.controller', ProfileController);

}
