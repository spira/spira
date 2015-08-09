namespace app.guest.resetPassword {

    export const namespace = 'app.guest.resetPassword';

    export class ResetPasswordController {

        static $inject = ['$mdDialog', 'userService', '$mdToast', 'defaultEmail'];

        constructor(private $mdDialog:ng.material.IDialogService,
                    private userService:common.services.user.UserService,
                    private $mdToast:ng.material.IToastService,
                    public email:string) {
        }

        /**
         * trigger password reset flow for given email
         * @param email
         */
        public resetPassword(email:string):ng.IPromise<any> {
            return this.userService.resetPassword(email)
                .then(() => {
                    this.cancelResetPasswordDialog();
                    this.$mdToast.show({
                        hideDelay: 2000,
                        position: 'top',
                        template: '<md-toast>Password reset instructions have been sent to your email.</md-toast>'
                    });
                })
                .catch((error) => {

                    this.$mdToast.show({
                        hideDelay: 2000,
                        position: 'top',
                        parent: '#resetPasswordDialog',
                        template: '<md-toast>' + error.data.message + '</md-toast>'
                    });
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