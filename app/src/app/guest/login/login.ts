namespace app.guest.login {

    export const namespace = 'app.guest.login';

    export class LoginController {

        private credentials:NgJwtAuth.ICredentials;

        static $inject = ['$rootScope', '$mdDialog', 'notificationService', 'ngJwtAuthService', 'deferredCredentials', 'loginSuccess', 'userService', 'authService'];

        constructor(private $rootScope:global.IRootScope,
                    private $mdDialog:ng.material.IDialogService,
                    private notificationService:common.services.notification.NotificationService,
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
                (user) => {
                    this.$mdDialog.hide(user); //on success hide the dialog, pass through the returned user object

                    //user has logged on with an unconfirmed email
                    if(user.email != this.credentials.username) {
                        this.$mdDialog.show(this.$mdDialog.alert()
                            .clickOutsideToClose(false)
                            .title('Please confirm your email')
                            .content('You have signed in using an unconfirmed email address. Please note that your email address will not be changed until you have confirmed your email.')
                            .ok('Continue')
                            .ariaLabel('Confirm Email')
                        );
                    }
                },
                null,
                (err:Error) => {
                    if (err instanceof NgJwtAuth.NgJwtAuthCredentialsFailedException) {
                        this.notificationService.toast(err.message).options({parent: '#loginDialog'}).pop();
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

            this.credentials = {
                username: username,
                password: password,
            };

            this.deferredCredentials.notify(this.credentials); //resolve the deferred credentials with the passed creds

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
        .controller(namespace + '.controller', LoginController);

}
