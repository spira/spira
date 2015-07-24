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

        static $inject = ['$rootScope', 'ngJwtAuthService', '$mdDialog', '$timeout', '$window', '$state'];
        constructor(
            private $rootScope:global.IRootScope,
            private ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
            private $mdDialog:ng.material.IDialogService,
            private $timeout:ng.ITimeoutService,
            private $window:ng.IWindowService,
            private $state:ng.ui.IStateService
        ) {

            ngJwtAuthService
                .registerLoginPromptFactory((deferredCredentials:ng.IDeferred<NgJwtAuth.ICredentials>, loginSuccessPromise:ng.IPromise<NgJwtAuth.IUser>, currentUser:NgJwtAuth.IUser): ng.IPromise<any> => {

                    let dialogConfig:ng.material.IDialogOptions = {
                        templateUrl: 'templates/app/guest/login/login-dialog.tpl.html',
                        controller: namespace+'.controller',
                        clickOutsideToClose: true,
                        locals : {
                            deferredCredentials: deferredCredentials,
                            loginSuccess: {
                                promise: loginSuccessPromise //nest the promise in a function as otherwise material will try to wait for it to resolve
                            },
                        }
                    };

                    return $timeout(_.noop) //first do an empty timeout to allow the controllers to init if login prompt is fired from within a .run() phase
                        .then(() => $mdDialog.show(dialogConfig));

                })
                .init(); //initialise the auth service (kicks off the timers etc)



            $rootScope.socialLogin = (type:string, redirectState:string = $state.current.name, redirectStateParams:Object = $state.current.params) => {

                let url = '/auth/social/' + type;

                url += '?returnUrl=' + (<any>this.$window).encodeURIComponent(this.$state.href(redirectState, redirectStateParams));

                this.$window.location.href = url;

            }
        }

    }

    interface IScope extends ng.IScope
    {
        login(username:string, password:string):void;
        cancelLoginDialog():void;
        loginError:string;
        socialLogin(type:string);
    }

    class LoginController {

        static $inject = ['$scope', '$rootScope', '$mdDialog', 'ngJwtAuthService', 'deferredCredentials', 'loginSuccess'];
        constructor(
            private $scope : IScope,
            private $rootScope : global.IRootScope,
            private $mdDialog:ng.material.IDialogService,
            private ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
            private deferredCredentials:ng.IDeferred<NgJwtAuth.ICredentials>,
            private loginSuccess:{promise:ng.IPromise<NgJwtAuth.IUser>}
        ) {

            $scope.loginError = '';

            $scope.login = (username, password) => {

                let credentials:NgJwtAuth.ICredentials = {
                    username: username,
                    password: password,
                };

                deferredCredentials.resolve(credentials); //resolve the deferred credentials with the passed creds

                loginSuccess.promise
                    .then(
                        (user) => $mdDialog.hide(user), //on success hide the dialog, pass through the returned user object
                        (err:Error) => {
                            if (err instanceof NgJwtAuth.NgJwtAuthException){
                                $scope.loginError = err.message; //if the is an auth exception, show the value to the user
                            }
                        }
                    )
                ;

            };

            $scope.cancelLoginDialog = () => {
                ngJwtAuthService.logout(); //make sure the user is logged out
                $mdDialog.cancel('closed');
            }; //allow the user to manually close the dialog


            $scope.socialLogin = $rootScope.socialLogin;

        }

    }

    angular.module(namespace, [])
        .config(LoginConfig)
        .run(LoginInit)
        .controller(namespace+'.controller', LoginController);

}