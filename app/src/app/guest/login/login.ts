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

        static $inject = ['$rootScope', 'ngJwtAuthService', '$mdDialog', '$timeout', '$window', '$state', '$q', '$location'];
        constructor(
            private $rootScope:global.IRootScope,
            private ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
            private $mdDialog:ng.material.IDialogService,
            private $timeout:ng.ITimeoutService,
            private $window:ng.IWindowService,
            private $state:ng.ui.IStateService,
            private $q:ng.IQService
        ) {


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
        resetPassword():void;
    }

    class LoginController {

        static $inject = ['$scope', '$rootScope', '$mdDialog', '$mdToast', 'ngJwtAuthService', 'deferredCredentials', 'loginSuccess', 'userService'];
        constructor(
            private $scope : IScope,
            private $rootScope : global.IRootScope,
            private $mdDialog:ng.material.IDialogService,
            private $mdToast:ng.material.IToastService,
            private ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
            private deferredCredentials:ng.IDeferred<NgJwtAuth.ICredentials>,
            private loginSuccess:{promise:ng.IPromise<NgJwtAuth.IUser>},
            private userService:common.services.user.UserService
        ) {

            $scope.login = (username, password) => {

                let credentials:NgJwtAuth.ICredentials = {
                    username: username,
                    password: password,
                };

                deferredCredentials.notify(credentials); //resolve the deferred credentials with the passed creds

            };

            $scope.cancelLoginDialog = () => {
                ngJwtAuthService.logout(); //make sure the user is logged out
                $mdDialog.cancel('closed');
            }; //allow the user to manually close the dialog

            $scope.socialLogin = $rootScope.socialLogin;

            $scope.resetPassword = () => {
                $scope.cancelLoginDialog();
                userService.promptResetPassword();
            }; //close the login modal and open the reset password one

            //register error handling and close on success
            loginSuccess.promise
                .then(
                (user) => $mdDialog.hide(user), //on success hide the dialog, pass through the returned user object
                null,
                (err:Error) => {
                    if (err instanceof NgJwtAuth.NgJwtAuthCredentialsFailedException){
                        this.$mdToast.show(
                            (<any>$mdToast).simple() //<any> added so the parent method doesn't throw error, see https://github.com/borisyankov/DefinitelyTyped/issues/4843#issuecomment-124443371
                                .hideDelay(2000)
                                .position('top')
                                .content(err.message)
                                .parent('#loginDialog')
                        );
                    }else{
                        console.error(err);
                    }
                }
            );

        }

    }

    angular.module(namespace, [])
        .config(LoginConfig)
        .run(LoginInit)
        .controller(namespace+'.controller', LoginController);

}