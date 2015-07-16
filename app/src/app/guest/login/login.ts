module app.guest.login {

    export const namespace = 'app.guest.login';

    class LoginConfig {

        static $inject = ['ngJwtAuthServiceProvider'];
        constructor(private ngJwtAuthServiceProvider:NgJwtAuth.NgJwtAuthServiceProvider){

            let config : NgJwtAuth.INgJwtAuthServiceConfig = {
                refreshBeforeSeconds: 60 * 10, //10 mins
                checkExpiryEverySeconds: 60, //1 min
                apiEndpoints: {
                    base: '/api/auth/jwt',
                    login: '/login',
                    tokenExchange: '/token',
                    refresh: '/refresh',
                },
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
                .registerLoginPromptFactory((existingUser, deferredCredentials:ng.IDeferred) => {

                    let dialogConfig:ng.material.IDialogOptions = {
                        templateUrl: 'templates/app/guest/login/login-dialog.tpl.html',
                        controller: namespace+'.controller',
                        clickOutsideToClose: true,
                        locals : {
                            deferredCredentials: deferredCredentials
                        }
                    };

                    return $mdDialog.show(dialogConfig)
                        .catch(() => deferredCredentials.reject()) //if the dialog closes without resolving, reject the credentials request
                    ;

                })
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

        static $inject = ['$scope', '$mdDialog', 'deferredCredentials'];
        constructor(
            private $scope : IScope,
            private $mdDialog:ng.material.IDialogService,
            private deferredCredentials:ng.IDeferred
        ) {

            $scope.login = (username, password) => {

                let credentials:NgJwtAuth.ICredentials = {
                    username: username,
                    password: password,
                };

                deferredCredentials.resolve(credentials);

                deferredCredentials.promise
                    .then(
                        () => $mdDialog.hide(credentials), //on success hide the credentials
                        (err) => {
                            console.log('error'); //@todo display the error to the user. This will be something like password incorrect
                        }
                    )
                ;

            };

            $scope.cancelLoginDialog = () => $mdDialog.cancel();

        }

    }

    angular.module(namespace, [])
        .config(LoginConfig)
        .run(LoginInit)
        .controller(namespace+'.controller', LoginController);

}