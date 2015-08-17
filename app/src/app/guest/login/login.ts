namespace app.guest.login {

    export const namespace = 'app.guest.login';

    class LoginConfig {

        static $inject = ['ngJwtAuthServiceProvider'];

        constructor(private ngJwtAuthServiceProvider:NgJwtAuth.NgJwtAuthServiceProvider) {

            let config:NgJwtAuth.INgJwtAuthServiceConfig = {
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

    export class LoginController {

        static $inject = ['$rootScope', '$mdDialog', '$mdToast', 'ngJwtAuthService', 'deferredCredentials', 'loginSuccess', 'userService', 'authService'];

        constructor(private $rootScope:global.IRootScope,
                    private $mdDialog:ng.material.IDialogService,
                    private $mdToast:ng.material.IToastService,
                    private ngJwtAuthService:NgJwtAuth.NgJwtAuthService,
                    private deferredCredentials:ng.IDeferred<NgJwtAuth.ICredentials>,
                    private loginSuccess:{promise:ng.IPromise<NgJwtAuth.IUser>},
                    private userService:common.services.user.UserService,
                    private authService:common.services.auth.AuthService
        ) {

            this.handleLoginSuccessPromise();

        }

        /**
         * Register social login function for Login Controller
         * @param type
         */
        public socialLogin(type:string):void {
            this.authService.socialLogin(type);
        }

        /**
         * Register the login success promise handler
         */
        private handleLoginSuccessPromise() {

            //register error handling and close on success
            this.loginSuccess.promise
                .then(
                (user) => this.$mdDialog.hide(user), //on success hide the dialog, pass through the returned user object
                null,
                (err:Error) => {
                    if (err instanceof NgJwtAuth.NgJwtAuthCredentialsFailedException) {
                        this.$mdToast.show(
                            (<any>this.$mdToast).simple() //<any> added so the parent method doesn't throw error, see https://github.com/borisyankov/DefinitelyTyped/issues/4843#issuecomment-124443371
                                .hideDelay(2000)
                                .position('top')
                                .content(err.message)
                                .parent('#loginDialog')
                        );
                    } else {
                        console.error(err);
                    }
                }
            );
        }

        /**
         * allow the user to manually close the dialog
         */
        public cancelLoginDialog() {
            this.ngJwtAuthService.logout(); //make sure the user is logged out
            this.$mdDialog.cancel('closed');
        }

        /**
         * Attempt login
         * @param username
         * @param password
         */
        public login(username, password) {

            let credentials:NgJwtAuth.ICredentials = {
                username: username,
                password: password,
            };

            this.deferredCredentials.notify(credentials); //resolve the deferred credentials with the passed creds

        }

        /**
         * Trigger reset password flow
         */
        public resetPassword(email?:string) {
            this.cancelLoginDialog();
            this.userService.promptResetPassword(email);
        }

    }

    angular.module(namespace, [])
        .config(LoginConfig)
        .controller(namespace + '.controller', LoginController);

}