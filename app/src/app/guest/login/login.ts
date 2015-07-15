module app.guest.login {

    export const namespace = 'app.guest.login';

    class LoginConfig {

        static $inject = ['ngJwtAuthServiceProvider'];
        constructor(private ngJwtAuthServiceProvider:NgJwtAuth.NgJwtAuthServiceProvider){

            let config : NgJwtAuth.INgJwtAuthServiceConfig = {
                refreshBeforeSeconds: 60 * 10, //10 mins
                checkExpiryEverySeconds: 60, //1 min
            };

            ngJwtAuthServiceProvider.configure(config);

        }

    }

    class LoginInit {

        static $inject = ['ngJwtAuthService', '$mdDialog'];
        constructor(
            private ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
            private $mdDialog:ng.material.IDialogService
        ) {

            ngJwtAuthService
                .registerCredentialPromiseFactory(function(existingUser){

                    let dialogConfig:ng.material.IDialogOptions = {
                        templateUrl: 'templates/app/guest/login/login-dialog.tpl.html',
                        controller: namespace+'.controller',
                        clickOutsideToClose: true,
                    };

                    return $mdDialog.show(dialogConfig);
                })
                .init(); //initialise the auth service (kicks off the timers etc)
        }

    }

    interface IScope extends ng.IScope
    {
        login(username:string, password:string):void;
        cancelLoginDialog():void;
    }

    class LoginController {

        static $inject = ['$scope', '$mdDialog'];
        constructor(
            private $scope : IScope,
            private $mdDialog:ng.material.IDialogService
        ) {

            $scope.login = function (username, password) {

                let credentials:NgJwtAuth.ICredentials = {
                    username: username,
                    password: password,
                };

                $mdDialog.hide(credentials);
            };

            $scope.cancelLoginDialog = () => {
                $mdDialog.cancel();
            }

        }

    }

    angular.module(namespace, [])
        .config(LoginConfig)
        .run(LoginInit)
        .controller(namespace+'.controller', LoginController);

}