///<reference path="../../../../src/global.d.ts" />

module app.user.profile {

    export const namespace = 'app.user.profile';

    class ProfileConfig {

        static $inject = ['stateHelperServiceProvider'];
        constructor(private stateHelperServiceProvider){

            let state:global.IState = {
                url: '/profile',
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
    }

    class ProfileController {

        static $inject = ['userService', '$stateParams'];
        constructor(
            private userService:common.services.user.UserService,
            private $stateParams:IStateParams
        ) {

            let runOnBoarding = $stateParams.onBoard;

        }

        public updateProfile() {
            console.log('here');
        }

    }

    angular.module(namespace, [])
        .config(ProfileConfig)
        .controller(namespace+'.controller', ProfileController);

}
