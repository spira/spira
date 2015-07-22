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

    interface IScope extends ng.IScope
    {
    }

    interface IStateParams extends ng.ui.IStateParamsService
    {
        onBoard?:boolean;
    }

    class ProfileController {

        static $inject = ['$scope', 'userService', '$stateParams'];
        constructor(
            private $scope : IScope,
            private userService:common.services.UserService,
            private $stateParams:IStateParams
        ) {

            let runOnBoarding = $stateParams.onBoard;
            //@todo complete controller

        }

    }

    angular.module(namespace, [])
        .config(ProfileConfig)
        .controller(namespace+'.controller', ProfileController);

}
