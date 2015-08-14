namespace app.guest.resetPassword {

    export const namespace = 'app.guest.resetPassword';

    export class ResetPasswordController {

        static $inject = ['$mdDialog', 'userService', 'defaultEmail', 'notificationService'];

        constructor(private $mdDialog:ng.material.IDialogService,
                    private userService:common.services.user.UserService,
                    public email:string,
                    private notificationService:common.services.notification.NotificationService) {
        }

        /**
         * trigger password reset flow for given email
         * @param email
         */
        public resetPassword(email:string):ng.IPromise<any> {
            return this.userService.resetPassword(email)
                .then(() => {
                    this.cancelResetPasswordDialog();

                    this.notificationService.toast('Password reset instructions have been sent to your email').pop();
                })
                .catch((error) => {
                    this.notificationService.toast(error.data.message).pop();
                });
        }

        /**
         * Abort password reset dialog
         */
        public cancelResetPasswordDialog():void {
            this.$mdDialog.cancel('closed');
        }

    }

    angular.module(namespace, [])
        .controller(namespace + '.controller', ResetPasswordController);
}